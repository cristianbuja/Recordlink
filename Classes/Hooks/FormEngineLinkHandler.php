<?php
namespace Intera\Recordlink\Hooks;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Buja Cristian <cristian.buja@intera.it>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;

/**
 * FormEngine linkHandler Hooks
 *
 * @package Recordlink
 * @subpackage Hooks
 * @route off
 */
class FormEngineLinkHandler {

	/**
	 * Get Form Data
	 *
	 * @param array $linkData
	 * @param array $linkParts
	 * @param array $data
	 * @param AbstractFormElement $pObj
	 * @return string
	 */
    public function getFormData($linkData, $linkParts, $data, $pObj) {

	    $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $table = $data['pageTsConfig']['TCEMAIN.']['linkHandler.'][$linkData['type'] . '.']['configuration.'][$linkData['identifier'] . '.']['table'];
        $record = BackendUtility::getRecord($table, $linkData['uid']);
        $recordTitle = BackendUtility::getRecordTitle($table, $record);
        $label = $data['pageTsConfig']['TCEMAIN.']['linkHandler.'][$linkData['type'] . '.']['configuration.'][$linkData['identifier'] . '.']['label'];

	    if ($table) {
		    return [
			    'text' => sprintf('%s [%s:%d]', $recordTitle, $label, $linkData['uid']),
			    'icon' => $iconFactory->getIconForRecord($table, $record, Icon::SIZE_SMALL)->render()
		    ];
	    } else {
		    return [
			    'text' => $linkData['type'] . ' ' . $linkData['identifier'] . ' ' . $linkData['uid'],
			    'icon' => ''
		    ];
	    }
  }

}
