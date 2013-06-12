<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>

<fieldset class="adminform">

	<legend><?php echo JText::_( 'MYMUSE_OTHER_CATS' ); ?></legend>
		<ul class="adminformlist">
			<li><label id="jform_preview_list-lbl" for="jform_preview_list"  class="hasTip" title="<?php echo JText::_("MYMUSE_SELECT_OTHER_CATS")?>">
				<?php echo JText::_( 'MYMUSE_SELECT_OTHER_CATS' ); ?></label>
				<?php echo $this->form->getInput('othercats'); ?> </li>
		</ul>
</fieldset>

<div class="clr"></div>


	<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
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
				</fieldset>
			<?php endforeach; ?>


		
			
		<?php echo JHtml::_('sliders.panel',JText::_("Publishing"), 'pub-options'); ?>
		<fieldset class="panelform">

				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('created_by'); ?>
					<?php echo $this->form->getInput('created_by'); ?></li>

					<li><?php echo $this->form->getLabel('created_by_alias'); ?>
					<?php echo $this->form->getInput('created_by_alias'); ?></li>

					<li><?php echo $this->form->getLabel('created'); ?>
					<?php echo $this->form->getInput('created'); ?></li>

					<li><?php echo $this->form->getLabel('publish_up'); ?>
					<?php echo $this->form->getInput('publish_up'); ?></li>

					<li><?php echo $this->form->getLabel('publish_down'); ?>
					<?php echo $this->form->getInput('publish_down'); ?></li>

					<?php if ($this->item->modified_by) : ?>
						<li><?php echo $this->form->getLabel('modified_by'); ?>
						<?php echo $this->form->getInput('modified_by'); ?></li>

						<li><?php echo $this->form->getLabel('modified'); ?>
						<?php echo $this->form->getInput('modified'); ?></li>
					<?php endif; ?>

					<?php if ($this->item->version) : ?>
						<li><?php echo $this->form->getLabel('version'); ?>
						<?php echo $this->form->getInput('version'); ?></li>
					<?php endif; ?>

					<?php if ($this->item->hits) : ?>
						<li><?php echo $this->form->getLabel('hits'); ?>
						<?php echo $this->form->getInput('hits'); ?></li>
					<?php endif; ?>
				</ul>
		</fieldset>
		
<?php echo JHtml::_('sliders.panel',JText::_('MYMUSE_PARAMETERS_METADATA'), 'mets-options'); ?>			
<fieldset class="panelform">
		<ul class="adminformlist">
			<?php
			foreach ($this->form->getFieldset('metadata') as $field):
			?>
					<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
			<?php
			endforeach;
			?>
			</ul>
</fieldset>

<?php echo JHtml::_('sliders.end'); ?>
