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
JPlugin::loadLanguage( 'plg_mymuse_payment_offline', JPATH_ADMINISTRATOR );

/**
* MyMuse PaymentOffline plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePaymentOffline extends JPlugin
{
	

	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
		
		
	/**
	 * Offline Payment method
	 *
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid )
	{

		$string = '';
		
		$string .= '
		<form action="index.php?Itemid='.$Itemid.'" method="post" name="adminFormPayOffline">
		<input type="hidden" name="option" value="com_mymuse">
		<input type="hidden" name="task" value="thankyou">
		<input type="hidden" name="notifyCustomer" value="1">
		<input type="hidden" name="id" value="'.$order->id.'">
		<div id="paypal_form">
		<input type="submit" class="button" name="paymentoffline" value="'.JText::_('MYMUSE_I_WILL_PAY_OFFLINE').'" />
		</div>
		</form>
		';
		if ($this->params->get('paymentoffline_msg') != ""){
			$string .= "<br />".JText::_($this->params->get('paymentoffline_msg'));
		}
		return $string;
	
	}
	
	function onAfterMyMusePayment()
	{
	
		$email_msg = "paymentpaypal:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		return $email_msg;
	
	}
} ?>