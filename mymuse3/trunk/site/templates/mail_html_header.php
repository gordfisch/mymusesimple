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
$my_email_msg = preg_replace("/\\n/","<br />",$params->get('my_email_msg'));
	$header =  '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" >
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />


<link rel="stylesheet" href="'.JURI::root().'templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="'.JURI::root().'components/com_mymuse/assets/css/mymuse.css" type="text/css" />

	
    <title>'.$store->title.'</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
    <body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" >    

    <table width=800  border=0 cellspacing=0 cellpadding=2>
      <tr>
        <td valign="top><a href="'.JURI::root().'"><img align="left" src="'.JURI::root().$params->get('store_thumb_image').'" border="0"></a></td>
        <td>
        <table border="0" cellpadding="0" cellspacing="2" width="100%">
        <tr><td>'.$store->title.'</td></tr>
        <tr><td>'.$params->get('address_1').' '.$params->get('address_2').'</td></tr>
        <tr><td>'.$params->get('city').', '.$params->get('state').'</td></tr>
        <tr><td>'.$params->get('country').', '.$params->get('zip').'</td></tr>
        <tr><td>Phone: '.$params->get('phone').' <br />
        Email: <a href="mailto: '.$params->get('contact_email').'">'.$params->get('contact_email').'</a><br />
        Web: <a href="'.JURI::root().'">'.JURI::root().'</a><br />
        
        </td></tr>
        </table>
      </td>
      </tr>

      <tr valign="top" colspan="2"> 
        <td>'.$my_email_msg.'</td>
      </tr>
    </table>
	<br />
	<br />
'; 


	$footer = '
    </body>
    </html>

';
