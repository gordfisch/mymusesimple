<?php
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

	$lists =& $this->lists;
	$order =& $this->item;
	$order->colspan = 3;
	$order->colspan2 = 1;
	$shopper =& $this->item->user;
	$user =& $this->user;
	$form =& $this->form;
	$extra =& $this->extra;
	$params = $this->params;
	$downloads = 0;
	$download_header = '<table class="contentpaneopen">
	<tr>
		<td><div class="componentheading">'.JText::_('MYMUSE_DOWNLOADS_IN_THIS_ORDER').'</div></td>
	</tr>
</table>
<table class="contentpaneopen">
<tr>
		<td>
		<ul>
';
@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
$shopper->address1 		= isset($shopper->profile['address1'])? $shopper->profile['address1'] : '';
$shopper->address2 		= isset($shopper->profile['address2'])? $shopper->profile['address2'] : '';
$shopper->city 			= isset($shopper->profile['city'])? $shopper->profile['city'] : '';
$shopper->region 		= isset($shopper->profile['region'])? $shopper->profile['region'] : '';
$shopper->region 		= isset($shopper->profile['region_name'])? $shopper->profile['region_name'] : $shopper->region;
$shopper->postal_code 	= isset($shopper->profile['postal_code'])? $shopper->profile['postal_code'] : '';
$shopper->country		= isset($shopper->profile['country'])? $shopper->profile['country'] : '';
$shopper->phone		= isset($shopper->profile['phone'])? $shopper->profile['phone'] : '';
$shopper->mobile		= isset($shopper->profile['mobile'])? $shopper->profile['mobile'] : '';



foreach($order->items as $item){ 
	if($item->file_name != ""){
	$download_header .= '
	<li>'.$item->product_name.'</li>
	';
	$downloads = 1;
 	}
} 

// for no_reg
if($params->get('my_registration') == "no_reg"){
	$link = JURI::root()."index.php?option=com_mymuse&task=accdownloads&id=".$order->order_number;
}else{
	$link = JURI::root()."index.php?option=com_mymuse&task=downloads&id=".$order->order_number;
}

$download_header .= '
		</ul>
		</td>
	</tr>
</table>
<table class="contentpaneopen">
	<tr>
		<td><div class="componentheading">'.JText::_('MYMUSE_DOWNLOAD_LINK_PLEASE_CLICK').'</div></td>
	</tr>
	<tr>
		<td><a href="'.$link.'">'.$link.'</a></td>
	</tr>
</table>

';

if($downloads && $order->order_status == "C"){
	echo $download_header;
}

?>
     <table class="mymuse_cart" width="90%">
 
        <!-- Begin Order Summary -->
        <tr>
            <td class="sectiontableheader mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_ORDER_SUMMARY') ?></b></td>
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
            <td><?php echo JText::_(MyMuseHelper::getStatusName($order->order_status)) ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('MYMUSE_ORDER_TOTAL') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->order_total)." ".$order->order_currency ?></td>
        </tr>
    <?php if($order->reservation_fee > 0){ ?>
        <tr>
            <td><?php echo JText::_('MYMUSE_RESERVATION_FEE') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->reservation_fee)." ".$order->order_currency ?></td>
        </tr>
        	<?php if($order->non_res_total > 0){ ?>
        	<tr>
            	<td><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</td>
            	<td><?php echo MyMuseHelper::printMoney($order->non_res_total)." ".$order->order_currency ?></td>
        	</tr>
        	<tr>
            <td><?php echo JText::_('MYMUSE_PAID') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->pay_now)." ".$order->order_currency ?></td>
        </tr>
    	<?php } ?>
    <?php } ?>
	</table>
	<br />
   <!-- Begin 2 column bill-ship to -->
        <div class="componentheading"><?php echo JText::_('MYMUSE_SHOPPER_INFORMATION') ?></div>

		<table class="mymuse_cart" width="90%">
        <tr VALIGN=top>
            <td width=50%> <!-- Begin BillTo -->

            <table class="mymuse_cart"  width=100% >
                <tr class="sectiontableheader mymuse_cart_top">
                <td class="sectiontableheader mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                <td>
                <?php echo $shopper->first_name ?>
        
                <?php echo $shopper->last_name ?>
                </td>
                </tr>
                <?php if($shopper->address1){?>
                <tr VALIGN=TOP>
                <td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                <td>
                <?php echo $shopper->address1 ?>
                <BR>
                <?php echo $shopper->address2 ?>
                </td>
                </tr>
                <?php }?>
                <?php if($shopper->city){?>
                <tr>
                <td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                <td><?php echo $shopper->city ?></td>
                </tr>
                <?php }?>
               
                <?php if(isset($shopper->region)){ 
							if(!isset($shopper->region_name)){
								if(!is_numeric($shopper->region)){
									$shopper->region_name = $shopper->region;
								}else{
									$db = JFactory::getDBO();
									$query = "SELECT * FROM #__mymuse_state WHERE $field='".$shopper->region."'";
									$db->setQuery($query);
									if($row = $db->loadObject()){
										$shopper->region_name = $row->state_name;
									}
								}
							}
						
						?>
					<tr>
						<td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
						<td><?php echo $shopper->region_name ?></td>
					</tr>
				<?php }?>
                
                
                <?php if($shopper->postal_code){?>
                <tr>
                <td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                <td><?php echo $shopper->postal_code ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->country){?>
                <tr>
                <td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                <td><?php echo $shopper->country ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->phone ){?>
                <tr>
                <td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                <td><?php echo $shopper->phone ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->mobile){?>
                <tr>
                <td><?php echo JText::_('MYMUSE_MOBILE') ?>:</td>
                <td><?php echo $shopper->mobile ?></td>
                </tr>
                <?php }?>

                <tr>
                <td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                <td><?php echo $shopper->email ?></td>
                </tr>
            </table>
            <!-- End BillTo --> </td>
        
            <td width=50%>
        <?php 
        if($params->get('my_use_shipping') && isset($shopper->shipto)){
        ?>
            <table width=100% cellspacing=0 cellpadding=2 border=0>
                <tr class="sectiontableheader mymuse_cart_top">
                <th class="sectiontableheader mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_SHIPPING_ADDRESS') ?></b></th>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_COMPANY') ?>:</td>
                <td><?php echo $shopper->shipto->company ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                <td>
                <?php echo $shopper->shipto->first_name ?>
        
                <?php echo $shopper->shipto->middle_name ?>
        
                <?php echo $shopper->shipto->last_name ?>
                </td>
                </tr>
                <tr VALIGN=TOP>
                <td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                <td>
                <?php echo $shopper->shipto->address_1 ?>
                <BR>
                <?php echo $shopper->shipto->address_2 ?>
                </td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                <td><?php echo $shopper->shipto->city ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                <td><?php echo $shopper->shipto->state ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                <td><?php echo $shopper->shipto->zip ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                <td><?php echo $shopper->shipto->country ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                <td><?php echo $shopper->shipto->phone_1 ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_FAX') ?>:</td>
                <td><?php echo $shopper->shipto->fax ?></td>
                </tr>
                <tr>
                <td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                <td><?php echo $shopper->shipto->email ?></td>
                </tr>
            </table>
            <!-- End ShipTo -->

          <?php 
        }
        ?></td>
            <!-- End Customer Information --> 
        </tr>
        </table>
        <br />


		<div >
		<!-- start of basket -->
		<div class="componentheading"><?php echo JText::_('MYMUSE_SHOPPING_CART'); ?></div>
		
		<table class="mymuse_cart" width="95%" border="0">
		<tr class="sectiontableheader mymuse_cart_top">
		<td class="sectiontableheader mymuse_cart_top"><b><?php echo JText::_('MYMUSE_TITLE'); ?></b></td>
		<!--  td class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_SKU'); ?></b></td -->
		<td class="sectiontableheader mymuse_cart_top" align="right" width="80"><b><?php echo JText::_('MYMUSE_CART_PRICE'); ?></b></td>
		<td class="sectiontableheader mymuse_cart_top"><b><?php echo JText::_('MYMUSE_CART_QUANTITY'); ?></b></td>
		<td class="sectiontableheader mymuse_cart_top" width="80" align="right"><b><?php echo JText::_('MYMUSE_CART_SUBTOTAL'); ?></b></td>
		</tr>
		
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order->items);$i++) { 
		      if ($i % 2){
		          $class = "row1";
		      }else{
		          $class = "row2";
		      }
		      $order_item[$i] = $order->items[$i];
		?>
		
		    <tr class="<?php echo $class ?>">
		        <td align="left">
		        <?php 
		        if($order_item[$i]->category_name != ''){
		        	echo $order_item[$i]->category_name." : ";
		        }
		        if($order_item[$i]->parent_name != ''){
		        	echo $order_item[$i]->parent_name." : ";
		        }
		        ?>
		        
		        <?php echo $order_item[$i]->product_name; ?></td>
		        <td align="right"> <?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_price); ?></td>
		        <td align="center"><?php echo $order_item[$i]->product_quantity; ?></td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order_item[$i]->subtotal); ?></td>
		       </tr>
		<?php } ?>
		
		
		
		
		
		<?php if($params->get("my_use_coupons") && @$coupon_id){ ?>
		    <tr>
		    <td colspan="<?php echo $order->colspan; ?>" align="right"><?php echo $order->coupon_name ?>:
		        </td>
		        <td colspan="<?php echo $order->colspan2; ?>"><?php echo $order->coupon_discount; ?> %
		        </td>

		    </tr>
		<?php } ?>
				
		<?php if ($params->get("my_use_shipping") && $order->order_shipping > 0) { ?>
		    <tr>
		    <td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_SHIPPING') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($order->order_shipping); ?>
		    </td>
		    </tr>
		<?php } ?>
		
		<?php // TAXES
		if(@$order->tax_array){
		    while(list($key,$val) = each($order->tax_array)){ ?>
		        <tr>
		        <td colspan="<?php echo $order->colspan; ?>" align="right"><?php echo $key; ?></td>
		        <td colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        </tr>
		<?php  } 
		} ?>
		
		
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>" class="textbox2" align="right"><b><?php echo JText::_('MYMUSE_CART_TOTAL') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" class="textbox2" align="right"><b><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    <?php echo $order->order_currency; ?></b></td>

		</tr>
		
		
		<?php  if($order->reservation_fee > 0){ ?>
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_RESERVATION_FEE') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
		    </td>
		</tr>
			<?php  if($order->non_res_total > 0){ ?>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_OTHER_CHARGES') ?>:</b></td>
		    	<td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
		    	</td>
			</tr>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>" align="right"><b><?php echo JText::_('MYMUSE_PAYNOW') ?>:</b></td>
		    	<td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->must_pay_now); ?></b>
		    	</td>

			</tr>
			<?php } ?>
		
		<?php } ?>
		
		</table>
		</div>

		<br />


        <?php if($extra){ ?>
        <div class="componentheading"><?php echo JText::_('MYMUSE EXTRA INFO'); ?></div>
        <table class="mymuse_cart" width="95%" border="0">
        <tr>
			<td width="100%" valign="top"><?php print_pre($extra); ?> </td>
        </tr>
        </table>
        <?php }?>
        


