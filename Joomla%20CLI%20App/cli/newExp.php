<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the files_joomla file in the current language (without allowing the loading of the file in the default language)
$lang->load('files_joomla.sys', JPATH_SITE, null, false, false)
// Fallback to the files_joomla file in the default language
|| $lang->load('files_joomla.sys', JPATH_SITE, null, true);

/**
 * A command line cron job to attempt to export tables and data.
 *
 * @since  3.0
 */
class DBExporterCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		// Import the dependencies
		jimport('joomla.filesystem.file');
		$pathPart = JPATH_ROOT . '/cli/dbdump/';
		$tables   = JFactory::getDbo()->getTableList();
		$prefix   = JFactory::getDbo()->getPrefix();
		$exp      = JFactory::getDbo()->getExporter()->withStructure();

		foreach ($tables as $table)
		{
			if (strpos($table, $prefix) !== false)
			{
				$filename = $pathPart . $table . '.xml';
				$this->out('Exporting ' . $table . '....');
				$this->out();
				$data =(string) $exp->from($table)->withData(true);
				if (JFile::exists($filename)) JFile::delete($filename);
				JFile::write($filename, $data);
			}	
		}

	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('DBExporterCli')->execute();
