<?php defined('_JEXEC') or die(); 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */
 

?>
<form action="<?php echo $data->URL ?>" method="post" class="form form-horizontal">
	<input type="hidden" name="METHOD" value="<?php echo $data->METHOD ?>" />
	<input type="hidden" name="USER" value="<?php echo $data->USER ?>" />
	<input type="hidden" name="PWD" value="<?php echo $data->PWD ?>" />
	<input type="hidden" name="SIGNATURE" value="<?php echo $data->SIGNATURE ?>" />
	<input type="hidden" name="VERSION" value="<?php echo $data->VERSION ?>" />
	<input type="hidden" name="PAYMENTACTION" value="<?php echo $data->PAYMENTACTION ?>" />
	<input type="hidden" name="IPADDRESS" value="<?php echo $data->IPADDRESS ?>" />
	<input type="hidden" name="mode" value="init" />
	<input type="hidden" name="BUTTONSOURCE" value="<?php echo $data->BUTTONSOURCE ?>" />
	
	<input type="hidden" name="num_cart_items" value="<?php echo $data->ITEMS ?>" />
    
	<input type="hidden" name="AMT" value="<?php echo $data->AMT ?>" />
	
	<input type="hidden" name="ITEMAMT" value="<?php echo $data->ITEMAMT ?>" />
	<input type="hidden" name="TAXAMT" value="<?php echo $data->TAXAMT ?>" />
	<input type="hidden" name="CURRENCYCODE" value="<?php echo $data->CURRENCYCODE ?>" />
	<input type="hidden" name="DESC" value="<?php echo $data->DESC ?>" />
	<input type="hidden" name="SHIPPINGAMT" value="<?php echo $data->SHIPPINGAMT ?>" />
	
	<?php if(! empty($data->INVNUM)) { ?>
	<input type="hidden" name="INVNUM" value="<?php echo $data->INVNUM ?>" />
	<?php } ?>
	<?php if(! empty($data->PROFILEREFERENCE)) { ?>
	<input type="hidden" name="PROFILEREFERENCE" value="<?php echo $data->PROFILEREFERENCE ?>" />
	<?php } ?>
	<?php if(! empty($data->BILLINGPERIOD)) { ?>
	<input type="hidden" name="BILLINGPERIOD" value="<?php echo $data->BILLINGPERIOD ?>" />
	<?php } ?>
	<?php if(! empty($data->BILLINGFREQUENCY)) { ?>
	<input type="hidden" name="BILLINGFREQUENCY" value="<?php echo $data->BILLINGFREQUENCY ?>" />
	<?php } ?>
	<?php if($data->ITEMS){ 
				for($i = 0; $i < $data->ITEMS; $i++){
					
					$item_name = 'L_NAME'. $i;
					$quant_name = 'L_QTY'. $i;
					$amount_name = 'L_AMT'. $i;
					?>
					<input type="hidden" name="<?php echo $item_name; ?>" value="<?php echo $data->$item_name?>" />
					<input type="hidden" name="<?php echo $quant_name; ?>" value="<?php echo $data->$quant_name?>" />
					<input type="hidden" name="<?php echo $amount_name; ?>" value="<?php echo $data->$amount_name?>" />
				<?php 
				}
		
	} ?>
	<input type="hidden" name="NOTIFYURL" 	id="NOTIFYURL" 		value="<?php echo $data->NOTIFYURL ?>" />
    <input type="hidden" name="FIRSTNAME" 	id="FIRSTNAME" 		value="<?php echo $data->FIRSTNAME ?>" />
    <input type="hidden" name="LASTNAME" 	id="LASTNAME"    	value="<?php echo $data->LASTNAME ?>"  />
    <input type="hidden" name="EMAIL" 		id="EMAIL"  		value="<?php echo $data->EMAIL ?>" />
    <input type="hidden" name="STREET" 		id="STREET"  		value="<?php echo $data->STREET ?>"  />
    <input type="hidden" name="STREET2" 	id="STREET2" 		value="<?php echo $data->STREET2 ?>"  />
    <input type="hidden" name="COUNTRYCODE" id="COUNTRYCODE" 	value="<?php echo $data->COUNTRYCODE ?>"  />
    <input type="hidden" name="CITY" 		id="CITY" 			value="<?php echo $data->CITY ?>"  />
    <input type="hidden" name="STATE" 		id="STATE" 			value="<?php echo $data->STATE ?>"  />
    <input type="hidden" name="ZIP" 		id="ZIP" 			value="<?php echo $data->ZIP ?>"  />
    

<?php if($data->SHIPPINGAMT > 0){ ?>   
    <input type="hidden" name="SHIPTONAME" 		id="SHIPTONAME" 	value="<?php echo $data->SHIPTONAME ?>" />
    <input type="hidden" name="SHIPTOSTREET" 	id="SHIPTOSTREET"  	value="<?php echo $data->SHIPTOSTREET ?>" />
    <input type="hidden" name="SHIPTOSTREET2" 	id="SHIPTOSTREET2"  value="<?php echo $data->SHIPTOSTREET2?>"  />
    <input type="hidden" name="SHIPTOCITY" 		id="SHIPTOCITY" 	value="<?php echo $data->SHIPTOCITY ?>"  />
    <input type="hidden" name="SHIPTOSTATE" 	id="SHIPTOSTATE" 	value="<?php echo $data->SHIPTOSTATE ?>"  />
    <input type="hidden" name="SHIPTOZIP" 		id="SHIPTOZIP" 		value="<?php echo $data->SHIPTOZIP ?>"  />
    <input type="hidden" name="SHIPTOCOUNTRY" 	id="SHIPTOCOUNTRY" 	value="<?php echo $data->SHIPTOCOUNTRY ?>"  />
    <input type="hidden" name="ZIP" 			id="ZIP" 			value="<?php echo $data->ZIP ?>"  />
 <?php } ?>
    
    <div style="float:left;width:80%;">
		<h4><?php echo JText::_('MYMUSE_PAYMENT_METHOD'); ?></h4>
        <table>
        <tr>
            <td>
            <label for="CREDITCARDTYPE" class="control-label" style="width: 170px; margin-right: 5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CCTYPE') ?>
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
            <td><label for="ACCT" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CC') ?>
            </label></td>
    
            <td>
            <div class="">
                <input type="text" name="ACCT" id="ACCT" class="input-medium" />
            </div></td>
       </tr>
        <tr> 
            <td><label for="EXPDATE" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_EXPDATE') ?>
            </label></td>
            <td>
            <div class="">
                <?php echo $this->selectExpirationDate() ?>
            </div></td>
       </tr>
        <tr> 
            <td>
            <label for="CVV2" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CVV') ?>
            </label></td>
            <td>
            <div class="">
                <input type="text" name="CVV2" id="CVV2" class="input-mini" />
            </div></td>
       </tr>
        <tr> 
            <td></td>
  
            <td>
            <div class="">
                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" style="width:68px; height:23px;" name="submit" alt="Paypal Payments Pro" />
            </div></td>
       </tr>
        </table>
    </div>
</div>
</form>


