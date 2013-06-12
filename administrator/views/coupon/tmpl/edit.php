<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
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

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="coupon-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MYMUSE_LEGEND_COUPON'); ?></legend>
			<ul class="adminformlist">

            
			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>
			<li><?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?></li>
			<li><?php echo $this->form->getLabel('code'); ?>
			<?php echo $this->form->getInput('code'); ?></li>
			<li><?php echo $this->form->getLabel('coupon_type'); ?>
			<?php echo $this->form->getInput('coupon_type'); ?></li>
			<li><label id="jform_product_id-lbl" for="jform_product_id" 
			class="hasTip" 
			title="<?php echo JText::_('MYMUSE_COUPON_PRODUCT').": ".JText::_('MYMUSE_COUPON_PRODUCT_TIP'); ?>">
			<?php echo JText::_('MYMUSE_COUPON_PRODUCT'); ?></label>
			<?php echo $this->lists['products']?>	
			
			<li><?php echo $this->form->getLabel('state'); ?>
                    <?php echo $this->form->getInput('state'); ?></li><li><?php echo $this->form->getLabel('checked_out'); ?>
                    <?php echo $this->form->getInput('checked_out'); ?></li><li><?php echo $this->form->getLabel('checked_out_time'); ?>
                    <?php echo $this->form->getInput('checked_out_time'); ?></li>
            <li><?php echo $this->form->getLabel('coupon_value'); ?>
			<?php echo $this->form->getInput('coupon_value'); ?></li>
			<li><label id="jform_currency_id-lbl" for="jform_currency_id" 
			class="hasTip" 
			title="<?php echo JText::_('MYMUSE_COUPON_CURRENCY').": ".JText::_('MYMUSE_COUPON_CURRENCY_TIP'); ?>">
			<?php echo JText::_('MYMUSE_COUPON_CURRENCY'); ?></label>
			<?php echo $this->lists['currency']?>
			
			<li><?php echo $this->form->getLabel('coupon_value_type'); ?>
			<?php echo $this->form->getInput('coupon_value_type'); ?></li>
			<li><?php echo $this->form->getLabel('coupon_max_uses'); ?>
			<?php echo $this->form->getInput('coupon_max_uses'); ?></li>
			<li><?php echo $this->form->getLabel('coupon_max_uses_per_user'); ?>
			<?php echo $this->form->getInput('coupon_max_uses_per_user'); ?></li>
			<li><?php echo $this->form->getLabel('start_date'); ?>
			<?php echo $this->form->getInput('start_date'); ?></li>
			<li><?php echo $this->form->getLabel('expiration_date'); ?>
			<?php echo $this->form->getInput('expiration_date'); ?></li>
			<li><?php echo $this->form->getLabel('description'); ?>
			<?php echo $this->form->getInput('description'); ?></li>

            

            </ul>
		</fieldset>
	</div>


	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>