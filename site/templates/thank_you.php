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
     <table class="mymuse_cart">
 
        <!-- Begin Order Summary -->
        <tr>
            <td class="mymuse_cart_top" colspan="2"><b><?php echo JText::_('MYMUSE_ORDER_SUMMARY') ?></b></td>
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
	</table>
	
	<?php if(isset($my_email_msg)){ ?>
        <table class="mymuse_cart_email">
        <tr>
            <td class="mymuse_cart_top" colspan="2"><b><?php echo $my_email_msg; ?></b></td>
        </tr>
        </table>
    <?php } ?>
    
    <?php if(isset($params->my_email_msg)){ ?>
        <table class="mymuse_cart_email">
        <tr>
            <td class="mymuse_cart_top" colspan="2"><b><?php echo $params->my_email_msg; ?></b></td>
        </tr>
        </table>
    <?php } ?>
        

		<!-- start of basket -->
		<table class="mymuse_cart">
		<tr>
			<td  class="sectiontableheader mymuse_cart_top" COLSPAN="4"><b><?php echo JText::_('MYMUSE_ORDER_DETAILS') ?></b></td>
		</tr>
		<tr class="mymuse_cart_top">
			<td class="sectiontableheader mymuse_cart_top"><b><?php echo JText::_('MYMUSE_TITLE'); ?></b></td>
		<?php if($params->get("my_show_sku")){ ?>
			<td class="sectiontableheader mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_SKU'); ?></b></td>
		<?php } ?>
			<td class="sectiontableheader mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_PRICE'); ?></b></td>
			<td class="sectiontableheader mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></b></td>
			<td class="sectiontableheader mymuse_cart_top" width="50"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></td>
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
		        <td align="left"> 
		        <?php echo $order->items[$i]->title; ?> :
				<?php echo $order->items[$i]->category_name; ?> </td>
		        <?php if($params->get("my_show_sku")){ ?>
		        <td align="right"><?php echo $order->items[$i]->product_sku; ?></td>
		        <?php } ?>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->items[$i]->product_item_price); ?></td>
		        <td align="center"><?php echo $order->items[$i]->product_quantity; ?></td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->items[$i]->product_item_subtotal); ?></td>
		       </tr>
		<?php } ?>
		
		
		
		<?php if(isset($order->discount) && $order->discount > 0){ ?>

			<tr class="'.$class .'">
		    	<td colspan="3" align="right"><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?>:</td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->discount + $order->order_subtotal); ?></td>
		    </tr>
		    
		    <tr>
		    	<td colspan="3" align="right"><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>:
		    	<?php echo $shopper->shopper_group_name.' '.$shopper->discount; ?> %</td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->discount); ?></td>
		    </tr>
		    
		    <tr>
		    	<td colspan="3" align="right"><?php echo JText::_('MYMUSE_CART_NEW_SUBTOTAL'); ?>:</td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order->order_subtotal); ?></td>
		    </tr>
		<?php } ?>
		
		<?php if($params->get("my_use_coupons") && isset($order->coupon_discount) && $order->coupon_discount > 0){ ?>
		    <tr>
		    	<td colspan="3" align="right"><?php echo $order->coupon_name ?>:
		        </td>
		        <td align="right">- <?php echo MyMuseHelper::printMoney($order->coupon_discount); ?> 
		        </td>
		    </tr>
		<?php } ?>
				
		<?php if ($params->get("my_use_shipping") && 
				isset($order->order_shipping) &&
				@$order->order_shipping->cost > 0) { ?>
		    <tr>
		    	<td colspan="3" align="right">Shipping:</td>
		    	<td align="right"><?php echo  MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    </td>
		    </tr>
		<?php } ?>
		
		<?php // TAXES
		if(@$order->tax_array){
		    while(list($key,$val) = each($order->tax_array)){ 
		    	$key = preg_replace("/_/"," ", $key);
		    	?>
		        <tr>
		        <td colspan="3" align="right"><?php echo $key; ?></td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php  } ?>
		        </tr>
		<?php  } 
		} ?>
		
		
		<tr>
		    <td colspan="3" class="textbox2" align="right"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</b></td>
		    <td align="right"><b><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    </b></td>
		</tr>
		
		

		
		</table>
		<br />

      
  <!-- Begin 2 column bill-ship to -->

		<table class="mymuse_cart">
		<tr>
			<td class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_SHOPPER_INFORMATION') ?></b></td>
		</tr>
        <tr VALIGN=top>
            <td width=50%> <!-- Begin BillTo -->

            <table class="mymuse_cart_inner">
                <tr>
                <td class="mymuse_cart_top" COLSPAN=2><b><?php echo JText::_('MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                <td>
                <?php echo $shopper->name; ?>
                </td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                <td><?php echo $shopper->email ?></td>
                </tr>
           <?php 
           if(isset($shopper->profile)){
           		foreach ($shopper->profile as $label=>$value){ 
           			if($value == ''){ continue;} 
           			if($label == 'shopper_group'){continue;}
           			
           			if($label == 'category_owner'){
           				continue;
           			}
           			if($label == 'region'){continue;}
           			if($label == 'region'){
           				continue;
           			}
           			if($label == 'region_name'){
           				$label = "region";
           			}
           			if($label == 'first_name'){
           				continue;
           			}
           			if($label == 'last_name'){
           				continue;
           			}
           			if($label == 'email'){
           				continue;
           			}
           			?>
               
                <tr>
                <td><?php echo JText::_("MYMUSE_".strtoupper($label)) ?>:</td>
                <td>
                <?php echo $value ?>
                </td>
                </tr>
                
            <?php } 
           }
            ?>
                
            </table>
            <!-- End BillTo --> </td>
        
            <td width=50%>&nbsp;
        <?php 
        if($params->get('my_use_shipping') && isset($shopper->shipto)){
        ?>
            <table class="mymuse_cart_inner">
                <tr>
                <td class="mymuse_cart_top" COLSPAN=2><b><?php echo JText::_('MYMUSE_SHIPPING_ADDRESS') ?></b></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                <td>
                <?php echo $shopper->shipto->name ?>
                </td>
                </tr>
                <tr VALIGN=TOP>
                <td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                <td>
                <?php echo $shopper->shipto->address1 ?>
                <br />
                <?php echo $shopper->shipto->address2 ?>
                </td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                <td><?php echo $shopper->shipto->city; ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                <td><?php echo $shopper->shipto->region; ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                <td><?php echo $shopper->shipto->postal_code; ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                <td><?php echo $shopper->shipto->country; ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                <td><?php echo $shopper->shipto->phone1; ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                <td><?php echo $shopper->shipto->email; ?></td>
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