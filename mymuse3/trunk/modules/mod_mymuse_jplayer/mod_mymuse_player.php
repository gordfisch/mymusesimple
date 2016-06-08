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

$document = JFactory::getDocument();
$this->direction = $document->direction;
$site_url = preg_replace("#administrator/#","",JURI::base());

if($this->direction == "rtl"){
	$this->css_path = $site_url.'plugins/mymuse/audio_html5/skin/jplayer.blue.monday.rtl.css';
}else{
	$this->css_path = $site_url.'plugins/mymuse/audio_html5/skin/jplayer.blue.monday.css';
}


require(JModuleHelper::getLayoutPath('mod_mymuse_jplayer'));