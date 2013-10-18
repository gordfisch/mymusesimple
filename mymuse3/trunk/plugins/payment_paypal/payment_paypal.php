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
JPlugin::loadLanguage( 'plg_mymuse_payment_paypal', JPATH_ADMINISTRATOR );

/**
* MyMuse PaymnetPaypal plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Paypal extends JPlugin
{
	/**
	 * Constructorcd plugins
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	  * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgMyMusePayment_Paypal(&$subject, $config)  {
		parent::__construct($subject, $config);
		

		//PAYMENT URL
		if($this->params->get('my_paypal_sandbox'))
		{
			define ("PAYPAL_URL","https://www.sandbox.paypal.com/cgi-bin/webscr");
			define ("PAYPAL_HOST","www.sandbox.paypal.com");
		}
		else
		{
			define("PAYPAL_URL","https://www.paypal.com/cgi-bin/webscr");
			define ("PAYPAL_HOST","www.paypal.com");
		}
	}

	/**
	 * PayPal Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{

		$mainframe =& JFactory::getApplication();
		$db		=& JFactory::getDBO();
		if(isset($shopper->profile['country'])){
			// Paypal wants the country_2_code
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
		$shopper->last_name 		= isset($shopper->profile['last_name'])? $shopper->profile['last_name'] : '';
		if(!$shopper->first_name){
			@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
			if($shopper->last_name = ""){
				$shopper->last_name = $shopper->first_name;
			}
		}
		
		if(isset($shopper->profile['region'])){
			// Paypal wants the state_2_code
			$query = "SELECT state_2_code from #__mymuse_state WHERE id='".$shopper->profile['region']."'";
			$db->setQuery($query);
			$shopper->region = $db->loadResult();
		}
		
		//PayPal Account Email
		if(
			$this->params->get('my_paypal_sandbox') &&
			$this->params->get('my_paypal_sandbox_email')
		){
			$merchant_email = $this->params->get('my_paypal_sandbox_email');
		}elseif(
			$this->params->get('my_paypal_micropayments') && 
			$this->params->get('my_paypal_micropayments_cutoff') >= $order->order_total &&
			$this->params->get('my_paypal_micro_email') != ''
		){
			$merchant_email = $this->params->get('my_paypal_micro_email');
		}else{

			$merchant_email = $this->params->get('my_paypal_email');
		}
		
		//Shopper Email
		if($this->params->get('my_paypal_sandbox') && $this->params->get('my_paypal_sandbox_customer_email')){
			$shopper_email = $this->params->get('my_paypal_sandbox_customer_email');
		}else{
			$shopper_email = $shopper->email;
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

		$string = '
		<form action="'.PAYPAL_URL.'" method="post" name="adminFormPayPal" >
	    <input type="hidden" name="amount" value="'.sprintf("%.2f", $order->order_subtotal).'" />
		<input type="hidden" name="tax_cart"        value="'. $order->tax_total.'" />
		<input type="hidden" name="return"          value="'. JURI::base().'index.php?option=com_mymuse&task=thankyou&Itemid='.$Itemid.'" />
		<input type="hidden" name="cancel_return"   value="'. JURI::base().'index.php?option=com_mymuse&task=paycancel&Itemid='.$Itemid.'" />
		<input type="hidden" name="notify_url"      value="'. JURI::base().'index.php?option=com_mymuse&task=notify" />
		
		<input type="hidden" name="cmd"             value="_cart" />
		<input type="hidden" name="business"        value="'. $merchant_email.'" />
		<input type="hidden" name="custom"          value="'. $order->order_number.'" />
		<input type="hidden" name="upload"          value="1" />
		<input type="hidden" name="currency_code"   value="'. $store->currency.'" />
		<input type="hidden" name="item_name"       value="'. $store->title.'" />
		<input type="hidden" name="item_number"     value="'. $order->order_number.'" />
		<input type="hidden" name="first_name"      value="'. $shopper->first_name.'" />
		<input type="hidden" name="last_name"       value="'. $shopper->last_name.'" />
		<input type="hidden" name="address_street"  value="'. $shopper->address1." ".$shopper->address2.'" />
		<input type="hidden" name="address_city"    value="'. $shopper->city.'" />
		<input type="hidden" name="address_state"   value="'. $shopper->region.'" />
		<input type="hidden" name="address_country" value="'. $shopper->country.'" />
		<input type="hidden" name="address_zip"     value="'. $shopper->postal_code.'" />
		<input type="hidden" name="address1"  		value="'. $shopper->address1.'" />
		<input type="hidden" name="address2"  		value="'. $shopper->address2.'" />
		<input type="hidden" name="city"    		value="'. $shopper->city.'" />
		<input type="hidden" name="state"   		value="'. $shopper->region.'" />
		<input type="hidden" name="country" 		value="'. $shopper->country.'" />
		<input type="hidden" name="zip"     		value="'. $shopper->postal_code.'" />
		<input type="hidden" name="payer_email"     value="'. $shopper_email.'" />
		
		';
		
		//send individual items, unless we have a coupon
		if(!isset($order->coupon_discount)){
			for ($i=0;$i<$order->idx;$i++) {
				$j = $i+1;
				$string .= '
				<input type="hidden" name="item_name_'. $j .'"
				value="'. $order->items[$i]->title;
				if($params->get('my_show_sku') || $params->get('my_saveorder') == "after"){
					$string .= ' : '.$order->items[$i]->product_sku;
				}
				$string .= '" />
				<input type="hidden" name="quantity_'. $j .'"
				value="'. $order->items[$i]->quantity.'" />
				<input type="hidden" name="amount_'. $j .'"
				value="'. $order->items[$i]->product_item_price.'" />
				';
			}
		}else{
			//one item
			$title = '';
			for ($i=0;$i<$order->idx;$i++) {
				$title .= $order->items[$i]->title.":\n";
			}
			$string .= '
			<input type="hidden" name="item_name_1"
			value="'. $title;

			$string .= '" />
			<input type="hidden" name="quantity_1"
			value="1" />
			<input type="hidden" name="amount_1"
			value="'. sprintf("%.2f", $order->order_subtotal).'" />
			';
			
		}

		

		if($params->get('my_use_shipping') && isset($order->order_shipping->cost) && $order->order_shipping->cost > 0){
			$string .= '<input type="hidden" name="shipping_1" value="'. $order->order_shipping->cost.'" />
			';
			$string .= '<input type="hidden" name="invoice" value="'. $order->order_shipping->id.'" />
			';
		}
		$string .= '
		<div id="paypal_form">
		<input type="submit" class="button" name="paypal" value="'. JText::_('MYMUSE_PAY_AT_PAYPAL').'">
		</div>
		</form>
		';
		
		return $string;
	}

	/**
	 * notify
	 * catch the IPN post from PayPal, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		global $mainframe;

		$db	= & JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nPayPal notify PLUGIN\n";

		$result = array();
		$result['plugin'] 				= "PayPal";
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

	
		if(!isset($_POST['notify_version'])){
			//wasn't paypal
			$debug .= "Was not PayPal. \n";
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
		
		// respond to PayPal
        header("HTTP/1.0 200 OK");
        
		JPluginHelper::importPlugin('mymuse');
		$result['order_number'] 		= $_POST['custom'];
		$result['payer_email'] 			= urldecode($_POST['payer_email']);
  		$result['payment_status'] 		= $_POST['payment_status'];
  		$result['txn_id'] 				= trim(stripslashes($_POST['txn_id']));
		$result['amountin'] 			= $_POST['mc_gross'];
        $result['currency'] 			= $_POST['mc_currency'];
        $result['rate'] 				= @$_POST['rate'];
        $result['fees'] 				= @$_POST['mc_fee'];
        $result['transaction_id'] 		= $_POST['txn_id'];
        $result['transaction_status'] 	= $_POST['payment_status'];
        $result['description'] 			= @$_POST['note'];
	
        $sendToPayPal = file_get_contents("php://input")."&cmd=_notify-validate";

		$paypalpath = "/cgi-bin/webscr";
		
        $header = "POST $paypalpath HTTP/1.0\r\n";
        $header .= "Host: ".PAYPAL_HOST."\r\n";
        $header.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header.= "Content-Length: ".strlen($sendToPayPal)."\r\n";
        $header.= "Accept: */*\r\n\r\n";
        $date = date('Y-m-d h:i:s');
        
        $debug = "$date  1. Connecting to: ".PAYPAL_HOST."$paypalpath\n";
        $debug .= "Using this http Header: \n";    
        $debug .= "$header";
        $debug .= "and this String:\n";    
        $debug .= "$sendToPayPal\n\n";
        
        /**--------------------------------------------
        * Open a socket to the PayPal server...
        *--------------------------------------------*/
        $fp = fsockopen ( 'ssl://'.PAYPAL_HOST, 443, $errno, $errstr, 30);
        
        if (!$fp) {
            $debug .= "2. Status: FAILED TO OPEN SOCKET\n $errstr ($errno)\n\n";
            $debug .= "-------END-------";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			$result['error'] = $debug;
  			return $result;
        }else{
        	$date = date('Y-m-d h:i:s');
        	$debug .= "$date 2. Connection successful. Now posting to ".PAYPAL_HOST."$paypalpath \n\n";
        	$result['message_sent'] = 1;

        	fwrite($fp, $header . $sendToPayPal);
        	$res = '';
        	while (!feof($fp)) {
        		$res .= fgets ($fp, 1024);
        	}
        	fclose ($fp);
        	
        	$date = date('Y-m-d h:i:s');
        	$debug .= "$date 3. Response from ".PAYPAL_HOST.": \n";
        	$debug .= $res."\n\n";
        	$result['message_received'] = 1;
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  					
        	
        	if (preg_match ( "/VERIFIED/", $res) ) {
        		//order was verified!
            	$date = date('Y-m-d h:i:s');
            	$debug = "$date  4. order VERIFIED at PayPal\n\n";
            	$result['order_verified'] = 1;
        		if($params->get('my_debug')){
        			MyMuseHelper::logMessage( $debug  );
  				}
            	
            	
  				// SAVE ORDER AFTER
            	if($params->get('my_saveorder') == "after"){
            		//must capture the order here

            		$MyMuseCart		=& MyMuse::getObject('cart','helpers');
					$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
					$MyMuseShopper 	=& MyMuse::getObject('shopper','models');
            		$debug = "4.0.0 We have a post: $q  ".print_r($_POST,true)."\n\n";
            		if($params->get('my_debug')){
        				MyMuseHelper::logMessage( $debug  );
  					}
					
					$q = "SELECT u.id from #__users as u
					WHERE 
					u.email='".$_POST['payer_email']."'";
					$db->setQuery($q);
					$user_id = $db->loadResult();
					if(!$user_id){
						$debug = "4.0.1 We do not have a user id! Must exit. Email was ".$_POST['payer_email']."\n";
						if($params->get('my_debug')){
        					MyMuseHelper::logMessage( $debug  );
  						}
  						$result['error'] = $debug;
  						return $result;
					}
					
            		$cart = array();
            		$cart['idx'] = $_POST['num_cart_items'];
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
            		if($_POST['discount']){
            			list($discount_type,$coupon_id) = explode(":",$_POST['custom']);
            			$cart[$i]['coupon_id']= $coupon_id;
            			$cart['idx']++;
            		}
            		$cart['ship_method_id'] = $_POST['invoice'];
            		
            		
            		$MyMuseCart->cart = $cart;
            		//save the cart in the session
					$session = &JFactory::getSession();
					$session->set("cart",$MyMuseCart->cart);
            		$cart_order = $MyMuseCart->buildOrder( 0 );
  					
  					
            		$dispatcher		=& JDispatcher::getInstance();
                	$res = $dispatcher->trigger('onCaclulateMyMuseShipping', array($cart_order, $cart['ship_method_id'] ));

					$MyMuseCart->cart['shipping'] = $res[0];
            		$debug = "4.0.2 We have created a cart: $q  ".print_r($MyMuseCart->cart,true)."\n\n";
            		if($params->get('my_debug')){
        				MyMuseHelper::logMessage( $debug  );
  					}
            		$session->set("cart",$MyMuseCart->cart);
            		
            		// Load the profile data from the database.
            		
            		$shopper = $MyMuseShopper->getShopperByUser($user_id);
					$debug = "4.0.1 We have created a shopper: $user_id  ".print_r($shopper,true)."\n\n";
            		if($params->get('my_debug')){
        				MyMuseHelper::logMessage( $debug  );
  					}
            		$session = JFactory::getSession();
            		$session->set("user",$shopper);

  					
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
            		$debug = "4.0.4 Order saved:  ".print_r($order,true)."\n\n";
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
        			$debug = "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";
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
                		MyMuseHelper::orderStatusUpdate($result['order_id'] , "C");
                		$date = date('Y-m-d h:i:s');
                		$debug .= "$date 5. order COMPLETED at PayPal, update in DB\n\n";
                		$result['order_completed'] = 1;
        			}else{
        				// not completed, set order status to 
        				MyMuseHelper::orderStatusUpdate($result['order_id'] , "I");
        			}
        		}

        	}else{
        		//not verified
        		$date = date('Y-m-d h:i:s');
        		$debug .= "$date 4. Not VERIFIED at PayPal\n\n";
        		$debug .= "-------END PLUGIN-------";
        		if($params->get('my_debug')){
        			MyMuseHelper::logMessage( $debug  );
  				}
  				$result['error'] = $debug;
  				return $result;
        	}
        }
        $date = date('Y-m-d h:i:s');
        $debug .= "$date Finished talking to PayPal \n\n";
		$debug .= "-------END PLUGIN-------";
  		if($params->get('my_debug')){
        	MyMuseHelper::logMessage( $debug  );
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