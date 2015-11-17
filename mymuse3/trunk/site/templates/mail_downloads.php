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

$download_header = '<h3>'.JText::_('MYMUSE_DOWNLOADS_IN_THIS_ORDER').'</h3>

<table class="contentpaneopen">
<tr>
		<td>
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
	$link = JURI::root().JRoute::_("index.php?option=com_mymuse&task=accdownloads&id=".$order->order_number."&Itemid=".$Itemid);
}else{
	$link = JURI::root().JRoute::_("index.php?option=com_mymuse&task=downloads&id=".$order->order_number."&Itemid=".$Itemid);
}
$download_header .= '
		</ul>
		</td>
	</tr>
</table>
<h3>'.JText::_('MYMUSE_DOWNLOAD_LINK_PLEASE_CLICK').'</h3>
<a href="'.$link.'">'.$link.'</a>
<table class="contentpaneopen">
	<tr>
		<td></td>
	</tr>
	<tr>
		<td></td>
	</tr>
</table>
<br />
<br />
';

?>