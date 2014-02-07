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
* MyMuse PaymentMoneybookers plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Moneybookers extends JPlugin
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
	function plgMyMusePayment_Moneybookers(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		//PAYMENT URL
		define("MONEYBOOKERS_URL","https://www.moneybookers.com/app/payment.pl");
		define ("MONEYBOOKERS_HOST","www.moneybookers.com");

	}

	/**
	 * Moneybookers Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		$mainframe =& JFactory::getApplication();
		$db		=& JFactory::getDBO();

		//Moneybookers Account Email
		$merchant_email = $this->params->get('my_moneybookers_email');

		//Shopper Email
		$shopper_email = $shopper->email;


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
		<form action="'.MONEYBOOKERS_URL.'" method="post" name="adminForm" >
		<input type="hidden" name="pay_to_email"        value="'. $merchant_email.'" />
		
		<input type="hidden" name="recipient_description"        value="'. $store->title.'" />
		<input type="hidden" name="return_url"          value="'. JURI::base().'index.php?option=com_mymuse&task=thankyou&Itemid='.$Itemid.'" />
		<input type="hidden" name="return_url_text"     value="'. JText::_("MYMUSE_MONEYBOOKERS_RETURN_TO").' '. $store->title.'" />
		<input type="hidden" name="return_url_target"   value="1" />
		<input type="hidden" name="cancel_url"   		value="'. JURI::base().'index.php?option=com_mymuse&task=paycancel&Itemid='.$Itemid.'" />
		<input type="hidden" name="cancel_url_target"   value="1" />
		<input type="hidden" name="status_url"      	value="'. JURI::base().'index.php?option=com_mymuse&task=notify" />
		<input type="hidden" name="transaction_id"  	value="'.$order->order_number.'" />
		';
		if($this->params->get('my_moneybookers_status_url2')){
			$string .= '<input type="hidden" name="status_url2"      value="'.$this->params->get('my_moneybookers_status_url2').'" />
			';
		}
		
		@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
		if($shopper->last_name = ""){
			$shopper->last_name = $shopper->first_name;
		}
		
		$string .= '
		<input type="hidden" name="language"        value="EN" />
		<input type="hidden" name="pay_from_email"  value="'. $shopper_email.'" />
		<input type="hidden" name="firstname"       value="'. $shopper->first_name.'" />
		<input type="hidden" name="lastname"        value="'. $shopper->last_name.'" />
		<input type="hidden" name="address"  		value="'. $shopper->profile['address1'].'" />
		<input type="hidden" name="address2"  		value="'. $shopper->profile['address2'].'" />
		<input type="hidden" name="postal_code"     value="'. $shopper->profile['postal_code'].'" />
		<input type="hidden" name="phone_number"    value="'. $shopper->profile['phone'].'" />
		<input type="hidden" name="city"    		value="'. $shopper->profile['city'].'" />
		<input type="hidden" name="state"   		value="'. $shopper->profile['region'].'" />
		<input type="hidden" name="country" 		value="'. $shopper->profile['country'].'" />
		<input type="hidden" name="currency"   		value="'. $store->currency.'" />
		<input type="hidden" name="amount" 			value="'.sprintf("%.2f", $total).'" />
		
		<input type="hidden" name="amount2_description" value="'.JText::_("MYMUSE_MONEYBOOKERS_TAX_TOTAL").'" />
		<input type="hidden" name="amount2"        value="'. $order->tax_total.'" />
		';
		if($params->get('my_use_shipping') && $order->order_shipping->cost > 0){
			$string .= '<input type="hidden" name="amount3_description" value="'.JText::_("MYMUSE_MONEYBOOKERS_SHIPPING").'" />
			<input type="hidden" name="amount3" value="'. $order->order_shipping->cost.'" />
			';
		}
		$string .= '
		<input type="hidden" name="detail1_description" value="'. JText::_("MYMUSE_MONEYBOOKERS_TITLE").'" />
		<input type="hidden" name="detail2_description" value="'. JText::_("MYMUSE_MONEYBOOKERS_QUANTITY").'" />
		<input type="hidden" name="detail3_description" value="'. JText::_("MYMUSE_MONEYBOOKERS_PRICE").'" />
		';
		$detail1_text = '';
		$detail2_text = '';
		$detail3_text = '';

		for ($i=0;$i<$order->idx;$i++) { 
			
			$detail1_text .= $order->items[$i]->title.",\n";
			$detail2_text .= $order->items[$i]->quantity.",\n"; 
			$detail3_text .= $order->items[$i]->product_item_price.",\n"; 
			
		}
		$detail1_text = preg_replace("/,$/","",$detail1_text);
		$detail2_text = preg_replace("/,$/","",$detail2_text);
		$detail3_text = preg_replace("/,$/","",$detail3_text);
		$string .= '
        	<input type="hidden" name="detail1_text" value="'. $detail1_text.'" />
			<input type="hidden" name="detail2_text" value="'. $detail2_text.'" />
			<input type="hidden" name="detail3_text" value="'. $detail3_text.'" />
			';

		$string .= '
		<div id="moneybookers_form">
		<input type="submit" class="button" name="moneybookers" value="'. JText::_('MYMUSE_PAY_AT_MONEYBOOKERS').'">
		</div>
		</form>
		';
		
		return $string;
	}
	
	/**
	 * notify
	 * catch the IPN post from Moneybookers, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$mainframe &= JFactory::getApplication();

		
		$date = date('Y-m-d h:i:s');
		$debug = "$date\n#####################\nMoneybookers notify PLUGIN\n";


		$result = array();
		$result['plugin'] 				= "Moneybookers";
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

		
		if(!isset($_POST['mb_transaction_id'])){
			//wasn't moneybookers
			$debug .= "Was not Moneybookers. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}elseif($params->get('my_debug')){
				$debug .= "RECIEVED THIS POST\n";
				$debug .= print_r($_POST, true);
        		MyMuseHelper::logMessage( $debug  );
  			}
		$result['myorder'] = 1;
		
		// respond to Moneybookers
        header("HTTP/1.0 200 OK");
        
		JPluginHelper::importPlugin('mymuse');
		$status_array = array(
			'2' => 'Processed',
			'0' => 'Pending',
			'-1' => 'Cancelled',
			'-2' => 'Failed',
			'-3' => 'Chargeback'
		);

		$status = $status_array[$_POST['status']];

		$result['order_number'] 		= $_POST['transaction_id'];
		$result['payer_email'] 			= $_POST['pay_from_email'];
  		$result['payment_status'] 		= $status;
  		$result['txn_id'] 				= $_POST['mb_transaction_id'];
		$result['amountin'] 			= $_POST['amount'];
        $result['currency'] 			= $_POST['mb_currency'];
        $result['rate'] 				= '';
        $result['fees'] 				= '';
        $result['transaction_id'] 		= $_POST['mb_transaction_id'];
        $result['transaction_status'] 	= $status;
        $result['description'] 			= @$_POST['note'];
		

        $date = date('Y-m-d h:i:s');
        $debug .= "$date 2. Sent this to Moneybooker: HTTP/1.0 200 OK \n\n";
        $result['message_sent'] = 1;
        $result['message_received'] = 1;
         
       // Get the Order Details from the database
        if($result['order_number']){
        	$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$result['order_number']."'";
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
        		if($this->params->get('my_moneybookers_secret')){
					//calculate md5
					$secret = strtoupper(md5($this->params->get('my_moneybookers_secret')));
					$md5string = strtoupper(md5($_POST['merchant_id'].$_POST['transaction_id'].
					$secret.$_POST['mb_amount'].$_POST['mb_currency'].$_POST['status']));
					$debug = "$date 4.2 Moneybookers secret \nmd5sig = ".$_POST['md5sig']."\noursig = ".$md5string."\n\n";
					if($md5string != $_POST['md5sig']){
						$debug .= "$date 5. Secret md5 values do not match!\n\n";
        				$debug .= "-------END-------";
        				if($params->get('my_debug')){
        					MyMuseHelper::logMessage( $debug  );
  						}
  						$result['error'] = "$date 5. Secret md5 values do not match!\n\n";
  						return $result;
					}
				}
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
        	$debug .= "$date  4.3 order PROCESSED at Moneybookers\n\n";
        	$result['order_verified'] = 1;
        	MyMuseHelper::orderStatusUpdate($result['order_id'] , "C");
        	$debug .= "$date 5. order updated to Completed in DB\n\n";
        	
        }elseif ($status == 'Pending') {
        	//pending
        	$debug .= "$date 4. Order still PENDING at Moneybookers\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			$result['error'] = "$date 4. Order still PENDING at Moneybookers\n\n";
  			return $result;
  			
        }elseif ($status == 'Cancelled') {
        	//cancelled
        	$debug .= "$date 4. Order CANCELLED at Moneybookers\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			MyMuseHelper::orderStatusUpdate($result['order_id'] , "X");
  			$result['error'] = "$date 4.3 Order CANCELLED at Moneybookers\n\n";
  			return $result;
  			
        }elseif ($status == 'Chargeback') {
        	//chargeback
        	$debug .= "$date 4. Order CHARGEBACK at Moneybookers\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			//MyMuseHelper::orderStatusUpdate($result['order_id'] , "X");
  			return $result;
        }elseif ($status == 'Failed') {
        	//chargeback
        	$debug .= "$date 4. Order FAILED at Moneybookers\n\n";
        	$debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			$result['error'] = "$date 4.3 Order FAILED at Moneybookers. Code: ".$_POST['failed_reason_code'] ."\n\n";
  			return $result;
        }

        $date = date('Y-m-d h:i:s');
        $debug .= "$date Finished talking to Moneybookers \n\n";
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
			$email_msg = "payment_moneybookers:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
				
		}
		return $email_msg;
	}

}

?>