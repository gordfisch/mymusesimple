
		<fieldset class="adminform">

			<legend><?php echo empty($this->item->id) ? JText::_('MYMUSE_NEW_PRODUCT') : JText::sprintf('MYMUSE_EDIT_PRODUCT', $this->item->id); ?></legend>
			<div class="pull-left">
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?></li>
				
				<li><label id="jform_preview_list-lbl" for="jform_preview_list"  class="hasTip" title="<?php echo JText::_("MYMUSE_SELECT_OTHER_CATS")?>">
				<?php echo JText::_( 'MYMUSE_SELECT_OTHER_CATS' ); ?></label>
				<?php echo $this->form->getInput('othercats'); ?> </li>
				
				<li><?php echo $this->form->getLabel('product_sku'); ?>
				<?php echo $this->form->getInput('product_sku'); ?></li>
				
				<li><?php echo $this->form->getLabel('product_physical'); ?>
				<?php echo $this->form->getInput('product_physical'); ?></li>
				
				<li><?php echo $this->form->getLabel('product_in_stock'); ?>
				<?php echo $this->form->getInput('product_in_stock'); ?></li>
			</ul>
			</div>

			<div class="pull-right">
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('price'); ?>
				<?php echo $this->form->getInput('price'); ?></li>

				<li><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('featured'); ?>
				<?php echo $this->form->getInput('featured'); ?></li>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			</ul>
			</div>
			
			
			
			<div style="clear:both"> </div>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div style="clear:both"> </div>
			<?php echo $this->form->getInput('articletext'); ?>
		</fieldset>







