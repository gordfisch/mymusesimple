<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */
 
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class MyMuseController extends JControllerLegacy
{
		
	/**
	 * MyMuseStore object
	 *
	 * @var object ref
	 */
	var $MyMuseStore = null;
	
	/**
	 * MyMuseStore store object
	 *
	 * @var object
	 */
	var $store = null;
	
	/**
	 * MyMuseCart object ref
	 *
	 * @var object
	 */
	var $MyMuseCart = null;
	
	/**
	 * MyMuseCheckout object ref
	 *
	 * @var object
	 */
	var $MyMuseCheckout = null;
	
	/**
	 * MyMuseShopper object ref
	 *
	 * @var object
	 */
	var $MyMuseShopper = null;
	
	/**
	 * shopper object ref
	 * subset of MyMuseShopper
	 *
	 * @var object
	 */
	var $shopper = null;
	
	/**
	 * params object ref
	 *
	 * @var object
	 */
	var $params = null;
	
	/**
	 * jinput object
	 *
	 * @var object
	 */
	var $jinput = null;
	
	/**
	 * Custom Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->MyMuseStore		=& MyMuse::getObject('store','models');
		$this->store			= $this->MyMuseStore->getStore();
		$this->params			= MyMuseHelper::getParams();
		$this->MyMuseCart		=& MyMuse::getObject('cart','helpers');
		$this->MyMuseCheckout 	=& MyMuse::getObject('checkout','helpers');
		$this->MyMuseShopper 	=& MyMuse::getObject('shopper','models');
		$this->shopper 			=  $this->MyMuseShopper->getShopper();
		$this->jinput 			= JFactory::getApplication()->input;

		$myview = $this->jinput->get('view', 'store');
		if($myview == "product"){
			$this->jinput->set('layout','product');
		}
        if($myview == 'store'){
            $view = $this->getView( 'store', 'html' );
            $view->setModel( $this->getModel( 'category', 'MyMuseModel' ), false );
        }
        $this->Itemid = $this->jinput->get('Itemid', $this->params->get('mymuse_default_itemid'));
  
	}

	
	/**
	 * display
	 * Method to display
	 *
	 * @access	public
	 */
	function display($cachable = false, $urlparams = false)
	{
		// Set a default view if none exists
		if ( !$this->jinput->get( 'view','' ) ) {
			$this->jinput->set('view', 'store');
		}
		// View caching logic -- simple... are we logged in? or in the cart?
		
		$user = JFactory::getUser();
		$view = $this->jinput->get( 'view' ) ;
		if ($user->get('id') || $view == "cart") {
			parent::display(false);
		} else {
			parent::display(true);
		}
	}


	/**
	 * addtocart
	 * add an item to the cart
	 *
	 * @access	public
	 */
	function addtocart()
	{

		if(!$this->MyMuseCart->addToCart( )){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		if($this->jinput->get('return','')){
			$return = base64_decode($this->jinput->get('return'));
			$msg = JText::_("MYMUSE_ADDED_TO_CART");
			$this->setRedirect( $return, $msg);
			return true;
		}
		if($this->params->get('my_use_coupons')){
			$this->jinput->set('task', 'coupon');
		}

		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
		
	}

	/**
	 * updatecart
	 * update the cart
	 *
	 * @access	public
	 */
	function updatecart()
	{

		if(!$this->MyMuseCart->updateCart( )){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
	}

	/**
	 * cartdelete
	 * delete an item to the cart
	 *
	 * @access	public
	 */
	function cartdelete()
	{
		$product_id = $this->jinput->get('product_id',0);
		if(!$this->MyMuseCart->delete($product_id )){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid) );
		return;
	}
	
	
	/**
	 * cartclear
	 * clear the cart
	 *
	 * @access	public
	 */
	function cartClear()
	{

		if(!$this->MyMuseCart->reset()){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		//$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid) );
		return;
	}
	/**
	 * showcart
	 * display the cart
	 *
	 * @access	public
	 */
	function showcart()
	{
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
	}

	/**
	 * coupon
	 * display coupon input form
	 *
	 * @access	public
	 */
	function coupon()
	{
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
	}
	
	/**
	 * couponadd
	 * add a coupon to the cart
	 *
	 * @access	public
	 */
	function couponadd()
	{
		
		if(!$this->MyMuseCart->couponadd()){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		$msg = JText::_("MYMUSE_COUPON_ADDED");
		
		$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
		
	}
	
	/**
	 * checkoutasguest
	 * clear the guest buyer's profile and log them in
	 *
	 * @access	public
	 */
	
	function guestcheckout()
	{
		
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		
		
		$query = "SELECT id from #__users WHERE username = 'buyer'";
		$db->setQuery($query);
		if(!$guestid = $db->loadResult()){
			$msg = JText::_("MYMUSE_COULD_NOT_FIND_GUEST");
			$this->setRedirect( JRoute::_("index.php?option=com_mymuse&task=checkout&Itemid=".$this->Itemid, $msg));
			return false;
		}
		
		$query = "DELETE FROM #__user_profiles WHERE user_id ='$guestid'";
		$db->setQuery($query);
		if(!$db->execute()){
			$msg = $db->getErrorMsg();
			$this->setRedirect( JRoute::_("index.php?option=com_mymuse&task=checkout&Itemid=".$this->Itemid, $msg));
			return false;
		}

		$session->set('guestcheckout', 1);
		$this->setRedirect( JRoute::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid));
		return true;
		/**
		 * 
		 $query = "UPDATE #__users set email='', name='' WHERE id ='$guestid'";
		$db->setQuery($query);
		if(!$db->execute()){
			$msg = $db->getErrorMsg();
			$this->setRedirect( JRoute::_("index.php?option=com_mymuse&task=checkout&Itemid=".$this->Itemid, $msg));
			return false;
		}
		
		$res = $this->savenoreg();

		if($res){
			$user	= JFactory::getUser();
			$user->email = '';
			$user->email1 = '';
			$user->email2 = '';
			$user->name = '';
			$user->profile = array();
			$user->guestcheckout = 1;
			$session->set('user', $user);
			$session->set('guestcheckout', 1);
		}
		
		return $res;
		*/
	}
	
	
	/**
	 * savenoreg
	 * save no registration, try to log in as guest buyer
	 *
	 * @access	public
	 */
	function savenoreg()
	{	
		
		if($this->MyMuseShopper->savenoreg()){
			$this->setRedirect( JRoute::_("index.php?option=com_mymuse&task=checkout&Itemid=".$this->Itemid));
			return true;
		}else{
			// Redirect back to the registration screen.
			// enqueued messages will display
			$this->setRedirect( JRoute::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid));
			return false;
		}
	}
	
	/**
	 * checkout
	 * take me to the checkout page
	 *
	 * @access	public
	 */
	function checkout()
	{

		$user = JFactory::getUser();

		//no_reg and not logged in
        if(!$user->get('id') && $this->params->get('my_registration') == "no_reg"){
        	
        	$plugin = JPluginHelper::getPlugin('user', 'mymusenoreg');
        	
        	if(!count($plugin)){
       
        		//plugin is not on, try to login as buyer
        		if(!$this->MyMuseShopper->savenoreg()){
        			echo $this->MyMuseShopper->getError();
        			echo "Could not Log in"; 
        			return false;
        		}else{
        			$this->shopper 	=  $this->MyMuseShopper->getShopper();
        			$url = JRoute::_("index.php?option=com_mymuse&task=checkout&view=cart&Itemid=".$this->Itemid);
        			$this->setRedirect( $url );
        			return true;
        		}
        	    
        	}else{
        		$url = JRoute::_(JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        		$return = base64_encode($url);

        		$msg = JText::_("MYMUSE_PLEASE_COMPLETE_THE_FORM");
        		$this->setRedirect( JRoute::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid), $msg );
        		return true;
        	}
        }
        //
        //no_reg, logged in but no form yet
        if($user->get('id') && ($this->params->get('my_registration') == "no_reg") && !$this->shopper->perms){
        	$msg = JText::_("MYMUSE_PLEASE_COMPLETE_THE_FORM");
        	$this->setRedirect( JRoute::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid), $msg );
        	return false;
        	
        }
        
        // not logged in and jomsocial
        if(!$user->get('id') && $this->params->get('my_registration') == "jomsocial"){
            $msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER_BELOW");
            $this->setRedirect( JRoute::_('index.php?option=com_community'), $msg );
            return false;
        }
       
        //user and shopper but missing fields, so no shopper perms
        if($user->get('id') && $this->shopper->id && !$this->shopper->perms){
        	
        	$url = JRoute::_(JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);

            $msg = JText::_("MYMUSE_PLEASE_FILL_IN_MISSING_ITEMS") .": ".$this->MyMuseShopper->getError();
        	$this->setRedirect( JRoute::_('index.php?option=com_users&view=profile&layout=edit&return='.$return.'&Itemid='.$this->Itemid), $msg );
            return false;
        }
        
        //normal registration 
		if(!$this->shopper->perms){
			
			$url = JRoute::_(JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);
			
			$rpage = strtolower($this->params->get('my_registration_redirect'));
			$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");
        	$this->setRedirect( JRoute::_('index.php?option=com_users&view='.$rpage.'&return='.$return.'&Itemid='.$this->Itemid), $msg );
            return false;
		}

		// see if any plugins want to check the cart
		$this->Itemid		= $this->jinput->get('Itemid', 0);
		$dispatcher	= JDispatcher::getInstance();
		$results 	= $dispatcher->trigger('onBeforeMyMuseCheckout',
				array(&$this->shopper, &$this->store, &$this->MyMuseCart->cart, &$this->params, &$this->Itemid) );
		
		if(is_array($results)){
			foreach($results as $result){
				eval($result);
			}
		}
		
	
		if($this->params->get('my_use_shipping') 
		&& !isset($this->MyMuseCart->cart['ship_method_id'])
		&& $this->MyMuseCart->shipping_needed() ){
			
			$this->jinput->set('task', 'shipping');
			$this->shipping();
			return true;
		}
		
		
		//See if we want to skip the confirm page
		if($this->params->get('my_checkout','regular') == "skip_confirm"){
			$this->jinput->set('task','confirm');
			$this->confirm();
			return true;
		}

		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		
		$this->display();

	}
	
	/**
	 * shipping
	 * display shipping options
	 *
	 * @access	public
	 */
	function shipping()
	{

		if(!isset($this->shopper->perms)){
			$url = JRoute::_(JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);
			$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        	$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( JRoute::_('index.php?option=com_users&view='.$rpage.'&return='.$return), $msg );
            return false;
		}else{
			
			JPluginHelper::importPlugin('mymuse');
			$dispatcher		= JDispatcher::getInstance();
			$this->order		= $this->MyMuseCart->buildOrder();
			$results = $dispatcher->trigger('onListMyMuseShipping',
					array($this->shopper, $this->store, $this->order, $this->params) );
			if(isset($results[0])){
				$res = $results[0];
			}else{
				$res = array();
			}
			//if we only have one shipping, add it to the order
			if(count($res) == 1 && $this->params->get('my_add_shipping_auto',0)){
				$url =  'index.php?option=com_mymuse&task=confirm&shipmethodid='.$res['0']->id;
				$url .= '&Itemid='.$this->Itemid;
				$url = JRoute::_($url);
				$msg = JText::_('MYMUSE_SHIPPING_ADDED')." ".$res['0']->ship_carrier_name." ";
				$msg .= $res['0']->ship_method_name.": ".MyMuseHelper::printMoney($res['0']->cost);
				$this->setRedirect( $url, $msg );
				return false;
			}
			
			//show shipping options
			$this->jinput->set('view', 'cart');
			$this->jinput->set('layout', 'cart');
			$this->display();
		}
	}
	
	/**
	 * confirm
	 * they have confirmed the order, do some checks and save it
	 *
	 * @access	public
	 */
	function confirm()
	{
		
		
		// are they logged in?
		if(!$this->shopper->perms){
			$url = JRoute::_(JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);
			$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        	$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( 'index.php?option=com_users&view='.$rpage.'&return='.$return, $msg );
            return false;
		}
		
		// need shipping?
		if($this->params->get('my_use_shipping') && $this->MyMuseCart->shipping_needed()){
			$shipmethodid = $this->jinput->get('shipmethodid', 0);
			if(!$shipmethodid){
				$msg = JText::_('MYMUSE_SHIP_METHOD_ID_IS_NOT_VALID');
				$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=shipping&Itemid='.$this->Itemid), $msg );
				return false;
			}else{
                $order 		= $this->MyMuseCart->buildOrder( 0, 1 );
                $this->MyMuseCart->cart['shipmethodid'] = $shipmethodid;
                $dispatcher		= JDispatcher::getInstance();
                JPluginHelper::importPlugin('mymuse');
                $results = $dispatcher->trigger('onCaclulateMyMuseShipping', array($order, $shipmethodid ));
				$this->MyMuseCart->cart['shipping'] = $results[0];

			}
		}

		//save the order
		if($this->MyMuseCart->cart['idx']){
			$this->jinput->set('view', 'cart');
			$this->jinput->set('layout', 'cart');
	
			if($this->params->get('my_saveorder') != "after"){
				// save the order
				if(!$this->MyMuseShopper->order = $this->MyMuseCheckout->save( )){
					$msg = $this->MyMuseCheckout->error;
					$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
					return false;
				}
				
				if($this->MyMuseShopper->order->order_status == "C"){
					$this->jinput->set('task', 'makemail');
					$this->display();
					$this->jinput->set('task', 'confirm');
				}
				
				$this->MyMuseCart->cart['orderid'] = $this->MyMuseShopper->order->id;
				if($this->MyMuseShopper->order->order_total == 0.00){
					$this->jinput->set('task', 'thankyou');
					$this->thankyou();
					return true;
				}
			}else{
				//print_pre($this->MyMuseCart->cart); exit;
			}

			$this->display();
	
		}else{
			$this->jinput->set('view', 'cart');
			$this->jinput->set('layout', 'cart');
			$this->display();
		}
	}

	/**
	 * process_payment
	 * process a payment
	 *
	 * @access	public
	 */
	function process_payment()
	{
		$session 		= JFactory::getSession();
		$plugin 		= $session->get('process_payment',0);
		$orderid 		= $this->jinput->get('orderid',0);
		
		
		if($plugin && $orderid){
			$dispatcher		= JDispatcher::getInstance();
			$this->order 	= $this->MyMuseCheckout->getOrder($orderid);
			$func 			= 'on'.ucfirst($plugin).'ProcessPayment';
			JPluginHelper::importPlugin('mymuse');
			$results = $dispatcher->trigger($func,
				array($this->shopper, $this->store, $this->order, $this->params, $this->Itemid) );
		}else{
			$msg = "Could not find plugin or orderid";
			$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
			
		}
	}
	
	
	
	/**
	 * makepayment
	 * make a payment
	 *
	 * @access	public
	 */
	function makepayment()
	{
		$mainframe = JFactory::getApplication();
		
		
		if(!$this->shopper->perms){
			$url = JRoute::_(JURI::base().'index.php?option=com_mymuse&view=cart&layout=cart&Itemid='.$this->Itemid);
        	$return = base64_encode($url);
			$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        	$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( JRoute::_('index.php?option=com_users&view='.$rpage.'&return='.$return), $msg );
            return false;

		}  
            
		if(!$this->MyMuseCart->cart['orderid'] && $this->params->get('my_shop_test')){
			if($this->params->get('my_saveorder') != "after"){
				// save the order
				if(!$this->MyMuseShopper->order = $this->MyMuseCheckout->save( )){
					$msg = $this->MyMuseCheckout->error;
					$this->setRedirect( JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
					return false;
				}
				$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder($this->MyMuseShopper->order->id);
					
				if($this->params->get('my_shop_test')){
					$this->jinput->set('view', 'cart');
					$this->jinput->set('layout', 'cart');
				}
			}
		}elseif($this->MyMuseCart->cart['orderid']){
			
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder($this->MyMuseCart->cart['orderid']);
			
			if($this->params->get('my_shop_test')){
				$this->jinput->set('view', 'cart');
				$this->jinput->set('layout', 'cart');

			}else{
				$this->jinput->set('task', 'thankyou');
				$this->jinput->set('view', 'cart');
				$this->jinput->set('layout', 'cart');
			}
		}else{
			$this->jinput->set('view', 'cart');
			$this->jinput->set('layout', 'empty');
		}
		$this->display();

	}

	/**
	 * thankyou
	 * thank you after payment
	 *
	 * @access	public
	 */
	function thankyou()
	{
		$errorName = $this->jinput->get('errorName', 0);
		if($errorName){
			$errorMsg = $this->jinput->get('errorMsg', '');
			$msg = $errorName." : ".$errorMsg;
			$this->setRedirect("index.php", $msg);
		}
		$session = JFactory::getSession();
		$session->set("process_payment","");

		//get order
		$db 			= JFactory::getDBO();
		$user			= JFactory::getUser();
		$user_id 		= $user->get('id');
		$orderid 		= $this->jinput->get('orderid', 0);
		$session 		= JFactory::getSession();
		$order_number 	= $session->get("order_number",0);

		$st 			= $this->jinput->get('st', 0);
		$after			= $this->jinput->get('after', 0);
		$tx 			= $this->jinput->get('tx', 0);
		$date 			= date('Y-m-d h:i:s');
		
		
		$pp 			= $this->jinput->get('pp', 0);
		
		$pesapal_merchant_reference = $this->jinput->get('pesapal_merchant_reference', 0);
		if($pesapal_merchant_reference){
			$order_number = $pesapal_merchant_reference;
		}

		if($after || $this->params->get('my_saveorder') == "after"){
			// See if there is a transaction value
			
			if($tx){
				$q = "SELECT order_id FROM #__mymuse_order_payment WHERE
				transaction_id='$tx'";
				$db->setQuery($q);
				$orderid = $db->loadResult();
				if($this->params->get('my_debug')){
					$debug = "$date: Got orderid from transaction: $orderid";
					MyMuseHelper::logMessage( $debug  );
				}
			}
			
			if(!$orderid && $pp !== 'paymentoffline' && $this->params->get('my_registration') == "no_reg"){
				//get the last orderid
				$q1 = "SELECT id from #__mymuse_order WHERE 
				notes LIKE '%". $user->get('email')  ."%' ORDER BY id DESC LIMIT 0,1";
				$db->setQuery($q1);
				$orderid = $db->loadResult();
				if($this->params->get('my_debug')){
					$debug = "$date: Got last orderid : $orderid";
					MyMuseHelper::logMessage( $debug  );
				}
			}elseif(!$orderid && $pp !== 'paymentoffline'){
				//get the last orderid
				$q1 = "SELECT id from #__mymuse_order WHERE
				user_id = '$user_id' ORDER BY id DESC LIMIT 0,1";
				$db->setQuery($q1);
				$orderid = $db->loadResult();
			}
			
			if($orderid){
				$this->MyMuseCart->reset();
			}
		}
		
		if(!$orderid && $order_number){
			$q = "SELECT id from #__mymuse_order WHERE order_number='".$order_number."' ";
			$db->setQuery($q);
			$orderid = $db->loadResult();
		}
		
		if(!$orderid && $this->params->get('my_saveorder') == "after"){
			// no id
			$msg = JText::_("MYMUSE_NO_ORDER_WAITING");
			$this->setRedirect(JRoute::_("index.php?option=com_mymuse&view=shopper&layout=waiting"), $msg);
			return false;
		}
		
		
		if(!$orderid){
			// no id
			$msg = JText::_("MYMUSE_NO_ORDER_ID".' ');
			$this->setRedirect("index.php", $msg);
			return false;
		}
		
		$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );

		if(!isset($this->MyMuseShopper->order->id)){
			// no order
			$msg = JText::_("MYMUSE_NO_ORDER");
			$this->setRedirect("index.php", $msg);
			return false;
		}
		
		if($this->MyMuseShopper->order->user_id != $user_id ){
			// not the right user!!
			$msg = JText::_("MYMUSE_USER_ORDER_OWNER_MISMATCH");
			$this->setRedirect("index.php", $msg);
			return false;
		}
	
		$notifyCustomer = $this->jinput->get('notifyCustomer', 0);
		if($notifyCustomer){
			$this->jinput->set('task', 'makemail');
			$this->jinput->set('view', 'cart');
			$this->display();
		}

	
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){
			// waiting for IPN
			sleep(3);
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$this->MyMuseShopper->order->waited = 3;
		}
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){
			// waiting for IPN
			sleep(3);
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$this->MyMuseShopper->order->waited = 6;
		}
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){
			// waiting for IPN
			sleep(3);
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$this->MyMuseShopper->order->waited = 9;
		}
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){

			$uri = JUri::getInstance();
			$msg = JText::_("MYMUSE_WAITING_FOR_IPN"). " <a href='".$uri->toString()."'>".$uri->toString()."</a>";
			
			$this->setRedirect('index.php', $msg);
			return false;
		}
		
		

		if($this->MyMuseShopper->order->order_status == "C"){
			//already confirmed 
			$dispatcher		= JDispatcher::getInstance();
			$results = $dispatcher->trigger('onAfterMyMuseConfirm', 
				array(&$this->shopper, &$this->store, &$this->params, &$this->Itemid) );

			if(is_array($results)){
				foreach($results as $result){
					eval($result);
				}
			}
		
			if($this->MyMuseShopper->order->downloadable){
				//save the download page
				$this->jinput->set('task', 'downloads');
				$this->jinput->set('id', $this->MyMuseShopper->order->order_number);
				ob_start();
				$this->downloads();
				$download_page = ob_get_contents();
				ob_end_clean();
				$this->jinput->set('download_page',$download_page);
			}
			
			$this->jinput->set('task', 'vieworder');
			$this->jinput->set('view', 'cart');
			$this->jinput->set('layout', 'cart');
			$this->display();
			
		}else{

			$this->jinput->set('task', 'vieworder');
			$this->jinput->set('view', 'cart');
			$this->jinput->set('layout', 'cart');
			$this->display();
		}
	}

	/**
	 * vieworder
	 * display an order
	 *
	 * @access	public
	 */
	function vieworder()
	{
		//get order
		$db 		= JFactory::getDBO();
		$user		= JFactory::getUser();
		$user_id 	= $user->get('id');
		$orderid 	= $this->jinput->get('orderid', 0);
		$session 	= JFactory::getSession();
		$order_number = $session->get("order_number",0);
		$st 		= $this->jinput->get('st', 0);
		$this->params 	= MyMuseHelper::getParams();
		
		if(!$user_id ){
			// not a user!!
			if($this->params->get('my_registration') == "no_reg"){
				$msg = JText::_("JGLOBAL_AUTH_ACCESS_DENIED");;
				$this->setRedirect( JRoute('index.php'), $msg );
			}else{
				$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        		$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        		$this->setRedirect( JRoute::_('index.php?option=com_users&view='.$rpage.'&return='.$return), $msg );
            return false;
			}
		}

		if($order_number && !$orderid){
			$q = "SELECT id from #__mymuse_order WHERE order_number='".$order_number."' ORDER BY id DESC";
			$db->setQuery($q);
			$orderid = $db->loadResult();
		}
		
		$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );

		if(intval($this->MyMuseShopper->order->user_id) != intval($user_id) ){
			// not the right user!!
			$msg = JText::_("MYMUSE_USER_ORDER_OWNER_MISMATCH");
			$this->setRedirect("index.php", $msg);
			return false;
		}
		
		if($this->MyMuseShopper->order->downloadable
				&& $this->MyMuseShopper->order->order_status == "C"){
			//save the download page
				$this->jinput->set('task', 'downloads');
				$this->jinput->set('id', $this->MyMuseShopper->order->order_number);
				ob_start();
				$this->downloads();
				$download_page = ob_get_contents();
				ob_end_clean();
				$this->jinput->set('download_page',$download_page);
		}		
		$this->jinput->set('task', 'vieworder');
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
	}
		 
	/**
	 * paycancel
	 * cancel a payment
	 *
	 * @access	public
	 */
	function paycancel()
	{
		$this->params 	= MyMuseHelper::getParams();
		
		if($this->params->get('my_saveorder') == "after"){
			//there won't be an order
		}else{
			// get order
			$db = JFactory::getDBO ();
			$user = JFactory::getUser ();
			$user_id = $user->get ( 'id' );
			$id = $this->jinput->get ( 'id', 0 );
			$session = JFactory::getSession ();
			$order_number = $session->get ( "order_number", 0 );
			
			if ($order_number) {
				$q = "SELECT id from #__mymuse_order WHERE order_number='" . $order_number . "' ORDER BY id DESC";
				$db->setQuery ( $q );
				$id = $db->loadResult ();
			}
			
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder ( $id );
			
			if ($this->MyMuseShopper->order->user_id != $user_id) {
				// not the right user!!
				$msg = JText::_ ( "MYMUSE_USER_ORDER_OWNER_MISMATCH" );
				$this->setRedirect ( "index.php", $msg );
				return false;
			}
		}
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
	}

		 

	/**
	 * downloads
	 * display downloads page
	 *
	 * @access	public
	 */
	function downloads()
	{
		
		$shopper = $this->shopper;
		$uri = JFactory::getURI();
		$current = $uri->toString();

		if(!$shopper->perms){
			$url = $current;;
			$return = base64_encode($url);
			$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
			$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( 'index.php?option=com_users&view='.$rpage.'&return='.$return, $msg );
			return false;
		}

		$this->jinput->set('view', 'store');
		$this->jinput->set('layout', 'store');
		$this->display();
		return true;
	}
	
	/**
	 * accdownloads
	 * downloads for no registration
	 *
	 * @access	public
	 */
	function accdownloads()
	{	
		$this->jinput->set('task', 'downloads');
		$this->jinput->set('view', 'store');
		$this->jinput->set('layout', 'store');
		$this->display();
		return true;
	}
		 
	/**
	 * downloadfile
	 * get the file and send it back
	 *
	 * @access	public
	 */
	function downloadfile()
	{
		
		$shopper =  $this->MyMuseShopper->getShopper();
		if(!isset($shopper->perms)){
			$url = JURI::root()."index.php?option=com_mymuse&view=cart&layout=cart";
			$return = base64_encode($url);
			$msg = JText::_("MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
			$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( 'index.php?option=com_users&view='.$rpage.'&return='.$return, $msg );
			return false;
		}
		$this->jinput->set('view', 'store');
		$this->jinput->set('layout', 'store');
		if(!$this->display()){
			$id = $this->jinput->get('id',0);
			$msg = $this->jinput->get('msg',0);
			if($this->params->get('my_registration') == "no_reg"){
				$current =  JRoute::_("index.php?option=com_mymuse&view=cart&task=accdownloads&id=".$id."&item_id=");
			}else{
				$current =  JRoute::_("index.php?option=com_mymuse&view=cart&task=downloads&id=".$id."&item_id=");
			}
			$this->setRedirect( $current, $msg );
			return false;
		}
		return true;
	}

	/**
	 * notify
	 * catch the post from the payment processor, return required responses, update orders and do mailouts
	 * 
	 * @access	public
	 */
	function notify()
	{
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();

	}
	
	function makemail()
	{
		$this->jinput->set('view', 'cart');
		$this->jinput->set('layout', 'cart');
		$this->display();
	
	}
	/**
	 * rate
	 * Store a vote on a product
	 * 
	 * @access	public
	 */
	function rate()
	{
		$db = JFactory::getDBO();
		$index = $this->jinput->get('index', '');
		$productid = $this->jinput->get('productid', '');
		$rating = $this->jinput->get('user_rating', '');
		$url = $this->jinput->get('url', '');

		$query = "SELECT id FROM #__mymuse_product WHERE id='$productid'";
		$db->setQuery($query);
		$id = $db->loadResult();

		$model = $this->getModel('product');
		if ($model->storeVote($id, $rating)) {
			$data = JText::_('MYMUSE_PRODUCT_VOTE_SUCCESS');
		} else {
			$data = JText::_('MYMUSE_PRODUCT_VOTE_FAILURE');
		}

		$this->setRedirect($url, $data);

		return true;
		
	}
	
	/**
	 * rateajax
	 * store a vote on a product using ajax
	 *
	 * @access	public
	 */
	function rateajax()
	{
		$db = JFactory::getDBO();
		$index = $this->jinput->get('index', '');
		$cat_prod = $this->jinput->get('cat_prod', '');
		/**
		Array
		(
				[option] => com_mymuse
				[task] => rateajax
				[index] => 0
				[cat_prod] => Iron Brew: Are You My Sister
				[rating] => 3
				[title] => Are You My Sister (3.33 MB)
				[Itemid] =>
				[return] => L215bXVzZXRlc3QyNS9pbmRleC5waHA/b3B0aW9uPWNvbV9teW11c2UmdGFzaz1yYXRlYWpheA==
		)
		*/
		list($cat,$prod) = explode(":",$cat_prod);
		$cat = trim($cat);
		$prod= trim($prod);
		$rating = $this->jinput->get('rating', '');
	
	
		$query = "SELECT id FROM #__mymuse_product WHERE title='$prod'";
		$db->setQuery($query);
		$id= $db->loadResult();
		if(!$id){
			exit;
		}
	
		$model = $this->getModel('product');
		if ($model->storeVote($id, $rating)) {
			$data = JText::_('MYMUSE_PRODUCT_VOTE_SUCCESS');
		} else {
			$data = JText::_('MYMUSE_PRODUCT_VOTE_FAILURE');
		}
	
	
		$document = JFactory::getDocument();
		$document->setMimeEncoding('text/html');
		JResponse::setHeader("Expires", "Sun, 19 Nov 1978 05:00:00 GMT");
		JResponse::setHeader("Last-Modified",  gmdate("D, d M Y H:i:s") . " GMT");
		JResponse::setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		JResponse::setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		JResponse::setHeader("Pragma", "no-cache");
	
		echo $data;
		exit;
	
	}
	
	/**
	 * ajaxtogglecart
	 *
	 * Given  product id, add it to cart, unless it is there already, then delete from cart
	 * return json encoded string with message, cat idx
	 *
	 * return string
	 */
	function ajaxtogglecart()
	{
		$jinput = JFactory::getApplication()->input;
		$productid  = $jinput->get('productid', 0, 'int');
        $variation  = $jinput->get('variation', 0, 'ARRAY');
		if(!$productid ){
			$data = array();
		}else{

			$db = JFactory::getDBO();
			$query = "SELECT title from #__mymuse_product WHERE id =$productid";
			$db->setQuery($query);
			$title = $db->loadResult();
			
			$incart = 0;
			for ($i=0;$i<$this->MyMuseCart->cart["idx"];$i++) {
				if($this->MyMuseCart->cart[$i]["product_id"] == $productid){
					$incart = 1;
				}
			}
	
			if($incart){
				// let us remove it
				$this->MyMuseCart->delete($productid );
				$msg = JText::_("MYMUSE_DELETED")." ".$title;
				$action = "deleted";
			}else{
				//let us add it
				$this->MyMuseCart->addToCart();
				$msg = JText::_("MYMUSE_ADDED")." ".$title;
				$action = "added";
			}
			$data = array('action'=>$action, 'msg'=>$msg, 
            'idx' => $this->MyMuseCart->cart['idx'],
            'variation'=> $variation[$productid]);
		}
	
		//save the cart in the session
		$session = JFactory::getSession();
		$session->set("cart",$this->MyMuseCart->cart);
	
		$rand = JUserHelper::genRandomPassword(8);
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		JResponse::setHeader("Expires","Sun, 19 Nov 1978 05:00:00 GMT");
		JResponse::setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		JResponse::setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		JResponse::setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		JResponse::setHeader("Pragma", "no-cache");
		JResponse::setHeader('Content-Disposition','attachment;filename="coupon_'.$rand .'.json"');
		echo json_encode($data);
		exit;
	}
	
	
	function ajaxupdatecart()
	{
		$jinput = JFactory::getApplication()->input;
		$productid  = $jinput->get('productid', 0, 'int');
		$variation  = $jinput->get('variation', 0, 'ARRAY');
		if(!$productid ){
			$data = array();
		}else{
			$order_subtotal = 0.00;
			$product_item_subtotal = 0.00;
			$product_item_price = 0.00;
			$tax_total = 0.00;
			
			
			$db = JFactory::getDBO();
			$query = "SELECT title from #__mymuse_product WHERE id =$productid";
			$db->setQuery($query);
			$title = $db->loadResult();
			
			if(!$this->MyMuseCart->updateCart()){
				$msg = "ERROR: ".$this->MyMuseCart->error;
			}
			$order = $this->MyMuseCart->buildOrder(0,1);
			$order_subtotal = $order->order_subtotal;
			
			//print_pre($order);
			$tax_total = $order->tax_total;
			
			for ($i=0;$i<count($order->items);$i++) {
				if($order->items[$i]->id  == $productid){
					$product_item_subtotal = $order->items[$i]->product_item_subtotal;
					$product_item_price = $order->items[$i]->product_item_price;
					$msg = JText::_('MYMUSE_UPDATED_CART');
				}
				$msg = JText::_('MYMUSE_UPDATED_CART');
			}
			$data = array(
					'id' => $productid,
					'msg'=>  $msg,
					'order_subtotal'=>$order_subtotal,
					'tax_total'=>$tax_total,
					'product_item_subtotal'=>$product_item_subtotal,
					'product_item_price'=>$product_item_price,
			);

		}
		
		
		
		
		//save the cart in the session
		$session = JFactory::getSession();
		$session->set("cart",$this->MyMuseCart->cart);
	
		$rand = JUserHelper::genRandomPassword(8);
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		JResponse::setHeader("Expires","Sun, 19 Nov 1978 05:00:00 GMT");
		JResponse::setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		JResponse::setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		JResponse::setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		JResponse::setHeader("Pragma", "no-cache");
		JResponse::setHeader('Content-Disposition','attachment;filename="coupon_'.$rand .'.json"');
		echo json_encode($data);
		exit;
	}
	
	function ajaxupdatelicence()
	{
		$jinput = JFactory::getApplication()->input;
		$my_licence  = $jinput->get('my_licence', 0);
		$session = JFactory::getSession();
	
		$session->set("my_licence",$my_licence);
		$order = $this->MyMuseCart->buildOrder(0,1);
		$msg = JText::_('MYMUSE_LICENCE_UPDATED');
		unset($order->update_form);
		unset($order->update_url);
		foreach($order->items as $item){
			unset($item->parent);
			unset($item->artist);
			unset($item->_upload_errors);
			unset($item->metadata);
		}
		$data = array(
			'msg'=>  $msg,
			'order'=>$order,
		);
		//echo "licence = $my_licence";
		//save the cart in the session
		$session->set("cart",$this->MyMuseCart->cart);
	
		$rand = JUserHelper::genRandomPassword(8);
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		JResponse::setHeader("Expires","Sun, 19 Nov 1978 05:00:00 GMT");
		JResponse::setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		JResponse::setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		JResponse::setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		JResponse::setHeader("Pragma", "no-cache");
		JResponse::setHeader('Content-Disposition','attachment;filename="coupon_'.$rand .'.json"');
		echo json_encode($data);
		exit;
	}
	/*
	 * send_ipn
	*
	* For testing pesapal, url must have pesapal_merchant_reference
	*/
	function send_ipn()
	{
		$pesapalNotification="CHANGE";
		$pesapalTrackingId=md5(time());
		$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];
		$url = 'index.php?option=com_mymuse&task=notify';
		$url .= "&pesapal_notification_type=$pesapalNotification";
		$url .= "&pesapal_merchant_reference=$pesapal_merchant_reference";
		$url .= "&pesapal_transaction_tracking_id=$pesapalTrackingId";
		$this->setRedirect( $url);
		return false;
	}
	
	/*
	 * send_status
	*
	* For testing pesapal
	*/
	
	function send_status()
	{
		echo "STATUS=CONFIRMED";
		exit;
	
	}
}
