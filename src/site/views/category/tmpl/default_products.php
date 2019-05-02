<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtmlBehavior::framework();

// Create some shortcuts.
$params		= &$this->item->params;
$n			= count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$height 	= $this->params->get('category_product_image_height',0);
$inner_cols	= 0;
if ($this->params->get('list_show_date') && $this->params->get('order_date')) {
	$inner_cols++;
}
if ($this->params->get('list_show_author', 0)) {
	$inner_cols++;
}
if ($this->params->get('list_show_hits', 0)) {
	$inner_cols++;
}
if ($this->params->get('list_show_price', 0)) {
	$inner_cols++;
}
if ($this->params->get('list_show_discount', 0))  {
	$inner_cols++;
}
if ($this->params->get('list_show_sales', 0)) {
	$inner_cols++;
}
if ($this->params->get('category_show_comment_total', 0) && file_exists($comments)) {
	$inner_cols++;
}
$inner_span = floor(12 / $inner_cols);


?>
<?php if (empty($this->items)) : ?>

	<?php if ($this->params->get('show_no_products', 1)) : ?>
	<p><?php echo JText::_('MYMUSE_NO_PRODUCTS'); ?></p>
	<?php endif; ?>

<?php else : ?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="limitstart" value="" />
<?php if ($this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit')) :?>
	<table class="mymuse_cart">
		<tr>
		<?php if ($this->params->get('filter_field') != 'hide') : ?>
			<td align="left" width="60%" nowrap="nowrap">
				<?php echo JText::_('MYMUSE_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?>
				<input type="text" name="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" 
				class="inputbox" 
				onchange="this.start.value=0;this.form.submit();" />
			</td>
		<?php endif; ?>
        
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<td  nowrap="nowrap">
			<?php
				echo '&nbsp;&nbsp;&nbsp;'.JText::_('MYMUSE_DISPLAY_NUM').'&nbsp;';
				echo $this->pagination->getLimitBox();
			?>
			</td>
		<?php endif; ?>
		</tr>
	</table>
	<br />
<?php endif; ?>


<!-- table less -->
<?php if ($this->params->get('show_headings')) :?>
<div class="row row-fluid mymuse_cart_top hidden-phone">
	<div class="span4 cols-md-4">
		<div class="list-image "><?php echo JText::_('MYMUSE_IMAGE'); ?></div>
	</div>
	<div class="span4 cols-md-4">

		<div class="list-title "><?php  echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder) ; ?></div>
	</div>
	<div class="span4 cols-md-4">
		<div class="row row-fluid">
			<?php if ($this->params->get('list_show_date') && $this->params->get('order_date')) : 
					$date = $this->params->get('order_date');
					?>
				<div class="list-date  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>">
					<?php if ($date == "created") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
					<?php elseif ($date == "modified") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.modified', $listDirn, $listOrder); ?>
					<?php elseif ($date == "published") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
					<?php elseif ($date == "product_made_date") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.product_made_date', $listDirn, $listOrder); ?>
					<?php endif; ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_author')) : ?>
				<div class="list-author  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_hits')) : ?>
				<div class="list-hits  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_price')) : ?>
				<div class="list-price  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php echo JHtml::_('grid.sort', 'MYMUSE_CART_PRICE', 'a.price', $listDirn, $listOrder); ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_discount')) : ?>
				<div class="list-discount  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php echo JHtml::_('grid.sort', 'MYMUSE_DISCOUNT', 'a.product_discount', $listDirn, $listOrder); ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_sales')) : ?>
				<div class="list-sales  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php echo JHtml::_('grid.sort', 'MYMUSE_SALES', 's.sales', $listDirn, $listOrder); ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('category_show_comment_total', 0) && file_exists($comments)) : ?>
			<div class="list-comments  span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php echo JText::_('COMMENTS_LIST_HEADER'); ?></div>
			<?php endif; ?>

		</div>
			

	</div>
</div>
<?php endif; ?>

<?php foreach ($this->items as $i => $product) : ?>
<div class="row row-fluid">

	<div class="span4 cols-md-4">
		<div class="list-image "><a href="<?php echo JRoute::_(MyMuseHelperRoute::getProductRoute($product->id, $this->category->id)); ?>">
			<img <?php if($height) : ?> style="height:<?php echo $height; ?>px"
				<?php endif; ?> src="<?php echo $product->list_image; ?>"
				alt="<?php echo htmlspecialchars($product->list_image); ?>" />
			</a></div>
	</div>

	<div class="span4 cols-md-4">
		
			<div class="list-title "><a href="<?php 
				echo JRoute::_(MyMuseHelperRoute::getProductRoute($product->id, $this->category->id)); ?>">
				<?php echo $this->escape($product->title); ?>
				</a>
			</div>
	</div>
	<div class="span4 cols-md-4">
		<div class="row row-fluid">
			<?php if ($this->params->get('list_show_date')) : ?>
				<div class="list-date mydate span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?>"><?php 
						if($product->displayDate != '0000-00-00'){
							echo JHtml::_('date', $product->displayDate, $this->escape(
							$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); 
						}
						?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_author')) : ?>
				<div class="list-author span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?> myauthor"><?php 
						if(!empty($product->author )) : 
							$author =  $product->author ?>
						<?php $author = ($product->created_by_alias ? $product->created_by_alias : $author);?>

						<?php if (!empty($product->contactid ) &&  $this->params->get('link_author') == true):?>
							<?php echo JHtml::_(
									'link',
									JRoute::_('index.php?option=com_contact&view=contact&id='.$product->contactid),
									$author
							); ?>

						<?php else :?>
							<?php echo $author; ?>
						<?php endif; ?>
						<?php endif; ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_hits')) : ?>
				<div class="list-hits span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?> myhits"><?php echo $product->hits; ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_price')) : ?>
				<div class="list-price span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?> myprice"><?php echo myMuseHelper::printMoney($product->price); ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_discount')) : ?>
				<div class="list-discount span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?> mydiscount"><?php 
						if($product->product_discount > 0){
							echo myMuseHelper::printMoney($product->product_discount); 
						}else{
							echo "-";
						}
						?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_sales')) : ?>
				<div class="list-sales span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?> mysales"><?php echo $product->sales; ?></div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_comments')) : ?>
				<div class="list-comments span<?php echo $inner_span; ?> cols-md-<?php echo $inner_span; ?> mycomments"><?php 
						$count = JComments::getCommentsCount($product->id, 'com_mymuse');
						if($count){
							echo $count ;
						}
					?></div>
			<?php endif; ?>
		</div>
	</div>

</div>
<?php endforeach; ?>




<?php // Add pagination links ?>
<?php if (!empty($this->items)) : ?>
	<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
	<div class="pagination">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		 	<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php endif; ?>

		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php endif; ?>

<?php  endif; ?>
</form>
<?php endif; ?>
