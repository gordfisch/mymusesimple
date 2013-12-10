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

// Load tooltips behavior
JHtml::_('behavior.switcher');
JHtml::_('behavior.multiselect');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
jimport ('joomla.html.html.bootstrap');

$lists = $this->lists;

$startOffset = 'details';
if($lists['subtype'] == "file"){
	$startOffset = 'tracks';
}
if($lists['subtype'] == "item"){
	$startOffset = 'items';
}

$app = JFactory::getApplication();
$input = $app->input;

$assoc = isset($app->item_associations) ? $app->item_associations : 0;

?>

<script type="text/javascript">




	Joomla.submitbutton = function(task)
	{
		if (task == 'product.cancel' || document.formvalidator.isValid(document.id('product-form'))) {
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task, document.getElementById('product-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

</script>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>"
	id="product-form" method="post" name="adminForm" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => $startOffset)); ?>

			
				
				
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('MYMUSE_DETAILS', true)); ?>
					<?php echo $this->loadTemplate('details'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
		
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'images', JText::_('MYMUSE_IMAGES', true)); ?>
					<?php echo $this->loadTemplate('images'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'recording', JText::_('MYMUSE_RECORDING_DETAILS', true)); ?>
					<?php echo $this->loadTemplate('recording'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('MYMUSE_PUBLISHING', true)); ?>
					<?php echo $this->loadTemplate('publishing'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('MYMUSE_PARAMETERS_METADATA', true)); ?>
					<?php echo $this->loadTemplate('metadata');  ?>
					
					<?php if(isset($this->item->alias) && $this->item->alias != ""){ ?>
						<input type="hidden" name="old_alias" value="<?php echo $this->item->alias;?>" />
					<?php }?>
					<?php if(isset($this->item->catid) && $this->item->catid != ""){ ?>
						<input type="hidden" name="old_catid"
						value="<?php echo $this->item->catid;?>" />
					<?php } ?>
					<input type="hidden" name="task" value="" /> 
					<input type="hidden" name="subtype" value="details" /> 
					<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
					<?php echo JHtml::_('form.token'); ?>
					</form>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tracks', '<b>'.strtoupper(JText::_('MYMUSE_TRACKS', true))).'</b>'; ?>
				<?php 
					if(!$this->item->id){
						echo JText::_('MYMUSE_SAVE_THEN_ADD_TRACKS');
					}else{
						echo $this->loadTemplate('listtracks');
					} ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'items', '<b>'.strtoupper(JText::_('MYMUSE_ITEMS', true))).'</b>'; ?>
					<?php 
					if(!$this->item->id){
						echo JText::_('MYMUSE_SAVE_THEN_ADD_ITEMS');
					}else{
						echo $this->loadTemplate('listitems');
					} ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				
				

				
			
		</div>
	</div>
