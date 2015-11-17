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

//Grab taxes
$MyMuseCart 	=& MyMuse::getObject('cart','helpers');
$cart 			=& $MyMuseCart->cart;
$order 			= $MyMuseCart->buildOrder( 1,0 );
$mobile_style 	= '';

if(isset($order->tax_array) && count($order->tax_array)){
	reset($order->tax_array);
	while(list($key,$val) = each($order->tax_array)){
		$mobile_style .= 'td.'.strtolower(preg_replace("/_/","", $key)).' {
				align: right;
				}
				';
	}
}

$mobile_style .= '
/* Only Phones */
@media (max-width: 767px) {
	/*
	 Label the data
	 
	 TODO: mytaxname mytax
	*/

	
	td.myselect:before { content: "'.JText::_('MYMUSE_SELECT').'";}
	td.mytitle:before { content: "'.JText::_('MYMUSE_TITLE').'";}
	td.mytime:before { content: "'.JText::_('MYMUSE_TIME').'";}
	td.myfilesize:before { content: "'.JText::_('MYMUSE_FILE_SIZE').'";}
	td.mydownloads:before { content: "'.JText::_('MYMUSE_DOWNLOADS').'";}
	td.myexpiry:before { content: "'.JText::_('MYMUSE_EXPIRES').'";}
	td.myprice:before { content: "'.JText::_('MYMUSE_CART_PRICE').'";}
	td.mypreviews:before { content: "'.JText::_('MYMUSE_PLAY').'";}
	td.myquantity:before { content: "'.JText::_('MYMUSE_QUANTITY').'";}
	td.mysku:before { content: "'.JText::_('MYMUSE_CART_SKU').'";}
	td.mysubtotal:before { content: "'.JText::_('MYMUSE_CART_SUBTOTAL').'";}
	td.myaction:before { content: "'.JText::_('MYMUSE_CART_ACTION').'";}

	td.myoriginalsubtotal:before { content: "'.JText::_('MYMUSE_CART_ORIGINAL_SUBTOTAL').'";}
	td.myshoppergroupdiscount:before { content: "'.JText::_('MYMUSE_DISCOUNT').'";}
	td.mynewsubtotal:before { content: "'.JText::_('MYMUSE_CART_NEW_SUBTOTAL').'";}
	td.myshipping:before { content: "'.JText::_('MYMUSE_SHIPPING').'";}
	td.mytotal:before { content: "'.JText::_('MYMUSE_TOTAL').'";}
	td.myupdatecart:before { content: "'.JText::_('MYMUSE_UPDATE_CART').'";}
	td.mycoupon:before { content: "'.JText::_('MYMUSE_YOUR_COUPON').'";}
	
	td.myreservationfee:before { content: "'.JText::_('MYMUSE_RESERVATION_FEE').'";}
	td.myothercharges:before { content: "'.JText::_('MYMUSE_OTHER_CHARGES').'";}
	td.mypaynow:before { content: "'.JText::_('MYMUSE_PAYNOW').'";}
	td.myshipmethod:before { content: "'.JText::_('MYMUSE_SHIP_METHOD').'";}
	td.mydiscount:before { content: "'.JText::_('MYMUSE_DISCOUNT').'";}
	
	td.myimage:before { content: "'.JText::_('MYMUSE_IMAGE').'";}
	td.myauthor:before { content: "'.JText::_('JAUTHOR').'";}
	td.myhits:before { content: "'.JText::_('JGLOBAL_HITS').'";}
	td.mysales:before { content: "'.JText::_('MYMUSE_SALES').'";}
	td.mydate-modified:before { content: "'.JText::_('MYMUSE_MODIFIED_DATE').'";}
	td.mydate-created:before { content: "'.JText::_('MYMUSE_CREATED_DATE').'";}
	td.mydate-published:before { content: "'.JText::_('MYMUSE_PUBLISHED_DATE').'";}
	td.mydate-product_made_date:before { content: "'.JText::_('MYMUSE_PRODUCT_CREATED_DATE').'";}
	td.myartist:before { content: "'.JText::_('MYMUSE_ARTIST').'";}
	
	td.myorderid:before { content: "'.JText::_('MYMUSE_ORDER_ID').'";}
	td.mydate:before { content: "'.JText::_('MYMUSE_DATE').'";}
	td.myorderstatus:before { content: "'.JText::_('MYMUSE_ORDER_STATUS').'";}
	td.myordernumber:before { content: "'.JText::_('MYMUSE_ORDER_NUMBER').'";}
	td.myorderdate:before  { content: "'.JText::_('MYMUSE_ORDER_DATE').'";}
	td.mypaid:before  { content: "'.JText::_('MYMUSE_PAID').'";}
	
	td.myfullname:before { content: "'.JText::_('MYMUSE_FULL_NAME').'";}
	td.myemail:before { content: "'.JText::_('MYMUSE_EMAIL').'";}
	td.myphone:before { content: "'.JText::_('MYMUSE_PHONE').'";}
	td.myaddress:before { content: "'.JText::_('MYMUSE_ADDRESS').'";}
	td.mycity:before { content: "'.JText::_('MYMUSE_CITY').'";}
	td.myzip:before { content: "'.JText::_('MYMUSE_ZIP').'";}
	td.myregion:before { content: "'.JText::_('MYMUSE_STATE').'";}
	td.mycountry:before { content: "'.JText::_('MYMUSE_COUNTRY').'";}
	td.mycompany:before { content: "'.JText::_('MYMUSE_COMPANY').'";}
	td.myfax:before { content: "'.JText::_('MYMUSE_FAX').'";}
			
	td.mychoose:before { content: "'.JText::_('MYMUSE_CHOOSE').'";}
	td.myshipmethod:before { content: "'.JText::_('MYMUSE_SHIP_METHOD').'";}
	td.mysummarytotal:before { content: "'.JText::_('MYMUSE_TOTAL').'";}
			
	
';


if(isset($order->tax_array) && count($order->tax_array)){
	reset($order->tax_array);
	while(list($key,$val) = each($order->tax_array)){
		$mobile_style .= 'td.'.strtolower(preg_replace("/_/","", $key)).':before { 
				content: "'.JText::_(preg_replace("/_/"," ", $key)).'";  
				white-space: nowrap;
				padding-right: 7%;
				margin-right: 7%;
				width: 23%;
				display: inline-block;
				border-right: 1px solid #ccc;
				}
				td.'.strtolower(preg_replace("/_/","", $key)).'
				{ 
					clear:both;
				}
				';
	}
}
	
$mobile_style .= '
}
';