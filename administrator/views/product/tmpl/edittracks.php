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

$editor =& JFactory::getEditor();

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
		<h2><?php echo $item->title; ?></h2>
		<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="edittracks">
<div class="pull-left">

		<fieldset class="adminform">

			<legend><?php echo empty($this->item->id) ? JText::_('MYMUSE_NEW_TRACK') : JText::_('MYMUSE_EDIT_TRACK'); ?></legend>
			<ul class="adminformlist">
			
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>
				
				<li><?php echo $this->form->getLabel('file_type'); ?>
				<?php echo $this->form->getInput('file_type'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>
				
				<li><?php echo $this->form->getLabel('product_sku'); ?>
				<?php echo $this->form->getInput('product_sku'); ?></li>
				
				<li><?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?></li>
				
				<li><label id="jform_product_file-lbl" for="jform_product_file"  class="hasTip" title="<?php echo JText::_("MYMUSE_BROWSE_TO_FILE")?>">
				<?php echo JText::_( 'MYMUSE_UPLOAD_NEW_FILE' ); ?></label>
				<input class="inputbox" type="file" name="product_file" id="jform_product_file" size="40" /></li>

		<?php //if(!$this->params->get('my_encode_filenames')){ ?>
				<li><label id="jform_product_file_select-lbl" for="jform_product_file_select"  class="hasTip" title="<?php echo JText::_("MYMUSE_BROWSE_TO_FILE")?>">
				<?php echo JText::_( 'MYMUSE_SELECT_FILE' ); ?></label>
				<?php echo $lists['select_file']; ?></li>
		<?php //} ?>
		
		<?php if(!$this->params->get('my_use_database')){ ?>
				<li><label id="jform_download_dir-lbl" for="jform_download_dir"  class="hasTip" title="<?php echo JText::_("MYMUSE_DOWNLOAD_DIR_DESC")?>">
				<?php echo JText::_( 'MYMUSE_DOWNLOAD_PATH' ); ?></label>
				<input  type="text" name="download_dir" id="jform_download_dir" value="<?php echo $lists['download_dir']; ?>" 
				class="readonly" readonly="readonly" style="font-weight:normal; font-size: 10px;" size="60" />
				</li>
		<?php } ?>
		
				<li><?php echo $this->form->getLabel('file_name'); ?>
				<?php echo $this->form->getInput('file_name'); ?></li>
				
		<?php if($item->title_alias != ""){ ?>
				<li><label id="jform_file_alias-lbl" class="hasTip" title="" for="jform_file_alias"><?php echo JText::_("MYMUSE_FILE_ALIAS")?></label>
				<input id="jform_file_alias" class="readonly" type="text" size="40" value="<?php echo $item->title_alias; ?>" name="jform[file_alias]">
		<?php }?>
				
				<li><?php echo $this->form->getLabel('file_length'); ?>
				<?php echo $this->form->getInput('file_length'); ?></li>
				
				<li><?php echo $this->form->getLabel('file_time'); ?>
				<?php echo $this->form->getInput('file_time'); ?></li>
				
				<li><?php echo $this->form->getLabel('file_downloads'); ?>
				<?php echo $this->form->getInput('file_downloads'); ?></li>
				
				<li><?php echo $this->form->getLabel('price'); ?>
				<?php echo $this->form->getInput('price'); ?></li>
				
				<li><?php echo $this->form->getLabel('product_discount'); ?>
				<?php echo $this->form->getInput('product_discount'); ?></li>
				
				<li><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('featured'); ?>
				<?php echo $this->form->getInput('featured'); ?></li>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>
			
				<li><?php echo $this->form->getLabel('detail_image'); ?>
				<?php echo $this->form->getInput('detail_image'); ?></li>
				<?php if($this->item->list_image && file_exists(JPATH_ROOT.DS.$this->item->detail_image)){?>
					<img src="../<?php  echo JURI::root().DS.$this->item->detail_image; ?>" />
				<?php } ?>
				</li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
				
			</ul>
		</fieldset>
</div>
<div class="pull-right">
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
		<fieldset class="adminform">

			<legend><?php echo JText::_( 'MYMUSE_DESCRIPTION' ); ?></legend>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('articletext'); ?>

		</fieldset>
</div>
</div>
<div style="clear: both;"></div>

		<input type="hidden" name="parentid" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[parentid]" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $item->parent->catid ?>" />
		<input type="hidden" name="current_preview" value="<?php echo stripslashes($item->file_preview) ?>" />
		<input type="hidden" name="current_preview_2" value="<?php echo stripslashes($item->file_preview_2) ?>" />
		<input type="hidden" name="current_preview_3" value="<?php echo stripslashes($item->file_preview_3) ?>" />
		<input type="hidden" name="current_title_alias" value="<?php echo stripslashes($item->title_alias) ?>" />
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