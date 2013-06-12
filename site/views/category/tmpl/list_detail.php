<?php 
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
$cells = 0;
$category_height = $this->params->get('category_image_height',0);
$product_height = $this->params->get('category_product_image_height',0);
$params = $this->params;

$check = 0;
$return_link = myMuseHelperRoute::getCategoryRoute($this->category->id); 
$count = 0;
foreach ($this->items as $item) {
	foreach($item->tracks as $track){
		if($track->product_allfiles){
			continue;
		}
		$count++;
	}
}
?>
<script type="text/javascript">
function hasProduct(that, count){
	//alert("count="+count);
	for(i = 0; i < count; i++)
	{
		var thisCheckBox = 'box' + i;
		if (document.getElementById(thisCheckBox).checked)
		{
			//alert(thisCheckBox+ " was checked");
			return true;
		}else{
			//alert(thisCheckBox+ " was not checked");
		}
	}
	alert("<?php echo JText::_("MYMUSE_PLEASE_SELECT_A_PRODUCT") ?>");
	return false;
}
function tableOrdering( order, dir, task )
{
	var form = document.adminForm;
	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>
<!--  the category  -->
<?php if ($this->params->get('show_category_title', 1)) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<h1><?php echo $this->category->title; ?></h1>
</div>
<?php endif; ?>
<table class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" border="0">
<tr>
	<td style="padding-bottom:22px" class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" colspan="2">
	<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
		<img class="catimage" src="<?php echo $this->baseurl ."/". $this->category->getParams()->get('image');?>" 
		align="left" hspace="6"  alt="<?php echo $this->category->getParams()->get('image');?>" 
		<?php if($category_height){ echo 'height="'.$category_height.'"'; } ?>

		/>
	<?php endif; ?>
	<?php if ($this->params->get('show_description')) : ?>
		<?php echo $this->category->description; ?>
	<?php endif; ?>
</td>
</tr>
</table>
<br />
<br />

<!--  the products  -->
<?php if ($this->params->get('category_show_all_products')) : ?>
<form method="post" action="index.php" onsubmit="return hasProduct(this,<?php echo $count; ?>);">
<?php if ( ($this->params->get('filter') || $this->params->get('show_pagination_limit'))
&& $this->total > $this->limit
) : ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
		<?php if ($this->params->get('filter')) : ?>
			<td align="left" width="60%" nowrap="nowrap">
				<?php echo JText::_('MYMUSE_'.strtoupper($this->params->get('filter_type')) . '_FILTER').'&nbsp;'; ?>
				<input type="text" name="filter" value="<?php echo $this->escape($this->lists['filter']);?>" class="inputbox" onchange="document.adminForm.submit();" />
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
<?php endif; ?>


<table width="100%" border="0">
<?php $i = 0;
foreach ($this->items as $item) : 

?>
	<tr class="row<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<td>
	<?php if ($this->params->get('category_show_product_image')) : ?>
		<div style="float:left; margin:0px 12px 24px 24px;">
		<?php if($item->list_image) : ?>
		<span class="mymuse_product_image">
			<a href="<?php echo myMuseHelperRoute::getProductRoute($item->id,$this->category->id); ?>"--><img
			<?php if($product_height){ ?> height="<?php echo $product_height; ?>" <?php } ?>
			src="<?php echo $this->baseurl ."/".  $item->list_image;?>"
			hspace="6" alt="<?php echo $item->list_image;?>" /></a>
		<?php endif; ?>
			</div>
	<?php $cells++; endif; ?>
	

		<?php if ($item->access) : ?>
		<h2><a href="<?php echo myMuseHelperRoute::getProductRoute($item->id,$this->category->id); ?>">
		  <?php echo $this->escape($item->title); ?> 
		 </a>
		</h2>
		 
		
	
			<?php if ($this->params->get('product_show_intro_text')) : ?>
			<br /><?php echo $item->introtext; ?>
			<?php endif; ?>
		
		<?php 
		$cells++; 
		else : ?>
		<td valign="middle">
		<?php
			echo $this->escape($item->title).' : ';
			$link = JRoute::_('index.php?option=com_user&view=login');
			$returnURL = JRoute::_(MyMuseProductRoute::getProductRoute($item->ud, $item->catid), false);
			$fullURL = new JURI($link);
			$fullURL->setVar('return', base64_encode($returnURL));
			$link = $fullURL->toString();
		?>
		<a href="<?php echo $link; ?>">
			<?php echo JText::_( 'MYMUSE_REGISTER_TO_READ_MORE' ); ?></a>
		
		<?php $cells++;  
		endif; ?>
	</td>	
	<?php if ($this->params->get('show_date')) : ?>
	<td valign="middle">
		<?php echo $item->product_made_date; ?>
	</td>
	<?php $cells++; endif; ?>
	
	
	<?php if ($this->params->get('show_hits',1)) : ?>
	<td valign="middle" align="center">
		<?php echo $this->escape($item->hits) ? $this->escape($item->hits) : '-'; ?>
	</td>
	<?php $cells++; endif; ?>
</tr>
</table>


<!--  the tracks  -->
<?php if(count($item->tracks)) : 
			$tracks = $item->tracks;
	?><div class="tracks">
		<h3> <?php echo JText::_('MYMUSE_DOWNLOADABLE_ITEMS'); ?></h3>
				
		<?php if($params->get('product_player_type') == "single"){ ?>
			<div id="product_player" 
			<?php if($params->get('product_player_height')){ ?>
				style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>	
			><?php echo $item->flash; ?>
			</div>
			<div id="playing_title"></div>
		<?php } ?>
		<?php if($params->get('product_player_type') == "playlist"){ ?>
			<div id="product_player"
			<?php if($params->get('product_player_height')){ ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>><?php echo $item->flash; ?>
			</div>
			
		<?php } ?>
<?php if($params->get('product_show_tracks',1)){ ?>		
		<div 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		class="clips petrol" >

		<table class="mymuse_cart">
		    <tr class="sectiontableheader">
		    <?php  if($params->get('my_downloads_enable')){?>
		    	<td class="mymuse_cart_top" align="left" width="10%" ><?php echo JText::_('MYMUSE_SELECT'); ?></td>
        	<?php } ?>
        	
        		<td class="mymuse_cart_top" align="left" width="60%" ><?php echo JText::_('MYMUSE_NAME'); ?></td>
       		<?php  if($params->get('my_downloads_enable')){?>
       			<td class="mymuse_cart_top" align="center" width="10%"><?php echo JText::_('MYMUSE_COST'); ?></td>
       		<?php } ?>

       		<?php if($params->get('product_player_type') != "playlist"){ ?>
        		<td class="mymuse_cart_top" align="left" width="20%"><?php echo JText::_('MYMUSE_PREVIEWS'); ?></td>
      		<?php } ?>
      		</tr>
      		<?php foreach($tracks as $track){ ?>
			  		<tr>
			  		<?php  if($params->get('my_downloads_enable')){?>
        				<td align="center" height="42px" valign="middle">
        				<?php if($track->file_name || $track->product_allfiles) :?>
        				<span class="checkbox"><input type="checkbox" name="productid[]" 
        				value="<?php echo $track->id; ?>" id="box<?php echo $check; $check++; ?>" />
      						</span>
      					<?php  endif; ?>
      					</td>
      				<?php } ?>
      						
						<td valign="middle"><?php echo $track->title; ?> <?php if($track->file_length){echo "(".MyMuseHelper::ByteSize($track->file_length).")";} ?>
      						<?php  if($track->product_allfiles == "1"){ 
								echo JText::_("MYMUSE_ALL_TRACKS");
					 		} ?>
      						</td>

        				
        				<td align="center" valign="middle">
        			<?php if($params->get('my_free_downloads') || $params->get('my_downloads_enable')){
        				if(isset($track->free_download) && isset($track->free_download_link)){ ?>
        					<a class="free_download_link" 
        					target="_new"
        					href="<?php echo $track->free_download_link; ?>"><img src="components/com_mymuse/assets/images/download_dark.png" border="0" /></a>
        				<?php }else{ 
							echo MyMuseHelper::printMoneyPublic($track->price); 
        					 } 
        				}else{ 
        					echo MyMuseHelper::printMoneyPublic($track->price);
        				} 
        				?>
        				</td>
        				
        				
        			<?php if($params->get('product_player_type') != "playlist"){ ?>
        				<td align="center" width="53" valign="middle"><?php echo $track->flash; ?></td>
        			<?php }?>
      				</tr>
      		<?php  } ?>
		</table>
		
<?php } ?>
<div class="add_selections"><input class="button" type="submit" 
value="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>"
title="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>" /></div>

</div>

	<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php if ($this->params->get('show_pagination')) : ?>
<table>
<tr>
	<td colspan="<?php echo $cells; ?>">&nbsp;</td>
</tr>
<tr>
	<td align="center" colspan="<?php echo $cells; ?>" class="sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</td>
</tr>
<tr>
	<td colspan="<?php echo $cells; ?>" align="right">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>

<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="task" value="addtocart" />
<input type="hidden" name="catid" value="<?php echo $this->category->id; ?>" />

</form>



