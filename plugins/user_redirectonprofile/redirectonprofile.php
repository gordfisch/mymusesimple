<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2016 - Arboreta Internet Services - All rights reserved.
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
class plgUserRedirectonprofile extends JPlugin
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
	 * Saves user profile data
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return bool
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
    {
        $app = JFactory::getApplication();

        $username = $data['username'];;
		$session = JFactory::getSession();
		$cart = $session->get('cart');

		if($result && $cart && $cart['idx'] > 0 && $username == 'buyer'){
			$return = JRoute::_("index.php?option=com_mymuse&task=confirm");
			$app->redirect($return);
            $app->close();
		}
		return true;
    }

	
	
	

}
