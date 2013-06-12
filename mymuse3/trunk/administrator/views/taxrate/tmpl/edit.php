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
		if (task == 'taxrate.cancel' || document.formvalidator.isValid(document.id('taxrate-form'))) {
			Joomla.submitform(task, document.getElementById('taxrate-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="taxrate-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MYMUSE_LEGEND_TAXRATE'); ?></legend>
			<?php if($this->form->getValue('tax_name') != ''){
				?><input type="hidden" name="jform[old_tax_name]" value="<?php echo $this->form->getValue('tax_name'); ?>">
			<?php } ?>
				
			<ul class="adminformlist">

            
			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>
			<li><?php echo $this->form->getLabel('tax_name'); ?>
			<?php echo $this->form->getInput('tax_name'); ?></li>
			<li><?php echo $this->form->getLabel('tax_rate'); ?>
			<?php echo $this->form->getInput('tax_rate'); ?></li>
			<li><?php echo $this->form->getLabel('tax_applies_to'); ?>
			<?php echo $this->form->getInput('tax_applies_to'); ?></li>
			<li><?php echo $this->form->getLabel('country'); ?>
			<?php echo $this->form->getInput('country'); ?></li>
			<li><?php echo $this->form->getLabel('province'); ?>
			<?php echo $this->form->getInput('province'); ?></li>
			<li><?php echo $this->form->getLabel('compounded'); ?>
			<?php echo $this->form->getInput('compounded'); ?></li>
			<li><?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?></li>
			

         

            <li><?php echo $this->form->getLabel('state'); ?>
            <?php echo $this->form->getInput('state'); ?></li>
            <li><?php echo $this->form->getLabel('checked_out'); ?>
           <?php echo $this->form->getInput('checked_out'); ?></li>
           <li><?php echo $this->form->getLabel('checked_out_time'); ?>
           <?php echo $this->form->getInput('checked_out_time'); ?></li>

            </ul>
		</fieldset>
	</div>


	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>