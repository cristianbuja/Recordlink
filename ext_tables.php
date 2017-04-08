<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {

    // register record link handlers
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
		TCEMAIN.linkHandler {
			recordlink {
				handler = Intera\Recordlink\LinkHandler\RecordLinkHandler
				label = LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:record
				scanAfter = page
				configuration {
				}
			}
		}
	');

}
