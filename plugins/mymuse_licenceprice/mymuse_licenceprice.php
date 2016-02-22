<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

/** MyMuse licenceprice plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseMymuse_licenceprice extends JPlugin
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
	function plgMymuseMymuse_licenceprice(&$subject, $config)  {
		
		parent::__construct($subject, $config);

	}
		
		
	/**
	 * 
	 * onCalculatePrice
	 * if licenceprice, modify cart
	 * 
	 *
	 * @param object		$shopper
	 * @param object		$store
	 * @param object		$order
	 * @param object		$params
	 * 
	 * returns true
	 */
	function onCalculatePrice(&$price_info, $cart)
	{
		$app = JFactory::getApplication('site');
		$jinput = $app->input;
		$this_licenceprice = 0;
		$quantity = $cart["idx"];
		$session = JFactory::getSession();
		$my_licence = $jinput->get('my_licence',$session->get("my_licence",0)) + 1; //we add one because the params start at 1.
		
		for($i = 1; $i < 7; $i++){
			$min = $this->params->get('licenceprice_'.$my_licence.'_minimum_'.$i);
			if($quantity >= $min){
				//we have a match
				$price_info['product_price'] = $this->params->get('licenceprice_'.$my_licence.'_amount_'.$i);
				//echo 'min = '.$min.' licenceprice_'.$my_licence.'_amount_'.$i.' '.$this->params->get('licenceprice_'.$my_licence.'_amount_'.$i); exit;
				//echo "price_info['product_price'] = ".$price_info['product_price'];
				break;
			}
		}

		return true;
	}
}