<?php defined('_JEXEC') or die(); 
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */
 

?>

<form action="<?php echo $callbackUrl ?>" method="post" class="form form-horizontal">
	<input type="hidden" name="amount" value="<?php echo sprintf("%.2f", $order->order_total); ?>" />
	<input type="hidden" name="customer_ip" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />
	<input type="hidden" name="num_cart_items" value="<?php echo $order->idx; ?>" />
	<input type="hidden" name="custom" value="<?php echo $custom; ?>" />
	<input type="hidden" name="invoice_num" value="<?php echo $order->id; ?>" />
	<input type="hidden" name="cust_id" value="<?php echo $shopper->id; ?>" />

	<?php 
	//ITEMS
	$j = 1;
	for ($i=0;$i<$order->idx;$i++) {
		if(isset($order->items[$i]->title) && $order->items[$i]->title != ''){
		?>
			<input type="hidden" name="ITEM_<?php echo $j; ?>_ID" value="<?php echo $order->items[$i]->id; ?>" />
			<input type="hidden" name="ITEM_<?php echo $j; ?>_NAME" value="<?php echo $order->items[$i]->title; ?>" />
			<input type="hidden" name="ITEM_<?php echo $j; ?>_DESC" value="<?php echo @$order->items[$i]->desc; ?>" />
			<input type="hidden" name="ITEM_<?php echo $j; ?>_QUANT" value="<?php echo $order->items[$i]->product_quantity; ?>" />
			<input type="hidden" name="ITEM_<?php echo $j; ?>_PRICE" value="<?php echo $order->items[$i]->product_item_price; ?>" />
			<input type="hidden" name="ITEM_<?php echo $j; ?>_TAXABLE" value="<?php echo @$order->items[$i]->taxable; ?>" />
			<?php
			$j++;
		}
			
	}

	?>

    <input type="hidden" name="first_name" 	id="first_name" 	value="<?php echo $shopper->profile['first_name']; ?>" />
    <input type="hidden" name="last_name" 	id="last_name"    	value="<?php echo $shopper->profile['last_name']; ?>"  />
    <input type="hidden" name="email" 		id="email"  		value="<?php echo $shopper->profile['email']; ?>" />
    <input type="hidden" name="address" 	id="address"  		value="<?php echo $shopper->profile['address1'] .' '.$shopper->profile['address2']; ?>"  />
    <input type="hidden" name="country" 	id="country" 		value="<?php echo $shopper->profile['country']; ?>"  />
    <input type="hidden" name="city" 		id="city" 			value="<?php echo $shopper->profile['city']; ?>"  />
    <input type="hidden" name="state" 		id="state" 			value="<?php echo $shopper->profile['region']; ?>"  />
    <input type="hidden" name="zip" 		id="zip" 			value="<?php echo $shopper->profile['postal_code']; ?>"  />
    <input type="hidden" name="phone" 		id="phone" 			value="<?php echo $shopper->profile['phone']; ?>"  />
    
    

<?php if($params->get('my_use_shipping') && isset($order->order_shipping->cost) && $order->order_shipping->cost > 0){ ?>   
    <input type="hidden" name="ship_to_first_name" 	id="ship_to_first_name" value="<?php echo $shopper->profile['shipping_first_name']; ?>" />
    <input type="hidden" name="ship_to_last_name" 	id="ship_to_last_name" 	value="<?php echo $shopper->profile['shipping_last_name']; ?>" />
    <input type="hidden" name="ship_to_address" 	id="ship_to_address"  	value="<?php echo $shopper->profile['shipping_address1'].' '.$shopper->profile['shipping_address2'];  ?>"  />
    <input type="hidden" name="ship_to_city" 		id="ship_to_city" 		value="<?php echo $shopper->profile['shipping_city']; ?>"  />
    <input type="hidden" name="ship_to_state" 		id="ship_to_state" 		value="<?php echo $shopper->profile['shipping_region_name']; ?>"  />
    <input type="hidden" name="ship_to_zip" 		id="ship_to_zip" 		value="<?php echo $shopper->profile['shipping_postal_code']; ?>"  />
    <input type="hidden" name="ship_to_country" 	id="ship_to_country" 	value="<?php echo $shopper->profile['shipping_country']; ?>"  />
    <input type="hidden" name="freight" 			id="freight" 			value="<?php echo $order->order_shipping->cost; ?>"  />
    
    

 <?php } ?>
    
 <div>
		<h3><?php echo $this->params->get('title',JText::_('PLG_MYMUSE_AUTHORIZENET_TITLE')); ?></h3>
        <table>
        <tr>
            <td>
            <label for="CREDITCARDTYPE" class="control-label">
                <?php echo JText::_('PLG_MYMUSE_AUTHORIZENET_FORM_CCTYPE') ?>
            </label></td>

            <td>
            <div class="">
                <select id="CREDITCARDTYPE" name="CREDITCARDTYPE" class="input-medium">
                    <option value="Visa">Visa</option>
                    <option value="MasterCard">Master Card</option>
                </select>
            </div></td>
        </tr>
        <tr> 
            <td><label for="card_num" class="control-label" >
                <?php echo JText::_('PLG_MYMUSE_AUTHORIZENET_FORM_CC') ?>
            </label></td>
    
            <td>
            <div class="">
                <input type="text" name="card_num" id="card_num" class="input-medium" />
            </div></td>
        </tr>
        <tr> 
            <td><label for="exp_date" class="control-label" >
                <?php echo JText::_('PLG_MYMUSE_AUTHORIZENET_FORM_EXPDATE') ?>
            </label></td>
            <td>
            <div class="">
                <?php echo $this->selectExpirationDate() ?>
            </div></td>
        </tr>
        <tr> 
            <td>
            <label for="cardCode" class="control-label" >
                <?php echo JText::_('PLG_MYMUSE_AUTHORIZENET_FORM_CVV') ?>
            </label></td>
            <td>
            <div class="">
                <input type="text" name="cardCode" id="cardCode" class="input-mini" />
            </div></td>
       </tr>
        <tr> 
            <td></td>
  
            <td>
            <div class="">
                <input type="submit" class="button" name="submit" value="<?php echo JText::_('PLG_MYMUSE_AUTHORIZENET_FORM_SUBMIT') ?>" alt="Authorize.net" />
            </div></td>
       </tr>
        </table>
    </form>
</div>



