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

if($product->product_physical){
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
<!--  START PRODUCT VIEW -->	
<!--  HEADING TITLE ICONS -->
<div class="mymuse">

<?php if ($this->params->get('show_page_heading', 0)) : ?>
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

<!--  END HEADING -->

<!-- PRODUCT ATTRIBUTES -->
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
<!-- END ATTRIBUTES -->

<!-- RECORDING DETAILS -->
<?php $useRecList = ($params->get('show_recording_details') && ($this->item->product_made_date or $this->item->product_full_time or $this->item->product_country
	or $this->item->product_publisher or $this->item->product_producer or $this->item->product_studio)); ?>
	
<?php if ($useRecList) : ?>
	<dl class="article-info">
	<dt class="article-info-term"><?php  echo JText::_('MYMUSE_RECORDING_DETAILS'); ?></dt>

<?php if ($this->item->product_made_date && $this->item->product_made_date > 0) : ?>
	<dd class="product_made_date">
	<?php echo JText::_('MYMUSE_PRODUCT_CREATED_LABEL'); ?> :
	<?php echo JHtml::_('date', $product->product_made_date, $this->escape(
		$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); ?>
	</dd>
<?php endif; ?>
<?php if ($this->item->product_full_time) : ?>
	<dd class="product_full_time">
	<?php echo JText::_('MYMUSE_PRODUCT_FULL_TIME_LABEL'); ?> :
	<?php echo $this->item->product_full_time; ?>
	</dd>
<?php endif; ?>
<?php if ($this->item->product_country) : ?>
	<dd class="product_country">
	<?php echo JText::_('MYMUSE_PRODUCT_COUNTRY_LABEL'); ?> :
	<?php echo $this->item->product_country; ?>
	</dd>
<?php endif; ?>
<?php if ($this->item->product_publisher) : ?>
	<dd class="product_publisher">
	<?php echo JText::_('MYMUSE_PRODUCT_PUBLISHER_LABEL'); ?> :
	<?php echo $this->item->product_publisher; ?>
	</dd>
<?php endif; ?>
<?php if ($this->item->product_producer) : ?>
	<dd class="product_producer">
	<?php echo JText::_('MYMUSE_PRODUCT_PRODUCER_LABEL'); ?> :
	<?php echo $this->item->product_producer; ?>
	</dd>
<?php endif; ?>
<?php if ($this->item->product_studio) : ?>
	<dd class="product_studio">
	<?php echo JText::_('MYMUSE_PRODUCT_STUDIO_LABEL'); ?> :
	<?php echo $this->item->product_studio; ?>
	</dd>
<?php endif; ?>
	</dl>
<?php endif; ?>
<div style="clear: both"></div>

<!-- END RECORDING DETAILS -->


<form method="post" action="<?php echo JURI::base() ?>index.php?Itemid=<?php echo $Itemid; ?>" onsubmit="return hasProduct(this,<?php echo $count; ?>);" name="mymuseform">
<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="task" value="addtocart" />
<input type="hidden" name="catid" value="<?php echo $product->catid; ?>" />

<!-- IMAGE INTROTEXT -->

<?php 

if( ($params->get('product_show_product_image') && $product->detail_image) || $params->get('show_intro')) :?>

   <?php if ($params->get('product_show_product_image') && $product->detail_image) : ?>
		<div class="product-image"><img
			<?php if($height) : ?>
			height="<?php echo $height; ?>"
			<?php endif; ?>
			src="<?php echo $product->detail_image;?>"
			alt="<?php echo $product->title;?>" 
			title="<?php echo $product->title;?>" 
			/>
		</div>
		
	<?php endif; ?>
	
		<div class="product-intro">
		<?php  if ($params->get('show_intro')) : ?>
			<?php echo $product->introtext ?>
		<?php endif ?>
		<?php if($product->introtext && $product->fulltext && $params->get('show_readmore')) : ?><br />
			<a href="#readmore" class="readon"><?php echo JText::_("MYMUSE_READ_MORE"); ?>
			<?php 
			if ($params->get('show_readmore_title', 0) != 0) :
				echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
			endif;
			?>
			
			</a>
		<?php endif ?>
		</div>
<?php endif; ?>
<!-- END IMAGE INTROTEXT -->
<div style="clear: both"></div>
<!--  PRODUCT PHYSICAL -->

		<?php if($product->product_physical){  ?>
		<h3><?php echo JText::_('MYMUSE_PRODUCT'); ?></h3> 
		<table class="mymuse_cart">
			<thead>
		    <tr>
		    	<th class="myselect" align="left" width="5%" ><?php echo JText::_('MYMUSE_SELECT'); ?></th>  
        		<th class="mytitle" align="left" width="55%" ><?php echo JText::_('MYMUSE_NAME'); ?></th>
       			<th class="myprice" align="center" width="20%"><?php echo JText::_('MYMUSE_COST'); ?></th>
       		<?php if ($params->get('product_show_quantity')) :?>
        		<th class="myquantity" align="left" width="20%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></th>
      	    <?php endif; ?>
      		</tr>
      		</thead>
			<tr>
				<td class="myselect"><span class="mycheckbox"><input type="checkbox" name="productid[]" 
				value="<?php echo $product->id; ?>" id="box<?php echo $check; $check++; ?>" 
				<?php if($count == 1){ ?>
				CHECKED="CHECKED"
				<?php } ?>
				/></span></td>
				<td class="mytitle" ><?php echo $product->title; ?></td>
				<td class="myprice"><?php  echo MyMuseHelper::printMoneyPublic($product->price);
				?></td>
			<?php if ($params->get('product_show_quantity')) :?>
				<td class="myquantity"><input class="inputbox" type="text" name="quantity[<?php echo $product->id; ?>]" size="2" value="1" /> 
				</td>

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
<!-- END PRODUCT PHYSICAL -->	
		
			
<!-- ITEMS   ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS -->
	<?php if(count($items) && !$items_select){  ?>
	<style type="text/css">
	@media (max-width: 767px) { 
	<?php foreach($product->attribute_sku as $a_sku){ ?>
		td.my<?php echo $a_sku->name ?>:before { content: "<?php echo JText::_($a_sku->name); ?>";}
		td.my<?php echo $a_sku->name ?>{
			text-align: left;
		}
		td.my<?php echo $a_sku->name ?>:before {
			white-space: nowrap;
			padding-right: 7%;
			margin-right: 7%;
			width: 23%;
			display: inline-block;
			border-right: 1px solid #ccc;
		}
		td.my<?php echo $a_sku->name ?> {
			clear: both;
		}
	}
		
	<?php } ?>
	</style>
		<h3><?php echo JText::_('MYMUSE_ITEMS'); ?></h3> 
		<table class="mymuse_cart">
			<thead>
		    <tr>
		    	<th class="myselect" align="left" width="5%" ><?php echo JText::_('MYMUSE_SELECT'); ?></th>
        		<th class="mytitle" align="left" width="55%" ><?php echo JText::_('MYMUSE_NAME'); ?>
				</th>
       			<?php foreach($product->attribute_sku as $a_sku){ ?>
						<th class="my<?php echo $a_sku->name ?>" align="left"><?php echo $a_sku->name; ?></th>
				<?php } ?>
       			
				<th class="myprice" align="center" width="20%"><?php echo JText::_('MYMUSE_COST'); ?></th>
        	<?php if ($params->get('product_show_quantity')) :?>
        		<th class="myquantity" align="left" width="20%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></th>
      	    <?php endif; ?>
      		</tr>
      		</thead>
			<?php 

			foreach($items as $item){  
				?>
			  		<tr>
        				<td class="myselect"><span class="mycheckbox"><input type="checkbox" name="productid[]" 
        				value="<?php echo $item->id; ?>" id="box<?php echo $check; $check++; ?>" /></span></td>
        				<td class="mytitle"><?php echo $item->title; ?></td>
        			<?php foreach($product->attribute_sku as $a_sku){ ?>
						<td class="my<?php echo $a_sku->name ?>"><?php echo $item->attributes[$a_sku->name]; ?></td>
					<?php } ?>
						<td class="myprice">
						<?php echo MyMuseHelper::printMoneyPublic($item->price); 
				?></td>
        			<?php if ($params->get('product_show_quantity')) :?>
						<td class="myquantity"><input class="inputbox" type="text" name="quantity[<?php echo $item->id; ?>]" size="2" value="1" /></td>
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
			<thead>
		    <tr>
		    	<th class="mytitle" align="left" width="45%" ><?php echo JText::_('MYMUSE_NAME'); ?></th>
		    	<th class="myselect" align="left" width="45%" ><?php echo JText::_('MYMUSE_SELECT'); ?></th>
        	<?php if ($params->get('product_show_quantity')) :?>
        		<th class="myquantity" align="left" width="20%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></th>
      	    <?php endif; ?>
      		</tr>
      		</thead>
      	<?php foreach($items as $item){ ?>
      		<tr>
      			<td class="mytitle"><?php echo $item->title; ?></td>
      			<td class="myselect"><?php echo $item->select; ?></td>
      			<?php if ($params->get('product_show_quantity')) :?>
						<td class="myquantity"><input class="inputbox" type="text" name="item_quantity[<?php echo $item->pidselect; ?>]" 
						id="item_quantity<?php echo $item->pidselect; ?>"
						size="2" value="1" 
						onchange="updateq(<?php echo $item->pidselect; ?>);"
						/> 
						</td>
				<?php endif; ?>
      		</tr>
      	<?php } ?>
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
<!--  END ITEMS -->


<!--  TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS  -->
<?php if(count($tracks)){ ?>
		<h3><?php echo JText::_('MYMUSE_DOWNLOADABLE_ITEMS'); ?></h3>

		<?php if($params->get('product_player_type') == "single"){ ?>
			<div id="product_player" 
			<?php if($params->get('product_player_height')){ ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>
			><?php echo $product->flash; ?>
			</div>
			<?php if($product->flash){ ?>
			<div><?php echo JText::_('MYMUSE_NOW_PLAYING');?> <span id="jp-title-li"></span></div>
			<?php } ?>
		<?php } ?>
		<?php if($params->get('product_player_type') == "playlist"){ ?>
			<div id="product_player" ><?php echo $product->flash; ?>
			</div>
			
		<?php } ?>
		<div style="clear: both"></div>
		
		<div class="clips petrol" 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		>
		
		
<?php if($params->get('product_show_tracks',1)){ ?>
		<table class="mymuse_cart">
			<thead>
		    <tr>
		    <?php  if($params->get('product_show_select_column', 1)){?>
		    	<th class="myselect" align="left" width="5%" ><?php echo JText::_('MYMUSE_SELECT'); ?></th>
        	<?php } ?>
        	
        		<th class="mytitle" align="left" width="55%" ><?php echo JText::_('MYMUSE_NAME'); ?></th>
       		
       		<?php  if($params->get('product_show_filetime', 0)){?>
       			<th class="mytime" align="center" width="10%"><?php echo JText::_('MYMUSE_TIME'); ?></th>
       		<?php } ?>
       		
       		<?php  if($params->get('product_show_filesize', 0)){?>
       			<th class="myfilesize" align="center" width="10%"><?php echo JText::_('MYMUSE_FILE_SIZE'); ?></th>
       		<?php } ?>
       		
       		<?php  if($params->get('product_show_cost_column', 1)){?>
       			<th class="myprice" align="center" width="10%"><?php echo JText::_('MYMUSE_COST'); ?></th>
       		<?php } ?>

       		<?php if($params->get('product_show_preview_column', 1) && $params->get('product_player_type') != "playlist"){ ?>
        		<th class="mypreviews" align="left" width="10%"><?php echo JText::_('MYMUSE_PREVIEWS'); ?></th>
      		<?php } ?>
      		</tr>
      		</thead>

      		
      		<?php foreach($tracks as $track){ ?>
			  		<tr>
			  		<!--  SELECT COLUMN -->
			  		<?php  if($params->get('product_show_select_column', 1)){?>
        				<td class="myselect">
        				<?php if($track->file_name || $track->product_allfiles) :?>
        				<span class="mycheckbox"><input type="checkbox" name="productid[]" 
        				value="<?php echo $track->id; ?>" id="box<?php echo $check; $check++; ?>" />
      						</span>
      					<?php  endif; ?>
      					</td>
      				<?php } ?>	
      					
      				<!--  TITLE COLUMN -->	
						<td class="mytitle"><?php echo $track->title; ?> 
      						<?php  if($track->product_allfiles == "1"){ 
								echo "(".JText::_("MYMUSE_ALL_TRACKS").")";
					 		} ?>
					 		<?php if($track->introtext && $track->introtext != $track->title){ 
					 			echo "<br />".$track->introtext;
							}?>
      					</td>
      				<!--  TIME COLUMN -->
        			<?php  if($params->get('product_show_filetime', 0)){?>	
        				<td class="mytime">
        				<?php echo $track->file_time ?>
        				</td>
        			<?php } ?>
        			<!--  FILE SIZE COLUMN -->
        			<?php  if($params->get('product_show_filesize', 0)){?>	
        				<td class="myfilesize">
        				<?php 
        				if(!$track->product_allfiles){
        					echo "(".MyMuseHelper::ByteSize($track->file_length).")"; 
						}?>
        				</td>
        			<?php } ?>
					<!--  COST COLUMN -->
        			<?php  if($params->get('product_show_cost_column', 1)){?>	
        				<td class="myprice">
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
        				<td class="mypreviews tracks jp-gui ui-widget ui-widget-content ui-corner-all"><?php echo $track->flash; ?></td>
        			<?php }?>
        			
      				</tr>
      		<?php  } ?>
		</table>
	</div>
<!--  END TRACKS -->

<div style="clear: both"></div>
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

<?php } ?>
<!-- END TRACKS -->
</form>
<div style="clear: both"></div>
<!--  FULLTEXT -->
<?php if($product->fulltext != ''){ ?>	

	<div class="fulltext"><a name="readmore"></a>
	<?php echo $product->fulltext; ?> </div>

<?php } ?>

<div style="clear: both"></div>
<!--END FULL TEXT -->

<!-- COMMENTS -->
<?php 
$comments = JPATH_SITE . DS .'components' . DS . 'com_jcomments' . DS . 'jcomments.php';
  if (file_exists($comments)) {
    require_once($comments);
    echo JComments::showComments($product->id, 'com_mymuse', $product->title);
  }
?>
<!-- ENDCOMMENTS -->


<?php echo $this->item->event->afterDisplayProduct; ?>
</div>
<!--  end PRODUCT VIEW -->	