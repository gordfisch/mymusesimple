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
JPlugin::loadLanguage( 'plg_mymuse_payment_monsterpay', JPATH_ADMINISTRATOR );

/**
* MyMuse PaymentMonsterpay plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Monsterpay extends JPlugin
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
	function plgMyMusePayment_Monsterpay(&$subject, $config)  {
		parent::__construct($subject, $config);
		

		//PAYMENT URL
		define("MONSTER_URL","https://www.monsterpay.com/secure/index.cfm");
		define ("MONSTER_HOST","www.monsterpay.com");

	}

	/**
	 * Monsterpay Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		$mainframe =& JFactory::getApplication();
		$db		=& JFactory::getDBO();

		//Monsterpay Account
		if($this->params->get('my_monsterpay_mode') == "test"){
			$merchantid = $this->params->get('my_monsterpay_test_merchantid');
			$username = $this->params->get('my_monsterpay_test_username');
			$password = $this->params->get('my_monsterpay_test_password');
		}else{
			$merchantid = $this->params->get('my_monsterpay_merchantid');
			$username = $this->params->get('my_monsterpay_username');
			$password = $this->params->get('my_monsterpay_password');
		}
		

		// Monsterpay wants the country_2_code
		$db		=& JFactory::getDBO();
		$query = "SELECT country_2_code from #__mymuse_country WHERE country_3_code='".$shopper->country."'";
		$db->setQuery($query);
		$shopper->country = $db->loadResult();


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
		if($this->params->get('my_use_shipping') && $order->order_shipping > 0){
			$total += $order->order_shipping;
		}
/**	
<form action="https://www.monsterpay.com/secure/index.cfm" method="post">
  <input type="hidden" name="buttonaction" value="checkout">
  <input type="hidden" name="merchantidentifier" value="69J945280B">
  <input type="hidden" name="CurrencyAlphaCode" value="USD">
  <input type="hidden" name="LIDSKU" value="AYMS01">
  <input type="hidden" name="LIDDesc" value="Are You My Sister">
  <input type="hidden" name="LIDPrice" value="2.00AYMS01">
  <input type="hidden" name="LIDQty" value="1">
  <input type="hidden" name="LIDExpiry" value="1">
  <input type="hidden" name="ShippingRequired" value="1">
  <input type="hidden" name="IsVoucher" value="0">
  <input type="image" src="https://www.monsterpay.com/images/cartbuttons/co01.gif">
</form>		
	*/	
		@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
		if($shopper->last_name = ""){
			$shopper->last_name = $shopper->first_name;
		}
		$string = '
		<form action="'.MONSTER_URL.'" method="post" name="monsterpayForm" >
		<input type="hidden" name="MerchantIdentifier"        value="'. $merchantid.'" />
		<input type="hidden" name="ButtonAction"        value="checkout" />
		<input type="hidden" name="CurrencyAlphaCode"   		value="'. $this->params->get('my_monsterpay_currency').'" />
		<input type="hidden" name="MerchCustom"          value="'. $order->order_number .'" />

		<input type="hidden" name="BuyerInformation"  value="1" />
		<input type="hidden" name="Email"  value="'. $shopper->email.'" />
		<input type="hidden" name="FirstName"       value="'. $shopper->first_name.'" />
		<input type="hidden" name="LastName"        value="'. $shopper->last_name.'" />
		<input type="hidden" name="HomeNumber"       value="'. $shopper->profile['phone'].'" />
		<input type="hidden" name="MobileNumber"       value="'. $shopper->profile['mobile'].'" />
		<input type="hidden" name="Address1"  		value="'. $shopper->profile['address1'].'" />
		<input type="hidden" name="Address2"  		value="'. $shopper->profile['address2'].'" />
		<input type="hidden" name="PostalCode"     value="'. $shopper->profile['postal_code'].'" />
		<input type="hidden" name="City"    		value="'. $shopper->profile['city'].'" />
		<input type="hidden" name="State"   		value="'. $shopper->profile['region'].'" />
		<input type="hidden" name="Country" 		value="'. $shopper->profile['country'].'" />
		';

		$string .= '
		<input type="hidden" name="LIDSKU" value="'. $order->items[0]->product_sku.'" />
		<input type="hidden" name="LIDDesc" value="'. $order->items[0]->title.'" />
		<input type="hidden" name="LIDPrice" value="'. $order->items[0]->product_item_price.'" />
		<input type="hidden" name="LIDQty" value="'. $order->items[0]->quantity.'" />
		';
		
		if($this->params->get('my_monsterpay_shipping_required') && $order->items[0]->product_physical){
			$string .= '<input type="hidden" name="ShippingRequired" value="1" />
			';
		}

		for ($i=1;$i<$order->idx;$i++) { 
			
			$string .= '
			<input type="hidden" name="LIDSKU'.$i.'" value="'. $order->items[$i]->product_sku.'" />
			<input type="hidden" name="LIDDesc'.$i.'" value="'. $order->items[$i]->title.'" />
			<input type="hidden" name="LIDPrice'.$i.'" value="'. $order->items[$i]->product_item_price.'" />
			<input type="hidden" name="LIDQty'.$i.'" value="'. $order->items[$i]->quantity.'" />
			';
			if($this->params->get('my_monsterpay_shipping_required') && $order->items[$i]->product_physical){
				$string .= '<input type="hidden" name="ShippingRequired'.$i.'" value="1" />
				';
			}
			
		}
		if(isset($order->tax_total) && $order->tax_total > 0){
			
			$string .= '
			<input type="hidden" name="LIDSKU'.$i.'" value="TAXES" />
			<input type="hidden" name="LIDDesc'.$i.'" value="Tax Total" />
			<input type="hidden" name="LIDPrice'.$i.'" value="'. $order->tax_total.'" />
			<input type="hidden" name="LIDQty'.$i.'" value="1" />
			';
			$i++;
			
		}
		if($this->params->get('my_use_shipping') && $order->order_shipping  > 0){

			$string .= '
			<input type="hidden" name="LIDSKU'.$i.'" value="SHIPPING" />
			<input type="hidden" name="LIDDesc'.$i.'" value="Shipping Total" />
			<input type="hidden" name="LIDPrice'.$i.'" value="'. $order->order_shipping->cost.'" />
			<input type="hidden" name="LIDQty'.$i.'" value="1" />
			';
			$i++;
			
		}
		if($this->params->get('my_monsterpay_templateid',0)){
			$string .= '
			<input type="hidden" name="TemplateID" value="'.$this->params->get('my_monsterpay_templateid',0).'" />
			';
		}

		$string .= '
		<div id="monsterpay_form">
		<input type="submit" class="button" name="monsterpay" value="'. JText::_('MYMUSE_PAY_AT_MONSTERPAY').'">
		</div>
		</form>
		';
		
		return $string;
	}
	
	/**
	 * notify
	 * catch the IPN post from Monsterpay, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$mainframe &= JFactory::getApplication();

		if(!isset($_REQUEST['tnxid'])){
			//wasn't monsterpay
			
			if($params->get('my_debug')){
				$debug .= "Was not Monsterpay. \n";
				$debug .= "-------END-------\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}
		$date = date('Y-m-d h:i:s');
		
		
		if($params->get('my_debug')){
			$debug = "#####################\nMonsterpay notify PLUGIN\n";
			$debug .= $date."\n";
			$debug .= "Received from Monsterpay:\n";
			$debug .= print_r($_REQUEST, true);
			MyMuseHelper::logMessage( $debug  ); 
		}
		
		JPluginHelper::importPlugin('mymuse');
		
		
		$result = array();
		$result['plugin'] 				= "Monsterpay";
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
		
		
		//missing checksum?
		if (empty($_REQUEST['checksum'])) {
			if($params->get('my_debug')){
    			$debug = 'checksum not found.\n';
    			$debug .= "-------END-------\n";
    			MyMuseHelper::logMessage( $debug  );
    			return $result;
			}
  		}
  		//missing parity?
  		if (empty($_REQUEST['parity'])) {
  			if($params->get('my_debug')){
    			$debug = 'parity not found.\n';
    			$debug .= "-------END-------\n";
    			MyMuseHelper::logMessage( $debug  );
    			return $result;
  			}
  		}
		// first step is good
		$result['myorder'] = 1;
		
		// respond to Monsterpay
		if($this->params->get('my_monsterpay_mode') == "test"){
			$merchantid = $this->params->get('my_monsterpay_test_merchantid');
			$username = $this->params->get('my_monsterpay_test_username');
			$password = $this->params->get('my_monsterpay_test_password');
		}else{
			$merchantid = $this->params->get('my_monsterpay_merchantid');
			$username = $this->params->get('my_monsterpay_username');
			$password = $this->params->get('my_monsterpay_password');
		}

        $replyToMonster = 'Method=order_synchro'.
  		'&identifier='. $merchantid .
  		'&usrname='. $username .
  		'&pwd='. $password .
  		'&tnxid='. $_REQUEST['tnxid'] .
  		'&checksum='. $_REQUEST['checksum'] .
  		'&parity='. $_REQUEST['parity'];
        
        if($params->get('my_debug')){
        	$debug = "1. Sent this to Monsterpay:\n";
        	$debug .= $replyToMonster."\n\n";
        	MyMuseHelper::logMessage( $debug  );
        }
	 	// send $replyToMonster to Monsterpay by utilizing CURL
  		$url = "https://www.monster.com/secure/components/synchro.cfc?wsdl";
  
  		$ch = curl_init(); // initialize curl handle
  		curl_setopt($ch, CURLOPT_URL, $url); // set url
  		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  		curl_setopt($ch, CURLOPT_HEADER, 0);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
  		curl_setopt($ch, CURLOPT_POST, 1); // set POST method
  		curl_setopt($ch, CURLOPT_POSTFIELDS, $replyToMonster); // set Post variable
  		$monster_result = curl_exec($ch); // Perform the POST and get the data returned
  		if (curl_errno($ch)) {
  			$result['error'] = curl_error($ch); // If CURL returns an error, stores it in a variable.
  			if($params->get('my_debug')){
        		$debug = "Curl error:\n";
        		$debug .= $result['error']."\n\n";
        		$debug .= "-------END-------\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
  			return $result;
  		}
  		else {
  			$scurlerror = '';
  			curl_close($ch);
  			$result['message_sent'] = 1;
  		}
  		if (empty($scurlerror)) {
  			
  			//filter result for XML
  			$monster_wddx = trim($monster_result);
  			if(preg_match("/An error occurred while processing your request/",$monster_wddx)){
  				if($params->get('my_debug')){
        			$debug = "Monster wddx Error:\n";
        			$debug .= $monster_wddx."\n\n";
        			MyMuseHelper::logMessage( $debug  );
        		}
        		$result['error'] = "An error occurred while processing your request";
        		return $result;
  			}
  			if(preg_match("/Service Unavailable/",$monster_wddx)){
  				if($params->get('my_debug')){
        			$debug = "Monster wddx Error:\n";
        			$debug .= $monster_wddx."\n\n";
        			MyMuseHelper::logMessage( $debug  );
        		}
        		$result['error'] = "Service Unavailable";
        		return $result;
  			}
  			
  			//$monster_xml = wddx_deserialize($monster_wddx);
  			$order_synchro = simplexml_load_string($monster_xml);

  			if($params->get('my_debug')){
  				$debug = "Reply from Monsterpay:\n";
  				$debug .= $monster_result."\n\n";
        		$debug .= "Order synchro:\n";
        		$debug .= print_r($order_synchro, TRUE)."\n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	if($order_synchro == "" || pregmatch("/Service Unavailable/",$monster_result)){
        		$result['error'] = "Service Unavailable at Monsterpay";
        		return $result;
        	}
        	$result['message_received'] = 1;
        	
        	
  			//tnx details
  			$result['payment_status'] = $order_synchro->outcome->status;
  			$result['txn_id']= $order_synchro->outcome->order->id;
  			$funds_avail = $order_synchro->outcome->order->funds_available;

  			//error details
  			$error_code = $order_synchro->outcome->error_code;
  			$error_desc = $order_synchro->outcome->error_desc;
  			$error_solution = $order_synchro->outcome->error_solution;

  			//seller details
  			$seller_ref = $order_synchro->seller->reference;
  			$seller_email = $order_synchro->seller->username;


  			//buyer details
  			$result['payer_email'] = $order_synchro->buyer->billing_address->email_address;
  			
  			$buyer_ref = $order_synchro->buyer->reference;
  			$buyer_uname = $order_synchro->buyer->username;
  			$buyer_title = $order_synchro->buyer->billing_address->title;
  			$buyer_fname = $order_synchro->buyer->billing_address->firstname;
  			$buyer_lname = $order_synchro->buyer->billing_address->lastname;
  			$buyer_street1 = $order_synchro->buyer->billing_address->street1;
  			$buyer_street2 = $order_synchro->buyer->billing_address->street2;
  			$buyer_city = $order_synchro->buyer->billing_address->city;
  			$buyer_state = $order_synchro->buyer->billing_address->state;
  			$buyer_zip = $order_synchro->buyer->billing_address->zip;
  			$buyer_country = $order_synchro->buyer->billing_address->country;
  			$buyer_cnumber = $order_synchro->buyer->billing_address->contact_number;

  			//payment details
  			$pmt_type = $order_synchro->payment_instrument->type;

  			//financial details
  			$tnx_amount = $order_synchro->financial->amount_total;
  			$currency = $order_synchro->financial->currency;
  			$result['order_number'] = $order_synchro->MerchCustom;

  			//transaction is unsuccessful
  			if ($error_code != '0') {
  				$result['error'] = '<strong>Your order has been Declined.</strong><br><br>';
  				$result['error'] .= 'Sorry '. $buyer_title .' '. $buyer_fname .' '. $buyer_surname .' your payment to '. $seller_email .' was <strong>unsuccesful</strong>.<br><br>';
  				$result['error'] .= ' The order has not been processed<br><br>';
  				$result['error'] .= 'Error Code: '. $error_code .'<br>';
  				$result['error'] .= 'Error Description: '. $error_desc .'<br>';
  				$result['error'] .= 'Error Solution: '. $error_solution .'<br>';
  				if($params->get('my_debug')){
  					$debug = "Order Declined\n";
  					$debug .= 'Error Code: '. $error_code ."\n";
  					$debug .= 'Error Description: '. $error_desc ."\n";
  					$debug .= 'Error Solution: '. $error_solution ."\n";
  					$debug .= "-------END-------\n";
  					MyMuseHelper::logMessage( $debug  );
  				}
  				return $result;
  			}
  			else 
  			{ //end transaction is unsuccessful
 		     	//transaction is successful

      			$result['tnx_amount'] = $tnx_amount/100; //returned amount is without decimal


  			} //end tnx successful

  		}
  		else { // END - [if (empty($scurlerror))]
  			$result['error'] = 'There was an error in the communication with Monsterpay. No confirmation reply from their server. ';
  			if($params->get('my_debug')){
  				$debug = $result['error']."\n\n";
  				$debug .= "-------END-------\n";
  				MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
  		}
        
         
       // Get the Order Details from the database
        if($result['order_number']){
        	$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$result['order_number']."'";
        	$date = date('Y-m-d h:i:s');
        	$debug .= "$date  4.1 $query \n\n";
        	$db	= & JFactory::getDBO();
        	$db->setQuery($query);
        	if(!$this_order = $db->loadObject()){
        		
        		if($params->get('my_debug')){
        			$debug .= "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";
        			$debug .= "-------END-------\n";
        			MyMuseHelper::logMessage( $debug  );
        		}
        		$result['error'] = "5. !!!!Error no order object: ".$db->_errorMsg."\n\n";;
        		return $result;
        	}else{
        		
        		// update the payment status
        		
        		if($params->get('my_debug')){
        			$debug .= "4.2 Order found ".$result['order_number']."\n\n";
        			MyMuseHelper::logMessage( $debug  );
        		}
        		$result['order_found']  = 1;
        		$result['order_id'] 	= $this_order->id;
        		$result['order_completed'] = 1;

        	}
        }else{
        	$debug = "5. !!!!Error no order number: \n\n";
        	$debug .= "-------END-------\n";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$result['error'] = "5. !!!!Error no order number: \n\n";;
        	return $result;
        }
        
        if ($result['payment_status'] == 'Complete') {
        	//order was verified!
        	$debug = "$date  4.3 order COMPLETE at Monsterpay\n\n";
        	$result['order_verified'] = 1;
        	MyMuseHelper::orderStatusUpdate($result['order_id'] , "C");
        	$debug .= "$date 5. order updated to Completed in DB\n\n";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
        	
        }elseif ($result['payment_status'] == 'Pending') {
        	//pending
        	$debug = "$date 4. Order still PENDING at Monsterpay\n\n";
        	$debug .= "-------END-------\n";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			$result['error'] = "$date Order still PENDING at Monsterpay\n\n";
  			return $result;
  			
        }elseif ($result['payment_status'] == 'Declined') {
        	//cancelled
        	$debug .= "$date 4. Order Declined at Monsterpay\n\n";
        	$debug .= "-------END-------\n";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
  			MyMuseHelper::orderStatusUpdate($result['order_id'] , "X");
  			$result['error'] = "$date Order DECLINED at Monsterpay\n\n";
  			return $result;
  			
        }

        
  		if($params->get('my_debug')){
  			$date = date('Y-m-d h:i:s');
        	$debug .= "$date Finished talking to Monsterpay \n\n";
			$debug .= "-------END-------\n";
        	MyMuseHelper::logMessage( $debug  );
  		}
  		$result['redirect'] = JURI::base()."index.php&option=com_mymuse&task=thankyou";
        return $result;

	}

}


/**
* Replacement function if wddx_deserialize does not exist. 
*/
if (!function_exists('wddx_deserialize')) {
  // Clone implementation of wddx_deserialize
  function wddx_deserialize($xmlpacket) {
    if ($xmlpacket instanceof SimpleXMLElement) {
      if (!empty($xmlpacket->struct)) {
        $struct = array();
        foreach ($xmlpacket->xpath("struct/var") as $var) {
          if (!empty($var["name"])) {
            $key = (string) $var["name"];
            $struct[$key] = wddx_deserialize($var);
          }
        }
        return $struct;
      }
      else if (!empty($xmlpacket->array)) { //jEdit php parser thinks this is an error, maybe because of the use of 'array' name as an object variable?
        $array = array();
        foreach ($xmlpacket->xpath("array/*") as $var) {
          array_push($array, wddx_deserialize($var));
        }
        return $array;
      } 
      else if (!empty($xmlpacket->string)) {
        return (string) $xmlpacket->string;
      } 
      else if (!empty($xmlpacket->number)) {
        return (int) $xmlpacket->number;
      } 
      else {
        if (is_numeric((string) $xmlpacket)) {
          return (int) $xmlpacket;
        } 
        else {
          return (string) $xmlpacket;
        }
      }
    }
    else {
      $sxe = simplexml_load_string($xmlpacket);
      $datanode = $sxe->xpath("/wddxPacket[@version='1.0']/data");
      return wddx_deserialize($datanode[0]);
    }
  }
}


?>