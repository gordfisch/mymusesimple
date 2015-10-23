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
		//-->
		</script>
		<h2><?php echo empty($this->item->id) ? JText::_('MYMUSE_NEW_TRACK') : JText::_('MYMUSE_EDIT_TRACK'); ?> <?php echo $item->title; ?></h2>

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
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('ordering'); ?>
				</div>
			</div>
			
		<?php if(!$this->params->get('my_use_database')){ ?>
			<div class="control-group">
				<div class="control-label">
				<?php echo JText::_( 'MYMUSE_DOWNLOAD_PATH' ); ?>
				</div>
				<div class="controls">
				<input  type="text" name="download_dir" id="jform_download_dir" value="<?php echo $lists['download_dir']; ?>" 
				class="readonly" readonly="readonly" style="font-weight:normal; font-size: 10px;" size="60" />
				</div>
			</div>
		<?php } ?>
		
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('price'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('price'); ?>
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_discount'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('product_discount'); ?>
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
				<?php if($this->item->list_image && file_exists(JPATH_ROOT.DS.$this->item->detail_image)){?>
					<img src="../<?php  echo JURI::root().DS.$this->item->detail_image; ?>" />
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
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('recommend'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('recommend'); ?>
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
	<div class="pull-left span5">
		<!--  
			<div class="control-group">
				<div class="control-label">
				<?php echo JText::_( 'MYMUSE_UPLOAD_NEW_FILE' ); ?>
				</div>
				<div class="controls">
				<input class="inputbox" type="file" name="product_file" id="jform_product_file" size="40" /><br />
				upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?><br />
				post_max_size: <?php echo ini_get('post_max_size'); ?><br />
				
				</div>
			</div>
		-->
	
			<div class="control-group">
				<div class="control-label">
				<?php echo JText::_( 'MYMUSE_SELECT_FILE' ); ?>
				</div>
				<div class="controls">
				</div>
				<div class="controls">
				<?php echo $lists['select_file']; ?>
				</div>
			</div>
	
		

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_name'); ?>
				</div>
				<div class="controls">
				<input id="jform_file_name_0" class="readonly" type="text" size="40" value="<?php echo $item->file_name[0]->file_name; ?>" name="file_name_0">
				</div>
			</div>
				
		<?php if(isset($item->file_name[0]->file_alias) && $item->file_name[0]->file_alias != ""){ ?>
			<div class="control-group">
				<div class="control-label"><?php echo JText::_("MYMUSE_FILE_ALIAS")?>
				</div>
				<div class="controls">
				<input id="jform_file_alias_0" class="readonly" type="text" size="40" value="<?php echo $item->file_name[0]->file_alias; ?>" name="file_alias_0">
				</div>
			</div>
		<?php }?>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_length'); ?>
				</div>
				<div class="controls">
				<input id="jform_file_length_0" class="readonly" type="text" size="40" value="<?php echo $item->file_name[0]->file_length; ?>" name="file_length_0">
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_time'); ?>
				</div>
				<div class="controls">
				<input id="jform_file_time_0" class="readonly" type="text" size="40" value="<?php echo $item->file_name[0]->file_time; ?>" name="file_time_0">
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_downloads'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('file_downloads'); ?>
				</div>
			</div>
			<input type="hidden" name="current_title_alias[0]" value="<?php 
			echo isset($item->file_name[0]->file_alias)? stripslashes($item->file_name[0]->file_alias): ''; ?>" />
			
			VARIATION
			<div class="control-group">
				<div class="control-label">
				<?php echo JText::_( 'MYMUSE_SELECT_FILE' ); ?>
				</div>
				<div class="controls">
				</div>
				<div class="controls">
				<?php echo $lists['select_file']; ?>
				</div>
			</div>
			
		</fieldset>
<?php echo JHtml::_('bootstrap.endTab'); ?>


<!--  PREVIEWS TAB -->
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'previews', '<b>'.strtoupper(JText::_('MYMUSE_PREVIEWS', true))).'</b>'; ?>
<div class="pull-left">
		<fieldset class="adminform">

			<legend><?php echo JText::_( 'MYMUSE_PREVIEW_PATH_STORE' ); ?></legend>
			<ul class="adminformlist">
				<li><label id="jform_product_preview-lbl" for="jform_product_preview"  class="hasTip" title="<?php echo JText::_("MYMUSE_BROWSE_TO_FILE")?>">
				<?php echo JText::_( 'MYMUSE_UPLOAD_NEW_PREVIEW' ); ?></label>
				<input class="inputbox" type="file" name="product_preview" id="jform_product_preview" size="40" /></li>

				<li><?php echo $this->form->getLabel('file_preview'); ?>
				<?php echo $this->form->getInput('file_preview'); ?></li>
				
				<li><label id="jform_preview_dir-lbl" for="jform_preview_dir"  class="hasTip" title="<?php echo JText::_("MYMUSE_PREVIEW_PATH_DESC")?>">
				<?php echo JText::_( 'MYMUSE_PREVIEW_PATH' ); ?></label>
				<input  type="text" name="preview_dir" id="jform_preview_dir" value="<?php echo $lists['preview_dir']; ?>" 
				class="readonly" readonly="readonly" style="font-weight:normal; font-size: 10px;" size="60" /></li>
				
				<li><label id="jform_remove_preview-lbl" for="jform_remove_preview"  class="hasTip" title="<?php echo JText::_("MYMUSE_DELETE_PREVIEW_DESC")?>">
				<?php echo JText::_( 'MYMUSE_DELETE_PREVIEW' ); ?></label>
				<input type="checkbox" name="remove_preview" id="jform_remove_preview" /></li>
				
				<li><label id="jform_preview_list-lbl" for="jform_preview_list"  class="hasTip" title="<?php echo JText::_("MYMUSE_PREVIEW_FILE")?>">
				<?php echo JText::_( 'MYMUSE_PREVIEW_FILE' ); ?></label>
				<?php echo $lists['previews']; ?> </li>
			</ul>
		</fieldset>
		<fieldset class="adminform">

			<legend><?php echo JText::_( 'MYMUSE_PREVIEW_PATH_STORE_2' ); ?></legend>
			<ul class="adminformlist">
				<li><label id="jform_product_preview-2-lbl" for="jform_product_preview_2"  class="hasTip" title="<?php echo JText::_("MYMUSE_BROWSE_TO_FILE")?>">
				<?php echo JText::_( 'MYMUSE_UPLOAD_NEW_PREVIEW' ); ?></label>
				<input class="inputbox" type="file" name="product_preview_2" id="jform_product_preview_2" size="40" /></li>

				<li><?php echo $this->form->getLabel('file_preview_2'); ?>
				<?php echo $this->form->getInput('file_preview_2'); ?></li>
								
				<li><label id="jform_remove_preview-2-lbl" for="jform_remove_preview_2"  class="hasTip" title="<?php echo JText::_("MYMUSE_DELETE_PREVIEW_DESC")?>">
				<?php echo JText::_( 'MYMUSE_DELETE_PREVIEW' ); ?></label>
				<input type="checkbox" name="remove_preview_2" id="jform_remove_preview_2" /></li>
				
				<li><label id="jform_preview_list-2-lbl" for="jform_preview_list_2"  class="hasTip" title="<?php echo JText::_("MYMUSE_PREVIEW_FILE")?>">
				<?php echo JText::_( 'MYMUSE_PREVIEW_FILE' ); ?></label>
				<?php echo $lists['previews_2']; ?> </li>
			</ul>
		</fieldset>
		<fieldset class="adminform">

			<legend><?php echo JText::_( 'MYMUSE_PREVIEW_PATH_STORE_3' ); ?></legend>
			<ul class="adminformlist">
				<li><label id="jform_product_preview-3-lbl" for="jform_product_preview_3"  class="hasTip" title="<?php echo JText::_("MYMUSE_BROWSE_TO_FILE")?>">
				<?php echo JText::_( 'MYMUSE_UPLOAD_NEW_PREVIEW' ); ?></label>
				<input class="inputbox" type="file" name="product_preview_3" id="jform_product_preview_3" size="40" /></li>

				<li><?php echo $this->form->getLabel('file_preview_3'); ?>
				<?php echo $this->form->getInput('file_preview_3'); ?></li>
								
				<li><label id="jform_remove_preview-3-lbl" for="jform_remove_preview_3"  class="hasTip" title="<?php echo JText::_("MYMUSE_DELETE_PREVIEW_DESC")?>">
				<?php echo JText::_( 'MYMUSE_DELETE_PREVIEW' ); ?></label>
				<input type="checkbox" name="remove_preview_3" id="jform_remove_preview_3" /></li>
				
				<li><label id="jform_preview_list-3-lbl" for="jform_preview_list_3"  class="hasTip" title="<?php echo JText::_("MYMUSE_PREVIEW_FILE")?>">
				<?php echo JText::_( 'MYMUSE_PREVIEW_FILE' ); ?></label>
				<?php echo $lists['previews_3']; ?> </li>
			</ul>
		</fieldset>

	</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>
</div>
</div>

<div style="clear: both;"></div>

		<input type="hidden" name="parentid" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[parentid]" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $item->parent->catid ?>" />
		<input type="hidden" name="current_preview" value="<?php echo stripslashes($item->file_preview) ?>" />
		<input type="hidden" name="current_preview_2" value="<?php echo stripslashes($item->file_preview_2) ?>" />
		<input type="hidden" name="current_preview_3" value="<?php echo stripslashes($item->file_preview_3) ?>" />
		
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $item->version; ?>" />
		<input type="hidden" name="jform[product_downloadable]" value="1" />
		<input type="hidden" name="subtype" value="file" />
		<input type="hidden" name="layout" value="listtracks" />
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />

		<?php echo JHTML::_( 'form.token' ); ?>
		</form>