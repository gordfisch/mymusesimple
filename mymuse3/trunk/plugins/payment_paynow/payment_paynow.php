<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2016 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

/**
* MyMuse PaymnetPaynow plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Paynow extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	var $id = '';
	var $IntegrationKey = '';
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	function plgMyMusePayment_Paynow(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		if(!defined('DS')){
			define('DS',DIRECTORY_SEPARATOR);
		}
			
		if(!defined('MYMUSE_ADMIN_PATH')){
			define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
		}
		
		require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
		
		//testing?
		if($this->params->get('my_paypal_sandbox'))
		{
			$this->id = $this->params->get('my_paynow_sandbox_integration_id');
			$this->IntegrationKey = $this->params->get('my_paynow_sandbox_integration_key');
		}
		else
		{
			$this->id = $this->params->get('my_paynow_integration_id');
			$this->IntegrationKey = $this->params->get('my_paynow_integration_key');
		}
		
		$config = array_merge($config, array(
				'pn'		=> 'paynow'
		));
		
		parent::__construct($subject, $config);
	}
	
	//Integration ID: 2138
	//Integration Key: 186a8d6e-8d91-4924-93e7-3312c2ea5ef6
	
	
	

	/**
	 * PayNow initiate and redirect
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{

		$session = JFactory::getSession();
		$paynow_process = $session->get("paynow_process",0);
		
		if(!$paynow_process){
			
			$session->set("paynow_process",'1');
			// make a form and return
			$string = '<form action="index.php">
					<input type="hidden" name="option" value="com_mymuse">
					<input type="hidden" name="task" value="confirm">
					<input type="hidden" name="layout" value="cart">
					<input type="hidden" name="view" value="cart">
					<div id="paynow_form" class="pull-right">
					<button class="button uk-button shopper-info" type="submit">'.JText::_('MYMUSE_PAY_AT_PAYNOW').'</button>
					</div>
					</form>
					';
			return $string;
			
			
			
		}
		
		$app = JFactory::getApplication();
		if($params->get('my_debug')){
			$debug = "#####################\nPayNow PLUGIN onBeforeMyMusePayment\n";
			MyMuseHelper::logMessage( $debug  );
		}
		
		
		$additionalinfo = '';
		
		$shopper->first_name 	= isset($shopper->profile['first_name'])? $shopper->profile['first_name'] : '';
		$shopper->last_name 	= isset($shopper->profile['last_name'])? $shopper->profile['last_name'] : '';

		if(!$shopper->first_name){
			@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
			if($shopper->last_name = ""){
				$shopper->last_name = $shopper->first_name;
			}
		}
		$reference = $shopper->first_name.' '.$shopper->last_name.':'.$order->id;
		
		$requestData = array(
			'id' => $this->id,
			'reference'		=> $reference,
			'amount'		=> sprintf('%.2f',$order->order_total),
			'additionalinfo'=> $additionalinfo,
			'returnurl'		=> JURI::root().'index.php?option=com_mymuse&task=thankyou&view=cart&pp=paynow&st=Completed&Itemid='.$Itemid,
			'resulturl'		=> JURI::root().'index.php?option=com_mymuse&task=notify&orderid='.$order->id,
			'authemail'		=> $shopper->email,
			'status'		=> 'Message'
				);
		$requestData['hash'] = $this->CreateHash($requestData, $this->IntegrationKey);
		
		if($params->get('my_debug')){
			$debug = "\nrequestData\n";
			$debug .= print_r($requestData,true);
			MyMuseHelper::logMessage( $debug  );
		}
		$requestQuery = http_build_query($requestData);
		
		if(!$responseQuery = $this->myCurl('https://www.paynow.co.zw/interface/initiatetransaction', $requestQuery, $params)){
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
		
		if($responseData['status'] == "Error") {
			$app->enqueueMessage($responseData['Error'], 'error');
			return false;
		}elseif($responseData['status'] == "Ok") {
			$this->pollurl = $responseData['pollurl'];
			// check hash
			if($responseData['hash'] != $this->CreateHash($responseData, $this->IntegrationKey)){
				if($params->get('my_debug')){
					$debug = "\nHash does not Match \n";
					$debug .= $responseData['hash']."\n";
					$debug .= $this->CreateHash($responseData, $this->IntegrationKey)."\n";
					MyMuseHelper::logMessage( $debug  );
				}
				$app->enqueueMessage("Signature does not match", 'error');
				return false;
			}
			//redirect browser
			$app->redirect($responseData['browserurl'],$msg,'error');
		}
			
		

	}

	
	
	/**
	 * notify
	 * catch the IPN post from PayPal, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$jinput = JFactory::getApplication()->input;

		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nPayNow notify PLUGIN\n";

		$result = array();
		$result['plugin'] 				= "payment_paynow";
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
		$result['description'] 		= '';

		if(!isset($_POST['paynowreference'])){
			//wasn't paynow
			$debug .= "Was not Paynow. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}else{
			if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
		}
		$result['myorder'] = 1;
		
		// respond to Paynow
        //header("HTTP/1.1 200 OK");
        
		
		$order_id 			= $jinput->get('orderid');
		
		$result['order_id'] 			= $order_id;
		$result['txn_id'] 				= $_POST['paynowreference'];
		$result['amountin'] 			= $_POST['amount'];
		$result['currency'] 			= 'USD';
		$result['payment_status'] 		= $_POST['status'];
		$result['hash'] 				= @$_POST['hash'];
		$result['transaction_id'] 		= $_POST['paynowreference'];
		$result['transaction_status'] 	= $_POST['status'];
		$result['message_sent']			= 1;
		$result['message_received']		= 1;

		$debug = $date. "\nPOST\n".print_r($_POST, true)."\n";
		$debug .= "Result\n".print_r($result, true)."\n";
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		
		if ($_POST['status'] == "Paid") {
			//check hash
			if($result['hash'] != $this->CreateHash($_POST, $this->IntegrationKey)){
				if($params->get('my_debug')){
					$debug = "\nHash does not Match \n";
					$debug .= "Theirs: ".$result['hash']."\n";
					$debug .= "Mine: ".$this->CreateHash($_POST, $this->IntegrationKey)."\n";
					MyMuseHelper::logMessage( $debug  );
				}
				return $result;
			}
			// order was paid!
			$result['order_verified'] = 1;
			// Get the Order Details from the database
			$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `id`='" . $result['order_id'] . "'";
			$debug = "$date  4.1 $query \n\n";
			if ($params->get( 'my_debug' )) {
				MyMuseHelper::logMessage ( $debug );
			}
			
			$db->setQuery( $query );
			
			if (! $this_order = $db->loadObject ()) {
				$debug .= "$date 5. !!!!Error no order object: " . $db->_errorMsg . "\n\n";
				$debug .= "-------END-------";
				if ($params->get( 'my_debug' )) {
					MyMuseHelper::logMessage ( $debug );
				}
				$result['error'] = "Order not found";
				return $result;
			} else {
				// update the payment status
				$result['order_found'] = 1;
				$result['order_id'] = $this_order->id;
				$result['order_number'] = $this_order->order_number;
				
				$MyMuseHelper = new MyMuseHelper ();
				$MyMuseHelper->orderStatusUpdate ( $result['order_id'], "C" );
				$debug .= "$date 5. order COMPLETED at Paynow, update in DB\n\n";
				$result['order_completed'] = 1;
			}
			if ($params->get( 'my_debug' )) {
				MyMuseHelper::logMessage ( $debug );
			}
		} else {
			// not paid
			$debug .= "$date 4.2 Not PAID at Paynow\n\n";
			$debug .= "Order status: " . $result ['payment_status'] . "\n";
			$debug .= "-------END PLUGIN-------";
			if ($params->get( 'my_debug' )) {
				MyMuseHelper::logMessage ( $debug );
			}
			$result['error'] = $_POST['status'];
			return $result;
		}
		
		// get user
		$q = "SELECT * from #__users as u
  						WHERE
  						u.id='" . $this_order->user_id . "'";

		$db->setQuery( $q );

		if (!$user = $db->loadObject()) {
			$debug = "4.3 We do not have a user! Must exit. ";
			$debug .= "\n $q \nEmails were \npayer " . $result ['payer_email'] . " user " . $result ['user_email'] . "\n";
			$debug .= "-------END-------";
			if ($params->get( 'my_debug' )) {
				MyMuseHelper::logMessage ( $debug );
			}
			$result['error'] = "No user found";
			return $result;
		}

		if ($params->get( 'my_registration' ) == "no_reg" && $user->username == "buyer") {
			// it's the guest user
			$fields = MyMuseHelper::getNoRegFields ();
			
			$registry = new JRegistry ();
			$registry->loadString ( $this_order->notes );
			$notes = $registry->toArray ();
			
			$result['payer_email'] = $notes ['email'];
			$result['user_email'] = $notes ['email'];
			$result['userid'] = $user->id;
		} else {
			$result['payer_email'] = $user->email;
			$result['user_email'] = $user->email;
			$result['userid'] = $user->id;
		}
        	
        $debug = "$date Finished talking to Paynow \n\n";
		$debug .= "-------END PLUGIN-------";
  		if($params->get('my_debug')){
        	MyMuseHelper::logMessage( $debug  );
  		}
        return $result;

	}
	
	private function CreateHash($values, $IntegrationKey){

		$string = "";
		foreach($values as $key=>$value) {
			if( strtoupper($key) != "HASH" ){
				$string .= $value;
			}
		}
		$string .= $IntegrationKey;
		$hash = hash("sha512", $string);
		return strtoupper($hash);
	}
	
	/**
	 * myCurl
	 *
	 * @param $url string
	 * @param $requestQuery string
	 * @param $params array
	 * @return mixed boolean or string
	 */
	static function myCurl($url, $requestQuery, &$params)
	{
		$app = JFactory::getApplication();
		$curlOptions = array (
				CURLOPT_SSLVERSION => 6,
				CURLOPT_URL => $url,
				CURLOPT_VERBOSE => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $requestQuery
		);
	
		$ch = curl_init ();
	
		curl_setopt_array ( $ch, $curlOptions );
	
		$responseQuery = curl_exec ( $ch ); // make the request
	
		if (curl_errno ( $ch )) {
			$_errors = curl_error ( $ch );
			curl_close ( $ch );
			$app->enqueueMessage(print_r($_errors), 'error');
			if($params->get('my_debug')){
				$debug = "\nPaynow PLUGIN ERROR\n";
				$debug .= print_r($_errors,true);
				MyMuseHelper::logMessage( $debug  );
			}
			return false;
		} else {
			curl_close ( $ch );
			return $responseQuery;
		}
	}
	
	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "payment_paynow:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		}
		return $email_msg;
	
	}
}
?>