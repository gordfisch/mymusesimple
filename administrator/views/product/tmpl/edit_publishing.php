	<div class="pull-left span5">

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('created_by'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('created_by'); ?>
			</div>
		</div><div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('created_by_alias'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('created_by_alias'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('created'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('created'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('publish_up'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('publish_up'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('publish_down'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('publish_down'); ?>
			</div>
		</div>
	<?php if ($this->item->modified_by) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('modified_by'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('modified_by'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('modified'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('modified'); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->item->version) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('version'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('version'); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->item->hits) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('hits'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('hits'); ?>
			</div>
		</div>
	<?php endif; ?>

</div>



