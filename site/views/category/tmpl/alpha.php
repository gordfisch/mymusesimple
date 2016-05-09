<?php
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
$category = $this->category;
$uri = JFactory::getURI();
$cat_uri = $uri->toString();
$Itemid		= $this->Itemid;
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

<div class="categories-list<?php echo $this->pageclass_sfx;?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div id="parent">
		<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	</div>
	

	<?php if($this->params->get('show_minicart')) :?>
	<!--  INLINE MINICART  -->
	<div id="mini-cart">
		<div id="mini-cart-top">
			<div id="mini-cart-content">
				<div id="mini-cart-cart"></div>
				<div id="mini-cart-text"><?php
			if ($this->cart ['idx']) :
				$word = ($this->cart ['idx'] == 1) ? "item" : "items";
				echo $this->cart ['idx'] . " $word";
			
			endif;
			?></div>
				<div id="mini-cart-link"><?php
			if ($this->cart ['idx']) :
				echo '<a href="' . JRoute::_ ( 'index.php?option=com_mymuse&view=cart&task=showcart&Itemid=' . $Itemid ) . '">' . JText::_ ( 'MYMUSE_VIEW_CART' ) . '</a>';
			 else :
				echo JText::_ ( 'MYMUSE_YOUR_CART_IS_EMPTY' );
			endif;
			?></div>
			</div>
		</div>
	</div>
	<!--  END INLINE MINICART  -->
	<div style="clear: both;"></div>
	<?php  endif; ?>
		
	
<?php endif; ?>

	<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
	<h2>
		<?php echo $this->escape($this->params->get('page_subheading')); ?>
		<?php if ($this->params->get('show_category_title')) : ?>
			<span class="subheading-category"><?php echo $this->category->title;?></span>
		<?php endif; ?>
	</h2>
	<?php endif; ?>
	

<div class="moduletable ">
	<div class="search">
	<form action="" method="get" class="form-inline">
		<label for="mod-search-searchword" class="element-invisible">Type your search and press enter...</label> 
        <input name="searchword" id="mod-search-searchword" maxlength="200" class="inputbox search-query" 
        placeholder="Type your search and press enter..." type="search">		
		<input name="option" value="com_mymuse" type="hidden">
		<input name="Itemid" value="<?php echo $Itemid; ?>" type="hidden">
        <input name="view" value="category" type="hidden">
        <input name="catid" value="<?php echo $category->id; ?>" type="hidden">
        <input name="id" value="<?php echo $category->id; ?>" type="hidden">
        <input name="layout" value="tracks" type="hidden">
        <input name="filter_order" value="a.ordering" type="hidden">
        <input name="filter_order_Dir" value="" type="hidden">
        <input name="filter_alpha" value="" type="hidden">
        <input name="lang" value="en-GB" type="hidden">
        <input name="language" value="en-GB" type="hidden">
        <input name="limit" value="1000" type="hidden">

	</form>
</div>	
	
	
	
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
		<div class="clear"></div>
	</div>
	<?php endif; ?>
	<div class="clear"></div>
<?php echo $category->event->beforeDisplayProduct; ?>

<div class="cat-items">
<?php
echo $this->loadTemplate('items');
?>
</div>

<?php echo $category->event->afterDisplayProduct; ?>
</div>
<div class="clear"></div>