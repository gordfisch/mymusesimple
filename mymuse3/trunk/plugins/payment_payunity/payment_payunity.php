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
			$parameters['SECURITY.SENDER'] = $this->params->get('test_sender');
			$parameters['USER.LOGIN'] = $this->params->get('test_user_login');
			$parameters['USER.PWD'] = $this->params->get('test_user_pwd');
			$parameters['TRANSACTION.CHANNEL'] = $this->params->get('test_channel');
			$parameters['TRANSACTION.MODE'] = "INTEGRATOR_TEST";
		}
		else
		{
			$url = "https://payunity.com/frontend/payment.prc";
			$parameters['SECURITY.SENDER'] = $this->params->get('sender');
			$parameters['USER.LOGIN'] = $this->params->get('user_login');
			$parameters['USER.PWD'] = $this->params->get('user_pwd');
			$parameters['TRANSACTION.CHANNEL'] = $this->params->get('channel');
			$parameters['TRANSACTION.MODE'] = "LIVE";
		
		}
		$parameters['REQUEST.VERSION'] = "1.0";
		$parameters['IDENTIFICATION.TRANSACTIONID'] = $order->id;
		$parameters['FRONTEND.ENABLED'] = "true";
		$parameters['FRONTEND.POPUP'] = "true";
		$parameters['FRONTEND.MODE'] = "DEFAULT";
		$parameters['FRONTEND.LANGUAGE'] = "en";
		$parameters['PAYMENT.CODE'] = "CC.DB";
		
		$parameters['FRONTEND.RESPONSE_URL'] = JURI::base()."index.php?option=com_mymuse&task=notify";
				
		$parameters['NAME.GIVEN'] = $shopper->first_name;
		$parameters['NAME.FAMILY'] = $shopper->last_name;
		$parameters['ADDRESS.STREET'] = $shopper->address1." ".$shopper->address2;
		$parameters['ADDRESS.ZIP'] = $shopper->postal_code;
		$parameters['ADDRESS.CITY'] = $shopper->city;
		$parameters['ADDRESS.COUNTRY'] = $shopper->country;
		$parameters['CONTACT.EMAIL'] = $shopper->email;
		$parameters['PRESENTATION.AMOUNT'] = sprintf("%.2f", $order->order_total);
		$parameters['PRESENTATION.CURRENCY'] = $store->currency;
		
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
			$temp=split("=",$temp,2);
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
				header("Location: ".JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=payunity&Itemid='.$Itemid);
			}
		}// there is a connection-problem to the ctpe server ... redirect to error page
		//(change the URL to YOUR error page)
		else
		{
		header("Location: ".JURI::base().'index.php?msg=error');
		}

	}
	
	/**
	 * onMyMuseNotify(
	 * catch the post from PayUnity, return required responses, update orders and do mailouts
	 *
	 */
	function onMyMuseNotify($params)
	{
		$mainframe 	= JFactory::getApplication();
	
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nPayUnity notify PLUGIN\n";
		$debug .= print_pre($_POST);
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
	
		$result = array();
		$result['plugin'] 				= "payment_paypal";
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
		
		return $result;
		
	}
}
