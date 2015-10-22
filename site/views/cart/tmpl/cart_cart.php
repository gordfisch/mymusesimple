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

$order		= $this->order;
$order_item = $order->items;
$Itemid 	= @$this->Itemid;
$user 		= $this->user;
$params 	= $this->params;
$task		= $this->task;

?>

		<?php if($order->do_html){ ?>
			<form action="index.php?Itemid=<?php echo $Itemid; ?>" method="post" name="adminForm">
		<?php } ?>

		<!-- start of basket -->

		<h2><?php echo JText::_('MYMUSE_SHOPPING_CART'); ?></h2>
		<?php if($params->get("my_use_coupons") && (preg_match("/shipping|addtocart|updatecart|cartdelete|showcart|checkout/",$task) || $task == '') && !isset($order->coupon->id) ){ ?>
		    <div class="coupon"><a class="titles" href="index.php?option=com_mymuse&task=coupon&Itemid=<?php echo $Itemid ?>"><b><?php echo JText::_('MYMUSE_ENTER_A_COUPON'); ?></b></a>
		    </div>
		<?php } ?>
	<table class="mymuse_cart">
		<thead>
		<tr class="mymuse_cart">
		<th class="mytitle"><b><?php echo JText::_('MYMUSE_TITLE'); ?></b></th>
	<?php if($params->get("my_show_sku")){ ?>
		<th class="mysku"><b><?php echo JText::_('MYMUSE_CART_SKU'); ?></b></th>
	<?php } ?>
		<th class="myprice"><b><?php echo JText::_('MYMUSE_CART_PRICE'); ?></b></th>
		<th class="myquantity"><b><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></b></th>
		<th class="mysubtotal"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></th>
		<?php if(@$order->do_html){ ?>
		    <th class="myaction"><b><?php echo JText::_('MYMUSE_CART_ACTION'); ?></b>&nbsp;<?php echo $order->update_form; ?></th>		    
		<?php } ?>
		</tr>
		</thead>
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order->items); $i++) { 
		?>
		
		    <tr>
		        <td class="mytitle">
		        <?php if(isset($order_item[$i]->category_name) && $params->get('mymuse_show_category')){ ?>
		        	 <?php echo $order_item[$i]->category_name; ?> : 
		        <?php } ?>
		        
		        <?php if(isset($order_item[$i]->parent->title)){ ?>
		        	 <?php echo $order_item[$i]->parent->title; ?> :
		        <?php } ?>
		        <?php echo $order_item[$i]->title; ?>
		        
		        <?php if(isset($order_item[$i]->file_name)){ ?>
		        	 :<br /><?php echo $order_item[$i]->file_name; ?> 
		        <?php } ?>
		        
		        </td>
		    <?php if($params->get("my_show_sku")){ ?>
		        <td class="mysku"><?php echo $order_item[$i]->product_sku; ?></td>
		    <?php } ?>
		        <td class="myprice"> <?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_price); ?></td>
		        
		   <?php if($order->do_html && $order_item[$i]->quantity){ ?>
		        <td class="myquantity"> <input class="inputbox" type="text" size="4" maxlength="4" name="quantity[<?php echo $order_item[$i]->id ?>]"
		        value="<?php echo $order_item[$i]->quantity;?>"   />&nbsp;</td>
		        
		    <?php }else{ ?>
		        <td class="myquantity"><?php echo $order_item[$i]->quantity; 
		        if($params->get('my_add_stock_zero',0) && $order_item[$i]->quantity == 0) {
		        	echo " ".JText::_('MYMUSE_BACKORDERED');
		        }
		        ?></td>
		    <?php } ?>
		        
		        <td class="mysubtotal"><?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_subtotal); ?></td>
		        
		    <?php if($order->do_html){ ?>
		        <td class="myaction"><a href="<?php echo $order_item[$i]->delete_url; ?>"><?php echo JText::_('MYMUSE_DELETE'); ?></a></td>
		    <?php } ?>
		       </tr>
		<?php } ?>
		
		<?php if($order->discount > 0.00 || ($params->get("my_use_coupons") && @$order->coupon->id)
		|| count($order->tax_array) > 0){ ?>
		<!--  original subtotal -->
			<tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></td>
		        <td class="myoriginalsubtotal" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->subtotal_before_discount); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		
		<?php //for shopper group discount 
		if($order->shopper_group_discount > 0.00){ ?>
		    <tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?></b>
		    	<?php echo $order->shopper_group_name; ?> <?php echo $user->shopper_group->discount; ?> %</td>
		        <td class="myshoppergroupdiscount" colspan="<?php echo $order->colspan2; ?>">(<?php echo MyMuseHelper::printMoney($order->shopper_group_discount); ?>)</td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		
		<?php //for regular discount
		if($order->discount > 0.00){ ?>
		    <tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo JText::_('MYMUSE_DISCOUNT'); ?></b>
		    	</td>
		        <td class="mydiscount" colspan="<?php echo $order->colspan2; ?>">- <?php echo MyMuseHelper::printMoney($order->discount); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
				

		<?php //COUPONS
		if($params->get("my_use_coupons") && @$order->coupon->id){ ?>
		    <tr>
		    	<td class="mobile-hide"><b><?php echo JText::_('MYMUSE_YOUR_COUPON'); ?></b> <?php echo $order->coupon->title ?></td>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan -1; ?>">&nbsp;</td>
		        <td class="mycoupon" colspan="<?php echo $order->colspan2; ?>">- <?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> </td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
				

		
		<?php // TAXES
		if(isset($order->tax_array)  && count($order->tax_array)){
		    foreach($order->tax_array as $key=>$val){
		    	$pre_key = preg_replace("/_/","", $key);
		    	$key = preg_replace("/_/"," ", $key);
		    	?>
		        <tr>
		        	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo $key; ?></b></td>
		        	<td class="mytax <?php echo strtolower($pre_key); ?>" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        	<?php if(@$order->do_html){ ?>
		        	<td class="mobile-hide">&nbsp;</td>
		        	<?php  } ?>
		        </tr>
		<?php  } 
		} ?>
		
		<?php // SHIPPING
		if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) { ?>
		    <tr>
		    <td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo JText::_('MYMUSE_SHIPPING') ?></b>
		    <?php echo $order->order_shipping->ship_carrier_name ?> <?php echo $order->order_shipping->ship_method_name ?></td>
		    <td class="myshipping" colspan="<?php echo $order->colspan2; ?>">
		    <?php echo MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		<tr>
		    <td class="textbox2 mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</b></td>
		    <td class="textbox2 mytotal" colspan="<?php echo $order->colspan2; ?>"><b><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    <?php echo $this->currency['currency_code']; ?></b></td>
		    <?php if($order->do_html){ ?>
		        <td class="mobile-hide" >&nbsp;</td>
		    <?php  } ?>
		
		
		<?php if($order->do_html){ ?>
		<tr>
		    <td class="" colspan="<?php echo $order->colspan; ?>">
		    <div class="pull-right myupdate"><button class="button uk-button " 
				type="submit" >
				<?php echo JText::_('MYMUSE_UPDATE_CART'); ?></button></div>
		    </td>
		    <td colspan="<?php echo $order->colspan2; ?>"> </td>
		    <td class="mobile-hide">&nbsp;</td>
		</tr>
		    
		
		<?php } ?>
		
		<?php  if($order->reservation_fee > 0){ ?>
		<tr>
		    <td class="mobile-hide" colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_RESERVATION_FEE') ?>:</b></td>
		    <td class="myreservationfee" colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		</tr>
			<?php  if($order->non_res_total > 0){ ?>
			<tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</b></td>
		    	<td class="myothercharges" colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_PAYNOW') ?>:</b></td>
		    	<td class="mypaynow" colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->must_pay_now); ?></b>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<?php } ?>
		
		<?php } ?>
		
		<?php if($order->do_html){ ?>
		</form>
		<?php } ?>
	</table>

		<?php 
		if(isset($order->show_checkout) && $order->show_checkout){ 
		    // add the checkout link
		?> 
		<div class="mymuse-wrap">
	  			
				<div class="pull-left mymuse-button-left"><button class="button uk-button" 
				type="button" 
				onclick="location.href='<?php echo JURI::root(); ?>index.php?option=com_mymuse&task=checkout&Itemid=<?php echo $Itemid; ?>'">
				<?php echo JText::_('MYMUSE_CHECKOUT'); ?></button></div>
				
				<div class="pull-right  mymuse-button-right"><button class="button uk-button" 
				type="button" 
				onclick="location.href='<?php echo $params->get('my_continue_shopping'); ?>'">
				<?php echo JText::_('MYMUSE_CONTINUE_SHOPPING'); ?></button></div>
	  		<div style="clear: both;"></div>
		</div>

	
		<?php } 
		if(isset($order->waited)){
			echo "<!-- waited:".$order->waited." -->";
		}
		
		?>
