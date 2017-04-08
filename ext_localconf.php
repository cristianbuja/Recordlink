<?php
if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}


// Backend Form
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['recordlink']
	= 'Intera\Recordlink\Hooks\FormEngineLinkHandler';

// Frontend
$GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['recordlink']
	= 'Intera\Recordlink\Hooks\RecordLinkBuilder';



// NOTE: Will be removed when  7.6 compatibility will be dropped

// Frontend
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['recordlink']
	= 'Intera\Recordlink\Hooks\LinkHandler';


