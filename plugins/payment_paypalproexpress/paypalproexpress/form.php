<?php defined('_JEXEC') or die(); ?>

<h3><?php echo JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_REDIRECTING_HEADER') ?></h3>
<p><?php echo JText::_('PLG_MYMUSE_PAYPALPROEXPRESS_REDIRECTING_BODY') ?></p>
<p align="center">
<form action="<?php echo $data['URL'] ?>" method="post" id="paymentForm" >
	<input type="submit" class="btn" />
</form>
</p>