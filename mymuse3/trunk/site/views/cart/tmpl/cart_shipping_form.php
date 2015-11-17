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
<div class="componentheading"><?php echo Jtext::_('MYMUSE_SHIPPING'); ?></div>


<?php if($this->order->need_shipping && count($this->shipMethods) == 0){ ?>
	<div class="message alert"><?php echo JText::_('MYMUSE_NO_SHIPPING_AVAILABLE'); ?></div>
<?php  }else{ ?>


<form action="<?php echo JRoute::_('index.php?option=com_mymuse&task=confirm&Itemid='.$this->Itemid); ?>" 
method="post" name="adminForm">


<?php if($this->order->need_shipping){ ?>

<table class="mymuse_cart">
	<thead>
	<tr>
		<th class="myselect" width="50"><b><?php echo JText::_('MYMUSE_SELECT'); ?></b></th>
		<th class="myshipmethod"><b><?php echo JText::_('MYMUSE_SHIP_METHOD'); ?></b></th>
		<th class="myprice"><b><?php echo JText::_('MYMUSE_COST'); ?></b></th>
	</tr>
	</thead>
<?php foreach($this->shipMethods as $sm){ ?>
	<tr>
		<td class="myselect"><input type="radio" name="shipmethodid" value="<?php echo $sm->id; ?>" /></td>
		<td class="myshipmethod"><?php echo $sm->ship_carrier_name." ".$sm->ship_method_name; ?></td>
		<td class="myprice"><?php echo MyMuseHelper::printMoney($sm->cost); ?></td>
	</tr>
<?php } ?>

	<tr>
		<td colspan="3">
		<div class="pull-left mymuse_button_left">
		<button class="button uk-button" type="submit" >
		<?php echo $this->button; ?>
		</button></div>
		</td>
	</tr>
</table>

<?php  }else{ ?>
	<input type="hidden" name="shipmethodid" value="60">
		<?php echo JText::_('MYMUSE_NO_SHIPPING_NEEDED')?> <input type="submit" class="button" name="confirm" value="<?php echo JText::_('Next'); ?>">
<?php }?>
</form>

<?php } ?>
