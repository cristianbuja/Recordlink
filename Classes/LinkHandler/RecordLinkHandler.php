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
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;
use Intera\Recordlink\RecordList\RecordRecordList;

use TYPO3\CMS\Core\LinkHandling\LinkService;

/**
 * Link handler for record links
 */
class RecordLinkHandler extends AbstractLinkHandler implements LinkHandlerInterface, LinkParameterProviderInterface
{

    /**
     * Parts of the current link
     *
     * @var array
     */
    protected $linkParts = [];

	protected $configuration = [];

    /**
     * Initialize the handler
     *
     * @param AbstractLinkBrowserController $linkBrowser
     * @param string $identifier
     * @param array $configuration Page TSconfig
     *
     * @return void
     */
    public function initialize(AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration)
    {
        parent::initialize($linkBrowser, $identifier, $configuration);

	    // Will be removed
        $this->configuration = $configuration;
        $this->configKey = GeneralUtility::_GP('config_key');
        $this->searchString = GeneralUtility::_GP('search_field');
        $this->pointer = intval(GeneralUtility::_GP('pointer'));
    }

    /**
     * Checks if this is the handler for the given link
     *
     * The handler may store this information locally for later usage.
     *
     * @param array $linkParts Link parts as returned from TypoLinkCodecService
     *
     * @return bool
     */
    public function canHandleLink(array $linkParts)
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

	    // This will be removed
	    $linkParts['act'] = 'recordlink';
	    $linkParts['info'] = $this->configuration[$this->configKey.'.']['label'];
	    $linkParts['configKey'] = $this->configKey;
	    $linkParts['recordTable'] = $table;
	    $linkParts['recordUid'] = $uid;

	    $this->linkParts = $linkParts;

	    return true;
    }

    /**
     * Format the current link for HTML output
     *
     * @return string
     */
    public function formatCurrentUrl()
    {
        $label = $this->linkParts['info'];
        $table = $this->linkParts['recordTable'];
        $uid = $this->linkParts['recordUid'];
        $record = BackendUtility::getRecordWSOL($table, $uid);
        $title = BackendUtility::getRecordTitle($table, $record, FALSE, TRUE);
        $titleLen = (int)$this->getBackendUser()->uc['titleLen'];
        $title = GeneralUtility::fixed_lgd_cs($title, $titleLen);

        return $label . ' \'' . $title . '\' (ID:' . $uid . ')';
    }

    /**
     * Render the link handler
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function render(ServerRequestInterface $request)
    {
        GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('TYPO3/CMS/Recordlink/RecordLinkHandler');

	    $content = '
			<div class="element-browser-panel element-browser-main">
				<div class="element-browser-main-content">
					<div class="element-browser-body">
						<form action="" id="lrecordselector" class="form-horizontal">
							<div class="form-group form-group-sm">
								<label class="col-xs-4 control-label">' . $GLOBALS['LANG']->sL('LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:select_linktype') . '</label>
								<div class="col-xs-8">' . $this->getRecordSelector() . '</div>
							</div>
						</form>
						' . $this->getRecordList() . '
					</div>
				</div>
			</div>
	    ';

	    // Non LTS version untested
	    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8000000) {
		    $content = '

				<!--
					Wrapper table for record Selector:
				-->
						<table border="0" cellpadding="0" cellspacing="0" id="typo3-EBrecords">
							<tr>
								<td class="c-wCell" valign="top">' . $content . '</td>
							</tr>
						</table>
						';
	    }

        return $content;

    }

    /**
     * @return string[] Array of body-tag attributes
     */
    public function getBodyTagAttributes()
    {

	    $attributes = [];

	    // Non LTS version untested
	    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8000000) {
		    $configKey = $this->linkParts['configKey'];
		    $uid = $this->linkParts['recordUid'];
		    if (!empty($this->linkParts)) {
			    $attributes['data-current-link'] = 'recordlink:' . $configKey . ':' . $uid;
		    }
	    } else {
		    if (!empty($this->linkParts)) {
			    $attributes['data-current-link'] = GeneralUtility::makeInstance(LinkService::class)->asString($this->linkParts['url']);
		    }
        }

	    return $attributes;

    }

    /**
     * @param array $values Array of values to include into the parameters or which might influence the parameters
     *
     * @return string[] Array of parameters which have to be added to URLs
     */
    public function getUrlParameters(array $values)
    {
	    $pid = isset($values['pid']) ? (int)$values['pid'] : $this->expandPage;
	    $parameters = [
		    'expandPage' => $pid,
		    'config_key' => $this->configKey
	    ];

	    return array_merge(
		    $this->linkBrowser->getUrlParameters($values),
		    ['P' => $this->linkBrowser->getParameters()],
		    $parameters
	    );
    }

    /**
     * @param array $values Values to be checked
     *
     * @return bool Returns TRUE if the given values match the currently selected item
     */
    public function isCurrentlySelectedItem(array $values)
    {
	    // Will be removed
	    return false;
    }

    /**
     * Returns the URL of the current script
     *
     * @return string
     */
    public function getScriptUrl()
    {
	    // Will be removed
        return $this->linkBrowser->getScriptUrl();
    }

    // *********
    // Internal:
    // *********

    protected function getRecordSelector() {
	    $out = '';
	    $onChange = 'onchange="jumpToUrl(' . GeneralUtility::quoteJSvalue('?act=recordlink&config_key=') . ' + this.value); return false;"';
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

    protected function getRecordList() {
        $out = '';
	    $configKey = (!empty($this->configKey))
		    ? $this->configKey
		    : $this->linkParts['configKey'];
        $table = $this->configuration[$configKey . '.']['table'];
        $id = intval($this->configuration[$configKey . '.']['pid']);
        $pointer = $this->pointer;
        $recursive = intval($this->configuration[$configKey . '.']['recursive']);
	    $searchString = $this->searchString;

        if ($table) {

	        $recordRecordList = GeneralUtility::makeInstance(RecordRecordList::class);
	        $recordRecordList->configKey = $configKey;
	        $recordRecordList->iLimit = 10;
	        $recordRecordList->disableSingleTableView = TRUE;
	        $recordRecordList->clickMenuEnabled = FALSE;
	        $recordRecordList->noControlPanels = TRUE;
	        $recordRecordList->searchLevels = FALSE;

	        // TODO: Fare in modo migliore, soprattutto su RTE
	        $overrideParams = array(
		        'linkAttributes' => GeneralUtility::_GP('linkAttributes'),
		        'P' => GeneralUtility::_GP('P'),
		        'bparams' => GeneralUtility::_GP('bparams'),
		        'editorNo' => GeneralUtility::_GP('editorNo'),
		        'RTEtsConfigParams' => GeneralUtility::_GP('RTEtsConfigParams'),
		        'curUrl' => GeneralUtility::_GP('curUrl'),
	        );
	        foreach ($overrideParams as $k => $v) {
	        	if (empty($v)) {
	        		unset($overrideParams[$k]);
		        }
	        }

	        $recordRecordList->setOverrideUrlParameters($overrideParams);
	        $recordRecordList->start(
	        	$id, $table, $pointer,
		        $searchString,
		        $recursive, 10
	        );
            $list = $recordRecordList->getTable($table, $id, $GLOBALS['TCA'][$table]['ctrl']['label']);

            if (empty($list)) {
                $out .= '<div class="alert alert-info">'
                    . $GLOBALS['LANG']->sL('LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:norecords_found')
                    . '</div>';
            } else {
                $out .= $list;
            }
            $out .= $recordRecordList->getSearchBox();

        }

        return $out;
    }

}
