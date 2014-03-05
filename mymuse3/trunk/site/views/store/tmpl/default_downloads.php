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
	window.location.href=url;
}
</script>
<h2><?php echo JText::_('MYMUSE_DOWNLOAD_PAGE') ?></h2>
		<table  class="mymusetable">
 
        <!-- Begin Order Summary -->
        <tr>
            <td colspan="2"  class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_ORDER_SUMMARY') ?></b></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_NUMBER') ?>:</td>
            <td><?php echo sprintf("%08d", $order->id) ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_DATE') ?>:</td>
            <td><?php echo $order->created ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_STATUS') ?>:</td>
            <td><?php echo JText::_('MYMUSE_'.strtoupper($order->status_name)) ?></td>
        </tr>
        </table>
        <br />
        <br />

		<table class="mymusetable">
			<tr>
				<td class="mymuse_cart_top"><?php echo JText::_('MYMUSE_FILENAME'); ?></td>
				<td class="mymuse_cart_top"><?php echo JText::_('MYMUSE_NUMBER_DOWNLOADS'); ?></td>
				<td class="mymuse_cart_top"><?php echo JText::_('MYMUSE_FILE_SIZE'); ?></td>
				<td class="mymuse_cart_top"><?php echo JText::_('MYMUSE_EXPIRES'); ?></td>
			</tr>
			<?php 
			foreach($order->items as $item){ 
				if($item->product->product_downloadable){
				?>
			<tr>
				<td align="center"><?php 

				$end_date = $item->end_date? $item->end_date : time()*2;
				$my_download_max = $params->get('my_download_max')? $params->get('my_download_max') : ($item->downloads+1)*2;

				if($item->downloads < $my_download_max && $end_date > time()){
				?><a href="javascript:void(0);" onclick="mydownload('index.php?option=com_mymuse&task=downloadfile&id=<?php echo $id; ?>&item_id=<?php echo $item->id; ?>','<?php echo $item->id; ?>');">
				<?php } ?>
				
				<?php echo $item->product_name; ?>
				<?php 
				if($item->downloads < $my_download_max && $end_date > time()){ ?></a><?php } ?></td>
				
				<td align="center"><?php echo $item->downloads; ?></td>
				<td align="center"><?php echo MyMuseHelper::ByteSize($item->file_length); ?></td>
				<td align="center"><?php if($item->end_date < time()){ ?><span style="color : #c30;">*</span> <?php } ?>
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
		</table>