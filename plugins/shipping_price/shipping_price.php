<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
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
class plgMymuseShipping_Price extends JPlugin
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
	function plgMymuseShipping_Price(&$subject, $config)  {
		parent::__construct($subject, $config);
		

	}
		
		
	/**
	 * onListMyMuseShipping
	 * 
	 * For this shipping method, only the order object is needed.
	 * Other shipping methods may depend on the shopper, the store or the component params.
	 * Return array: each item should have id, ship_carrier_name, ship_method_name, cost
	 *
	 * @param object		$shopper
	 * @param object		$store
	 * @param object		$order
	 * @param object		$params
	 * 
	 * returns array
	 */
	function onListMyMuseShipping($shopper, $store, $order, $params)
	{

        //var_dump($this->params); exit;
        $result = array();
        $j = 0;
		for($i=1;$i<3;$i++){
            $param = "ship_".$i."_active";
            if($this->params->get($param)){
                $result[$j] = new JObject;
                $result[$j]->id = $i;
                $carrier    = "ship_carrier_".$i;
                $method       = "ship_method_".$i;
                $result[$j]->ship_carrier_name          = $this->params->get($carrier);
                $result[$j]->ship_method_name           = $this->params->get($method);
                
                for($k=1;$k<5;$k++){
                	$min   = "ship_minimum_".$i.$k;
                	$max = "ship_maximum_".$i.$k;
                	$percent = "ship_percent_".$i.$k;	
              
                	$result[$j]->ship_mimimum[$k]    = $this->params->get($min);
                	$result[$j]->ship_maximum[$k]    = $this->params->get($max);
                	$result[$j]->ship_percent[$k]    = $this->params->get($percent);
                	
                }
                $result[$j]->cost                = $this->calculateShipping($order,$result[$j]);
            }
            $j++;
        }

		return $result;
	
	}
    
    /**
     * onCaclulateMyMuseShipping
     * @param object		$shopper 
     * @param object		$shopper 
     */
     function onCaclulateMyMuseShipping($order, $shipmethodid)
     {

        $cost = 0.00;

        $result = new JObject;
        $result->id = $shipmethodid;
        $carrier    = "ship_carrier_".$shipmethodid;
        $method      = "ship_method_".$shipmethodid;
        $handling   = "ship_handling_".$shipmethodid;
        $additional = "ship_additional_".$shipmethodid;
        $result->ship_type          		= "Price";
        $result->ship_carrier_name          = $this->params->get($carrier);
        $result->ship_carrier_code 			= $this->params->get($carrier);
        $result->ship_method_name           = $this->params->get($method);
        $result->ship_method_code 			= $this->params->get($method);
        $result->tracking_id 				= '';
     	for($k=1;$k<5;$k++){
            $min   = "ship_minimum_".$shipmethodid.$k;
            $max = "ship_maximum_".$shipmethodid.$k;
            $percent = "ship_percent_".$shipmethodid.$k;	
              
            $result->ship_mimimum[$k]    = $this->params->get($min);
            $result->ship_maximum[$k]    = $this->params->get($max);
            $result->ship_percent[$k]    = $this->params->get($percent); 	
        }
        
        $result->cost                    = $this->calculateShipping($order,$result);
        

        return $result;
     }
         
         
    
    /**
     * calculateShipping
     * 
     * @param array $cart
     * @return int
     */
	function calculateShipping($order, $shipMethod){

		$shipping_total = 0.00;
		// find the level based on sub_total
		$level = 1;
		for($k=1;$k<5;$k++){
			if(!$shipMethod->ship_maximum[$k]){
				$shipMethod->ship_maximum[$k] = 1000000000;
			}
			if($order->order_subtotal >= $shipMethod->ship_mimimum[$k] 
			&& $order->order_subtotal <= $shipMethod->ship_maximum[$k]){
				$level = $k;
			}
		}

		$shipping_total = $order->order_subtotal * $shipMethod->ship_percent[$level] / 100;
		
		return $shipping_total;
	}
} ?>