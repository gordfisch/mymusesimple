<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

if($params->get('my_registration') == "no_reg" || $order->user->username == "buyer"){
	$link = "index.php?option=com_mymuse&view=store&task=accdownloads&id=".$order->order_number;
}else{
	$link = "index.php?option=com_mymuse&view=store&task=downloads&id=".$order->order_number;
}

$link = JURI::root().$link;
if($params->get('my_default_itemid','')){
	$link .= "&Itemid=".$params->get('my_default_itemid');	
}

//for nexgen
/**
if($params->get('my_registration') == "no_reg"){
	$link = JURI::root()."store/accdownloads?id=".$order->order_number;
}else{
	$link = JURI::root()."store/downloads?id=".$order->order_number;
}
//if($params->get('my_default_itemid','')){top_menu_item
if($params->get('my_default_itemid','')){
	$link .= "&Itemid=".$params->get('my_default_itemid');
}
*/
$download_header = '<h3 class="cart-header">'.JText::_('MYMUSE_DOWNLOADS_IN_THIS_ORDER').'</h3>

<table class="mymuse_cart cart">
<tr>
		<td class="mymuse_cart cart">
		<ul>
';
foreach($order->items as $item){ 

	if($item->file_name != ""){
		$download_header .= '<li>';
		if(isset($item->product->parent->title)){
			$download_header .= $item->product->parent->title;
		}
		$download_header .= ' '.$item->product_name.'</li>';
 	}
} 

$download_header .= '
		</ul>
		</td>
	</tr>
</table>
<h3 class="cart-header">'.JText::_('MYMUSE_DOWNLOAD_LINK_PLEASE_CLICK').'</h3>
<a href="'.$link.'">'.$link.'</a>

<br />
<br />
';

?>