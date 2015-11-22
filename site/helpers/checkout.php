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

require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS.'tables'.DS.'order.php' );

class MyMuseCheckout
{
	

	/**
	 * error string
	 *
	 * @var string
	 */
	var $error = '';
	
	/**
	 * db object
	 *
	 * @var object
	 */
	var $_db = null;


	function __construct()
	{
		$this->_db 	= JFactory::getDBO();
	}

	/**
	 * add
	 * Assemble all order information and add to database
	 *
	 * @param object $MyMuse
	 * @param object $MyMuseShopper
	 * @param array $cart
	 * @return bool
	 */

	function save( ) {

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$params = MyMuseHelper::getParams();

		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$MyMuseStore  	=& MyMuse::getObject('store','models');
		$MyMuseCart  	=& MyMuse::getObject('cart','helpers');

		$shopper 		=& $MyMuseShopper->getShopper();
		$store 			= $MyMuseStore->getStore();
		$cart 			= $MyMuseCart->cart;
		$cart_order 	= $MyMuseCart->buildOrder(0,1);
		$d 				= $jinput->post->getArray();

		// TODO stop repeat orders on reload
		if($params->get('my_debug')){
			$date = date('Y-m-d h:i:s');
			$debug = "################### \nCHECKOUT SAVE FUNCTION\n";
			$debug .= "$date  Have a cart ". print_r($cart,true) ;
			MyMuseHelper::logMessage( $debug );
		}
		$coupon_id 			= 0;
		$coupon_discount 	= 0;
		$ship_method_id 	= 0;
		$order_shipping     = 0;
		$order_total_tax    = 0;
		$order_status 		= "P";
		if($params->get('my_shop_test')){
			$order_status = "C";
		}
		$downloadable 		= 0;


		// LOOP OVER CART ITEMS TO PROCESS
		$idx = $cart["idx"];
		for($i = 0; $i < $idx; $i++) {
			if(@$cart[$i]["coupon_id"]){
				$coupon_id = $cart[$i]["coupon_id"];
				$d["coupon_id"] = $cart[$i]["coupon_id"];
				continue;
			}
			if(!$cart[$i]["product_id"]){
				continue;
			}
			$cart[$i]['product'] = $MyMuseCart->getProduct($cart[$i]["product_id"]);
			$ext = '';
			$jason = json_decode($cart[$i]['product']->file_name);
			if(is_array($jason)){
				$cart[$i]['product']->file_name = $jason[$cart[$i]["variation"]]->file_name;
				$cart[$i]['product']->ext = $jason[$cart[$i]["variation"]]->file_ext;
				//print_pre($jason);
			}else{
				
				$cart[$i]['product']->ext = pathinfo($cart[$i]['product']->file_name, PATHINFO_EXTENSION);
			}
			$cart[$i]['product']->price = MyMuseModelProduct::getPrice($cart[$i]["product"]);
			// SEE IF IT IS AN ALL_FILES, ADD ALL FILES TO CART

			if($cart[$i]['product']->product_allfiles && !$params->get('my_use_zip')){
				$query = "SELECT id from #__mymuse_product WHERE parentid='".$cart[$i]['product']->parentid."'
				AND product_downloadable='1' AND product_allfiles !='1' ORDER BY ordering ";
				$this->_db->setQuery($query);
				$rows = $this->_db->loadObjectList();
				foreach($rows as $row){
					if($cart[$i]["product_id"] == $row->id){
						continue;
					}
					$cart[$cart["idx"]]['product_id'] = $row->id;
					$cart[$cart["idx"]]['quantity'] = 1;
					$cart[$cart["idx"]]['catid'] = '';
					$cart[$cart["idx"]]['product']= $MyMuseCart->getProduct($row->id);
					$jason = json_decode($cart[$cart["idx"]]['product']->file_name);
					if(is_array($jason)){
						$cart[$cart["idx"]]['product']->file_name = $jason[$cart[$i]["variation"]]->file_name;
						$cart[$cart["idx"]]['product']->ext = $jason[$cart[$i]["variation"]]->file_ext;
						//print_pre($jason);
					}else{
						$cart[$cart["idx"]]['product']->ext = pathinfo($cart[$cart["idx"]]['product']->file_name, PATHINFO_EXTENSION);
					}
					$cart[$cart["idx"]]['product']->price = array();
					$cart[$cart["idx"]]['product']->price['product_price'] = 0;
					$cart["idx"]++;
						
				}
			}elseif($cart[$i]['product']->product_allfiles && $params->get('my_use_zip')){
				
			
			}
		}

		// Loop over cart if 'my_check_stock' is on
		if ($params->get('my_check_stock')) {
			for($i = 0; $i < $cart["idx"]; $i++) {
				if(isset($cart[$i]["coupon_id"]) && $cart[$i]["coupon_id"]){
					continue;
				}
				if(!isset($cart[$i]["product_physical"]) || !$cart[$i]["product_physical"]){
					continue;
				}
				$q = "SELECT product_in_stock ";
				$q .= "FROM #__mymuse_product where id=";
				$q .= $cart[$i]['product_id'];
				$this->_db->setQuery($q);
				$product_in_stock = $this->_db->loadResult();
				if ($cart[$i]['quantity'] > $product_in_stock) {
					$this->error = JText::_('MYMUSE_THIS_ORDER_EXEEDS_OUR_STOCK_FOR')." ". $cart[$i]['product']->title." : ";
					$this->error .= JText::_('MYMUSE_CURRENT_IN_STOCK')." ".$product_in_stock;
					return False;
				}
			}
		}

		/**
		 // update stock moved to notify function
		 */
		// TODO: SHOULD EMAIL STORE IF STOCK LESS THAN ZERO
		// TODO: add option for shipping different from billing
		
		if(!@$d["ship_info_id"]){
			$d["ship_info_id"] = $shopper->id;
		}
		if(!$this->_db){
			MyMuseHelper::logMessage( "Order::save: NO DB");
		}
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'order.php' );
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'orderitem.php' );
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'ordershipping.php' );
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'orderpayment.php' );
		$order 		= new MymuseTableorder( $this->_db );
		$config 	= JFactory::getConfig();
		$tzoffset 	= $config->get('config.offset');
		$date 		= JFactory::getDate('now', $tzoffset);
		
		// We used to keep separate shopper,user id's
		$order->user_id 				= $shopper->id;
		$order->order_number 			= md5(time().mt_rand());
		$order->store_id 				= $store->id;
		$order->shopper_id 				= $shopper->id;
		$order->order_subtotal 			= $cart_order->order_subtotal;
		$order->ship_info_id 			= $d["ship_info_id"];
		$order->order_shipping 			= @$cart_order->order_shipping->cost;
		$order->order_currency 			= $MyMuseStore->_store->currency;
		$order->order_status 			= $order_status;
		$order->coupon_id 				= 0;
		$order->coupon_name 			= '';
		$order->coupon_discount 		= 0;
		$order->created 				= $date->toSql(true);
		$order->modified 				= $date->toSql(true);
		$order->discount 				= $cart_order->discount;
		$order->shopper_group_discount 	= $cart_order->shopper_group_discount;
		$order->notes 					= @$cart['notes'];

		//check coupons
		if($params->get('my_use_coupons') && $coupon_id){
			MyMuseHelper::logMessage( "Order::save: coupon");
			$order->coupon_id			= $cart_order->coupon->id;
			$order->coupon_name 		= $cart_order->coupon->title;
			$order->coupon_discount		= $cart_order->coupon->discount;
			// update hit on this coupon
			$query = "UPDATE #__mymuse_coupon SET
			coupon_uses = coupon_uses +1
			WHERE id='".$order->coupon_id."' ";

			$this->_db->setQuery($query);
			$this->_db->execute();
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( "$date Update coupon" . $query);
			}
		}
		

		if($params->get('my_registration') == "no_reg"){
			$fields = MyMuseHelper::getNoRegFields();
			$order->notes = '';
			foreach($fields as $field){
				if(isset($shopper->$field)){
					$order->notes .= $field."=".$shopper->$field."\n";
				}
			}
		}
		$order->reservation_fee 	= $cart_order->reservation_fee;
		$order->non_res_total		= $cart_order->non_res_total;
		$order->pay_now				= $cart_order->must_pay_now;
		$order->extra 				= @$cart['extra'];


		//save the order number in the session
		$session = JFactory::getSession();
		$session->set("order_number",$order->order_number);


		while(list($name,$amount) = each($cart_order->tax_array)){
			$order->$name = $amount;
		}

		$total_tax = $cart_order->tax_total;


		// Store the order to the database
		if (!$order->store()) {
			JError::raiseError( 500, $this->_db->stderr() );
			return false;
		}



		// LOOP OVER CART ITEMS TO STORE TO DB
		$order->idx = 0;
		for($i = 0; $i < $cart["idx"]; $i++) {
			if(@$cart[$i]["coupon_id"]){
				continue;
			}
			if(!@$cart[$i]["product_id"]){
				continue;
			}

			$query = "SELECT * FROM #__mymuse_product WHERE id='".$cart[$i]["product_id"]."'";
			$this->_db->setQuery($query);
			$prod = $this->_db->loadObject();
			$parentid = $prod->parentid;
			
			$jason = json_decode($cart[$i]['product']->file_name);
			if(is_array($jason)){
				$cart[$i]['product']->file_name = $jason[$cart[$i]["variation"]]->file_name;
			}
		
			$order->items[$i] = new MymuseTableorderitem( $this->_db );
			$order->idx++;
			$order->items[$i]->order_id = $order->id;
			$order->items[$i]->product_id = $cart[$i]["product_id"];
			$order->items[$i]->product_quantity = $cart[$i]["quantity"];
			$order->items[$i]->product_item_price = $cart[$i]['product']->price['product_price'];
				
			$order->items[$i]->product_sku = $cart[$i]['product']->product_sku;
			$order->items[$i]->product_name = $cart[$i]['product']->title;
			$order->items[$i]->created = $date->toSql();
			$order->items[$i]->modified = $date->toSql();

			if( $params->get('my_downloads_enable') == "1" ) {
				$order->items[$i]->file_name = stripslashes($cart[$i]['product']->file_name);
				if($params->get('my_download_expire') == "-"){
					$enddate = "0";
				}else{
					$enddate = time() + $params->get('my_download_expire');
				}
				$order->items[$i]->end_date = $enddate;
				$order->items[$i]->downloads =0;
			//echo "file name ".$cart[$i]['product']->file_name;
				if($cart[$i]['product']->file_name != ''){
					$downloadable++;
				}
			}
		
			// Store the item to the database
			if (!$order->items[$i]->store()) {
				JError::raiseError( 500, $this->_db->stderr() );
				return false;
			}

			// more fields for printing
			$order->items[$i]->product_sku = $prod->product_sku;
			$order->items[$i]->title = $cart[$i]['product']->title;
			$order->items[$i]->file_length = MyMuseHelper::ByteSize($cart[$i]['product']->file_length);
			$order->items[$i]->product_item_subtotal = sprintf("%.2f", $order->items[$i]->product_item_price * $order->items[$i]->product_quantity);
			 
			// Build URLs
			if(isset($cart[$i]['catid']) && $cart[$i]['catid'] != ''){
				$query = "SELECT * FROM #__categories WHERE id='".$cart[$i]['catid']."'";
				$this->_db->setQuery($query);
				if($cat = $this->_db->loadObject()){
					$order->items[$i]->category_name = $cat->title;
				}
				if ($parentid){
					$pid = $parentid;
				}else{
					$pid = $order->items[$i]->product_id;
				}
					
				$order->items[$i]->url = myMuseHelperRoute::getProductRoute($pid, $cart[$i]['catid']);
				$order->items[$i]->cat_url = myMuseHelperRoute::getCategoryRoute($cart[$i]['catid']);
			}
		} // end of item insertion

		
		$order->order_total = $order->order_subtotal + $order->order_shipping + $total_tax;
		if($cart_order->order_total == 0.00){
			$order->order_total = 0.00;
		}
		 
		// if the total is zero, change the order status to confirmed
		if($order->order_total == 0.00 || $order->order_total < 0.00){
			$order->order_status = "C";
			$query = "UPDATE #__mymuse_order set order_status='C' WHERE id='".$order->id."'";
			$this->_db->setQuery($query);
			if (!$this->_db->execute()) {
				JError::raiseError( 500, $this->_db->stderr() );
				return false;
			}
		}

		//Shipping
		if ($params->get('my_use_shipping') && $cart_order->need_shipping
				&& isset($cart_order->order_shipping)) {
			$order_shipping = new MymuseTableordershipping( $this->_db );
			$order_shipping->order_id = $order->id;
			$order_shipping->ship_type = $cart_order->order_shipping->ship_type;
			$order_shipping->ship_carrier_code = $cart_order->order_shipping->ship_carrier_code;
			$order_shipping->ship_carrier_name = $cart_order->order_shipping->ship_carrier_name;
			$order_shipping->ship_method_code = $cart_order->order_shipping->ship_method_code;
			$order_shipping->ship_method_name = $cart_order->order_shipping->ship_method_name;
			$order_shipping->cost = $cart_order->order_shipping->cost;
			$order_shipping->tracking_id = $cart_order->order_shipping->tracking_id;
			$order_shipping->created = $date->toSql();
			if (!$order_shipping->store()) {
				JError::raiseError( 500, $this->_db->stderr() );
				return false;
			}
		}

		//add more to the order object for printing
		$order->total_res 		= $cart_order->reservation_fee;
		$order->idx 			= $cart["idx"];
		$order->do_html 		= 0;
		$order->show_checkout 	= 0;
		$order->tax_array 		= $cart_order->tax_array;
		$order->status_name 	= MyMuseHelper::getStatusName($order->order_status );
		$order->tax_total 		= $total_tax;
		$order->downloadable  	= $downloadable;
		$order->ship_method_id 	= @$cart_order->order_shipping->id;
		$order->order_shipping  = $order_shipping;
		 
		// All done with inserting ORDER!!
		// attach the current order to the shopper
		$MyMuseShopper->order = $order;
		
		if($params->get('my_debug')){
			$debug = "$date Order saved:  ".$order->order_number."\n\n";
			MyMuseHelper::logMessage( $debug  );
		}
		// SEND EMAIL CONFIRMATION MESSAGES IF STATUS IS CONFIRMED
		// or if payment offline is enabled
		jimport( 'joomla.plugin.helper' );
		if($order->order_status == "C"){
			$this->mailOrder($MyMuseShopper,$MyMuseStore);
		}
		 
		if(!$params->get('my_shop_test') && !$params->get('my_debug')){
			$MyMuseCart->reset();
		}

		return $order;
	}


	function mailOrder(&$MyMuseShopper,&$MyMuseStore){

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$params = MyMuseHelper::getParams();

		$shopper 	=& $MyMuseShopper->getShopper();
		$user_email = $shopper->email;
		$order 		= $MyMuseShopper->order;

		$store 		= $MyMuseStore->_store;
		$currency 	= MyMuseHelper::getCurrency($store->currency);
		$order->colspan		= 3;
		$order->colspan2 	= 1;
		$link_message = '';
		
		//see if there is a message
		$dispatcher		= JDispatcher::getInstance();
		$pp = $jinput->get('pp', '');
		$my_email_msg = $params->get('my_email_msg');
		if($pp){
			JPluginHelper::importPlugin('mymuse',$pp);
			$results = $dispatcher->trigger('onAfterMyMusePayment',
					array() );
			
			foreach($results as $res){
				if(preg_match("/$pp/", $res)){
					$arr = explode(":",$res);
					array_shift($arr);
					$my_email_msg .= implode(":",$arr);
				}
			}
			 
		}

		ob_start();
		include_once( JPATH_COMPONENT.DS.'templates'.DS.'thank_you.php' );
		$contents = ob_get_contents();
		ob_end_clean();
		
		/*
		 * here is how it is done in the cart view
		ob_start();
		parent::display('checkout_header');
		parent::display('order_summary');
		parent::display('shopper_info');
		parent::display('cart');
		$contents .= ob_get_contents();
		ob_end_clean();
		*/

		$download_header = '';

		include_once( JPATH_COMPONENT.DS.'templates'.DS.'mail_html_header.php' );
		 
		//IS SOMETHING DOWNLOADABLE AND IS IT PAID FOR?
		if($order->downloadable && $order->order_status == "C"){
			if($params->get('my_registration') == "no_reg"){
				$link = JURI::base().JRoute::_("index.php?option=com_mymuse&task=accdownloads&id=".$order->order_number);
			}else{
				$link = JURI::base().JRoute::_("index.php?option=com_mymuse&task=downloads&id=".$order->order_number);
			}
			 
			$link_message .= JText::_("MYMUSE_YOUR_DOWNLOAD_KEY")." = ".$order->order_number." <br />";
			$link_message .= JText::_('MYMUSE_DOWNLOAD_LINK')." ";
			$link_message .= $link;
			$contents .= $link_message;
			
			$jinput = JFactory::getApplication()->input;
			$Itemid = $jinput->get("Itemid",$params->get('mymuse_default_itemid'));
		
			include_once( JPATH_COMPONENT.DS.'templates'.DS.'mail_downloads.php' );
			$order->downloadlink = $link_message;
		}
		if($order->downloadable  && $order->order_status == "P"){
			$link = JRoute::_(JURI::base()."index.php?option=com_mymuse&task=vieworder&orderid=".$order->id);
			$link_message .= JText::_('MYMUSE_PURCHASE_ORDER')." <br />\n";
			$link_message .= '<a href="'.$link.'">'.$link.'</a><br /><br />'."\n";
			 
			$download_header .= $link_message;
			$order->downloadlink = $link_message;
		}

		$message = $header.$download_header.$contents.$footer;
		$message = html_entity_decode($message, ENT_QUOTES,'UTF-8');

		// Send email to user

		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		// from
		$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
		$mailfrom = $params->get('contact_email');
		$sender = array(
				$mailfrom,
				$fromname );
		$mailer->setSender($sender);
		//recipient
		$recipient = $shopper->email;
		if($params->get('my_cc_webmaster')){
			$recipient = array($shopper->email, $params->get('my_webmaster'));
		}
		$mailer->addRecipient($recipient);
		//subject, body
		$subject = Jtext::_('MYMUSE_NEW_ORDER_FOR')." ".$store->title;
		$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
		$mailer->setSubject($subject);
		$mailer->setBody($message);

		$send = $mailer->Send();
		if ( $send instanceof Exception ) {
			 
			//$msg =  'Error sending email: ' . $send->getError();
			//JFactory::getApplication()->enqueueMessage($msg, 'error');
		}

		return true;
	}

	/**
	 * calc_order_subtotal
	 *
	 * @param array $cart Cart includes product objects
	 * @return float
	 */
	function calc_order_subtotal(&$cart) {

		$subtotal = 0.00;
		for($i = 0; $i < $cart["idx"]; $i++) {
			if(@$cart[$i]["coupon_id"]){
				continue;
			}
			if(!@$cart[$i]["product_id"]){
				continue;
			}

			$subtotal += $cart[$i]['product']->price['product_price'] * $cart[$i]['quantity'];
		}
		//$subtotal = sprintf("%.2f", $subtotal);
		return($subtotal);
	}

	/**
	 * calc_order_tax
	 * Did I mention that I hate taxes?
	 *
	 * @param object $order
	 * @return array
	 */
	function calc_order_tax($order) {
		 
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		=& $MyMuseShopper->getShopper();
		$params 		= MyMuseHelper::getParams();
		$order_subtotal = $order->order_subtotal;

		$taxes = array();
		
		// No profile?
		if(!isset($shopper->profile['country']) && !$params->get('my_add_taxes')){
			return $taxes;
		}
		
		if ($params->get('my_tax_shipping') && isset($order->order_shipping->cost)) {
			$order_taxable = $order->order_subtotal + $order->order_shipping->cost;
		}else{
			$order_taxable = $order->order_subtotal;
		}
	
		//get tax rates
		$q = "SELECT t.*, c.country_name, s.state_name FROM #__mymuse_tax_rate as t
		LEFT JOIN #__mymuse_country as c ON t.country = c.country_3_code
		LEFT JOIN #__mymuse_state as s ON s.id = t.province
				WHERE t.state = 1
		ORDER BY ordering";
		$this->_db->setQuery($q);
		$regex = TAX_REGEX;
		
		if($tax_rates = $this->_db->loadObjectList()){
			//we have rates
		}else{
			//we have no rates
			return $taxes;
		}
		
		// GET USER STATE,COUNTRY, BLOC
		$user_state 	= isset($shopper->profile['region'])? $shopper->profile['region'] : 'unknown';
		$user_country 	= isset($shopper->profile['country'])? $shopper->profile['country'] : "unknown";
		$user_bloc 		= '';
		
		if($user_country != 'unknown'){
			$query = "SELECT bloc FROM #__mymuse_country WHERE country_3_code='$user_country'";
			$this->_db->setQuery($query);
			$user_bloc = $this->_db->loadResult();
		}
		
		// GET STORE STATE,COUNTRY, BLOC
		$store_state	= $params->get('province');
		$store_country	= $params->get('country');
		
		$query = "SELECT * FROM #__mymuse_country WHERE country_2_code='$store_country'";
		$this->_db->setQuery($query);
		$store_country_res 		= $this->_db->loadObject();
		$store_country_3_code 	= $store_country_res->country_3_code;
		$store_bloc 			= $store_country_res->bloc;
		
		
		// for European taxes, are shopper and store both in EU? Both in same country?
		// For digital goods, you must now charge VAT based on the buyer's country,
		// break totals up into downloadable and physical
		if(($store_bloc == 'EU' && $user_bloc == 'EU') || $params->get('my_add_taxes')){
					
			//do euro tax
			$total_physical = 0;
			$total_downloadable = 0;
			foreach($order->items as $item) {
				if($item->product_physical){
					$total_physical += $item->product_item_subtotal;
				}else{
					$total_downloadable += $item->product_item_subtotal;
				}
			}
			
			//how to apply any discount
			if(!$total_physical){ //all downloads
				$total_downloadable = $total_downloadable - $order->discount;
			}
			if(!$total_downloadable){ //all physical
				$total_physical = $total_physical - $order->discount;
			}
			if($total_physical && $total_downloadable){ //it's a mix, take the highest one
				if($total_physical > $total_downloadable){
					$total_physical = $total_physical - $order->discount;
				}else{
					$total_downloadable = $total_downloadable - $order->discount;
				}
			}
			
			//case 1: same country, always charge VAT
			if($store_country_3_code == $user_country){
				//same country
				//we will use the regular calculation below
			
			}elseif(isset($shopper->profile['vat_number']) && $shopper->profile['vat_number'] != ''){
				//different country within union but has VAT Number so no tax
				$taxes['VAT Exempt'] = 0.00;
				return($taxes);
			}else{
				
				$temp_tax = 0;
				foreach($tax_rates as $rate){
					$name = preg_replace("/$regex/","_",$rate->tax_name);
					$taxes[$name] = 0;
					if($rate->country == $user_country){
						//downloadables
						$temp_tax = $total_downloadable * $rate->tax_rate;
						$taxes[$name] += $temp_tax;
						$taxes[$name] = round($taxes[$name],2);
					}elseif($rate->country == $store_country_3_code){
						//physical items
						$temp_tax = $total_physical * $rate->tax_rate;
						$taxes[$name] += $temp_tax;
						$taxes[$name] = round($taxes[$name],2);
					}
					if ($taxes[$name] == 0){
						unset($taxes[$name]);
					}
				}
				return $taxes;
			}
		}
		
		//regular calculation
		$temp_tax = 0;
		foreach($tax_rates as $rate){
			$name = preg_replace("/$regex/","_",$rate->tax_name);
			$taxes[$name] = 0;

			// APPLIES TO ALL
			if($rate->tax_applies_to == "C" &&
					($user_country == $rate->country || strtolower($user_country) == strtolower($rate->country_name))){
				$temp_tax = $order_subtotal * $rate->tax_rate;
				$taxes[$name] += $temp_tax;
			}

			// APPLIES TO A STATE/REGION
			
			//first is Californian
			if($rate->tax_applies_to == "S" &&
				($user_state == $rate->province || strtolower($user_state) == strtolower($rate->state_name)) &&
				$rate->state_name == "California" ){
				//only phisical items in California
				$temp_tax = $total_physical * $rate->tax_rate;
				$taxes[$name] += $temp_tax;
				
				// now all other states
			}elseif($rate->tax_applies_to == "S" &&
					($user_state == $rate->province || strtolower($user_state) == strtolower($rate->state_name)) ){
				if($rate->compounded == "1"){
					$order_subtotal += $temp_tax;
				}
				$temp_tax = $order_subtotal * $rate->tax_rate;
				$taxes[$name] += $temp_tax;
			}
		}

		reset($tax_rates);
		foreach($tax_rates as $rate){
			$name = preg_replace("/$regex/","_",$rate->tax_name);
			if ($taxes[$name] == 0){
				unset($taxes[$name]);
			}else{
				$taxes[$name] = round($taxes[$name],2);
			}
		}
		
		return($taxes);
	}

	/**
	 * addTax
	 *
	 * @param float price
	 * @return mixed float on success or false
	 */
	static function addTax($price=0)
	{
		
		if(!$price){
			return false;
		}
		$db	= JFactory::getDBO();
		$params = MyMuseHelper::getParams();
		$query = "SELECT country_3_code FROM #__mymuse_country WHERE country_2_code='".$params->get('country')."'";
		$db->setQuery($query);
		$country_3 = $db->loadResult();
		$new_price = $price;
		$taxes = array();
		$query = "SELECT tax_rate FROM #__mymuse_tax_rate WHERE state = '1' 
		AND country='".$country_3."'";
		//echo $query;
		$db->setQuery($query);
		$regex = TAX_REGEX;

		if($tax_rate = $db->loadResult()){
			$temp_tax = 0;
			
				$temp_tax = $price * $tax_rate;
				$new_price += $temp_tax;
			
		}
		$new_price = round($new_price,2);
		return $new_price;

	}



	function getOrder($id=0){
		$mainframe 	= JFactory::getApplication();
		$params 	= MyMuseHelper::getParams();
		$db			= JFactory::getDBO();

		if(!$id){
			$this->error = JText::_('MYMUSE_NO_ORDER_ID');
			return false;
		}
		 
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		=& $MyMuseShopper->getShopper();
		
		$MyMuseStore  	=& MyMuse::getObject('store','models');
		$store 			= $MyMuseStore->getStore();
		$MyMuseCart  	=& MyMuse::getObject('cart','helpers');
		$cart 			= $MyMuseCart->cart;
		$downloadable = 0;

		// get the main order
		$query = "SELECT * from #__mymuse_order WHERE id='$id'";
		$db->setQuery($query);
		$order = $db->loadObject();
		$order->shopper_group_name = @$shopper->shopper_group_name;
		$order->shopper_group_discount = @$shopper->discount;

		//get the taxes
		$order->tax_array = array();
		$order->tax_total = 0.00;
		$q = "SELECT * FROM #__mymuse_tax_rate ORDER BY ordering";
		$db->setQuery($q);
		$tax_rates = $db->loadObjectList();
		$regex = TAX_REGEX;

		foreach($tax_rates as $rate){
			$name = trim($rate->tax_name);
			$name = preg_replace("/$regex/","_",$name);
			if($order->$name > 0.00){
				$order->tax_array[$name] = $order->$name;
				$order->tax_total += $order->$name;
			}
		}

		//build up the items
		$query = "SELECT * from #__mymuse_order_item WHERE order_id=$id ORDER BY id";
		$db->setQuery($query);
		$order->items = $db->loadObjectList();

		for($i = 0; $i < count($order->items); $i++){
			$order->items[$i]->product = $MyMuseCart->getProduct($order->items[$i]->product_id);
			$order->items[$i]->title = $order->items[$i]->product->title;
			$order->items[$i]->quantity = $order->items[$i]->product_quantity;
			$order->items[$i]->product_item_subtotal = $order->items[$i]->product_item_price * $order->items[$i]->product_quantity;
			$order->items[$i]->product_in_stock = $order->items[$i]->product->product_in_stock;
			$order->items[$i]->ext = pathinfo($order->items[$i]->file_name, PATHINFO_EXTENSION);
			
			$order->items[$i]->ext = pathinfo($order->items[$i]->file_name, PATHINFO_EXTENSION);
			$order->items[$i]->file_length = $order->items[$i]->product->file_length;
			$order->items[$i]->file_time = $order->items[$i]->product->file_time;
			
			$catid = 0;
			// Does it have a parent?
			if ($order->items[$i]->product->parentid){
				$pid = $order->items[$i]->product->parentid;

			}else{
				$pid = $order->items[$i]->id;
			}

			$catid = $order->items[$i]->product->catid;

				
			$order->items[$i]->url = myMuseHelperRoute::getProductRoute($pid, $catid);
			$order->items[$i]->cat_url = myMuseHelperRoute::getCategoryRoute($catid);
			$query = "SELECT * FROM #__categories WHERE id='".$catid."'";
			$db->setQuery($query);
			$cat = $db->loadObject();
				
			$order->items[$i]->category_name = $cat->title;
			if( $params->get('my_downloads_enable') == "1" ) {
				if($order->items[$i]->file_name != ''){
					$downloadable++;
				}

			}
		}
		if($downloadable){
			$jinput = JFactory::getApplication()->input;
			$Itemid = $jinput->get("Itemid",$params->get('mymuse_default_itemid'));
			$download_header = '';
			include_once( JPATH_COMPONENT.DS.'templates'.DS.'mail_downloads.php' );
			$order->downloadlink = $download_header;
		}else{
			$order->downloadlink = '';
		}

		//coupon
		if($order->coupon_id){
			$order->coupon = new JObject;
			$order->coupon->id = $order->coupon_id;
			$order->coupon->title = $order->coupon_name;
			$order->coupon->discount = $order->coupon_discount;
		}

		//add payments
		$query = "SELECT * from #__mymuse_order_payment WHERE order_id=$id ORDER BY id";
		$db->setQuery($query);
		$order->payments = $db->loadObjectList();

		//add shipments
		$query = "SELECT * from #__mymuse_order_shipping WHERE order_id=$id ORDER BY id";
		$db->setQuery($query);
		if($order->shipments = $db->loadObjectList()){
			$order->order_shipping = $order->shipments[0];
		}else{
			$order->order_shipping = new JObject;
			$order->order_shipping->cost = 0;
		}

		//add more to the order object for printing
		$order->idx = count($order->items);
		$order->status_name = MyMuseHelper::getStatusName($order->order_status );
		$order->subtotal_before_discount = $order->order_subtotal  + @$order->coupon->discount + @$order->discount;
		$order->order_total = $order->order_subtotal + $order->order_shipping->cost + $order->tax_total;
		if($order->order_total < 0){
			$order->order_total = 0.00;
		}
		$order->order_currency = MyMuseHelper::getCurrency($MyMuseStore->_store->currency);
		
		if($params->get("my_show_sku",0) >0){
			$order->colspan=4;
		}else{
			$order->colspan=3;
		}
		$order->colspan2 = 1;
		$order->do_html = 0;
		$order->downloadable = $downloadable;

		return $order;

	}

}
?>