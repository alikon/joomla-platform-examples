<?php 
/** 
 * @package    Joomla.Cli 
 * 
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved. 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt 
 */ 

/** 
 * Add user CLI create dummy users
 * 
 * Run the framework bootstrap with a couple of mods based on the script's needs 
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
require_once dirname(__DIR__) . '/cli/clipbar.php';
// Import the configuration. 
require_once JPATH_CONFIGURATION . '/configuration.php'; 

// System configuration. 
$config = new JConfig; 

// Configure error reporting to maximum for CLI output. 
error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', 1); 
@set_time_limit(0);
@ini_set('memory_limit', '-1');

/**  
 * Bootstrap file for the Joomla Platform.  Including this file into your application will make Joomla  
 * Platform libraries available for use.  
 *  
 * @package    Joomla.Platform  
 *  
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.  
 * @license    GNU General Public License version 2 or later; see LICENSE  
 */  

// Set the platform root path as a constant if necessary.  
if (!defined('JPATH_PLATFORM'))  
{  
   
    define('JPATH_PLATFORM', __DIR__);  
}  
  
// Detect the native operating system type.  
$os = strtoupper(substr(PHP_OS, 0, 3));  

if (!defined('IS_WIN'))  
{  
    define('IS_WIN', ($os === 'WIN') ? true : false);  
}  
if (!defined('IS_UNIX'))  
{  
    define('IS_UNIX', (IS_WIN === false) ? true : false);  
}  

// Import the platform version library if necessary.  
if (!class_exists('JPlatform'))  
{  

    require_once JPATH_PLATFORM . '/platform.php';  
}  

// Import the library loader if necessary.  
if (!class_exists('JLoader'))  
{  
  
    require_once JPATH_PLATFORM . '/loader.php';  
}  
  
// Make sure that the Joomla Platform has been successfully loaded.  
if (!class_exists('JLoader'))  
{  
    throw new RuntimeException('Joomla Platform not loaded.');  
}  

// Setup the autoloaders.  
JLoader::setup();  

// Import the base Joomla Platform libraries.  
JLoader::import('joomla.factory');  

// Register classes for compatability with PHP 5.3  
if (version_compare(PHP_VERSION, '5.4.0', '<'))  
{  
    JLoader::register('JsonSerializable', JPATH_PLATFORM . '/compat/jsonserializable.php');  
}  

// Register classes that don't follow one file per class naming conventions.  
JLoader::register('JText', JPATH_PLATFORM . '/joomla/language/text.php');  
JLoader::register('JRoute', JPATH_PLATFORM . '/joomla/application/route.php');  

// Work around for not being in the CMS and needing to deal with wrong named files  
// and new cross-library dependencies.  
if (!defined('JPATH_LIBRARIES'))  
{  
    define('JPATH_LIBRARIES', dirname(__FILE__) . '/libraries');  
    define('JPATH_ROOT', dirname(__FILE__));  
    require JPATH_PLATFORM . '/import.legacy.php';  
    require JPATH_PLATFORM . '/cms.php';  
    require JPATH_PLATFORM . '/cms/helper/tags.php';  
    JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');  
    JLoader::Register('J', JPATH_PLATFORM . '/cms');  
}   
     





/** 
 * A command line cron job to create dummy articles and categories base on com_overload. 
 * 
 * @package  Joomla.Cli 
 * @since    3.0 
 */ 
class BulkcontentCli extends JApplicationCli 
{ 
    var $categories =1; 
    var $depth = 1;  
    var $totalcats=0; 
    var $donecats= 0; 
    var $level=0; 
    var $levelmap=0;
    var $menumap=0;
    var $articles=1;  
    protected $dbo = null; 
    const CLI_NAME = 'Add dummy articles, categories and menus from CLI';
    const CLI_VERSION ='Bulk 0.1 RC [FirstStep] - www.alikonweb.it';
    private $_time;
     
     
     public function __construct() { 
       
        // Call the parent __construct method so it bootstraps the application class. 
        //   parent::__construct(); 
        $this->app = JFactory::getApplication('site'); 
        // 
        // Prepare the logger. 
        // 
     

        // Include the JLog class. 
        jimport('joomla.log.log'); 

        // Get the date so that we can roll the logs over a time interval. 
        $date = JFactory::getDate()->format('Y-m-d'); 

        JLog::addLogger( 
                // Pass an array of configuration options. 
                // Note that the default logger is 'formatted_text' - logging to a file. 
                array( 
            // Set the name of the log file. 
            'text_file' => 'BulkContentcli.' . $date . '.php', 
             
                // Set the path for log files. 
                //    'text_file_path' => __DIR__ . '/logs' 
                ), JLog::INFO 
        ); 

        // 
        // Prepare the database connection. 
        // 

        jimport('joomla.database.database'); 
        $config = JFactory::getConfig(); 
        // Note, this will throw an exception if there is an error 
        // creating the database connection. 
        /* 
          $this->dbo = JDatabase::getInstance( 
          array( 
          'driver' => $this->get('dbDriver'), 
          'host' => $this->get('dbHost'), 
          'user' => $this->get('dbUser'), 
          'password' => $this->get('dbPass'), 
          'database' => $this->get('dbName'), 
          'prefix' => $this->get('dbPrefix'), 
          ) 
          ); 
         */ 
        $this->dbo = JFactory::getDBO(); 
        // Get the quey builder class from the database. 
         
    } 
 
    /** 
     * Entry point for CLI script 
     * 
     * @return  void 
     * 
     * @since   3.0 
     */ 
    public function doExecute() 
    {  
    	  echo(JPlatform::getLongVersion())."\n";
        echo(JPlatform::COPYRIGHT)."\n"; 
        echo(BulkContentCli::CLI_NAME)."\n"; 
        echo(BulkContentCli::CLI_VERSION)."\n"; 
        $this->_time = microtime(true);  
        // Fool the system into thinking we are running as JSite with Finder as the active component 
        JFactory::getApplication('admin'); 
        $_SERVER['HTTP_HOST'] = 'domain.com'; 
         // Include the JLog class. 
        $args = (array) $GLOBALS['argv'];
      
        if (defined('JSHELL'))
        {
         array_shift($args);
        }     
        if (count($args) < 2) {
          $this->out($this->help());
         exit(1);
        }
      $m=0;  
     // var_dump($args);
      for($i = 1; $i <= count($args); $i++) {  
         switch ($args[$i])
        {
        	 case  '-a':         	   
        	   $m++;
        	   $this->articles =$args[$i+1];        	  
        	   break;
        	 case  '-c':    
        	   $m++;     	   
        	   $this->categories =$args[$i+1];      	  
        	   break;  
        	 case  '-d':   
          	 $m++;         	 
             $this->depth  =$args[$i+1];      	  
        	   break;  

        	 case  '-x':       
        	  $m++;  	   
        	   $result=$this->clean(); 
        	   $m++;  	   
        	   exit(1);       	  
        	   break;  
        	 /*
        	 default:   
        	
             echo '[WARNING] Unknown parameter'."\n" ;
             $this->help();
                exit(1);
           
           }   
            break; 
           */                            

        }	      
      }	   
 //       jexit();
 
  
        //echo 'start'."\n";  
        if($m < 1){ 
         echo '[WARNING] Unknown parameter'."\n" ;
             $this->help();
                exit(1);
        } 
        $this->start(); 
    } 
   /**
	 * Generates a category level mapping, i.e. an array containing a category
	 * hierarchy based on the category and depth preferences.
	 * 
	 * @param type $categories
	 * @param type $depth
	 * @param type $prefix
	 * @return array
	 */
	private function makeLevelmap($categories, $depth, $prefix = '')
	{
		$ret = array();
		$prefix = empty($prefix) ? '' : $prefix.'.';
		for($i = 1; $i <= $categories; $i++) {
			$partial = $i;
			$ret[] = (string)$partial;
			if($depth > 1) {
				$fulls = $this->makeLevelmap($categories, $depth - 1, $partial);
				foreach($fulls as $something) {
					$ret[] = $partial.'.'.$something;
				}
			}
		}
	//	 print_r($ret);
    unset($full);
		return $ret;
	}
    public function clean() 
    {
    	// Remove articles from category
	$db = JFactory::getDBO(); 
	//$db->quoteName('id')
	$query = "DELETE FROM #__assets WHERE ".$db->qn('title')." LIKE " .$db->q('Bulk%');
	$db->setQuery($query);
	$result=$db->query(); // redo use try
	if(!$result) { 
            exit('311'); 
        } 
	$query = "DELETE FROM #__categories WHERE ".$db->qn('alias')." LIKE "  .$db->q('bulkcategory-%');
			$db->setQuery($query);
			$result=$db->query();  // redo use try
        if(!$result) { 
            exit('312'); 
        } 
        $query = "DELETE FROM #__content WHERE ".$db->qn('alias')." LIKE ".$db->q('bulkcontent-%');
	$db->setQuery($query);
	$result=$db->query();  // redo use try
	if(!$result) { 
            exit('313'); 
        } 
        $query = "DELETE FROM #__menu WHERE ".$db->qn('menutype')." LIKE ".$db->q('BulkMenuType%');
	$db->setQuery($query);
	$result=$db->query(); // redo use try
	if(!$result) { 
            exit('314'); 
        }       
        $query = "DELETE FROM #__menu_types WHERE ".$db->qn('menutype')." LIKE ".$db->q('bulkmenu%');
	$db->setQuery($query);
	$result=$db->query(); //  redo use try
	if(!$result) { 
            exit('315'); 
        }   
     
    }	 
    /** 
     * Begins the content overload process 
     * @return bool  
     */ 
    public function start() 
    { 
        /* 
        $categories = 10; 
        $depth = 1; 
        */ 
        
       $this->clean();
        $logger = true; 
        $depth = $this->depth; 
        $categories=$this->categories; 
        JLog::add('Depth:'.$this->depth, JLog::INFO);
        JLog::add('Number of categories:'.$this->categories, JLog::INFO);
        JLog::add('Number of articles:'.$this->articles, JLog::INFO);
        JLog::add('Calculating total number of categories', JLog::INFO); 
        //calculate total categoriess 
        JLog::add('Creating level map', JLog::INFO); 
        // create level map 
        $killme = $this->makeLevelmap($categories, $depth); 
        
       //print_r($killme);

        $tcat=count($killme);
        $tart=($tcat*$this->articles)+$tcat;
      //  print_r($tart);
        JLog::add('Total number of categories:'.$tcat, JLog::INFO);
        JLog::add('Total number of articles:'.$tart, JLog::INFO);
       JLog::add('Total number of Memu itmes:'.$tcat, JLog::INFO);
         $bartask = ($tcat*$this->articles)+($tcat*2)+2;
        //calculate total article 
       // echo 'Articles:'.($totalcats * $this->articles);
       // JLog::add('Total number of Articles:'.($totalcats * $this->articles), JLog::INFO);
             $this->a = new CliProgressBar();
             $this->a->initPBar($bartask, 13);
             $this->status = 1;
             $this->a->advancePBar($this->status, 'BulkContent start');
        //echo 'Creating level map for '.$totalcats."\n"; 
        //JLog::add('Creating level map', JLog::INFO); 
         
        $levelmap = array(); 
        $menumap = array(); 
        
        foreach($killme as $key) { 
            $levelmap[$key] = 0;             
        } 
        foreach($killme as $key) { 
            $menumap[$key] = 0;             
        } 
        $this->level=0; 
        $this->levelmap=$levelmap; 
        $this->menumap=$menumap; 
        // jexit(var_dump(count($this->levelmap)));
        JLog::add('Starting creating bulk data', JLog::INFO); 
        //echo 'Starting the engines!'."\n"; 
      
        //$this->startTimer(); 
        // $this->makeCategories(); 
         $this->createBulk();
        //return;  
        $this->a->finishPBar();
         $this->out("\n" .'Completed in '. round(microtime(true) - $this->_time, 3)."\n");   
        return ; 
    } 
    
    
     
   /**
	 * Generates categories and articles based on the hierarchical level map generated by
	 * the model
	 */
	private function createBulk()
	{
		$logger = true;

    $db = JFactory::getDBO(); 
		//$levelMap = $this->levelmap;
		//$db->transactionStart();
	 	 $this->createMenuType($key);		
	//	 JLog::add('Starting creating bulk menu', JLog::INFO); 
	//   $this->createMenu();
		foreach($this->levelmap as $key => $id) {
			$parts = explode('.',$key);
			$level = count($parts);
			$parent = ($level == 1) ? 1 : $this->levelmap[ implode('.',  array_slice($parts, 0, count($parts) - 1)) ];
			 // Wrap everything in a transaction
			  $db->transactionStart(); 
			JLog::add('Starting creating bulk category:'.$key, JLog::INFO); 
			 $id = $this->createCategory($level, $key, $parent);
			 $art = $this->createArticles($id,$level, $key);
			 		$this->levelmap[$key] = $id;   
			 //print_r($this->levelmap[$key]);
			 $menuparent = ($level == 1) ? 1 : $this->menumap[ implode('.',  array_slice($parts, 0, count($parts) - 1)) ];
		  $old=$this->createMenuitem($level, $key, $menuparent,$id); 	
		  $this->menumap[$key] = $old;  
		 // $menuid=$old;
		 //$menuid
	 
			//print_r($key. ' - '.$id);    	
	    JLog::add('Starting creating bulk articles', JLog::INFO); 
			
			JLog::add('Starting creating bulk menu', JLog::INFO); 
			//$menuid = $this->createMenu($level, $key, $parent);
			//$this->levelmap[$key] = $id;
			//JLog::add('Starting creating bulk menu items', JLog::INFO); 
			//$art = $this->createMenuItems($id,$level, $key);
		 //array_shift($this->levelmap); 
			  // Wrap everything in a transaction
		  $db->transactionCommit();
 		
      //$this->process();  
			//$this->status++;
      //$this->a->advancePBar($this->status, ' ' . $key);     
			//$levelMap[$key] = $id;
		

		}
   // $this->_rebuildMenu();
		JLog::add("Unsetting levelmap", JLog::DEBUG);
    unset($levelMap);
    unset($menuMap);
	  //$this->levelmap= $levelMap;
	}
  private function createMenuType() 
 {
 	 	 $my = JTable::getInstance('menuType');
 	 	 $my->reset();
			$my->menutype    = 'BulkMenuType';
			$my->title       = 'BulkMenuTitle';
			$my->description = 'BulkMenudescription';
			$my->check();
			$my->store();
    
 } 
 private function createMenu() 
{         
       $data = $this->getMenuData(); 
       $table = JTable::getInstance('menu');        			 	 
				// Bind the data.
				$rootId = $table->getRootId();
				$data['parent_id']=$rootId;
				 // Specify where to insert the new node.
        $table->setLocation($rootId, 'first-child');
		if (!$table->bind($data))
    {
			jexit('444');	 
		}
		if (!$table->store($data))
		{
			jexit($table->getError());		 
		}                        
    JLog::add("catxarticle:".$table->id, JLog::INFO); 
    return $table->id;   
}  
private function createMenuitem($level, $key, $parent,$cat_id) 
 	{   
        $data  = $this->getMenuData();  
        $link   = "index.php?option=com_content&view=category&layout=blog&id=".$cat_id;       
        $data['link'] = $link;
        $data['parent_id']=$parent;
        $data['title'] = $data['title'].$key;
        $data['alias'] = $data['alias'].$key;
        $data['level'] = $level;
        $data['type'] = 'component';
        
        
        $table = JTable::getInstance('menu');      
        // Specify where to insert the new node.
        $table->setLocation($parent, 'last-child'); 
        //$table= JTable::getInstance('Menu', 'MenusTable');                         				 
				// Bind the data.
		if (!$table->bind($data) || !$table->check() || !$table->store())	
		{	
		   	jexit($table->getError());		
		}
				//$table->rebuild();		
				//$table->rebuild($table->getRootId());
		 $this->status++;
     $this->a->advancePBar($this->status, ' ' . $data['title']); 		
				
		JLog::add("New menu item:".$data['title'], JLog::INFO); 
		//JLog::add("New menu itemid:".$table->id, JLog::INFO); 				 
		return $table->id;
}
   private function getMenuData()
	{ 
		$title = 'BulkMenu ';
		$alias = 'Bulkmenu-';
		//$params = JComponentHelper::getParams('com_menus');
		$link   = "";
		$params = '{"layout_type":"blog","show_category_heading_title_text":"","show_category_title":"","show_description":"","show_description_image":"","maxLevel":"","show_empty_categories":"","show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"","show_cat_tags":"","page_subheading":"","num_leading_articles":"","num_intro_articles":"","num_columns":"","num_links":"","multi_column_order":"","show_subcategory_content":"","orderby_pri":"","orderby_sec":"","order_date":"","show_pagination":"","show_pagination_results":"","show_title":"","link_titles":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}';
		$registry = new JRegistry;
		$registry->loadString($params);
	  $params = (string)$registry;
		$data = array(
		        'id'			  => 0,
            'menutype' => 'bulkmenutype',
			      'title'		 => $title,
			      'alias'		 => $alias,					
			      'link'		 => $link,
			      'type'			=> 'separator',
			      'published'		=> 1,
			      'component_id' => 22,
			      'parent_id'		=> 0,			     
			      'level'		=> 1,
			      'params'=> $params,
			      'language'		=> '*'
			     
			      );
    unset($registry);
		return $data;
	}	   
 
  private function createArticles($catid, $level,$key) 
 {
 	 	
    for($i = 1; $i<= $this->articles; $i ++) { 
   
      $this->createArticle($catid,$level, $i,$key); 
    } 
    //array_shift($levelmap); 
 }  
   
private function createArticle($cat_id = '1', $levelpath = '1', $currentArticle = 1, $key) 
    { 
        $data = $this->getArticleData($cat_id, $levelpath, $currentArticle, $key); 
        
         //jexit(var_dump($data));
        require_once JPATH_ADMINISTRATOR.'/components/com_content/models/article.php'; 
        $model = new ContentModelArticle(); 
        $result = $model->save($data); 
         

       /*
        if(!$result) { 
            jexit(var_dump($result)); 
        }
        */
        JLog::add("New article:".$data['title'], JLog::INFO); 
        //echo ($data['title'])."\n";
         $this->status++;
         $this->a->advancePBar($this->status, ' ' . $data['title']);     
    }   

    /** 
     * Create a single category and return its ID. If the category alias already 
     * exists, return the ID of that specific category alias. 
     *  
     * @param type $level 
     * @param type $levelpath 
     * @param type $parent_id 
     * @return type  
     */ 
    private function createCategory($level = 1, $levelpath = '1', $parent_id = 1) 
    { 
        $logger = true; 
        $title = 'BulkCategory '; 
        $alias = 'BulkCategory-'; 
        $title .= $levelpath; 
        $alias .= str_replace('.', '-', $levelpath); 
         
        $data = array( 
            'parent_id'        => $parent_id, 
            'level'            => $level, 
            'extension'        => 'com_content', 
            'title'            => $title, 
            'alias'            => $alias, 
            'description'    => '<p>Dummy category generated by BulkContentCli</p>', 
            'access'        => 1, 
            'params'        => array('target' => '', 'image' => ''), 
            'metadata'        => array('page_title' => '', 'author' => '', 'robots' => '', 'tags' => ''), 
            'metadesc'		=> '{}',
            'metakey'		=> '{}',
            'rules'		=> '{}',
            'hits'            => 0, 
            'language'        => '*', 
            'associations'    => array(),             
            //'tags'            => array(array(null)),             
            'published'        => 1 
        ); 
       /*
        jimport('joomla.observer.mapper');  
        jimport('cms.helper.tags');  
        jimport('cms.table.corecontent');  
        // Categories is in legacy for CMS 3 so we have to check there.  
        JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');  
        JLoader::Register('J', JPATH_PLATFORM . '/cms');   
        */
        $app = JFactory::getApplication('administrator'); 
        $basePath = JPATH_ADMINISTRATOR . '/components/com_categories'; 
         
        require_once $basePath . '/models/category.php'; 
        $config = array('table_path' => $basePath . '/tables'); 
        $model = new CategoriesModelCategory($config); 
        //echo 'after model'."\n"; 
        		
		 
        $result = $model->save($data); 
     
        $catid = $model->getState($model->getName().'.id'); 
            
            
               
            $this->status++;
            $this->a->advancePBar($this->status, ' ' . $data['title']);     
            
            //JLog::add("catxarticle:".$result->id, JLog::INFO); 
            return $catid;
        
      
    } 
     
     

    private function getArticleData($cat_id = '1', $levelpath = '1', $currentArticle = 1, $key)
	{
		$logger = true;
    $addPictures=true;
		$title = 'BulkContent ';
		$alias = 'BulkContent-';
		$title .= $key.'.'.$currentArticle;
		$alias .= $key.'.'.$currentArticle;

		$url = str_replace('/administrator', '', JURI::base(true));
		$url = rtrim($url,'/');
		$picture1 = $addPictures ? '<img src="'.$url.'/images/sampledata/fruitshop/apple.jpg" align="left" />' : '';
		$picture2 = $addPictures ? '<img src="'.$url.'/images/sampledata/parks/animals/180px_koala_ag1.jpg" align="right" />' : '';

		$introtext = <<<ENDTEXT
$picture1<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor velit blandit risus posuere sit amet sollicitudin enim dictum. Nunc a commodo magna. Cras mattis, purus et ornare dictum, velit mi dictum nisl, sed rutrum massa eros nec leo. Sed at nibh nec felis dignissim tristique. Mauris sed posuere velit. Curabitur vehicula dui libero. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis felis ac elit euismod vitae venenatis dui iaculis. Morbi nec ipsum sit amet erat scelerisque auctor ac eget elit. Phasellus ut mattis ipsum. In auctor lacinia porttitor. Aliquam erat volutpat. In hac habitasse platea dictumst. Pellentesque iaculis mi ut ante tempor pharetra.</p>
ENDTEXT;
		$fulltext = <<<ENDTEXT
<p>Alikon, aenean nisl velit, consectetur hendrerit ultricies eu, vehicula eu massa. Nunc elementum enim vitae tortor dignissim eget vulputate quam condimentum. Pellentesque ante felis, venenatis non malesuada a, sodales ut nunc. Morbi sed nulla <a href="http://www.joomla.org">sit amet erat cursus venenatis</a>. Nulla non diam id risus egestas varius vel nec nulla. Nullam pretium congue cursus. Nullam ultricies laoreet porttitor. Proin ultricies aliquam lacinia. Proin porta interdum enim eu ultrices. Maecenas id dui vitae nisl ultrices cursus quis et nisi. Sed rhoncus vestibulum eros vel faucibus. Nulla facilisi. Mauris lacus metus, aliquet eu iaculis vitae, tempor ac metus. Sed sem nunc, tempor vehicula condimentum at, ultricies a tellus. Proin dui velit, accumsan vitae facilisis mollis, tristique aliquet purus. Aliquam porta, orci nec feugiat semper, tortor nunc pulvinar lorem, sed ultricies mauris justo eu orci. Nullam urna leo, vehicula at interdum non, fringilla eget neque. Quisque dui metus, hendrerit ut porttitor non, Alikon dignissim eu ipsum.</p>
<p>Pellentesque ultricies adipiscing odio, <em>at interdum dui tempus ac</em>. Aliquam accumsan sem et tortor facilisis sagittis. Sed interdum erat in ante venenatis dignissim. Nulla neque metus, interdum a porta eu, lobortis quis libero. Maecenas condimentum lectus id nisi suscipit tempus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas at neque diam. Suspendisse arcu purus, eleifend accumsan imperdiet in, porta ac ante. Nam lobortis tincidunt erat, non ornare mauris vestibulum non. Vivamus feugiat nunc pretium mi pharetra dictum. Donec auctor tincidunt pulvinar. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
$picture2<p>Nunc feugiat porta faucibus. Nulla facilisi. Sed viverra laoreet mollis. Morbi ullamcorper lorem a lacus porttitor tristique. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean <strong>consequat</strong> tincidunt lacinia. Maecenas dictum volutpat lacus, nec malesuada ipsum congue sed. Sed nec neque erat. Donec eros urna, vulputate ac elementum sit amet, pharetra sit amet urna. Phasellus in lectus metus. Proin vitae diam augue, vel lacinia lectus. Ut tincidunt, dolor sit amet hendrerit gravida, augue mauris bibendum sapien, nec porta ipsum diam eget erat. In porta nisl eget odio placerat gravida commodo tortor feugiat. Donec in tincidunt dui. In in neque tellus. Phasellus velit lacus, viverra et sodales nec, porta in velit.</p>
<p>Etiam quis velit odio. Nunc dignissim enim vel enim blandit tempus. Integer pellentesque leo ac risus hendrerit sed consequat lacus elementum. Aenean placerat leo vitae nunc bibendum cursus. Ut ac dui diam. Vivamus massa tortor, consectetur at scelerisque eget, hendrerit et elit. Aliquam hendrerit quam posuere tellus sollicitudin sollicitudin. Ut eget lacinia metus. Curabitur vitae orci ac libero vestibulum commodo. Sed id nibh eu erat pretium tempus. Nullam suscipit fringilla tortor, ac pretium metus iaculis eu. Fusce pellentesque volutpat tortor, at interdum tortor blandit at. Morbi rhoncus euismod ultricies. Fusce sed massa at elit lobortis iaculis non id metus. Aliquam erat volutpat. Vivamus convallis mauris ut sapien tempus quis tempor nunc cursus. Quisque in lorem sem.</p>
ENDTEXT;
		jimport('joomla.utilities.date');
		$jNow = new JDate();

		if (version_compare(JVERSION, '3.0', 'ge')) {
			$now = $jNow->toSql();
		} else {
			$now = $jNow->toMysql();
		}

		$state  =    1;

		$data = array(
			'id'			=> 0,
			'title'			=> $title,
			'alias'			=> $alias,
			'introtext'		=> $introtext,
			'fulltext'		=> $fulltext,
			'state'			=> $state,
			'sectionid'		=> 0,
			'mask'			=> 0,
			'catid'			=> $cat_id,
			'created'		=> $now,
			'created_by_alias' => 'BulkContent',
			'attribs'		=> array(
				"show_title"=>"","link_titles"=>"","show_intro"=>"","show_category"=>"","link_category"=>"","show_parent_category"=>"","link_parent_category"=>"","show_author"=>"","link_author"=>"","show_create_date"=>"","show_modify_date"=>"","show_publish_date"=>"","show_item_navigation"=>"","show_icons"=>"","show_print_icon"=>"","show_email_icon"=>"","show_vote"=>"","show_hits"=>"","show_noauth"=>"","alternative_readmore"=>"","article_layout"=>""
			),
			'version'		=> 1,
			'parentid'		=> 0,
			'ordering'		=> 0,
			'metakey'		=> '',
			'metadesc'		=> '{}',
			'access'		=> 1,
			'hits'			=> 0,
			//'featured'		=> 0,
			'language'		=> '*',
			'state'			=> $state,
			'metadata'      => array(
				"tags"=>json_encode($alias)
			)
		);

		return $data;
	}
	
protected function help($option=null) {
        // Initialize variables.
        $help = array();
        // Build the help screen information.
        $help[] = 'Add bulk content data from CLI';
        $help[] = 'Usage: php bulk.php [options]';
        $help[] = '';
        $help[] = 'Option: -a [the number of articles]';
        $help[] = 'Example usage:php bulk.php -a 100';
        $help[] = 'Add 100 dummy articles';
        $help[] = '';
        $help[] = 'Option: -c [ the number of categories]';
        $help[] = 'Example usage:php bulk.php -a 100 -c 1';
        $help[] = 'Add 100 article in 1 category';        
        $help[] = '';
        $help[] = 'Option: -x';
        $help[] = 'Example usage:php bulk.php -x';
        $help[] = 'Delete all dummy bulk data';
        $help[] = '';
        $help[] = 'Option: -d';
        $help[] = 'Example usage:php bulk.php -a 10 -c 1 -d 2';
        $help[] = 'Add 10 article in each category';
        $help[] = '';
       // Print out the help information.
        if(!$option) {
          echo(implode("\n", $help));
        }else  {
        	   for($i = $option; $i < $option+4; $i++) {
        	   	echo $help[$i]."\n";
        	   }	
        }
    }
  
		private function _rebuildMenu()
	{
		/** @var JTableMenu $table */
		$table = JTable::getInstance('menu');
		$db = $table->getDbo();

		// We need to rebuild the menu based on its root item. By default this is the menu item with ID=1. However, some
		// crappy upgrade scripts enjoy screwing it up. Hey, ho, the workaround way I go.
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__menu'))
			->where($db->qn('id') . ' = ' . $db->q(1));
		$rootItemId = $db->setQuery($query)->loadResult();

		if (is_null($rootItemId))
		{
			// Guess what? The Problem has happened. Let's find the root node by title.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('title') . ' = ' . $db->q('Menu_Item_Root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// For crying out loud, did that idiot changed the title too?! Let's find it by alias.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('alias') . ' = ' . $db->q('root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Dude. Dude! Duuuuuuude! The alias is screwed up, too?! Find it by component ID.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('component_id') . ' = ' . $db->q('0'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Your site is more of a "shite" than a "site". Let's try with minimum lft value.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->order($db->qn('lft') . ' ASC');
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// I quit. Your site is broken.
			return false;
		}

		$table->rebuild($rootItemId);
	}  
    
} 

// Instantiate the application object, passing the class name to JCli::getInstance 
// and use chaining to execute the application. 
JApplicationCli::getInstance('BulkcontentCli')->execute();
