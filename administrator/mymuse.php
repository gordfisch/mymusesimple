<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


// no direct access
defined('_JEXEC') or die;
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'liveupdate'.DS.'liveupdate.php';
if(JRequest::getCmd('view','') == 'liveupdate') {
	LiveUpdate::handleRequest();
	return;
}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_mymuse')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
	
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_mymuse/assets/css/mymuse.css');

// require the helper
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'mymuse.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'permission.php');


//initialize
$params = MyMuseHelper::getParams();

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Mymuse');
$controller->execute(JFactory::getApplication()->input->get('task'));

$controller->redirect();
