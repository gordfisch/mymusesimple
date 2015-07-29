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

/**
* MyMuse Shipping Price plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseMymuse_discount extends JPlugin
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
	function plgMymuseMymuse_discount(&$subject, $config)  {
		
		parent::__construct($subject, $config);

	}
		
		
	/**
	 * onBeforeMyMuseCheckout
	 * if discount, modify cart
	 * 
	 *
	 * @param object		$shopper
	 * @param object		$store
	 * @param object		$order
	 * @param object		$params
	 * 
	 * returns true
	 */
	function onAfterBuildOrder($order, $cart)
	{
		$this_discount = 0;
		$quantity = 0;
		if($this->params->get('discount_based_on')){
			//1 = subTotal
			$target = $order->order_subtotal;
		}else{
			//0 = units,
			$quantity = 0;
			for ($i=0;$i<$cart["idx"];$i++) {
				if(isset($cart[$i]['quantity'])){
					$quantity += $cart[$i]['quantity'];
				}
			}
			$target = $quantity;
		}
		$discount_type = $this->params->get('discount_type'); //0 = amount, 1 = percent
        $result = 0;
        $j = 0;
		for($i=1;$i<13;$i++){
            $param = "discount_minimum_".$i;
            if($this->params->get($param, 0)){
				$min = $this->params->get($param);
				if($target >= $min){
					$discount = $this->params->get('discount_'.$i);
					if($this->params->get('discount_type')){
						//percent
						$this_discount = $order->order_subtotal * $discount / 100;
					}else{
						//amount
						$this_discount = $discount;
					}

				}
            } 
        }

		return $this_discount;
	}
}