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
<h3><?php echo JText::_('COM_MYMUSE_LEGEND_TAXRATE'); ?></h3>
<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" 
method="post" name="adminForm" id="taxrate-form" class="form-validate">
<div class="row-fluid">
	<div class="span10 form-horizontal">
		<div class="span5 pull-left">

			<?php if($this->form->getValue('tax_name') != ''){
				?><input type="hidden" name="jform[old_tax_name]" value="<?php echo $this->form->getValue('tax_name'); ?>">
			<?php } ?>
			
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
					<?php echo $this->form->getLabel('tax_name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('tax_name'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('tax_rate'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('tax_rate'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('tax_applies_to'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('tax_applies_to'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('country'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('country'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('province'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('province'); ?>
				</div>
			</div>
		</div>
		<div class="span5 pull-left">	

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('compounded'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('compounded'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('ordering'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('ordering'); ?>
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
	</div>
</div>
<?php echo $this->form->getInput('checked_out'); ?>
<?php echo $this->form->getInput('checked_out_time'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>