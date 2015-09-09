<?php defined('_JEXEC') or die(); ?>
<div style="clear: both;">
<h3><?php 
echo $this->params->get('title',JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_REDIRECTING_HEADER')); 
  ?></h3>
<p><?php echo JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_REDIRECTING_BODY') ?></p>
<p align="center">
<form action="<?php echo $data['URL'] ?>" method="post" id="paymentForm" >
	
	<div id="paypalproexpress_form" class="pull-left">
		<button class="button uk-button " 
			type="submit" ><img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;"></button>
	</div>
</form>
</p>
</div>