<?php
/**
 * @package		mymuse
 * @copyright	Copyright (c)2010-2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die();

$Mymuseinclude = include_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/mymuse.php';
if(!$Mymuseinclude) { unset($Mymuseinclude); return; } else { unset($Mymuseinclude); }

class plgMyMusePayment_Paypalproexpress extends JPlugin
{
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	public function __construct(&$subject, $config = array())
	{
		if(!defined('DS')){
			define('DS',DIRECTORY_SEPARATOR);
		}
			
		if(!defined('MYMUSE_ADMIN_PATH')){
			define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
		}
		
		require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
		
		$config = array_merge($config, array(
			'pppe'		=> 'paypalproexpress'
		));

		parent::__construct($subject, $config);
	}

	/**
	 * PayPalProExpress Payment form
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
	
		$app = JFactory::getApplication();
		if($params->get('my_debug')){
			$debug = "#####################\nPayPalPro Express PLUGIN onBeforeMyMusePayment\n";
			//$debug .= print_r($order,true);
			MyMuseHelper::logMessage( $debug  );
		}
		

		$callbackUrl = JURI::root().'index.php?option=com_mymuse&task=notify&orderid='.$order->id;
		$cancelUrl = JURI::root().'index.php?option=com_mymuse&task=paycancel';
		$requestData = (object)array(
			'METHOD'							=> 'SetExpressCheckout',
			'USER'								=> $this->getMerchantUsername(),
			'PWD'								=> $this->getMerchantPassword(),
			'SIGNATURE'							=> $this->getMerchantSignature(),
			'VERSION'							=> '124.0',
			'RETURNURL'							=> $callbackUrl,
			'CANCELURL'							=> $cancelUrl,
			'PAYMENTREQUEST_0_AMT'				=> sprintf('%.2f',$order->order_total),
			'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'SALE',
			'PAYMENTREQUEST_0_CURRENCYCODE'		=> strtoupper($store->currency),
			'PAYMENTREQUEST_0_TAXAMT'			=> $order->tax_total,
			'PAYMENTREQUEST_0_ITEMAMT'			=> $order->order_subtotal,
			'BUTTONSOURCE'						=> 'Arboreta_SP'
		);

		$j = 0;
		$requestData->ITEMS = 0;
		for ($i=0;$i<$order->idx;$i++) {
			if(isset($order->items[$i]->title) && $order->items[$i]->title != ''){
				$item_name = 'L_PAYMENTREQUEST_0_NAME'. $i;
				$quant_name = 'L_PAYMENTREQUEST_0_QTY'. $i;
				$amount_name = 'L_PAYMENTREQUEST_0_AMT'. $i;
		
				$requestData->$item_name = $order->items[$i]->title;
				$requestData->$quant_name = $order->items[$i]->product_quantity;
				$requestData->$amount_name = $order->items[$i]->product_item_price;
				$j++;
			}
			
		}
		//coupon discount
		if(isset($order->coupon_discount) && $order->coupon_discount > 0){
		
			$item_name = 'L_PAYMENTREQUEST_0_NAME'. $i;
			$quant_name = 'L_PAYMENTREQUEST_0_QTY'. $i;
			$amount_name = 'L_PAYMENTREQUEST_0_AMT'. $i;
			$requestData->$item_name = JText::_('MYMUSE_DISCOUNT');
			$requestData->$quant_name = 1;
			$requestData->$amount_name = -sprintf("%01.2f", $order->coupon_discount);
			$j++;
			$i++;
		}
			
		//plugin discount
		if(isset($order->discount) && $order->discount > 0){
		
			$item_name = 'L_PAYMENTREQUEST_0_NAME'. $i;
			$quant_name = 'L_PAYMENTREQUEST_0_QTY'. $i;
			$amount_name = 'L_PAYMENTREQUEST_0_AMT'. $i;
			$requestData->$item_name = JText::_('MYMUSE_DISCOUNT');
			$requestData->$quant_name = 1;
			$requestData->$amount_name = -sprintf("%01.2f", $order->discount);
			$j++;
			$i++;
		}
		//shopper_group_discount is figured in price for each item

			
		$requestData->ITEMS = $j;
		
		//if(isset($order->coupon_discount)){
		//	$custom .= "&coupon_id=".$order->coupon_id;
		//	$requestData->discount_amount_cart = sprintf("%.2f", $order->coupon_discount);
		//}
		//$requestData->CUSTOM = $custom;
		
		if($params->get('my_use_shipping') && isset($order->order_shipping->cost) && $order->order_shipping->cost > 0){

			$requestData->PAYMENTREQUEST_0_SHIPPINGAMT 		= sprintf("%01.2f", $order->order_shipping->cost);
		
		}
		
		
		if($params->get('my_debug')){
			$debug = "\nrequestData\n";
			$debug .= print_r($requestData,true);
			MyMuseHelper::logMessage( $debug  );
		}
		$requestQuery = http_build_query($requestData);

		if(!$responseQuery = MyMuseHelper::myCurl($this->getPaymentURL (), $requestQuery, $params)){
			return false;
		}

		// Payment Response
		$responseData = array();
		$requestData = array();
		parse_str($responseQuery, $responseData);
		
		
		if($params->get('my_debug')){
			$debug = "\nResponse Data \n";
			$debug .= print_r($responseData,true);
			MyMuseHelper::logMessage( $debug  );
		}
		
		
		if(preg_match('/^SUCCESS/', strtoupper($responseData['ACK']))) {
			$data['URL'] = $this->getPaypalURL($responseData['TOKEN']);
		} else {
			$jinput = JFactory::getApplication()->input;
			$msg = isset($responseData['L_LONGMESSAGE0'])? $responseData['L_LONGMESSAGE0'] : "Did not get a response from PayPal";
			$error_url = 'index.php?option=com_mymuse&view=cart&layout=cart&Itemid='.$Itemid;
			$error_url = JRoute::_($error_url,false);
			JFactory::getApplication()->redirect($error_url,$msg,'error');
		}
		
		
		$path = JPluginHelper::getLayoutPath('mymuse', 'payment_paypalproexpress');
		@ob_start();
		include $path;
		$html = @ob_get_clean();

		return $html;
	}


	public function onMyMuseNotify($params, $Itemid = 1)
	{

		//http://test.joomlamymuse.com/index.php?option=com_mymuse&task=notify&
		//mode=init&token=EC-9XF8801684577273P&PayerID=DBYA4BH44DMTQ
		$jinput = JFactory::getApplication()->input;
		$data = $jinput->post->getArray();
		$get = $jinput->get->getArray();
		$data = array_merge($data, $get);
		$db	= JFactory::getDBO();
		
		if($params->get('my_debug')){
			$date = date('Y-m-d h:i:s');
			$debug = "$date #####################\nPayPalProExpress onMyMuseNotify PLUGIN\n";
			$debug .= "Incoming data = \n".print_r($data,true);
			MyMuseHelper::logMessage( $debug  );
		}
		
		if(isset($data['mc_gross'])) {
			return $this->IPNCallback($data, $params);
		} else {
			return $this->formCallback($data, $params);
		}
	}

	private function formCallback($data, $params)
	{
		JLoader::import('joomla.utilities.date');
		$isValid = true;
		$db	= JFactory::getDBO();
		
		$result = array ();
		$result ['plugin'] = "paypalproexpress";
		$result ['myorder'] = 0; // must be >0 to trigger that it was this plugin
		$result ['message_sent'] = 0; // must be >0 or tiggers error
		$result ['message_received'] = 0; // must be >0 or tiggers error
		$result ['order_found'] = 0; // must be >0 or tiggers error
		$result ['order_verified'] = 0; // must be >0 or tiggers error
		$result ['order_completed'] = 0; // must be >0 or tiggers error
		$result ['order_number'] = 0; // must be >0 or tiggers error
		$result ['order_id'] = $data['orderid']; // must be >0 or tiggers error
		$result ['payer_email'] = 0;
		
		$result ['error'] = '';
		$result ['redirect'] = '';
		
		$error_url = 'index.php?option=com_mymuse&view=cart&layout=cart';
		$error_url = JRoute::_($error_url,false);
		
		if($params->get('my_debug')){
			$date = date('Y-m-d h:i:s');
			$debug = "$date \n#####################\nPayPalProExpress notify FORMCALLBACK\n";
			MyMuseHelper::logMessage( $debug  );
		}
		
		if(isset($data['orderid'])){
			$result ['myorder'] = 1;
			$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `id`='".$data['orderid']."'";
			$db->setQuery($query);
			$order = $db->loadObject();
			$result ['order_number'] = $order->order_number;
			$notes = $order->notes;	
		}else{
			$result ['error'] = "No Order ID";
			$debug = $result ['error']."\n";
			MyMuseHelper::logMessage( $debug  );

			$result ['redirect'] = $error_url;
			return $result;
		}


		if($isValid && isset($data['token']) && isset($data['PayerID']) && isset($data['orderid']) ) {
			require_once( JPATH_COMPONENT.DS.'mymuse.class.php');
			$MyMuseCheckout 	= MyMuse::getObject('checkout','helpers');
			$order 				= $MyMuseCheckout->getOrder($data['orderid']);
			//print_r($order);
			$result ['order_found'] = 1;
			
			$store = MyMuseHelper::getStore();
				
			$total = $order->order_subtotal + $order->order_shipping->cost + $order->tax_total;
			$requestData = (object)array(
					'METHOD'							=> 'DoExpressCheckoutPayment',
					'USER'								=> $this->getMerchantUsername(),
					'PWD'								=> $this->getMerchantPassword(),
					'SIGNATURE'							=> $this->getMerchantSignature(),
					'VERSION'							=> '124.0',
					'TOKEN'								=> $data['token'],
					'PAYERID'							=> $data['PayerID'],
					'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'Sale',
					'PAYMENTREQUEST_0_AMT'				=> sprintf('%.2f',$order->order_total),
					'PAYMENTREQUEST_0_CURRENCYCODE'		=> strtoupper($store->currency),
					'PAYMENTREQUEST_0_INVNUM'			=> $data['orderid'],
					'PAYMENTREQUEST_0_DESC'				=> $store->title,
					'IPADDRESS'							=> $_SERVER['REMOTE_ADDR'],
					'BUTTONSOURCE'						=> 'Arboreta_SP'
			);
			
			
			if($params->get('my_debug')){
				$debug = "FormCallBack requestData = \n".print_r($requestData,true);			
				MyMuseHelper::logMessage( $debug  );
			}
		
			$requestQuery = http_build_query($requestData);
			
			if(!$responseQuery = MyMuseHelper::myCurl($this->getPaymentURL(), $requestQuery, $params)){
				// mycurl will record any errors
				$result ['order_verified'] = 0;

				return false;
			}
			$result ['message_sent'] =1;
			$result ['message_received'] = 1;
			
			// Payment Response
			$responseData = array();
			parse_str($responseQuery, $responseData);
			
			if($params->get('my_debug')){
				$debug = "FormCallBack responseData = \n".print_r($responseData,true);
				MyMuseHelper::logMessage( $debug  );
			}
			
			//errors
			
			if(! preg_match('/^SUCCESS/', strtoupper($responseData['ACK']))) {
				$isValid = false;

				JFactory::getApplication()->redirect($error_url,$responseData['L_LONGMESSAGE0'],'error');
				if($params->get('my_debug')){
					$debug = "formCallback ERROR: ACK != SUCCESS: ".$responseData['L_LONGMESSAGE0'];
					MyMuseHelper::logMessage( $debug  );
				}
				return false;
			} else if(! preg_match('/^SUCCESS/', strtoupper($responseData['PAYMENTINFO_0_ACK']))) {
				$isValid = false;
				JFactory::getApplication()->redirect($error_url,$responseData['L_LONGMESSAGE0'],'error');
				if($params->get('my_debug')){
					$debug = "formCallback ERROR: PAYMENTINFO_0_ACK' != SUCCESS: ".$responseData['L_LONGMESSAGE0'];
					MyMuseHelper::logMessage( $debug  );
				}
				return false;
			}
			$result ['order_verified'] = 1;
			$result ['payment_status'] = $responseData['PAYMENTINFO_0_PAYMENTSTATUS'];
			$result ['txn_id'] = $responseData['PAYMENTINFO_0_TRANSACTIONID'];
			//completed
			if($responseData['PAYMENTINFO_0_PAYMENTSTATUS'] == "Completed" ){
				$result ['order_completed'] = 1;
				MyMuseHelper::orderStatusUpdate($data['orderid'] , "C");
			}else{
				$result ['error'] = $responseData['PAYMENTINFO_0_REASONCODE']." ".$responseData['PAYMENTINFO_0_PENDINGREASON'];
				$result ['redirect'] = $error_url;
				JFactory::getApplication()->redirect($error_url,$result ['error'],'error');
				return false;
			}


		}


		// Fraud attempt? Do nothing more!
			if(!$isValid ){
				$msg = "Payment Failed: ".$result ['error'];
				$result ['redirect'] = $error_url;
				if($params->get('my_debug')){
					$debug = "formCallback: $msg \n";
					MyMuseHelper::logMessage( $debug  );
				}
				JFactory::getApplication()->redirect($error_url,$result ['error'],'error');
				return false;
			}
			
			$path = JURI::root(true);
			$thankyouUrl = JRoute::_('index.php?option=com_mymuse&task=thankyou&view=cart&pp=paypalexpresscheckout&orderid='.$orderid.'&Itemid='.$Itemid, false);
			$thankyouUrl = JURI::root().preg_replace("#$path/#",'',$thankyouUrl);
			//JFactory::getApplication()->redirect($thankyouUrl);
			$result ['redirect'] = $thankyouUrl;
			return $result;
	}

	private function IPNCallback($data, $params)
	{
		JLoader::import('joomla.utilities.date');
		
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');

				// paypal IPN coming in
			$debug = "#####################\nPayPalProExpress PLUGIN IPN Response\n";
			
			$result = array ();
			$result ['plugin'] = "paypalproexpress";
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
			$isValid = $this->isValidIPN ( $data, $params );
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
			$result['order_id'] 			= $data['invoice'];
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
				
				
			}
			if (! $isValid) {
				$result ['error'] = "Transaction type " . $data ['txn_type'] . " can't be processed by this payment plugin.";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $result ['error'] );
				}
				return $result;
			}
			
			//order was verified!
			$result['order_verified'] = 1;
			//$result['payment_status'] = "Completed";
			
			if($params->get('my_debug')){
				$date = date('Y-m-d h:i:s');
				$debug = "$date  4. order VERIFIED at PayPal\n\n";
				$debug .= print_r($result, true);
				MyMuseHelper::logMessage( $debug  );
			}

			
			
			// Get the Order Details from the database
			
			$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `id`='".$result['order_id']."'";
			
			
			if($params->get('my_debug')){
				$date = date('Y-m-d h:i:s');
				$debug = "$date  4.1 $query \n\n";
				MyMuseHelper::logMessage( $debug  );
			}
			$db->setQuery($query);
			if(!$this_order = $db->loadObject()){
				
				if($params->get('my_debug')){
					$debug = "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";
					$debug .= "-------END-------";
					MyMuseHelper::logMessage( $debug  );
				}
				$result['error'] = $debug;
				return $result;
			}else{
				// update the payment status
				$result['order_found']  = 1;
				$result['order_id'] 	= $this_order->id;
				$result['order_number'] 	= $this_order->order_number;
				if (preg_match ("/Completed/", $result['payment_status'])) {
					$helper = new MyMuseHelper;
					$helper->orderStatusUpdate($result['order_id'] , "C");
					$date = date('Y-m-d h:i:s');
					if($params->get('my_debug')){
						$debug = "$date 5. order COMPLETED at PayPal, update in DB\n\n";
						MyMuseHelper::logMessage( $debug  );
					}
					$result['order_completed'] = 1;
				}else{
					// not completed, set order status to
					MyMuseHelper::orderStatusUpdate($result['order_id'] , "I");
				}
			}
				
			
			return $result;

	}

	/**
	 * Validates the incoming data against PayPal's IPN to make sure this is not a
	 * fraudelent request.
	 */
	private function isValidIPN($data, &$params)
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
			if($params->get('my_debug')){
				$debug = "ERROR! fsockopen: ".$errno."\n". $errstr."\n\n";
				MyMuseHelper::logMessage( $debug  );
			}
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

	private function getPaypalURL($token)
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
		} else {
			return 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
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
	
	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "Payment PayPalPro:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		}
		return $email_msg;
	
	}
}