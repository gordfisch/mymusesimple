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
<div >
		<!-- start of basket -->

		<h2><?php echo JText::_('MYMUSE_SHOPPING_CART'); ?></h2>
		<?php if($params->get("my_use_coupons") && (preg_match("/addtocart|updatecart|cartdelete|showcart/",$task) || $task == '') && !isset($order->coupon->id) ){ ?>
		    <div class="coupon"><a class="titles" href="index.php?option=com_mymuse&task=coupon&Itemid=<?php echo $Itemid ?>"><b><?php echo JText::_('MYMUSE_ENTER_A_COUPON'); ?></b></a></div>
		    </div>
		<?php } ?>
		<table class="mymuse_cart">
		<tr class="mymuse_cart_top">
		<td class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_TITLE'); ?></b></td>
	<?php if($params->get("my_show_sku")){ ?>
		<td class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_SKU'); ?></b></td>
	<?php } ?>
		<td class="mymuse_cart_top" align="right" width="80"><b><?php echo JText::_('MYMUSE_CART_PRICE'); ?></b></td>
		<td class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></b></td>
		<td class="mymuse_cart_top" width="80" align="right"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></td>
		<?php if(@$order->do_html){ ?>
		    <td align="center" class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_ACTION'); ?></b>&nbsp;<?php echo $order->update_form; ?></td>		    
		<?php } ?>
		</tr>
		
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order->items); $i++) { 
		      if ($i % 2){
		          $class = "row1";
		      }else{
		          $class = "row2";
		      }
		?>
		
		    <tr class="<?php echo $class ?>">
		        <td align="left">
		        <?php if(isset($order_item[$i]->category_name) && isset($order_item[$i]->cat_url)){ ?>
		        	 <a href="<?php echo JURI::base().$order_item[$i]->cat_url; ?>"><?php echo $order_item[$i]->category_name; ?></a> :
		        <?php } ?>
		        
		        <?php if(isset($order_item[$i]->parent->title)){ ?>
		        	 <a href="<?php echo JURI::base().$order_item[$i]->url; ?>"><?php echo $order_item[$i]->parent->title; ?></a> :
		        <?php } ?>
		        <a href="<?php echo JURI::base().$order_item[$i]->url; ?>"><?php echo $order_item[$i]->title; ?></a>
		        
		        </td>
		    <?php if($params->get("my_show_sku")){ ?>
		        <td align="right"><?php echo $order_item[$i]->product_sku; ?></td>
		    <?php } ?>
		        <td align="right"> <?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_price); ?></td>
		        
		   <?php if($order->do_html && $order_item[$i]->quantity){ ?>
		        <td> <input class="inputbox" type="text" size="4" maxlength="4" name="quantity[<?php echo $order_item[$i]->id ?>]"
		        value="<?php echo $order_item[$i]->quantity;?>"/>&nbsp;</td>
		        
		    <?php }else{ ?>
		        <td align="center"><?php echo $order_item[$i]->quantity; ?></td>
		    <?php } ?>
		        
		        <td align="right"><?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_subtotal); ?></td>
		        
		    <?php if($order->do_html){ ?>
		        <td>
		        <table>
		        	<tr>
		        		<td><a href="<?php echo $order_item[$i]->delete_url; ?>"><?php echo JText::_('MYMUSE_DELETE'); ?></a></td>
		        	</tr>
		        </table>
		        </td>
		    <?php } ?>
		       </tr>
		<?php } ?>
		
		<?php 
		if($order->discount > 0.00){ 
			//for shopper group discount
			
			?>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_CART_ORIGINAL_SUBTOTAL'); ?>:</td>
		        <td align="right" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->discount + $order->order_subtotal); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		    
		    <tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>:
		    	<?php echo $order->shopper_group_name; ?> <?php echo $order->shopper_group_discount; ?> %</td>
		        <td align="right" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->discount); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		    
		    <tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_CART_NEW_SUBTOTAL'); ?>:</td>
		        <td align="right" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->order_subtotal); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		
		<?php } ?>
		
		
		
		<?php //COUPONS
		if($params->get("my_use_coupons") && @$order->coupon->id){ ?>
		    <tr>
		    	<td><?php echo JText::_('MYMUSE_YOUR_COUPON'); ?> : <?php echo $order->coupon->title ?></td>
		    	<td colspan="<?php echo $order->colspan -1; ?>">&nbsp;</td>
		        <td colspan="<?php echo $order->colspan2; ?>" align="right">-<?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> </td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
				
		<?php // SHIPPING
		if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) { ?>
		    <tr>
		    <td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_SHIPPING') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		
		<?php // TAXES
		if(@$order->tax_array){
		    while(list($key,$val) = each($order->tax_array)){ 
		    	$key = preg_replace("/_/"," ", $key);
		    	?>
		        <tr>
		        <td colspan="<?php echo $order->colspan; ?>" align="right"><?php echo $key; ?></td>
		        <td colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php  } ?>
		        </tr>
		<?php  } 
		} ?>
		
		
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>" class="textbox2" align="right"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" class="textbox2" align="right"><b><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    <?php echo $this->currency['currency_code']; ?></b></td>
		    <?php if($order->do_html){ ?>
		        <td>&nbsp;</td>
		    <?php  } ?>
		</tr>
		
		
		<?php if($order->do_html){ ?>
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>"  class="textbox2" align="left"></td>
		    <td colspan="<?php echo $order->colspan2; ?>" class="textbox2"><input type="submit" name="submit" 
		    value="<?php echo JText::_('MYMUSE_UPDATE_CART'); ?>" class="button" /></td>
		    <td>&nbsp;</td>
		</tr>
		    
		
		<?php } ?>
		
		<?php  if($order->reservation_fee > 0){ ?>
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_RESERVATION_FEE') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		</tr>
			<?php  if($order->non_res_total > 0){ ?>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</b></td>
		    	<td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_PAYNOW') ?>:</b></td>
		    	<td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->must_pay_now); ?></b>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<?php } ?>
		
		<?php } ?>
		
		</table>

		</form>
		<br />
		<?php 
	
		if(isset($order->show_checkout) && $order->show_checkout){ 
		    // add the checkout link
		?> 
		

		<table class="mymuse_cart">
			<tr>
				<td align="center" width="50%"><form>
				<input type="button" class="button" 
				onclick="location.href='index.php?option=com_mymuse&task=checkout&Itemid=<?php echo $Itemid; ?>'"
				value="<?php echo JText::_('MYMUSE_CHECKOUT'); ?>"
				/></form>
				
				</td>
				
				<td align="center"><form>
				<input type="button" class="button" 
				onclick="location.href='<?php echo $params->get('my_continue_shopping'); ?>'"
				value="<?php echo JText::_('MYMUSE_CONTINUE_SHOPPING'); ?>"
				/></form>
		
				</td>
			</tr>
		</table>

		<?php } 
		if(isset($order->waited)){
			echo "<!-- waited:".$order->waited." -->";
		}
		?>
</div>