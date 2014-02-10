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
* MyMuse PaymentOffline plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Offline extends JPlugin
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
		<input type="hidden" name="pp" value="payoffline">
		<div id="payoffline_form">
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
	
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "payment_offline:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		
		}
		return $email_msg;
	
	}
} ?>