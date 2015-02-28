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


	<table class="mymuse_cart">
		<?php if ($this->params->get('show_headings')) :?>
		<thead>
			<tr>
			<?php if ($this->params->get('category_show_product_image')): ?>
				<th class="list-image mymuse_cart_top"><?php echo JText::_('MYMUSE_IMAGE'); ?></th>
			<?php endif; ?>
				<th class="myselect" id="tableOrdering">
					<?php  echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder) ; ?>
				</th>

				<?php if ($this->params->get('list_show_date') && $this->params->get('order_date')) : 
                		$date = $this->params->get('order_date');
                ?>
				<th class="mydate-<?php  echo $date; ?>" id="tableOrdering2">
					<?php if ($date == "created") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
					<?php elseif ($date == "modified") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.modified', $listDirn, $listOrder); ?>
					<?php elseif ($date == "published") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
					<?php elseif ($date == "product_made_date") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'a.product_made_date', $listDirn, $listOrder); ?>
					<?php endif; ?>
				</th>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_author', 1)) : ?>
				<th class="myauthor" id="tableOrdering3">
					<?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_hits', 1)) : ?>
				<th class="myhits" id="tableOrdering4">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				
				<?php if ($this->params->get('list_show_price', 0)) : ?>
				<th class="myprice" id="tableOrdering4">
					<?php echo JHtml::_('grid.sort', 'MYMUSE_CART_PRICE', 'a.price', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				
				<?php if ($this->params->get('list_show_discount', 0)) : ?>
				<th class="list-discount mymuse_cart_top" id="tableOrdering6">
					<?php echo JHtml::_('grid.sort', 'MYMUSE_DISCOUNT', 'a.product_discount', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				
				<?php if ($this->params->get('list_show_sales', 0)) : ?>
				<th class="mysales" id="tableOrdering7">
					<?php echo JHtml::_('grid.sort', 'MYMUSE_SALES', 's.sales', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				
				<?php if ($this->params->get('category_show_comment_total', 0) && file_exists($comments)) : ?>
				<th class="mycomments" id="tableOrdering8">
					<?php echo JHtml::_('grid.sort', 'COMMENTS_LIST_HEADER', 'a.price', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
			</tr>
		</thead>
		<?php endif; ?>

		<tbody>

		<?php foreach ($this->items as $i => $product) : ?>
			<?php if ($this->items[$i]->state == 0) : ?>
				<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
			<?php else: ?>
				<tr class="cat-list-row<?php echo $i % 2; ?>" >
			<?php endif; ?>

			<?php if (in_array($product->access, $this->user->getAuthorisedViewLevels())) : ?>
				<?php if ($this->params->get('category_show_product_image')): ?>
				<td class="myimage"><?php if ($product->list_image): ?>
					<a
					href="<?php echo JRoute::_(MyMuseHelperRoute::getProductRoute($product->id, $this->category->id)); ?>">
						<img <?php if($height) : ?> height="<?php echo $height; ?>"
						<?php endif; ?> src="<?php echo $product->list_image; ?>"
						alt="<?php echo htmlspecialchars($product->list_image); ?>" />
				</a> <?php endif; ?>
				</td>
			<?php endif; ?>

				<td class="mytitle"><a
					href="<?php echo JRoute::_(MyMuseHelperRoute::getProductRoute($product->id, $this->category->id)); ?>">
						<?php echo $this->escape($product->title); ?>
				</a> <?php if ($product->params->get('access-edit')) : ?> <!--  
						 <ul class="actions">
							<li class="edit-icon">
								<?php echo JHtml::_('icon.edit', $product, $params); ?>
							</li>
						</ul>
						--> <?php endif; ?>
				</td>

				
				<?php if ($this->params->get('list_show_date')) : ?>
					<td class="mydate-<?php  echo $date; ?>" valign="top">
						<?php 
						if($product->displayDate != '0000-00-00'){
							echo JHtml::_('date', $product->displayDate, $this->escape(
							$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); 
						}
						?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_author', 1) ) : ?>
					<td class="myauthor" valign="top">
						<?php 
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
						<?php endif; ?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_hits', 1)) : ?>
					<td class="myhits" valign="top">
						<?php echo $product->hits; ?>
					</td>
				<?php endif; ?>
					
				<?php if ($this->params->get('list_show_price', 0)) : ?>
					<td class="myprice" valign="top">
						<?php echo myMuseHelper::printMoney($product->price); ?>
					</td>
				<?php endif; ?>
					
				<?php if ($this->params->get('list_show_discount', 0)) : ?>
					<td class="mydiscount" valign="top">
						<?php 
						if($product->product_discount > 0){
							echo myMuseHelper::printMoney($product->product_discount); 
						}
						?>
					</td>
				<?php endif; ?>
					
				<?php if ($this->params->get('list_show_sales', 0)) : ?>
					<td class="mysales" valign="top">
						<?php echo $product->sales; ?>
					</td>
				<?php endif; ?>
					
				<?php if ($this->params->get('category_show_comment_total', 0) && file_exists($comments)) : ?>
					<?php 
						$count = JComments::getCommentsCount($product->id, 'com_mymuse');
						if($count){
							echo '<td class="mycomments" valign="top">'.$count.'</td>';
						}
					?>
				<?php endif; ?>
					

				<?php else : // Show unauth links. ?>
					<td class="myregister" valign="top">
						<?php
							echo $this->escape($product->title).' : ';
							$menu		= JFactory::getApplication()->getMenu();
							$active		= $menu->getActive();
							$itemId		= $active->id;
							$link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$itemId);
							$returnURL = JRoute::_(MyMuseHelperRoute::getProductRoute($product->id));
							$fullURL = new JURI($link);
							$fullURL->setVar('return', base64_encode($returnURL));
						?>
						<a href="<?php echo $fullURL; ?>" class="register">
							<?php echo JText::_( 'MYMUSE_REGISTER_TO_READ_MORE' ); ?></a>
					</td>
				<?php endif; ?>
				</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php // Code to add a link to submit an product. ?>
<?php if ($this->category->getParams()->get('access-create')) : ?>
	<!-- <?php echo JHtml::_('icon.create', $this->category, $this->category->params); ?> -->
<?php  endif; ?>

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
</form>
<?php  endif; ?>
