
		<fieldset class="adminform form-horizontal">

			<legend><?php echo empty($this->item->id) ? JText::_('MYMUSE_NEW_PRODUCT') : JText::sprintf('MYMUSE_EDIT_PRODUCT', $this->item->id); ?></legend>
			<div class="pull-left span5">

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
					<?php echo $this->form->getLabel('catid'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('catid'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label id="jform_preview_list-lbl" for="jform_preview_list"  class="hasTip" title="<?php echo JText::_("MYMUSE_SELECT_OTHER_CATS")?>">
				<?php echo JText::_( 'MYMUSE_SELECT_OTHER_CATS' ); ?></label>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('othercats'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_sku'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_sku'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_physical'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_physical'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_in_stock'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_in_stock'); ?>
				</div>
			</div>

			</div>

			<div class="pull-right span5">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('price'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('price'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_discount'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_discount'); ?>
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
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('featured'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('featured'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('language'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('language'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('id'); ?>
				</div>
			</div>
			</div>
			
			
			
			<div style="clear:both"> </div>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div style="clear:both"> </div>
			<?php echo $this->form->getInput('articletext'); ?>
		</fieldset>







