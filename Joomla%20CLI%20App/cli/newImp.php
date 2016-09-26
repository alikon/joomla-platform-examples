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
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class ImporterCli extends JApplicationCli
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
		jimport('joomla.filesystem.folder');
		$pathPart = JPATH_ROOT . '/cli/dbdump/';
		$tables   = JFolder::files($pathPart, '\.xml$'); 
		$db       = JFactory::getDbo();
		$prefix   = $db->getPrefix();

		foreach ($tables as $table)
		{
			$percorso = $pathPart.$table; 
			$table_name = str_replace( '.xml' , '' ,$table);
			$this->out(' Importing ' . $table_name . ' from ' . $table);
			$this->out(' ');

			try
			{
				$this->out(' drop ' . $table_name);
				$this->out(' ');
				$db->dropTable($table_name, true);
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out(' Error in DROP TABLE ' . $table_name. ' ' . $e);
				return false;
			}

			$this->out(' dropped ' . $table_name);
			$this->out(' ');

			try
			{
				$imp = JFactory::getDbo()->getImporter()->from(JFile::read($percorso))->asXml();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out('Error on getImporter' . $table . ' ' . $e);
				return false;
			}

			$this->out(' Reading data from ' . $table);
			$this->out(' ');

			try
			{
				$imp->mergeStructure();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out('Error on mergeStructure' . $table . ' ' . $e);
				return false;
			}

			$this->out(' Merged structure from ' . $table);
			$this->out(' ');

			try
			{
				$imp->importData();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out('Error on importData' . $table . ' ' . $e);
				return false;
			}

		}

	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('ImporterCli')->execute();