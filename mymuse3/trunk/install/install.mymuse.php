<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca

 * Modified from install.php file
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * Original install.php file
 * @package   Zoo Component
 * @author    YOOtheme http://www.yootheme.com
 * @copyright Copyright (C) 2007 - 2009 YOOtheme GmbH
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only

*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );


$VERSION = $this->manifest->version;
list($MAJOR, $MINOR, $RELEASE) = explode(".", $VERSION);

$thisextension = strtolower( "com_mymuse" );
$thisextensionname = substr ( $thisextension, 4 );
$status = new JObject;
/**
 * Executes additional installation processes
 *
 * @since 1.0
 */


// first check if PHP5 is running
if (version_compare(PHP_VERSION, '5.0.0', '<')) {

	echo '<div class="fc-error">';
	echo 'Please upgrade PHP above version 5.0.0<br />';
	echo '</div>';
	return false;
}


