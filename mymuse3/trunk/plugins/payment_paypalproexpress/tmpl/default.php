<?php defined('_JEXEC') or die(); ?>
<div style="clear: both;">
<h3><?php echo JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_REDIRECTING_HEADER') ?></h3>
<p><?php echo JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_REDIRECTING_BODY') ?></p>
<p align="center">
<form action="<?php echo $data['URL'] ?>" method="post" id="paymentForm" >
	<input type="submit" class="btn" />
	<div id="paypalproexpress_form" class="pull-left">
			<button class="button uk-button " 
			type="submit" >'. JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_TITLE').'</button>
		</div>
</form>
</p>
</div>