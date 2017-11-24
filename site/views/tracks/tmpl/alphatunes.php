<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3.0
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
$cells = 0;
$category_height = $this->params->get('category_image_height',0);
$product_height = $this->params->get('category_product_image_height',0);
$params = $this->params;
$Itemid		= $this->Itemid;
$check = 0;
$return_link = myMuseHelperRoute::getCategoryRoute($this->category->id); 
$count = 0;
$listOrder	= $this->sortColumn;
$listDirn	= $this->sortDirection;
$item = $this->category;
$document = JFactory::getDocument();
$url = "index.php?option=com_mymuse&task=ajaxtogglecart";
$products = array();
for ($i=0;$i<$this->cart["idx"];$i++) {
    $products[] = $this->cart[$i]['product_id'];
}

$js = '';
foreach($this->items as $track){

    			$js .= '
jQuery(document).ready(function($){
		$("#box_'.$track->id.'").click(function(e){
            
            //alert("'.$url.'");
            $.post("'.$url.'",
            {
                productid:"'.$track->id.'"
            },
            function(data,status)
            {
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                action = res.action;
                //alert(res.msg + "\nStatus: " + status);
                if(action == "deleted"){
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'/components/com_mymuse/assets/images/checkbox.png");
                }else{
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'/components/com_mymuse/assets/images/cart.png");
                }
                if(idx){
                    if(idx == 1){
                        txt = idx+" "+"item";
                    }else{
                        txt = idx+" "+"items";
                    }
                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.JText::_('MYMUSE_VIEW_CART').'</a>\';
                    $("#mini-cart-text").html(txt);
                    $("#mini-cart-link").html(link);
                }else{
                    
                    $("#mini-cart-text").html(" ");
                    $("#mini-cart-link").html("'.JText::_('MYMUSE_YOUR_CART_IS_EMPTY').'");
                }
                my_modal.open({content: msg+"<br />"+link, width: 300 });
            });
            
		}); 
	});

';
			
    $count++;
}

//flip price between formats
if(count($params->get('my_formats') > 1) && $params->get('my_price_by_product')){
	$js .= 'function flip_price(id) {'."\n";
	$js .= ' var formats = new Array();'."\n";
	foreach($params->get('my_formats') as $index=>$format) {
		$js .= 'formats['.$index.'] = "'.$format.'"'."\n";
	}
	foreach($params->get('my_formats') as $format) {
		$js .= 'var  '.$format.'_id = "#'.$format.'_"+id'."\n";
	}
	$js .= 'var select_id = "#variation_"+id+"_id"'."\n";

	for($i=0; $i < count($params->get('my_formats')); $i++){
		$js .= 'jQuery('.$params->get('my_formats')[$i].'_id).hide();'."\n";
	}
	$js .= '
			//alert(formats[jQuery(select_id).val()]+"_"+id);
			jQuery("#"+formats[jQuery(select_id).val()]+"_"+id).show();'."\n}";
}

$document->addScriptDeclaration($js);

?>
<script type="text/javascript">
function updateTop(idx)
{
    
    
}
function tableOrdering( order, dir, task )
{
	var form = document.adminForm;
	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}


</script>
<style>
/* to hide the player, uncomment this commented sction
#product_player {
  top: -1000px;
  position: absolute;
  z-index:2000000;
}

.tracks a.jp-play,
.tracks a.jp-pause {
	width:40px;
	height:40px;
    display:block;
    overflow: hidden;
    text-indent: -9999px;
}

.tracks a.jp-play {
	background: url("<?php echo $this->baseurl."/"; ?>plugins/mymuse/audio_jplayer/skin/jplayer.blue.monday.jpg") 0 0 no-repeat;
}
#main  .tracks a.jp-play:hover {
	background: url("<?php echo $this->baseurl."/"; ?>plugins/mymuse/audio_jplayer/skin/jplayer.blue.monday.jpg") -41px 0 no-repeat;
	
}
.tracks a.jp-pause {
	background: url("<?php echo $this->baseurl."/"; ?>plugins/mymuse/audio_jplayer/skin/jplayer.blue.monday.jpg") 0 -42px no-repeat;
}
*/
</style>

<div class="track-list<?php echo $this->pageclass_sfx;?>">

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
	
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
		<?php if ($this->params->get('show_description_image') && $this->category->params->get('image'))  : ?>
			<img src="<?php echo $this->category->params->get('image'); ?>"
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
<div class="clear"></div>


<?php if($this->params->get('show_minicart')) :?>
<!--  INLINE MINICART  -->
<!--  the cart box  -->
<div id="mini-cart-top">
<div id="mini-cart-content">
<div id="mini-cart-cart"></div>
<div id="mini-cart-text"><?php
if($this->cart['idx']) :
    $word = ($this->cart['idx'] == 1) ? "item" : "items"; 
    echo $this->cart['idx']." $word";
endif;
?></div>
<div id="mini-cart-link"><?php
if($this->cart['idx']) :
    echo '<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&task=showcart&Itemid='.$Itemid).'">'.JText::_('MYMUSE_VIEW_CART').'</a>';
else :
    echo JText::_('MYMUSE_YOUR_CART_IS_EMPTY');
endif;
?></div>
</div>
</div>
<div class="clear"></div>
<!--  END INLINE MINICART  -->
<?php  endif; ?>


<?php if($this->params->get('show_alphabet')) :?>
<!--  the alphabet  -->
<div id="alphabet">
	<?php foreach($this->alpha as $letter){
		echo $letter;
	}
	?>
</div>
<div class="clear"></div>
<?php  endif; ?>



<!--  the filters  -->

<form method="post" action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" id="adminForm">

<?php if ( ($this->params->get('filter') == "show" || $this->params->get('show_pagination_limit'))) : ?>
	<table class="mymuse_cart">
		<tr>
		<?php if ($this->params->get('filter') != 'hide') : ?>
			<td align="left" width="60%" nowrap="nowrap">
				<?php echo JText::_('MYMUSE_TITLE_FILTER').'&nbsp;'; ?>
				<input type="text" name="searchword" value="<?php echo $this->escape($this->state->get('list.searchword')); ?>" 
				style="width:80%"
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

<!--  the tracks  -->
<?php if(count($this->items)) {
	$tracks = $this->items;
	?>
	<!--  PLAYER -->
	<div class="tracks">
		<?php if($params->get('product_player_type') == "single"){ ?>
			<div id="product_player" 
			<?php if($params->get('product_player_height')){ ?>
				style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>	
			><?php echo $item->flash; ?>
			
			<div><?php echo JText::_('MYMUSE_NOW_PLAYING');?> <span id="jp-title-li"></span></div>
			</div>
		<?php } ?>
		<?php if($params->get('product_player_type') == "playlist"){ ?>
			<div id="product_player"
			<?php if($params->get('product_player_height')){ ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>><?php echo $item->flash; ?>
			</div>
			
		<?php } ?>	
		<div 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		class="clips petrol" >

		<table class="mymuse_cart tracks jp-gui ui-widget ui-widget-content ui-corner-all">
			<thead>
		    <tr>
				<?php if($params->get('list_show_artist')) { ?>
        		<th class="mymuse_cart_top myartist" >
        		<?php echo JHtml::_('grid.sort', 'MYMUSE_ARTIST', 'category_name', $listDirn, $listOrder); ?></th>
       			<?php } ?>
       			
       			<?php if($params->get('list_show_album')) { ?>
        		<th class="mymuse_cart_top myartist" >
        		<?php echo JHtml::_('grid.sort', 'MYMUSE_ALBUM', 'product_title', $listDirn, $listOrder); ?></th>
       			<?php } ?>
       			
       			<th class="mymuse_cart_top mytitle" align="center" width="40%">
       			<?php echo JHtml::_('grid.sort', 'MYMUSE_NAME', 'a.title', $listDirn, $listOrder); ?>
       			</th>
       			
       			<?php  if($params->get('product_show_filetime', 0)) :?>
       			<th class="mymuse_cart_top mytime" align="center" width="10%"><?php echo JText::_('MYMUSE_TIME'); ?></th>
       			<?php endif; ?>
       	
     			<?php if($params->get('list_show_price')) { ?>
        			<th class="mymuse_cart_top myprice" align="left" width="10%">
        			<?php echo JHtml::_('grid.sort', 'MYMUSE_CART_PRICE', 'a.price', $listDirn, $listOrder); ?>
        			</th>
                <?php } ?>
                <?php if(count($params->get('my_formats')) > 1) :?>
		    		<th class="mymuse_cart_top myselect" align="left" width="20%" ><?php echo JText::_('MYMUSE_FORMAT'); ?></th>
        		<?php endif;?>
                <?php if($params->get('list_show_discount')) { ?>
        			<th class="mymuse_cart_top mydiscount" align="left" width="20%">
        			<?php echo JHtml::_('grid.sort', 'MYMUSE_DISCOUNT', 'a.product_discount', $listDirn, $listOrder); ?>
        			</th>
                <?php } ?>
                
                <?php if ($this->params->get('list_show_date') && $this->params->get('order_date')) : 
                		$date = $this->params->get('order_date');
                ?>
				<th class="mymuse_cart_top myordering" id="tableOrdering2">
					<?php if ($date == "created") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'p.created', $listDirn, $listOrder); ?>
					<?php elseif ($date == "modified") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'p.modified', $listDirn, $listOrder); ?>
					<?php elseif ($date == "published") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'p.publish_up', $listDirn, $listOrder); ?>
					<?php elseif ($date == "product_made_date") : ?>
						<?php echo JHtml::_('grid.sort', 'MYMUSE_'.$date.'_DATE', 'p.product_made_date', $listDirn, $listOrder); ?>
					<?php endif; ?>
				</th>
				<?php endif; ?>
				
				<?php if($params->get('list_show_sales')){ ?>
				<th class="mymuse_cart_top mysales" align="center" width="10%">
        			<?php echo JHtml::_('grid.sort', 'MYMUSE_SALES', 's.sales', $listDirn, $listOrder); ?>
        		</th>	
                <?php } ?>
                
                <?php if($params->get('product_show_downloads')){ ?>
				<th class="mymuse_cart_top mydownloads" align="center" width="10%">
        			<?php echo JHtml::_('grid.sort', 'MYMUSE_DOWNLOADS', 'a.file_downloads', $listDirn, $listOrder); ?>
        		</th>	
                <?php } ?>
                
                
                <?php if($params->get('product_show_preview_column')  && $params->get('product_player_type') != "playlist") { ?>
                	<th class="mymuse_cart_top mypreview" align="center" width="10%"><?php echo JText::_('MYMUSE_PLAY'); ?></th>
                <?php } ?>
                
                <?php if($params->get('product_show_cartadd')) { ?>
                	<th class="mymuse_cart_top myslect" align="center" width="10%" ><?php echo JText::_('MYMUSE_ADD'); ?></th>
    			<?php } ?>
    
      		</tr>
      		</thead>
      		<?php foreach($tracks as $track){ ?>
      				
			  		<tr>
			  		<?php if($params->get('list_show_artist')) { ?>
			  		<!--  ARTIST COLUMN -->
                        <td class="myartist">
                            <?php 
                				if($params->get('category_product_link_titles')){
                            		$link = myMuseHelperRoute::getCategoryRoute($track->artistid);
                            		echo '<a href="'.$link.'">';
                            	}
                            	echo $track->artist_name;
                            	if($params->get('category_product_link_titles')){
                            		echo '</a>';
                            	}
                            ?>
                        </td>
                     <?php } ?>
                     
                     <?php if($params->get('list_show_album')) { ?>
			  		<!--  ALBUM COLUMN -->
                        <td class="myartist">
                            <?php
                            	if($params->get('category_product_link_titles')){
                            		$link = myMuseHelperRoute::getProductRoute($track->parentid,$track->artistid);
                            		echo '<a href="'.$link.'">';
                            	}		
                            	echo $track->product_title;
                            	if($params->get('category_product_link_titles')){
                            		echo '</a>';
                            	} 
                            ?>
                        </td>
                     <?php } ?>
                     
      				<!--  TITLE COLUMN -->			
						<td class="mytitle" valign="middle"><?php echo $track->title; ?> <br />
							<?php if($params->get('product_show_filesize') && $track->file_length) { ?>
								<?php echo "(".MyMuseHelper::ByteSize($track->file_length).")"; ?>
      						<?php } ?>
      						
      						<?php  if($track->product_allfiles == "1"){ 
								echo JText::_("MYMUSE_ALL_TRACKS");
					 			} ?>
      						</td>

        			<?php  if($params->get('product_show_filetime', 0)) { ?>
        			<!--  TIME COLUMN -->	
        				<td class="mytime">
        				<?php echo $track->file_time ?>
        				</td>
        			<?php } ?>
        			
        			<?php if($params->get('list_show_price')) : ?>
        			<!--  PRICE COLUMN -->	
        				<td class="myprice">
        				<?php 
        				if("1" == $params->get('my_price_by_product')) :
        					$first = 1;
        					
							foreach($this->params->get('my_formats') as $format) :
								$product_price = $track->price[$format];
        						echo '<div id="'.$format.'_'.$track->id.'" class="price"';
        						if(!$first):
        							echo ' style="display:none" ';
      							endif;
      							$first = 0;
 								echo '>'.MyMuseHelper::printMoneyPublic($product_price).'</div>';
 							endforeach;
 							
        				elseif($params->get('my_free_downloads') && isset($track->free_download) && $track->free_download) :
        					if($user->get('guest')) :
        						$menu = JFactory::getApplication()->getMenu();
        						$active = $menu->getActive();
        						$itemId = $active->id;
        						$link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
        						$link->setVar('return', base64_encode(JRoute::_(myMuseHelperRoute::getProductRoute($this->item->id, $this->item->catid, $this->item->language))));
        					else :
        						$link = $track->free_download_link;
        					endif;
        					?>
        				    <a class="free_download_link" href="<?php echo $link; ?>"
        				    ><img src="components/com_mymuse/assets/images/download_dark.png" border="0" /></a>
							<?php 
        				else :
        					
        					echo MyMuseHelper::printMoneyPublic($track->price);
        				
        				endif; ?>
        				</td>
        			<?php endif; ?>	
        			
        			
        			<?php if(count($params->get('my_formats')) > 1) :?>
        			<!--  FORMAT COLUMN -->
        				<td class="myformat">
        				<?php if(isset($track->variation_select)) :
      							echo $track->variation_select;
      						 endif;
      					?>
        				</td>
        			
                    <?php endif; ?>
                    
        			
        			<?php if($params->get('list_show_discount')) { ?>
        			<!--  DISCOUNT COLUMN -->	
        				<td class="mydiscount">
        				<?php echo MyMuseHelper::printMoneyPublic($track->product_discount); ?>
        				</td>
        			<?php } ?>
        			
        			<?php if ($this->params->get('list_show_date') && $track->displayDate) : ?>
        			<!--  DATE COLUMN -->	
					<td class="mydate-<?php  echo $date; ?>">
						<?php if($track->displayDate != "0000-00-00"){
							echo JHtml::_('date', $track->displayDate, $this->escape(
							$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); 
      					} ?>
					</td>
					<?php endif; ?>
					
					<?php if($params->get('list_show_sales')) { ?>
					<!--  SALES COLUMN -->	
        				<td class="mysales" ><?php echo $track->sales; ?></td>
        			<?php } ?>
        			
        			<?php if($params->get('product_show_downloads')){ ?>
        			<!-- DOWNLOADS COLUMN -->	
        				<td class="mydownloads" ><?php echo $track->file_downloads; ?></td>
        			<?php } ?>
        				
        			<?php if($params->get('product_show_preview_column') && $params->get('product_player_type') != "playlist"){ ?>
        				<!--  PREVIEW COLUMN -->	
        				<td class="mypreviews"><?php echo $track->flash; ?></td>
        			<?php }?>
        			
                    <?php if($params->get('product_show_cartadd')) { ?>
                    <!--  SELECT COLUMN -->	
                        <td class="myselect" height="42px">
        				<?php if($track->file_name || $track->product_allfiles) :?>
                        <a href="javascript:void(0)" id="box_<?php echo $track->id; ?>"><img id="img_<?php echo $track->id; ?>" src="<?php
                            if(in_array($track->id, $products)){
                                echo "components/com_mymuse/assets/images/cart.png";
                            }else{
                                echo "components/com_mymuse/assets/images/checkbox.png";
                            }
                        ?>"></a>
      					<?php  endif; ?>
      					</td>
      				<?php } ?>
      				</tr>
      		<?php  } ?>
		</table>

<?php } ?>
</div>
</div>
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

<?php  endif; ?>

<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="view" value="tracks" />
<input type="hidden" name="layout" value="alphatunes" />
<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="filter_alpha" value="<?php echo $this->filterAlpha; ?>" />
</form>
</div>

<div id='my_overlay' style="display:none"></div>
<div id='my_modal' style="display:none">
    <div id='my_content'>No JavaScript!</div>
    <a href='#' id='my_close'>close</a>
</div>

