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
		
		
		$task		= JRequest::getVar('task', '');
		if($task == "notify"){
			$this->notify();
			exit;
		}
		
		
		if($task == "coupon"){
			parent::display("coupon");
		}
		
		$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
		$MyMuseCart 	=& MyMuse::getObject('cart','helpers');
		$cart 			=& $MyMuseCart->cart;

		$MyMuseStore	=& MyMuse::getObject('store','models');
		
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		=& $MyMuseShopper->getShopper();

		$user			=& JFactory::getUser();
		$document		=& JFactory::getDocument();
		$dispatcher		=& JDispatcher::getInstance();

		
		$document->setTitle( JText::_('MYMUSE_SHOPPING_CART') );
	
		
		$this->Itemid = JRequest::getVar("Itemid",'');
		
		$this->assignRef('user'  , $user);
		$this->assignRef('params', $params);
		$this->assignRef('task', $task);
		$this->assignRef('shopper', $shopper);
		$this->assignRef('store', $MyMuseStore->_store);

		$heading 			= '';
		$footer 			= '';
		$edit 				= true;
		$currency 			= MyMuseHelper::getCurrency($MyMuseStore->_store->currency);


		// set the heading for the top of page
		// find the order attached to the shopper object, or build it from session cart
		switch ($task)
		{
				
			case "checkout":
				$order 		= $MyMuseCart->buildOrder( $edit );
				$heading 	= Jtext::_('MYMUSE_CHECKOUT');
				$message 	= Jtext::_('MYMUSE_MAKE_ANY_FINAL_CHANGES');
				$order->show_checkout = 0;
				$order->show_summary  = 0;
				break;
				
			case "shipping":
				$order 		= $MyMuseCart->buildOrder( $edit );
			
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
					$order 		= $MyMuseCheckout->getOrder($MyMuseShopper->order->id);
					if(isset($MyMuseShopper->order->id) && $order->order_total == 0.00){
						$order 		= $MyMuseCheckout->getOrder($MyMuseShopper->order->id);
						$heading 	= Jtext::_('MYMUSE_CONFIRM');
						$message 	= Jtext::_('MYMUSE_ACCEPT_ORDER');
						$order->show_checkout = 0;
						$order->show_summary  = 0;
						$free = 1;
					}elseif(isset($MyMuseShopper->order->id)){
						
						$heading 	= Jtext::_('MYMUSE_CONFIRM');
						$message 	= Jtext::_('MYMUSE_CHOOSE_PAYMENT_METHOD');
							
						$order->show_checkout = 0;
						$order->show_summary  = 0;
					}
				}else{
					// this is the after payment option
					$heading 	= Jtext::_('MYMUSE_CONFIRM');
					$message 	= Jtext::_('MYMUSE_CHOOSE_PAYMENT_METHOD');
					$order 		= $MyMuseCart->buildOrder( 0 );
					$order->order_number 		= session_id();
					//save the faux order number in the session
					$session = &JFactory::getSession();
					$session->set("order_number",$order->order_number);
					
					$order->show_checkout = 0;
					$order->show_summary  = 0;
				}
				break;
				
			case "makepayment":
				$order 		= $MyMuseShopper->order;
				$currency 	= $order->order_currency;
				$edit 		= false;
				$heading 	= Jtext::_('MYMUSE_THANK_YOU');
				$message 	= Jtext::_('MYMUSE_WE_HAVE_RECEIVED_YOUR_ORDER');
				$order->show_checkout = 0;
				$order->show_summary  = 1;
				break;
				
			case "vieworder":
				$st 		= JRequest::getVar('st', '');
				$order 		= $MyMuseShopper->order;
				$order->waited = 0;

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
				$message 	= Jtext::_('MYMUSE_HERE_IS_YOUR_ORDER');
				if($order->downloadable && $order->order_status == "C"){
					$footer .= "<br />".$order->downloadlink;
				}
				$order->show_checkout = 0;
				$order->show_summary  = 1;
				break;
				
			case "paycancel":
				$edit 		= false;
				$order 		= $MyMuseCheckout->getOrder($MyMuseShopper->order->id);
				$heading 	= Jtext::_('MYMUSE_CONFIRM');
				$message 	= Jtext::_('MYMUSE_PAY_CANCEL');
				$order->show_checkout = 0;
				$order->show_summary  = 0;
				break;
				
			default:
				$order 		= $MyMuseCart->buildOrder( $edit );
				$order->show_checkout = 1;
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
		
	
		// show the heading
		if($heading){
			$this->assignRef('heading', $heading);
			$this->assignRef('message', $message);
			parent::display('checkout_header');
		}
		
		// do we need an order summary? Only if we already have a saved order
		if($task == "makepayment" || $task == "vieworder"){
			parent::display('order_summary');
		}

		// display the cart part!
		parent::display(); 

		//display the shopper info, if we have one
		if($heading && $user->get('id') > 0 && $user->get('name') != "Guest Buyer"){
			parent::display("shopper_info"); //
		}
		

		if($task == "checkout"){
			if($params->get('my_use_shipping') && $order->need_shipping){
				$task = "shipping";
				$button = JText::_("MYMUSE_SHIPPING");
			}else{
				$task = "confirm";
				$button  = JText::_("MYMUSE_CONFIRM");
			}
			$this->assignRef('button', $button);
			parent::display("next_form");
			
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
			 
			parent::display("shipping_form");
			
		}elseif($task == "confirm" || $task == "paycancel" || 
		($task == "vieworder" && $this->order->order_status == "P") ){
			
			if(isset($free) && $free == 1){
				$task = "thankyou";
				$button = JText::_("MYMUSE_ACCEPT");
				$this->assignRef('button', $button);
				parent::display("next_form");
			}
			
			elseif($params->get('my_shop_test')){
				$task = "makepayment";
				$button = JText::_("MYMUSE_TEST_STORE");
				$this->assignRef('button', $button);
				parent::display("next_form");
			}
			
			else{
				
				 /* payment plugins *///save the order number in the session
				$session = &JFactory::getSession();
				$session->set("order_number",$this->order->order_number);

				JPluginHelper::importPlugin('mymuse');
				$results = $dispatcher->trigger('onBeforeMyMusePayment', 
				array($this->shopper, $this->store, $this->order, $params, $this->Itemid) );
				$this->assignRef('results', $results);
				parent::display("payment_form");

			}
		}
	
		// show the footer
		if($footer){
			$this->assignRef('footer', $footer);
			parent::display('checkout_footer');
		}
	

	}
	
	/**
	 * notify
	 * catch the post from whatever payment processor, return required responses, update orders and do mailouts
	 * 
	 */
	function notify()
	{
		ini_set('log_errors', 1);
		ini_set('error_log', JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'php_error' );
		
		$params = MyMuseHelper::getParams();

		$date = date('Y-m-d h:i:s');
        if($params->get('my_debug')){
            $debug = $date."\n#####################\nCART VIEW NOTIFY FUNCTION\n";
            MyMuseHelper::logMessage( $debug  );
        }
		$result = array();
	
		// see if any plugins wants to deal with notification
		// plugin should run MyMuseHelper::orderStatusUpdate
		$dispatcher		=& JDispatcher::getInstance();
     	$results 		= $dispatcher->trigger('onMyMuseNotify', array($params) );
     	foreach($results as $r){
            if($params->get('my_debug')){
     			$debug .= print_r( $r, true );
     			$debug .= "\n\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
     		if($r['myorder']){
     			$result = $r;
     		}
     	}
     	
     	if(!count($result)){
     		
     		if($params->get('my_debug')){
     			$debug .= "$date Did not get a result!\n";
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


        $debug = "$date Making response emails \n";
        	
        //Make message
        if(!$result['message_sent'] || !$result['message_received']){

        	$debug .= $result['plugin'].": Notify Fatal Error\n\n";
        	
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

        	$debug .= "$date ".$result['plugin'].": Order was not VERIFIED \n\n";
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

        	$debug .= "$date ".$result['plugin'].": Order was not found \n\n";
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

        	$debug .= "$date ".$result['plugin'].": Order was not completed: ".$result['payment_status']." \n\n";
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

        	$debug .= "$date Making cust emails, logging payment \n\n";
        	if($params->get('my_debug')){
        		MyMuseHelper::logMessage( $debug  );
        	}

        	//make the email and send to customer
        	$db	= & JFactory::getDBO();
        	$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
        	$MyMuseShopper  =& MyMuse::getObject('shopper','models');
			$query = "SELECT user_id FROM `#__mymuse_order` WHERE `order_number`='".$result['order_number']."'";
        	$db->setQuery($query);
        	$user_id = $db->loadResult();
        	$order 			= $MyMuseCheckout->getOrder($result['order_id']);
        	$shopper 		=& $MyMuseShopper->getShopperByUser($user_id );
        	$user 			=& JFactory::getUser($user_id);
        	$currency 		= $order->order_currency;
        	$heading 		= Jtext::_('MYMUSE_THANK_YOU');
        	$message 		= Jtext::_('MYMUSE_HERE_IS_YOUR_ORDER');
            
            if($order->notes && $params->get('my_registration') == "no_reg" ){
                $accparams = new JParameter( $order->notes);
                $user->set('email',$accparams->get('email'));
                $user->set('name',$accparams->get('first_name')." ".$accparams->get('last_name'));
                $shopper->email         = $accparams->get('email');
                $shopper->first_name    = $accparams->get('first_name');
                $shopper->last_name     = $accparams->get('last_name');
                $shopper->country       = $accparams->get('country');
                $shopper->state         = $accparams->get('state');
            }
            $user_email 	= $user->email;

        	$this->assignRef('user'  , $user);
        	$this->assignRef('params', $params);
        	$this->assignRef('task', $task);
        	$this->assignRef('shopper', $shopper);
        	$this->assignRef('store', $MyMuseStore->_store);
        	$this->assignRef('order', $order);
        	$this->assignRef('currency', $currency);
        	$this->assignRef('heading', $heading);
        	$this->assignRef('message', $message);


        	$subject = Jtext::_('MYMUSE_ORDER')." ".$result['payment_status']." ".$store->title;
        	$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
        	$download_header = '';

        	$debug .= "Downloadable = ".$order->downloadable."\n";

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
        	$recipient = array($user_email, $mailfrom);
        	if($params->get('my_cc_webmaster')){
        		$recipient = array($user_email, $mailfrom, $params->get('my_webmaster'));
        	}
        	$mailer->addRecipient($recipient);
        	$mailer->setSubject($subject);
        	$mailer->setBody($message);
        	$send = $mailer->Send();
        	
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
        		$debug .= "!!Log Payment Error: ".$MyMuseHelper->getError()."\n\n";
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
        	$mailer->setSubject($subject);
        	$mailer->setBody($message);
        	$send = $mailer->Send();
        	
        	
        	// update stock
        	if ($params->get('my_use_stock')) {
        		for($i = 0; $i < count($order->items); $i++) {
        			if(@$order->items[$i]->coupon_id){ continue; }
        			 
        			if($order->items[$i]->product->product_physical){
        				if (!MyMuseHelper::updateStock($order->items[$i]->product->id, $order->items[$i]->quantity)) {
        					$db= JFactory::getDBO();
        					
        					
        					$debug .= "Could not update stock\n".$db->getErrorMsg()."\n";
        				}
        				$debug .= " Subtracted ".$order->items[$i]->quantity. " From ".$order->items[$i]->product->title."\n";
        			}
        		}
        	}else{
        		$debug .= "params->get('my_use_stock') was ".$params->get('my_use_stock')."\n";
        	}
     	
     	
        }
	   
        

	    

  		if($params->get('my_debug')){
            $debug .= "-------END-------";
        	MyMuseHelper::logMessage( $debug  );
  		}
        if(isset($result['redirect']) && $result['redirect'] != ""){
        	header( 'Location: '.$result['redirect'] ) ;
        }

		exit;
			
		
	}
}
?>