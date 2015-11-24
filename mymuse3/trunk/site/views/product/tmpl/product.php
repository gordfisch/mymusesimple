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
$document 	= JFactory::getDocument();
$lang = JFactory::getLanguage();
$langtag = $lang->getTag();

if($this->params->get('my_price_by_product')){
	$product_price_physical = array('product_price' => $this->item->attribs->get('product_price_physical'));

	foreach($this->params->get('my_formats') as $format){
		$str = 'product_price_'.$format;
		$$str = array('product_price' => $this->item->attribs->get($str));
		$str = 'product_price_'.$format.'_all';
		$$str = array('product_price' => $this->item->attribs->get($str));
	}
}


$product_price_mp3      = array('product_price' => $this->item->attribs->get('product_price_mp3'));
$product_price_mp3_all  = array('product_price' => $this->item->attribs->get('product_price_mp3_all'));
$product_price_wav      = array('product_price' => $this->item->attribs->get('product_price_wav'));
$product_price_wav_all  = array('product_price' => $this->item->attribs->get('product_price_wav_all'));

$all_tracks = 0;
if(count($tracks)){ 
    foreach($tracks as $track){ 
        if($track->product_allfiles == 1){
            $all_tracks = $track;
        }
    }
}

$url = "index.php?option=com_mymuse&task=ajaxtogglecart";
$products = array();
for ($i=0;$i<$this->cart["idx"];$i++) {
	$products[] = $this->cart[$i]['product_id'];
}

// get the count of all products, items and tracks
if($product->product_physical){
	$count++;
}
if(count($items) && !$items_select){ 
	$count += count($items);
}

if(count($tracks)){ 
	$count += count($tracks);
}

//add javascript 
$js = '

function hasProduct(that, count){
';

if($items_select && count($items)){
$js .= 	'    item_count='.count($items).';
	var pidselect=document.getElementById("pidselect");
    var pid = pidselect.options[pidselect.selectedIndex].value;
	if(pid != ""){
		return true;
	}
    .';
}
$js .= '	for(i = 1; i < count+1; i++)
	{
		var thisCheckBox = "box" + i;
		if (document.getElementById(thisCheckBox).checked)
		{
			return true;
		}
	}
	alert("'.JText::_("MYMUSE_PLEASE_SELECT_A_PRODUCT").'");
	return false;
}

function tableOrdering( order, dir, task )
{
	var form = document.adminForm;
	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}

function flip_price(id) {
    //alert(id);
    var mp3_id = "#mp3_"+id;
    var wav_id = "#wav_"+id;
    var select_id = "#variation_"+id+"_id";
    

    if(jQuery(select_id).val() == "0") {
        jQuery(mp3_id).show();
        jQuery(wav_id).hide();
    } else {
        jQuery(wav_id).show();
        jQuery(mp3_id).hide();
    }
	
};
        
var my_modal = (function(){
    var 
    method = {}

    // Center the my_my_modal in the viewport
    method.center = function () {
		var top, left;

    	top = Math.max(jQuery(window).height() - jQuery("#my_modal").outerHeight(), 0) / 2;
    	left = Math.max(jQuery(window).width() - jQuery("#my_modal").outerWidth(), 0) / 2;

    	jQuery("#my_modal").css({
        	top:top + jQuery(window).scrollTop(), 
        	left:left + jQuery(window).scrollLeft()
    	});
	};

    // Open the my_modal
    method.open = function (settings) {
		
		jQuery("#my_content").empty().append(settings.content);

    	jQuery("#my_modal").css({
        	width: settings.width || "auto", 
        	height: settings.height || "auto"
   		})

    	method.center();

    	jQuery(window).bind(\'resize.my_modal\', method.center);
    	jQuery("#my_overlay").show();
		jQuery("#my_modal").show();
		jQuery("#my_modal").fadeOut(4000,method.close);
	};
		

    // Close the my_modal
    method.close = function () {
		jQuery("#my_modal").hide();
    	jQuery("#my_overlay").hide();
    	jQuery("#my_content").empty();
    	jQuery(window).unbind(\'resize.my_modal\');
	};
	
	;

    return method;
}());
jQuery(document).ready(function($){
	$("#my_modal").hide();
	$("#my_overlay").hide();
	jQuery("#my_close").click(function(e){
		e.preventDefault();
		my_modal.close();
	})
});
';
$url = JURI::Root()."index.php?option=com_mymuse&task=ajaxtogglecart";
foreach($tracks as $track){

	$js .= '
jQuery(document).ready(function($){
		$("#box_'.$track->id.'").click(function(e){
			if(typeof document.mymuseform.variation_'.$track->id.'_id !== "undefined"){	
				myvariation = document.mymuseform.variation_'.$track->id.'_id.value;
				alert("variation "+myvariation);
			}else{
				myvariation = "";
			}
            //alert("'.$url.'");
            $.post("'.$url.'",
            {
                productid:"'.$track->id.'",
                variationid:"myvariation"
                		
            },
            function(data,status)
            {
        
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                action = res.action;
                //alert(res.msg);
                if(action == "deleted"){
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'/components/com_mymuse/assets/images/checkbox.png");
                }else{
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'/components/com_mymuse/assets/images/cart.png");
                }
                if(idx){
                    txt = idx+" "+"'.JText::_('MYMUSE_PRODUCTS').'";
                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&layout=cart').'">'.JText::_('MYMUSE_VIEW_CART').'</a>\';
                    $("#carttop1").html(txt);
                    $("#carttop2").html(link);
                }else{

                    $("#carttop1").html(" ");
                    $("#carttop2").html("'.JText::_('MYMUSE_YOUR_CART_IS_EMPTY').'");
                }
                my_modal.open({content: msg+"<br />"+link, width: 400 });
            });

		});
	});

';
}
$document->addScriptDeclaration($js);
?>


<?php if($this->params->get('show_minicart')) :?>

<!--  the cart box  -->
<div id='carttop'>
<img src="components/com_mymuse/assets/images/cart.png" align="left"><span id="carttop1"><?php
if($this->cart['idx']){
    echo $this->cart['idx']." ".JText::_('MYMUSE_PRODUCTS');
}
?></span><br />
<span id="carttop2"><?php
if($this->cart['idx']){
    echo '<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&layout=cart').'">'.JText::_('MYMUSE_VIEW_CART').'</a>';
}else{
    echo JText::_('MYMUSE_YOUR_CART_IS_EMPTY');
}
?></span>
</div>
<div class="clear"></div>

<?php  endif; ?>
	
<form method="post" action="<?php JRoute::_('index.php?lang='.$langtag) ?>" onsubmit="return hasProduct(this,<?php echo $count; ?>);" name="mymuseform">
<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="task" value="addtocart" />
<input type="hidden" name="catid" value="<?php echo $product->catid; ?>" />
<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />

<!--  START PRODUCT VIEW -->	
<div class="mymuse">

<!-- IMAGE  -->
<?php if( ($params->get('product_show_product_image') && $product->detail_image)) :?>

   
		<div class="product-image"><img
			<?php if($height) : ?>
			height="<?php echo $height; ?>"
			<?php endif; ?>
			src="<?php echo JURI::Root().$product->detail_image;?>"
			alt="<?php echo $product->title;?>" 
			title="<?php echo $product->title;?>" 
			/>
		</div>
	

<?php endif; ?>
<!-- END IMAGE  -->

<!-- START RELEASE INFO  -->
    <div class="product-content">
    <span class="release">Release</span>
    <span class="release-title"><?php echo $product->title ?></span>
    
    <ul class="product-content-list">
        <li class="product-content-item">
            <span class="category">Artist</span>
            <span class="value"><?php echo $product->artist_title;?></span>
        </li>
        <li class="product-content-item">
            <span class="category">Release Date</span>
            <span class="value"><?php echo $product->product_made_date;?></span>
        </li>
        <li class="product-content-item">
            <span class="category">Label</span>
            <span class="value"><?php echo $product->category_title;?></span>
        </li>
        <li class="product-content-item">
            <span class="category">Catalog</span>
            <span class="value"><?php echo $product->product_sku;?></span>
        </li>
        <li class="product-content-item-actions">
            <span class="product-preview-play"><?php echo $tracks[0]->flash; ?></span>
            <span class="value"><?php if($all_tracks) : ?>
                <!--  PRODUCT ALL TRACKS -->
                <span class="product-full">
                    <span class="product-full-title">Buy Full Release</span>
                    <span id="mp3_<?php echo $all_tracks->id; ?>" class="price"><?php echo MyMuseHelper::printMoneyPublic($product_price_mp3_all); ?></span>
                    <span id="wav_<?php echo $all_tracks->id; ?>" class="price" style="display:none"><?php echo MyMuseHelper::printMoneyPublic($product_price_wav_all); ?></span>
                    <span class="format"> <?php 
                    if(isset($track->variation_select)) :
                        echo $track->variation_select;
                    endif;?>
                </span>
            </span>
            <?php endif;?>
        </li>
        <li class="product-content-item">
            <span class="category">Description</span>
            <span class="value">
                <div class="product-description">
                    <?php  if ($params->get('show_intro')) : ?>
                        <?php echo $product->introtext ?>
                    <?php endif ?>
                    
                    <?php if($product->introtext && $product->fulltext && $params->get('show_readmore')) : ?><br />
                        <a href="#readmore" class="readon"><?php echo JText::_("MYMUSE_READ_MORE"); ?>
                        <?php 
                        if ($params->get('show_readmore_title', 0) != 0) :
                            echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
                        endif;
                        ?></a>
                    <?php endif ?>
                </div>
            </span>
        </li>
    </ul>
    </div>
    
<!-- END RELEASE INFO -->
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
		<div class="mymuse-wrap">
				<div class="pull-left mymuse-button-left"><button class="button uk-button" type="submit" >
				<?php echo JText::_('MYMUSE_ADD_SELECTIONS_TO_CART'); ?></button></div>
				<div class="pull-right mymuse-button-right"><button 
				class="button uk-button" 
				type="button" 
				onclick="window.location='<?php echo htmlentities($return_link); ?>'"
				><?php echo JText::_('MYMUSE_CANCEL'); ?></button></div>
	  		<div style="clear: both;"></div>
		</div>
<?php } ?>
<!-- END PRODUCT PHYSICAL -->	
		
			
<!--  TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS  -->
<?php if(count($tracks)){ ?>
    
		<div class="track-count"><?php echo count($tracks); 
        if(count($tracks) == 1){ $word = "Track"; }else{ $word = "Tracks";} ?> 
            <?php echo $word; ?> Total</div>
            
		<table class="mymuse_cart tracks">
			<thead>
		    <tr>
        	<th class="mymuse_cart_top mytitle" align="center" width="40%"><?php echo JText::_('MYMUSE_NAME'); ?></th>
       		
       		<?php  if($params->get('product_show_filetime', 0)){?>
       			<th class="mymuse_cart_top mytime" align="center" width="10%"><?php echo JText::_('MYMUSE_TIME'); ?></th>
       		<?php } ?>
       		
       		<?php  if($params->get('product_show_filesize', 0)){?>
       			<th class="mymuse_cart_top myfilesize" align="center" width="10%"><?php echo JText::_('MYMUSE_FILE_SIZE'); ?></th>
       		<?php } ?>
       		
       		<?php if($params->get('product_show_sales', 0)){ ?>
        		<th class="mymuse_cart_top mysales" align="left" width="10%"><?php echo JText::_('MYMUSE_SALES'); ?></th>
      		<?php } ?>
      		
      		<?php if($params->get('product_show_downloads', 0)){ ?>
        		<th class="mymuse_cart_top mydownloads" align="left" width="10%"><?php echo JText::_('MYMUSE_DOWNLOADS'); ?></th>
      		<?php } ?>
       		
       		<?php  if($params->get('product_show_cost_column', 1)){?>
       			<th class="mymuse_cart_top myprice" align="center" width="10%"><?php echo JText::_('MYMUSE_COST'); ?></th>
       		<?php } ?>
            
            
		    	<th class="mymuse_cart_top myselect" align="left" width="20%" ><?php echo JText::_('MYMUSE_FORMAT'); ?></th>
        	
            
            <?php  if($params->get('product_show_select_column', 1)){?>
		    	<th class="mymuse_cart_top myselect" align="left" width="20%" ><?php echo JText::_('MYMUSE_SELECT'); ?></th>
        	<?php } ?>

       		<?php if($params->get('product_show_preview_column', 1) && $params->get('product_player_type') != "playlist"){ ?>
        		<th class="mymuse_cart_top mypreviews" align="left" width="10%"><?php echo JText::_('MYMUSE_PREVIEWS'); ?></th>
      		<?php } ?>
            
      		</tr>
      		</thead>

      		
      		<?php foreach($tracks as $track){ 
                if($track->product_allfiles == 1){
                    continue;
                }
                ?>
			  		<tr>

      					
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
        			
        			<!--  SALES COLUMN -->
        			<?php  if($params->get('product_show_sales', 0)){?>	
        				<td class="mysales">
        				<?php echo $track->sales; ?>
        				</td>
        			<?php } ?>
        			
        			<!--  DOWNLOADS COLUMN -->
        			<?php  if($params->get('product_show_downloads', 0)){?>	
        				<td class="mydownloads">
        				<?php echo $track->file_downloads; ?>
        				</td>
        			<?php } ?>
        			
					<!--  COST COLUMN -->
        			<?php  if($params->get('product_show_cost_column', 1)){?>	
        				<td class="myprice">
                        <div id="mp3_<?php echo $track->id; ?>" class="price"><?php echo MyMuseHelper::printMoneyPublic($product_price_mp3); ?></div>
                        <div id="wav_<?php echo $track->id; ?>" class="price" style="display:none"><?php echo MyMuseHelper::printMoneyPublic($product_price_wav); ?></div>
        				</td>
        			<?php } ?>	
                    
                    <!--  FORMAT COLUMN -->
        			
        				<td class="myselect">
        				<?php if(isset($track->variation_select)){
      							echo $track->variation_select;
      						}
      					?>
        				</td>
        			
                    
                    <!--  SELECT COLUMN -->
			  		<?php  if($params->get('product_show_select_column', 1)){?>
        				<td class="myselect"  nowrap>
                        <?php if($track->file_name || $track->product_allfiles) :?>
                        <a href="javascript:void(0)" id="box_<?php echo $track->id; ?>"><img id="img_<?php echo $track->id; ?>" src="<?php
                            if(in_array($track->id, $products)){
                                echo "components/com_mymuse/assets/images/cart.png";
                            }else{
                                echo "components/com_mymuse/assets/images/checkbox.png";
                            }
                        ?>"></a>
      					<?php  endif; ?>
                        
        				<?php if($track->file_name || $track->product_allfiles) :?>
        				<span class="mycheckbox"><input style="display:none;" type="checkbox" name="productid[]" 
        				value="<?php echo $track->id; ?>" id="box<?php echo $check; $check++; ?>" />
      					</span>

      					<?php  endif; ?>
      					</td>
      				<?php } ?>	
        			
        			<!--  PREVIEW COLUMN -->

        				<td class="mypreviews tracks"><?php echo $track->flash; ?></td>
        		

      				</tr>
      		<?php  } ?>
		</table>
	</div>
<!--  END TRACKS -->

<div style="clear: both"></div>


<?php } ?>
<!-- END TRACKS -->
</form>
<div style="clear: both"></div>


<?php if(isset($this->recommends_display)){ ?>
<!-- START RECOMMENDS -->

<?php echo $this->recommends_display; ?>

<!-- END RECOMMENDS -->
<?php } ?>

<?php echo $this->item->event->afterDisplayProduct; ?>

</div>
<!--  end PRODUCT VIEW -->	
</div>


<div id='my_overlay' style="display:none"></div>
<div id='my_modal' style="display:none">
    <div id='my_content'>No JavaScript Yet!</div>
    <a href='#' id='my_close'>close</a>
</div>

