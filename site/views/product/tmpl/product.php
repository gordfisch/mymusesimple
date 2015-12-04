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
    ';
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
		jQuery("#my_modal").fadeOut(2000,method.close);
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
				//alert("variation = "+myvariation);
			}else{
				myvariation = "";
			}
            //alert("'.$url.'");
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

<!--  HEADING TITLE ICONS -->
<?php echo $this->item->event->beforeDisplayHeader; ?>

<?php if ($this->params->get('show_page_heading', 0)) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>


<?php if($this->params->get('show_minicart')) :?>
<!--  the cart box  -->
<div id='carttop'>
<div class="carttop"></div><div id="carttop1"><?php
if($this->cart['idx']){
    $word = ($this->cart['idx'] == 1) ? "item" : "items"; 
    echo $this->cart['idx']." $word";
}
?></div>
<div id="carttop2"><?php
if($this->cart['idx']){
    echo '<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&task=showcart&Itemid='.$Itemid).'">'.JText::_('MYMUSE_VIEW_CART').'</a>';
}else{
    echo JText::_('MYMUSE_YOUR_CART_IS_EMPTY');
}
?></div>
</div>
<div class="clear"></div>
<?php  endif; ?>
	
	
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
<?php if( $params->get('show_title') ): ?>
    <h2 class="product-title"><?php echo $product->title ?></h2>
 <?php endif; ?>   
  

    <ul class="product-content-list">
        <li class="product-content-item">
            <span class="category">Artist</span>
            <span class="value"><?php echo $product->artist_title;?></span>
        </li>
        
        <?php if ($this->item->product_made_date && $this->item->product_made_date > 0) : ?>
        <li class="product-content-item">
            <span class="category"><?php echo JText::_('MYMUSE_PRODUCT_CREATED_LABEL'); ?></span>
            <span class="value"><?php echo $product->product_made_date;?></span>
        </li>
        <?php endif; ?>
        
        <?php if($params->get('show_category')) : ?>
        <li class="product-content-item">
            <span class="category"><?php echo JText::_('MYMUSE_CATEGORY'); ?></span>
            <span class="value"><?php echo $product->category_title;?></span>
        </li>
        <?php endif; ?>
        
        <li class="product-content-item">
            <span class="category"><?php echo JText::_('MYMUSE_CATALOG'); ?></span>
            <span class="value"><?php echo $product->product_sku;?></span>
        </li>
        
        <li class="product-content-item-actions">
            <span class="product-preview-play"><?php echo $tracks[0]->flash; ?></span>
            <span class="value"><?php if($all_tracks) : ?>
                <!--  PRODUCT ALL TRACKS -->
                <span class="product-full">
                    <span class="product-full-title"><?php echo JText::_('MYMUSE_BUY_FULL_RELEASE'); ?></span>
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

  <!-- END RELEASE INFO --> 
  
 <!-- START RECORDING INFO  -->
 <?php if( $params->get('show_recording_details') ): ?>   

<?php if ($this->item->product_made_date && $this->item->product_made_date > 0) : ?>
	<li class="product_made_date">
	<?php echo JText::_('MYMUSE_PRODUCT_CREATED_LABEL'); ?> :
	<?php echo JHtml::_('date', $product->product_made_date, $this->escape(
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
		
<!-- ITEMS   ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS -->
	<?php if(count($items) && !$items_select){  ?>

	<style type="text/css">
	@media (max-width: 767px) { 
	<?php foreach($product->attribute_sku as $a_sku){ ?>
		td.my<?php echo $a_sku->name ?>:before { 
			content: "<?php echo JText::_($a_sku->name); ?>";
		}
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
		
	<?php } ?>
	}
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

