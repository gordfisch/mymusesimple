<?php
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

/**
 * MyMuse Payment Payunity plugin
 *
 * @package 		MyMuse
 * @subpackage	mymuse
*/
class plgMymusePayment_Payunity extends JPlugin
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
	function plgMyMusePayment_Payunity(&$subject, $config)  {
		parent::__construct($subject, $config);

	}

	/**
	 * PayPal Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		
		$mainframe 	= JFactory::getApplication();
		$db			= JFactory::getDBO();
		
		$uri = JURI::getInstance();
		$lang = $uri->getVar('lang', $this->params->get('default_lang'));

		
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nPayUnity PLUGIN onBeforeMyMusePayment\n";
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		
		if(isset($shopper->profile['country'])){
			// PayUnity wants the country_2_code
			$query = "SELECT country_2_code from #__mymuse_country WHERE country_3_code='".$shopper->profile['country']."'";
			$db->setQuery($query);
			$shopper->country = $db->loadResult();
		}else{
			$shopper->country = '';
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
			if($shopper->last_name = ""){
				$shopper->last_name = $shopper->first_name;
			}
		}
		
		
		//TEST ENVIRONMENT??
		if($this->params->get('test'))
		{
			$url = "https://test.payunity.com/frontend/payment.prc";
			$parameters['SECURITY.SENDER'] 		= $this->params->get('test_sender');
			$parameters['USER.LOGIN'] 			= $this->params->get('test_user_login');
			$parameters['USER.PWD'] 			= $this->params->get('test_user_pwd');
			$parameters['TRANSACTION.CHANNEL'] 	= $this->params->get('test_channel');
			$parameters['TRANSACTION.MODE'] 	= "INTEGRATOR_TEST";
		}
		else
		{
			$url = "https://payunity.com/frontend/payment.prc";
			$parameters['SECURITY.SENDER'] 		= $this->params->get('sender');
			$parameters['USER.LOGIN'] 			= $this->params->get('user_login');
			$parameters['USER.PWD'] 			= $this->params->get('user_pwd');
			$parameters['TRANSACTION.CHANNEL'] 	= $this->params->get('channel');
			$parameters['TRANSACTION.MODE'] 	= "LIVE";
		
		}
		$parameters['REQUEST.VERSION'] 				= "1.0";
		$parameters['IDENTIFICATION.TRANSACTIONID'] = $order->id;
		$parameters['FRONTEND.ENABLED'] 			= "true";
		$parameters['FRONTEND.POPUP'] 				= "false";
		$parameters['FRONTEND.MODE'] 				= "DEFAULT";
		$parameters['FRONTEND.LANGUAGE'] 			= $lang;
		$parameters['FRONTEND.SHOP_NAME'] 			= $store->title;
		$parameters['PAYMENT.CODE'] 				= "CC.DB";
		
		$parameters['FRONTEND.RESPONSE_URL'] 		= JURI::base()."index.php?option=com_mymuse&task=notify";
				
		$parameters['NAME.GIVEN'] 					= $shopper->first_name;
		$parameters['NAME.FAMILY'] 					= $shopper->last_name;
		$parameters['ADDRESS.STREET'] 				= $shopper->address1." ".$shopper->address2;
		$parameters['ADDRESS.ZIP'] 					= $shopper->postal_code;
		$parameters['ADDRESS.CITY'] 				= $shopper->city;
		$parameters['ADDRESS.COUNTRY'] 				= $shopper->country;
		$parameters['CONTACT.EMAIL'] 				= $shopper->email;
		$parameters['PRESENTATION.AMOUNT'] 			= sprintf("%.2f", $order->order_total);
		$parameters['PRESENTATION.CURRENCY'] 		= $store->currency;
		
		
		if($this->params->get('css_file', 0)){
			$parameters["FRONTEND.CSS_PATH"] = $this->params->get('css_file');
		}
		if($this->params->get('js_file', 0)){
			$parameters["FRONTEND.JSCRIPT_PATH"] = $this->params->get('js_file');
		}
		
		$debug = "parameters\n".print_r($parameters, TRUE);
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		//building the postparameter string to send into the WPF
		foreach (array_keys($parameters) AS $key)
		{
			$$key .= $parameters[$key];
			$$key = urlencode($$key);
			$$key .= "&";
			$var = strtoupper($key);
			$value = $$key;
			$result .= "$var=$value";
		}
		$strPOST = stripslashes($result);
		//open the request url for the Web Payment Frontend
		$cpt = curl_init();
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
		));

		curl_setopt($cpt, CURLOPT_URL, $url);
		curl_setopt($cpt, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($cpt, CURLOPT_USERAGENT, "php ctpepost");
		curl_setopt($cpt, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cpt, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($cpt, CURLOPT_POST, 1);
		curl_setopt($cpt, CURLOPT_POSTFIELDS, $strPOST);
		$curlresultURL = curl_exec($cpt);
		$curlerror = curl_error($cpt);
		$curlinfo = curl_getinfo($cpt);
		curl_close($cpt);
		// here you can get all variables returned from the ctpe server (see post
		//integration transactions documentation for help)
		//print "$curlresultURL";
		// parse results
		
		$debug = "curlResultURL\n".$curlresultURL."\n";
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		
		$r_arr=explode("&",$curlresultURL);
		foreach($r_arr AS $buf)
		{
			$temp=urldecode($buf);
			$temp=explode("=",$temp,2);
			$postatt=$temp[0];
			$postvar=$temp[1];
			$returnvalue[$postatt]=$postvar;
			//print "<br>var: $postatt - value: $postvar<br>";
		}
		
		$debug = print_r($returnvalue, true);
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		$processingresult=$returnvalue['POST.VALIDATION'];
		$redirectURL=$returnvalue['FRONTEND.REDIRECT_URL'];
		// everything ok, redirect to the WPF
		if ($processingresult=="ACK")
		{
			if (strstr($redirectURL,"http")) 
				// redirect url is returned ==> verything ok
			{
					header("Location: $redirectURL");
			}
			else // error-code is returned ... failure
			{
			
				//header("Location: ".JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=payunity&Itemid='.$Itemid);
				$msg = JText::_('ERROR')." code:".$processingresult;
				JFactory::getApplication()->enqueueMessage($msg, 'warning');
				return;
			}
		}// there is a connection-problem to the ctpe server ... redirect to error page
		//(change the URL to YOUR error page)
		else
		{	
			$msg = JText::_('ERROR')." code:".$processingresult;
			JFactory::getApplication()->enqueueMessage($msg, 'warning');
			return;
		}

	}
	
	/**
	 * onMyMuseNotify(
	 * catch the post from PayUnity, return required responses, update orders and do mailouts
	 *
	 */
	function onMyMuseNotify($params)
	{
	
		if(!defined('DS')){
			define('DS',DIRECTORY_SEPARATOR);
		}
		
		if(!defined('MYMUSE_ADMIN_PATH')){
			define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
		}
		if(!defined('MYMUSE_PATH')){
			define('MYMUSE_PATH',JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS);
		}
		require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
		
		$Helper = new MyMuseHelper;
		
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$jinput = JFactory::getApplication()->input;
		$data = $jinput->post->getArray();
		
		
		$debug = "#####################\nPayUnity notify PLUGIN\n";
		if(!isset($_POST['PROCESSING_RESULT'])){
			//wasn't payunity
			$debug .= "Was not PayUnity. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}else{
			if($params->get('my_debug')){
				$debug .= "1. POSt\N".print_r($data, true);
        		MyMuseHelper::logMessage( $debug  );
  			}
		}
		
	
		$result = array();
		$result['plugin'] 				= "payment_payunity";
		$result['myorder'] 				= 1; //must be >0 to trigger that it was this plugin
		$result['message_sent'] 		= 1; //must be >0 or tiggers error
		$result['message_received'] 	= 1; //must be >0 or tiggers error
		$result['order_found']			= 0; //must be >0 or tiggers error
		$result['order_verified'] 		= 0; //must be >0 or tiggers error
		$result['order_completed'] 		= 0; //must be >0 or tiggers error
		$result['order_number']			= 0; //must be >0 or tiggers error
		$result['order_id']				= 0; //must be >0 or tiggers error
		$result['payer_email']			= 0;
		$result['payment_status']		= 0;
		$result['txn_id']				= 0;
		$result['error']				= '';
		
		
		if(!isset($data['PROCESSING_RESULT'])){
			$debug = "2. !!!!Error No processing result: \n\n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
			$result['error'] = "No processing result";
			return $result;
		}
		$result['payment_status'] = $data['PROCESSING_RESULT'];
		
		if(!isset($data['IDENTIFICATION_TRANSACTIONID'])){
			$debug = "2. !!!!Error No order id: \n\n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
			$result['error'] = "No order id";
			return $result;
			
		}
		$result['order_id'] = $data['IDENTIFICATION_TRANSACTIONID'];

		// Get the Order Details from the database
		$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `id`='".$result['order_id']."'";
		$date = date('Y-m-d h:i:s');
		$debug = "$date  2. $query \n\n";
		
		$db->setQuery($query);
		if(!$this_order = $db->loadObject()){
			$debug .= "3. !!!!Error no order object: ".$db->_errorMsg."\n\n";
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
			$result['order_number'] = $this_order->order_number;
			if (preg_match ("/ACK/", $result['payment_status'])) {
				$Helper->orderStatusUpdate($result['order_id'] , "C");
				$date = date('Y-m-d h:i:s');
				$debug .= "$date 4. order COMPLETED at PayUnity, update in DB\n\n";
				$result['order_completed'] = 1;
				$result['order_verified'] = 1;
			}else{
				// not completed, set order status to
				$Helper->orderStatusUpdate($result['order_id'] , "I");
			}
		}
		
		$result['payment_status'] 		= $data['PROCESSING_RESULT'];
		$result['amountin'] 			= $data['CLEARING_AMOUNT'];
		$result['currency'] 			= $data['CLEARING_CURRENCY'];
		$result['rate'] 				= $data['CLEARING_FXRATE'];
		$result['txn_id'] 				= $data['IDENTIFICATION_UNIQUEID'];
		$result['transaction_id'] 		= $data['IDENTIFICATION_UNIQUEID'];
		$result['transaction_status'] 	= $data['PROCESSING_RESULT'];
		$result['description'] 			= $data['PROCESSING_RETURN'];
		$result['fees']					= '';
		
		$result['payer_email'] 			= $data['CONTACT_EMAIL'];
		
		$date = date('Y-m-d h:i:s');
		$debug .= "$date Finished talking to PayUnity \n\n";
		$debug .= "-------END PLUGIN-------";
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		
		return $result;
		
	}
}
