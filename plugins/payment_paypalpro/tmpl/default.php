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
	<input type="hidden" name="num_cart_items" value="<?php echo $data->ITEMS ?>" />
    
	<input type="hidden" name="AMT" value="<?php echo $data->AMT ?>" />
	<input type="hidden" name="CUSTOM" value="<?php echo $data->CUSTOM ?>" />
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
	<input type="hidden" name="NOTIFYURL" value="<?php echo $data->NOTIFYURL ?>" />
    
<div id="container" style="width:100%; ">
    
    <div style="float:left;width:45%;">
		<h4><?php echo JText::_('MYMUSE_BILLING_ADDRESS'); ?></h4>
		<table>
        <tr>
            <td><label for="FIRSTNAME" class="control-label" style="margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_FIRSTNAME') ?>
            </label></td>
        
        </tr>
        <tr>
           <td><div class="">
                <input type="text" name="FIRSTNAME" id="FIRSTNAME" size="10" value="<?php echo $data->FIRSTNAME ?>" />
            </div></td>
        
        </tr>
        </tr>
            <td colspan="2">
            <label for="LASTNAME" class="control-label" style="margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_LASTNAME') ?>
            </label></td>
         </tr>    
         
        
        </tr>
            <td colspan="2"><div class="">
                <input type="text" name="LASTNAME" id="LASTNAME"   size="10" value="<?php echo $data->LASTNAME ?>"  />
            </div></td>
        
        </tr>
        
        </tr>
            <td colspan="2"><label for="EMAIL" class="control-label" style="smargin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_EMAIL') ?>
            </label></td>
        </tr>
        </tr>
            <td colspan="2">
            
            
            <div class="">
                <input type="text" name="EMAIL" id="EMAIL"  value="<?php echo $data->EMAIL ?>" />
            </div></td>
        </tr>
        
        <tr>
            <td colspan="2"><label for="STREET" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_STREET') ?>
            </label></td>
        </tr>
        </tr>
            <td colspan="2">
            <div class="">
                <input type="text" name="STREET" id="STREET"  value="<?php echo $data->STREET ?>"  />
            </div></td>
        </tr>
        <tr>
            <td colspan="2">
				 <div class="">
                <input type="text" name="STREET2" id="STREET2" value="<?php echo $data->STREET2 ?>"  />
            </div></td>
        </tr>
        <tr>
            <td colspan="2">
            <label for="COUNTRYCODE" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_COUNTRYCODE') ?>
            </label></td>
        </tr>
        <tr> 
            <td colspan="2">
            <div class="">
               <?php  echo $COUNTRY_SELECT_HTML ?>
            </div></td>
        </tr>
        <tr> 
            <td colspan="2">
            <label for="CITY" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CITY') ?>
            </label></td>
        </tr>
            
        <tr> 
            <td colspan="2">
            <div class="">
                <input type="text" name="CITY" id="CITY" class="input-medium" value="<?php echo $data->CITY ?>"  />
            </div></td>
        </tr>
        
        <tr> 
            <td colspan="2">
            <label for="STATE" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_STATE') ?>
            </label></td>
        </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <?php  echo $STATE_SELECT_HTML ?>
            </div></td>
        </tr>
        <tr> 
            <td colspan="2">
            <label for="ZIP" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_ZIP') ?>
            </label></td>
        </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <input type="text" name="ZIP" id="ZIP" class="input-mini" value="<?php echo $data->ZIP ?>"  />
            </div></td>
        </tr>
        </table>
        
    </div>
    
    <div style="float:right;width:45%;">
		<h4><?php echo JText::_('MYMUSE_PAYMENT_METHOD'); ?></h4>
        <table>
        <tr>
            <td>
            <label for="CREDITCARDTYPE" class="control-label" style="width: 170px; margin-right: 5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CCTYPE') ?>
            </label></td>
       </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <select id="CREDITCARDTYPE" name="CREDITCARDTYPE" class="input-medium">
                    <option value="Visa">Visa</option>
                    <option value="MasterCard">Master Card</option>
                    <option value="Discover">Discover</option>
                    <option value="Amex">American Express</option>
                </select>
            </div></td>
       </tr>
        <tr> 
            <td colspan="2"><label for="ACCT" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CC') ?>
            </label></td>
       </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <input type="text" name="ACCT" id="ACCT" class="input-medium" />
            </div></td>
       </tr>
        <tr> 
            <td colspan="2"><label for="EXPDATE" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_EXPDATE') ?>
            </label></td>
       </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <?php echo $this->selectExpirationDate() ?>
            </div></td>
       </tr>
        <tr> 
            <td colspan="2">
            <label for="CVV2" class="control-label" style="width:110px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CVV') ?>
            </label></td>
       </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <input type="text" name="CVV2" id="CVV2" class="input-mini" />
            </div></td>
       </tr>
        <tr> 
            <td colspan="2"> <label for="CVV2" class="control-label" style="width:110px; margin-right:5px;">
            </label></td>
       </tr>
        <tr> 
            <td colspan="2">
            <div class="">
                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" style="width:68px; height:23px;" border="0" name="submit" alt="Paypal Payments Pro" />
            </div></td>
       </tr>
        </table>
    </div>
</div>
</form>


