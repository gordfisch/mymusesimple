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

$uri = JFactory::getURI();
$cat_uri = $uri->toString();

$document 	= JFactory::getDocument();
$description = ($this->category->description != '')? $this->category->description : $this->category->title;
$document->setMetaData( 'og:site_name',$this->escape($this->store->title));
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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
$category = $this->category;
?>
<?php  echo $category->event->beforeDisplayHeader; ?>

<div class="category-list<?php echo $this->pageclass_sfx;?>">

	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<?php if ($this->params->get('show_category_title')) : ?>
	<h2>
		<span class="category-title"><?php echo $this->category->title;?></span>
	</h2>
	<?php endif; ?>
	
	<?php if ($this->params->get('page_subheading')) : ?>
	<h3>
		<span class="category-subheading"><?php echo $this->escape($this->params->get('page_subheading')); ?></span>
	</h3>
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
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	<div class="clearfix"></div>
	
<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
		<div class="cat-children cat-items">
		<h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
			<?php echo $this->loadTemplate('children'); ?>
		</div>
<?php endif; ?>
	<div class="clearfix"></div>
	
<?php echo $category->event->beforeDisplayProduct; ?>

<?php echo $this->loadTemplate('products'); ?>


<?php echo $category->event->afterDisplayProduct; ?>
</div>
