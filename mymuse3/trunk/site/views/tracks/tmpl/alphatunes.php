<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3.0
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
$item = $this->category;
$document = JFactory::getDocument();
$url = "index.php?option=com_mymuse&task=ajaxtogglecart";
$products = array();
for ($i=0;$i<$this->cart["idx"];$i++) {
    $products[] = $this->cart[$i]['product_id'];
}


$js = '';
foreach($this->items as $track){
    if($track->product_allfiles){
        continue;
    }
    			$js .= '
jQuery(document).ready(function($){
		$("#box_'.$track->id.'").click(function(e){
            
            //alert("'.$track->id.'");
            $.post("'.$url.'",
            {
                productid:"'.$track->id.'"
            },
            function(data,status)
            {
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                //alert("msg: " + res.msg + "\nStatus: " + status);
                if(msg == "deleted"){
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
            });
            
		}); 
	});

';
			
    $count++;
}
$document->addScriptDeclaration($js);
$listOrder	= $this->sortColumn;
$listDirn	= $this->sortDirection;
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
/*
#product_player {
  //top: -1000px;
  //position: absolute;
  //z-index:2000000;
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
	//background: url("<?php echo $this->baseurl."/"; ?>plugins/mymuse/audio_html5/skin/jplayer.blue.monday.jpg") 0 0 no-repeat;
}
#main  .tracks a.jp-play:hover {
	//background: url("<?php echo $this->baseurl."/"; ?>plugins/mymuse/audio_html5/skin/jplayer.blue.monday.jpg") -41px 0 no-repeat;
	
}
*/
.tracks a.jp-pause {
	//background: url("<?php echo $this->baseurl."/"; ?>plugins/mymuse/audio_html5/skin/jplayer.blue.monday.jpg") 0 -42px no-repeat;
}
#alphabet .letter{
    padding: 0 5px 0 0;
    font-weight:bold;
}
#alphabet .selected{
   font-size:150%
}
#main .pagination ul{
	list-style-type: none;
    margin: 0;
    padding: 0;
    text-align: left;
}
.pagination li {
    border: 1px solid #EEEEEE;
    display: inline;
    margin: 0 2px;
    padding: 2px 5px;
    text-align: left;
}
#carttop {
    width: 200px;
    border: 1px solid;
    height: 50px;
    margin-bottom:10px;
}
</style>
<!--  the category title -->
<?php if ($this->params->get('show_category_title', 1)) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<h1><?php echo $this->category->title; ?></h1>
</div>
<?php endif; ?>

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

<!--  the alphabet  -->
<div id="alphabet">
<?php foreach($this->alpha as $letter){
        echo $letter;
    }
    ?>
</div>

<!--  the category image and description -->
<?php if (($this->params->get('show_description_image') && $this->category->params->get('image')) ||
		$this->params->get('show_description')) : ?>
<table class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" border="0">
<tr>
	<td class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" colspan="2">
	<?php if ($this->params->get('show_description_image') && $this->category->params->get('image')) : ?>
		<img class="catimage" src="<?php echo $this->baseurl ."/". $this->category->params->get('image');?>" 
		align="left" hspace="6"  alt="<?php echo $this->category->params->get('image');?>" 
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
<?php endif; ?>


<!--  the products  -->
<?php if ($this->params->get('category_show_all_products')) : ?>
<form method="post" action="index.php" id="adminForm">
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
<?php endif; ?>


<?php 

?>
<!--  the tracks  -->
<?php if(count($this->items)) : 
	$tracks = $this->items;
	?><div class="tracks">
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
		<div 
		<?php if($params->get('product_player_type') == "each"){ ?>
		id="product_player"
		<?php } ?>
		class="clips petrol" >

		<table class="mymuse_cart">
		    <tr class="sectiontableheader">

        		<th class="mymuse_cart_top" align="left" width="40%" >
        		<?php echo JHtml::_('grid.sort', 'MYMUSE_ARTIST', 'category_name', $listDirn, $listOrder); ?></th>
       
       			<th class="mymuse_cart_top" align="center" width="40%">
       			<?php echo JHtml::_('grid.sort', 'MYMUSE_NAME', 'a.title', $listDirn, $listOrder); ?>
       			</th>
       	
        		<th class="mymuse_cart_top" align="left" width="10%"><?php echo JText::_('MYMUSE_CART_PRICE'); ?></th>
                
                <th class="mymuse_cart_top" align="left" width="10%"><?php echo JText::_('MYMUSE_PLAY'); ?></th>
                
                <th class="mymuse_cart_top" align="left" width="10%" ><?php echo JText::_('MYMUSE_ADD'); ?></th>
    
      		</tr>
      		<?php foreach($tracks as $track){ 
                //print_pre($track);
                ?>
			  		<tr>
			  		
        				
                        <td>
                            <?php echo $track->category_name; ?><br />
                            (<?php echo $track->product_title; ?>)
                        </td>
      						
						<td valign="middle"><?php echo $track->title; ?> <br />
							(T) (<?php echo $track->product_made_date; ?>)
						
							<?php if($track->file_length){echo "(".MyMuseHelper::ByteSize($track->file_length).")";} ?>
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
                    
                        <td align="center" height="42px" valign="middle">
        				<?php if($track->file_name || $track->product_allfiles) :?>
        				<span class="checkbox">
                        <a href="javascript:void(0)" id="box_<?php echo $track->id; ?>"><img id="img_<?php echo $track->id; ?>" src="<?php
                            if(in_array($track->id, $products)){
                                echo "components/com_mymuse/assets/images/cart.png";
                            }else{
                                echo "components/com_mymuse/assets/images/checkbox.png";
                            }
                        ?>"></a>
      						</span>
      					<?php  endif; ?>
      					</td>
      				</tr>
      		<?php  } ?>
		</table>
<?php endif; ?>
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
