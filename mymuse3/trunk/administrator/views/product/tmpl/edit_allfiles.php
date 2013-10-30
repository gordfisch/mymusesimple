<?php
/**
 * @version		$$
 * @package		mymuse2.5
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$item = $this->item;
$params = $this->params;


JFilterOutput::objectHTMLSafe( $item );
JHTML::_('behavior.tooltip');
?>
		<script language="javascript" type="text/javascript">
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
			//} else if (form.product_sku.value == ""){
			//	alert( "<?php echo JText::_( 'MYMUSE_FILE_MUST_HAVE_AN_SKU', true ); ?>" );

			} else {

				submitform( pressbutton );
			}
		}
		//-->
		</script>

		<form action="index.php" method="post" name="adminForm" id="adminForm">
<h2><?php echo $item->parent->title; ?>: <?php echo JText::_("MYMUSE_ALL_FILES"); ?></h2>
<div class="edittracks">
	<div class="pull-left span5">
			<ul class="adminformlist">
			
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>
				
				<li><?php echo $this->form->getLabel('product_sku'); ?>
				<?php echo $this->form->getInput('product_sku'); ?></li>
				
				<li><?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?></li>
				
				<li><?php echo $this->form->getLabel('file_downloads'); ?>
				<?php echo $this->form->getInput('file_downloads'); ?></li>
				
				<li><?php echo $this->form->getLabel('price'); ?>
				<?php echo $this->form->getInput('price'); ?></li>
				
				<li><?php echo $this->form->getLabel('product_discount'); ?>
				<?php echo $this->form->getInput('product_discount'); ?></li>
			</ul>
	</div>			
	<div class="pull-right span5">
			<ul class="adminformlist">
				
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
</div>
<div class="clr"></div>
		<input type="hidden" name="product_allfiles" value="1">
		<input type="hidden" name="jform[product_allfiles]" value="1">
		<input type="hidden" name="jform[parentid]" value="<?php echo $item->parent->id ?>" />
		<input type="hidden" name="parentid" value="<?php echo $item->parent->id ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $item->parent->catid ?>" />
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $item->version; ?>" />
		<input type="hidden" name="jform[product_downloadable]" value="1" />
		<input type="hidden" name="subtype" value="allfiles" />

		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		