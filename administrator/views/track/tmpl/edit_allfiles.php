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


			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_id'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('product_id'); ?>
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
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
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
				


	</div>
</div>
<div class="clr"></div>
		<input type="hidden" name="jform[allfiles]" value="1">
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		