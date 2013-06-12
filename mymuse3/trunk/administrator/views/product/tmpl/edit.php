<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// no direct access
defined('_JEXEC') or die;

// Load tooltips behavior
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.switcher');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$lists = $this->lists;
$startOffset = 'details';
if($lists['subtype'] == "file"){
	$startOffset = 'tracks';
}
if($lists['subtype'] == "item"){
	$startOffset = 'items';
}

?>

<script type="text/javascript">

	function pageLoad(page)
	{
   	 //hide all then show the good one
    	document.getElementById("details").style.display="none";
    	document.getElementById("images").style.display="none";
    	document.getElementById("tracks").style.display="none";
    	document.getElementById("items").style.display="none";
    	document.getElementById("details_link").className = document.getElementById("details_link").className.replace( /(?:^|\s)active(?!\S)/ , '' );
    	document.getElementById("tracks_link").className = document.getElementById("tracks_link").className.replace( /(?:^|\s)active(?!\S)/ , '' );
    	document.getElementById("items_link").className = document.getElementById("items_link").className.replace( /(?:^|\s)active(?!\S)/ , '' );
    	document.getElementById("images_link").className = document.getElementById("images_link").className.replace( /(?:^|\s)active(?!\S)/ , '' );
		document.adminForm.subtype.value="details";
		if(page == "images"){
			document.adminForm.subtype.value="images";
		}
    	
    	document.getElementById(page).style.display="block";
    	document.getElementById(page+"_link").className += " active";  
	}


	Joomla.submitbutton = function(task)
	{
		if (task == 'product.cancel' || document.formvalidator.isValid(document.id('product-form'))) {
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task, document.getElementById('product-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

</script>

<div id="submenu-box">
	<div class="submenu-box">
		<div class="submenu-pad">
<ul id="submenu">
	<li><a id="details_link" class="active" href="#" onclick="javascript:pageLoad('details');"><?php echo JText::_( 'MYMUSE_DETAILS' ); ?></a></li>
	<li><a id="images_link" href="#" onclick="javascript:pageLoad('images');"><?php echo JText::_( 'MYMUSE_IMAGES' ); ?></a></li>
	<li><a id="tracks_link" href="#" onclick="javascript:pageLoad('tracks');"><?php echo JText::_( 'MYMUSE_TRACKS' ); ?></a></li>
	<li><a id="items_link" href="#" onclick="javascript:pageLoad('items');"><?php echo JText::_( 'MYMUSE_ITEMS' ); ?></a></li>
</ul>
			<div class="clr"></div>
		</div>
	</div>
	<div class="clr"></div>
</div>
<div class="clr"></div>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" 
id="product-form" method="post" name="adminForm" class="form-validate">

	<div id="product-document">
		<div id="details" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('details'); ?>
				</div>
				<div class="width-40 fltrt">
					<?php echo $this->loadTemplate('metadata'); ?>
				</div>
			</div>
		</div>
		
		<div id="images" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('images'); ?>
				</div>

			</div>
		</div>
<?php if(isset($this->item->alias) && $this->item->alias != ""){ ?>
		<input type="hidden" name="old_alias" value="<?php echo $this->item->alias;?>" />
<?php }?>
<?php if(isset($this->item->catid) && $this->item->catid != ""){ ?>
		<input type="hidden" name="old_catid" value="<?php echo $this->item->catid;?>" />
<?php }?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="subtype" value="details" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>

		<div class="clr"></div>
		</form>

		<div id="tracks" class="tab" style="display:none;">
			<div class="noshow">
				<div class="width-100 fltlft">
					<?php 
					if(!$this->item->id){
						echo JText::_('MYMUSE_SAVE_THEN_ADD_TRACKS');
					}else{
						echo $this->loadTemplate('listtracks');
					} ?>
				</div>
			</div>
		</div>
		<div class="clr"></div>

		<div id="items" class="tab" style="display:none;">
			<div class="noshow">
				<div class="width-100 fltlft">
					<?php 
					if(!$this->item->id){
						echo JText::_('MYMUSE_SAVE_THEN_ADD_ITEMS');
					}else{
						echo $this->loadTemplate('listitems');
					}
					 ?>
				</div>
			</div>
		</div>
		<div class="clr"></div>

	</div>

<script type="text/javascript">
window.addEvent('domready', pageLoad('<?php echo $startOffset; ?>'));
</script>