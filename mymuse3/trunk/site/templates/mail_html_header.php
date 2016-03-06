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
$my_email_msg = isset($my_email_msg)? $my_email_msg : '';

	$header =  '
<!DOCTYPE HTML>
<html lang="en-gb" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta charset="utf-8" />

<link rel="stylesheet" href="'.JURI::root().'templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="'.JURI::root().'components/com_mymuse/assets/css/mymuse.css" type="text/css" />

	
<title>'.$store->title.'</title>

</head>
    <body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" >    

    <table class="mymusecart cart">
      <tr class="mymusecart cart">
        <td valign="top  class="mymusecart cart"><a href="'.JURI::root().'"><img align="left" src="'.JURI::root().$params->get('store_thumb_image').'" border="0"></a></td>
        <td>
        <table border="0" cellpadding="0" cellspacing="2" width="100%">
        <tr><td>'.$store->title.'</td></tr>
        <tr><td>'.$params->get('address_1').' '.$params->get('address_2').'</td></tr>
        <tr><td>'.$params->get('city').', '.$params->get('state').'</td></tr>
        <tr><td>'.$params->get('country').', '.$params->get('zip').'</td></tr>
        <tr><td>Phone: '.$params->get('phone').' </td></tr>
        <tr><td>Email: <a href="mailto: '.$params->get('contact_email').'">'.$params->get('contact_email').'</a></td></tr>
        <tr><td>Web: <a href="'.JURI::root().'">'.JURI::root().'</a></td></tr>
        </table>
      </td>
      </tr>
    </table>
	<br />
	<br />
'; 


	$footer = '
	<table class="mymusecart cart">
      <tr class="mymusecart cart">
			<td>'.$my_email_msg.'</td>
      </tr>
	</table>
    </body>
    </html>

';
