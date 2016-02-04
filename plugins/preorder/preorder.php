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
	function plgMymusePreOrder(&$subject, $config)  {
		parent::__construct($subject, $config);

	}
		
		
	/**
	 * Offline Payment method
	 *
	 */
	function onBeforeMyMuseCheckout(&$shopper, &$store, &$cart, &$params, &$Itemid )
	{
		$msg = $this->params->get ( 'my_msg' );
		$string = '';
		$app = JFactory::getApplication();
		$jinput = $app->input;
		
		if ($this->params->get ( 'which_min' ) == "number") {
			if ($cart ['idx'] < $this->params->get ( 'my_min' )) {
				$string .= '$msg = "' . JText::_ ( $msg ) . '"';
				//$jinput->set( "task", "" );
				$app->redirect( JRoute::_("index.php?option=com_mymuse&task=showcart&view=cart"), $msg );
			}
		} elseif ($this->params->get ( 'which_min' ) == "price") {
			require_once (JPATH_COMPONENT . DS . 'mymuse.class.php');
			$MyMuseCart = MyMuse::getObject ( 'cart', 'helpers' );
			$order = $MyMuseCart->buildOrder ( false );
			if ($order->order_subtotal < $this->params->get ( 'my_min' )) {
				$string .= '$msg = "' . JText::_ ( $msg ) . '"';
				//$jinput->set( "task", "" );
				$app->redirect( JRoute::_("index.php?option=com_mymuse&task=showcart&view=cart"), $msg );
			}
		}
		
		return $string;
	
	}
} ?>