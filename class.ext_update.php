<?php
namespace Intera\Recordlink;


use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;
use TYPO3\CMS\Install\Service\SqlSchemaMigrationService;

/**
 * Class for updating the db
 */
class ext_update
{
	/**
	 * @var string Name of the extension this controller belongs to
	 */
	protected $extensionName = 'Recordlink';

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager Extbase Object Manager
	 */
	protected $objectManager;
	
	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\InstallUtility Extension Manager Install Tool
	 */
	protected $installTool;

	/**
	 * Main function, returning the HTML content
	 *
	 * @return string HTML
	 */
	public function main()
	{
		$content = '';
		$content .= '<h2>';
		$content .= 'Required if update to version 2.0.0';
		$content .= '</h2>';
		$content .= '<p>'
			. 'Query for update link and RTE fields: ' . '<br>'
			. 'UPDATE [table] SET [field] = REPLACE([field], \'recordlink:[key]:[uid]\',  \'t3://recordlink?identifier=[key]&amp;uid=[uid]\') WHERE 1=1'
			. '</p>';
		$content .= '<p>'
			. 'Example Query for tt_content and sys_file_reference for example key "category": ' . '<br>'
			. 'UPDATE tt_content SET header_link = REPLACE(header_link, \'recordlink:category:\', \'t3://recordlink?identifier=category&amp;uid=\') WHERE 1=1;' .'<br>'
			. 'UPDATE tt_content SET bodytext = REPLACE(bodytext, \'&lt;a href="recordlink:category:\', \'&lt;a href="t3://recordlink?identifier=category&amp;uid=\') WHERE 1=1;' .'<br>'
			. 'UPDATE sys_file_reference SET link = REPLACE(link, \'recordlink:category\', \'t3://recordlink?identifier=category&amp;uid=\') WHERE 1=1;'
			. '</p>';

		return $content;
	}


	public function access()
	{
		return true;
	}

}