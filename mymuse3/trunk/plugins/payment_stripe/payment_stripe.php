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




require_once(__DIR__.'/vendor/autoload.php');


class plgMymusePayment_Stripe extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	var $stripe = array();
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		$this->plgMyMusePayment_Stripe($subject, $config);
	}
	
	function plgMyMusePayment_Stripe(&$subject, $config)  {
		parent::__construct($subject, $config);
		//JHtml::_('script','https://js.stripe.com/v3/', false, true, false, false);

		$this->stripe = array(
				"secret_key"      => $this->params->get('my_stripe_private_key'),
				"publishable_key" => $this->params->get('my_stripe_public_key')
		);
		
		\Stripe\Stripe::setApiKey($this->stripe['secret_key']);

	}
	
	
	

	/**
	 * Stripe Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		
		$document = JFactory::getDocument();
		//elements can accept options: font and local
		$js = 'var elements = stripe.elements();';
		$js .= 'stripe.createToken(card).then(function(result) {
			// handle result.error or result.token
		});
		stripe.createToken';

		//$document->addScriptDeclaration($js);
		//https://api.stripe.com
		
		$mainframe 	= JFactory::getApplication();
		$db			= JFactory::getDBO();
		if(isset($shopper->profile['country'])){
			// Stripe wants the country_2_code
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
		$shopper->country = isset($shopper->profile['country'])? $shopper->profile['country'] : '';

		
		if(!$shopper->first_name){
			@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
			if($shopper->last_name = ""){
				$shopper->last_name = $shopper->first_name;
			}
		}
		
		//does this order have reservation fees? How much is the "Pay_now" field?
		if(isset($order->pay_now) && $order->pay_now > 0 && $order->pay_now < $order->order_subtotal){
			$order->idx = 1;
			$order->order_subtotal = $order->pay_now;
			$order->items[0]->product_item_price = sprintf("%.2f", $order->pay_now);
			$order->items[0]->quantity = 1;
			$order->items[0]->title = JText::_('MYMUSE_REGISTRATION_FEE');
			$order->tax_total = 0.00;
		}
		//description
		$desc = '';
		for ($i=0;$i<$order->idx;$i++) {
			if(isset($order->items[$i]->title) && $order->items[$i]->title != ''){
				$desc .= preg_replace('/"/','',$order->items[$i]->title);
				if($params->get('my_show_sku') || $params->get('my_saveorder') == "after"){
					$desc .= ' : '.$order->items[$i]->product_sku;
				}
				$desc .= " : ".$order->items[$i]->quantity;
				$desc .= " : ". $order->items[$i]->product_item_price;

			}
		}

		$currency 		= $store->currency;
		$payer_email 	= $shopper->email;
		$amount 		= preg_replace("/\./","",sprintf("%.2f", $order->order_subtotal));
		
		$string = '
		<form action="'. JURI::root().'index.php?option=com_mymuse&task=notify" method="post">
  		<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          	data-key="'.$this->stripe['publishable_key'].'"
          	data-description="'.$desc.'"
          	data-currency="'.$currency.'"
          	data-amount="'.$amount.'"
          	data-locale="auto"
          			';
		if($params->get('my_stripe_need_address', 0)){
          	$string .= 'data-billing-address="true" ';
		}
		if($params->get('my_stripe_need_shipping', 0)){
			$string .= 'data-shipping-address="true" ';
		}
		if($params->get('my_stripe_need_zip_code', 0)){
			$string .= 'data-zip-code="true" ';
		}
		$string .= '
          	data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
			data-label="'.JText::_('MYMUSE_PAY_AT_STRIPE').'"
          	data-email="'.$payer_email.'"
          ></script>
          			
          <input type="hidden" name="amount" 	value="'.sprintf("%.2f", $order->order_subtotal).'" />
          <input type="hidden" name="invoice"   value="'. $order->order_number.'" />
          <input type="hidden" name="currency"  value="'. $store->currency.'" />
          		';
		//coupon discount
		if(isset($order->coupon_discount) && $order->coupon_discount > 0){
			$string .= '
			<input type="hidden" name="discount_amount_cart"
			value="'. sprintf("%01.2f", $order->coupon_discount).'" />
			';
		}
		//plugin discount
		if(isset($order->discount) && $order->discount > 0){
			$string .= '
			<input type="hidden" name="discount_amount_cart"
			value="'. sprintf("%01.2f", $order->discount ).'" />
			';
		}
		
		$string .= '</form>';

		return $string;
	}

	/**
	 * notify
	 * catch the IPN post from Stripe, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$mainframe 	= JFactory::getApplication();
		

		/**
		 * POST Array
Array
(
    [amount] => 20.00
    [invoice] => d805e908185501f317a805ab9e89306e
    [currency] => EUR
    [stripeToken] => tok_1AqVJkDvF1hKJ1SbWkWGdnWG
    [stripeTokenType] => card
    [stripeEmail] => info@arboreta.ca
    
    [stripeBillingName] => Gord Fisch
    [stripeBillingAddressCountry] => Canada
    [stripeBillingAddressCountryCode] => CA
    [stripeBillingAddressZip] => H4V 2K1
    [stripeBillingAddressLine1] => 5382 King Edward
    [stripeBillingAddressCity] => Montreal
    [stripeBillingAddressState] => QC
    [stripeShippingName] => Gord Fisch
    [stripeShippingAddressCountry] => Canada
    [stripeShippingAddressCountryCode] => CA
    [stripeShippingAddressZip] => H4V 2K1
    [stripeShippingAddressLine1] => 5382 King Edward
    [stripeShippingAddressCity] => Montreal
    [stripeShippingAddressState] => QC
)
*/

		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nStripe notify PLUGIN\n";

		$result = array();
		$result['plugin'] 				= "payment_stripe";
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

		if(!isset($_POST['stripeToken'])){
			//wasn't stripe
			$debug .= "Was not Stripe. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				
        		MyMuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}else{
			$debug .= print_r($_POST, true);
			if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
  			}
		}
		//we are in stipe
		$result['myorder'] = 1;
		$result['token']  = $_POST['stripeToken'];
		
		
		//get order
		if(!isset($_POST['invoice'])){
			//missing invoice
			$debug .= "Missing Invoice! \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
			$result['error'] = "Missing Invoice!";
			return $result;
		}

		//get amount
		if(!isset($_POST['amount'])){
			//missing invoice
			$debug .= "MissingAmount! \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
			$result['error'] = "Missing Amount!";
			return $result;
		}else{
			$amount = preg_replace("/\./","", $_POST['amount']);
		}

		$result['order_number'] = $_POST['invoice'];
		$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$result['order_number']."'";
		$date = date('Y-m-d h:i:s');
		$debug = "$date  1.1 $query \n\n";
		
		$db->setQuery($query);
		if(!$this_order = $db->loadObject()){
			$debug .= "1.2 !!!!Error no order object: ".$db->_errorMsg."\n\n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( $debug  );
			}
			$result['error'] = "Could not find order!";
			return $result;
		}
		$result['order_id'] 	= $this_order->id;
		$result['payer_email'] 	= urldecode($_POST['stripeEmail']);

		
		
		//create the customer
		try {
			$customer = \Stripe\Customer::create(array(
				'email' => $result['payer_email'],
				'source'  => $result['token']
			));
		} catch(\Stripe\Error\Card $e) {
			// Since it's a decline, \Stripe\Error\Card will be caught
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] = 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\RateLimit $e) {
			$result['error'] = "create the customer: Too many requests made to the API too quickly\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\InvalidRequest $e) {
			// Invalid parameters were supplied to Stripe's API
			$result['error'] = "create the customer: Invalid parameters were supplied to Stripe's API\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\Authentication $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$result['error'] = "create the customer: Authentication with Stripe's API failed\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\ApiConnection $e) {
			// Network communication with Stripe failed
			$result['error'] = "create the customer: Network communication with Stripe failed\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\Base $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			$result['error'] = "create the customer: Stripe had an unknown error\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (Exception $e) {
			$result['error'] = "create the customer: We had an unknown error\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		}
		
		
		//make the charge
		try {
			$charge = \Stripe\Charge::create(array(
				'customer' => $customer->id,
				'amount'   => $amount,
				'currency' => $_POST['currency']
			));
		} catch(\Stripe\Error\Card $e) {
			// Since it's a decline, \Stripe\Error\Card will be caught
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] = 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\RateLimit $e) {
			$result['error'] = "Charge: Too many requests made to the API too quickly\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\InvalidRequest $e) {
			// Invalid parameters were supplied to Stripe's API
			$result['error'] = "Charge: Invalid parameters were supplied to Stripe's API\n";
			$result['error'] .= $this->makeErrorMessage($e);
			return $result;
		} catch (\Stripe\Error\Authentication $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$result['error'] = "Charge: Authentication with Stripe's API failed\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (\Stripe\Error\ApiConnection $e) {
			// Network communication with Stripe failed
			$result['error'] = "Charge: Network communication with Stripe failed\n";
			$result['error'] .= $this->makeErrorMessage($e);
			return $result;
		} catch (\Stripe\Error\Base $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			$result['error'] = "Charge: Stripe had an unknown error\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		} catch (Exception $e) {
			$result['error'] = "Charge: We had an unknown error\n";
			$body = $e->getJsonBody();
			$err = $body ['error'];
			
			$result['error'] .= 'Status is:' . $e->getHttpStatus () . "\n";
			if (isset ( $err ['type'] )) {
				$result['error'] .= 'Type is:' . $err ['type'] . "\n";
			}
			if (isset ( $err ['code'] )) {
				$result['error'] .= 'Code is:' . $err ['code'] . "\n";
			}
			if (isset ( $err ['param'] )) {
				$result['error'] .= 'Param is:' . $err ['param'] . "\n";
			}
			if (isset ( $err ['message'] )) {
				$result['error'] .= 'Message is:' . $err ['message'] . "\n";
			}
			return $result;
		}		
		echo "charge-paid = ".$charge['paid'];

		exit;
		//$charge['paid'] = true on success
		if($charge['paid']){
			$result ['payment_status'] = 'Complete';
			$result ['txn_id'] = $charge ['balance_transaction'];
			$result ['amountin'] = $_POST ['amount'];
			$result ['currency'] = $_POST ['mcurrency'];
			$result ['transaction_id'] = $charge ['balance_transaction'];
			$result ['transaction_status'] = 1;
			$result ['description'] = @$_POST ['note'];
		}

        			// update the payment status
        			$result['order_found']  = 1;
        			$result['order_id'] 	= $this_order->id;
        			$result['order_number'] = $this_order->order_number;
        			if (preg_match ("/Completed/", $result['payment_status'])) {
        				$MyMuseHelper = new MyMuseHelper();
                		$MyMuseHelper->orderStatusUpdate($result['order_id'] , "C");
                		$date = date('Y-m-d h:i:s');
                		$debug .= "$date 5. order COMPLETED at Stripe, update in DB\n\n";
                		$result['order_completed'] = 1;
        			}else{
        				// not completed, set order status to 
        				
                		$date = date('Y-m-d h:i:s');
                		$debug .= "$date 5. order COMPLETED at Stripe, but still has status".$result['payment_status']."\n\n";
                		$result['order_completed'] = 1;
        			}
      
        		if($params->get('my_debug')){
        			MyMuseHelper::logMessage( $debug  );
        		}



        $date = date('Y-m-d h:i:s');
        $debug .= "$date Finished talking to Stripe \n\n";
		$debug .= "-------END PLUGIN-------";
  		if($params->get('my_debug')){
        	MyMuseHelper::logMessage( $debug  );
  		}
        return $result;

	}
	
	function makeErrorMessage($e){
		$body = $e->getJsonBody();
		$err  = $body['error'];
		
		$error = 'Status is:' . $e->getHttpStatus() . "\n";
		if(isset($err['type'])){
			$error .= 'Type is:' . $err['type'] . "\n";
		}
		if(isset($err['code'])){
			$error .= 'Code is:' . $err['code'] . "\n";
		}
		if(isset($err['param'])){
			$error .= 'Param is:' . $err['param'] . "\n";
		}
		if(isset($err['message'])){
			$error .= 'Message is:' . $err['message'] . "\n";
		}
		return $error;
		
	}
	
	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "payment_stripe:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		}
		return $email_msg;
	
	}
}
?>