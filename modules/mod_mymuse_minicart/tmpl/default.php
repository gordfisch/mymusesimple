<?php // no direct access

defined('_JEXEC') or die('Restricted access'); 
if(isset($order->items) && count($order->items)){
$i = 0;
?>
<table width="100%" class="mymuse_minicart">
<tr class="sectiontableheader mymuse_cart_top">
	<th class="sectiontableheader mymuse_cart_top"><?php echo JText::_('MYMUSE_ITEM') ?></th>
	<th class="sectiontableheader mymuse_cart_top"><?php echo JText::_('MYMUSE_CART_QUANTITY') ?></th>
	<th class="sectiontableheader mymuse_cart_top"><?php echo JText::_('MYMUSE_CART_SUBTOTAL') ?></th>
</tr>
<?php foreach($order->items as $item) {

	if ($i++ % 2){
		$class = "row1";
	}else{
		$class = "row2";
	}
	?>
	<tr class="<?php echo $class ?>">
		<td><?php echo $item->title; ?></td>
		<td><?php echo $item->quantity; ?></td>
		<td align="right"><?php echo MyMuseHelper::printMoney($item->product_item_subtotal); ?></td>
	</tr>
<?php }?>
		
		<?php 
		if($order->discount > 0.00){ 
			//for shopper group discount
			
			?>
			<tr>
		    	<td colspan="2" align="right"><?php echo JText::_('MYMUSE_CART_ORIGINAL_SUBTOTAL'); ?>:</td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->discount + $order->order_subtotal); ?></td>
		    </tr>
		    
		    <tr>
		    	<td colspan="2" align="right"><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>:
		    	<?php echo $order->shopper_group_name; ?> <?php echo $order->shopper_group_discount; ?> %</td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->discount); ?></td>

		    </tr>
		    
		    <tr>
		    	<td colspan="2" align="right"><?php echo JText::_('MYMUSE_CART_NEW_SUBTOTAL'); ?>:</td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->order_subtotal); ?></td>

		    </tr>
		
		<?php } ?>
		
		
		
		<?php //COUPONS
		if($params->get("my_use_coupons") && @$order->coupon->id){ ?>
		    <tr>
		    	<td olspan="2" align="right"><?php echo JText::_('MYMUSE_YOUR_COUPON'); ?> : <?php echo $order->coupon->title ?></td>
		        <td  align="right">-<?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> </td>

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
		        <td colspan="2" align="right"><?php echo $key; ?></td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($val); ?></td>

		<?php  } 
		} ?>
		
		
<tr>
	<td colspan="2" align="right"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?></b></td>
	<td align="right"><?php echo MyMuseHelper::printMoney($order->order_total); ?></td>
</tr>
<tr>
	<td colspan="3" align="center"><a href="index.php?option=com_mymuse&task=checkout"><?php echo JText::_('MYMUSE_CHECKOUT') ?></a></td>
</tr>
</table>
<?php }else{ ?>
<?php echo JText::_('MYMUSE_YOUR_CART_IS_EMPTY');?>
<?php } ?>