<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$class = ' class="first"';
$count = count($this->children[$this->category->id]);
$columns =  $this->params->get('subcat_columns', 1);
if($count <  $this->params->get('subcat_columns', 1)){
	$columns = $count;
}
$this->cols[$this->maxLevel] = $columns;
if($this->maxLevel < 1 && isset($this->cols[$this->maxLevel+1])){
	$columns = $this->cols[$this->maxLevel+1] - 2;
}
$columns = $columns? $columns : 1;

$break =  round($count / $columns,0,PHP_ROUND_HALF_DOWN);
$r = $count  %  $columns;
if($r){
	$break++;
}
if($columns == 1){
	$break = 0;
}
$total_shown = 0;
$column = 1;
$i=0;

?>

<div class="cols-<?php echo $columns; ?>">

<?php if (count($this->children[$this->category->id]) > 0 && $this->maxLevel != 0) : 
if(!$total_shown){
	//top of column
	?><div class="column-<?php echo $column; $column++;?>">
				<?php
}
?>
	<ul>
	<?php foreach($this->children[$this->category->id] as $id => $child) : 
	
		if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) :
			if (!isset($this->children[$this->category->id][$id + 1])) :
				$class = ' class="last"';
			endif;
		?>
		<li<?php echo $class; ?>>
			<?php $class = ''; 
			$total_shown++;
			$i++;
			?>
			
			<?php if ($this->params->get('show_subcat_image') == 1 && $child->getParams()->get('image')) :?>
			<span class="subcat-image"><a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($child->id));?>">
				<img src="<?php echo $child->getParams()->get('image'); ?>"
				<?php if ($this->params->get('category_image_height')) : ?>
					height="<?php echo $this->params->get('category_image_height'); ?>"
				<?php endif; ?> /></a>
			</span>
			<br />
			<?php endif; ?>
			
			
			<span class="item-title"><a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($child->id));?>">
				<?php echo $this->escape($child->title); ?></a>
			</span>
			<br />
			<?php if ( $this->params->get('show_cat_num_articles', 1)) : ?>
			<span class="item_products">
					<?php echo JText::_('MYMUSE_NUM_ITEMS') ; ?> <?php echo $child->product_total; ?>
			</span>
			<?php endif ; ?>

			<?php if ($this->params->get('show_subcat_desc') == 1) :?>
			<?php if ($child->description) : ?>
			<?php if ($this->params->get('subcat_desc_truncate')) : 
			$child->description = JHtmlString::truncate($child->description,$this->params->get('subcat_desc_truncate'));
		 	$child->description = str_replace("...",'',$child->description);
		 	$child->description = preg_replace("~</p>$~",' ...</p>',$child->description);
		
			endif; ?>
				<div class="category-desc">
					<?php echo JHtml::_('content.prepare', $child->description, '', 'com_content.category'); ?>
				</div>
			<?php endif; ?>
            <?php endif; ?>

			

			<?php if (count($child->getChildren()) > 0):
				$this->children[$child->id] = $child->getChildren();
				$this->category = $child;
				$this->maxLevel--;
				if ($this->maxLevel != 0) :
					echo $this->loadTemplate('children');
				endif;
				$this->category = $child->getParent();
				$this->maxLevel++;
			endif; ?>
		</li>
		<?php endif; ?>
		<?php 
			
			if($i == $break){
				echo '</ul>
				</div>
				<div class="column-'.$column.'">
				<ul>
				';
				$column++;
				$i=0;
			}
		?>		
		
		
		
		
	<?php endforeach; ?>
	</ul>
	</div>
<?php endif; ?>
</div>