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

		var variation = <?php  echo count($item->file); ?>;
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
			submitform( 'MYMUSE_NEW_TRACK.deletevariation' );

		}
		//-->
		</script>
		<h2><?php echo empty($item->id) ? JText::_('MYMUSE_NEW_TRACK') : JText::_('MYMUSE_EDIT_TRACK'); ?> 

			<?php if(isset($item->parent)){
				echo ' : <a href="index.php?option=com_mymuse&view=product&task=product.edit&id='.$item->parent->id.'">'.$item->parent->title."</a> : ";
			}
			?>
			<?php echo $item->title; ?></h2>

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
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('id'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('type'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('type'); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_time'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('file_time'); ?>
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
				<div class="control-label"><?php echo $this->form->getLabel('published'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('published'); ?>
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
				<div class="control-label"><?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>

	</div>
	</fieldset>
		<fieldset class="adminform">

			<legend><?php echo JText::_( 'MYMUSE_DESCRIPTION' ); ?></legend>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>

		</fieldset>
<?php echo JHtml::_('bootstrap.endTab'); ?>


<!--  TRACKS TAB -->
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tracks', '<b>'.strtoupper(JText::_('MYMUSE_TRACKS', true))).'</b>'; ?>
<fieldset class="adminform">

	<legend><?php echo JText::_('MYMUSE_TRACKS'); ?></legend>
	<div class="pull-left span10">



				<h3><a href="index.php?option=com_media"><?php echo JText::_( 'MYMUSE_UPLOAD_NEW_FILE' ); ?></a></h3>


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
						<td><?php echo isset($item->file_name[$i]->file_name)? $item->file_name[$i]->file_name: ''; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_alias)? $item->file_name[$i]->file_alias: ''; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_length)? $item->file_name[$i]->file_length: ''; ?>
						</td>
						<td><?php echo isset($item->file_name[$i]->file_downloads)? $item->file_name[$i]->file_downloads: ''; ?>
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

		<input type="hidden" name="product_id" value="<?php echo $item->product_id ?>" />
		<input type="hidden" name="jform[product_id]" value="<?php echo $item->product_id ?>" />
		<input type="hidden" name="current_preview" value="<?php echo stripslashes($item->preview) ?>" />
		<input type="hidden" name="current_preview_2" value="<?php echo stripslashes($item->preview_2) ?>" />
		<input type="hidden" name="current_preview_3" value="<?php echo stripslashes($item->preview_3) ?>" />
		
		<input type="hidden" name="view" value="track" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $item->version; ?>" />
		<input type="hidden" name="layout" value="edit" />
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="variation" value="" />

		<?php echo JHTML::_( 'form.token' ); ?>
		</form>