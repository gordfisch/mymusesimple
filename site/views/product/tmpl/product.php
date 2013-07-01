<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
global $store, $shopper, $cart;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$product 	=& $this->item;
$items		=& $this->item->items;
$tracks		=& $this->item->tracks;
$params 	=& $this->params;
$user 		=& $this->user;
$print 		= $this->print;
$Itemid		= $this->Itemid;
$height 	= $this->params->get('product_product_image_height',0);
$check 		= 1;
$count		= 0;
$return_link = 'index.php?option=com_mymuse&view=product&task=product&id='.$product->id.'&catid='.$product->catid.'&Itemid='.$Itemid;
$canEdit	= $this->item->params->get('access-edit',0);
$items_select 	= $this->params->get('product_item_selectbox',0);

if($product->product_physical && !count($items)){
	$count++;
}
if(count($items) && !$items_select){ 
	$count += count($items);
}

if(count($tracks)){ 
	$count += count($tracks);
}
?>
<script type="text/javascript">
function hasProduct(that, count){
	<?php if($items_select && count($items)){ ?>
	item_count=<?php echo count($items); ?>;
	var pidselect=document.getElementById("pidselect");
    var pid = pidselect.options[pidselect.selectedIndex].value;
	if(pid != ""){
		return true;
	}
	<?php }?>
	//alert("count="+count);
	for(i = 1; i < count+1; i++)
	{
		var thisCheckBox = 'box' + i;
		if (document.getElementById(thisCheckBox).checked)
		{
			//alert(thisCheckBox+ " was checked");
			return true;
		}
	}
	alert("<?php echo JText::_("MYMUSE_PLEASE_SELECT_A_PRODUCT") ?>");
	return false;
}

</script>

<div class="contentpaneopen mymuse">
<div class="contentpane">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php if ($params->get('show_title')) : ?>
	<h2>
	<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
		<a href="<?php echo $this->item->readmore_link; ?>">
		<?php echo $this->escape($this->item->title); ?></a>
	<?php else : ?>
		<?php echo $this->escape($this->item->title); ?>
	<?php endif; ?>
	</h2>
<?php endif; ?>

<?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
	<ul class="actions">
	<?php if (!$this->print) : ?>
		<?php if ($params->get('show_print_icon')) : ?>
			<li class="print-icon">
			<?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?>
			</li>
		<?php endif; ?>

		<?php if ($params->get('show_email_icon')) : ?>
			<li class="email-icon">
			<?php echo JHtml::_('icon.email',  $this->item, $params); ?>
			</li>
		<?php endif; ?>

		<?php if ($canEdit) : ?>
			<!--  li class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
			</li -->
		<?php endif; ?>

	<?php else : ?>
		<li>
		<?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
		</li>
	<?php endif; ?>

	</ul>
<?php endif; ?>

<?php  if (!$params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>

<?php echo $this->item->event->beforeDisplayProduct; ?>

<?php $useDefList = (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_parent_category'))
	or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))
	or ($params->get('show_hits'))); ?>

<?php if ($useDefList) : ?>
	<dl class="article-info">
	<dt class="article-info-term"><?php  echo JText::_('MYMUSE_PRODUCT_INFO'); ?></dt>
<?php endif; ?>
<?php if ($params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
	<dd class="parent-category-name">
	<?php	$title = $this->escape($this->item->parent_title);
	$url = '<a href="'.JRoute::_(myMuseHelperRoute::getCategoryRoute($this->item->parent_id)).'">'.$title.'</a>';?>
	<?php if ($params->get('link_parent_category') and $this->item->parent_slug) : ?>
		<?php echo JText::sprintf('MYMUSE_PARENT', $url); ?>
	<?php else : ?>
		<?php echo JText::sprintf('MYMUSE_PARENT', $title); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_category')) : ?>
	<dd class="category-name">
	<?php 	$title = $this->escape($this->item->category_title);
	$url = '<a href="'.JRoute::_(myMuseHelperRoute::getCategoryRoute($this->item->catid)).'">'.$title.'</a>';?>
	<?php if ($params->get('link_category') and $this->item->catslug) : ?>
		<?php echo JText::sprintf('MYMUSE_PRODUCT_CATEGORY', $url); ?>
	<?php else : ?>
		<?php echo JText::sprintf('MYMUSE_PRODUCT_CATEGORY', $title); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_create_date')) : ?>
	<dd class="create">
	<?php echo JText::sprintf('MYMUSE_CREATED_DATE_ON', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'))); ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_modify_date')) : ?>
	<dd class="modified">
	<?php echo JText::sprintf('MYMUSE_LAST_UPDATED', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_publish_date')) : ?>
	<dd class="published">
	<?php echo JText::sprintf('MYMUSE_PUBLISHED_DATE_ON', JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
	<dd class="createdby">
	<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
	<?php if (!empty($this->item->contactid) && $params->get('link_author') == true): ?>
	<?php
		$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getItems('link', $needle, true);
		$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
	?>
		<?php echo JText::sprintf('MYMUSE_WRITTEN_BY', JHtml::_('link', JRoute::_($cntlink), $author)); ?>
	<?php else: ?>
		<?php echo JText::sprintf('MYMUSE_WRITTEN_BY', $author); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_hits')) : ?>
	<dd class="hits">
	<?php echo JText::sprintf('MYMUSE_PRODUCT_HITS', $this->item->hits); ?>
	</dd>
<?php endif; ?>
<?php if ($useDefList) : ?>
	</dl>
<?php endif; ?>
<form method="post" action="<?php echo JURI::base() ?>index.php?Itemid=<?php echo $Itemid; ?>" onsubmit="return hasProduct(this,<?php echo $count; ?>);" name="mymuseform">
<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="task" value="addtocart" />
<input type="hidden" name="catid" value="<?php echo $product->catid; ?>" />




<table class="mymusetable">
<?php if( ($params->get('product_show_product_image') && $product->detail_image) || $params->get('show_intro')) :?>
<tr>
	<td>
	<table class="mymusetable">
	  <tr>
   <?php if ($params->get('product_show_product_image') && $product->detail_image) : ?>
		<td 
		<?php if($height) : ?>
		 height="<?php echo $height; ?>"
		<?php endif; ?> 
		rowspan="2" valign="top">
			<div><img
			<?php if($height) : ?>
			height="<?php echo $height; ?>"
			<?php endif; ?>
			src="<?php echo $product->detail_image;?>"
			hspace="10" 
			alt="<?php echo $product->title;?>" 
			title="<?php echo $product->title;?>" 
			/></div>
		</td>
	<?php endif; ?>
	
		<td width="100%" height="10%" valign="top">
		<?php  if ($params->get('show_intro')) : ?>
			<?php echo $product->introtext ?>
		<?php endif ?>
		<?php if($product->introtext && $product->fulltext && $params->get('show_readmore')) : ?><br />
			<a href="#readmore" class="readon"><?php echo JText::_("MYMUSE_READ_MORE"); ?></a>
		<?php endif ?>
		</td>
		</tr>
	</table>
	</td>
</tr>	
<?php endif; ?>


<tr>
	<td>

		<?php if($product->product_physical){  ?>
		<h3><?php echo JText::_('MYMUSE_PRODUCT'); ?></h3> 
		<table class="mymuse_cart">
		    <tr class="mymuse_cart_top">
		    	<td class="mymuse_cart_top" align="left" width="5%" ><?php echo JText::_('MYMUSE_SELECT'); ?></td>  
        		<td class="mymuse_cart_top" align="left" width="55%" ><?php echo JText::_('MYMUSE_NAME'); ?></td>
       			<td class="mymuse_cart_top" align="center" width="20%"><?php echo JText::_('MYMUSE_COST'); ?></td>
       		<?php if ($params->get('product_show_quantity')) :?>
        		<td class="mymuse_cart_top" align="left" width="20%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></td>
      	    <?php endif; ?>
      		</tr>
			<tr>
				<td align="center"><span class="checkbox"><input type="checkbox" name="productid[]" 
				value="<?php echo $product->id; ?>" id="box<?php echo $check; $check++; ?>" 
				<?php if($count == 1){ ?>
				CHECKED="CHECKED"
				<?php } ?>
				/></span></td>
				<td><?php echo $product->title; ?></td>
				<td align="center"><?php  echo MyMuseHelper::printMoneyPublic($product->price);
				?></td>
			<?php if ($params->get('product_show_quantity')) :?>
				<td><input class="inputbox" type="text" name="quantity[<?php echo $product->id; ?>]" size="2" value="1" /> 
				<?php echo JText::_('MYMUSE_QUANTITY'); ?></td>

			<?php endif; ?>
			</tr>
		</table>
		

		<table class="mymuse_cart">
	  		<tr>	
				<td width="30%"><input class="button" type="submit" value="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>"
				title="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td width="30%"><input class="button" type="button" value="<?php echo JText::_('MYMUSE_CANCEL');; ?>" 
				title="<?php echo JText::_('MYMUSE_CANCEL'); ?>" onclick="window.location='<?php echo htmlentities($return_link); ?>'" /></td>
	  		</tr>
		</table>
		<br />
		<br />
		
		


		<?php } ?>
		
	<?php if(count($items) && !$items_select){  ?>
		<h3><?php echo JText::_('MYMUSE_ITEMS'); ?></h3> 
		<table class="mymuse_cart">
		    <tr class="sectiontableheader">
		    	<td class="mymuse_cart_top" align="left" width="5%" ><?php echo JText::_('MYMUSE_SELECT'); ?></td>
        		<td class="mymuse_cart_top" align="left" width="55%" ><?php echo JText::_('MYMUSE_NAME'); ?>
				</td>
       			<?php foreach($product->attribute_sku as $a_sku){ ?>
						<td class="mymuse_cart_top" align="left"><?php echo $a_sku->name; ?></td>
				<?php } ?>
       			
				<td class="mymuse_cart_top" align="center" width="20%"><?php echo JText::_('MYMUSE_COST'); ?></td>
        	<?php if ($params->get('product_show_quantity')) :?>
        		<td class="mymuse_cart_top" align="left" width="20%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></td>
      	    <?php endif; ?>
      		</tr>
			<?php 

			foreach($items as $item){  
				?>
			  		<tr>
        				<td align="center"><span class="checkbox"><input type="checkbox" name="productid[]" 
        				value="<?php echo $item->id; ?>" id="box<?php echo $check; $check++; ?>" /></span></td>
        				<td><?php echo $item->title; ?></td>
        			<?php foreach($product->attribute_sku as $a_sku){ ?>
						<td><?php echo $item->attributes[$a_sku->name]; ?></td>
					<?php } ?>
						<td align="center">
						<?php echo MyMuseHelper::printMoneyPublic($item->price); 
				?></td>
        			<?php if ($params->get('product_show_quantity')) :?>
						<td><input class="inputbox" type="text" name="quantity[<?php echo $item->id; ?>]" size="2" value="1" /></td>
					<?php endif; ?>
      				</tr>
      		<?php  } ?>
		</table>
		<table class="mymuse_cart">
	  		<tr>	
				<td width="30%"><input class="button" type="submit" value="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>"
				title="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td width="30%"><input class="button" type="button" value="<?php echo JText::_('MYMUSE_CANCEL');; ?>" 
				title="<?php echo JText::_('MYMUSE_CANCEL'); ?>" onclick="window.location='<?php echo htmlentities($return_link); ?>'" /></td>
	  		</tr>
		</table>
		<br />
		<br />
	<?php } ?>

	<?php 
		//select option
		if(count($items) && $items_select){  ?>
		<h3><?php echo JText::_('MYMUSE_ITEMS'); ?></h3> 
		<script type="text/javascript">
		function updateq (sid){
			var element = 'pidselect' + sid;
			var pidselect=document.getElementById(element);
		    var pid = pidselect.options[pidselect.selectedIndex].value;
		    //alert("chosen pid = "+pid);
		    var qid = 'quantity'+pid;
		    var qchosen =document.getElementById(qid);
		    var iid = "item_quantity" + sid
		    var qbox=document.getElementById(iid);
		    var q = qbox.value;
		    //alert("quantity = "+q);

		    qchosen.value = q;
		    //alert(qchosen.value);
		    return true;
		
		}
		</script>
		<table class="mymuse_cart">
		    <tr class="sectiontableheader">
		    	<td class="sectiontableheader mymuse_cart_top" align="left" width="45%" ><?php echo JText::_('MYMUSE_NAME'); ?></td>
		    	<td class="sectiontableheader mymuse_cart_top" align="left" width="45%" ><?php echo JText::_('MYMUSE_SELECT'); ?></td>
        	<?php if ($params->get('product_show_quantity')) :?>
        		<td class="sectiontableheader mymuse_cart_top" align="left" width="20%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></td>
      	    <?php endif; ?>
      		</tr>
      	<?php foreach($items as $item){ ?>
      		<tr>
      			<td><?php echo $item->title; ?></td>
      			<td><?php echo $item->select; ?></td>
      			<?php if ($params->get('product_show_quantity')) :?>
						<td><input class="inputbox" type="text" name="item_quantity[<?php echo $item->pidselect; ?>]" 
						id="item_quantity<?php echo $item->pidselect; ?>"
						size="2" value="1" 
						onchange="updateq(<?php echo $item->pidselect; ?>);"
						/> 
						</td>
				<?php endif; ?>
      		</tr>
      	<?php } ?>
		</table>
		<table width="100%" border="0">
	  		<tr>	
				<td width="30%"><input class="button" type="submit" value="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>"
				title="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td width="30%"><input class="button" type="button" value="<?php echo JText::_('MYMUSE_CANCEL');; ?>" 
				title="<?php echo JText::_('MYMUSE_CANCEL'); ?>" onclick="window.location='<?php echo htmlentities($return_link); ?>'" /></td>
	  		</tr>
		</table>
		<br />
		<br />
<?php } ?>

	
<?php if(count($tracks)){ 
	?>
		<h3><?php echo JText::_('MYMUSE_DOWNLOADABLE_ITEMS'); ?></h3>

		<?php if($params->get('product_player_type') == "single"){ ?>
		
			<div id="product_player" 
			<?php if($params->get('product_player_height')){ ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>
			><?php echo $product->flash; ?>
			</div><div id="playing_title"></div>
		<?php } ?>
		<?php if($params->get('product_player_type') == "playlist"){ ?>
			<div id="product_player" ><?php echo $product->flash; ?>
			</div>
			
		<?php } ?>
		<div class="clips petrol" 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		>
		<div style="clear: both"></div>
<?php if($params->get('product_show_tracks',1)){ ?>
		<table class="mymuse_cart">
		    <tr class="sectiontableheader">
		    <?php  if($params->get('product_show_select_column', 1)){?>
		    	<td class="mymuse_cart_top" align="left" width="5%" ><?php echo JText::_('MYMUSE_SELECT'); ?></td>
        	<?php } ?>
        	
        		<td class="mymuse_cart_top" align="left" width="55%" ><?php echo JText::_('MYMUSE_NAME'); ?></td>
       		
       		<?php  if($params->get('product_show_filetime', 0)){?>
       			<td class="mymuse_cart_top" align="center" width="10%"><?php echo JText::_('MYMUSE_TIME'); ?></td>
       		<?php } ?>
       		
       		<?php  if($params->get('product_show_cost_column', 1)){?>
       			<td class="mymuse_cart_top" align="center" width="10%"><?php echo JText::_('MYMUSE_COST'); ?></td>
       		<?php } ?>

       		<?php if($params->get('product_show_preview_column', 1) && $params->get('product_player_type') != "playlist"){ ?>
        		<td class="mymuse_cart_top" align="left" width="20%"><?php echo JText::_('MYMUSE_PREVIEWS'); ?></td>
      		<?php } ?>
      		</tr>
      		
      		<?php foreach($tracks as $track){ ?>
			  		<tr>
			  		<!--  SELECT COLUMN -->
			  		<?php  if($params->get('product_show_select_column', 1)){?>
        				<td align="center" valign="top">
        				<?php if($track->file_name || $track->product_allfiles) :?>
        				<span class="checkbox"><input type="checkbox" name="productid[]" 
        				value="<?php echo $track->id; ?>" id="box<?php echo $check; $check++; ?>" />
      						</span>
      					<?php  endif; ?>
      					</td>
      				<?php } ?>	
      					
      				<!--  TITLE COLUMN -->	
						<td valign="top"><?php echo $track->title; ?> 
						<?php  if($params->get('product_show_filesize', 1) && !$track->product_allfiles){?>
							<?php echo "(".MyMuseHelper::ByteSize($track->file_length).")"; ?>
						<?php } ?>
						
      						<?php  if($track->product_allfiles == "1"){ 
								echo "(".JText::_("MYMUSE_ALL_TRACKS").")";
					 		} ?>
					 		<?php if($track->introtext){ 
					 			//echo $track->introtext;
							}?>
      					</td>
      				<!--  TIME COLUMN -->
        			<?php  if($params->get('product_show_filetime', 0)){?>	
        				<td align="center" valign="top">
        				<?php echo $track->file_time ?>
        				</td>
        			<?php } ?>
					<!--  COST COLUMN -->
        			<?php  if($params->get('product_show_cost_column', 1)){?>	
        				<td align="center" valign="top">
        				<?php if($params->get('my_free_downloads') || $params->get('my_downloads_enable')){
        				if(isset($track->free_download) && $track->free_download){ ?>
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
        			<?php } ?>	
        			
        			<!--  PREVIEW COLUMN -->
        			<?php  if($params->get('product_show_preview_column', 1) && $params->get('product_player_type') != "playlist"){?>	
        				<td align="center" width="253" valign="top"><?php echo $track->flash; ?></td>
        			<?php }?>
      				</tr>
      		<?php  } ?>
		</table>
		</div>
	<?php } ?>
		<?php if($params->get('product_show_cartadd')){ ?>
		<table class="mymuse_cart">
	  		<tr>	
				<td width="30%"><input class="button" type="submit" value="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>"
				title="<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td width="30%"><input class="button" type="button" value="<?php echo JText::_('MYMUSE_CANCEL');; ?>" 
				title="<?php echo JText::_('MYMUSE_CANCEL'); ?>" onclick="window.location='<?php echo htmlentities($return_link); ?>'" /></td>
	  		</tr>
		</table>
		<?php } ?>
	</td>
</tr>
<?php } ?>
<tr>
	<td>&nbsp;</td>
</tr>
<?php if($product->fulltext != ''){ ?>	
<tr>
	<td valign="top" colspan="2"><a name="readmore"></a>
	<?php echo $product->fulltext; ?> </td>
</tr>
<?php } ?>
</table>
</form>

</div>
</div>
<?php echo $this->item->event->afterDisplayProduct; ?>