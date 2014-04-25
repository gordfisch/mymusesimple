<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'coupon.cancel' || document.formvalidator.isValid(document.id('coupon-form'))) {
			Joomla.submitform(task, document.getElementById('coupon-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<h3><?php echo JText::_('COM_MYMUSE_LEGEND_COUPON'); ?></h3>
<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" method="post" 
method="post" name="adminForm" id="coupon-form" class="form-validate">
<div class="adminform form-horizontal">
	<div class="span5 pull-left">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('code'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('code'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('coupon_type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('coupon_type'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_product_id-lbl" for="jform_product_id" 
			class="hasTip" 
			title="<?php echo JText::_('MYMUSE_COUPON_PRODUCT').": ".JText::_('MYMUSE_COUPON_PRODUCT_TIP'); ?>">
			<?php echo JText::_('MYMUSE_COUPON_PRODUCT'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['products']; ?>	
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('state'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('state'); ?>
			</div>
		</div>
		

	</div>
	<div class="span5 pull-left"> 
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('coupon_value'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('coupon_value'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_currency_id-lbl" for="jform_currency_id" 
			class="hasTip" 
			title="<?php echo JText::_('MYMUSE_COUPON_CURRENCY').": ".JText::_('MYMUSE_COUPON_CURRENCY_TIP'); ?>">
			<?php echo JText::_('MYMUSE_COUPON_CURRENCY'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['currency']; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('coupon_value_type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('coupon_value_type'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('coupon_max_uses'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('coupon_max_uses'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('coupon_max_uses_per_user'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('coupon_max_uses_per_user'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('start_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('start_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('expiration_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('expiration_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('descriptio'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('descriptio'); ?>
			</div>
		</div> 

			<?php echo $this->form->getInput('checked_out'); ?>
            <?php echo $this->form->getInput('checked_out_time'); ?>
	</div>
</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>