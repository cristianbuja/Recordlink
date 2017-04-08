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
		$content .= 'Please perform query manualy before upgrade to TYPO3 8 LTS. Follow examples:';
		$content .= '</h2>';
		$content .= '<p>'
			. 'Query for update link fields: ' . '<br>'
			. 'UPDATE [table] SET [field] = REPLACE([field], \'record:[key]\',  \'recordlink:[key]\') WHERE 1=1'
			. '</p>';
		$content .= '<p>'
			. 'Query for update rte fields: ' . '<br>'
			. 'UPDATE [table] SET [field] = REPLACE([field], \'<link record:[key]\',  \'<link recordlink:[key]\') WHERE 1=1'
			. '</p>';

		$content .= '<p>'
			. 'Example Query for tt_content and sys_file_reference for example key "category": ' . '<br>'
			. 'UPDATE tt_content SET header_link = REPLACE(header_link, \'record:category\', \'recordlink:category\') WHERE 1=1;' .'<br>'
			. 'UPDATE tt_content SET bodytext = REPLACE(bodytext, \'&lt;link record:category\', \'&lt;link recordlink:category\') WHERE 1=1;' .'<br>'
			. 'UPDATE sys_file_reference SET link = REPLACE(link, \'record:category\', \'recordlink:category\') WHERE 1=1;'
			. '</p>';

		return $content;
	}


	public function access()
	{
		return true;
	}

}