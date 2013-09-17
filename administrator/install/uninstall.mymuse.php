<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2012 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @website		http://www.mymuse.ca
 *
 * Original uninstall.php file
 * @package   Zoo Component
 * @author    YOOtheme http://www.yootheme.com
 * @copyright Copyright (C) 2007 - 2009 YOOtheme GmbH
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Modified first by Flexicontent
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Executes additional uninstallation processes
 *
 * @since 1.0
 */
// init vars
$error = false;
$extensions = array();
$db =& JFactory::getDBO();


//name => folder
$mymuseplugins = array(
		"audio_mp"				=>	"mymuse",
		"audio_html5"			=>	"mymuse",
		"payment_moneybookers"	=>	"mymuse",
		"payment_monsterpay"	=>	"mymuse",
		"mp3player_dewplayer"	=>	"mymuse",
		"mp3player_flowplayer"	=>	"mymuse",
		"payment_payfast"		=>	"mymuse",
		"paymentoffline"		=>	"mymuse",
		"paymentpesapal"		=>	"mymuse",
		"paymentpaypal"			=>	"mymuse",
		"preorder"				=>	"mymuse",
		"shipping_price"		=>	"mymuse",
		"shipping_standard"		=>	"mymuse",
		"shoppergroupview"		=>	"mymuse",
		"vidplayer_dewplayer"	=>	"mymuse",
		"vidplayer_flowplayer"	=>	"mymuse",
		"vidplayer_html5"		=>	"mymuse",
		"vote"					=>	"mymuse",
		"searchmymuse"			=>	"search",
		"mymuse"				=>	"user",
		"redirectonlogin"		=>	"user",
		"mymusenoreg"		=>	"user",
		"mod_mymuse_latest"		=> ""
);
// additional extensions
$add_array =& $this->manifest->xpath('plugins');
$add = NULL;
if(count($add_array)) $add = $add_array[0];
if (is_a($add, 'JXMLElement') && count($add->children())) {
	$exts =& $add->children();
	foreach ($exts as $ext) {

		// set query
		switch ($ext->name()) {
			case 'plugin':
				$attribute_name = $ext->getAttribute('name');
				if(array_key_exists($attribute_name, $mymuseplugins)) {
					$query = 'SELECT * FROM #__extensions WHERE type='.$db->Quote($ext->name()).' 
					AND element='.$db->Quote($ext->getAttribute('name'))." 
					AND folder='".$mymuseplugins[$attribute_name]."';";
					// query extension id and client id
					//echo "$query <br />";
					$db->setQuery($query);
					$res = $db->loadObject();

					$extensions[] = array(
							'name' => $ext->data(),
							'type' => $ext->name(),
							'id' => isset($res->extension_id) ? $res->extension_id : 0,
							'client_id' => isset($res->client_id) ? $res->client_id : 0,
							'installer' => new JInstaller(),
							'status' => false);
				}
				break;
			case 'module':
				$query = 'SELECT * FROM #__extensions WHERE type='.$db->Quote($ext->name()).' AND element='.$db->Quote($ext->getAttribute('name'));
				// query extension id and client id
				$db->setQuery($query);
				$res = $db->loadObject();
				$extensions[] = array(
						'name' => $ext->data(),
						'type' => $ext->name(),
						'id' => isset($res->extension_id) ? $res->extension_id : 0,
						'client_id' => isset($res->client_id) ? $res->client_id : 0,
						'installer' => new JInstaller(),
						'status' => false);
				break;
		}
	}
}

// uninstall additional extensions
for ($i = 0; $i < count($extensions); $i++) {
	$extension =& $extensions[$i];

	if ($extension['id'] > 0 && $extension['installer']->uninstall($extension['type'], $extension['id'], $extension['client_id'])) {
		$extension['status'] = true;
	}
}



