<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$need_shipping = 0;
$need_closure = 0;
foreach ($this->form->getFieldsets() as $fieldset)
{
	if($fieldset->name == "shipping"){
		$need_shipping = 1;
		
	}
}

?>
<div class="registration<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

	<form id="guest-registration" action="<?php echo JRoute::_('index.php?option=com_mymuse'); ?>" method="post" class="form-validate">
	<input type="hidden" name="task" value="savenoreg">
	<?php if($need_shipping):?>
	<div style="float: left">
	<?php endif?>
<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($fieldset->name);?>
	
	<?php if (count($fields)):?>
	
	<?php if($fieldset->name == "address"): ?>
		<h4><?php echo JText::_('MYMUSE_ADDRESS');?></h4>
	<?php endif; ?>
	
	<?php if($fieldset->name == "shipping"): ?>
		</div><div style="float: left; margin-left: 20px;"><h4><?php echo JText::_('MYMUSE_SHIPPING');?></h4>
	<?php endif; ?>
	
	
		<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
		?>
			<legend><?php echo JText::_($fieldset->label);?></legend>
		<?php endif;?>
			<dl>
		<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
		
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				
			
				<dt>
					<?php echo $field->label; ?>
					<?php if (!$field->required && $field->type!='Spacer' && !preg_match("/shipping/",$field->name)): ?>
						<span class="optional"><?php echo JText::_('MYMUSE_OPTIONAL'); ?></span>
					<?php endif; ?>
				</dt>
				<dd><?php echo ($field->type!='Spacer') ? $field->input : "&#160;"; ?></dd>
				<?php if($field->name == "jform[profile][shipping_add_address]"):
					$need_closure = 1; 
					?>
					<div id="shipping_fields" style="display: none">
				<?php endif?>
			<?php endif;?>
		<?php endforeach;?>
		<?php if($need_closure):?>
		</div>
	<?php endif?>
		
			</dl>
		</fieldset>
	<?php endif;?>
<?php endforeach;?>
<?php if($need_shipping):?>
	</div>
<?php endif?>
		<div style="clear: both;">
			<button type="submit" class="button"><?php echo JText::_('JSAVE');?></button>
			&nbsp;
			<a href="<?php echo JRoute::_('');?>" title="<?php echo JText::_('JCANCEL');?>" class="button"><?php echo JText::_('JCANCEL');?></a>
			<input type="hidden" name="option" value="com_mymuse" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>

