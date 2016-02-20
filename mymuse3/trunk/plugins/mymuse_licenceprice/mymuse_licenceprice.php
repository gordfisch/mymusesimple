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
	 * onBeforeMyMuseCheckout
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
	function onCalculatePrice($order, $cart)
	{
		$this_licenceprice = 0;
		$quantity = 0;
		if($this->params->get('licenceprice_based_on')){
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
		$licenceprice_type = $this->params->get('licenceprice_type'); //0 = amount, 1 = percent
        $result = 0;
        $j = 0;
		for($i=1;$i<13;$i++){
            $param = "licenceprice_minimum_".$i;
            if($this->params->get($param, 0)){
				$min = $this->params->get($param);
				if($target >= $min){
					$licenceprice = $this->params->get('licenceprice_'.$i);
					if($this->params->get('licenceprice_type')){
						//percent
						$this_licenceprice = $order->order_subtotal * $licenceprice / 100;
					}else{
						//amount
						$this_licenceprice = $licenceprice;
					}

				}
            } 
        }

		return $this_licenceprice;
	}
}