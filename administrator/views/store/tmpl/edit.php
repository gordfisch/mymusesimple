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

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');


?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'store.cancel' || document.formvalidator.isValid(document.id('store-form'))) {
			Joomla.submitform(task, document.getElementById('store-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&view=store&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="store-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MYMUSE_LEGEND_STORE'); ?></legend>
			<ul class="adminformlist">

            
			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>
			<li><?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?></li>
            <li><?php echo $this->form->getLabel('alias'); ?>
                    <?php echo $this->form->getInput('alias'); ?></li>
			<li><?php echo $this->form->getLabel('my_catid'); ?>
                    <?php echo $this->form->getInput('my_catid'); ?></li>
            <li><?php echo $this->form->getLabel('state'); ?>
                    <?php echo $this->form->getInput('state'); ?></li>
            <li><?php echo $this->form->getLabel('checked_out'); ?>
                    <?php echo $this->form->getInput('checked_out'); ?></li>
            <li><?php echo $this->form->getLabel('checked_out_time'); ?>
                    <?php echo $this->form->getInput('checked_out_time'); ?></li>
            </ul>
		</fieldset>
		<fieldset class="adminform">
		<legend><?php echo $this->form->getLabel('description'); ?></legend>
		<?php echo $this->form->getInput('description'); ?>
		</fieldset>
		<fieldset class="adminform">
		<legend><?php echo JText::_('MYMUSE_EDIT_CSS'); ?></legend>
		<textarea cols="220" rows="80" name="mymuse_css" id="mymuse_css" style="width:100%"><?php echo $this->css; ?></textarea>
		</fieldset>
	</div>
	<div class="width-40 fltrt">

	<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php $fieldSets = $this->form->getFieldsets('params'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options'); ?>
				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
				<?php endif; ?>
				<fieldset class="panelform">
					<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<li><?php echo $field->label; ?>
						<?php echo $field->input; ?></li>
					<?php endforeach; ?>
					</ul>
					<?php if($name == "testing"){ 
						$url = preg_replace("#administrator/#","",JURI::base())."components".DS."com_mymuse".DS."log.txt";
					echo '<br /><a target="_blank" href="'.$url.'">'.JText::_("MYMUSE_VIEW_LOG").'</a>';
						
					}?>
				</fieldset>
			<?php endforeach; ?>

			<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
			<fieldset class="panelform">
				<?php echo $this->loadTemplate('metadata'); ?>
			</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
	</div>
</form>