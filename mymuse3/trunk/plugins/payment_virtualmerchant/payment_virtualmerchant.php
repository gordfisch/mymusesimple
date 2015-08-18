<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2013 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

/**
* MyMuse PaymentVirtualmerchant plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Virtualmerchant extends JPlugin
{
	
	/*
	 * var ssl_merchant_id 
	 * string
	 */
	
	var $ssl_merchant_id = '';
	
	/*
	 * var ssl_user_id
	* string
	*/
	
	var $ssl_user_id = '';
	
	/*
	 * var ssl_pin
	* string
	*/
	
	var $ssl_pin = '';
	
	
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
	function plgMyMusePayment_Virtualmerchant(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		//PAYMENT URL
		if($this->params->get('my_virtualmerchant_test'))
		{
			define ("VIRTUALMERCHANT_URL","https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do");
			define ("VIRTUALMERCHANT_HOST","demo.myvirtualmerchant.com");
			$this->ssl_merchant_id 	= $this->params->get('my_virtualmerchant_test_merchant_id');
			$this->ssl_user_id 		= $this->params->get('my_virtualmerchant_test_user_id');
			$this->ssl_pin 			= $this->params->get('my_virtualmerchant_test_pin');
		}
		else
		{
			define("VIRTUALMERCHANT_URL","https://www.myvirtualmerchant.com/VirtualMerchant/process.do");
			define ("VIRTUALMERCHANT_HOST","www.myvirtualmerchant.com");
			$this->ssl_merchant_id 	= $this->params->get('my_virtualmerchant_merchant_id');
			$this->ssl_user_id 		= $this->params->get('my_virtualmerchant_user_id');
			$this->ssl_pin 			= $this->params->get('my_virtualmerchant_pin');
		}
		
		//VIRTUAL TERMINAL
		//live: https://www.myvirtualmerchant.com/VirtualMerchant/login.do
		//test: https://demo.myvirtualmerchant.com/VirtualMerchantDemo/login.do

	}

	/**
	 * Virtualmerchant Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		$mainframe =& JFactory::getApplication();
		$db		=& JFactory::getDBO();

		//Virtualmerchant Account Email
		$merchant_email = $this->params->get('my_virtualmerchant_email');

		//Shopper Email
		$shopper_email 			= $shopper->email;
		$shopper->address1 		= isset($shopper->profile['address1'])? $shopper->profile['address1'] : '';
		$shopper->address2 		= isset($shopper->profile['address2'])? $shopper->profile['address2'] : '';
		$shopper->city 			= isset($shopper->profile['city'])? $shopper->profile['city'] : '';
		$shopper->region 		= isset($shopper->profile['region_name'])? $shopper->profile['region_name'] : '';
		$shopper->postal_code 	= isset($shopper->profile['postal_code'])? $shopper->profile['postal_code'] : '';
		$shopper->first_name 	= isset($shopper->profile['first_name'])? $shopper->profile['first_name'] : '';
		$shopper->last_name 	= isset($shopper->profile['last_name'])? $shopper->profile['last_name'] : '';
		$shopper->country		= isset($shopper->profile['country'])? $shopper->profile['country'] : '';
		$shopper->phone		= isset($shopper->profile['phone'])? $shopper->profile['phone'] : '';
	
		if($shopper->region){
			$query = "SELECT state_2_code FROM #__mymuse_state WHERE id='".$shopper->region ."'";
			$db->setQuery($query);
			$shopper->region = $db->loadResult();
		}
		
		if(!$shopper->first_name){
			@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
			if($shopper->last_name == ""){
				$shopper->last_name = $shopper->first_name;
			}
		}


		//does this order have reservation fees? How much is the "Pay_now" field?
		if($order->pay_now > 0 && $order->pay_now < $order->order_subtotal){
			$order->idx = 1;
			$order->order_subtotal = $order->pay_now;
			$order->items[0]->product_item_price = sprintf("%.2f", $order->pay_now);
			$order->items[0]->quantity = 1;
			$order->items[0]->title = JText::_('MYMUSE_REGISTRATION_FEE');
			$order->tax_total = 0.00;
		}
		$total = $order->order_subtotal + $order->tax_total;
		if($params->get('my_use_shipping') && $order->order_shipping > 0){
			$total += $order->order_shipping;
		}
		$string = '
		<form action="'.VIRTUALMERCHANT_URL.'" method="post" name="adminForm" >
		<input type="hidden" name="ssl_merchant_id"        
			value="'. $this->ssl_merchant_id.'" />
		<input type="hidden" name="ssl_user_id"        
			value="'. $this->ssl_user_id.'" />
		<input type="hidden" name="ssl_pin"        
			value="'. $this->ssl_pin.'" />
		<input type="hidden" name="ssl_transaction_type"        
			value="ccsale" />
		<input type="hidden" name="ssl_amount" 			
			value="'.sprintf("%.2f", $total).'" />
		<input type="hidden" name="ssl_invoice_number"  	
			value="'.$order->id.'" />
		<input type="hidden" name="ssl_show_form"  		
			value="true" />	
		<input type="hidden" name="ssl_first_name"       value="'. $shopper->first_name.'" />
		<input type="hidden" name="ssl_last_name"        value="'. $shopper->last_name.'" />
		
		<input type="hidden" name="ssl_avs_address"  		
			value="'. $shopper->address1.'" />	
		<input type="hidden" name="ssl_address2"  		
			value="'. $shopper->address2.'" />	
		<input type="hidden" name="ssl_city"  		
			value="'. @$shopper->city.'" />		
		<input type="hidden" name="ssl_avs_zip"  		
			value="'. @$shopper->postal_code.'" />		
		<input type="hidden" name="ssl_state"  		
			value="'. @$shopper->region.'" />	
		<input type="hidden" name="ssl_country"  		
			value="'. @$shopper->country.'" />	
		<input type="hidden" name="ssl_phone"  		
			value="'. @$shopper->phone.'" />	
		<input type="hidden" name="ssl_email"  value="'. $shopper_email.'" />
			
		
		<input type="hidden" name="ssl_receipt_apprvl_method"        value="REDG" />
		<input type="hidden" name="ssl_receipt_apprvl_get_url"      	
			value="'. JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=virtualmerchant&Itemid='.$Itemid.'" />
				
		<input type="hidden" name="ssl_error_url"      	
			value="'. JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=virtualmerchant&Itemid='.$Itemid.'" />
			
		<input type="hidden" name="ssl_receipt_decl_method"        value="REDG" />
		<input type="hidden" name="ssl_receipt_decl_get_url"      	
			value="'. JURI::base().'index.php?option=com_mymuse&task=thankyou&pp=virtualmerchant&Itemid='.$Itemid.'" />	
			
		';

		
		@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
		if($shopper->last_name = ""){
			$shopper->last_name = $shopper->first_name;
		}

		$string .= '
		<div id="virtualmerchant_form" class="pull-left">
			<button class="button uk-button " 
			type="submit" >'. JText::_('MYMUSE_PAY_AT_VIRTUALMERCHANT').'</button>
		</div>
		</form>
		';
		
		return $string;
	}
	
	/**
	 * notify
	 * catch the IPN post from Virtualmerchant, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$mainframe &= JFactory::getApplication();

		
		$date = date('Y-m-d h:i:s');
		$debug = "$date\n#####################\nVirtualmerchant notify PLUGIN\n";


		$result = array();
		$result['plugin'] 				= "Virtualmerchant";
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

		
		if(!isset($_REQUEST['ssl_transaction_type'])){
			//wasn't virtualmerchant
			$debug .= "Was not Virtualmerchant. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}elseif($params->get('my_debug')){
				$debug .= "RECIEVED THIS REQUEST\n";
				$debug .= print_r($_REQUEST, true);
        		MyMuseHelper::logMessage( $debug  );
  		}
		$result['myorder'] = 1;
		$result['message_sent'] 		= 1; //must be >0 or tiggers error
		$result['message_received'] 	= 1; //must be >0 or tiggers error
		
		// respond to Virtualmerchant
        header("HTTP/1.0 200 OK");
        
		JPluginHelper::importPlugin('mymuse');
		$status_array = array(
			'0' => 'Processed',
			'1' => 'Pending',
			'-1' => 'Cancelled',
			'-2' => 'Failed',
			'-3' => 'Chargeback'
		);
		$status = $status_array[$_REQUEST['ssl_result']];
		
		if(isset($_REQUEST['errorCode']) && $_REQUEST['errorCode'] > 0){
			$result['error'] = $_REQUEST['errorCode']." : ".$_REQUEST['errorMessage'];
		}
		

		$result['order_number'] 		= $_REQUEST['ssl_invoice_number'];
		$result['payer_email'] 			= $_REQUEST['ssl_email'];
  		$result['payment_status'] 		= $status;
  		$result['txn_id'] 				= $_REQUEST['ssl_txn_id'];
		$result['amountin'] 			= $_REQUEST['ssl_amount'];
        $result['currency'] 			= $params->get('my_currency');
        $result['rate'] 				= '';
        $result['fees'] 				= '';
        $result['transaction_id'] 		= $_REQUEST['ssl_txn_id'];
        $result['transaction_status'] 	= $status;
        $result['description'] 			= @$_REQUEST['note'];
		

        $date = date('Y-m-d h:i:s');
        $debug .= "$date 2. Sent this to VirtualMerchant: HTTP/1.0 200 OK \n\n";
        $result['message_sent'] = 1;
        $result['message_received'] = 1;
         
       // Get the Order Details from the database
        if($result['order_number']){
        	$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `id`='".$result['order_number']."'";
        	$date = date('Y-m-d h:i:s');
        	$debug .= "$date  4.1 $query \n\n";
        	$db	= & JFactory::getDBO();
        	$db->setQuery($query);
        	if(!$this_order = $db->loadObject()){
        		$debug .= "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";
        		$debug .= "-------END-------";
        		if($params->get('my_debug')){
        			MyMuseHelper::logMessage( $debug  );
        		}
        		$result['error'] = "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";;
        		return $result;
        	}else{

        		// update the payment status
        		$debug .= "4.2 Order found ".$result['order_number']."\n\n";
        		$result['order_found']  = 1;
        		$result['order_id'] 	= $this_order->id;
        		$result['order_completed'] = 1;

        	}
        }else{
        	$debug .= "5. !!!!Error no order number: \n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$result['error'] = "5. !!!!Error no order number: \n\n";;
        	return $result;
        }
        if ($status == 'Processed') {
        	//order was verified!
        	$debug .= "$date  4.3 order PROCESSED at Virtualmerchant\n\n";
        	$result['order_verified'] = 1;
        	MyMuseHelper::orderStatusUpdate($result['order_id'] , "C");
        	$debug .= "$date 5. order updated to Completed in DB\n\n";
        	
        }elseif ($status == 'Pending') {
        	//pending
        	$debug .= "$date 4. Order still PENDING at Virtualmerchant\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			$result['error'] = "$date 4. Order still PENDING at Virtualmerchant\n\n";
  			return $result;
  			
        }elseif ($status == 'Cancelled') {
        	//cancelled
        	$debug .= "$date 4. Order CANCELLED at Virtualmerchant\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			MyMuseHelper::orderStatusUpdate($result['order_id'] , "X");
  			$result['error'] = "$date 4.3 Order CANCELLED at Virtualmerchant\n\n";
  			return $result;
  			
        }elseif ($status == 'Chargeback') {
        	//chargeback
        	$debug .= "$date 4. Order CHARGEBACK at Virtualmerchant\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			//MyMuseHelper::orderStatusUpdate($result['order_id'] , "X");
  			return $result;
        }elseif ($status == 'Failed') {
        	//chargeback
        	$debug .= "$date 4. Order FAILED at Virtualmerchant\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			$result['error'] = "$date 4.3 Order FAILED at Virtualmerchant. Code: ".$_REQUEST['failed_reason_code'] ."\n\n";
  			return $result;
        }

        $date = date('Y-m-d h:i:s');
        $debug .= "$date Finished talking to Virtualmerchant \n\n";
		$debug .= "-------END-------";
  		if($params->get('my_debug')){
        	MyMuseHelper::logMessage( $debug  );
  		}
        return $result;

	}
	
	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "payment_virtualmerchant:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		
		}
		return $email_msg;
	}

}

?>