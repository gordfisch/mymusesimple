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
		if (task == 'shoppergroup.cancel' || document.formvalidator.isValid(document.id('shoppergroup-form'))) {
			Joomla.submitform(task, document.getElementById('shoppergroup-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="shoppergroup-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MYMUSE_LEGEND_SHOPPERGROUP'); ?></legend>
			<ul class="adminformlist">

            
			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>

            

            <li><?php echo $this->form->getLabel('state'); ?>
            <?php echo $this->form->getInput('state'); ?></li>
            
            <li><?php echo $this->form->getLabel('shopper_group_name'); ?>
            <?php echo $this->form->getInput('shopper_group_name'); ?></li>
            
            <li><?php echo $this->form->getLabel('shopper_group_description'); ?>
            <?php echo $this->form->getInput('shopper_group_description'); ?></li>
            
            <li><?php echo $this->form->getLabel('discount'); ?>
            <?php echo $this->form->getInput('discount'); ?></li>
            
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