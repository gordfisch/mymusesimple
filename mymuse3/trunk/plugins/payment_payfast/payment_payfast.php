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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');


/**
* MyMuse Payment Payfast plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Payfast extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	  * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgMyMusePayment_Payfast(&$subject, $config)  {
		parent::__construct($subject, $config);
		

	}

	/**
	 * Payfast Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		$mainframe =& JFactory::getApplication();
		$db		=& JFactory::getDBO();
		

		// Include the PayFast common file
		define( 'PF_DEBUG', ( $this->params->get('debug') == 'on' ? true : false ) );
		require_once( dirname(__FILE__).'/payfast_common.inc' );

		$pfHost = ( ( $this->params->get('test_mode') == 'on' ) ? 'sandbox' : 'www' ) . '.payfast.co.za';
		$payfastUrl = 'https://'. $pfHost .'/eng/process';

		// If NOT test mode, use normal credentials
		if( $this->params->get('test_mode') != 'on' )
		{
			$merchantId = $this->params->get('merchant_id');
			$merchantKey = $this->params->get('merchant_key');
			$shopperEmail = $shopper->email;
			
		}
		// If test mode, use generic sandbox credentials
		else
		{
			$merchantId = '10000100';
			$merchantKey = '46f0cd694581a';
			$shopperEmail = "sbtu01@payfast.co.za";
		}

		// total
		
		$total = $order->order_subtotal + $order->tax_total;
		if($params->get('my_use_shipping') && isset($order->order_shipping->cost)){
			$total += $order->order_shipping->cost;
		}
		
		//convert currency
		if($this->params->get('conversion_multiplier') 
		    && $this->params->get('conversion_multiplier') != ""){
		    	$total =  $total * $this->params->get('conversion_multiplier');
		    }
		    
		// Create URLs
		$returnUrl = JURI::Base() .'index.php?option=com_mymuse&task=thankyou&Itemid='.$Itemid;
		$cancelUrl = JURI::Base() .'index.php?option=com_mymuse&task=paycancel&Itemid='.$Itemid;
		$notifyUrl = JURI::Base() .'index.php?option=com_mymuse&task=notify&order_number='.$order->order_number;

		// Create description
		// Line item details are not available in the $params variable
		$description = '';

		// Construct data for the form

		@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
		if($shopper->last_name == ""){
			$shopper->last_name = $shopper->first_name;
		}
		$data = array(
		// Merchant details
        'merchant_id' => $merchantId,
        'merchant_key' => $merchantKey,
        'return_url' => $returnUrl,
        'cancel_url' => $cancelUrl,
        'notify_url' => $notifyUrl,

		// Buyer Details
		
        'name_first' => $shopper->first_name,
        'name_last' => $shopper->last_name,
        'email_address' => $shopperEmail,

		// Item details
    	'item_name' => $store->title .' purchase, Invoice ID #'. $order->order_number,
    	'item_description' => $description,
    	'amount' => sprintf("%.2f", $total),
        'm_payment_id' => $order->order_number,
        'currency_code' => $this->params->get('currency'),

		// Other
        'user_agent' => PF_USER_AGENT,
		);

		// Output the form
		$output = '<form id="payfast_form" name="payfast_form" action="'. $payfastUrl .'" method="post">';
		foreach( $data as $name => $value )
		$output .= '<input type="hidden" name="'.$name.'" value="'. htmlspecialchars( $value ) .'">'."\n";

		$output .= $data['amount']." ".$this->params->get('currency')."<br />";
		$output .= '<input type="submit" class="button" name="payfaster" value="'.JText::_('MYMUSE_PAY_AT_PAYFAST').'" />';
		$output .= '</form>';

		return( $output );
		/**
		 * Testing Merchant account:

		 * Username: sbtm01@payfast.co.za
		 * Account Name: Sandbox Text
		 * Merchant ID: 10000100
		 * Merchant Key: 46f0cd694581a
		 * PDT: Enabled
		 * PDT Key: 0a1e2e10-03a7-4928-af8a-fbdfdfe31d43
		 * ITN: Disabled (Use notify_url to test ITN)

		 User account:

		 * Username: sbtu01@payfast.co.za
		 * Password: clientpass
		 */

	}
	

	
	/**
	 * notify
	 * catch the IPN post from Payfast, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$mainframe &= JFactory::getApplication();
		
		JPluginHelper::importPlugin('mymuse');
		
		if(!isset($_REQUEST['m_payment_id'])){
			//wasn't payfast
			
			if($params->get('my_debug')){
				$debug .= "Was not Payfast. \n";
				$debug .= "-------END-------\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}
		
		
		require_once( dirname(__FILE__).'/payfast_common.inc' );
		define( 'PF_DEBUG', ( $this->params->get('debug') == 'on' ? true : false ) );
		
		// Variable Initialization
		$pfError = false;
		$pfErrMsg = '';
		$pfData = array();
		$pfHost = ( ( $this->params->get('test_mode') == 'on' ) ? 'sandbox' : 'www' ) . '.payfast.co.za';
		$pfOrderId = '';
		$pfParamString = '';
		
		//// Notify PayFast that information has been received
		if( !$pfError )
		{
    		header( 'HTTP/1.0 200 OK' );
    		flush();
		}
		
		$date = date('Y-m-d h:i:s');
		if($params->get('my_debug')){
			$debug = "#####################\nPayfast notify PLUGIN\n";
			$debug .= $date."\n";
			$debug .= "Received from Payfast:\n";
			$debug .= print_r($_POST, true);
			MyMuseHelper::logMessage( $debug  ); 
		}

		
		/** typical return
         *     [option] => com_mymuse
    [task] => notify
    [order_number] => ef17a38314c977ec9409137504e5575c
    [m_payment_id] => ef17a38314c977ec9409137504e5575c
    [pf_payment_id] => 24715
    [payment_status] => COMPLETE
    [item_name] => MyMuse purchase, Invoice ID #ef17a38314c977ec9409137504e5575c
    [item_description] => 
    [amount_gross] => 6.00
    [amount_fee] => -0.14
    [amount_net] => 5.86
    [custom_str1] => 
    [custom_str2] => 
    [custom_str3] => 
    [custom_str4] => 
    [custom_str5] => 
    [custom_int1] => 
    [custom_int2] => 
    [custom_int3] => 
    [custom_int4] => 
    [custom_int5] => 
    [name_first] => Test
    [name_last] => User 01
    [email_address] => sbtu01@payfast.co.za
    [merchant_id] => 10000100
    [signature] => 81d8a4a4d640b87c0b224332afce7340
    [Itemid] => 
    [return] => L3BpcGVyL2luZGV4LnBocD9vcHRpb249Y29tX215bXVzZSZ0YXNrPW5vdGlmeQ==
    [view] => cart
    [layout] => cart

*/
		
		$result = array();
		$result['plugin'] 				= "Payfast";
		$result['myorder'] 				= 1; //must be >0 to trigger that it was this plugin
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

        unset($_POST['task']);
        unset($_POST['Itemid']);
        unset($_POST['return']);
        unset($_POST['view']);
        unset($_POST['layout']);
        unset($_POST['option']);
		
		
		///// Get data sent by PayFast
		if( !$pfError )
		{
			pflog( 'Get posted data' );
			
			// Posted variables from ITN
			$pfData = pfGetData();

			pflog( 'PayFast Data: '. print_r( $pfData, true ) );
            if($params->get('my_debug')){
                $debug = 'PayFast Data: '. print_r( $pfData, true )."\n\n";
                MyMuseHelper::logMessage( $debug  );
            }
            $result['message_sent'] = 1;
			if( $pfData === false )
			{
				$pfError = true;
				$pfErrMsg = PF_ERR_BAD_ACCESS;
				$result['error'] = PF_ERR_BAD_ACCESS;
                if($params->get('my_debug')){
                    $debug = $result['error']."\n\n";
                    MyMuseHelper::logMessage( $debug  );
                }
				return $result;
			}
			$result['message_received'] = 1;
		}

		//// Verify security signature
		if( !$pfError )
		{
			pflog( 'Verify security signature' );

			// If signature different, log for debugging
			if( !pfValidSignature( $pfData, $pfParamString ) )
			{
				$pfError = true;
				$pfErrMsg = PF_ERR_INVALID_SIGNATURE;
				$result['error'] = PF_ERR_INVALID_SIGNATURE." ".$pfParamString;
                if($params->get('my_debug')){
                    $debug = $result['error']."\n\n";
                    $debug .= "-------END-------\n";
                    MyMuseHelper::logMessage( $debug  );
                }
				return $result;
			}
		}
        $result['myorder'] = 1;
        $result['message_sent'] = 1;
        $result['message_received'] = 1;
        $result['order_verified'] = 1;
		$result['payer_email'] 			= urldecode($_POST['email_address']);
  		$result['payment_status'] 		= $_POST['payment_status'];
        if($_POST['pf_payment_id']){
            $result['txn_id'] 				= trim(stripslashes($_POST['pf_payment_id']));
        }else{
            $result['txn_id']           = $_POST['m_payment_id'];
        }
		$result['amountin'] 			= $_POST['amount_gross'];
        $result['currency'] 			= $this->params->get('currency');
        $result['fees'] 				= @$_POST['amount_fee'];
        $result['description'] 			= @$_POST['note'];

		//// Verify source IP (If not in debug mode)
		if( !$pfError && !defined( 'PF_DEBUG' ) )
		{
			pflog( 'Verify source IP' );

			if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
			{
				$pfError = true;
				$pfErrMsg = PF_ERR_BAD_SOURCE_IP;
				$result['error'] = PF_ERR_BAD_SOURCE_IP;
                if($params->get('my_debug')){
                    $debug = $result['error']."\n\n";
                    $debug .= "-------END-------\n";
                    MyMuseHelper::logMessage( $debug  );
                }
				return $result;
			}
		}

		//// Get internal order and verify it hasn't already been processed
		if( !$pfError )
		{
			pflog( "Check order hasn't been processed" );

			// Get the Order Details from the database
			if($pfData['m_payment_id']){
				$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$pfData['m_payment_id']."'";
				$date = date('Y-m-d h:i:s');
				
                if($params->get('my_debug')){
                    $debug = "$date  $query \n\n";
                    MyMuseHelper::logMessage( $debug  );
                }
				$db	= & JFactory::getDBO();
				$db->setQuery($query);
				if(!$this_order = $db->loadObject()){

					if($params->get('my_debug')){
						$debug = "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";
						$debug .= "-------END-------\n";
						MyMuseHelper::logMessage( $debug  );
					}
					$result['error'] = "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";;
					return $result;
				}else{

					// update the payment status

					if($params->get('my_debug')){
						$debug = "$date Order found ".$pfData['order_number']."\n\n";
						MyMuseHelper::logMessage( $debug  );
					}
					$result['order_found']  = 1;
					$result['order_id'] 	= $this_order->id;
					
                    $result['order_number'] = $pfData['m_payment_id'];

				}
			}else{
				$debug = "$date !!!!Error no order number: \n\n";
				$debug .= "-------END-------\n";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
				$result['error'] = "!!!!Error no order number: \n\n";;
				return $result;
			}
		}

		//// Verify data received
		if( !$pfError )
		{
			pflog( 'Verify data received' );

			$pfValid = pfValidData( $pfHost, $pfParamString );

			if( !$pfValid )
			{
				$pfError = true;
				$pfErrMsg = PF_ERR_BAD_ACCESS;
				$result['error']  = PF_ERR_BAD_ACCESS;
				return $result;
			}
		}

		//// Check status and update order
		if( !$pfError )
		{
			pflog( 'Check status and update order' );


        
			if ($pfData['payment_status'] == 'COMPLETE') {
				//order was verified!
				
				$result['order_completed'] = 1;
				MyMuseHelper::orderStatusUpdate($result['order_id'] , "C");
				
				if($params->get('my_debug')){
                    $debug = "$date Order COMPLETE at Payfast\n\n";
                    $debug .= "$date Order updated to Completed in DB\n\n";
					MyMuseHelper::logMessage( $debug  );
				}
                return $result;
				 
			}elseif ($pfData['payment_status'] == 'PENDING') {
				//pending
				$debug = "$date Order still PENDING at Payfasty\n\n";
				$debug .= "-------END-------\n";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
				$result['error'] = "$date Order still PENDING at Payfast\n\n";
				return $result;
					
			}elseif ($pfData['payment_status'] == 'DECLINED') {
				//cancelled
				$debug .= "$date Order Declined at Payfast\n\n";
				$debug .= "-------END-------\n";
				if($params->get('my_debug')){
					MyMuseHelper::logMessage( $debug  );
				}
				MyMuseHelper::orderStatusUpdate($result['order_id'] , "X");
				$result['error'] = "$date Order DECLINED at Payfast\n\n";
				return $result;
					
			}


			if($params->get('my_debug')){
				$date = date('Y-m-d h:i:s');
				$debug .= "$date Finished talking to Payfast \n\n";
				$debug .= "-------END-------\n";
				MyMuseHelper::logMessage( $debug  );
			}
        
    
		}
		return $result;
	}
	
	function onAfterMyMusePayment()
	{
	
		$email_msg = "paymentpaypal:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		return $email_msg;
	
	}

}




?>