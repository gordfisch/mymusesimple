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

$download_header = '<h3 class="cart-header">'.JText::_('MYMUSE_DOWNLOADS_IN_THIS_ORDER').'</h3>

<table class="mymuse_cart cart">
<tr>
		<td class="mymuse_cart cart">
		<ul>
';
foreach($order->items as $item){ 
	if($item->file_name != ""){
	$download_header .= '
	<li>'.$item->product_name.'</li>
	';
 	}
} 
if($params->get('my_registration') == "no_reg"){
	$link = JRoute::_("index.php?option=com_mymuse&view=store&task=accdownloads&id=".$order->order_number."&Itemid=".$Itemid);
}else{
	$link = JRoute::_("index.php?option=com_mymuse&view=store&task=downloads&id=".$order->order_number."&Itemid=".$Itemid);
}

$link = JURI::root().ltrim($link,'/');

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