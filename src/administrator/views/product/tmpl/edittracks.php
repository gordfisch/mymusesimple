<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$item = $this->item;
$lists = $this->lists;
JHtml::_('behavior.switcher');
JHtml::_('behavior.multiselect');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
jimport ('joomla.html.html.bootstrap');
$editor = JFactory::getEditor();

JHTML::_('behavior.tooltip');


?>
		<script type="text/javascript">
		<!--

		function submitbutton(pressbutton)
		{

			var form = document.adminForm;

			if (pressbutton == 'cancelitem') {
				submitform( pressbutton );
				return;
			}

			// do field validation

			if (form.title.value == ""){
				alert( "<?php echo JText::_( 'MYMUSE_FILE_MUST_HAVE_A_TITLE', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}

		var variation = <?php  echo count($item->file_name); ?>;
		function addvariation()
		{
			
			row_number = "#row_"+variation;
			jQuery(row_number).removeClass('hidden');
			variation++;
			
			
		}

		function deletevariation (variationid){
			var form = document.adminForm;
			form.variation.value = variationid;
			//alert(variationid);
			submitform( 'product.deletevariation' );

		}
		//-->
		</script>
		<h2><?php echo empty($this->item->id) ? JText::_('MYMUSE_NEW_TRACK') : JText::_('MYMUSE_EDIT_TRACK'); ?> : <?php echo $item->title; ?></h2>

		<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">

<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

<!--  DETAILS TAB -->
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('MYMUSE_DETAILS', true)); ?>
<fieldset class="adminform">

	<legend><?php echo JText::_('MYMUSE_DETAILS'); ?></legend>
	<div class="pull-left span5">

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_type'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('file_type'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('alias'); ?>
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_sku'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('product_sku'); ?>
				</div>
			</div>
			
<?php


// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $this->form->getFieldsets('attribs');
?>

<?php foreach ($fieldSets as $name => $fieldSet) : ?>

	<?php

	foreach ($this->form->getFieldset($name) as $field)
	{
		if ($field->name != 'jform[metadata][tags][]' && !preg_match("/product/",$field->name))
		{
			
			echo $field->renderField();
		}
	} ?>
<?php endforeach; ?>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('ordering'); ?>
				</div>
			</div>
			

		<?php if(!$this->params->get('my_price_by_product')){ ?>
		 
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('price'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('price'); ?>
				</div>
			</div>
		<?php } ?>	
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_discount'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('product_discount'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_downloads'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('file_downloads'); ?>
				</div>
			</div>
				

			
	</div>
	<div class="pull-right span5">	

				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('featured'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('featured'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('language'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('language'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('detail_image'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('detail_image'); ?><br />
				<?php if($this->item->detail_image && file_exists(JPATH_ROOT.DS.$this->item->detail_image)){?>
					<img src="<?php  echo JURI::root().DS.$this->item->detail_image; ?>" />
				<?php } ?>
				
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('id'); ?>
				</div>
			</div>
			

			

	</div>
	</fieldset>
		<fieldset class="adminform">

			<legend><?php echo JText::_( 'MYMUSE_DESCRIPTION' ); ?></legend>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('articletext'); ?>

		</fieldset>
<?php echo JHtml::_('bootstrap.endTab'); ?>


<!--  TRACKS TAB -->
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tracks', '<b>'.strtoupper(JText::_('MYMUSE_TRACKS', true))).'</b>'; ?>
<fieldset class="adminform">

	<legend><?php echo JText::_('MYMUSE_TRACKS'); ?></legend>
			
	<div class="pull-left span12">
		<div class="control-group">
				<div class="control-label"><?php echo JText::_( 'MYMUSE_DOWNLOAD_PATH' ); ?>
				</div>
				<div class="controls"><?php echo $lists['download_dir']; ?>
				</div>
			</div>
		<table class="table table-striped" id="articleList">
				<thead>
					<tr>
						<th class="title"><?php echo JText::_( 'MYMUSE_SELECT_FILE' ); ?>
						</th>
						<th class="title"><?php echo JText::_( 'MYMUSE_FILE_NAME' ); ?>
						</th>
						<th class="title"><?php echo JText::_("MYMUSE_FILE_ALIAS")?>
						</th>
						<th class="title"><?php echo JText::_( 'MYMUSE_FILE_LENGTH' ); ?>
						</th>
						<th class="title"><?php echo JText::_( 'MYMUSE_NUMBER_DOWNLOADS' ); ?>
						</th>
						<th class="title"><?php echo JText::_( 'MYMUSE_DELETE_ITEM' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$formats = $this->params->get('my_formats');

				for($i = 0; $i < count($formats); $i++){ 
					$class = '';
					if($i >= count($formats)){
						$class = "hidden";
					}
					?>
					<tr class="<?php echo $class;?>" id="row_<?php echo $i; ?>">
						<td><?php echo $lists['select_file'][$i]; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_name)? $item->file_name[$i]->file_name : ''; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_alias)? $item->file_name[$i]->file_alias : ''; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_length)? MyMuseHelper::ByteSize($item->file_name[$i]->file_length) : ''; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_downloads)? $item->file_name[$i]->file_downloads : ''; ?>
						</td>
						<td><a href="javascript:deletevariation(<?php echo $i; ?>)"><?php echo JText::_( 'MYMUSE_DELETE_ITEM' ); ?></a>
						</td>
					</tr>
				<?php } ?>
				
				<?php if(count($this->params->get('my_formats')) > 1){ ?>
					<!--   <tr>
						<td colspan="7"><a href="javascript:addvariation();"><?php echo JText::_('MYMUSE_ADD_VARIATION')?></a></td>
					</tr> -->
				<?php } ?>
				</tbody>
			</table>
		</div>			
</fieldset>
		
<?php echo JHtml::_('bootstrap.endTab'); ?>
	

<!--  PREVIEWS TAB -->
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'previews', '<b>'.strtoupper(JText::_('MYMUSE_PREVIEWS', true))).'</b>'; ?>
	
<fieldset class="adminform">
		
	<legend><?php echo JText::_( 'MYMUSE_PREVIEW' ); ?></legend>
		<div class="pull-left span12">
			<div class="control-group">
				<div class="control-label"><?php echo JText::_( 'MYMUSE_PREVIEW_PATH' ); ?>
				</div>
				<div class="controls"><?php echo $lists['preview_dir']; ?>
				</div>
			</div>
			<table class="table table-striped" id="articleList">
				<thead>
					<tr>
						<th class="title"><?php echo JText::_( 'MYMUSE_SELECT_FILE' ); ?>
						</th>
						<th class="title"><?php echo JText::_( 'MYMUSE_FILE_NAME' ); ?>
						</th>
						<th class="title"><?php echo JText::_( 'MYMUSE_DELETE_ITEM' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr class="<?php echo $class;?>" id="row_<?php echo $i; ?>">
						<td><?php echo $lists['previews']; ?>
						</td>
						<td><?php echo $this->form->getInput('file_preview'); ?>
						</td>
						<td><input type="checkbox" name="remove_preview" id="jform_remove_preview" /></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
</fieldset>
	

<?php echo JHtml::_('bootstrap.endTab'); ?>
</div>
</div>

<div style="clear: both;"></div>

		<input type="hidden" name="parentid" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[parentid]" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $item->parent->catid ?>" />
		<input type="hidden" name="jform[artistid]" value="<?php echo $item->parent->artistid ?>" />
		<input type="hidden" name="current_preview" value="<?php echo stripslashes($item->file_preview) ?>" />
		
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $item->version; ?>" />
		<input type="hidden" name="jform[product_downloadable]" value="1" />
		<input type="hidden" name="subtype" value="file" />
		<input type="hidden" name="layout" value="editfile" />
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="variation" value="" />

		<?php echo JHTML::_( 'form.token' ); ?>
		</form>