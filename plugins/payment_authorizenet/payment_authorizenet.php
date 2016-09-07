<?php
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2016 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

defined('_JEXEC') or die();

include_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/mymuse.php';
include_once JPATH_SITE.'/plugins/mymuse/payment_authorizenet/PaymentTransactions/charge-credit-card.php';
jimport( 'joomla.plugin.plugin');

class plgMyMusePayment_Authorizenet extends JPlugin
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
		parent::__construct($subject, $config);
		
		if($this->params->get('my_authorizenet_sandbox')){
			define('MERCHANT_LOGIN_ID', $this->params->get('sb_apiloginid') );
			define('MERCHANT_TRANSACTION_KEY', $this->params->get('sb_apitransactionkey') );
			define('AUTHORIZENET_API_LOGIN_ID', $this->params->get('sb_apiloginid') );
			define('AUTHORIZENET_TRANSACTION_KEY', $this->params->get('sb_apitransactionkey') );
			define("AUTHORIZENET_SANDBOX", true);
			
			
		}else{
			define('MERCHANT_LOGIN_ID', $this->params->get('apiloginid') );
			define('MERCHANT_TRANSACTION_KEY', $this->params->get('apitransactionkey') );
			define('AUTHORIZENET_API_LOGIN_ID', $this->params->get('apiloginid') );
			define('AUTHORIZENET_TRANSACTION_KEY', $this->params->get('apitransactionkey') );
			define("AUTHORIZENET_SANDBOX", false);
		}
	}


	
	
	/**
	 *Example fields
	 $sale->card_num           = '4111111111111111';
	 $sale->exp_date           = '04/20';
	 $sale->amount             = $amount = rand(1,99);
	 $sale->description        = $description = "Sale description";
	 $sale->first_name         = $first_name = "Jane";
	 $sale->last_name          = $last_name = "Smith";
	 $sale->company            = $company = "Jane Smith Enterprises Inc.";
	 $sale->address            = $address = "20 Main Street";
	 $sale->city               = $city = "San Francisco";
	 $sale->state              = $state = "CA";
	 $sale->zip                = $zip = "94110";
	 $sale->country            = $country = "US";
	 $sale->phone              = $phone = "415-555-5557";
	 $sale->fax                = $fax = "415-555-5556";
	 $sale->email              = $email = "foo@example.com";
	 $sale->cust_id            = $customer_id = "55";
	 $sale->customer_ip        = "98.5.5.5";
	 $sale->invoice_num        = $invoice_number = "123";
	 $sale->ship_to_first_name = $ship_to_first_name = "John";
	 $sale->ship_to_last_name  = $ship_to_last_name = "Smith";
	 $sale->ship_to_company    = $ship_to_company = "Smith Enterprises Inc.";
	 $sale->ship_to_address    = $ship_to_address = "10 Main Street";
	 $sale->ship_to_city       = $ship_to_city = "San Francisco";
	 $sale->ship_to_state      = $ship_to_state = "CA";
	 $sale->ship_to_zip        = $ship_to_zip_code = "94110";
	 $sale->ship_to_country    = $ship_to_country = "US";
	 $sale->tax                = $tax = "0.00";
	 $sale->freight            = $freight = "Freight<|>ground overnight<|>12.95";
	 $sale->duty               = $duty = "Duty1<|>export<|>15.00";
	 $sale->tax_exempt         = $tax_exempt = "FALSE";
	 $sale->po_num             = $po_num = "12";
	
	
	
	//send individual items
	
	 $sale->addLineItem(
	 'item1', // Item Id
	 'Golf tees', // Item Name
	 'Blue tees', // Item Description
	 '2', // Item Quantity
	 '5.00', // Item Unit Price
	 'N' // Item taxable
	 );
	 */
	
	
	/**
	 * Returns the payment form to be submitted by the user's browser.
	 * Authorizenet Payment form
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
		//print_pre($shopper->profile);
		//first name
		if(!$shopper->profile['first_name'] && isset($shopper->name)){
			@list($shopper->profile['first_name'],$shopper->profile['last_name']) = explode(" ",$shopper->name);
		}
		//country 2 code
		if(isset($shopper->profile['country'])){
			// Paypal wants the country_2_code
			$query = "SELECT country_2_code from #__mymuse_country WHERE country_3_code='".$shopper->profile['country']."'";
			$db->setQuery($query);
			$shopper->profile['country'] = $db->loadResult();
		}else{
			$shopper->shipping_country = '';
		}
		//shipping country 2 code
		if(isset($shopper->profile['shipping_country'])){
			// Paypal wants the country_2_code
			$query = "SELECT country_2_code from #__mymuse_country WHERE country_3_code='".$shopper->profile['shipping_country']."'";
			$db->setQuery($query);
			$shopper->profile['shipping_country'] = $db->loadResult();
		}else{
			$shopper->shipping_country = '';
		}		


		//region 2 code
		if(isset($shopper->profile['region'])){
			// Paypal wants the state_2_code
			$query = "SELECT state_2_code from #__mymuse_state WHERE id='".$shopper->profile['region']."'";
			$db->setQuery($query);
			$shopper->profile['region'] = $db->loadResult();
		}
		//shipping region 2 code
		if(isset($shopper->profile['shipping_region'])){
			// Paypal wants the state_2_code
			$query = "SELECT state_2_code from #__mymuse_state WHERE id='".$shopper->profile['shipping_region']."'";
			$db->setQuery($query);
			$shopper->profile['shipping_region'] = $db->loadResult();
		}else{
			$shopper->profile['shipping_region'] = '';
		}
		
		
		//Shopper Email
		if(!isset($shopper->profile['email'])){
			$shopper->profile['email'] = $shopper->email;
		}
		
		//check other fields
		if(!isset($shopper->profile['address1'])){ $shopper->profile['address1'] = ''; }
		if(!isset($shopper->profile['address2'])){ $shopper->profile['address2'] = ''; }
		if(!isset($shopper->profile['city'])){ $shopper->profile['city'] = ''; }
		if(!isset($shopper->profile['postal_code'])){ $shopper->profile['postal_code'] = ''; }
		if(!isset($shopper->profile['phone'])){ $shopper->profile['phone'] = ''; }

		//custom field
		$custom = 'userid='.$shopper->id;

		if(isset($order->order_number)){
			$custom .= '&order_number='.$order->order_number;
		}
		if($params->get('my_use_shipping') && isset($order->order_shipping->id)){
			$custom .= '&order_shipping_id='.$order->order_shipping->id;
		}
		if(!isset($order->order_shipping->cost)){
			$order->order_shipping->cost = 0.00;
		}
		
		
		$callbackUrl = JURI::base().'index.php?option=com_mymuse&task=notify&pp=anet';
		if(isset($order->id)){
			$callbackUrl .= '&id='.$order->id;
		}

		$j = 0;
		//coupon discount
		if(isset($order->coupon_discount) && $order->coupon_discount > 0){
			$custom .= "&coupon_id=".$order->coupon_id;
			$custom .= "&discount_amount_cart=".sprintf("%01.2f", $order->coupon_discount);
		}
		//plugin discount
		if(isset($order->discount) && $order->discount > 0){

			$custom .= "&discount_amount_cart=".sprintf("%.2f", $order->discount);
		}


		$path = JPluginHelper::getLayoutPath('mymuse', 'payment_authorizenet');
		@ob_start();
		include $path;
		$html = @ob_get_clean();
		
		return $html;
	}

	/**
	 * notify
	 * try the transaction at Authorize.net, return required responses, update orders and do mailouts
	 *
	 */
	function onMyMuseNotify($params, $Itemid = 1)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$data = $jinput->post->getArray();
		//$data['zip'] = 46203;
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nAuthorize.net notify PLUGIN\n";
	
		$result = array();
		$result['plugin'] 				= "payment_authorize.net";
		$result['myorder'] 				= 0; //must be >0 to trigger that it was this plugin
		$result['message_sent'] 		= 0; //must be >0 or tiggers error
		$result['message_received'] 	= 0; //must be >0 or tiggers error
		$result['order_found']			= 0; //must be >0 or tiggers error
		$result['order_verified'] 		= 0; //must be >0 or tiggers error
		$result['order_completed'] 		= 0; //must be >0 or tiggers error
		$result['order_number']			= 0; //must be >0 or tiggers error
		$result['order_id']				= 0; //must be >0 or tiggers error
		$result['payer_email']			= 0;
		$result['payment_status']		= 0;
		$result['txn_id']				= 0;
		$result['error']				= '';
		$result['rate'] 				= '';
		$result['fees'] 				= '';
		$result['description'] 			= '';
		
		
		if(!isset($_GET['pp']) || $_GET['pp'] != "anet"){
			//wasn't anet
			$debug .= "Was not Authorize.net. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
			return $result;
		}else{
			$debug .= "DATA comin in ".print_r($data, true);
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
		}
		$result['myorder'] = 1;

		//response = chargeCreditCard($data);
		$response =  $this->_myChargeCreditCard($data, $params);
		
		if ($response != null){
			
		}else{
			$app->enqueueMessage(JText::_('PLG_MYMUSE_AUTHORIZENET_NULL_RESPONSE'), 'error');
			$result['error'] 		= JText::_('PLG_MYMUSE_AUTHORIZENET_NULL_RESPONSE');
			$result['redirect']		= JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=anet';
			return $result;
		}
		
		if($params->get('my_debug')){
			$debug = "Authorise.net response\n";
			$debug .= print_r($response, true);
			MyMuseHelper::logMessage( $debug  );
		}
		
		if ( $response->approved == "1" ){
			if($params->get('my_debug')){
				$debug = "Authorise.net approved \n";
				MyMuseHelper::logMessage( $debug  );
			}
		}else{

			if($response->error != ''){
				$error_code = $response->response_reason_code;
				$error_text = $response->response_reason_text;

				if($params->get('my_debug')){
					$debug = "Authorise.net Charge Credit Card ERROR :  Invalid response \n";
					$debug .= "code = $error_code, text = $error_text \n";
					MyMuseHelper::logMessage( $debug  );
				}
				$app->enqueueMessage(JText::_('PLG_MYMUSE_AUTHORIZENET_INVALID_RESPONSE'), 'error');
				$app->enqueueMessage($error_code.' '.$error_text, 'error');
				$result['error'] 		= JText::_('PLG_MYMUSE_AUTHORIZENET_INVALID_RESPONSE').' '.$error_code.' '.$error_text;
				$result['redirect']		= JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=anet';
				return $result;
			}
			
			$app->enqueueMessage(JText::_('PLG_MYMUSE_AUTHORIZENET_INVALID_RESPONSE'), 'error');
			$result['error'] 		= JText::_('PLG_MYMUSE_AUTHORIZENET_INVALID_RESPONSE');
			$result['redirect']		= JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=anet';
			return $result;
		}

		$message_code = $response->response_reason_code;
		$message_description = $response->response_reason_text;
		
		$app->enqueueMessage(JText::_('PLG_MYMUSE_AUTHORIZENET_VALID_RESPONSE'), 'message');
		
		$c = explode('&',$_POST['custom']);
		foreach($c as $pair){
			if($pair){
				list($key,$val) = explode('=',$pair);
				$custom[$key] = $val;
			}
		}
		$result ['order_id'] = $data ['invoice_num'];
		$result ['payer_email'] = $data ['email'];
		$result ['user_email'] = $data ['email'];
		$result ['userid'] = $data ['cust_id'];
		
		$result ['payment_status'] = "Completed";
		$result ['txn_id'] = $response->transaction_id;
		$result ['amountin'] = $data ['amount'];
		//$result ['currency'] = $data ['mc_currency'];
		$result ['rate'] = @$data ['rate'];
		$result ['fees'] = @$data ['mc_fee'];
		$result ['description'] = $message_description;
		
		$result ['transaction_id'] = $result ['txn_id'];
		$result ['transaction_status'] = $result ['payment_status'];
		
		
		// order was verified!
		$date = date ( 'Y-m-d h:i:s' );
		$result ['order_verified'] = 1;
		$result ['message_sent'] = 1;
		$result ['message_received'] = 1;
		
		// $result['payment_status'] = "Completed";
		
		if ($params->get ( 'my_debug' )) {
			$debug = "$date  4. order VERIFIED at Authorize.net\n\n";
			MyMuseHelper::logMessage ( $debug );
		}
		
		// Get the Order Details from the database
		
		$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `id`='" . $result ['order_id'] . "'";
		$date = date ( 'Y-m-d h:i:s' );
		$debug = "$date  4.1 $query \n\n";
		
		$db->setQuery ( $query );
		if (! $this_order = $db->loadObject ()) {
			$debug .= "5. !!!!Error no order object: " . $db->_errorMsg . "\n\n";
			$debug .= "-------END-------";
			if ($params->get ( 'my_debug' )) {
				MyMuseHelper::logMessage ( $debug );
			}
			$result ['error'] = $debug;
			return $result;
		} else {
			// update the payment status
			$result ['order_found'] = 1;
			$result ['order_number'] = $this_order->order_number;
			if (preg_match ( "/Completed/", $result ['payment_status'] )) {
				$helper = new MyMuseHelper ();
				$helper->orderStatusUpdate ( $result ['order_id'], "C" );
				$date = date ( 'Y-m-d h:i:s' );
				$debug .= "$date 5. order COMPLETED at Authorize.net, update in DB\n\n";
				$result ['order_completed'] = 1;
			} else {
				// not completed, set order status to
				MyMuseHelper::orderStatusUpdate ( $result ['order_id'], "I" );
			}
		}
		$result['redirect']		= JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=anet';
		
		return $result;

		
		
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
	
		return JHTML::_('select.genericlist', $options, 'exp_date', 'class="input-medium"', 'value', 'text', '', 'exp_date');
	}
	
	
	private function _myChargeCreditCard($data, $params)
	{
	
		$sale = new AuthorizeNetAIM;
		$all_aim_fields = array("address","allow_partial_auth","amount",
				"auth_code","authentication_indicator", "bank_aba_code","bank_acct_name",
				"bank_acct_num","bank_acct_type","bank_check_number","bank_name",
				"card_code","card_num","cardholder_authentication_value","city","company",
				"country","cust_id","customer_ip","delim_char","delim_data","description",
				"duplicate_window","duty","echeck_type","email","email_customer",
				"encap_char","exp_date","fax","first_name","footer_email_receipt",
				"freight","header_email_receipt","invoice_num","last_name","line_item",
				"login","method","phone","po_num","recurring_billing","relay_response",
				"ship_to_address","ship_to_city","ship_to_company","ship_to_country",
				"ship_to_first_name","ship_to_last_name","ship_to_state","ship_to_zip",
				"split_tender_id","state","tax","tax_exempt","test_request","tran_key",
				"trans_id","type","version","zip");
		
		// set any fields that are set
		foreach($all_aim_fields as $field){
			if(isset($data[$field])){
				$sale->setField($field, $data[$field]);
			}
			
		}
		
		$sale->setCustomField('custom',$data['custom']);
		
		//addLineItem($item_id, $item_name, $item_description, $item_quantity, $item_unit_price, $item_taxable)
		for($i = 1; $i < $data['num_cart_items'] + 1; $i++){
			$data['ITEM_'.$i.'_NAME'] = substr($data['ITEM_'.$i.'_NAME'], 0,30 );
			$sale->addLineItem($data['ITEM_'.$i.'_ID'], $data['ITEM_'.$i.'_NAME'], $data['ITEM_'.$i.'_DESC'], $data['ITEM_'.$i.'_QUANT'], $data['ITEM_'.$i.'_PRICE'], $data['ITEM_'.$i.'_TAXABLE']);
		}
		$debug = print_r($sale->getLineItems(), TRUE);
		//if ($params->get ( 'my_debug' )) {
			MyMuseHelper::logMessage ( $debug );
		//}
		$response = $sale->authorizeAndCapture();

		return $response;
		
	}

	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "Payment Authorizenet:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		}
		return $email_msg;
	
	}
}
