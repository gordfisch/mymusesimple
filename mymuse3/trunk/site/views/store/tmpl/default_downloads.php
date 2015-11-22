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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$order = $this->order;
$params = $this->params;
$id = $this->id;

?>
<script type="text/javascript">
function mydownload(url,item_id){
	var current = '<?php echo $this->current; ?>'+item_id;
	setTimeout("location.href='"+current+"'",3000);
	window.open(url, "_download");
}
</script>
<h1 class="cart-header"><?php echo JText::_('MYMUSE_DOWNLOAD_PAGE') ?></h1>
		<table  class="mymuse_cart cart">
 
        <!-- Begin Order Summary -->
        <tr class="mymuse_cart cart cart" >
            <td colspan="2"  class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_ORDER_SUMMARY') ?></b></td>
        </tr>
        <tr>
            <td class="mobile-hide cart"><?php echo JText::_('MYMUSE_ORDER_NUMBER') ?>:</td>
            <td class="myordernumber cart"><?php echo sprintf("%08d", $order->id) ?></td>
        </tr>
        <tr>
            <td class="mobile-hide cart"><?php echo JText::_('MYMUSE_ORDER_DATE') ?>:</td>
            <td class="myorderdate cart"><?php echo $order->created ?></td>
        </tr>
        <tr>
            <td class="mobile-hide cart"><?php echo JText::_('MYMUSE_ORDER_STATUS') ?>:</td>
            <td class="myorderstatus cart"><?php echo JText::_('MYMUSE_'.strtoupper($order->status_name)) ?></td>
        </tr>
        </table>
        <br />
        <br />

		<table class="mymuse_cart cart">
            <thead>
			<tr>
				<th class="mytitle cart"><?php echo JText::_('MYMUSE_FILENAME'); ?></th>
				<th class="mydownloads cart"><?php echo JText::_('MYMUSE_NUMBER_DOWNLOADS'); ?></th>
				<th class="myfilesize cart"><?php echo JText::_('MYMUSE_FILE_SIZE'); ?></th>
				<th class="myexpiry cart"><?php echo JText::_('MYMUSE_EXPIRES'); ?></th>
			</tr>
            </thead>
            <tbody>
			<?php 
			foreach($order->items as $item){ 
				if($params->get('my_use_zip')){
					$test = 1;
				}else{
					$test = $item->product->product_downloadable;
				}
				if($test){
				?>
			<tr>
				<td class="mytitle cart"><?php 

				$end_date = $item->end_date? $item->end_date : time()*2;
				$my_download_max = $params->get('my_download_max')? $params->get('my_download_max') : ($item->downloads+1)*2;

				if($item->downloads < $my_download_max && $end_date > time()){
						$url = JRoute::_('index.php?option=com_mymuse&view=store&task=downloadfile&id='.$id.'&item_id='.$item->id);
					
				?><a href="javascript:void(0);" onclick="mydownload('<?php echo $url; ?>','<?php echo $item->id; ?>');">
				<?php } ?>
				
				<?php echo $item->product_name; ?>
				<?php if($item->file_name){
					echo ": <br />".$item->file_name;
				}
					?>
				<?php 
				if($item->downloads < $my_download_max && $end_date > time()){ ?></a><?php } ?></td>
				
				<td class="mydownload cart"><?php echo $item->downloads; ?></td>
				<td class="myfilesize cart"><?php echo MyMuseHelper::ByteSize($item->file_size); ?></td>
				<td class="myexpiry cart"><?php if($item->end_date < time()){ ?><span style="color : #c30;">*</span> <?php } ?>
				<?php 
				if($item->end_date){
					$date = JFactory::getDate($item->end_date);
					$mydate = $date->format($params->get('my_date_format'));
					echo $mydate;
				}
					?></td>
			</tr>
			<?php } 
				} ?>
            </tbody>
		</table>
