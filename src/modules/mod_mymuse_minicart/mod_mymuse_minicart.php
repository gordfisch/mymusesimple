<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
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





$MyMuseCart 	=& MyMuse::getObject('cart','helpers');
$cart 			=& $MyMuseCart->cart;
$order 			= $MyMuseCart->buildOrder( 0 );
$params			= MyMuseHelper::getParams();
$jinput 		= JFactory::getApplication()->input;
$Itemid			= $jinput->get("Itemid", "", "string");
$checkoutUrl 	= 'index.php?option=com_mymuse&task=checkout&Itemid='.$Itemid;

if(!$params->get('my_disable_css',0)){
	// add css
	$Doc = JFactory::getDocument();
	$Doc->addStyleSheet( 'components/com_mymuse/assets/css/mymuse.css' );
	if(!include_once(MYMUSE_PATH.'assets'.DS.'css'.DS.'mobile_css.php')){  //include_once returns TRUE if the file is already included
		$Doc->addStyleDeclaration($mobile_style);
	}
}

require(JModuleHelper::getLayoutPath('mod_mymuse_minicart'));