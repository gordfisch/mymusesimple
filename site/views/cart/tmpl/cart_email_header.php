<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

$params 		= $this->params;
$shopper 		= $this->shopper;
$store 			= $this->store;

?>
<!DOCTYPE HTML>
<html lang="en-gb" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta charset="utf-8" />

<style>
/* cart */

.mymuse_cart ul{
	list-style-type:none;
	margin:0;
	padding:0;
}
table.mymuse_cart, div.mymuse_cart {
	background-color: #FFFFFF;
	margin: 1px;
	padding: 0px;
	border: 1px solid #CCCCCC;
	border-spacing: 0px;
    width: 100%;
}

table.mymuse_cart tr:nth-of-type(even) { 
  background: #eee; 
}

table.mymuse_cart_inner {
	background-color: #FFFFFF;
	margin: 0px;
	padding: 0px;
	border: 1px solid #CCCCCC;
	border-spacing: 0px;    
	width: 100%;

}

.mymuse_cart thead th{ 
    background-color: #ddd; 
    font-weight: bold; 
	border: 1px solid #999999; 
}


table.mymuse_cart td, table.mymuse_cart th, div.mymuse_cart {
	padding: 4px;
	vertical-align: middle;
	border: 1px solid #CCCCCC;
}
/* Only Phones */  
@media (max-width: 767px) { 
	td.mobile-hide{
		position: absolute;
		top: -9999px;
		left: -9999px;
	}
}

</style>
	
<title><?php echo $store->title; ?></title>

</head>
    <body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" >    

    <table class="mymuse_cart my-email-header">
      <tr class="mymuse_cart my-email-header">
        <td valign="top  class="mymusecart cart"><a href="'.JURI::root().'"><img align="left" src="<?php echo JURI::root().$params->get('store_thumb_image'); ?>" border="0"></a></td>
        <td>
        <table class="my-email-header mymuse_cart">
        <tr><td class="my-email-header mytitle"><?php echo $store->title; ?></td></tr>
        <tr><td class="my-email-header myaddress"><?php echo $params->get('address_1').' '.$params->get('address_2'); ?></td></tr>
        <tr><td class="my-email-header mycity"><?php echo $params->get('city').', '.$params->get('state'); ?></td></tr>
        <tr><td class="my-email-header mycountry"><?php echo $params->get('country').', '.$params->get('zip'); ?></td></tr>
        <tr><td class="my-email-header myphone">Phone: <?php echo $params->get('phone'); ?></td></tr>
        <tr><td class="my-email-header myemail">Email: <a href="mailto: <?php echo $params->get('contact_email'); ?>"><?php echo $params->get('contact_email'); ?></a></td></tr>
        <tr><td class="my-email-header myweb">Web: <a href="<?php echo JURI::root(); ?>"><?php echo JURI::root(); ?></a></td></tr>
        </table>
      </td>
      </tr>

      <tr valign="top" colspan="2"> 
        <td class="mmy-email-header email-msg"><?php echo $this->my_email_msg; ?></td>
      </tr>
    </table>

