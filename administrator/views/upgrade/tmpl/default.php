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
		if (task == 'upgrade.importFromMymuse15') {
			Joomla.submitform(task, document.getElementById('upgrade-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse'); ?>" 
method="post" name="adminForm" id="upgrade-form">
	<div>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MYMUSE_TITLE_UPGRADE'); ?></legend>

	<h3>If you have not created any categories for 2.5 yet, you should go 
	<a href="index.php?option=com_categories&extension=com_mymuse">here</a> and create at least one category for MyMuse</h3>
	<table class="admingorm">
		<tr>
			<td colspan="3"><b>FROM OLD SECTIONS</b></td>
			<td colspan="2"><b>TO NEW CATEGORIES</b></td>
		
		</tr>
        <tr>
        
			<td><?php echo $this->form->getLabel('oldartistcat'); ?></td>
			<td><?php echo $this->form->getInput('oldartistcat'); ?></td>
			<td>Goes to: </td>
			<td><?php echo $this->form->getLabel('artistcat'); ?></td>
			<td><?php echo $this->form->getInput('artistcat'); ?></td>
			</tr>
			<tr>
			<td><?php echo $this->form->getLabel('oldgenrecat'); ?></td>
			<td><?php echo $this->form->getInput('oldgenrecat'); ?></td>
			<td>Goes to: </td>
			<td><?php echo $this->form->getLabel('genrecat'); ?></td>
			<td><?php echo $this->form->getInput('genrecat'); ?></td>
		</tr>

    </table>
		</fieldset>
	</div>


	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>