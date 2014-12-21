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

     // Enable plugin
     $db->setQuery("UPDATE #__extensions SET enabled=1 WHERE element='searchmymuse' AND type='plugin'");
     $db->execute();
     
     echo '<p>'. JText::_('MYMUSE_PLUGIN_ENABLED') .'</p>';    
  } 
}
?>