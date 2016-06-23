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
$no_items 	= count($order_item = $order->items);
$Itemid 	= @$this->Itemid;
$user 		= $this->user;
$params 	= $this->params;
$task		= $this->task;
$got_flash  = 0;
$post_order = array('confirm','makepayment','thankyou','vieworder', 'notify');
$notes_required = $params->get('my_notes_required',0);

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

for ($i=0;$i<count($order->items); $i++) { 
    if(!isset($order->items[$i])){
                  continue;
              }
    if(isset($order_item[$i]->flash)){
            $got_flash = 1;
    }
    if(isset($order_item[$i]->variation_select)){
        $got_variation = 1;
    }
}
?>
<?php if(isset($this->lists['licences'])){ 

	?>
<script>
jQuery(document).ready(function(){  
	jQuery("#licence").on('change', function(e){
		newval = jQuery('#licence>option:selected').val();
		newtext = jQuery('#licence>option:selected').text();
		//alert(newval);
		items = <?php echo $no_items; ?>;
		jQuery.post("index.php?option=com_mymuse&task=ajaxupdatelicence",
		{
			"my_licence":newval
		},

		function(data,status)
		{
			var res = jQuery.parseJSON(data);
			msg = res.msg;
			order = res.order;
			console.log(JSON.stringify(order ,null, 4));


			var output = '';
			for (var property in order) {
				 output += property + ': ' + order[property]+'; \n';
			}
			

			for(i=0; i < items; i++){
				jQuery("#item_price_"+i).html('<?php echo $params->get('my_currency_symbol')?>'+order["items"][i]["price"]["product_price"]);
				jQuery("#product_item_subtotal_"+i).html('<?php echo $params->get('my_currency_symbol')?>'+order["items"][i]["product_item_subtotal"]);
			}
			jQuery("#mytotal").html('<?php echo $params->get('my_currency_symbol')?>'+order["order_subtotal"]);
				my_modal.open({content: msg+"<br />", width: 300 });
			for(i=0; i < 5; i++){
				jQuery("#licence_desc_"+i).hide();
			}	
			jQuery("#licence_desc_"+newval).show();
			
				
		});
	});

});

</script>
<?php } ?>
		<?php if($order->do_html){ ?>
			<form action="index.php?Itemid=<?php echo $Itemid; ?>" method="post" name="adminForm">
		<?php } ?>

		<!-- start of basket -->

		<h2><?php echo JText::_('MYMUSE_SHOPPING_CART'); ?></h2> 
		  
		<?php if($params->get('product_player_type') == "single" && isset($order->flash)) : ?>
			<div id="product_player" 
			<?php if($params->get('product_player_height')) : ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php endif; ?>
			><?php echo $order->flash; ?>
			</div>
		<?php endif; ?>
		<?php if($params->get('product_player_type') == "playlist"){ ?>
			<div id="product_player" ><?php echo $product->flash; ?>
			</div>
			
		<?php } ?>
		<div style="clear: both"></div>
		
		<div class="clips petrol" 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		>
		
		       
	<table class="mymuse_cart cart">
		<thead>
		<tr class="mymuse_cart cart">
	<?php if($params->get("my_show_cart_preview") && $got_flash): ?>  
		<th class="mypreviews cart"></th>
	<?php endif; ?>			
		<th class="mytitle cart"><?php echo JText::_('MYMUSE_TITLE'); ?></th>
	<?php if($params->get("my_show_sku")): ?>
		<th class="mysku cart"><?php echo JText::_('MYMUSE_CART_SKU'); ?></th>
	<?php endif; ?>
		<th class="myprice cart"><?php echo JText::_('MYMUSE_CART_PRICE'); ?></th>
	
		<th class="myquantity cart"><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></th>
	
		<th class="mysubtotal cart"><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></th>
	<?php if(@$order->do_html): ?>
		   <th class="myaction cart"><?php echo JText::_('MYMUSE_CART_ACTION'); ?>&nbsp;<?php echo $order->update_form; ?></th>		    
	<?php endif; ?>
		</tr>
		</thead>
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order_item); $i++) { ?>
		
		    <tr>
		     <?php if($params->get("my_show_cart_preview") && $got_flash){ ?>  
		        <td class="mypreviews tracks jp-gui ui-widget cart" style="width: 42px;"><?php 
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
		        
		        <?php if(
                (isset($order_item[$i]->attribs['product_preorder']) && $order_item[$i]->attribs['product_preorder'])
                ){ ?>
		        	 : PRE-ORDER
		        <?php } ?> 
		        
		        </td>
		    <?php if($params->get("my_show_sku")){ ?>
		        <td class="mysku cart"><?php echo $order_item[$i]->product_sku; ?></td>
		    <?php } ?>
		    
		        <td class="myprice cart">
		        <?php if( isset($this->licence) &&  !in_array($task, $post_order) && isset($order_item[$i]->price['licence']) ){
		        	foreach($order_item[$i]->price['licence'] as $j=>$licence){ 
		        		if($this->my_licence == $j){
		        			echo '<div id="item_price_'.$i.'" class="price" >'.
		        		MyMuseHelper::printMoney($order_item[$i]->price['licence'][$j]['price']).
		        		'</div>';
		        		}
		        	}
		        }else{
		        	echo MyMuseHelper::printMoney($order_item[$i]->product_item_price);
		        }
		        ?>
		        </td>
		        
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
		        <td class="mysubtotal cart">
		        	<div id="product_item_subtotal_<?php echo $i ?>">
		        	<?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_subtotal); ?>
		        	</div>
		        </td>
		    
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
		        <td class="myoriginalsubtotal cart" colspan="<?php echo $order->colspan2; ?>">
		        	<div id="subtotal_before_discount">
		        	<?php echo MyMuseHelper::printMoney($order->subtotal_before_discount); ?>
		        	</div>
		       	</td>
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
		        <td class="myshoppergroupdiscount cart" colspan="<?php echo $order->colspan2; ?>">
		        	<div id="shopper_group_discount">(<?php echo MyMuseHelper::printMoney($order->shopper_group_discount); ?>)
		        	</div>
		        </td>
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
		        <td class="mydiscount cart" colspan="<?php echo $order->colspan2; ?>">
		        	<div id="discount">- <?php echo MyMuseHelper::printMoney($order->discount); ?>
		        	</div>
		        </td>
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
		        <td class="mycoupon cart" colspan="<?php echo $order->colspan2; ?>">
		        	<div id="coupon_discount"- <?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> 
		        	></div>
		        </td>
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
		        	<td class="mytax  cart" colspan="<?php echo $order->colspan2; ?>">
		        		<div id=tax_<?php echo strtolower($pre_key); ?>"><?php echo MyMuseHelper::printMoney($val); ?>
		        		</div>
		        	</td>
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
		    	<div id="order_shipping_cost">
		    	<?php echo MyMuseHelper::printMoney($order->order_shipping->cost); ?>
		    	</div>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		    </tr>
		<?php } ?>
		<tr>
		    <td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>"><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</td>
		    <td class="mytotal cart" colspan="<?php echo $order->colspan2; ?>">
		    	<div id="mytotal"><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    	</div>
		    </td>
		    <?php if($order->do_html){ ?>
		        <td class="mobile-hide cart" >&nbsp;</td>
		    <?php  } ?>
		</tr>
		
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
		    <td class="myreservationfee cart" colspan="<?php echo $order->colspan2; ?>" align="right">
		    	<div id="reservation_fee"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
		    	</div>
		    </td>
		    <?php if(@$order->do_html){ ?>
		        <td>&nbsp;</td>
		        <?php } ?>
		</tr>
			<?php  if($order->non_res_total > 0){ ?>
			<tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</td>
		    	<td class="myothercharges cart" colspan="<?php echo $order->colspan2; ?>" align="right">
		    		<div id=">non_res_total"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
		    		</div>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<tr>
		    	<td class="mobile-hide cart" colspan="<?php echo $order->colspan; ?>" align="right"><?php echo JText::_('MYMUSE_PAYNOW') ?>:</td>
		    	<td class="mypaynow cart" colspan="<?php echo $order->colspan2; ?>" align="right">
		    		<div id="must_pay_now">
		    		<?php echo MyMuseHelper::printMoney($order->must_pay_now); ?>
		    		</div>
		    	</td>
		    	<?php if(@$order->do_html){ ?>
		        	<td>&nbsp;</td>
		        	<?php } ?>
			</tr>
			<?php } ?>
		
		<?php } ?>

	</table>
		<?php 
		//LICENCE MODEL?
		if(2 == $params->get('my_price_by_product',0) && $user->username == 'buyer'){
			echo '<h3>'.JText::_('MYMUSE_LICENCE').'</h3>';

			if(!in_array($task, $post_order) && isset($this->lists['licences'])){
				echo $this->lists['licences'];
			}else{
				echo '<span class="licence-text">'.$this->my_licence_text.'</span>';
			}
			if(isset($this->lists['licences']) && $task != "confirm"){
				foreach($this->licence as $i=>$licence){
					if($i == $this->my_licence){
						$display = "block";
					}else{
						$display = "none";
					}
					$style = 'style="display:'.$display.'"';
					echo '<div id="licence_desc_'.$i.'" class="licence-text" '.$style.'>'.nl2br($licence['desc']).'</div>';
				}
			}elseif(isset($this->my_licence_desc) && $task != "confirm"){
				echo '<div id="licence_desc_'.$params->get('my_price_by_product',0).'" class="licence-text">'.nl2br($this->my_licence_desc).'</div>';
			}
		}
		?>
		
		
    <?php 
    //NOTES REQUIRED?
if($notes_required  && $user->username == 'buyer'){
	
    $notes = isset($order->notes['notes'])? $order->notes['notes'] : @$order->notes; 

    if(!is_array($notes)){
    	$jason = json_decode($notes);
    	if(is_array($jason)){
    		$notes = $jason['notes'];
    	}
    	if(is_object($jason)){
    		$notes = $jason->notes;
    	}
    }else{
    	$notes = '';
    }


    if(!in_array($task, $post_order)){ ?>		
		<h3><?php echo JText::_($params->get("my_notes_header"))?></h3>
        <?php echo JText::_($params->get("my_notes_msg"))?>
        <textarea class="required" style="height: 200px; width:90%;" name="notes" rows="10" cols="5"><?php 
        
        echo $notes; 
        
        ?></textarea>
<?php 
	}elseif($notes) { ?>
		<h3><?php echo JText::_($params->get("my_notes_header"))?></h3>
		<?php echo nl2br($notes);
	}
}
?>
        
    <?php if(!in_array($task, $post_order) && $user->username == 'buyer'){ ?>
        <div class="pull-left myupdate cart"><button class="button uk-button " 
				type="submit" >
				<?php echo JText::_('MYMUSE_UPDATE_CART'); ?></button>
		</div>
		
	<?php } ?>	
		<?php if($order->do_html){ ?>
		</form>
		<?php } ?>
		
		
		<?php 
		if(isset($order->show_checkout) && $order->show_checkout){ 
		    // add the checkout link
		?> 
	
			<?php if($user->username == '' && $params->get('my_registration') == "full_guest"){ ?>
				<div class="pull-left  mymuse-button-left cart">
					<button class="button uk-button" type="button"
					onclick="location.href='<?php echo JRoute::_("index.php?option=com_mymuse&task=guestcheckout&view=cart&Itemid=$Itemid") ?>'">
					Checkout as a guest.</button>
				</div>
			<?php } ?>
			
	  		<?php if(($user->username == 'buyer' && $notes_required && isset($notes) && $notes != '')
				|| $user->username != 'buyer'){ 
	  			?>
				<div class="pull-right mymuse-button-right cart">
					<button class="button uk-button" type="button" 
					onclick="location.href='<?php echo JRoute::_("index.php?option=com_mymuse&task=checkout&Itemid=$Itemid") ?>'">
					<?php echo JText::_('MYMUSE_CHECKOUT'); ?></button>
				</div>
			<?php } ?>
				
			<!-- 
				<div class="pull-right  mymuse-button-right cart">
				<button class="button uk-button" 
				type="button" 
				onclick="location.href='<?php echo $params->get('my_continue_shopping'); ?>'">
				<?php echo JText::_('MYMUSE_CONTINUE_SHOPPING'); ?></button>
				</div>
			 -->	

	  		
	


	
		<?php } 
		if(isset($order->waited)){
			echo "<!-- waited:".$order->waited." -->";
		}
		
		?>
		<?php if($params->get("my_use_coupons") && (preg_match("/shipping|addtocart|updatecart|cartdelete|showcart|checkout/",$task) || $task == '') && !isset($order->coupon->id) ){ ?>
		    <div class="mycoupon cart">
		    <form action="index.php" method="post" name="CouponadminForm">
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
