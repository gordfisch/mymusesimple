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
$params 	= $this->params;
?>
<form action="index.php?Itemid=<?php echo $this->Itemid; ?>" method="post" name="adminForm">
<input type="hidden" name="option" value="com_mymuse">
<input type="hidden" name="task" value="confirm">
<div class="componentheading"><?php echo Jtext::_('MYMUSE_SHIPPING'); ?></div>

<?php if($this->order->need_shipping){ ?>

<table class="mymuse_cart" width="90%">
	<tr class="mymuse_cart_top">
		<td class="mymuse_cart_top" width="50"><b><?php echo JText::_('MYMUSE_CHOOSE'); ?></b></td>
		<td class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_SHIP_METHOD'); ?></b></td>
		<td class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_COST'); ?></b></td>
	</tr>
<?php foreach($this->shipMethods as $sm){ ?>
	<tr>
		<td><input type="radio" name="shipmethodid" value="<?php echo $sm->id; ?>" /></td>
		<td><?php echo $sm->ship_carrier_name." ".$sm->ship_method_name; ?></td>
		<td><?php echo MyMuseHelper::printMoney($sm->cost); ?></td>
	</tr>
<?php } ?>
	<tr>
		<td><input type="submit" class="button" name="confirm" value="<?php echo JText::_('MYMUSE_CONFIRM'); ?>"></td>
	</tr>
</table>

<?php  }else{ ?>
	<input type="hidden" name="shipmethodid" value="60">
		<?php echo JText::_('MYMUSE_NO_SHIPPING_NEEDED')?> <input type="submit" class="button" name="confirm" value="<?php echo JText::_('Next'); ?>">
<?php }?>
</form>