<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');


// initialize
require_once( JPATH_COMPONENT.DS.'mymuse.class.php');
ini_set('memory_limit',"128M");
ini_set('max_execution_time',"60");

//load com_user language for logins and registration
$lang =& JFactory::getLanguage();
$extension = 'com_user';
$base_dir = JPATH_SITE;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

// add css and javascript
$Doc =& JFactory::getDocument();
$Doc->addStyleSheet( 'components/com_mymuse/assets/css/mymuse.css' );
$Doc->addScript( 'components/com_mymuse/assets/javascript/mymuse.js' );

JPluginHelper::importPlugin('mymuse');

// return URL
$return 	= JRequest::getVar('return','');
if(!$return){
	$return = MyMuseHelper::returnURL();
	JRequest::setVar('return',$return);
}

//task and controller
$task 		= JRequest::getVar('task', null, 'default', 'cmd');
if($task == 'login'){
	$controller = '';
}
$controller = JRequest::getWord('controller','');

// Execute the task.
$controller	= JController::getInstance('MyMuse');
$controller->execute($task);

//save the cart in the session
$session = &JFactory::getSession();
$MyMuseCart = MyMuse::getObject('cart','helpers');
$session->set("cart",$MyMuseCart->cart);

$controller->redirect();
