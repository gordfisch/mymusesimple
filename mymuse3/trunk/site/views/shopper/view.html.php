<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright		Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author			Gordon Fisch
 * @author mail		info@mymuse.ca
 * @website			http://www.mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

class myMuseViewShopper extends JViewLegacy
{
	function __construct()       {
    	parent::__construct();
        $layout = JRequest::getVar('layout', 'register');
         parent::setLayout($layout);         
    }
        
	function display($tpl = null){
		$mainframe = JFactory::getApplication();
		$params = MyMuseHelper::getParams();
		
		// Get the view data.
		$this->data		= $this->get('Data');
		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');
		$this->params	= $this->state->get('params');

		$MyMuseShopper  = MyMuse::getObject('shopper','models');
		$shopper 		=& $MyMuseShopper->getShopper();
		$MyMuseStore  	= MyMuse::getObject('store','models');
		$store 			= $MyMuseStore->_store;
		$MyMuseCart  	= MyMuse::getObject('cart','helpers');
		$return 		= JRequest::getVar('return','');
		$user			= JFactory::getUser();
		if(!$shopper->id && $user->get('id')){
			// not a shopper but already user
			// try to make first and last names
			list($shopper->first_name, $shopper->last_name) = explode(" ", $user->get('name'), 2);

		}
	
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		
		$document		= JFactory::getDocument();
		$dispatcher		= JDispatcher::getInstance();
		$pathway		= $mainframe->getPathway();
		$Itemid			= JRequest::getVar('Itemid', 0, '', 'int');
		
    	$this->assignRef('Itemid', $Itemid);
		$this->assignRef('user'  , $user);
		$this->assignRef('params', $params);
		$this->assignRef('store', $store);
		$this->assignRef('return', $return);
		$this->assignRef('shopper', $shopper);
		

		if($this->getLayout() == "thank_you"){
			$st 		= JRequest::getVar('st', 0);
			$heading 	= Jtext::_('MYMUSE_THANK_YOU');
			$message 	= Jtext::_('MYMUSE_WE_HAVE_RECEIVED_YOUR_ORDER');

			if(isset($MyMuseShopper->order->payments[0]->plugin) && $MyMuseShopper->order->payments[0]->plugin == "paypal"){
				$message .= Jtext::_('MYMUSE_PAYPAL_THANKYOU');
			}

			$link 		= "index.php?option=com_mymuse&task=vieworder&orderid=";
			$link 		.= $MyMuseShopper->order->id;

			
			if($Itemid){
				$link 		.= "&Itemid=$Itemid";
			}
			if($st){
				$link 		.= "&st=$st";
			}
			$message 	= $message.'<br /><a href="'.$link.'">'.Jtext::_('MYMUSE_HERE_IS_YOUR_ORDER').'</a>';
			$this->assignRef('heading', $heading);
			$this->assignRef('message', $message);
			parent::display();
			return true;
		}
		if($this->getLayout() == "waiting"){
			$heading 	= Jtext::_('MYMUSE_THANK_YOU');
			$link 		= "index.php?option=com_mymuse&task=thankyou";
		
			if($Itemid){
				$link 		.= "&Itemid=$Itemid";
			}
			$link 		.= "&st=5";
			$message 	= '<a href="'.$link.'">'.Jtext::_('MYMUSE_CHECK_ORDER_WAITING').'</a>';
				
			$this->assignRef('heading', $heading);
			$this->assignRef('message', $message);
			$this->setLayout("thank_you");
			parent::display();
			return true;
				
		}
		if($this->getLayout() == "listorders"){
			
			if($params->get('my_registration') == "no_reg"){
				return false;
			}
			$model = $this->getModel();
			$orders = $model->getOrders();
			
			$this->assignRef('orders', $orders);
			parent::display();
			return true;
		}
		if($this->getLayout() == "edit" && $params->get('my_registration') == "no_reg"){
			return false;

		}
		$continue = 1;
		if(!$MyMuseCart->cart['idx']){
			$continue = 0;
		}
		$this->assignRef('continue', $continue); 


		parent::display($tpl);

	}

}
?>