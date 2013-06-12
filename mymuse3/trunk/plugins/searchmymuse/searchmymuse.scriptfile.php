<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2012 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of searchmymuse plugin
 */
class plgSearchSearchmymuseInstallerScript
{ 
  function install($parent) { 
     // activate the plugin
	 $db = JFactory::getDbo();
     $tableExtensions = $db->nameQuote("#__extensions");
     $columnElement   = $db->nameQuote("element");
     $columnType      = $db->nameQuote("type");
     $columnEnabled   = $db->nameQuote("enabled");
     
     // Enable plugin
     $db->setQuery("UPDATE $tableExtensions SET $columnEnabled=1 WHERE $columnElement='searchmymuse' AND $columnType='plugin'");
     $db->query();
     
     echo '<p>'. JText::_('MYMUSE_PLUGIN_ENABLED') .'</p>';    
  } 
}
?>