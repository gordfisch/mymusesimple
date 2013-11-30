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
$shopper 	= $this->shopper;
$order 		= $this->order;
$params 	= $this->params;
?>
     <table class="mymuse_cart" width="90%">
 
        <!-- Begin Order Summary -->
        <tr>
            <td class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_ORDER_SUMMARY') ?></b></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_NUMBER') ?>:</td>
            <td><?php echo sprintf("%08d", $order->id) ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_DATE') ?>:</td>
            <td><?php echo $order->created ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_STATUS') ?>:</td>
            <td><?php echo JText::_('MYMUSE_'.strtoupper($order->status_name)) ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_TOTAL') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->order_total)." ".$order->order_currency['currency_code'] ?></td>
        </tr>
    <?php if($order->reservation_fee > 0){ ?>
        <tr>
            <td><?php echo JText::_('MYMUSE_RESERVATION_FEE') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->reservation_fee)." ".$order->order_currency['currency_code'] ?></td>
        </tr>
        	<?php if($order->non_res_total > 0){ ?>
        	<tr>
            	<td><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</td>
            	<td><?php echo MyMuseHelper::printMoney($order->non_res_total)." ".$order->order_currency['currency_code'] ?></td>
        	</tr>
        	<tr>
            <td><?php echo JText::_('MYMUSE_PAID') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->pay_now)." ".$order->order_currency['currency_code'] ?></td>
        </tr>
    	<?php } ?>
    <?php } ?>
    <?php if(isset($this->plugin) && $this->plugin != ''){ ?>
        <tr>
            <td><?php echo JText::_('MYMUSE_PAID') ?>:</td>
            <td><?php echo $this->plugin?></td>
        </tr>
    <?php } ?>
	</table>
	<br />
        