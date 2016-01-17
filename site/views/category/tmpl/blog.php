<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
$category = $this->category;
$uri = JFactory::getURI();
$cat_uri = $uri->toString();
$description = ($this->category->description != '')? $this->category->description : $this->category->title;
$document 	= JFactory::getDocument();
$document->setMetaData( 'og:site_name',nl2br($this->escape($this->store->title))  );
$document->setMetaData( 'og:type', 'article');
$document->setMetaData( 'og:url', $cat_uri);
$document->setMetaData( 'og:title', $this->escape($this->category->title));
$document->setMetaData( 'og:description', strip_tags($description));
$document->setMetaData( 'og:image', JURI::Root().$this->category->getParams()->get('image'));

$document->setMetaData( 'twitter:title', $this->escape($this->category->title));
$document->setMetaData( 'twitter:card', 'summary_large_image');
$document->setMetaData( 'twitter:site', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:creator', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:url', $cat_uri);
$document->setMetaData( 'twitter:description', strip_tags($description));
$document->setMetaData( 'twitter:image', JURI::Root().$this->category->getParams()->get('image'));
?>

<?php  echo $category->event->beforeDisplayHeader; ?>

<div class="blog<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
	<h2>
		<?php echo $this->escape($this->params->get('page_subheading')); ?>
		<?php if ($this->params->get('show_category_title')) : ?>
			<span class="subheading-category"><?php echo $this->category->title;?></span>
		<?php endif; ?>
	</h2>
<?php endif; ?>
<?php echo $category->event->afterDisplayTitle; ?>



<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
	<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
		<img src="<?php echo $this->category->getParams()->get('image'); ?>"
		<?php if ($this->params->get('category_image_height')) : ?>
			style="height: <?php echo $this->params->get('category_image_height'); ?>px; "
		<?php endif; ?>
		/>
	<?php endif; ?>
	<?php if ($this->params->get('show_description') && $this->category->description) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
	<?php endif; ?>
	<div class="clearfix"></div>
	</div>
<?php endif; ?>



<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
		<div class="cat-children cat-items">
		<h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
			<?php echo $this->loadTemplate('children'); ?>
		</div>
<?php endif; ?>

<div class="clear"></div>

<?php echo $category->event->beforeDisplayProduct; ?>

<?php if($this->params->get('category_show_all_products')) : ?>
<div class="cat-items">
<h3><?php echo JText::_("MYMUSE_PRODUCTS"); ?></h3>

<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="items-leading">
	<?php foreach ($this->lead_items as &$item) : ?>
		<div class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</div>
		<?php
			$leadingcount++;
		?>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
<?php endif; ?>


<?php
	$introcount=(count($this->intro_items));
	$counter=0;
?>
<?php if (!empty($this->intro_items)) : ?>
	
	<?php foreach ($this->intro_items as $key => &$item) : ?>
		<?php
		$key= ($key-$leadingcount)+1;
		$rowcount=( ((int)$key-1) %	(int) $this->columns) +1;
		$row = $counter / $this->columns ;
		$this->key = $key;
		if ($rowcount==1) : ?>
		<div
			class="items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row ; ?>">
			<?php endif; ?>
			<div
				class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
				<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
				?>
			</div>
			<?php $counter++; ?>
			<?php if (($rowcount == $this->columns) or ($counter ==$introcount)): ?>
			<span class="row-separator"></span>
		</div>
		<?php endif; ?>
	<?php endforeach; ?>
	<div class="clear"></div>
<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>

	<?php echo $this->loadTemplate('links'); ?>

<?php endif; ?>
<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
		<div class="pagination">
						<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="counter">
								<?php echo $this->pagination->getPagesCounter(); ?>
						</p>

				<?php endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
<?php  endif; ?>

	</div>
<?php  endif; ?>
</div>
<?php echo $category->event->afterDisplayProduct; ?>