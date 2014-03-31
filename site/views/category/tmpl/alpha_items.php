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
$class = ' class="first"';
$height = $this->params->get('subcat_image_height',0);

if (count($this->children[$this->category->id]) > 0 && $this->maxLevel != 0) :
$count = count($this->children[$this->category->id]) ;
$alpha = explode(":",JText::_('MYMUSE_ALPHABET'));
$alphaarr = array();
foreach($alpha as $letter){
	$n = 0;
	foreach($this->children[$this->category->id] as $id => $child){

		if(substr_compare($letter, $child->title,0,1,TRUE) === 0){
			$alphaarr[$letter][$n] = $child;
			if ( $this->params->get('show_cat_num_articles', 1)) :
				$alphaarr[$letter][$n]->title .= ' ('.$child->product_total.')';
			endif;
			$n++;
		}
	}
}
$count = count($this->children[$this->category->id]) + count($alphaarr);
$break =  round($count / $this->params->get('subcat_columns',2),0,PHP_ROUND_HALF_DOWN);
$r = $count  %  $this->params->get('subcat_columns',2);
if($r){
	$break++;
}

$i = 0;
$total_shown = 0;
$column = 1;

?>

<div class="cols-<?php echo $this->params->get('subcat_columns',2); ?>">
	<?php foreach($alphaarr as $letter => $children) : 
	
	if(!$total_shown){
		//top of column
		?><div class="column-<?php echo $column; $column++;?>">
		<?php
	}
	$total_shown++;
	$i++;
	if($i >= $break){
		if ($total_shown == $count){
			$ulend = 1;
			echo '<!-- 1 -->
			</ul>
			</div>
			';
		}else{
			$ulend = 1;
			echo '<!-- 2 -->
			</ul>
			</div>
			<div class="column-'.$column.'" >
			';
			$column++;
			if($lettercount != $l){
				echo '<ul>';
			}
				
		}
		$i = 0;
	}
	?>
	<span class="alphabet"><?php 
	echo $letter; 
	
	?></span>
		<ul>
		<?php
		$lettercount = count($children );
		$l = 0;
		$ulend = 0;
		foreach($children as $child){
			$total_shown++; 
			$i++;
			$l++;
			if ($total_shown == $count) :
				$class = ' class="last"';
			endif;
			?>
			<li <?php echo $class; ?>>
				<?php $class = ''; ?>
				<?php if ($this->params->get('show_subcat_image') && $child->getParams()->get('image')) : ?>
                <span class="subcat-image">
                  <a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($child->id));?>">
                  <img 
                  <?php if($height):?>
                  	style="height: <?php echo $height; ?>px"
                  <?php  endif; ?>
                  src="<?php echo $child->getParams()->get('image'); ?>"/></a></span>
            <?php endif; ?>
				<span class="item-title"><a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($child->id));?>">
					<?php echo $this->escape($child->title); ?></a>
				</span>
			</li>
		

			<?php 
			

			
			if($total_shown == $count && $i != $break && !$ulend){
				//very end
				echo '<!-- 3 -->
			</ul>
		</div>
';
			}elseif($lettercount == $l && !$ulend){
				//end of a letter's cats
				echo '<!-- 4 -->
			</ul>
';
			}
		}
		?>
	<?php endforeach; ?>
</div>

<?php endif;
