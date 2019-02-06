<?php
/**
 * @version		$Id: shipping_price.php 1932 2017-11-24 14:08:35Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
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
	function __construct(&$subject, $config)  {
		parent::__construct($subject, $config);
		

	}
		
	function plgMymuseShipping_Price(&$subject, $config)  {
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

        //var_dump($this->params); exit;
        $result = array();
        $j = 0;
		for($i=1;$i<13;$i++){
            $param = "ship_".$i."_active";
            //is it active?
            if($this->params->get($param, 0)){
            	$good = 0;
            	//is all countries set?
            	if($this->params->get("ship_all_countries_$i")){
            	
            		//yes see if there are exceptions to exlude
            		if(isset($shopper->profile['country'])
            				&& in_array($shopper->profile['country'], $this->params->get("ship_countries_$i", array()))){
            			//we have an exeption
            		}else{
            			$good = 1;
            		}
            	// not 'all countries' but in the list of accepted
            	}elseif(isset($shopper->profile['country']) && in_array($shopper->profile['country'], $this->params->get("ship_countries_$i"))){
            		$good = 1;
            	}
            	 
            	if($good){
                	$result [$j] = new JObject ();
					$result [$j]->id = $i;
					$carrier = "ship_carrier_" . $i;
					$method = "ship_method_" . $i;
					$result [$j]->ship_carrier_name = $this->params->get ( $carrier );
					$result [$j]->ship_method_name = $this->params->get ( $method );
					
					for($k = 0; $k < 12; $k ++) {
						$percent = "ship_percent_" . $i . $k;
						$result[$j]->ship_percent [$k] = $this->params->get ( $percent );
					}
					$result[$j]->cost = $this->calculateShipping ( $order, $result [$j] );
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

        $cost = 0.00;

        $result = new JObject;
        $result->id = $shipmethodid;
        $carrier    = "ship_carrier_".$shipmethodid;
        $method      = "ship_method_".$shipmethodid;

        $result->ship_type          		= "Price";
        $result->ship_carrier_name          = $this->params->get($carrier);
        $result->ship_carrier_code 			= $this->params->get($carrier);
        $result->ship_method_name           = $this->params->get($method);
        $result->ship_method_code 			= $this->params->get($method);
        $result->tracking_id 				= '';
        
     	for($k=0;$k<12;$k++){
            $percent = "ship_percent_".$shipmethodid.$k;	
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
		for($k=1;$k<13;$k++){
			$min = "ship_minimum_".$k;
			$ship_minimum = $this->params->get($min);
			$max = "ship_maximum_".$k;
			$ship_maximum = $this->params->get($max);
			//echo "min $min max $max <br />";
			
			if(!$ship_maximum){
				$ship_maximum = 1000000000;
			}
			if(round($order->order_subtotal_physical,2) >= round($ship_minimum, 2)
					&&
				round($order->order_subtotal_physical,2) <= round($ship_maximum, 2)				
			){
				$percent = "ship_percent_".$shipMethod->id.($k-1);
				$amount = $this->params->get($percent);
				//echo $order->order_subtotal." ";

				//echo round($ship_minimum, 2)." ";
				//echo round($ship_maximum, 2)." ";
				//echo $percent; exit;
				break;
			}
			if($ship_maximum == 1000000000){
				break;
			}
		}

		if($this->params->get('ship_percent') && isset($amount)){
			$shipping_total = $order->order_subtotal_physical * $amount / 100;
		}elseif(isset($amount)){
			$shipping_total = $amount;
		}
		return $shipping_total;
	}
} ?>