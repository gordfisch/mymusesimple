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
?>
<h1 class="cart-header"><?php echo JText::_('MYMUSE_ORDER_SUMMARY'); ?></h1>   
     <table class="mymuse_cart cart">
        <!-- Begin Order Summary -->
        <tr>
            <td class="mobile-hide cart"><?php echo JText::_('MYMUSE_ORDER_NUMBER') ?>:</td>
            <td class="myordernumber cart"><?php echo sprintf("%08d", $order->id) ?></td>
        </tr>
        <tr>
            <td class="mobile-hide cart"><?php echo JText::_('MYMUSE_ORDER_DATE') ?>:</td>
            <td class="myorderdate cart"><?php echo $order->created ?></td>
        </tr>
        <tr>
            <td class="mobile-hide cart"><?php echo JText::_('MYMUSE_ORDER_STATUS') ?>:</td>
            <td class="myorderstatus cart"><?php echo JText::_('MYMUSE_'.strtoupper($order->status_name)) ?></td>
        </tr>
        <tr>
            <td class="mobile-hide"><?php echo JText::_('MYMUSE_ORDER_TOTAL') ?>:</td>
            <td class="mysummarytotal"><?php echo MyMuseHelper::printMoney($order->order_total)." ".$order->order_currency['currency_code'] ?></td>
        </tr>
	</table>
	
	<?php if(isset($my_email_msg)){ ?>
        <table class="mymuse_cart mymuse_cart_email cart">
        <tr>
            <td class="mymuse_cart cart" colspan="2"><b><?php echo $my_email_msg; ?></b></td>
        </tr>
        </table>
    <?php } ?>
    
    <?php if(isset($params->my_email_msg)){ ?>
        <table class="mymuse_cart mymuse_cart_email cart">
        <tr>
            <td class="mymuse_cart cart" colspan="2"><b><?php echo $params->my_email_msg; ?></b></td>
        </tr>
        </table>
    <?php } ?>
        

		<!-- start of basket -->
		<table class="mymuse_cart cart">
		<tr>
			<td  class="cart mymuse_cart_top cart" COLSPAN="4"><b><?php echo JText::_('MYMUSE_ORDER_DETAILS') ?></b></td>
		</tr>
		<tr class="mymuse_cart_top">
			<td class="cart mymuse_cart_top cart"><b><?php echo JText::_('MYMUSE_TITLE'); ?></b></td>
		<?php if($params->get("my_show_sku")){ ?>
			<td class="cart mymuse_cart_top cart"><b><?php echo JText::_('MYMUSE_CART_SKU'); ?></b></td>
		<?php } ?>
			<td class="cart mymuse_cart_top cart"><b><?php echo JText::_('MYMUSE_CART_PRICE'); ?></b></td>
			<td class="cart mymuse_cart_top cart"><b><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></b></td>
			<td class="cart mymuse_cart_top cart" width="50"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></td>
		</tr>
		
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<$order->idx;$i++) { 
		      if ($i % 2){
		          $class = "row1";
		      }else{
		          $class = "row2";
		      }
		?>
		
		    <tr class="<?php echo $class ?>">
		        <td align="left" class="mytitle cart"> 
		        <?php if(isset($order->items[$i]->category_name) && $params->get('mymuse_show_category')){ ?>
		        	 <?php echo $order->items[$i]->category_name; ?> :
		        <?php } ?>
		        
		        <?php if(isset($order->items[$i]->parent->title)){ ?>
		        	 <?php echo $order->items[$i]->parent->title; ?> :
		        <?php } ?>
		        <?php echo $order->items[$i]->title; ?>
		        
		        <?php if(isset($order_items[$i]->file_name)){ ?>
		        	 :<br /><?php echo $order_items[$i]->file_name; ?> 
		        <?php } ?>
				
				</td>
		        <?php if($params->get("my_show_sku")){ ?>
		        <td class="mysku cart"><?php echo $order->items[$i]->product_sku; ?></td>
		        <?php } ?>
		        <td class="myprice cart"><?php echo MyMuseHelper::printMoney($order->items[$i]->product_item_price); ?></td>
		        <td class="myquantity cart"><?php echo $order->items[$i]->product_quantity; ?></td>
		        <td class="mysubtotal cart"><?php echo MyMuseHelper::printMoney($order->items[$i]->product_item_subtotal); ?></td>
		       </tr>
		<?php } ?>
		
		
			<tr class="'.$class .' mysubtotal cart">
		    	<td class="mobile-hide cart" colspan="3" align="right"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></td>
		        <td class="myoriginalsubtotal cart"><?php echo MyMuseHelper::printMoney($order->subtotal_before_discount); ?></td>
		    </tr>
			<tr>
		    	<td class="cart" colspan="<?php echo $order->colspan + $order->colspan2; ?>"><hr style="width: 100%"></td>
		    </tr>
		
		<?php //SHOPPER GROUP
		if(isset($order->discount) && $order->discount > 0){ ?>

		    <tr>
		    	<td class="mobile-hide cart" colspan="3" align="right"><b><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?></b>
		    	<?php echo $shopper->shopper_group_name.' '.$shopper->discount; ?> %</td>
		        <td class="myshoppergroupdiscount cart"><?php echo MyMuseHelper::printMoney($order->shopper_group_discount); ?></td>
		    </tr>

		<?php } ?>
		
		<?php //DISCOUNT
		if(isset($order->discount) && $order->discount > 0){ ?>

		    <tr>
		    	<td class="mobile-hide cart"colspan="3" align="right"><b><?php echo JText::_('MYMUSE_DISCOUNT'); ?></b></td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->discount); ?></td>
		    </tr>

		<?php } ?>
		
		<?php //COUPONS
		if($params->get("my_use_coupons") && isset($order->coupon_discount) && $order->coupon_discount > 0){ ?>
		    <tr>
		    	<td class="mobile-hide cart" colspan="3" align="right"><b><?php echo $order->coupon_name ?></b>
		        </td>
		        <td class="mycoupon cart">- <?php echo MyMuseHelper::printMoney($order->coupon_discount); ?> 
		        </td>
		    </tr>
		<?php } ?>
				
		<?php // TAXES
		if(@$order->tax_array){
		    while(list($key,$val) = each($order->tax_array)){ 
		    	$pre_key = preg_replace("/_/","", $key);
		    	$key = preg_replace("/_/"," ", $key);
		    	?>
		        <tr>
		        <td class="mobile-hide cart colspan="3"><b><?php echo $key; ?></b></td>
		        <td class="mytax cart <?php echo strtolower($pre_key); ?>"  align="right"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php  } ?>
		        </tr>
		<?php  } 
		} ?>
		
		<?php //SHIPPING
		if ($params->get("my_use_shipping") && 
				isset($order->order_shipping) &&
				@$order->order_shipping->cost > 0) { ?>
		    <tr>
		    	<td class="mobile-hide cart" colspan="3">Shipping:</td>
		    	<td class="myshipping cart" align="right"><?php echo  MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    </td>
		    </tr>
		<?php } ?>
		
		<tr>
		    <td class="mobile-hide cart" colspan="3" class="textbox2"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</b></td>
		    <td class="mytotal cart"><b><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    </b></td>
		</tr>
		
		

		
		</table>
		<br />

      
      <!-- Begin 2 column bill-ship to -->
        <h2><?php echo JText::_('MYMUSE_SHOPPER_INFORMATION') ?></h2>
		<table class="mymuse_cart">
        <tr>
            <td style="vertical-align:top"> <!-- Begin BillTo -->

            <table class="mymuse_cart" >
                <tr class="mymuse_cart_top">
                	<td class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                	<td class="myfullname">
                	<?php echo $shopper->name ?>
                	</td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                	<td class="myemail"><?php echo $shopper->email ?></td>
                </tr>
                
            <?php if(isset($shopper->profile)){ ?>
            
              <?php if(isset($shopper->profile['phone']) && $shopper->profile['phone'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                	<td class="myphone"><?php echo $shopper->profile['phone'] ?></td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['mobile']) && $shopper->profile['mobile'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_MOBILE') ?>:</td>
                	<td class="myphone"><?php echo $shopper->profile['mobile'] ?></td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['address1']) && $shopper->profile['address1'] !=''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td class="myaddress">
                	<?php echo $shopper->profile['address1'] ?>
                	
                	<?php echo @$shopper->profile['address2'] ?>
                	</td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['city']) && $shopper->profile['city'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td class="mycity"><?php echo $shopper->profile['city'] ?></td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['region_name']) && $shopper->profile['region_name'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td class="myregion"><?php echo $shopper->profile['region_name'] ?></td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['country']) && $shopper->profile['country'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td class="mycountry"><?php echo $shopper->profile['country'] ?></td>
                </tr>
            <?php  } ?>
            
            <?php if(isset($shopper->profile['postal_code']) && $shopper->profile['postal_code'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td class="myzip"><?php echo $shopper->profile['postal_code'] ?></td>
                </tr>
              <?php } ?>
              
			<?php } //end if profile?>
                
            </table>
            <!-- End BillTo --> </td>
        
            <td style="vertical-align:top">
    
        <?php 
        if($params->get('my_use_shipping') && isset($this->order->need_shipping) 
        		&& $this->order->need_shipping && isset($shopper->profile['shipping_first_name'])){
        ?>
            <table class="mymuse_cart">
                <tr class="mymuse_cart_top">
                <td class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_SHIPPING_ADDRESS') ?></b></td>
                </tr>
				<tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_NAME') ?>:</td>
                	<td class="mycity"><?php echo $shopper->profile['shipping_first_name']." ".$shopper->profile['shipping_last_name'] ?></td>
                </tr>
                <tr VALIGN=TOP>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td class="myaddress">
                	<?php echo $shopper->profile['shipping_address1'] ?>
                	<br />
                	<?php echo $shopper->profile['shipping_address2'] ?>
                	</td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td class="mycity"><?php echo $shopper->profile['shipping_city'] ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td class="myregion"><?php echo $shopper->profile['shipping_region_name'] ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td class="mycountry"><?php echo $shopper->profile['shipping_country'] ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td class="myzip"><?php echo $shopper->profile['shipping_postal_code'] ?></td>
                </tr>
            </table>
            <!-- End ShipTo -->

          <?php 
        }
        ?></td>
            <!-- End Customer Information --> 
        </tr>
        </table>
        <br />