<?php defined('_JEXEC') or die(); 
print_pre($data);

?>

<h3><?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_HEADER') ?></h3>
<div class="row-fluid">
<div class="span12">
<br />
<form action="<?php echo $data->URL ?>" method="post" class="form form-horizontal">
	<input type="hidden" name="METHOD" value="<?php echo $data->METHOD ?>" />
	<input type="hidden" name="USER" value="<?php echo $data->USER ?>" />
	<input type="hidden" name="PWD" value="<?php echo $data->PWD ?>" />
	<input type="hidden" name="SIGNATURE" value="<?php echo $data->SIGNATURE ?>" />
	<input type="hidden" name="VERSION" value="<?php echo $data->VERSION ?>" />
	<input type="hidden" name="PAYMENTACTION" value="<?php echo $data->PAYMENTACTION ?>" />
	<input type="hidden" name="IPADDRESS" value="<?php echo $data->IPADDRESS ?>" />
	<input type="hidden" name="mode" value="init" />
    
	<input type="hidden" name="AMT" value="<?php echo $data->AMT ?>" />
	<input type="hidden" name="ITEMAMT" value="<?php echo $data->ITEMAMT ?>" />
	<input type="hidden" name="TAXAMT" value="<?php echo $data->TAXAMT ?>" />
	<input type="hidden" name="CURRENCYCODE" value="<?php echo $data->CURRENCYCODE ?>" />
	<input type="hidden" name="DESC" value="<?php echo $data->DESC ?>" />
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
					<input type="hidden" name="<?php echo $item_name; ?>" value="<?php $data->$item_name?>" />
					<input type="hidden" name="<?php echo $quant_name; ?>" value="<?php $data->$quant_name?>" />
					<input type="hidden" name="<?php echo $amount_name; ?>" value="<?php $data->$amount_name?>" />
				<?php 
				}
		
	} ?>
	<input type="hidden" name="NOTIFYURL" value="<?php echo $data->NOTIFYURL ?>" />
    
<div id="container" style="width:100%; ">
    
    <div style="float:left;width:45%;">
        <div class="control-group">
            <label for="FIRSTNAME" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_FIRSTNAME') ?>
            </label>
            <div class="controls">
                <input type="text" name="FIRSTNAME" id="FIRSTNAME" class="input-medium" value="<?php echo $data->FIRSTNAME ?>" />
            </div>
        </div>
        <div class="control-group">
            <label for="LASTNAME" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_LASTNAME') ?>
            </label>
            <div class="controls">
                <input type="text" name="LASTNAME" id="LASTNAME" class="input-medium" value="<?php echo $data->LASTNAME ?>"  />
            </div>
        </div>
        <div class="control-group">
            <label for="STREET" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_STREET') ?>
            </label>
            <div class="controls">
                <input type="text" name="STREET" id="STREET" class="input-medium" value="<?php echo $data->STREET ?>"  />
            </div>
        </div>
        <div class="control-group">
            <label for="STREET2" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_STREET2') ?>
            </label>
            <div class="controls">
                <input type="text" name="STREET2" id="STREET2" class="input-medium" value="<?php echo $data->STREET2 ?>"  />
            </div>
        </div>
        <div class="control-group">
            <label for="CITY" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CITY') ?>
            </label>
            <div class="controls">
                <input type="text" name="CITY" id="CITY" class="input-medium" value="<?php echo $data->CITY ?>"  />
            </div>
        </div>
        <div class="control-group">
            <label for="STATE" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_STATE') ?>
            </label>
            <div class="controls">
                <input type="text" name="LASTNAME" id="STATE" class="input-mini" value="<?php echo $data->STATE ?>"  />
            </div>
        </div>
        <div class="control-group">
            <label for="COUNTRYCODE" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_COUNTRYCODE') ?>
            </label>
            <div class="controls">
                <input type="text" name="COUNTRYCODE" id="COUNTRYCODE" class="input-mini" value="<?php echo $data->COUNTRYCODE ?>"  />
            </div>
        </div>
        <div class="control-group">
            <label for="ZIP" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_ZIP') ?>
            </label>
            <div class="controls">
                <input type="text" name="ZIP" id="ZIP" class="input-mini" value="<?php echo $data->ZIP ?>"  />
            </div>
        </div>
        
    </div>
    <div style="float:right;width:45%;">
        <div class="control-group">
            <label for="CREDITCARDTYPE" class="control-label" style="width: 170px; margin-right: 5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CCTYPE') ?>
            </label>
            <div class="controls">
                <select id="CREDITCARDTYPE" name="CREDITCARDTYPE" class="input-medium">
                    <option value="Visa">Visa</option>
                    <option value="MasterCard">Master Card</option>
                    <option value="Discover">Discover</option>
                    <option value="Amex">American Express</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label for="ACCT" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CC') ?>
            </label>
            <div class="controls">
                <input type="text" name="ACCT" id="ACCT" class="input-medium" />
            </div>
        </div>
        <div class="control-group">
            <label for="EXPDATE" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_EXPDATE') ?>
            </label>
            <div class="controls">
                <?php echo $this->selectExpirationDate() ?>
            </div>
        </div>
        <div class="control-group">
            <label for="CVV2" class="control-label" style="width:140px; margin-right:5px;">
                <?php echo JText::_('PLG_MYMUSE_PAYPALPAYMENTSPRO_FORM_CVV') ?>
            </label>
            <div class="controls">
                <input type="text" name="CVV2" id="CVV2" class="input-mini" />
            </div>
        </div>
        <div class="control-group">
            <label for="CVV2" class="control-label" style="width:140px; margin-right:5px;">
            </label>
            <div class="controls">
                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" style="width:68px; height:23px;" border="0" name="submit" alt="Paypal Payments Pro" />
            </div>
        </div>
    </div>
</div>
</form>
</div>
</div>
