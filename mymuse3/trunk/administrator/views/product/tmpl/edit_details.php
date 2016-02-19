
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
					<?php echo $this->form->getLabel('artistid'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('artistid'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label id="jform_preview_list-lbl" for="jform_preview_list"  class="hasTip" title="<?php echo JText::_("MYMUSE_SELECT_OTHER_CATS")?>">
				<?php echo JText::_( 'MYMUSE_SELECT_OTHER_CATS' ); ?></label>
				</div>
				<div class="controls">
				<?php 
				if(!$this->item->id){
					echo JText::_( 'MYMUSE_SAVE_THEN_ADD_CATS' );
				}else{
					echo $this->form->getInput('othercats'); 
				}	
				?>
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
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_special'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_special'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('urls'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('urls'); ?>
				</div>
			</div>
<?php 	
$fieldSets = $this->form->getFieldsets('attribs'); 

foreach ($fieldSets as $name => $fieldSet) {

	foreach ($this->form->getFieldset($name) as $field)
	{
		if (preg_match("/media_/",$field->name))
		{
			echo $field->renderField();
		}

	} 
	
} 
?>			
			
			

			</div>
  
			<div class="pull-right span5">
			
<?php
 if( !$this->params->get('my_price_by_product')){ 
	//price by track and physical
	?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('price'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('price'); ?>
				</div>
			</div>


<?php 
}elseif(1 == $this->params->get('my_price_by_product')){ 
	$fieldSets = $this->form->getFieldsets('attribs'); 
	$physical = 0;
	foreach($this->params->get('my_formats') as $variation=>$format)
	{
	 	foreach ($fieldSets as $name => $fieldSet)
	 	{

			foreach ($this->form->getFieldset($name) as $field)
			{
				if (preg_match("/preorder/",$field->name) && !$physical)
				{
					echo $field->renderField();
				}
				if (preg_match("/physical/",$field->name) && !$physical)
				{
					echo $field->renderField();
					$physical++;
				}
				if (preg_match("/$format/",$field->name))
				{
					echo $field->renderField();
				}
			} 
	
 		} 
	}

 } ?>
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
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('recommend'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('recommend'); ?>
				</div>
			</div>
			
			
			</div>
			
			
			
			<div style="clear:both"> </div>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div style="clear:both"> </div>
			<?php echo $this->form->getInput('articletext'); ?>
		</fieldset>







