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
$input = JFactory::getApplication()->input;

// require the helper
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'mymuse.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'permission.php');
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'liveupdate'.DS.'liveupdate.php';
//initialize
$params = MyMuseHelper::getParams();
//print_pre($input); exit;
if($input->get('view','') == 'liveupdate') {
	
	LiveUpdate::handleRequest();
	return;
}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_mymuse'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_mymuse/assets/css/mymuse.css');


// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Mymuse');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
