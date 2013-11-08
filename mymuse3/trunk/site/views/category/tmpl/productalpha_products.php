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
?>

<?php 

if (count($this->items) > 0) : 
$count = count($this->items);

$alpha = explode(":",JText::_('MYMUSE_ALPHABET'));
$alphaarr = array();
foreach($alpha as $letter){
	$n = 0;
	foreach($this->items as $id => $child){

		if(mb_stripos($child->title, $letter ) === 0){
			$alphaarr[$letter][$n] = $child;
			$n++;
		}
	}
}
$break = round(count($this->items) / $this->params->get('num_columns'));
$r = $count  %  $this->params->get('num_columns');
if($r){ $break++; }

$i = 0;
$total_shown = 0;
$column = 1;
?>
<h3><?php echo JText::_('MYMUSE_PRODUCTS'); ?></h3>
<div class="cols-<?php echo $this->params->get('num_columns'); ?>">
	<?php foreach($alphaarr as $letter => $children) : 
	
	if(!$total_shown){
		//top of column
		?><div class="column-<?php echo $column; $column++;?>">
		<?php
	}
	
	?>
	<span class="alphabet"><?php echo $letter; ?></span>
		<ul>
		<?php
		$lettercount = count($children );
		$l = 0;
		foreach($children as $child){
            //print_pre($child); exit;
			$total_shown++; 
			$i++;
			$l++;
			if ($total_shown == $count) :
				$class = ' class="last"';
			endif;
			?>
			<li <?php echo $class; ?>>
				<?php $class = ''; ?>
				<span class="item-title"><a href="<?php echo JRoute::_(MyMuseHelperRoute::getProductRoute($child->id ,$child->catid));?>">
					<?php echo $this->escape($child->title); ?></a>  - 
                    <a href="<?php echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($child->catid)); ?>"><?php echo $child->category_title; ?></a>
				</span>
			</li>
		

			<?php 
			
			if($i == $break){
				if ($total_shown == $count){
					echo '</ul></div>';
				}else{
					echo '</ul></div>
					<div class="column-'.$column.'" >';
					$column++;
					if($lettercount != $l){
						echo '<ul>';
					}
					
				}
				$i = 0;
			}
			
			if($total_shown == $count && $i != $break){
				//very end
				echo '</ul></div>';
			}elseif($lettercount == $l){
				//end of a letter's cats
				echo '</ul>';
			}
		}
		?>
	<?php endforeach; ?>
</div>

<?php endif;
