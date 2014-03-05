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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$params 	= $this->params;
?>
<div class="componentheading"><?php echo JText::_('MYMUSE_YOUR_ORDER_HISTORY'); ?></div>

<table class="mymuse_cart">
	<thead>
	<tr>
		<th class="myorderid" width="10%"><?php echo JText::_('MYMUSE_ORDER_ID'); ?></th>
		<th class="mydate" width="50%"><?php echo JText::_('MYMUSE_DATE'); ?></th>
		<th class="myorderstatus" width="40%"><?php echo JText::_('MYMUSE_ORDER_STATUS'); ?></th>
		<th class="mytotal" width="40%" align="right"><?php echo JText::_('MYMUSE_ORDER_TOTAL'); ?></th>
		
	</tr>
	</thead>
	<?php  foreach($this->orders as $order){ ?>
	<tr>
		<td class="myorderid"><a href="<?php echo $order->url; ?>"><?php echo $order->id; ?></a></td>
		<td class="mydate" ><?php echo $order->created; ?></td>
		<td class="myorderstatus"><?php echo JText::_('MYMUSE_'.strtoupper(MyMuseHelper::getStatusName($order->order_status))) ?></td>
		<td class="mytotal"><?php echo MyMuseHelper::printMoney($order->order_total); ?></td>
		
	</tr>
	<?php } ?>
</table>
