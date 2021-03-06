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

jimport('joomla.application.component.controllerform');

/**
 * Order controller class.
 */
class MymuseControllerOrder extends JControllerForm
{

    function __construct() {
        $this->view_list = 'orders';
        parent::__construct();
    }
    
	function save($key = NULL, $urlVar = NULL)
	{
    
		$form = JRequest::getVar('jform',array(),'post');
		$old_status = JRequest::getVar('old_status','','post');
		$id = JRequest::getVar('id','');
		$task = JRequest::getVar('task','save');
        
	    $model = $this->getModel();
	    $this->msg = '';
		if(parent::save()){
			if($old_status != $form['order_status'])
			{
				$this->mail_client();
				$this->msg .= JText::_( 'MYMUSE_EMAILED_CUSTOMER' )." ";
			}
			if($task == 'apply'){
				$this->msg .= JText::_( 'MYMUSE_ORDER_SAVED' ).$model->getError();
        		$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $this->msg );
			}else{
				$this->msg .= JText::_( 'MYMUSE_ORDER_SAVED' ).$model->getError();
				$this->setRedirect( 'index.php?option=com_mymuse&view=orders', $this->msg );
			}
			
		}else{
			$this->msg = JText::_( 'MYMUSE_ERROR_SAVING_ORDER' ).$model->getError();
        	$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $this->msg );
		}
	}
	
	function mail_client()
	{        	
		// let's send mail about the change
		$id = JRequest::getVar('id','');
		$form = JRequest::getVar('jform',array(),'post');
		$params = MyMuseHelper::getParams();
		$date = date('Y-m-d h:i:s');
		if($params->get('my_debug')){
			$debug = $date."\n#####################\nORDER SAVE\n";
			$debug .= "ORDER: $id \nSTATUS: ".$form['order_status']."\n" ;
			MyMuseHelper::logMessage( $debug  );
		}

		JRequest::setVar( 'view', 'order' );
		JRequest::setVar( 'layout', 'order_customer');
		JRequest::setVar( 'task', 'mailcustomer'  );


		
		include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'mymuse.class.php' );
		$MyMuseStore  	=& MyMuse::getObject('store','models');
		$store 			= $MyMuseStore->getStore();
		$model 			= $this->getModel();
		$order			= $model->getItem();

		$language = JFactory::getLanguage();
		$uparams = $order->user->getParameters();
		$language_tag = $uparams->get('language');
		if(!$language_tag){
			$language_tag = $language->get('lang');
		}
		$extension = 'com_mymuse';
		$base_dir = JPATH_SITE;
		
		$language->load($extension, $base_dir, $language_tag, true);
		
	
		include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'templates'.DS.'mail_html_header.php' );
		ob_start();
		$this->display();
		$message .= ob_get_contents();
		ob_end_clean();
		$message  = $header.$message.$footer;

		//if using no_reg
		if($params->get('my_registration') == "no_reg"){
			$registry = new JRegistry;
			$registry->loadString($item->notes);
		}

		// SEND MAIL TO BUYER
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
     	$recipient = $order->user->email;
     	if($params->get('my_cc_webmaster')){
     		$recipient = array($order->user->email, $params->get('my_webmaster'));
     	}
     	$mailer->addRecipient($recipient);
     	//subject, body
     	$subject = Jtext::_('MYMUSE_ORDER_STATUS_CHANGED')." ".$store->title;
		$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
     	$mailer->setSubject($subject);
     	$mailer->setBody($message);

     	$send = $mailer->Send();
     	if ( $send !== true ) {
     		echo 'Error sending email: ' . $send->getError();
     	}
		JRequest::setVar( 'layout', 'edit');
		JRequest::setVar( 'task', 'save'  );
		JRequest::setVar( 'email_sent', '1' );
	}
}