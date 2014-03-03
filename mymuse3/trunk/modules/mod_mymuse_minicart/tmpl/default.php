<?php // no direct access

defined('_JEXEC') or die('Restricted access'); 
if(isset($order->items) && count($order->items)){
$i = 0;
?>
<table class="mymuse_cart">
<thead>
<tr class="sectiontableheader mymuse_cart_top">
	<th class="sectiontableheader mymuse_cart_top"><?php echo JText::_('MYMUSE_ITEM') ?></th>
	<th class="sectiontableheader mymuse_cart_top"><?php echo JText::_('MYMUSE_CART_QUANTITY') ?></th>
	<th class="sectiontableheader mymuse_cart_top"><?php echo JText::_('MYMUSE_CART_SUBTOTAL') ?></th>
</tr>
</thead>
<?php foreach($order->items as $item) { ?>
	<tr>
		<td class="mytitle"><?php echo $item->title; ?></td>
		<td class="myquantity"><?php echo $item->quantity; ?></td>
		<td class="myprice"><?php echo MyMuseHelper::printMoney($item->product_item_subtotal); ?></td>
	</tr>
<?php }?>
		
		<?php 
		if($order->discount > 0.00){ 
			//for shopper group discount
			
			?>
			<tr>
		    	<td class="mobile-hide" colspan="2"><?php echo JText::_('MYMUSE_CART_ORIGINAL_SUBTOTAL'); ?>:</td>
		        <td class="myoriginalsubtotal"><?php echo MyMuseHelper::printMoney($order->discount + $order->order_subtotal); ?></td>
		    </tr>
		    
		    <tr>
		    	<td colspan="2"><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>:
		    	<?php echo $order->shopper_group_name; ?> <?php echo $order->shopper_group_discount; ?> %</td>
		        <td class="mydiscount"><?php echo MyMuseHelper::printMoney($order->discount); ?></td>

		    </tr>
		    
		    <tr>
		    	<td class="mobile-hide" colspan="2"><?php echo JText::_('MYMUSE_CART_NEW_SUBTOTAL'); ?>:</td>
		        <td class="mynewsubtotal"><?php echo MyMuseHelper::printMoney($order->order_subtotal); ?></td>

		    </tr>
		
		<?php } ?>
		
		
		
		<?php //COUPONS
		if($params->get("my_use_coupons") && @$order->coupon->id){ ?>
		    <tr>
		    	<td colspan="2"><?php echo JText::_('MYMUSE_YOUR_COUPON'); ?> : <?php echo $order->coupon->title ?></td>
		        <td class="mydiscount">-<?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> </td>

		    </tr>
		<?php } ?>
				
		<?php // SHIPPING
		if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) { ?>
		    <tr>
		    	<td colspan="2" align="right"><b><?php echo JText::_('MYMUSE_SHIPPING') ?>:</b></td>
		    	<td align="right"><?php echo MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    </td>

		<?php } ?>
		
		<?php // TAXES
		if(@$order->tax_array){
		    while(list($key,$val) = each($order->tax_array)){ ?>
		        <tr>
		        <td class="mytaxname" colspan="2"><?php echo $key; ?></td>
		        <td><?php echo MyMuseHelper::printMoney($val); ?></td>
				</tr>
		<?php  } 
		} ?>
		
		
<tr>
	<td class="mobile-hide" colspan="2"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?></b></td>
	<td class="mytotal"><?php echo MyMuseHelper::printMoney($order->order_total); ?></td>
</tr>
<tr>
	<td colspan="3" align="center"><a href="index.php?option=com_mymuse&task=checkout"><?php echo JText::_('MYMUSE_CHECKOUT') ?></a></td>
</tr>
</table>
<?php }else{ ?>
<?php echo JText::_('MYMUSE_YOUR_CART_IS_EMPTY');?>
<?php } ?>