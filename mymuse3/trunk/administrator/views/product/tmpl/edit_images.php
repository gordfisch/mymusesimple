<fieldset class="adminform">

	<legend><?php echo JText::_('MYMUSE_IMAGES') ?></legend>
	<ul class="adminformlist">
		<li><?php echo $this->form->getLabel('list_image'); ?>
		<?php echo $this->form->getInput('list_image'); ?>
		<?php if($this->item->list_image && file_exists(JPATH_ROOT.DS.$this->item->list_image)){?>
			<img src="<?php  echo JURI::root().DS.$this->item->list_image; ?>" />
		<?php } ?>
		</li>
		<li><?php echo $this->form->getLabel('detail_image'); ?>
		<?php echo $this->form->getInput('detail_image'); ?>
		<?php if($this->item->list_image && file_exists(JPATH_ROOT.DS.$this->item->detail_image)){?>
			<img src="<?php  echo JURI::root().DS.$this->item->detail_image; ?>" />
		<?php } ?>
		</li>
	</ul>
</fieldset>