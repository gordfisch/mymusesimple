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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class myMuseViewCart extends JViewLegacy
{
	function __construct()       {
                parent::__construct(); 
                parent::setLayout('cart');       
        }
        
	function display($tpl = null)
	{

		$params = MyMuseHelper::getParams();
		$jinput = JFactory::getApplication()->input;
		
		$this->task = $task	= $jinput->get('task', '', 'CMD');
		
		if($task == "notify"){
			$this->notify();
			exit;
		}
		
		if($task == "coupon"){
			parent::display("coupon");
		}
		
		$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
		$MyMuseCart 	=& MyMuse::getObject('cart','helpers');
		$cart 			= $MyMuseCart->cart;
		$MyMuseStore	=& MyMuse::getObject('store','models');
		
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		= $MyMuseShopper->getShopper();

		$user			= JFactory::getUser();
		$document		= JFactory::getDocument();
		$dispatcher		= JDispatcher::getInstance();


		$document->setTitle( JText::_('MYMUSE_SHOPPING_CART') );
		$this->Itemid = $jinput->get("Itemid",'');
		
		$this->assignRef('user'  , $user);
		$this->assignRef('params', $params);
		$this->assignRef('task', $task);
		$this->assignRef('shopper', $shopper);
		$this->assignRef('store', $MyMuseStore->_store);

		$heading 			= '';
		$message 			= '';
		$footer 			= '';
		$edit 				= true;
		$currency 			= MyMuseHelper::getCurrency($MyMuseStore->_store->currency);

		// set the heading for the top of page
		// find the order attached to the shopper object, or build it from session cart
		switch ($task)
		{
				
			case "checkout":
				$this->order = $order 		= $MyMuseCart->buildOrder( $edit );
				$heading 	= Jtext::_('MYMUSE_CHECKOUT');
				$message 	= Jtext::_('MYMUSE_MAKE_ANY_FINAL_CHANGES');
				if(isset($order) && is_object($order)){
					$order->show_checkout = 0;
					$order->show_summary  = 0;
				}
				break;
				
			case "shipping":
				$this->order = $order 		= $MyMuseCart->buildOrder( $edit );
				if(isset($order->need_shipping) && $order->need_shipping){
					$heading 	= Jtext::_('MYMUSE_SHIPPING');
					$message 	= Jtext::_('MYMUSE_CHOOSE_SHIPPING_METHOD');
				}else{
					$heading 	= Jtext::_('MYMUSE_SHIPPING');
					$message 	= Jtext::_('MYMUSE_NO_SHIPPING_NEEDED');
				}

				$order->show_checkout = 0;
				$order->show_summary  = 0;
				break;
				
			case "confirm":

				$edit 		= false;
				if($params->get('my_saveorder') != "after" && isset($MyMuseShopper->order->id)){
					$this->order = $order 		= $MyMuseCheckout->getOrder($MyMuseShopper->order->id);
					if($order->order_total == 0.00){
						$heading 	= Jtext::_('MYMUSE_CONFIRM');
						$message 	= Jtext::_('MYMUSE_ACCEPT_ORDER');
						$order->show_checkout = 0;
						$order->show_summary  = 0;
						$free = 1;
					}else{
						$heading 	= Jtext::_('MYMUSE_CONFIRM');
						$message 	= Jtext::_('MYMUSE_CHOOSE_PAYMENT_METHOD');
						$order->show_checkout = 0;
						$order->show_summary  = 0;
					}
				}else{
					// this is the after payment option
					$heading 	= Jtext::_('MYMUSE_CONFIRM');
					$message 	= Jtext::_('MYMUSE_CHOOSE_PAYMENT_METHOD');
					$this->order = $order 		= $MyMuseCart->buildOrder( 0 );

					/**
					$order->order_number 		= session_id();
					//save the faux order number in the session
					$session = JFactory::getSession();
					$session->set("order_number",$order->order_number);
					*/
					
					if($cart['idx'] > 0){
						$order->show_checkout = 0;
						$order->show_summary  = 0;
					}
				}
				break;
				
			case "makepayment":
				$this->order = $order 	= $MyMuseShopper->order;
				$currency 	= $order->order_currency;
				$edit 		= false;
				$heading 	= Jtext::_('MYMUSE_THANK_YOU');
				$message 	= Jtext::_('MYMUSE_WE_HAVE_RECEIVED_YOUR_ORDER');
				$order->show_checkout = 0;
				$order->show_summary  = 1;
				break;
				
			case "vieworder":
				$st 			= $jinput->get('st', '');
				$this->order 	= $order 		= $MyMuseShopper->order;
				$order->waited 	= 0;

				if($st === "Completed" && $order->order_status != "C"){
					// waiting for IPN
					sleep(3);
					$order = MyMuseCheckout::getOrder($order->id);
					$order->waited = 1;
				}
				if($st === "Completed" && $order->order_status != "C"){
					// waiting for IPN
					sleep(3);
					$order = MyMuseCheckout::getOrder($order->id);
					$order->waited = 2;
				}

				$currency 	= $order->order_currency;
				$edit 		= false;
				$heading 	= Jtext::_('MYMUSE_THANK_YOU');
				
				if($order->downloadable && $order->order_status == "C"){
					$message  .= $order->downloadlink;
				}else{
					$message   = Jtext::_('MYMUSE_HERE_IS_YOUR_ORDER');
				}
				$order->show_checkout = 0;
				$order->show_summary  = 1;
				break;
				
			case "paycancel":
				$edit 		= false;
				if($params->get('my_saveorder') == "after"){
					$this->order = $order 		= $MyMuseCart->buildOrder( $edit );
				}else{
					$this->order = $order 		= $MyMuseCheckout->getOrder($MyMuseShopper->order->id);
				}
				$heading 	= Jtext::_('MYMUSE_CONFIRM');
				$message 	= Jtext::_('MYMUSE_PAY_CANCEL');
				$order->show_checkout = 1;
				$order->show_summary  = 1;
				break;
				
			default:
				if($cart['idx'] > 0){
					$this->order = $order 		= $MyMuseCart->buildOrder( $edit );
					$order->show_checkout = 1;
					$footer = $MyMuseCart->getRecommended();
				}
				break;
		}
		
		// check for order
		if(!isset($order->items) || !count($order->items)) {
			//Hmm nothing to display...
			parent::display('empty');
			return false;
		}

				
		$this->assignRef('order', $order);
		$this->assignRef('currency', $currency);
		
		//START CAPTURING THE DISPLAY PARTS
		
		// show the heading
		if($heading){
			$this->assignRef('heading', $heading);
			$this->assignRef('message', $message);
			ob_start();
			parent::display('checkout_header');
			$checkout_header = ob_get_contents();
			ob_end_clean();
			$this->assignRef('checkout_header', $checkout_header);
		}
		
		// do we need an order summary? Only if we already have a saved order
		if($task == "makepayment" || $task == "vieworder"){
			ob_start();
			parent::display('order_summary');
			$order_summary = ob_get_contents();
			ob_end_clean();
			$this->assignRef('order_summary', $order_summary);
			
		}

		// display the cart part!
		ob_start();
		parent::display('cart');
		$cart_display = ob_get_contents();
		ob_end_clean();
		$this->assignRef('cart_display', $cart_display);
		
		
		//display the shopper info, if we have one
		if($heading && $user->get('id') > 0 && $user->get('name') != "Guest Buyer"){
			ob_start();
			parent::display('shopper_info'); 
			$shopper_info = ob_get_contents();
			ob_end_clean();
			$this->assignRef('shopper_info', $shopper_info);
		}

		if($task == "checkout"){
			if($params->get('my_use_shipping') && $order->need_shipping){
				$task = "shipping";
				$button = JText::_("MYMUSE_SHIPPING_BUTTON");
			}else{
				$task = "confirm";
				$button  = JText::_("MYMUSE_CONFIRM_BUTTON");
			}
			$this->assignRef('button', $button);
			ob_start();
			parent::display("next_form");
			$next_form = ob_get_contents();
			ob_end_clean();
			$this->assignRef('next_form', $next_form);
			
		}elseif($task== "shipping"){

			JPluginHelper::importPlugin('mymuse');
			$results = $dispatcher->trigger('onListMyMuseShipping', 
			array($this->shopper, $this->store, $this->order, $params) );
            if(isset($results[0])){
            	$res = $results[0];
            }else{
            	$res = array();
            }
         
            
			$this->assignRef('shipMethods', $res);
			$button = JText::_("MYMUSE_SHIPPING_BUTTON");
			$this->assignRef('button', $button);
			ob_start();
			parent::display("shipping_form");
			$shipping_form = ob_get_contents();
			ob_end_clean();
			$this->assignRef('shipping_form', $shipping_form);
			
		}elseif($task == "confirm" || $task == "paycancel" || 
		($task == "vieworder" && $this->order->order_status == "P") ){
			
			if(isset($free) && $free == 1){
				$task = "thankyou";
				$button = JText::_("MYMUSE_ACCEPT");
				$this->assignRef('button', $button);
				ob_start();
				parent::display("next_form");
				$next_form = ob_get_contents();
				ob_end_clean();
				$this->assignRef('thankyou_form', $thankyou_form);
			}
			
			elseif($params->get('my_shop_test')){
				$task = "makepayment";
				$button = JText::_("MYMUSE_TEST_STORE");
				$this->assignRef('button', $button);
				ob_start();
				parent::display("next_form");
				$next_form = ob_get_contents();
				ob_end_clean();
				$this->assignRef('makepayment_form', $makepaymen_form);
			}
			
			else{
				
				 /* payment plugins */
				//save the order number in the session
				if(!isset($order->order_number)){
					$order->order_number = '';
				}
					
				$session = JFactory::getSession();
				$session->set("order_number",$order->order_number);
				
				JPluginHelper::importPlugin('mymuse');
				
				$results = $dispatcher->trigger('onBeforeMyMusePayment', 
				array($this->shopper, $this->store, $this->order, $params, $this->Itemid) );
			
				$this->assignRef('results', $results);
				
				ob_start();
				parent::display("payment_form");
				$payment_form= ob_get_contents();
				ob_end_clean();
				$this->assignRef('payment_form', $payment_form);

			}
		}
	
		// show the footer
		
		if($footer){
			$this->assignRef('footer', $footer);
			
			ob_start();
			parent::display('checkout_footer');
			$checkout_footer = ob_get_contents();
			ob_end_clean();
			$this->assignRef('checkout_footer', $checkout_footer);
			
		}
	
		parent::display();
	}
	
	/**
	 * notify
	 * catch the post from whatever payment processor, return required responses, 
	 * update orders and do mailouts
	 * 
	 */
	function notify()
	{
		ini_set('log_errors', 1);
		ini_set('error_log', JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'php_error' );
		
		$jinput = JFactory::getApplication()->input;
				
		
		$this->Itemid = $jinput->get("Itemid",'');
		$params = MyMuseHelper::getParams();
		

		$date = date('Y-m-d h:i:s');
        if($params->get('my_debug')){
            $debug = $date."\n#####################\nCART VIEW NOTIFY FUNCTION\n";
            MyMuseHelper::logMessage( $debug  );
        }
		$result = array();
	
		// see if any plugins wants to deal with notification
		// plugin should run MyMuseHelper::orderStatusUpdate
		$dispatcher		= JDispatcher::getInstance();
     	$results 		= $dispatcher->trigger('onMyMuseNotify', array($params, $this->Itemid) );
     	foreach($results as $r){
            if($params->get('my_debug')){
     			$debug = "Result from Plugin\n" . print_r( $r, true ). "\n\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
     		if($r['myorder']){
     			$result = $r;
     		}
     	}
     	
     	if(!count($result)){
     		
     		if($params->get('my_debug')){
     			$debug = "$date Did not get a result!\n";
     			$debug .= "-------END-------\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
  			exit;
     	}

		$MyMuseStore	=& MyMuse::getObject('store','models');
        $store = $MyMuseStore->_store;
        $store_params = new JRegistry;
        $store_params->loadString($store->params);
  		
     	// get mailer object
     	$mailer = JFactory::getMailer();
     	$mailer->isHTML(false);
     	$mailer->Encoding = 'base64';
     	// from
     	$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
     	$mailfrom = $params->get('contact_email');
     	$sender = array(
     			$mailfrom,
     			$fromname );
     	$mailer->setSender($sender);
     	//recipient
     	$recipient = $mailfrom;
     	if($params->get('my_cc_webmaster')){
     		$recipient = array($mailfrom, $params->get('my_webmaster'));
     	}
     	$mailer->addRecipient($recipient);

        if($params->get('my_debug')){
        	$debug = "$date Making response emails \n";
        	MyMuseHelper::logMessage( $debug  );
        }
        
        //special for pesapal
        if($result['plugin'] == "payment_pesapal"){
        	$pesapalNotification=$_GET['pesapal_notification_type'];
        	$pesapalTrackingId=$_GET['pesapal_transaction_tracking_id'];
        	$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];
        	$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
        	ob_start();
        	echo $resp;
        	ob_flush();
        	$debug .= "Sent to Pesapal: $resp \n";
        }
        	
        //Make message
        if($result['order_completed'] == "ALREADY_COMPLETED"){
  			if($params->get('my_debug')){
  				$debug = "$date ".$result['plugin'].": Order was already completed: ".$result['payment_status']." \n\n";
  				MyMuseHelper::logMessage( $debug  );
  			}
        }elseif(!$result['message_sent'] || !$result['message_received']){
        	if($params->get('my_debug')){
        		$debug = $result['plugin'].": Notify Fatal Error\n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	
        	$subject = $result['plugin'].": Notify Fatal Error";
            $message = "Hello,
            A fatal error occured while processing a transaction.
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
            

            $subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();

            
        }elseif(!$result['order_verified']){
        	if($params->get('my_debug')){
        		$debug = "$date ".$result['plugin'].": Order was not VERIFIED \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$subject = $result['plugin'].": Order was not VERIFIED";
            $message = "Hello,
            Received a response but it was not VERIFIED.
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
            
            $subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();
            
            
        }elseif(!$result['order_found']){
        	if($params->get('my_debug')){
        		$debug = "$date ".$result['plugin'].": Order was not found \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$subject = $result['plugin'].": Order was not found";
            $message = "Hello,
            Received a response but we could not find the order.
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
            
        	$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();
            
        }elseif(!$result['order_completed']){

        	
        	if($params->get('my_debug')){
        		$debug = "$date ".$result['plugin'].": Order was not completed: ".$result['payment_status']." \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$subject = $result['plugin'].": Order was not completed: ".$result['payment_status'];
            $message = "Hello,
            Received a response but if was not marked completed: ".$result['payment_status']."
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
        	
            $subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();
            
        }else{
        	//all is good!

        	if($params->get('my_debug')){
        		$debug = "$date All is good \n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	//make the email and send to customer
        	$db	= JFactory::getDBO();
        	$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
        	$MyMuseShopper  =& MyMuse::getObject('shopper','models');
			
        	$query = "SELECT user_id FROM `#__mymuse_order` WHERE `order_number`='".$result['order_number']."'";
        	$db->setQuery($query);
        	$user_id = $db->loadResult();
        	
        	$order 			= $MyMuseCheckout->getOrder($result['order_id']);
        	$shopper 		= $MyMuseShopper->getShopperByUser($user_id );
        	$user 			= JFactory::getUser($user_id);
        	$currency 		= $order->order_currency;
        	$heading 		= Jtext::_('MYMUSE_THANK_YOU');
        	$message 		= Jtext::_('MYMUSE_HERE_IS_YOUR_ORDER');
            
            if($order->notes && $params->get('my_registration') == "no_reg" ){
                $accparams = new JRegistry( $order->notes);
                /*
                first_name=Gord
                last_name=Fisch
                email=gord@arboreta.ca
                address1=5380 King Edward
                address2=
                city=Montreal
                country=CA
                region_name=QC
                postal_code=H4V 2K1

                */

                $user->set('email',$accparams->get('email'));
                $user->set('name',$accparams->get('first_name')." ".$accparams->get('last_name'));
                $shopper->email         = $accparams->get('email');
                $shopper->first_name    = $accparams->get('first_name');
                $shopper->last_name     = $accparams->get('last_name');
                $shopper->address1 		= $accparams->get('address1');
                $shopper->address2 		= $accparams->get('address2');
                $shopper->city 		= $accparams->get('city');
                $shopper->postal_code 		= $accparams->get('postal_code');
                $shopper->country       = $accparams->get('country');
                $shopper->region_name         = $accparams->get('region_name');
            }
           
            $user_email 	= $user->email;
			$task = $jinput->get('task','');
        	$this->assignRef('user'  , $user);
        	$this->assignRef('params', $params);
        	$this->assignRef('task', $task);
        	$this->assignRef('shopper', $shopper);
        	$this->assignRef('store', $MyMuseStore->_store);
        	$this->assignRef('order', $order);
        	$this->assignRef('currency', $currency);
        	$this->assignRef('heading', $heading);
        	$this->assignRef('message', $message);


        	$subject = $shopper->first_name." ".$shopper->last_name." ".Jtext::_('MYMUSE_ORDER')." ".$result['payment_status']." ".$store->title;
        	$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
        	$download_header = '';
        	
        	if($params->get('my_debug')){
        		$debug = "$date Downloadable = ".$order->downloadable."\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$contents  = '';
        	
        	//see if there is a message
        	$my_email_msg = $params->get('my_email_msg');
        	$dispatcher		=& JDispatcher::getInstance();
        	if($result['plugin']){
        		JPluginHelper::importPlugin('mymuse',$result['plugin']);
        		$results = $dispatcher->trigger('onAfterMyMusePayment', array() );
        		$pp = $result['plugin'];
        		foreach($results as $res){
        			if(preg_match("/$pp/", $res)){
        				$arr = explode(":",$res);
        				$my_email_msg .= $arr[1];
        			}
        		}
        	}
        	
        	if($params->get('my_debug')){
        		$debug = "$date Extra Email message: $my_email_msg \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}

        	include_once( JPATH_COMPONENT.DS.'templates'.DS.'mail_html_header.php' );
        	

        	$contents  = '';
        	
        	ob_start();
        	parent::display('checkout_header');
        	parent::display('order_summary');
        	parent::display('shopper_info');
        	parent::display();
        	$contents .= ob_get_contents();
        	ob_end_clean();
        	 
        	$message = $header . $order->downloadlink . $contents . $footer;
        	
        	if($params->get('my_debug')){
        		//$debug = "$date Email message: $message \n\n";
        		//MyMuseHelper::logMessage( $debug  );
        	}
        	// email client $user_email, and cc store owner $mailfrom
        	// get mailer object
        	$mailer = JFactory::getMailer();
        	$mailer->isHTML(true);
        	$mailer->Encoding = 'base64';
        	// from
        	$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
        	$mailfrom = $params->get('contact_email');
        	$sender = array(
        			$mailfrom,
        			$fromname );
        	$mailer->setSender($sender);
        	
        	//recipient
        	if($params->get('my_plugin_email')){
        		//cc admin
        		$recipient = array($user_email, $mailfrom);
        	}else{
        		//don't cc admin
        		$recipient = array($user_email);
        	}

        	if($params->get('my_cc_webmaster')){
        		$recipient = array($user_email, $mailfrom, $params->get('my_webmaster'));
        	}
        	$mailer->addRecipient($recipient);
        	$mailer->setSubject($subject);
        	$mailer->setBody($message);
        	$rs = $mailer->Send();
        	
        	if ($rs instanceof Exception){
        		$debug = "Error sending email to $user_email: " . $rs->getError();
        		
        	}elseif (empty($rs)){
        		$debug = "Error sending email to $user_email: return from mailer was empty";
        	} else {
        		$debug = "Mail sent to $user_email";
        	}
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
        	}
        	//$debug .= print_r($mailer, true);
        	//if($params->get('my_debug')){
        	//	MyMuseHelper::logMessage( $debug  );
        	//}
        	
            //$debug .= "user mail = $user_email\n\n";
            //$debug .= $message."\n\n";
            
        	//now log the payment
        	$payment['order_id'] 			= $result['order_id'];
        	$payment['date'] 				= date('Y-m-d h:i:s');
        	$payment['plugin'] 				= $result['plugin'];
        	$payment['institution'] 		= @$result['institution'];
        	$payment['amountin'] 			= $result['amountin'];
        	$payment['currency'] 			= $result['currency'];
        	$payment['rate'] 				= $result['rate'];
        	$payment['fees'] 				= $result['fees'];
        	$payment['transaction_id'] 		= $result['transaction_id'];
        	$payment['transaction_status'] 	= $result['transaction_status'];
        	$payment['description'] 		= $result['description'];
        	 
        	$MyMuseHelper = new MyMuseHelper;
        	if(!$MyMuseHelper->logPayment($payment)){
        		$debug = "$date !!Log Payment Error: ".$MyMuseHelper->getError()."\n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	if($params->get('my_debug')){
        		$debug = "$date Payment logged\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	
        	
        	// mail admin
        	$subject = $result['plugin']." txn on your site";
        	$message = "Hello,\n\n";
        	$message .= $result['plugin']." txn on your site!\n";
        	$message .= "-----------------------------------------------------------\n";
        	$message .= "Transaction ID: ".$result['txn_id']."\n";
        	$message .= "Payer Email: ".$result['payer_email']."\n";
        	$message .= "Order ID: ".$result['order_id']."\n";
        	$message .= "Order Number: ".$result['order_number']."\n";
        	$message .= "Payment Status returned by ".$result['plugin'].": ".$result['payment_status']."\n";
        	
        	
        	
        	if($params->get('my_plugin_email')){
        		$mailer = JFactory::getMailer();
        		$mailer->isHTML(false);
        		$mailer->Encoding = 'base64';
        		// from
        		$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
        		$mailfrom = $params->get('contact_email');
        		$sender = array(
        				$mailfrom,
        				$fromname );
        		$mailer->setSender($sender);
        		//recipient
        		$recipient = $mailfrom;
        		$mailer->addRecipient($recipient);
        		if($params->get('my_cc_webmaster')){
        			$recipient = array($mailfrom, $params->get('my_webmaster'));
        			$mailer->addRecipient($recipient);
        		}
        		
        		$mailer->setSubject($subject);
        		$mailer->setBody($message);
        		$send = $mailer->Send();
        	}
        	
        	
        	
        	// update stock
        	$debug = '';
        	if ($params->get('my_use_stock')) {
        		for($i = 0; $i < count($order->items); $i++) {
        			if(@$order->items[$i]->coupon_id){ continue; }
        			 
        			if($order->items[$i]->product->product_physical){
        				if (!MyMuseHelper::updateStock($order->items[$i]->product->id, $order->items[$i]->quantity)) {
        					$db= JFactory::getDBO();
        					$debug .= "$date Could not update stock\n".$db->getErrorMsg()."\n";
        				}
        				$debug .= "$date Subtracted ".$order->items[$i]->quantity. " From ".$order->items[$i]->product->title."\n";
        			}
        		}
        	}else{
        		$debug .= "$date use_stock was ".$params->get('my_use_stock')."\n";
        	}
        }
	   
  		if($params->get('my_debug')){
            $debug .= "-------END NOTIFY FUNCTION-------";
        	MyMuseHelper::logMessage( $debug  );
  		}
        if(isset($result['redirect']) && $result['redirect'] != ""){
        	header( 'Location: '.$result['redirect'] ) ;
        }

		exit;
			
		
	}
}
?>