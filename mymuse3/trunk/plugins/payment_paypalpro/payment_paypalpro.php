<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

defined('_JEXEC') or die();

$Mymuseinclude = include_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/mymuse.php';
if(!$Mymuseinclude) { unset($Mymuseinclude); return; } else { unset($Mymuseinclude); }

class plgMyMusePayment_Paypalpro extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config = array())
	{
		$config = array_merge($config, array(
			'ppName'		=> 'paypalpaymentspro',
			'ppKey'			=> 'PLG_MYMUSE_PAYPALPAYMENTSPRO_TITLE',
			'ppImage'		=> rtrim(JURI::base(),'/').'/media/com_akeebasubs/images/frontend/paypaldirectcc.png'
		));

		parent::__construct($subject, $config);
	}
	/**
	 * Returns the payment form to be submitted by the user's browser.
	 * PayPalPro Payment form
	 * 
	 * @param object $shopper
	 * @param object $store
	 * @param object $order
	 * @param object $params
	 * @param int $Itemid
	 * 
	 * return string
	 */
	
	public function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		$db			= JFactory::getDBO();
		if(isset($shopper->profile['country'])){
			// Paypal wants the country_2_code
			$query = "SELECT country_2_code from #__mymuse_country WHERE country_3_code='".$shopper->profile['country']."'";
			$db->setQuery($query);
			$shopper->country = $db->loadResult();
		}else{
			$shopper->country = '';
		}
		if(isset($shopper->profile['shipping_country'])){
			// Paypal wants the country_2_code
			$query = "SELECT country_2_code from #__mymuse_country WHERE country_3_code='".$shopper->profile['shipping_country']."'";
			$db->setQuery($query);
			$shopper->shipping_country = $db->loadResult();
		}else{
			$shopper->shipping_country = '';
		}
		
		$shopper->address1 		= isset($shopper->profile['address1'])? $shopper->profile['address1'] : ''; 
		$shopper->address2 		= isset($shopper->profile['address2'])? $shopper->profile['address2'] : '';
		$shopper->city 			= isset($shopper->profile['city'])? $shopper->profile['city'] : '';
		$shopper->region 		= isset($shopper->profile['region_name'])? $shopper->profile['region_name'] : '';
		$shopper->postal_code 	= isset($shopper->profile['postal_code'])? $shopper->profile['postal_code'] : '';
		$shopper->first_name 	= isset($shopper->profile['first_name'])? $shopper->profile['first_name'] : '';
		$shopper->last_name 	= isset($shopper->profile['last_name'])? $shopper->profile['last_name'] : '';

		if(!$shopper->first_name){
			@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
		}
        if($shopper->first_name == "Guest"){
            $shopper->first_name = '';
            $shopper->last_name = '';
        }
		
		if(isset($shopper->profile['region'])){
			// Paypal wants the state_2_code
			$query = "SELECT state_2_code from #__mymuse_state WHERE id='".$shopper->profile['region']."'";
			$db->setQuery($query);
			$shopper->region = $db->loadResult();
		}
		if(isset($shopper->profile['shipping_region'])){
			// Paypal wants the state_2_code
			$query = "SELECT state_2_code from #__mymuse_state WHERE id='".$shopper->profile['shipping_region']."'";
			$db->setQuery($query);
			$shopper->shipping_region = $db->loadResult();
		}else{
			$shopper->shipping_region = '';
		}
		
		//PayPal Account Email
		if(
			$this->params->get('my_paypal_sandbox') &&
			$this->params->get('my_paypal_sandbox_email')
		){
			$merchant_email = $this->params->get('my_paypal_sandbox_email');
		}else{

			$merchant_email = $this->params->get('my_paypal_email');
		}
		
		//Shopper Email
		if($this->params->get('my_paypal_sandbox') && $this->params->get('my_paypal_sandbox_customer_email')){
			$payer_email = $this->params->get('my_paypal_sandbox_customer_email');
		}else{
			$payer_email = $shopper->email;
		}
		if($payer_email == "guest@arboreta.ca"){
			$payer_email = '';
		}
		
		//custom field
		$custom = 'userid='.$shopper->id;

		if(isset($order->order_number)){
			$custom .= '&order_number='.$order->order_number.'&email='.$shopper->email;
		}
		if($params->get('my_use_shipping') && isset($order->order_shipping->id)){
			$custom .= '&order_shipping_id='.$order->order_shipping->id;
		}
		if(!isset($order->order_shipping->cost)){
			$order->order_shipping->cost = 0.00;
		}
		
		//does this order have reservation fees? How much is the "Pay_now" field?
		if(isset($order->pay_now) && $order->pay_now > 0 && $order->pay_now < $order->order_subtotal){
			$order->idx = 1;
			$order->order_subtotal = $order->pay_now;
			$order->items[0]->product_item_price = sprintf("%.2f", $order->pay_now);
			$order->items[0]->quantity = 1;
			$order->items[0]->title = JText::_('MYMUSE_REGISTRATION_FEE');
			$order->tax_total = 0.00;
		}
		
		//<input type="hidden" name="return"          value="'. JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=paypal&Itemid='.$Itemid.'" />
		//<input type="hidden" name="cancel_return"   value="'. JURI::base().'index.php?option=com_mymuse&task=paycancel&Itemid='.$Itemid.'" />
		//<input type="hidden" name="notify_url"      value="'. JURI::base().'index.php?option=com_mymuse&task=notify" />

		
		
		
		$callbackUrl = JURI::base().'index.php?option=com_mymuse&task=notify&Itemid='.$Itemid;
	
		$data = (object)array(
			'URL'				=> $callbackUrl."&mode=init",
			'NOTIFYURL'			=> $callbackUrl,
			'USER'				=> $this->getMerchantUsername(),
			'PWD'				=> $this->getMerchantPassword(),
			'SIGNATURE'			=> $this->getMerchantSignature(),
			'VERSION'			=> '124.0',
			'PAYMENTACTION'		=> 'Sale',
			'IPADDRESS'			=> $_SERVER['REMOTE_ADDR'],
			'FIRSTNAME'			=> $shopper->first_name,
			'LASTNAME'			=> $shopper->last_name,
			'EMAIL'				=> $payer_email,
			'STREET'			=> trim($shopper->address1),
			'STREET2'			=> trim($shopper->address2),
			'CITY'				=> trim($shopper->city),
			'STATE'				=> trim($shopper->region),
			'COUNTRYCODE'		=> strtoupper(trim($shopper->country)),
			'ZIP'				=> trim($shopper->postal_code),
			'AMT'				=> $order->order_total,
			'ITEMAMT'			=> $order->order_subtotal,
			'SHIPPINGAMT'		=> $order->order_shipping->cost,
			'TAXAMT'			=> $order->tax_total,
			'CURRENCYCODE'		=> strtoupper($store->currency),
			'DESC'				=> $store->title,
			'BUTTONSOURCE'		=> 'Arboreta_SP'
		);

		//send individual items
		$j = 0;
		$data->ITEMS = 0;
		for ($i=0;$i<$order->idx;$i++) {
			if(isset($order->items[$i]->title) && $order->items[$i]->title != ''){
				$item_name = 'L_NAME'. $j;
				$quant_name = 'L_QTY'. $j;
				$amount_name = 'L_AMT'. $j;
				
				$data->$item_name = $order->items[$i]->title;
				$data->$quant_name = $order->items[$i]->product_quantity;
				$data->$amount_name = $order->items[$i]->product_item_price;
				$j++;
			}
			
		}
		//coupon discount
		if(isset($order->coupon_discount) && $order->coupon_discount > 0){
			$custom .= "&coupon_id=".$order->coupon_id;
			$data->discount_amount_cart = sprintf("%01.2f", $order->coupon_discount);	
			
			$item_name = 'L_NAME'. $j;
			$quant_name = 'L_QTY'. $j;
			$amount_name = 'L_AMT'. $j;
			$data->$item_name = JText::_('MYMUSE_DISCOUNT');
			$data->$quant_name = 1;
			$data->$amount_name = -sprintf("%01.2f", $order->coupon_discount);
			$j++;
			$i++;
		}
		//plugin discount
		if(isset($order->discount) && $order->discount > 0){
			$data->discount_amount_cart = sprintf("%.2f", $order->discount);
			
			$item_name = 'L_NAME'. $j;
			$quant_name = 'L_QTY'. $j;
			$amount_name = 'L_AMT'. $j;
			$data->$item_name = JText::_('MYMUSE_DISCOUNT');
			$data->$quant_name = 1;
			$data->$amount_name = -sprintf("%01.2f", $order->discount);
			$j++;
		}
		$data->CUSTOM = $custom;
		$data->ITEMS = $j;
	
		
		if($params->get('my_use_shipping') && isset($order->order_shipping->cost) && $order->order_shipping->cost > 0){
			$data->SHIPPINGAMT 		= $order->order_shipping->cost;
			if(!isset($shopper->profile['shipping_first_name'])){
				$shopper->profile['shipping_first_name'] = '';
			}
			if(!isset($shopper->profile['shipping_last_name'])){
				$shopper->profile['shipping_last_name'] = '';
			}
			
			$data->SHIPTONAME 		= $shopper->profile['shipping_first_name']." ".$shopper->profile['shipping_last_name'];
			$data->SHIPTOSTREET 	= @$shopper->profile['shipping_address1'];
			$data->SHIPTOSTREET2 	= @$shopper->profile['shipping_address2'];
			$data->SHIPTOCITY 		= @$shopper->profile['shipping_city'];
			if(isset($shopper->profile['shipping_region'])){
				if (! is_numeric ( $shopper->profile ['shipping_region'] )) {
					$shopper->profile ['shipping_region'] = $shopper->profile ['shipping_region'];
				} else {
					$db = JFactory::getDBO ();
					$query = "SELECT * FROM #__mymuse_state WHERE id='" . $shopper->profile ['shipping_region'] . "'";
					$db->setQuery ( $query );
					if ($row = $db->loadObject ()) {
						$shopper->profile ['shipping_region_name'] = $row->state_2_code;
					}
				}
			}else{
				$shopper->profile['shipping_region_name'] = '';
				$shopper->profile['shipping_region'] = '';
			}
			$data->SHIPTOSTATE 		= $shopper->profile['shipping_region_name'];
			$data->SHIPTOZIP 		= @$shopper->profile['shipping_postal_code'];
			$data->SHIPTOCOUNTRY 	= @$shopper->profile['shipping_country'];		
		}
		


		$data->METHOD			= 'DoDirectPayment';
		$data->INVNUM			= $order->order_number;

		
		//make country select
		$query = 'SELECT country_2_code as value,  country_name AS text FROM #__mymuse_country';
		$db->setQuery($query);
		$countries = $db->loadObjectList();
	
		$country_select_array 	= array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_COUNTRY' ) .' -' ) );
		foreach ( $countries as $country ) {
			$country_select_array[] = JHTML::_('select.option',  $country->value, $country->text );
			$country_list[] = $country->value;
		}
		$COUNTRY_SELECT_HTML = JHTML::_('select.genericlist',  $country_select_array, 'COUNTRYCODE', 
				'class="inputbox" size="1" onchange="changeDynaList2(STATE, COUNTRYCODE, countrystates,0,0);"', 
				'value', 'text', $shopper->country );

		//make state/region select
		$country_list = implode('\', \'', $country_list);
		$query = 'SELECT #__mymuse_state.state_name as text, #__mymuse_state.state_2_code as value' .
				' FROM #__mymuse_state,#__mymuse_country' .
				' WHERE  #__mymuse_state.country_id=#__mymuse_country.id' .
				' ORDER BY country_id,state_name';
		
		$db->setQuery($query);
		
		$state_list = $db->loadObjectList();
		$state_select_array = array();
		foreach ($state_list as $state) {
			$state_select_array[] = JHTML::_('select.option',  $state->value, $state->text );
		}
		
		$STATE_SELECT_HTML = JHTML::_('select.genericlist',  $state_select_array, 'STATE', 'class="inputbox" size="1" ',
				'value', 'text', $shopper->region );
		
		if($params->get('my_use_shipping') && isset($order->order_shipping->cost) && $order->order_shipping->cost > 0){
			$SHIPPING_COUNTRY_SELECT_HTML = JHTML::_('select.genericlist',  $country_select_array, 'SHIPTOCOUNTRY',
					'class="inputbox" size="1" onchange="changeDynaList2(SHIPTOSTATE, SHIPTOCOUNTRY, countrystates,0,0);"',
					'value', 'text', $shopper->shipping_country );
			$SHIPPING_STATE_SELECT_HTML = JHTML::_('select.genericlist',  $state_select_array, 'SHIPTOSTATE', 'class="inputbox" size="1" ',
					'value', 'text', $shopper->shipping_region );
		}
		//make javascript
		$countrystates = $this->listCountryState($shopper->country,$shopper->region );
		
		
		$javascript = '
		var countrystates = new Array;
		';
		$i = 0;
		foreach ($countrystates as $k=>$items) {
			foreach ($items as $v) {
				$javascript .= "countrystates[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
			}
		}
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($javascript);
		
		$js = "/**
		* Changes a dynamically generated list
		* @param html obj The name of the list to change
		* @param html obj The instigator of the change
		* @param array A javascript array of list options in the form [key,value,text]
		* @param string The original key that was selected
		* @param string The original item value that was selected
		*/
		function changeDynaList2( list, source, myarr, orig_key, orig_val) {
		
			var key = source.options[source.selectedIndex].value;
		
			// empty the list
			for (i in list.options.length) {
				list.options[i] = null;
			}
			i = 0;
			for (x in myarr) {
				if (myarr[x][0] == key) {
					opt = new Option();
					opt.value = myarr[x][1];
					opt.text = myarr[x][2];
		
					if ((orig_key == key && orig_val == opt.value) || i == 0) {
						opt.selected = true;
					}
			
					list.options[i++] = opt;
				}
			}
			list.length = i;
		}
		
		window.onload = function(e){
			changeDynaList2(STATE, COUNTRYCODE, countrystates,'".$shopper->country."','".$shopper->region."');
			changeDynaList2(SHIPTOSTATE, SHIPTOCOUNTRY, countrystates,'".$shopper->country."','".$shopper->region."');
		}
		";
		$document->addScriptDeclaration($js);
		// print_pre($data);
		$path = JPluginHelper::getLayoutPath('mymuse', 'payment_paypalpro');
		@ob_start();
		include $path;
		$html = @ob_get_clean();
		
		return $html;
	}

	/**
	 * notify
	 * catch the IPN post from PayPal, return required responses, update orders and do mailouts
	 *
	 */
	function onMyMuseNotify($params, $Itemid = 1)
	{
		$jinput = JFactory::getApplication()->input;
		$data = $jinput->post->getArray();
	
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nPayPalPaymentsPro notify PLUGIN\n";
	
		//INIT MODE
		if(isset($data['mode']) && $data['mode'] == 'init') {
			//User has submitted the form 
			if ($params->get ( 'my_debug' )) {
				$debug .= "Init Mode Data : " . print_r ( $data, true );
				MyMuseHelper::logMessage ( $debug );
			}
			
			$requestData = array ();
			foreach ( $data as $key => $val ) {
				if ($key == 'option' || $key == 'view' || $key == 'task' || $key == 'pp')
					continue;
				$requestData [$key] = trim ( $val );
				if ($key == 'CVV2')
					break;
			}
			
			$requestQuery = http_build_query ( $requestData );
			if ($this->getApiMethod () == 'file_get_contents') {
				$requestContext = stream_context_create ( array (
						'http' => array (
								'method' => 'POST',
								'header' => "Connection: close\r\n" . "Content-Length: " . strlen ( $requestQuery ) . "\r\n",
								'content' => $requestQuery
						) 
				) );
				$responseQuery = file_get_contents ( $this->getPaymentURL (), false, $requestContext );
			} else {
				
				$http_header = array (
						'X-PAYPAL-SECURITY-USERID' => $this->getMerchantUsername (),
						'X-PAYPAL-SECURITY-PASSWORD' => $this->getMerchantPassword (),
						'X-PAYPAL-SECURITY-SIGNATURE' => $this->getMerchantSignature () 
				);
				
				$curlOptions = array (
						CURLOPT_HTTPHEADER => $http_header,
						CURLOPT_URL => $this->getPaymentURL (),
						CURLOPT_VERBOSE => 1,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => $requestQuery 
				);
				
				$ch = curl_init ();
				curl_setopt_array ( $ch, $curlOptions );
				
				$responseQuery = curl_exec ( $ch ); // make the request
				
				if (curl_errno ( $ch )) {
					$this->_errors = curl_error ( $ch );
					curl_close ( $ch );
					return false;
				} else {
					curl_close ( $ch );
				}
			}
			
			// Payment Response
			parse_str ( $responseQuery, $responseData );
			if ($params->get ( 'my_debug' )) {
				$debug = "responseData : " . print_r ( $responseData, true );
				MyMuseHelper::logMessage ( $debug );
			}
			if (! preg_match ( '/^Success/', $responseData ['ACK'] )) {
				$result ['error'] = $responseData ['L_LONGMESSAGE0'];
				$isValid = false;
			}else{
				$isValid = true;
			}
			
			$orderid = '';
			if(isset($data['INVNUM'])){
				$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$data['INVNUM']."'";
				$db->setQuery($query);
				$order = $db->loadObject();
				$orderid = $order->id;
				$notes = $order->notes;
			
			}
			
			if($params->get('my_registration') == "no_reg"){
				//update order notes
				// let's leave this for now
				/**
				$notes = '';
				$notes .= "first_name=".$data['FIRSTNAME']."\n";
				$notes .= "last_name=".$data['LASTNAME']."\n";
				$notes .= "email=".urldecode($data['EMAIL'])."\n";
				$notes .= "address1=".$data['STREET']."\n";
				$notes .= "address2=".$data['STREET2']."\n";
				$notes .= "city=".$data['CITY']."\n";
				$notes .= "country=".$data['COUNTRYCODE']."\n";
				$notes .= "region_name=".$data['STATE']."\n";
				$notes .= "postal_code=".$data['ZIP']."\n";
				if(isset($data['SHIPTONAME'])){
					list($first,$last) = explode(" ",$data['SHIPTONAME']);
					$notes .= "shipping_first_name=".$first."\n";
					$notes .= "shipping_last_name=".$last."\n";
				}
				if(isset($data['SHIPTOSTREET'])){
					$notes .= "shipping_address1=".$data['SHIPTOSTREET']."\n";
				}
				if(isset($data['SHIPTOSTREET2'])){
					$notes .= "shipping_address2=".$data['SHIPTOSTREET2']."\n";
				}
				if(isset($data['SHIPTOCITY'])){
					$notes .= "shipping_city=".$data['SHIPTOCITY']."\n";
				}
				if(isset($data['SHIPTOSTATE'])){
					$notes .= "shipping_region=".$data['SHIPTOSTATE']."\n";
				}
				if(isset($data['SHIPTOCOUNTRY'])){
					$notes .= "shipping_country=".$data['SHIPTOCOUNTRY']."\n";
				}
				if(isset($data['SHIPTOZIP'])){
					$notes .= "shipping_postal_code=".$data['SHIPTOZIP']."\n";
				}
				
				
				$query = "UPDATE #__mymuse_order SET notes='$notes' WHERE
				order_number='".$data['INVNUM']."'";
				$db->setQuery($query);
				$db->query();
				*/
			}
			
			
			
			
			sleep(5);
			$path = JURI::root(true);
			if(!$isValid ){
				$thankyouUrl = JRoute::_('index.php?option=com_mymuse&task=paycancel&view=cart&pp=paypalpaymentspro&orderid='.$orderid.'&Itemid='.$Itemid, false);
				$thankyouUrl = rtrim(JURI::root(),"/").preg_replace("#$path#",'',$thankyouUrl);
				$msg = "Payment Failed: ".$result ['error'];
			}else{
				$thankyouUrl = JRoute::_('index.php?option=com_mymuse&task=thankyou&view=cart&pp=paypalpaymentspro&orderid='.$orderid.'&Itemid='.$Itemid, false);
				$thankyouUrl = rtrim(JURI::root(),"/").preg_replace("#$path#",'',$thankyouUrl);
				$msg = "";
			}
			$path = JURI::root(true);
			$link = JURI::root().preg_replace("#$path/#",'',$thankyouUrl);
			JFactory::getApplication()->redirect($link, $msg);
			return true;
			exit ();
		

		} else {
			
			
			// paypal IPN coming in
			$debug = "#####################\nPayPalPaymentsPro PLUGIN IPN Response\n";
			
			$result = array ();
			$result ['plugin'] = "paypalpaymentspro";
			$result ['myorder'] = 0; // must be >0 to trigger that it was this plugin
			$result ['message_sent'] = 0; // must be >0 or tiggers error
			$result ['message_received'] = 0; // must be >0 or tiggers error
			$result ['order_found'] = 0; // must be >0 or tiggers error
			$result ['order_verified'] = 0; // must be >0 or tiggers error
			$result ['order_completed'] = 0; // must be >0 or tiggers error
			$result ['order_number'] = 0; // must be >0 or tiggers error
			$result ['order_id'] = 0; // must be >0 or tiggers error
			$result ['payer_email'] = 0;
			$result ['payment_status'] = 0;
			$result ['txn_id'] = 0;
			$result ['error'] = '';
			$result ['redirect'] = '';
				
			//if(!isset($data['ACK'])){
				//wasn't paypalpaymentspro
				//$debug .= "Was not paypalpaymentspro. \n";
				//$debug .= "-------END-------";
				//if($params->get('my_debug')){
				//	MyMuseHelper::logMessage( $debug  );
				//}
				//return $result;
			//}else{
				if($params->get('my_debug')){
					$debug .= "IPN Mode Data : " . print_r ( $data, true );
					MyMuseHelper::logMessage( $debug  );
				}
				$result ['myorder'] = 1;
			//}
			
			// Check IPN data for validity (i.e. protect against fraud attempt)
			$isValid = $this->isValidIPN ( $data );
			$result ['message_sent'] = 1;
			
			if (! $isValid){
				$result ['error'] = 'PayPal reports transaction as invalid';
				if($params->get('my_debug')){
					$debug = $result ['error'];
					MyMuseHelper::logMessage( $debug  );
				}
				return $result;
			}
			$result ['message_received'] = 1;
			$result ['order_found'] = 1;
			$result ['order_verified'] = 1;
			$result ['order_completed'] = 1;
			
			if(isset($data['custom'])){
				$c = explode('&',$data['custom']);
				foreach($c as $pair){
					if($pair){
						list($key,$val) = explode('=',$pair);
						$custom[$key] = $val;
					}
				}

			}
			$result['order_number'] 		= $data['invoice'];
			$result['payer_email'] 			= urldecode($data['payer_email']);
			$result['user_email'] 			= @$custom['email'];
			$result['userid'] 				= @$custom['userid'];
			
			$result['payment_status'] 		= $data['payment_status'];
			$result['txn_id'] 				= trim(stripslashes($data['txn_id']));
			$result['amountin'] 			= $data['mc_gross'];
			$result['currency'] 			= $data['mc_currency'];
			$result['rate'] 				= @$data['rate'];
			$result['fees'] 				= @$data['mc_fee'];
			$result['transaction_id'] 		= $data['txn_id'];
			$result['transaction_status'] 	= $data['payment_status'];
			$result['description'] 			= @$data['note'];
			
			
			// Check txn_type; we only accept web_accept transactions with this plugin
			if ($isValid) {
				// This is required to process some IPNs, such as Reversed and Canceled_Reversal
				if (! array_key_exists ( 'txn_type', $data )) {
					$data ['txn_type'] = 'workaround_to_missing_txn_type';
				}
				
				$validTypes = array (
						'workaround_to_missing_txn_type',
						'web_accept',
						'recurring_payment',
						'subscr_payment',
						'express_checkout',
						'pro_api',
						'cart'
				);
				$isValid = in_array ( $data ['txn_type'], $validTypes );
				
				if (! $isValid) {
					$result ['error'] = "Transaction type " . $data ['txn_type'] . " can't be processed by this payment plugin.";
					if($params->get('my_debug')){
						MyMuseHelper::logMessage( $result ['error'] );
					}
					return $result;
				}
			}
			
			//order was verified!
			$date = date('Y-m-d h:i:s');
			$debug = "$date  4. order VERIFIED at PayPal\n\n";
			$result['order_verified'] = 1;
			//$result['payment_status'] = "Completed";
			
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}

			// SAVE ORDER AFTER
			if($params->get('my_saveorder') == "after"){
				//must capture the order here
			
				$MyMuseCart		=& MyMuse::getObject('cart','helpers');
				$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
				$MyMuseShopper 	=& MyMuse::getObject('shopper','models');
				$debug = "4.0.0 We have a post:".print_r($_POST,true)."\n\n";
				$debug .= "We have custom:".print_r($custom,true)."\n\n";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
					
				if($params->get('my_registration') == "no_reg"){
					//it's the guest user
					$q = "SELECT u.id FROM #__users as u
  						WHERE
  						u.username='buyer'";
				}else{
					$q = "SELECT u.id from #__users as u
  						WHERE
  						u.id='".$result['userid']."'";
				}
				$db->setQuery($q);
				$user_id = $db->loadResult();
				if(!$user_id){
					$debug = "4.0.1 We do not have a user id! Must exit. ";
					$result['error'] = $debug;
					$debug .= "\n $q \nEmails were \npayer ".$data['payer_email']." user ".$result['user_email']."\n";
					$debug .= "-------END-------";
					if($params->get('my_debug')){
						MyMuseHelper::logMessage( $debug  );
					}
					$result['error'] = $debug;
					return $result;
				}
					
				$cart = array();
				$cart['idx'] = $data['num_cart_items'];
				$j=0;
				for($i=0;$i<$cart['idx']; $i++){
					$j++;
					$cart[$i]['quantity'] = $_POST['quantity'.$j];
					list($name,$sku) = explode(" : ",$_POST['item_name'.$j]);
					$q = "SELECT * FROM #__mymuse_product WHERE product_sku='$sku'";
					$db->setQuery($q);
					$p = $db->loadObject();
					$cart[$i]['product_id']= $p->id;
					$cart[$i]['catid']= $p->catid;
					$cart[$i]['product_physical']= $p->product_physical;
				}
				//coupon discount
				if(isset($custom['coupon_id'])){
					$cart[$i]['coupon_id']= $custom['coupon_id'];
					$cart['idx']++;
				}
				//shipping?
				if (isset($custom['order_shipping_id'])){
					$cart_order = $MyMuseCart->buildOrder( 0 );
					$cart['ship_method_id'] = $custom['order_shipping_id'];
					$dispatcher	= JDispatcher::getInstance();
					$res = $dispatcher->trigger('onCaclulateMyMuseShipping', array($cart_order, $cart['ship_method_id'] ));
					$MyMuseCart->cart['shipping'] = $res[0];
				}
					
				//save the cart in the session
				$MyMuseCart->cart = $cart;
				$session = JFactory::getSession();
				$session->set("cart",$MyMuseCart->cart);
			
			
				if($params->get('my_debug')){
					$debug = "4.0.2 We have created a cart: $q  ".print_r($MyMuseCart->cart,true)."\n\n";
					MyMuseHelper::logMessage( $debug  );
					$debug = '';
				}
					
				// Shopper
				$user = JFactory::getUser($user_id);
				$shopper = $MyMuseShopper->getShopperByUser($user_id);
				if($params->get('my_registration') == "no_reg"){
					$shopper->profile = $custom;
					foreach($custom as $field => $val){
						$debug = "Assign $val to $field";
						if($params->get('my_debug')){
							MyMuseHelper::logMessage( $debug  );
						}
						if(!$shopper->set($field,$val)){
							$debug = $shopper->getError();
							MyMuseHelper::logMessage( $debug  );
						}
					}
					if(isset($custom['first_name']) || isset($custom['last_name']) ){
						$shopper->set('name',@$custom['first_name']." ".@$custom['last_name']);
					}
				}
					
				$session->set("user",$shopper);
				$debug = "4.0.1 We have created a shopper in the session: $user_id  ".print_r($shopper,true)."\n\n";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
					
				//let's save the order at checkout
				if(!$order = $MyMuseCheckout->save( )){
					$msg = $MyMuseCheckout->error;
					$debug = "4.0.3 !!!!Could not save order after: ".$msg."\n\n";
					$debug .= "-------END-------";
					if($params->get('my_debug')){
						MyMuseHelper::logMessage( $debug  );
					}
					$result['error'] = $debug;
					return $result;
				}
				$result['order_number'] = $order->order_number;
				$debug = "4.0.4 Order saved:  ".$order->order_number."\n\n";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
			
			
			}
			
			// Get the Order Details from the database
			
			$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$result['order_number']."'";
			$date = date('Y-m-d h:i:s');
			$debug = "$date  4.1 $query \n\n";
			
			$db->setQuery($query);
			if(!$this_order = $db->loadObject()){
				$debug .= "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";
				$debug .= "-------END-------";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
				$result['error'] = $debug;
				return $result;
			}else{
				// update the payment status
				$result['order_found']  = 1;
				$result['order_id'] 	= $this_order->id;
				if (preg_match ("/Completed/", $result['payment_status'])) {
					$helper = new MyMuseHelper;
					$helper->orderStatusUpdate($result['order_id'] , "C");
					$date = date('Y-m-d h:i:s');
					$debug .= "$date 5. order COMPLETED at PayPal, update in DB\n\n";
					$result['order_completed'] = 1;
				}else{
					// not completed, set order status to
					MyMuseHelper::orderStatusUpdate($result['order_id'] , "I");
				}
			}
				
			
			return $result;
		}
		
		
	}
	/**
	 * Validates the incoming data against PayPal's IPN to make sure this is not a
	 * fraudelent request.
	 */
	private function isValidIPN($data)
	{
		$sandbox = $this->params->get('sandbox',0);
		$hostname = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
	
		$url = 'ssl://'.$hostname;
		$port = 443;
	
		$req = 'cmd=_notify-validate';
		foreach($data as $key => $value) {
			$value = urlencode($value);
			$req .= "&$key=$value";
		}
		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Host: $hostname:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";
	
	
		$fp = fsockopen ($url, $port, $errno, $errstr, 30);
	
		if (!$fp) {
			// HTTP ERROR
			return false;
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (stristr($res, "VERIFIED")) {
					return true;
				} else if (stristr($res, "INVALID")) {
					return false;
				}
			}
			fclose ($fp);
		}
	}
	
	private function getPaymentURL()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			return 'https://api-3t.paypal.com/nvp';
		}
	}
	
	private function getMerchantUsername()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return trim($this->params->get('sb_apiuser',''));
		} else {
			return trim($this->params->get('apiuser',''));
		}
	}
	
	private function getMerchantPassword()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return trim($this->params->get('sb_apipw',''));
		} else {
			return trim($this->params->get('apipw',''));
		}
	}
	
	private function getMerchantSignature()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return trim($this->params->get('sb_apisig',''));
		} else {
			return trim($this->params->get('apisig',''));
		}
	}
	
	private function getApiMethod()
	{
		$apimethod = $this->params->get('apimethod',0);
		if ($apimethod)
		{
			return 'curl';
		}
		else
		{
			return 'file_get_contents';
		}
	}
	
	public function selectExpirationDate()
	{
		$year = gmdate('Y');
	
		$options = array();
		$options[] = JHTML::_('select.option',0,'--');
		for($i = 0; $i <= 10; $i++) {
			$y = sprintf('%04u', $i+$year);
			for($j = 1; $j <= 12; $j++) {
				$m = sprintf('%02u', $j);
				$options[] = JHTML::_('select.option', ($m.$y), ($m.'/'.$y));
			}
		}
	
		return JHTML::_('select.genericlist', $options, 'EXPDATE', 'class="input-medium"', 'value', 'text', '', 'EXPDATE');
	}
	
	
	/**
	 * listCountryState
	 * Print a select box
	 *
	 * @param string $list_name
	 * @param string $value
	 * @return bool
	 */
	function listCountryState($country_select='', $state_select='', $store_country='') {
	
		$db	= JFactory::getDBO();

		//$countries[] = JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_SELECT_COUNTRY').' -');
		$query = "SELECT id, country_2_code as value, country_name as text from #__mymuse_country 
		 ORDER BY country_name ASC";
		$db->setQuery($query);
		$dbcountries = $db->loadObjectList();
		//$countries = array_merge($countries, $dbcountries);
	
	
		foreach ($dbcountries as $country)
		{
			$country_list[] = (int) $country->id;
	
			if ($country_select != '') {
				if ($country->value == $country_select) {
					$contentCountry = $country->text;
				}
			}
		}
	
		$countrystates = array ();
		//$countrystates[-1] = array ();
		//$countrystates[-1][] = JHTML::_('select.option', '-1', JText::_( 'MYMUSE_SELECT_COUNTRY' ), 'id', 'title');
		$country_list = implode('\', \'', $country_list);
	
		$query = 'SELECT #__mymuse_state.id as code, state_name as title, #__mymuse_state.state_2_code as id, country_2_code, country_id' .
				' FROM #__mymuse_state,#__mymuse_country' .
				' WHERE country_id IN ( \''.$country_list.'\' )' .
				' AND #__mymuse_state.country_id=#__mymuse_country.id' .
				' ORDER BY country_id,state_name';
	
		$db->setQuery($query);
		$state_list = $db->loadObjectList();
	
		foreach ($dbcountries as $country)
		{
	
			$countrystates[$country->value] = array ();
			$rows2 = array ();
			foreach ($state_list as $state)
			{
				if ($state->country_2_code == $country->value) {
					$rows2[] = $state;
				}
			}
			foreach ($rows2 as $row2) {
				$countrystates[$country->value][] = JHTML::_('select.option', $row2->id, $row2->title, 'id', 'title');
			}
		}
	
		$countrystates['-1'][] = JHTML::_('select.option', '-1', JText::_( 'MYMUSE_SELECT_STATE' ), 'id', 'title');
	
		return $countrystates;
	
	}

	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "Payment PayPalPro:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		}
		return $email_msg;
	
	}
}
