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

// Create shortcut to parameters.
//$params = $this->state->get('params');
$params = $this->params->toArray();
$app = JFactory::getApplication();
$input = $app->input;

$assoc = isset($app->item_associations) ? $app->item_associations : 0;


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
<style>
#jform_params_my_formats_chzn, #jform_params_my_formats-lbl { display: none; }
</style>
<form action="<?php echo JRoute::_('index.php?option=com_mymuse&view=store&layout=edit&id='.(int) $this->item->id); ?>" 
method="post" name="adminForm" id="store-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_MYMUSE_LEGEND_STORE', true)); ?>
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
					<?php echo $this->form->getLabel('title'); ?>
				</div>
                <div class="controls">
                    <?php echo $this->form->getInput('title'); ?>
               </div>
            </div>
            <div class="control-group">
            	<div class="control-label">
            		<?php echo $this->form->getLabel('alias'); ?>
            	</div>
                <div class="controls">
                	<?php echo $this->form->getInput('alias'); ?>
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

		<fieldset class="adminform">
		<legend><?php echo $this->form->getLabel('description'); ?></legend>

			<?php if(!$this->form->getInput('description')){
				echo $this->form->getError();
			 
			}
			echo $this->form->getInput('description');
			?>
		</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'css', JText::_('MYMUSE_EDIT_CSS', true)); ?>
		<fieldset class="adminform">
			<textarea cols="220" rows="80" name="mymuse_css" id="mymuse_css" style="width:100%"><?php echo $this->css; ?></textarea>
		</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		


		<?php $fieldSets = $this->form->getFieldsets('params'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
			
			  <?php echo JHtml::_('bootstrap.addTab','myTab', $name, JText::_($fieldSet->label), true); ?>
				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
				<?php endif; ?>
				<div class="span6">
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<?php 
						echo $field->renderField(); ?>
									
									<?php if($field->name == "jform[params][my_show_cart_player]" || $field->name == "jform[params][address_2]") :
									?></div><div class="span6 float-right"><?php
									endif;
									
									endforeach; ?>
					<?php if($name == "testing"){ 
						$url = preg_replace("#administrator/#","",JURI::base())."components".DS."com_mymuse".DS."log.txt";
						echo '<div><a target="_blank" href="'.$url.'">'.JText::_("MYMUSE_VIEW_LOG").'</a></div>';
						
					}?>
					 
				</div>
			  <?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endforeach; ?>
<!--  
			<?php //echo JHtml::_('bootstrap.addTab','myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), true); ?>
				<?php //echo $this->loadTemplate('metadata'); ?>
			<?php //echo JHtml::_('bootstrap.endTab'); ?>
-->
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo $this->form->getInput('checked_out'); ?>
        <?php echo $this->form->getInput('checked_out_time'); ?>
		<?php echo JHtml::_('form.token'); ?>
	  </div>
	</div>
</form>