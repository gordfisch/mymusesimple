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

defined('JPATH_BASE') or die;

/**
 * Plugin class for login redirect handling.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.logout
 */
class plgUserRedirectonlogin extends JPlugin
{
	/**
	 * Object Constructor.
	 *
	 * @access	public
	 * @param	object	The object to observe -- event dispatcher.
	 * @param	object	The configuration object for the plugin.
	 * @return	void
	 * @since	1.5
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data
	 * @param	array	$options	Array holding options (remember, autoregister, group)
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options = array())
	{

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$cart = $session->get('cart');

		if($cart && $cart['idx'] > 0){
			$return = JRoute::_("index.php?option=com_mymuse&view=cart&layout=cart");
			$app->setUserState('users.login.form.return', $return);
		}
		return true;
	}
	
	
	

}
