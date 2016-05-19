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

$category 	=& $this->category;

$tracks		=& $this->items;
$params 	=& $this->params;
$user 		=& $this->user;
$print 		= $this->print;
$Itemid		= $this->Itemid;
$height 	= $this->params->get('product_product_image_height',0);
$check 		= 1;
$count		= 0;
$listOrder	= $this->sortColumn;
$listDirn	= $this->sortDirection;
$searchword = $this->searchword;
//$return_link = 'index.php?option=com_mymuse&view=product&task=product&id='.$category->id.'&catid='.$category->catid.'&Itemid='.$Itemid;
$canEdit	= $category->params->get('access-edit',0);
$lang = JFactory::getLanguage();
$langtag = $lang->getTag();

$uri = JFactory::getURI();
$prod_uri = $uri->toString();
$description = ($category->introtext != '')? $category->introtext : $category->title;
$document 	= JFactory::getDocument();
$document->setMetaData( 'og:site_name',$this->escape($this->store->title));
$document->setMetaData( 'og:type', 'article');
$document->setMetaData( 'og:url', $prod_uri);
$document->setMetaData( 'og:title', $this->escape($category->title));
$document->setMetaData( 'og:description', strip_tags($description));
$document->setMetaData( 'og:image', JURI::Root().$this->category->getParams()->get('image'));

$document->setMetaData( 'twitter:title', $this->escape($category->title));
$document->setMetaData( 'twitter:card', 'summary_large_image');
$document->setMetaData( 'twitter:site', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:creator', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:url', $prod_uri);
$document->setMetaData( 'twitter:description', strip_tags($description));
$document->setMetaData( 'twitter:image', JURI::Root().$this->category->getParams()->get('image'));
//print_pre($params);

if("1" == $this->params->get('my_price_by_product')){//price by product
	$category_price_physical = array('product_price' => $this->item->attribs->get('product_price_physical'));

	foreach($this->params->get('my_formats') as $format){
		$str = 'product_price_'.$format;
		$$str = array('product_price' => $this->item->attribs->get($str));
		$str = 'product_price_'.$format.'_all';
		$$str = array('product_price' => $this->item->attribs->get($str));
	}
}

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
if(count($tracks)){ 
	$count = count($tracks);
}

//add javascript 
$js = '

function hasProduct(that, count){
';

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
			
			
';

$js .= "
		var expanded = false;
		var playlistEpxanded = false;
		/*
			jQuery Visual Helpers
		*/
	jQuery(document).ready(function($){
		jQuery('#small-player').hover(function(){
			jQuery('#small-player-middle-controls').show();
			jQuery('#small-player-middle-meta').hide();
		}, function(){
			jQuery('#small-player-middle-controls').hide();
			jQuery('#small-player-middle-meta').show();

		});

		jQuery('#top-large-album').hover(function(){
			jQuery('#top-header').show();
			jQuery('#small-player').show();
		}, function(){
			if( !jQuery('#top-header').is(':hover') && !jQuery('#small-player').is(':hover') ){
				jQuery('#top-header').fadeOut(1000);
				jQuery('#small-player').fadeOut(1000);
			}
		});

		jQuery('#top-header').hover(function(){
			jQuery('#top-header').show();
			jQuery('#small-player').show();
		}, function(){

		});

		/*
			Toggles Album Art
		*/
		jQuery('#small-player-toggle').click(function(){
			jQuery('.hidden-on-collapse').show();
			jQuery('.hidden-on-expanded').hide();
			/*
				Is expanded
			*/
			expanded = true;

			jQuery('#small-player').css('border-top-left-radius', '0px');
			jQuery('#small-player').css('border-top-right-radius', '0px');
		});

		jQuery('#top-header-toggle').click(function(){
			jQuery('.hidden-on-collapse').hide();
			jQuery('.hidden-on-expanded').show();
			/*
				Is collapsed
			*/
			expanded = false;

			jQuery('#small-player').css('border-top-left-radius', '5px');
			jQuery('#small-player').css('border-top-right-radius', '5px');
		});

		jQuery('.playlist-toggle').click(function(){
			if( playlistEpxanded ){
				jQuery('#small-player-playlist').hide();

				jQuery('#small-player').css('border-bottom-left-radius', '5px');
				jQuery('#small-player').css('border-bottom-right-radius', '5px');

				jQuery('#large-album-art').css('border-bottom-left-radius', '5px');
				jQuery('#large-album-art').css('border-bottom-right-radius', '5px');

				playlistEpxanded = false;
			}else{
				jQuery('#small-player-playlist').show();

				jQuery('#small-player').css('border-bottom-left-radius', '0px');
				jQuery('#small-player').css('border-bottom-right-radius', '0px');

				jQuery('#large-album-art').css('border-bottom-left-radius', '0px');
				jQuery('#large-album-art').css('border-bottom-right-radius', '0px');

				playlistEpxanded = true;
			}
		})
	});
	";

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
			alert(formats[jQuery(select_id).val()]+"_"+id);
			jQuery(formats[jQuery(select_id).val()]+"_"+id).show();'."\n}";
}

    //print_pre($params->get('my_formats'));
        

$url = JURI::Root()."index.php?option=com_mymuse&task=ajaxtogglecart";
foreach($tracks as $track){

	$js .= '
	jQuery(document).ready(function($){
		$("#box_'.$track->id.'").click(function(e){
			if(typeof document.mymuseform.variation_'.$track->id.'_id !== "undefined"){	
				myvariation = document.mymuseform.variation_'.$track->id.'_id.value;
				//alert("variation = "+myvariation);

			}else{
				myvariation = 0;
			}
            $.post("'.$url.'",
            {
                "productid":"'.$track->id.'",
                "variation['.$track->id.']":myvariation
                		
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
                    if(idx == 1){
                        txt = idx+" "+"item";
                    }else{
                        txt = idx+" "+"items";
                    }
                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.JText::_('MYMUSE_VIEW_CART').'</a>\';
                    //alert(txt+" "+"mini-cart-text");
                    $("#mini-cart-text").html(txt);
                    $("#mini-cart-link").html(link);
                }else{

                    $("#mini-cart-text").html(" ");
                    $("#mini-cart-links").html("'.JText::_('MYMUSE_YOUR_CART_IS_EMPTY').'");
                }
                my_modal.open({content: msg+"<br />"+link, width: 300 });
            });

		});
	});

	';
	}
	

$document->addScriptDeclaration($js);

?>

<!--  HEADING / MINICART / TITLE / ICONS -->
<?php //echo $this->item->event->beforeDisplayHeader; ?>

<?php if ($this->params->get('show_page_heading', 0)) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading','')); ?>
	</h1>
<?php endif; ?>



<!--  INLINE PARENT  -->
<?php  if($this->parent->id > 0){	?>
<div id="parent">
<div class="mini-cart-top">
<div class="mini-cart-content">
<div class="mini-cart-parent-text">
<a href="<?php echo JRoute::_("index.php?option=com_mymuse&view=category&layout=alpha&id=".$this->parent->id); ?>">
<?php echo $this->parent->title; ?></a>
</div>
</div>
</div>
</div>
<?php }?>
<!--  END INLINE PARENT  -->

	
<?php if($this->params->get('show_minicart')) :?>
<!--  INLINE MINICART  -->
<div id="mini-cart">
<div class="mini-cart-top">
<div class="mini-cart-content">
<div class="mini-cart-cart"></div>
<div class="mini-cart-text"><?php
if($this->cart['idx']) :
    $word = ($this->cart['idx'] == 1) ? "item" : "items"; 
    echo $this->cart['idx']." $word";
endif;
?></div>
<div class="mini-cart-link"><?php
if($this->cart['idx']) :
    echo '<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&task=showcart&Itemid='.$Itemid).'">'.JText::_('MYMUSE_VIEW_CART').'</a>';
else :
    echo JText::_('MYMUSE_YOUR_CART_IS_EMPTY');
endif;
?></div>
</div>
</div>
</div>
<!--  END INLINE MINICART  -->
<?php  endif; ?>

<?php 
if ( ($this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit'))) : ?>
<!--  FILTERS  -->
	<form method="post" action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" id="adminForm">
	<input type="hidden" name="option" value="com_mymuse" />
	<input type="hidden" name="view" value="category" />
	<input type="hidden" name="catid" value="<?php echo $category->catid; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
	<input type="hidden" name="filter_alpha" value="<?php echo $this->filterAlpha; ?>" />	
	<table class="mymuse_cart">
		<tr>
		<?php if ($this->params->get('filter_field') != 'hide') : ?>
			<td align="left" width="60%" nowrap="nowrap">
				Filter
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
	</form>
<!--  END FILTERS  -->
<?php endif; ?>


<div class="clear"></div>
<?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
<!-- ICONS -->
	<ul class="actions">
	<?php if (!$this->print) : ?>
		<?php if ($params->get('show_print_icon')) : ?>
			<li class="print-icon">
			<?php echo JHtml::_('icon.print_popup',  $this->category, $params); ?>
			</li>
		<?php endif; ?>

		<?php if ($params->get('show_email_icon')) : ?>
			<li class="email-icon">
			<?php echo JHtml::_('icon.email',  $this->category, $params); ?>
			</li>
		<?php endif; ?>

		<?php if ($canEdit) : ?>
			<!--  li class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->category, $params); ?>
			</li -->
		<?php endif; ?>

	<?php else : ?>
		<li>
		<?php echo JHtml::_('icon.print_screen',  $this->category, $params); ?>
		</li>
	<?php endif; ?>

	</ul>
<!-- END ICONS -->
<?php endif; ?>

<?php  if (!$params->get('show_intro')) :
	//echo $this->item->event->afterDisplayTitle;
endif; ?>

<?php //echo $this->item->event->beforeDisplayProduct; ?>

<!--  END HEADING -->
	

<?php $useDefList = (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_parent_category'))
	or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))
	or ($params->get('show_hits'))); ?>

<?php if ($useDefList) : ?>
<!-- PRODUCT ATTRIBUTES -->
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
<!-- END ATTRIBUTES -->
<?php endif; ?>




<!--  START PRODUCT VIEW -->	
<div class="mymuse">

<!-- IMAGE  -->


   
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
		<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image') != "images/A_MyMuseImages/") : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"
			<?php if ($this->params->get('category_image_height')) : ?>
				style="height: <?php echo $this->params->get('category_image_height'); ?>px; "
			<?php endif; ?>
		/>
		<?php endif; ?>
		<?php if ($this->params->get('show_description') && $this->category->description != '') : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
		<?php endif; ?>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	

<!-- END IMAGE  -->


<div class="product-content">
<?php if( $params->get('show_title') ): ?>
    <h2 class="product-title">
    <?php if($searchword){ ?>
    Search
    <?php } ?>
    <?php echo $category->title ?>
    <?php if($searchword){ 
    	echo ": ".$searchword;
     } ?>
    
    </h2>
 <?php endif; ?>   
  
 <?php if( $params->get('show_rlease_info') ): ?>
 <!-- START RELEASE INFO  -->
    <ul class="product-content">

        <!--  PRODUCT ALL TRACKS -->
        <?php if($all_tracks) : ?>
        <li class="product-content-item-actions">
            <span class="mypreviews tracks jp-gui ui-widget"><?php echo $tracks[0]->flash; ?></span>
            <span class="value">
                
                <span class="product-full">
                    <span class="product-full-title">
                  <a href="javascript:void(0)" id="box_<?php echo $all_tracks->id; ?>">
                  <?php echo JText::_('MYMUSE_BUY_FULL_RELEASE'); ?> &#10010;</a>
                    </span>
            <?php
            if($this->params->get('my_price_by_product')) :
                foreach($params->get('my_formats') as $format) : 
                
                    echo '<span id="'.$format.'_'.$all_tracks->id.' class="price">';
                    $category_price_all = 'product_price_'.$format.'_all';
                    echo MyMuseHelper::printMoneyPublic($category_price_all); 
                    //echo $format." ".$category_price_all."<br />"; 
                    ?>
                    </span>
				<?php 
				endforeach;?>
                <span class="format"> <?php 
                if(isset($tracks[0]->variation_select)) :
                    echo $tracks[0]->variation_select;
                endif;?>
                	</span>
            <?php 
            else :?>
            		<span class="price"><?php 
            		echo MyMuseHelper::printMoneyPublic($all_tracks->price); ?>
            		</span>
            <?php 
            endif;?>
            </span>
        </li>
      <?php endif;?>
  </ul>
<br />  
 <?php endif;?>   
    <ul class="product-content">
    	<?php  if ($params->get('show_intro')) : ?>

         <?php endif ?>

  <!-- END RELEASE INFO --> 
  
 <!-- START RECORDING INFO  -->
 <?php if( $params->get('show_recording_details') ): ?>   

<?php if ($this->item->product_made_date && $this->item->product_made_date > 0) : ?>
	<li class="product_made_date">
	<?php echo JText::_('MYMUSE_PRODUCT_CREATED_LABEL'); ?> :
	<?php echo JHtml::_('date', $category->product_made_date, $this->escape(
		$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); ?>
	</li>
<?php endif; ?>
<?php if ($this->item->product_full_time) : ?>
	<li class="product_full_time">
	<?php echo JText::_('MYMUSE_PRODUCT_FULL_TIME_LABEL'); ?> :
	<?php echo $this->item->product_full_time; ?>
	</li>
<?php endif; ?>
<?php if ($this->item->product_country) : ?>
	<li class="product_country">
	<?php echo JText::_('MYMUSE_PRODUCT_COUNTRY_LABEL'); ?> :
	<?php echo $this->item->product_country; ?>
	</li>
<?php endif; ?>
<?php if ($this->item->product_publisher) : ?>
	<li class="product_publisher">
	<?php echo JText::_('MYMUSE_PRODUCT_PUBLISHER_LABEL'); ?> :
	<?php echo $this->item->product_publisher; ?>
	</li>
<?php endif; ?>
<?php if ($this->item->product_producer) : ?>
	<li class="product_producer">
	<?php echo JText::_('MYMUSE_PRODUCT_PRODUCER_LABEL'); ?> :
	<?php echo $this->item->product_producer; ?>
	</li>
<?php endif; ?>
<?php if ($this->item->product_studio) : ?>
	<li class="product_studio">
	<?php echo JText::_('MYMUSE_PRODUCT_STUDIO_LABEL'); ?> :
	<?php echo $this->item->product_studio; ?>
	</li>
<?php endif; ?>
	</ul>
	
<?php endif; ?>
 </div>
<div style="clear: both"></div>
<!-- END RECORDING INFO -->
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
<form method="post" action="<?php JRoute::_('index.php?lang='.$langtag) ?>" onsubmit="return hasProduct(this,<?php echo $count; ?>);" name="mymuseform">
<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="task" value="addtocart" />
<input type="hidden" name="catid" value="<?php echo $category->catid; ?>" />
<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />

<?php if(count($tracks)) : ?>
<!--  TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS  -->
		<h3><?php echo JText::_('MYMUSE_TRACKS'); ?></h3>

		<?php if($params->get('product_player_type') == "single") : ?>
			<div id="product_player" 
			<?php if($params->get('product_player_height')) : ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php endif; ?>
			><?php echo $category->flash; ?>
			
			<?php if($category->flash) : ?>
			<div><?php echo JText::_('MYMUSE_NOW_PLAYING');?> <span id="jp-title-li"></span></div>
			<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if($params->get('product_player_type') == "playlist"){ ?>
			<div id="product_player" ><?php echo $category->flash; ?>
			</div>
			
		<?php } ?>
		<div style="clear: both"></div>
		
		<div class="" 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		>
			
		<div class="track-count"><?php echo count($tracks); 
        if(count($tracks) == 1){ $word = "Track"; }else{ $word = "Tracks";} ?> 
            <?php echo $word; ?> Total</div>
            
		<table class="mymuse_cart tracks">
			<thead>
		    <tr>
        	<th class="mymuse_cart_top mytitle" align="center" width="40%">
        	<?php echo JHtml::_('grid.sort', 'MYMUSE_NAME', 'a.title', $listDirn, $listOrder); ?></th>
       		
       		<?php  if($params->get('product_show_artist', 0)) :?>
       			<th class="mymuse_cart_top myartist" align="center" width="30%">
       			<?php echo JHtml::_('grid.sort', 'MYMUSE_ARTIST', 'artist_name', $listDirn, $listOrder); ?></th>
       		<?php endif; ?>
       		
       		<?php  if($params->get('product_show_filetime', 0)) :?>
       			<th class="mymuse_cart_top mytime" align="center" width="10%">
       			<?php echo JHtml::_('grid.sort', 'MYMUSE_TIME', 'file_time', $listDirn, $listOrder); ?></th>
       		<?php endif; ?>
       		
       		<?php  if($params->get('product_show_filesize', 0)) :?>
       			<th class="mymuse_cart_top myfilesize" align="center" width="10%">
       			<?php echo JHtml::_('grid.sort', 'MYMUSE_FILE_SIZE', 'ABS(file_length)', $listDirn, $listOrder); ?></th>
       		<?php endif; ?>
       		
       		<?php if($params->get('product_show_sales', 0)) : ?>
        		<th class="mymuse_cart_top mysales" align="left" width="10%">sales
        		<?php echo JHtml::_('grid.sort', 'FILE_SALES', 'sales', $listDirn, $listOrder); ?></th>
      		<?php endif; ?>
      		
      		<?php if($params->get('product_show_downloads', 0)) : ?>
        		<th class="mymuse_cart_top mydownloads" align="left" width="10%">
        		<?php echo JHtml::_('grid.sort', 'FILE_DOWNLOADS', 'file_downloads', $listDirn, $listOrder); ?></th>
      		<?php endif; ?>
       		
       		<?php  if($params->get('product_show_cost_column', 1)) :?>
       			<th class="mymuse_cart_top myprice" align="center" width="10%">
       			<?php echo JHtml::_('grid.sort', 'MYMUSE_CART_PRICE', 'a.price', $listDirn, $listOrder); ?></th>
       		<?php endif; ?>
            
            <?php if(count($params->get('my_formats')) > 1) :?>
		    	<th class="mymuse_cart_top myselect" align="left" width="20%" ><?php echo JText::_('MYMUSE_FORMAT'); ?></th>
        	<?php endif;?>
            
            <?php  if($params->get('product_show_select_column', 1)) :?>
		    	<th class="mymuse_cart_top myselect" align="left" width="20%" ><?php echo JText::_('MYMUSE_SELECT'); ?></th>
        	<?php endif; ?>

       		<?php if($params->get('product_show_preview_column', 1) && $params->get('product_player_type') != "playlist") : ?>
        		<th class="mymuse_cart_top mypreviews" align="left" width="10%"><?php echo JText::_('MYMUSE_PREVIEWS'); ?></th>
      		<?php endif; ?>
            
      		</tr>
      		</thead>

      		
      		<?php 
      		foreach($tracks as $track) : 
                if($track->product_allfiles == 1) :
                    continue;
                endif;
             	?>
			  		<tr>

      				
      				<!--  TITLE COLUMN -->	
						<td class="mytitle"><?php echo $track->title; ?>
      						<?php if($track->product_allfiles == "1") : 
								echo "(".JText::_("MYMUSE_ALL_TRACKS").")";
					 		 endif; ?>
					 		<?php if($track->introtext && $track->introtext != $track->title) :
					 			echo '<br /><span class="track-text">'.$track->introtext.'</span>';
							endif; ?>
      					</td>
      					
      				<?php  if($params->get('product_show_artist', 0)) :?>
      				<!-- ARTIST COLUMN -->
      				<td class="myartist">
        				<a href="<?php 
						echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($track->artistid, true));?>">
						<?php echo $track->artist_name ?></a>
        			</td>
      				<?php endif; ?>	
      				
      				<!--  TIME COLUMN -->
        			<?php  if($params->get('product_show_filetime', 0)) : ?>	
        				<td class="mytime">
        				<?php echo $track->file_time ?>
        				</td>
        			<?php endif; ?>
        			
        			<!--  FILE SIZE COLUMN -->
        			<?php  if($params->get('product_show_filesize', 0)) : ?>	
        				<td class="myfilesize">
        				<?php 
        				if(!$track->product_allfiles) :
        					echo "(".MyMuseHelper::ByteSize($track->file_length).")"; 
						endif; ?>
        				</td>
        			<?php endif; ?>
        			
        			<!--  SALES COLUMN -->
        			<?php  if($params->get('product_show_sales', 0)) : ?>	
        				<td class="mysales">
        				<?php echo $track->sales; ?>
        				</td>
        			<?php endif; ?>
        			
        			<!--  DOWNLOADS COLUMN -->
        			<?php  if($params->get('product_show_downloads', 0)) : ?>	
        				<td class="mydownloads">
        				<?php echo $track->file_downloads; ?>
        				</td>
        			<?php endif; ?>
        			
					<!--  COST COLUMN -->
        			<?php  if($params->get('product_show_cost_column', 1)) :?>	
        				<td class="myprice">
        				<?php 
        				if("1" == $params->get('my_price_by_product')) :
        					$first = 1;
							foreach($this->params->get('my_formats') as $format) :
								$category_price = 'product_price_'.$format;
        						echo '<div id="'.$format.'_'.$track->id.'" class="price"';
        						if(!$first):
        							echo ' style="display:none" ';
      							endif;
      							$first = 0;
 								echo '>'.MyMuseHelper::printMoneyPublic($$category_price).'<div>';
 							endforeach;
        				else :  
        					echo MyMuseHelper::printMoneyPublic($track->price);
        				
        				endif; ?>
        				</td>
        			<?php endif; ?>	
                    
                    <!--  FORMAT COLUMN -->
        			<?php if(count($params->get('my_formats')) > 1) :?>
        				<td class="myselect">
        				<?php if(isset($track->variation_select)) :
      							echo $track->variation_select;
      						 endif;
      					?>
        				</td>
        			
                    <?php endif; ?>
                    <!--  SELECT COLUMN -->
			  		<?php  if($params->get('product_show_select_column', 1)) :?>
        				<td class="myselect"  nowrap>
                        <?php if($track->file_name || $track->product_allfiles) :?>
                        <a href="javascript:void(0)" id="box_<?php echo $track->id; ?>"><img id="img_<?php echo $track->id; ?>" src="<?php
                            if(in_array($track->id, $products)) :
                                echo "components/com_mymuse/assets/images/cart.png";
                            else :
                                echo "components/com_mymuse/assets/images/checkbox.png";
                            endif;
                        ?>"></a>
      					<?php  endif; ?>
                        
        				<?php if($track->file_name || $track->product_allfiles) :?>
        				<span class="mycheckbox"><input style="display:none;" type="checkbox" name="productid[]" 
        				value="<?php echo $track->id; ?>" id="box<?php echo $check; $check++; ?>" />
      					</span>

      					<?php  endif; ?>
      					</td>
      				<?php  endif; ?>	
        			
        			
        			<?php  if($params->get('product_show_preview_column', 1)) :?>
        				<!--  PREVIEW COLUMN -->
        				<td class="mypreviews tracks jp-gui ui-widget"><?php echo $track->flash; ?></td>
        			<?php  endif; ?>	

      				</tr>
      		<?php  endforeach; ?>
		</table>
	</div>
<!-- END TRACKS -->
</form>
<div style="clear: both"></div>
<?php endif; ?>



<?php if(isset($this->recommends_display)) : ?>
<!-- START RECOMMENDS -->
<?php echo $this->recommends_display; ?>
<!-- END RECOMMENDS -->
<?php endif; ?>

<?php //echo $this->item->event->afterDisplayProduct; ?>

<!--  end PRODUCT VIEW -->	
</div>


<div id='my_overlay' style="display:none"></div>
<div id='my_modal' style="display:none">
    <div id='my_content'>No JavaScript!</div>
    <a href='#' id='my_close'>close</a>
</div>

