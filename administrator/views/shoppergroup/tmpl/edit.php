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

// Load the tooltip behavior.
JHtml::_('behavior.switcher');
JHtml::_('behavior.multiselect');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'shoppergroup.cancel' || document.formvalidator.isValid(document.id('shoppergroup-form'))) {
			Joomla.submitform(task, document.getElementById('shoppergroup-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<H2><?php echo JText::_('COM_MYMUSE_LEGEND_SHOPPERGROUP'); ?></H2>
<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="shoppergroup-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6">
			
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
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('shopper_group_name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('shopper_group_name'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('shopper_group_description'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('shopper_group_description'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('discount'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('discount'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('checked_out'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('checked_out'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('checked_out_time'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('checked_out_time'); ?>
				</div>
			</div>
		</div>
	</div>


	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>