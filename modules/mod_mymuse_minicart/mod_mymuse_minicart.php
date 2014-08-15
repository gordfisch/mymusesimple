<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
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

// add css
$Doc = JFactory::getDocument();
$Doc->addStyleSheet( MYMUSE_PATH.'assets/css/mymuse.css' );
include(MYMUSE_PATH.'assets'.DS.'css'.DS.'mobile_css.php');
$Doc->addStyleDeclaration($mobile_style);

$MyMuseCart 	=& MyMuse::getObject('cart','helpers');
$cart 			=& $MyMuseCart->cart;
$order 			= $MyMuseCart->buildOrder( 0 );
$params			= MyMuseHelper::getParams();
$Itemid			= JRequest::get("Itemid");
$checkoutUrl 	= 'index.php?option=com_mymuse&task=checkout&Itemid='.$Itemid;
require(JModuleHelper::getLayoutPath('mod_mymuse_minicart'));