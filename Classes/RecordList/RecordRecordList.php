<?php
namespace Intera\Recordlink\RecordList;

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

use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;

/**
 * Class for rendering of Web>List module
 */
class RecordRecordList extends DatabaseRecordList
{

	public $configKey = '';

	/**
	 * Returns the title (based on $code) of a record (from table $table) with the proper link around (that is for 'pages'-records a link to the level of that record...)
	 *
	 * @param string $table Table name
	 * @param int $uid Item uid
	 * @param string $code Item title (not htmlspecialchars()'ed yet)
	 * @param mixed[] $row Item row
	 * @return string The item title. Ready for HTML output (is htmlspecialchars()'ed)
	 */
	public function linkWrapItems($table, $uid, $code, $row)
	{
		$lang = $this->getLanguageService();
		$origCode = $code;
		// If the title is blank, make a "no title" label:
		if ((string)$code === '') {
			$code = '<i>[' . $lang->sL('LLL:EXT:lang/locallang_core.xlf:labels.no_title', 1) . ']</i> - ' . htmlspecialchars(GeneralUtility::fixed_lgd_cs(
					BackendUtility::getRecordTitle($table, $row),
					$this->getBackendUserAuthentication()->uc['titleLen']
				));
		} else {
			$code = htmlspecialchars(GeneralUtility::fixed_lgd_cs($code, $this->fixedL), ENT_QUOTES, 'UTF-8', false);
			if ($code != htmlspecialchars($origCode)) {
				$code = '<span title="' . htmlspecialchars($origCode, ENT_QUOTES, 'UTF-8', false) . '">' . $code . '</span>';
			}
		}

		$code = '<a href="#" class="t3js-recordLink" data-config="' . $this->configKey . '" data-uid="'.$uid.'">'
			. $code
			. '</a>';

		return $code;
	}

	/**
	 * Creates a page browser for tables with many records
	 *
	 * @param string $renderPart Distinguish between 'top' and 'bottom' part of the navigation (above or below the records)
	 * @return string Navigation HTML
	 */
	protected function renderListNavigation($renderPart = 'top')
	{
		$totalPages = ceil($this->totalItems / $this->iLimit);
		// Show page selector if not all records fit into one page
		if ($totalPages <= 1) {
			return '';
		}
		$content = '';
		$listURL = $this->getBrowseURL();

		// 1 = first page
		// 0 = first element
		$currentPage = floor($this->firstElementNumber / $this->iLimit) + 1;
		// Compile first, previous, next, last and refresh buttons
		if ($currentPage > 1) {
			$labelFirst = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_common.xlf:first', true);
			$labelPrevious = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_common.xlf:previous', true);
			$first = '<li><a href="' . $listURL . '&pointer=' . $this->getPointerForPage(1) . '" title="' . $labelFirst . '">'
				. $this->iconFactory->getIcon('actions-view-paging-first', Icon::SIZE_SMALL)->render() . '</a></li>';
			$previous = '<li><a href="' . $listURL . '&pointer=' . $this->getPointerForPage($currentPage - 1) . '" title="' . $labelPrevious . '">'
				. $this->iconFactory->getIcon('actions-view-paging-previous', Icon::SIZE_SMALL)->render() . '</a></li>';
		} else {
			$first = '<li class="disabled"><span>' . $this->iconFactory->getIcon('actions-view-paging-first', Icon::SIZE_SMALL)->render() . '</span></li>';
			$previous = '<li class="disabled"><span>' . $this->iconFactory->getIcon('actions-view-paging-previous', Icon::SIZE_SMALL)->render() . '</span></li>';
		}
		if ($currentPage < $totalPages) {
			$labelNext = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_common.xlf:next', true);
			$labelLast = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_common.xlf:last', true);
			$next = '<li><a href="' . $listURL . '&pointer=' . $this->getPointerForPage($currentPage + 1) . '" title="' . $labelNext . '">'
				. $this->iconFactory->getIcon('actions-view-paging-next', Icon::SIZE_SMALL)->render() . '</a></li>';
			$last = '<li><a href="' . $listURL . '&pointer=' . $this->getPointerForPage($totalPages) . '" title="' . $labelLast . '">'
				. $this->iconFactory->getIcon('actions-view-paging-last', Icon::SIZE_SMALL)->render() . '</a></li>';
		} else {
			$next = '<li class="disabled"><span>' . $this->iconFactory->getIcon('actions-view-paging-next', Icon::SIZE_SMALL)->render() . '</span></li>';
			$last = '<li class="disabled"><span>' . $this->iconFactory->getIcon('actions-view-paging-last', Icon::SIZE_SMALL)->render() . '</span></li>';
		}

		$reload = '<li><a href="#" onclick="document.dblistForm.action=' . GeneralUtility::quoteJSvalue($listURL
				. '&pointer=') . '+calculatePointer(document.getElementById(' . GeneralUtility::quoteJSvalue('jumpPage-' . $renderPart)
			. ').value); document.dblistForm.submit(); return true;" title="'
			. $this->getLanguageService()->sL('LLL:EXT:lang/locallang_common.xlf:reload', true) . '">'
			. $this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL)->render() . '</a></li>';
		// TODO: reload
		$reload = '';
		if ($renderPart === 'top') {
			// Add js to traverse a page select input to a pointer value
			$content = '
<script type="text/javascript">
/*<![CDATA[*/
	function calculatePointer(page) {
		if (page > ' . $totalPages . ') {
			page = ' . $totalPages . ';
		}
		if (page < 1) {
			page = 1;
		}
		return (page - 1) * ' . $this->iLimit . ';
	}
/*]]>*/
</script>
';
		}
		$pageNumberInput = '
			<input type="number" min="1" max="' . $totalPages . '" value="' . $currentPage . '" size="3" class="form-control input-sm paginator-input" id="jumpPage-' . $renderPart . '" name="jumpPage-'
			. $renderPart . '" onkeyup="if (event.keyCode == 13) { document.dblistForm.action=' . GeneralUtility::quoteJSvalue($listURL
				. '&pointer=') . '+calculatePointer(this.value); document.dblistForm.submit(); } return true;" />
			';
		$pageIndicatorText = sprintf(
			$this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:pageIndicator'),
			$pageNumberInput,
			$totalPages
		);
		$pageIndicator = '<li><span>' . $pageIndicatorText . '</span></li>';
		if ($this->totalItems > $this->firstElementNumber + $this->iLimit) {
			$lastElementNumber = $this->firstElementNumber + $this->iLimit;
		} else {
			$lastElementNumber = $this->totalItems;
		}
		$rangeIndicator = '<li><span>' . sprintf($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:rangeIndicator'), ($this->firstElementNumber + 1), $lastElementNumber) . '</span></li>';

		$titleColumn = $this->fieldArray[0];
		$data = [
			$titleColumn => $content . '
				<nav class="pagination-wrap">
					<ul class="pagination pagination-block">
						' . $first . '
						' . $previous . '
						' . $rangeIndicator . '
						' . $pageIndicator . '
						' . $next . '
						' . $last . '
						' . $reload . '
					</ul>
				</nav>
			'
		];
		return $this->addElement(1, '', $data);
	}

	public function getSearchBox($formFields = true) {

		$formElements = array('<form action="' . htmlspecialchars($this->getSearchURL()) . '" method="post" style="padding:0;">', '</form>');

		// Table with the search box:
		$content = '<div class="db_list-searchbox-form">
			' . $formElements[0] . '

				<!--
					Search box:
				-->
				<table border="0" cellpadding="0" cellspacing="0" id="typo3-dblist-search">
					<tr>
						<td style="padding-right:10px;">' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.enterSearchString', TRUE) . '</td>
						<td><input type="text" name="search_field" id="search_field" value="' . htmlspecialchars($this->searchString) . '" style="width: 100%; height: 23px;" /></td>
						<td><input type="submit" name="search" value="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.search', TRUE) . '" /></td>
					</tr>
				</table>
			' . $formElements[1]
			. '</div>'
			. '<br><br>';
		return $content;
	}

	public function getBrowseURL() {
		if (!isset($this->browseURL)) {
			$this->browseURL = $this->listURL() . '&act=record&config_key='.$this->configKey;
		}
		return $this->browseURL;
	}
	public function getSearchURL() {
		return $this->getBrowseURL() . '&pointer=0';
	}
}
