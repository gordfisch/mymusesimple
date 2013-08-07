<fieldset class="adminform form-horizontal">

	<legend><?php echo JText::_('MYMUSE_IMAGES') ?></legend>
	<div class="pull-left span5">

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('list_image'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('list_image'); ?>
				<?php if($this->item->list_image && file_exists(JPATH_ROOT.DS.$this->item->list_image)){?>
					<img src="<?php  echo JURI::root().DS.$this->item->list_image; ?>" />
				<?php } ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('detail_image'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('detail_image'); ?>
				<?php if($this->item->list_image && file_exists(JPATH_ROOT.DS.$this->item->detail_image)){?>
					<img src="<?php  echo JURI::root().DS.$this->item->detail_image; ?>" />
				<?php } ?>
			</div>
		</div>
</fieldset>