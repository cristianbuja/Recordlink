<?php
namespace Intera\Recordlink\LinkHandler;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Recordlist\Browser\RecordBrowser;


/**
 * Link handler for record links
 */
class RecordLinkHandler extends \TYPO3\CMS\Recordlist\LinkHandler\RecordLinkHandler
{

	/**
	 * Checks if this is the right handler for the given link.
	 *
	 * Also stores information locally about currently linked record.
	 *
	 * @param array $linkParts Link parts as returned from TypoLinkCodecService
	 * @return bool
	 */
	public function canHandleLink(array $linkParts): bool
	{

		// check first if old link style (recordlink:configkey:uid)
		if (isset($linkParts['url']) && !is_array($linkParts['url'])) {
			list($handler, $configKey, $uid) = explode(':', $linkParts['url']);
			if ($handler != 'recordlink') {
				return false;
			}
			$linkParts['url'] = array();
			$linkParts['url']['identifier'] = $configKey;
			$linkParts['url']['uid'] = $uid;
		} elseif(is_array($linkParts['url']) && isset($linkParts['url']['url']) && isset($linkParts['type']) && $linkParts['type']=='recordlink') {
			// T3 8 LTS
			list($configKey, $uid) = explode(':', $linkParts['url']['url']);
			unset($linkParts['url']['url']);
			$linkParts['url']['identifier'] = $configKey;
			$linkParts['url']['uid'] = $uid;
		}

		if (!$linkParts['url'] || !isset($linkParts['url']['identifier'])) {
			return false;
		}


		$this->configKey = $linkParts['url']['identifier'];
		if (!isset($this->configuration[$this->configKey.'.'])) {
			return false;
		}

		$data = $linkParts['url'];


		// Get the related record
		$table = $this->configuration[$this->configKey.'.']['table'];

		$record = BackendUtility::getRecord($table, $data['uid']);
		if ($record === null) {
			$linkParts['title'] = $this->getLanguageService()->getLL('recordNotFound');
		} else {
			$linkParts['tableName'] = $this->getLanguageService()->sL($GLOBALS['TCA'][$table]['ctrl']['title']);
			$linkParts['pid'] = (int)$record['pid'];
			$linkParts['title'] = $linkParts['title'] ?: BackendUtility::getRecordTitle($table, $record);
		}
		$linkParts['url']['type'] = $linkParts['type'];

		$this->linkParts = $linkParts;

		return true;
	}


	/**
	 * Returns attributes for the body tag.
	 *
	 * @return string[] Array of body-tag attributes
	 */
	public function getBodyTagAttributes(): array
	{
		$attributes = [
			'data-identifier' => 't3://recordlink?identifier=' . $this->configKey . '&uid=',
		];
		if (!empty($this->linkParts)) {
			$attributes['data-current-link'] = GeneralUtility::makeInstance(LinkService::class)->asString($this->linkParts['url']);
		}

		return $attributes;
	}

	/**
	 * Returns all parameters needed to build a URL with all the necessary information.
	 *
	 * @param array $values Array of values to include into the parameters or which might influence the parameters
	 * @return string[] Array of parameters which have to be added to URLs
	 */
	public function getUrlParameters(array $values): array
	{
		$pid = isset($values['pid']) ? (int)$values['pid'] : $this->expandPage;
		$parameters = [
			'expandPage' => $pid,
			'configKey' => $this->configKey
		];

		return array_merge(
			$this->linkBrowser->getUrlParameters($values),
			['P' => $this->linkBrowser->getParameters()],
			$parameters
		);
	}

	/**
	 * Renders the link handler.
	 *
	 * @param ServerRequestInterface $request
	 * @return string
	 */
	public function render(ServerRequestInterface $request): string
	{
		// Declare JS module
		GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('TYPO3/CMS/Recordlist/RecordLinkHandler');

		// Override configKey
		if (isset($request->getQueryParams()['configKey'])) {
			$this->configKey = $request->getQueryParams()['configKey'];
		}

		// Define the current page
		if (isset($request->getQueryParams()['expandPage'])) {
			$this->expandPage = (int)$request->getQueryParams()['expandPage'];
		} elseif (isset($this->configuration[$this->configKey.'.']['storagePid'])) {
			$this->expandPage = (int)$this->configuration[$this->configKey.'.']['storagePid'];
		} elseif (isset($this->linkParts['pid'])) {
			$this->expandPage = (int)$this->linkParts['pid'];
		}


		if (isset($this->configuration[$this->configKey.'.'])) {
			$config = $this->configuration[$this->configKey.'.'];

			$databaseBrowser = GeneralUtility::makeInstance(RecordBrowser::class);

			$recordList = $databaseBrowser->displayRecordsForPage(
				$this->expandPage,
				$config['table'],
				$this->getUrlParameters([])
			);
		} else {
			$recordList = '';
		}

		$path = GeneralUtility::getFileAbsFileName('EXT:recordlink/Resources/Private/Templates/LinkBrowser/Record.html');
		$view = GeneralUtility::makeInstance(StandaloneView::class);
		$view->setTemplatePathAndFilename($path);
		$view->assignMultiple([
			'recordSelector' => $this->getRecordSelector(),
			'recordList' => $recordList,

		]);

		return $view->render();
	}

	// *********
	// Internal:
	// *********

	protected function getRecordSelector() {
		$out = '';
		$onChange = 'onchange="jumpToUrl(' . GeneralUtility::quoteJSvalue('?act=recordlink&configKey=') . ' + this.value); return false;"';
		$out .= '<select class="form-control" ' . $onChange . ' >';
		$out .= '<option value=""></option>';
		foreach ($this->configuration as $key => $config) {
			if(substr($key, -1, 1) == '.') {
				$key = substr($key, 0, -1);
				$label = (isset($config['label'])) ? $config['label'] : '';
				if($key==$this->configKey) {
					$out .= '<option value="'.$key.'" selected="selected">'.$label.'</option>';
				} elseif (!empty($key)) {
					$out .= '<option value="'.$key.'">'.$label.'</option>';
				}
			}
		}
		$out .= '</select>';
		return $out;
	}

}
