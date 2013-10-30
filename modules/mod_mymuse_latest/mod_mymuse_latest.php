<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_ADMIN_PATH')){
	define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
}
if(!defined('MYMUSE_PATH')){
	define('MYMUSE_PATH',JPATH_SITE.DS."components".DS."com_mymuse".DS);
}
require_once( MYMUSE_PATH.'mymuse.class.php');
require_once( MYMUSE_PATH.'helpers'.DS.'route.php');
require_once (dirname(__FILE__).DS.'helper.php');


$doc =& JFactory::getDocument();
$doc->addStyleSheet( 'modules/mod_mymuse_latest/mod_mymuse_latest_style.css' );

		
$params->def('maximum_shown', 5);
$params->def('type_shown', 'tracks');
$params->def('module_number', 1);

$list	= modMyMuseLatestHelper::getResults($params);
require(JModuleHelper::getLayoutPath('mod_mymuse_latest'));