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
* MyMuse Shipping Standard plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseShipping_Standard extends JPlugin
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
	function __construct(&$subject, $config)  {
		parent::__construct($subject, $config);
		

	}
		
	function plgMymuseShipping_Standard(&$subject, $config)  {
		self::__construct($subject, $config);
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
		// loading plugin parameters
        $this->_plugin = JPluginHelper::getPlugin('mymuse', 'shipping_standard');
        $result = array();
		$translate = $this->params->get('translate');
        $j = 0;
		for($i=1;$i<5;$i++){

            $param = "ship_".$i."_active";
            if($this->params->get($param)){
            	// see if countries match
            	$good = 0;
            	if($this->params->get("ship_all_countries_$i")){
            		//print_pre($this->params->get("ship_countries_$i"));
            		//see if there are exceptions
            		if(isset($shopper->profile['country'])
            				&& in_array($shopper->profile['country'], (array)$this->params->get("ship_countries_$i"))){
            			//we have an exeption do not ship
            		}else{
            			$good = 1;
            		}
            	}elseif(isset($shopper->profile['country']) && in_array($shopper->profile['country'], (array)$this->params->get("ship_countries_$i"))){
            		$good = 1;
            	}
           
            	if($good){
                	$result[$j] 	= new JObject;
					$result [$j]->id = $i;
					$carrier = "ship_carrier_" . $i;
					$method = "ship_method_" . $i;
					$handling = "ship_handling_" . $i;
					$additional = "ship_additional_" . $i;
					
					if($translate){
						$result [$j]->ship_carrier_name =  JText::_($this->params->get ( $carrier ));
						$result [$j]->ship_method_name = JText::_($this->params->get ( $method ));
					}else{
						$result [$j]->ship_carrier_name =  $this->params->get ( $carrier );
						$result [$j]->ship_method_name = $this->params->get ( $method );
					}
					
					$result [$j]->ship_handling_charge = $this->params->get ( $handling );
					$result [$j]->ship_handling_additional = $this->params->get ( $additional );
					$result [$j]->cost = $this->calculateShipping ( $order, $result [$j] );
					$j ++;
            	}
            }
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
        // loading plugin parameters
        $this->_plugin = JPluginHelper::getPlugin('mymuse', 'shipping_standard');
		$translate = $this->params->get ( 'translate' );
        $cost = 0.00;

        $result = new JObject;
        $result->id = $shipmethodid;
        $carrier    = "ship_carrier_".$shipmethodid;
        $method     = "ship_method_".$shipmethodid;
        $handling   = "ship_handling_".$shipmethodid;
        $additional = "ship_additional_".$shipmethodid;
        $result->ship_type          		= "Standard";
        if($translate){
        	$result->ship_carrier_name          = JText::_($this->params->get($carrier));
        	$result->ship_carrier_code 			= JText::_($this->params->get($carrier));
        	$result->ship_method_name           = JText::_($this->params->get($method));
        	$result->ship_method_code 			= JText::_($this->params->get($method));
        }else{
        	$result->ship_carrier_name          = $this->params->get($carrier);
        	$result->ship_carrier_code 			= $this->params->get($carrier);
        	$result->ship_method_name           = $this->params->get($method);
        	$result->ship_method_code 			= $this->params->get($method);
        }
        $result->ship_carrier_name          = $this->params->get($carrier);
        $result->ship_carrier_code 			= $this->params->get($carrier);
        $result->ship_method_name           = $this->params->get($method);
        $result->ship_method_code 			= $this->params->get($method);
        $result->ship_handling_charge       = $this->params->get($handling);
        $result->ship_handling_additional   = $this->params->get($additional);
        $result->cost                       = $this->calculateShipping($order,$result);
        $result->tracking_id 				= '';

        return $result;
     }
         
         
    
    /**
     * calculateShipping
     * 
     * @param array $cart
     * @return int
     */
	function calculateShipping($order, $shipMethod){
		//how many items need shipping
		$numberItems = 0;
		$shipping_total = 0.00;

		for($i = 0; $i < count($order->items); $i++) {
            if($order->items[$i]->product_downloadable){
                continue;
            }else{
                $numberItems += $order->items[$i]->quantity;
            }
        }

		if($numberItems){
			$remainder = $numberItems - 1;
			$shipping_total = $shipMethod->ship_handling_charge + $remainder * $shipMethod->ship_handling_additional;
			$shipping_total = sprintf("%.2f", $shipping_total);

		}

		if($this->params->get('max_shipping') > 0.00)
			if(round($order->order_subtotal,2) > round($this->params->get('max_shipping'), 2)){ 
			$shipping_total = 0.00; 
		}

		return $shipping_total;
	}
} ?>