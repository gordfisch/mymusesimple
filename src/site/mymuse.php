<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');


// initialize
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once( JPATH_COMPONENT.DS.'mymuse.class.php');
ini_set('memory_limit',"512M");
ini_set('max_execution_time',"120");
ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

//include any custom code
if(file_exists(JPATH_COMPONENT.DS."custom.php")){
	require_once( JPATH_COMPONENT.DS.'custom.php');
}

// add css and javascript

JHtml::_('jquery.framework');
$params = MyMuseHelper::getParams();
$lang 	= JFactory::getLanguage();
$rtl 	= $lang->get('rtl');

if(!$params->get('my_disable_css',0)){
	$Doc = JFactory::getDocument();
	$Doc->addStyleSheet( 'components/com_mymuse/assets/css/mymuse.css' );
	if(include_once(JPATH_COMPONENT.DS.'assets'.DS.'css'.DS.'mobile_css.php')){
		$Doc->addStyleDeclaration($mobile_style);
	}
	if($rtl){
		$Doc->addStyleSheet( 'components/com_mymuse/assets/css/mymuse_rtl.css' );
	}
	$Doc->addScript( 'components/com_mymuse/assets/javascript/mymuse.js' );
}

JPluginHelper::importPlugin('mymuse');
$jinput 	= JFactory::getApplication()->input;
//print_pre($jinput); exit;
// return URL
$return 	= $jinput->get('return','');
if(!$return){
	$return = MyMuseHelper::returnURL();
	$jinput->set('return',$return);
}

//get controller
$controller	= JControllerLegacy::getInstance('Mymuse');

// Execute the task.
$controller->execute(JFactory::getApplication()->input->get('task', 'display'));

//save the cart in the session
$session = JFactory::getSession();
$MyMuseCart = MyMuse::getObject('cart','helpers');
$session->set("cart",$MyMuseCart->cart);

//redirect if neeeded
$controller->redirect();
