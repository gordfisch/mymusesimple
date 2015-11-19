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
$got_flash  = 0;
for ($i=0;$i<count($order->items); $i++) { 
    if(isset($order_item[$i]->flash)){
            $got_flash = 1;
    }
}
?>

		<?php if($order->do_html){ ?>
			<form action="index.php?Itemid=<?php echo $Itemid; ?>" method="post" name="adminForm">
		<?php } ?>

		<!-- start of basket -->

		<h2><?php echo JText::_('MYMUSE_SHOPPING_CART'); ?></h2>          
	<table class="mymuse_cart cart">
		<thead>
		<tr class="mymuse_cart cart">
	<?php if($params->get("my_show_cart_preview") && $got_flash){ ?>  
		<th class="mypreviews cart"></th>
	<?php }?>			
		<th class="mytitle cart"><?php echo JText::_('MYMUSE_TITLE'); ?></th>
	<?php if($params->get("my_show_sku")){ ?>
		<th class="mysku cart"><?php echo JText::_('MYMUSE_CART_SKU'); ?></th>
	<?php } ?>
		<th class="myprice cart"><?php echo JText::_('MYMUSE_CART_PRICE'); ?></th>
		<th class="myquantity cart"><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></th>

		<th class="mysubtotal cart"><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></th>
		<?php if(@$order->do_html){ ?>
		    <th class="myaction cart"><?php echo JText::_('MYMUSE_CART_ACTION'); ?>&nbsp;<?php echo $order->update_form; ?></th>		    
		<?php } ?>
		</tr>
		</thead>
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order->items); $i++) { 
		?>
		
		    <tr>
		     <?php if($params->get("my_show_cart_preview") && $got_flash){ ?>  
		        <td class="mypreviews cart"><?php 
                echo isset($order_item[$i]->flash)?  $order_item[$i]->flash : ''; ?></td>
		     <?php } ?>			    
		        <td class="mytitle cart">
		        <?php if(isset($order_item[$i]->category_name) && $params->get('mymuse_show_category')){ ?>
		        	 <?php echo $order_item[$i]->category_name; ?> : 
		        <?php } ?>
		        
		        <?php if(isset($order_item[$i]->product->parent->title)){ ?>
		        	 <?php echo $order_item[$i]->product->parent->title; ?> :
		        <?php } ?>
		        <?php echo $order_item[$i]->title; ?>
		        
		        <?php if(isset($order_item[$i]->file_ext)){ ?>
		        	 :<?php echo $order_item[$i]->file_ext ?> 
		        <?php } ?>
		        
		        </td>
		    <?php if($params->get("my_show_sku")){ ?>
		        <td class="mysku cart"><?php echo $order_item[$i]->product_sku; ?></td>
		    <?php } ?>
		        <td class="myprice cart"> <?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_price); ?></td>
		        
		   <?php if($order->do_html && $order_item[$i]->quantity){ ?>
		        <td class="myquantity cart"> <input class="inputbox" type="text" size="4" maxlength="4" name="quantity[<?php echo $order_item[$i]->id ?>]"
		        value="<?php echo $order_item[$i]->quantity;?>"   />&nbsp;</td>
		        
		    <?php }else{ ?>
		        <td class="myquantity cart"><?php echo $order_item[$i]->quantity; 
		        if($params->get('my_add_stock_zero',0) && $order_item[$i]->quantity == 0) {
		        	echo " ".JText::_('MYMUSE_BACKORDERED');
		        }
		        ?></td>
		    <?php } ?>  
		        <td class="mysubtotal cart"><?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_subtotal); ?></td>
		        
		    <?php if($order->do_html){ ?>
		        <td class="myaction cart"><a href="<?php echo $order_item[$i]->delete_url; ?>"><?php echo JText::_('MYMUSE_DELETE'); ?></a></td>
		    <?php } ?>
		       </tr>
		<?php } ?>
		
		<?php if($order->discount > 0.00 || ($params->get("my_use_coupons") && @$order->coupon->id)
		|| count($order->tax_array) > 0){ ?>
		<!--  original subtotal -->
			<tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></td>
		        <td class="myoriginalsubtotal cart" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->subtotal_before_discount); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		
		<?php //for shopper group discount 
		if($order->shopper_group_discount > 0.00){ ?>
		    <tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>
		    	<?php echo $order->shopper_group_name; ?> <?php echo $user->shopper_group->discount; ?> %</td>
		        <td class="myshoppergroupdiscount cart" colspan="<?php echo $order->colspan2; ?>">(<?php echo MyMuseHelper::printMoney($order->shopper_group_discount); ?>)</td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		
		<?php //for regular discount
		if($order->discount > 0.00){ ?>
		    <tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo JText::_('MYMUSE_DISCOUNT'); ?>
		    	</td>
		        <td class="mydiscount cart" colspan="<?php echo $order->colspan2; ?>">- <?php echo MyMuseHelper::printMoney($order->discount); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide cart">&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
				

		<?php //COUPONS
		if($params->get("my_use_coupons") && @$order->coupon->id){ ?>
		    <tr>
		    	<td class="mobile-hide cart"><?php echo JText::_('MYMUSE_YOUR_COUPON'); ?> <?php echo $order->coupon->title ?></td>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan -1; ?>">&nbsp;</td>
		        <td class="mycoupon cart" colspan="<?php echo $order->colspan2; ?>">- <?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> </td>
		        <?php if(@$order->do_html){ ?>
		        <td class="mobile-hide cart">&nbsp;</td>
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
		        	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo $key; ?></td>
		        	<td class="mytax  cart <?php echo strtolower($pre_key); ?>" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        	<?php if(@$order->do_html){ ?>
		        	<td class="mobile-hide cart">&nbsp;</td>
		        	<?php  } ?>
		        </tr>
		<?php  } 
		} ?>
		
		<?php // SHIPPING
		if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) { ?>
		    <tr>
		    <td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo JText::_('MYMUSE_SHIPPING') ?>
		    <?php echo $order->order_shipping->ship_carrier_name ?> <?php echo $order->order_shipping->ship_method_name ?></td>
		    <td class="myshipping cart" colspan="<?php echo $order->colspan2; ?>">
		    <?php echo MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		<tr>
		    <td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</td>
		    <td class="mytotal cart" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    <?php echo $this->currency['currency_code']; ?></td>
		    <?php if($order->do_html){ ?>
		        <td class="mobile-hide cart" >&nbsp;</td>
		    <?php  } ?>
		
		
		<?php if($order->do_html){ ?>
		<tr>
		    <td class="" colspan="<?php echo $order->colspan; ?>">
		    <div class="pull-right myupdate cart"><button class="button uk-button " 
				type="submit" >
				<?php echo JText::_('MYMUSE_UPDATE_CART'); ?></button></div>
		    </td>
		    <td colspan="<?php echo $order->colspan2; ?>"> </td>
		    <td class="mobile-hide cart">&nbsp;</td>
		</tr>
		    
		
		<?php } ?>
		
		<?php  if($order->reservation_fee > 0){ ?>
		<tr>
		    <td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_RESERVATION_FEE') ?>:</td>
		    <td class="myreservationfee cart" colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		</tr>
			<?php  if($order->non_res_total > 0){ ?>
			<tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</td>
		    	<td class="myothercharges cart" colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_PAYNOW') ?>:</td>
		    	<td class="mypaynow cart" colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($order->must_pay_now); ?>
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
	  			
				<div class="pull-left mymuse-button-left cart"><button class="button uk-button" 
				type="button" 
				onclick="location.href='<?php echo JRoute::_("index.php?option=com_mymuse&task=checkout&Itemid=$Itemid") ?>'">
				<?php echo JText::_('MYMUSE_CHECKOUT'); ?></button></div>
				
				<div class="pull-right  mymuse-button-right cart"><button class="button uk-button" 
				type="button" 
				onclick="location.href='<?php echo $params->get('my_continue_cart'); ?>'">
				<?php echo JText::_('MYMUSE_CONTINUE_SHOPPING'); ?></button></div>
	  		<div style="clear: both;"></div>
		</div>

	
		<?php } 
		if(isset($order->waited)){
			echo "<!-- waited:".$order->waited." -->";
		}
		
		?>
		<?php if($params->get("my_use_coupons") && (preg_match("/shipping|addtocart|updatecart|cartdelete|showcart|checkout/",$task) || $task == '') && !isset($order->coupon->id) ){ ?>
		    <div class="mycoupon cart">
		    <form action="index.php" method="post" name="adminForm">
            <input type="hidden" name="option" value="com_mymuse">
            <input type="hidden" name="task" value="couponadd">
            <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>">
            <table class="mymuse_cart cart" >
                <tr class="mymuse_cart_top cart">
                    <td colspan="2" class="mymuse_cart_top cart"><b><?php echo JText::_('MYMUSE_ENTER_COUPON_CODE'); ?></b></td>
                </tr>
                <tr>
                    <td><input type="text" class="input" name="coupon" value="" size="50"></td>
                    <td><div class="pull-left"><button class="button uk-button cart" 
                        type="submit" ><?php echo JText::_('MYMUSE_SUBMIT'); ?></button></div></td>
                </tr>
            </table>
            </form>
            </div>
		<?php } ?> 
