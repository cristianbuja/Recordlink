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

// Linkhandler
$GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['recordlink']
	= 'Intera\Recordlink\LinkHandling\RecordLinkHandler';
