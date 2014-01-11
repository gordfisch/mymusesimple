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
* MyMuse PreOrder plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePreOrder extends JPlugin
{
	
    /**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	 * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgMymusePreOrder(&$subject, $config)  {
		parent::__construct($subject, $config);

	}
		
		
	/**
	 * Offline Payment method
	 *
	 */
	function onBeforeMyMuseCheckout(&$shopper, &$store, &$cart, &$params, &$Itemid )
	{

    $msg    = $this->params->get('my_msg');
	$string = '';

    if($this->params->get('which_min') == "number"){
        if($cart['idx'] < $this->params->get('my_min')){
            $string .= '$msg = "'.JText::_($msg).'";
            JRequest::setVar("task","");
            $this->setRedirect("index.php?option=com_mymuse&task=showcart", $msg);';
        
        }
    }elseif($this->params->get('which_min') == "price"){
        require_once( JPATH_COMPONENT.DS.'mymuse.class.php');
        $MyMuseCart = MyMuse::getObject('cart','helpers');
        $order = $MyMuseCart->buildOrder(false);
        if($order->order_subtotal < $this->params->get('my_min')){
            $string .= '$msg = "'.JText::_($msg).'";
            JRequest::setVar("task","");
            $this->setRedirect("index.php?option=com_mymuse&task=showcart", $msg);';
        
        }
    }

	return $string;
	
	}
} ?>