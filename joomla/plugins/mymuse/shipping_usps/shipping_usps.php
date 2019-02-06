<?php

//defined ('_JEXEC') or die('Restricted access');

/**
*  This shipping plugin is used with a production USPS Shipping Account
*  to obtain the proper shipping rates and options from USPS using the weight of the
*  products in the cart. 
*
* @version 1.0.0.
* @package MyMuse
* @copyright Copyright (C) 2018 Arboreta Internet Systems.
* @license GNU General Public License version 3, or later http://www.gnu.org/licenses/gpl.html
*
* based on
* @version v6.9.1 2018/03/09 by Park Beach Systems, Inc.
* @package VirtueMart
* @subpackage shipping
* @copyright Copyright (C) 2018 Park Beach Systems, Inc. All rights reserved.
* @license GNU General Public License version 3, or later http://www.gnu.org/licenses/gpl.html
*/

//define('JPATH_USPSPLUGIN', JPATH_ROOT . '/plugins/mymuse/shipping_usps');
if (!class_exists('MMShippingPlugin')) {
	require ('api/mm_ship.php');
}
//JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
//JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
/**
* This is the Shipping class to call the USPS API for shipping costs
*/



class plgMymuseShipping_usps extends MMShippingPlugin
{

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	


	var $usps_username;
	var $usps_password;
	var $usps_server;
	var $usps_path;
	var $usps_proxyserver;
	var $usps_reporterrors;
	var $usps_machinable;
	var $usps_padding;
	var $usps_packagesize;
	var $usps_flat_rate_priority_avail; //Holds the flat rate Priority Mail method which passed conditions. Checked to avoid larger containers for same service
	var $usps_flat_rate_priority_express_avail; //Holds the flat rate Priority Mail method which passed conditions. Checked to avoid larger containers for same service
	

	var $order_weight = 0.0;
	var $destination_address = null;
	var $source_address = null;
	var $methods = array();
	var $currency = 'USD';


	// instance of class
	public static $_this = FALSE;

	/**
	 * @param object $subject
	 * @param array  $config
	 */
	function __construct (& $subject, $config) {
		parent::__construct ($subject, $config);

		//$this->_loggable = TRUE;
		//$this->_tablepkey = 'id';
		//$this->_tableId = 'id';
		//$this->tableFields = array_keys ($this->getTableSQLFields ());
		//$varsToPush = $this->getVarsToPush ();
		//$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
		$this->_debug = $this->params->get( 'debug', 0);
		$this->usps_username = $this->params->get( 'USPS_USERNAME', '' );
		$this->usps_password = $this->params->get( 'USPS_PASSWORD', '' );
		$this->usps_server = $this->params->get( 'USPS_SERVER', '' ); //"http://proxy.shr.secureserver.net:3128";
		$this->usps_path = '/ShippingAPI.dll';
		$this->usps_proxyserver = $this->params->get( 'USPS_PROXYSERVER', '' );
		$this->usps_reporterrors = $this->params->get( 'USPS_REPORTERRORS', 1 );
		$this->usps_packagesize = $this->params->get( 'USPS_PACKAGESIZE', 'REGULAR' );
		$this->usps_padding = $this->params->get( 'USPS_PADDING', '' );
		$this->usps_machinable = $this->params->get( 'USPS_MACHINABLE', 0 );
		$this->usps_smart_flatrate = $this->params->get( 'USPS_SMART_FLATRATE', 0 );
		$this->usps_rate_type = $this->params->get( 'USPS_RATE_TYPE', 'RETAIL' );
		$this->shipment_weightunit = 'LB';
		$this->max_weight = '70'; //hard-coded max for now
		$this->usps_flat_rate_priority_avail = -1;
		$this->usps_flat_rate_priority_express_avail = -1;
		$this->_makeMethods();

		$params 							= MyMuseHelper::getParams();
		$pcurrency 							= $params->get('my_currency_code', 0);
		$this->currency 					= ($pcurrency != 0)? $pcurrency : $this->currency;

		$this->source_address 				= (object)[];
		$this->source_address->zip			= $params->get('zip');
		$this->source_address->country		= $params->get('country'); // is country_2_code which i swhat we want
		$this->source_address->country_id 	= $this->getCountryIDByName($this->source_address->country);
		$this->source_address->state_name	= $params->get('province');
		$this->source_address->state_id 	= $this->getStateIDByName($this->source_address->state_name);
		$session 							= JFactory::getSession();
		$profile							= $session->get('myprofile');


		if(isset($profile['shipping_postal_code'])){
			$profile['postal_code'] = $profile['shipping_postal_code'];
		}
		if(isset($profile['shipping_country'])){
			$profile['country'] = $profile['shipping_country'];
		}
		if(isset($profile['shipping_region'])){
			$profile['region'] = $profile['shipping_region'];
		}
		
		if($profile && isset($profile['postal_code']) && isset($profile['country'])){
			$this->destination_address = (object)[];
			$this->destination_address->zip			= $profile['postal_code'];
			//we have country_3_code, want country_2_code
			$this->destination_address->country_id  = $this->getCountryIDByName($profile['country']);
			$this->destination_address->country_2_code 	= $this->getCountryByID($this->destination_address->country_id, 'country_2_code');	
			$this->destination_address->state_id	= isset($profile['region'])? $profile['region'] : '';	
		}

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


		$this->order_weight = $this->getOrderWeight($order, 'LB');

		$result = array();
		$j = 0;
		foreach($this->methods as $i => $method){

			if($this->checkConditions ($order, $method)){
				$result[$j] 	= new JObject;
				$result [$j]->id = $i;
				
				
				$additional = 0;
				$result [$j]->ship_carrier_name =  'USPS';
				$result [$j]->ship_method_name = $method->USPS_SERVICE;
				$result [$j]->ship_handling_charge = $method->USPS_HANDLINGFEE;
				$result [$j]->ship_handling_type = $method->USPS_HANDLINGFEE_TYPE;
				$result [$j]->cost = $this->shipmethod_rate[$method->id];
				$j++;
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
 
        //print_pre($this->methods[$shipmethodid]);
        $this->order_weight = $this->getOrderWeight($order, 'LB');
        $result = new JObject;
        $result->id = $shipmethodid;
        $result->ship_type          		= 'USPS';

        $result->ship_carrier_name          = 'USPS';
        $result->ship_carrier_code 			= 'USPS';
        $result->ship_method_name           = $this->methods[$shipmethodid]->USPS_SERVICE;
        $result->ship_method_code 			= $this->methods[$shipmethodid]->USPS_SERVICE;
        $result->ship_handling_type   		= $this->methods[$shipmethodid]->USPS_HANDLINGFEE_TYPE;
        $result->ship_handling_fee			= $this->methods[$shipmethodid]->USPS_HANDLINGFEE;
        $result->ship_handling_charge		= 0.00;
        $result->order_weight				= $this->order_weight;




        $this->logInfo("\n\n--------------\nonCaclulateMyMuseShipping\n\n");
        if($this->checkConditions ($order, $this->methods[$shipmethodid])){
        	$result->cost                       = $this->shipmethod_rate[$shipmethodid];
        	if($result->ship_handling_type != "NONE"){
        		$result->ship_handling_charge       = isset($this->shipmethod_handling[$shipmethodid])? $this->shipmethod_handling[$shipmethodid] : 0.00;
        	}
    	}
        $result->tracking_id 				= '';

        return $result;
     }


	/**
	 * logInfo
	 * to help debugging Payment notification for example
	 * Keep it for compatibilty
	 */
	protected function logInfo ($text, $type = 'message', $doLog=false) {
		$doLog = 1;
		if ((isset($this->_debug) and $this->_debug) OR $doLog) {
			MyMuseHelper::logMessage( $text  );
		}
	}

	/**
	 * Get the total weight for the order, based on which the proper shipping rate
	 * can be selected.
	 *
	 * @param object $cart Cart object
	 * @return float Total weight for the order
	 */
	protected function getOrderWeight ($order, $to_weight_unit) {

		static $weight = array();
		if(!isset($weight[$to_weight_unit])) $weight[$to_weight_unit] = 0.0;
		if(count($order->items)>0 and empty($weight[$to_weight_unit])){

			foreach ($order->items as $product) {
				$weight[$to_weight_unit] += ($this->convertWeightUnit ($product->product_weight, $product->product_weight_uom, $to_weight_unit) * $product->quantity);
				$this->logInfo("\n\n#################################\nEach product: weight= ".$product->product_weight.". uom = ".$product->product_weight_uom." to weight =". $to_weight_unit."\n");
			}
		}

		return $weight[$to_weight_unit];
	}

	protected function _makeMethods () {
		for($i = 1; $i < 5; $i++){
			if(!$this->params->get("ship_".$i."_active")){
				continue;
			}
			$this->methods[$i] = new JObject;
			$this->methods[$i]->id = $i;
			$this->methods[$i]->USPS_SERVICE = $this->params->get('USPS_SERVICE_'.$i);
			$this->methods[$i]->USPS_HANDLINGFEE_TYPE = $this->params->get('USPS_HANDLINGFEE_TYPE_'.$i);
			$this->methods[$i]->USPS_HANDLINGFEE = $this->params->get('USPS_HANDLINGFEE_'.$i);
			$this->methods[$i]->countries = $this->params->get('countries_'.$i);
			$this->methods[$i]->zip_start = $this->params->get('zip_start_'.$i);
			$this->methods[$i]->zip_stop = $this->params->get('zip_stop_'.$i);
			$this->methods[$i]->orderamount_start = $this->params->get('orderamount_start_'.$i);
			$this->methods[$i]->orderamount_stop = $this->params->get('orderamount_stop_'.$i);
			$this->methods[$i]->categories = $this->params->get('categories_'.$i);
			$this->methods[$i]->blocking_categories = $this->params->get('blocking_categories_'.$i);
			$this->methods[$i]->maximum_boxes = $this->params->get('maximum_boxes_'.$i);
			$this->methods[$i]->shipment_name = 'USPS '.$this->methods[$i]->USPS_SERVICE;
		}
	}



	/**
	 * @param \MyMuse 		  $cart
	 * @param int             $method
	 * @param array           $cart_prices
	 * @return bool
	 */
	protected function checkConditions ($order, $method) {
		$this->logInfo('*******************************************************************');
		$this->logInfo('Checking conditions for '.$method->shipment_name.'('.trim($method->USPS_SERVICE).')');
		//7/24/2013 - changes to add condition weight variable and clear selected id if conditions not met
		$usps_cond = false;
		$this->convert ($method);
		$pluginmethod_id = $method->id;

		//9/2/2014 - Clean USPS service name to support service names saved with old variations
		$method->USPS_SERVICE = $this->getCleanServiceName($method->USPS_SERVICE);
				
		//Order weight conditions
		$orderWeight = $this->order_weight;
		$orderWeightcond = true;
		if ($orderWeight <= 0) {
			$this->logInfo(' FALSE Reason: Cart weight is '.$orderWeight);
			$orderWeightcond = false;
		}
		if($orderWeight > 70.00 && !$this->allow_multi_box ) {
			$this->logInfo (' FALSE Reason: Cart weight of '.$orderWeight.' pounds exceeds the 70 pound limit.');
			$orderWeightcond = false;
		}
		
		//Order country/zip conditions
		$address = $this->destination_address;
		$countries = array();			

		if(isset($address->country_id)) {
			$dest_country = $address->country_2_code;
			if(!empty($method->countries)) {
				if(!is_array ($method->countries)) {
					$countries[0] = $method->countries;
				}else{
					$countries = $method->countries;
				}
			}
			if(in_array($address->country_id, $countries) || count($countries) == 0) {	
				$country_cond = true;
			}else{
				$this->logInfo(' FALSE for variable country_id = '.implode($countries,', ').', Reason: Country does not fit');
				$country_cond = false;
			}
			$isDomesticMethod = $this->_isDomesticShippingMethod(trim($method->USPS_SERVICE));
			if(( $dest_country == "US") || ( $dest_country == "PR") || ( $dest_country == "VI")){
				if(!$isDomesticMethod){
					$this->logInfo(' FALSE Reason: Destination country is US/PR/VI but method is International');
					$country_cond = false;
				}
			}else{
				if($isDomesticMethod){
					$this->logInfo(' FALSE Reason: Destination country is not US/PR/VI but method is Domestic');
					$country_cond = false;
				}
			}
		}else{ //no destination country
			//$address->country_id = 0;
			$this->logInfo(' FALSE Reason: Destination country is not set');
			$country_cond = false;
		}
		//test by zip
		if(isset($address->zip)) {
			$zip_cond = $this->testRange($address->zip,$method,'zip_start','zip_stop','zip');
		} else {
			$this->logInfo(' FALSE Reason: Destination zip is not set');
			$zip_cond = false;
		}
		
		//Order sales amount condition
		if(isset($order->order_total)){
			$orderamount_cond = $this->testRange($order->order_total,$method,'orderamount_start','orderamount_stop','order amount');
		}else{
			$orderamount_cond = FALSE;
		}
		
		//Category Conditions
		// Categories: if a product is in a selected category, display the shipment
		// Blocking category: if a product is in a selected category, DO NOT display the shipment
		$cat_cond = true;
		

		if($method->categories or $method->blocking_categories){
			if($method->categories)$cat_cond = false; //begin as false until 1 product in list of Categories
			if(!is_array($method->categories)) $method->categories = array($method->categories);
			if(!is_array($method->blocking_categories)) $method->blocking_categories = array($method->blocking_categories);
			//Loop through products in cart and check Categories and Blocking Categories
			foreach($order->items as $product){
				if(array_intersect(array($product->catid),$method->categories)){
					$cat_cond = true; //show method since 1 product in Categories
				}
				if(array_intersect(array($product->catid),$method->blocking_categories)){
					$this->logInfo(' FALSE Reason: product ' . $product->product_sku . ' in blocking categories');
					$cat_cond = false; //force no show if 1 product in BlockingCategories
					break;
				}
			}
			if($cat_cond == false) $this->logInfo(' FALSE Reason: products did not pass category conditions.');
		}

		$allconditions = (int)$orderWeightcond + (int)$zip_cond + (int)$country_cond + (int)$orderamount_cond + (int)$cat_cond;;
		$fitbox_cond = true; //default
		do if($allconditions === 5){
			//Create shipping containers
			$usps_container = $this->_getShippingMethodBox(trim($method->USPS_SERVICE));
			$usps_container_name = $usps_container['name'];
			
			if(isset($usps_container['flatratelevel'])){
				if(strpos($method->USPS_SERVICE, 'Express') !== false){
					//Can not be equal to level due to reprocess of rates when VM Auto Select of Shipping method is set
					//if($this->usps_flat_rate_priority_express_avail >= $usps_container['flatratelevel']){
					if(isset($usps_container['flatratelevel']) && ($this->usps_flat_rate_priority_express_avail > $usps_container['flatratelevel'])){
						$this->logInfo (' FALSE Reason: Flat rate Priority Mail Express container is larger than another flat rate container already available.');
						$fitbox_cond = false; //container not used
						break;
					}
				}else{
					//if($this->usps_flat_rate_priority_avail >= $usps_container['flatratelevel']){
					if(isset($usps_container['flatratelevel']) && ($this->usps_flat_rate_priority_avail > $usps_container['flatratelevel'])){
						$this->logInfo (' FALSE Reason: Flat rate Priority Mail container is larger than another flat rate container already available.');
						$fitbox_cond = false; //container not used
						break;
					}
				}
			} 

			if(empty($this->containersToShip[$usps_container_name])) {
				//Pack cart into proper container size
				$this->containersToShip[$usps_container_name] = $this->_packContainers($order, $usps_container);
			}
			if(empty($this->containersToShip[$usps_container_name])) {
				$this->logInfo (' FALSE Reason: Products are not able to fit into a container.');
				$fitbox_cond = false; //no containers for boxes
				break;
			}
			$total_containers = count($this->containersToShip[$usps_container_name]);
			if(isset($method->maximum_boxes) && $method->maximum_boxes > 0 && ($total_containers > $method->maximum_boxes)){
				$this->logInfo (' FALSE Reason: Products require '.$total_containers.' boxes which exceeds the shipping method parameters of '.$method->maximum_boxes.'.');
				$fitbox_cond = false; //max containers reached for shipping method param
			}elseif($total_containers > 25){
				$this->logInfo (' FALSE Reason: Products require over 25 boxes which exceeds USPS shipping API.');
				$fitbox_cond = false; //max containers reached for USPS
			}else{
				$fitbox_cond = true; //all boxes fit in container(s)
			}
		} while(false);
		
		$allconditions = $allconditions + (int)$fitbox_cond;

		if($allconditions === 6){
			//Continue with USPS API call
			$usps_service_code = $this->_getServiceCode(trim($method->USPS_SERVICE));
			//if($this->containersShipCost[$usps_container_name] == null){
			if(!isset($this->containersShipCost[$usps_container_name])){
				$this->_getUSPSRates($orderWeight, $order, $address, $order->order_total, $usps_service_code, $usps_container_name);
			}					
			$count = (isset($this->containersShipCost[$usps_container_name]) ? count($this->containersShipCost[$usps_container_name]) : 0);
			if ($count < 1){
				$this->logInfo('  No USPS options returned from USPS service.');
			}else{
				$this->logInfo('  USPS returned '.$count.' possible mail service options.');
				$i = 0;
				while ($i < $count) {
					// USPS returns Charges in USD.
					if (trim($this->containersShipCost[$usps_container_name][$i]['name']) == trim($method->USPS_SERVICE)){
						//set rate of method as usps_ship_rate to support autoselect when one shipping method exists
						$methodSalesPrice = floatval($this->containersShipCost[$usps_container_name][$i]['cost']);
						$handling = $this->_getHandlingCost($method, $methodSalesPrice, $order->order_total);
						$methodSalesPrice = $methodSalesPrice + $handling;
						$this->shipmethod_rate[$method->id] = $methodSalesPrice;
						$this->shipmethod_handling[$method->id] = $handling;
						//If Smart Flat Rate shipping set flatratelevel global level to prevent larger flat rate boxes from appearing if small already an option
						if(isset($usps_container['flatratelevel'])){
							//$this->logInfo('  Setting Flat Rate Global level to '.$usps_container['flatratelevel']);
							if(strpos($method->USPS_SERVICE, 'Express') !== false){
								$this->usps_flat_rate_priority_express_avail = $usps_container['flatratelevel'];
							}else{
								$this->usps_flat_rate_priority_avail = $usps_container['flatratelevel'];
							} 
						}
						$this->logInfo('  Setting USPS cost for shipment_rate'.$method->id.' = '.$this->shipmethod_rate[$method->id]);
						$usps_cond = true;
						break;
					}
					$i++;
				}
				if(!$usps_cond){
					$this->logInfo('  '.$method->shipment_name.' not matched with returned services.'.$method->id);
				}
			}
		}
		if($usps_cond){
			$this->logInfo('  '.$method->shipment_name.' DOES apply for this cart.');
			return TRUE;
		}else{
			$this->logInfo('  '.$method->shipment_name.' DOES NOT apply for this cart.');
			//7-22-2013- if this shipping method is currently selected but fails conditions we want to remove it.
			//this is required because we do not recall 'checkconditions' when setting price on CART page.
			//if($method->$pluginmethod_id == $cart->virtuemart_shipmentmethod_id){
				//$this->logInfo(' MATCH: method_id='.$method->$pluginmethod_id.' virtuemart_shipmentmethod_id='.$cart->virtuemart_shipmentmethod_id);
			//	$cart->virtuemart_shipmentmethod_id = null;
			//}
			return FALSE;
		}
	}


	/**
	 * @param $method
	 */
	function convert (&$method) {

		$method->orderamount_start = (float)$method->orderamount_start;
		$method->orderamount_stop = (float)$method->orderamount_stop;
		$method->zip_start = (int)$method->zip_start;
		$method->zip_stop = (int)$method->zip_stop;
	
	}

	/**
	* This function cleans the service name.
	* @param                $servicename
	* @return string
	*/
	function getCleanServiceName($serviceName){
		$serviceName = str_replace( "&lt;sup&gt;&amp;reg;&lt;/sup&gt;" , "" , $serviceName);
		$serviceName = str_replace( "&lt;sup&gt;&amp;trade;&lt;/sup&gt;" , "" , $serviceName);
		$serviceName = str_replace( "&lt;sup&gt;&#174;&lt;/sup&gt;" , "" , $serviceName); //July 2013
		$serviceName = str_replace( "&lt;sup&gt;&#8482;&lt;/sup&gt;" , "" , $serviceName); //July 2013
		$serviceName = str_replace( " 1-Day" , "" , $serviceName); //July 2013 - remove varying text from servicename
		$serviceName = str_replace( " 2-Day" , "" , $serviceName); //July 2013 - remove varying text from servicename
		$serviceName = str_replace( " 3-Day" , "" , $serviceName); //July 2013 - remove varying text from servicename
		$serviceName = str_replace( " Military" , "" , $serviceName); //July 2013 - remove varying text from servicename
		$serviceName = str_replace( " DPO" , "" , $serviceName); //July 2013 - remove varying text from servicename
		$serviceName = str_replace( " APO/FPO/DPO" , "" , $serviceName); //Sept 2014 - remove varying text from servicename
		
		$serviceName = str_replace( "&lt;sup&gt;&amp;reg;&lt;/sup&gt;" , "" , $serviceName);
		$serviceName = str_replace( "&lt;sup&gt;&amp;trade;&lt;/sup&gt;" , "" , $serviceName);
		$serviceName = str_replace( "&lt;sup&gt;&#174;&lt;/sup&gt;" , "" , $serviceName); //July 2013
		$serviceName = str_replace( "&lt;sup&gt;&#8482;&lt;/sup&gt;" , "" , $serviceName); //July 2013
		$serviceName = str_replace( "**" , "" , $serviceName); //September 2014
		
		return trim($serviceName);
	}


	private function testRange($value, $method, $floor, $ceiling,$name){
		$result = '';
		$cond = true;
		if(!empty($method->$floor) and !empty($method->$ceiling)){
			$cond = (($value >= $method->$floor AND $value <= $method->$ceiling));
			if(!$cond){
				$result = 'FALSE';
				$reason = 'is NOT within Range of the condition from '.$method->$floor.' to '.$method->$ceiling;
			} else {
				//$result = 'TRUE';
				//$reason = 'is within Range of the condition from '.$method->$floor.' to '.$method->$ceiling;
			}
		} else if(!empty($method->$floor)){
			$cond = ($value >= $method->$floor);
			if(!$cond){
				$result = 'FALSE';
				$reason = 'is not at least '.$method->$floor;
			} else {
				//$result = 'TRUE';
				//$reason = 'is over min limit '.$method->$floor;
			}
		} else if(!empty($method->$ceiling)){
			$cond = ($value <= $method->$ceiling);
			if(!$cond){
				$result = 'FALSE';
				$reason = 'is over '.$method->$ceiling;
			} else {
				//$result = 'TRUE';
				//$reason = 'is lower than the set '.$method->$ceiling;
			}
		} else {
			//$result = 'TRUE';
			//$reason = 'no boundary conditions set';
		}
		
		if($result == 'FALSE'){
			$this->logInfo($method->shipment_name.' = '.$result.' for variable '.$name.' = '.$value.' Reason: '.$reason);			
		}

		return $cond;
	}


	/**
	* This method determines if shipping method is domestic or international.
	* @param	$service_name
	* @return	address array
	*/
	function _isDomesticShippingMethod($service_name){
		$service_name = strtolower($service_name);
		if((strpos($service_name, 'international') == true) || (strpos($service_name, 'gxg') == true)) {
			return false;
		}else{
			return true;	
		}
	}	



	/**
	 * Renders the list for the Length, Width, Height Unit
	 *
	 */
	static function renderLWHUnitList ($name, $selected) {

		if (!class_exists ('VmHTML')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
		}

		$lwh_unit_default = array('M' => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_M')
		, 'CM'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_CM')
		, 'MM'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_MM')
		, 'YD'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_YARD')
		, 'FT'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_FOOT')
		, 'IN'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_INCH')
		);
		foreach ($lwh_unit_default as  $key => $value) {
			$lu_list[] = JHtml::_ ('select.option', $key, $value, $name);
		}
		$listHTML = JHtml::_ ('Select.genericlist', $lu_list, $name, '', $name, 'text', $selected);
		return $listHTML;

	}


	/**
	 * Return the countryname or code of a given countryID
	 *
	 * @access public
	 * @param int $id Country ID
	 * @param char $fld Field to return: country_name (default), country_2_code or country_3_code.
	 * @return string Country name or code
	 */
	static public function getCountryByID ($id, $fld = 'country_name') {

		if (empty($id)) {
			return '';
		}

		$id = (int)$id;
		$db = JFactory::getDBO ();

		$q = 'SELECT `' . $db->escape ($fld) . '` AS fld FROM `#__mymuse_country` WHERE id = ' . (int)$id;
		$db->setQuery ($q);
		$r = $db->loadObject ();
		return $r->fld;
	}

	/**
	 * Return the id of a given country name
	 *
	 * @access public
	 * @param string $name Country name (can be country_name or country_3_code  or country_2_code )
	 * @return int virtuemart_country_id
	 */
	static public function getCountryIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		if (strlen ($name) === 2) {
			$fieldname = 'country_2_code';
		} else {
			if (strlen ($name) === 3) {
				$fieldname = 'country_3_code';
			} else {
				$fieldname = 'country_name';
			}
		}
		$db = JFactory::getDBO ();
		$q = 'SELECT `id` FROM `#__mymuse_country` WHERE `' . $fieldname . '` = "' . $db->escape ($name) . '"';

		$db->setQuery ($q);
		return $db->loadResult ();
	}

	/**
	 * Return the statename or code of a given id
	 *
	 * @access public
	 * @param int $id State ID
	 * @param char $fld Field to return: state_name (default), state_2_code or state_3_code.
	 * @return string state name or code
	 */
	static public function getStateByID ($id, $fld = 'state_name') {

		if (empty($id)) {
			return '';
		}
		$db = JFactory::getDBO ();
		$q = 'SELECT ' . $db->escape ($fld) . ' AS fld FROM `#__mymuse_state` WHERE id = "' . (int)$id . '"';
		$db->setQuery ($q);
		$r = $db->loadObject ();
		return $r->fld;
	}

	/**
	 * Return the stateID of a given state name
	 *
	 * @access public
	 * @param string $name Country name
	 * @return int virtuemart_state_id
	 */
	static public function getStateIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		$db = JFactory::getDBO ();
		if (strlen ($name) === 2) {
			$fieldname = 'state_2_code';
		} else {
			if (strlen ($name) === 3) {
				$fieldname = 'state_3_code';
			} else {
				$fieldname = 'state_name';
			}
		}
		$q = 'SELECT `id` FROM `#__mymuse_state` WHERE `' . $fieldname . '` = "' . $db->escape ($name) . '"';
		$db->setQuery ($q);
		$r = $db->loadResult ();
		return $r;
	}



	/* This method returns the shipping box for the method.
	* @param                method service name
	* @return array
	*/
	function _getShippingMethodBox($service_name){
		//default
		$shippingbox['name'] = 'VARIABLE';
		$shippingbox['length'] = 999999;
		$shippingbox['width'] = 999999;
		$shippingbox['height'] = 999999;		
		//$shippingbox['max_weight'] = 70;
		$shippingbox['max_weight'] = $this->_isDomesticShippingMethod($service_name) ? 70 : 66; //USPS API not returning international 67-70lbs??
		$shippingbox['flatratelevel'] = null;
			
		//If Smart Flatrate is set define specific box sizes for shipping
		if($this->usps_smart_flatrate){
			switch($service_name){
		//Flat Rate Boxes
			case "Priority Mail Small Flat Rate Box": //8 5/8'' x 5 3/8'' x 1 5/8
			case "Priority Mail International Small Flat Rate Box":
				$shippingbox['name'] = 'SM FLAT RATE BOX';
				$shippingbox['length'] = 8.625;
				$shippingbox['width'] = 5.375;
				$shippingbox['height'] = 1.625;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 3;
				break;
			case "Priority Mail Medium Flat Rate Box": //11'' x 8 1/2'' x 5 1/2''
			case "Priority Mail International Medium Flat Rate Box":
				//TODO: $containers[] = array('length' => 13.625,'width' => 11.875,'height' => 3.375); //13 5/8" x 11 7/8" x 3 3/8"
				$shippingbox['name'] = 'MD FLAT RATE BOX';
				$shippingbox['length'] = 11;
				$shippingbox['width'] = 8.5;
				$shippingbox['height'] = 5.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 20; //lower weight for International
				$shippingbox['flatratelevel'] = 2;
				break;
			case "Priority Mail Large Flat Rate Box": //12" x 12" x 5 1/2"
			case "Priority Mail International Large Flat Rate Box":
				$shippingbox['name'] = 'LG FLAT RATE BOX';
				$shippingbox['length'] = 12;
				$shippingbox['width'] = 12;
				$shippingbox['height'] = 5.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 20; //lower weight for International
				$shippingbox['flatratelevel'] = 1;
				break;
		//Flat Rate Envelopes
			case "Priority Mail Small Flat Rate Envelope": //10" x 6" x 1/2"  Small envelope
			case "Priority Mail International Small Flat Rate Envelope":
				$shippingbox['name'] = 'SM FLAT RATE ENVELOPE';
				$shippingbox['length'] = 10;
				$shippingbox['width'] = 6;
				$shippingbox['height'] = 0.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 9;
				break;
			case "Priority Mail Window Flat Rate Envelope": //10" x 6" x 1/2"  Small window envelope
			case "Priority Mail International Window Flat Rate Envelope": 
				$shippingbox['name'] = 'WINDOW FLAT RATE ENVELOPE';
				$shippingbox['length'] = 10;
				$shippingbox['width'] = 6;
				$shippingbox['height'] = 0.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 9;
				break;
			case "Priority Mail Gift Card Flat Rate Envelope": //10" x 6" x 1/2"  Small gift card envelope
			case "Priority Mail International Gift Card Flat Rate Envelope":
				$shippingbox['name'] = 'GIFT CARD FLAT RATE ENVELOPE';
				$shippingbox['length'] = 10;
				$shippingbox['width'] = 7;
				$shippingbox['height'] = 0.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 8;
				break;
			case "Priority Mail Flat Rate Envelope": //12.5" x 9.5" x 1/2"  Regular envelope
			case "Priority Mail International Flat Rate Envelope":
			case "Priority Mail Express Flat Rate Envelope":
			case "Priority Mail Express International Flat Rate Envelope":
				$shippingbox['name'] = 'FLAT RATE ENVELOPE';
				$shippingbox['length'] = 12.5;
				$shippingbox['width'] = 9.5;
				$shippingbox['height'] = 0.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 7;
				break;
			case "Priority Mail Padded Flat Rate Envelope": //12.5" x 9.5" x 1/2"  Regular padded envelope
			case "Priority Mail International Padded Flat Rate Envelope": 
			case "Priority Mail Express Padded Flat Rate Envelope": 
			case "Priority Mail Express International Padded Flat Rate Envelope": 
				$shippingbox['name'] = 'PADDED FLAT RATE ENVELOPE';
				$shippingbox['length'] = 12.5;
				$shippingbox['width'] = 9.5;
				$shippingbox['height'] = 0.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 7;
				break;
			case "Priority Mail Legal Flat Rate Envelope": //15" x 9.5" x 1/2"  Legal envelope
			case "Priority Mail International Legal Flat Rate Envelope":
			case "Priority Mail Express Legal Flat Rate Envelope":
			case "Priority Mail Express International Legal Flat Rate Envelope":
				$shippingbox['name'] = 'LEGAL FLAT RATE ENVELOPE';
				$shippingbox['length'] = 15;
				$shippingbox['width'] = 9.5;
				$shippingbox['height'] = 0.5;
				if(strpos($service_name, 'International') !== false) $shippingbox['max_weight'] = 4; //lower weight for International
				$shippingbox['flatratelevel'] = 6;
				break;
			case "Priority Mail": //Set flatratelevel to 0 to prevent Priority Mail appearing if Flat Box or Envelope already used
			case "Priority Mail International":
			case "Priority Mail Express":
			case "Priority Mail Express International":
				$shippingbox['flatratelevel'] = 0;
				break;
			}
		}
		return $shippingbox;
	}
















	/**
	* This method executed to API call to USPS.
	* @param                $order_weight - The actual weight in the cart (prior to any padding)
	* @param                $cart - An array of the cart
	* @param                $destaddress - An array of the destination address that you are shipping to
	* @return currency
	*/
	private function _getUSPSRates($order_weight, $cart, $destaddress, $cartvalue, $usps_service_code, $usps_container_name) {
		$app 			= JFactory::getApplication();
		$dest_zip 		= trim($destaddress->zip);
		$dest_countryid = $destaddress->country_id;
		$dest_stateid 	= (isset($destaddress->state_id) ? $destaddress->state_id : 0);
		//Setup vendor address data
		$vendoraddress 	= $this->source_address;
		$source_zip 	= trim($vendoraddress->zip);
		$source_countryid = $vendoraddress->country_id;
		$source_stateid = $vendoraddress->state_id;
		
		$this->logInfo('****USPS API CALL: Total cart weight='.$order_weight.'lbs zipcode from='.$source_zip.' zipcode to='.$dest_zip.' countryid='.$dest_countryid.' service='.$usps_service_code.' container='.$usps_container_name.'****');
		//Store variables to cart which are used to determine if another call to USPS API is required on future requests.
		$session = JFactory::getSession();
		require_once( JPATH_COMPONENT.DS.'mymuse.class.php');
		$MyMuseCart = MyMuse::getObject('cart','helpers');
		$MyMuseCart->usps_ship_weight  = $order_weight;
		$MyMuseCart->usps_ship_source_zip = $source_zip;
		$MyMuseCart->usps_ship_dest_zip = $dest_zip;



		//Require that zip codes are only first 5 digits. USPS api requirement. this is done after storing in SESSION so that a change in extended zip code will trigger update.
		$source_zip = substr($source_zip, 0, 5);
		$dest_zip = substr($dest_zip, 0, 5);
				
		if($this->usps_reporterrors == 1) $usps_reporterrors = 1;
		else $usps_reporterrors = 0;
		
		if($order_weight > 0) {
			$weight_measure = 'LB';
			$usps_packagesize = $this->usps_packagesize;
										
			//USPS Machinable for Parcel Post
			$usps_machinable = $this->usps_machinable;
			if ($usps_machinable == '1') $usps_machinable = 'TRUE';
			else $usps_machinable = 'FALSE';

			//$shpService = 'All';
			$shpService = $usps_service_code;
			
			if(empty($dest_countryid)) {
				if($usps_reporterrors) $app->enqueueMessage(JText::_('MYMUSESHIP_USPS_SHIP_COUNTRY_ID_EMPTY'), 'error');
				return;
			}
			if(empty($source_zip)) {
				if($usps_reporterrors) $app->enqueueMessage(JText::_('MYMUSESHIP_USPS_SHIP_SOURCE_ZIP_EMPTY'), 'error');
				return;
			}
			if(empty($dest_zip)) {
				if($usps_reporterrors) $app->enqueueMessage(JText::_('MYMUSESHIP_USPS_SHIP_DEST_ZIP_EMPTY'), 'error');
				return;
			}
			
			$dest_country = $this->getCountryByID ($dest_countryid, 'country_2_code');
			$dest_country_name = $this->getCountryByID ($dest_countryid);

			
			$vendorCurrency_code_3 = $this->currency;

			$domestic = 0; //default to international
			if(($dest_country == "US") || ($dest_country == "PR") || ($dest_country == "VI")){
				$domestic = 1; //domestic if US, PR or VI
			}
			//Build XML string based on service request
			if ($domestic){
				//the xml that will be posted to usps for domestic rates
				$xmlPost = 'API=RateV4&XML=<RateV4Request USERID="'.$this->usps_username.'" PASSWORD="'.$this->usps_password.'">';
				$xmlPost .= '<Revision>2</Revision>';
				// Loop through all containers to ship
				foreach($this->containersToShip[$usps_container_name] as $key => $container){
					$c_value = $container->get_packed_value();
					$c_weight = $container->get_packed_weight();
					$c_weight = $c_weight + ($c_weight * $this->_getBoxPaddedAmount());  //2/12/2018 Pad the shipping weight to allow weight for shipping materials
					$c_weight = number_format($c_weight, 3); //go to 3 decimals
					if( $c_weight > 70.00 ) { //If one package is greater than USPS maximum then exit
						if($usps_reporterrors) $app->enqueueMessage( JText::sprintf('MYMUSESHIP_USPS_SHIP_WEIGHT_GT70', $c_weight), 'error');
						return;
					}							
					//Determine weight in pounds and ounces (USPS service will round lbs up when needed)
					$shipping_pounds = floor ($c_weight); //send integer rounded down
					$shipping_ounces = ceil(16 * ($c_weight - floor($c_weight))); //send integer rounded up
					
					$this->logInfo('  Container '.$key.' weight='.$c_weight.$weight_measure.' value='.$c_value.$vendorCurrency_code_3);					
					
					$xmlPost .= '<Package ID="'.$key.'">';
					$xmlPost .= "<Service>".$shpService."</Service>";
					$xmlPost .= "<ZipOrigination>".$source_zip."</ZipOrigination>";
					$xmlPost .= "<ZipDestination>".$dest_zip."</ZipDestination>";
					$xmlPost .= "<Pounds>".$shipping_pounds."</Pounds>";
					$xmlPost .= "<Ounces>".$shipping_ounces."</Ounces>";
					$xmlPost .= "<Container>".$usps_container_name."</Container>";
					$xmlPost .= "<Size>".$usps_packagesize."</Size>";
					$xmlPost .= "<Machinable>".$usps_machinable."</Machinable>";
					$xmlPost .= "</Package>";
				}
				$xmlPost .= "</RateV4Request>";
			}else{
				//the xml that will be posted to usps for international rates
				$xmlPost = 'API=IntlRateV2&XML=<IntlRateV2Request USERID="'.$this->usps_username.'" PASSWORD="'.$this->usps_password.'">';
				$xmlPost .= '<Revision>2</Revision>';
				// Loop through all containers to ship
				foreach($this->containersToShip[$usps_container_name] as $key => $container){
					$c_value = $container->get_packed_value();
					$c_weight = $container->get_packed_weight();
					$c_weight = $c_weight + ($c_weight * $this->_getBoxPaddedAmount());  //2/12/2018 Pad the shipping weight to allow weight for shipping materials
					$c_weight = number_format($c_weight, 3); //go to 3 decimals
					if( $c_weight > 70.00 ) { //If one package is greater than USPS maximum then exit
						if($usps_reporterrors) $app->enqueueMessage( JText::sprintf('MYMUSESHIP_USPS_SHIP_WEIGHT_GT70', $c_weight), 'error');
						return;
					}							
					//Determine weight in pounds and ounces (USPS service will round lbs up when needed)
					$shipping_pounds = floor ($c_weight); //send integer rounded down
					$shipping_ounces = ceil(16 * ($c_weight - floor($c_weight))); //send integer rounded up
					
					$this->logInfo('  Container '.$key.' weight='.$c_weight.$weight_measure.' value='.$c_value.$vendorCurrency_code_3);
					
					$xmlPost .= '<Package ID="'.$key.'">';
					$xmlPost .= "<Pounds>".$shipping_pounds."</Pounds>";
					$xmlPost .= "<Ounces>".$shipping_ounces."</Ounces>";
					$xmlPost .= "<MailType>".$shpService."</MailType>";
					$xmlPost .= "<ValueOfContents>0.0</ValueOfContents>"; //no insurance functionality at this time
					$xmlPost .= "<Country>".$dest_country_name."</Country>";				
					$xmlPost .= "<Container>RECTANGULAR</Container>";
					$xmlPost .= "<Size>$usps_packagesize</Size>";
					$xmlPost .= "<Width>0</Width>";
					$xmlPost .= "<Length>0</Length>";
					$xmlPost .= "<Height>0</Height>";
					$xmlPost .= "<Girth>0</Girth>";
					$xmlPost .= "<OriginZip>".$source_zip."</OriginZip>"; //5-19-15: API update for Canada destinations
					//12-30-2014 - support online & plus rates
					if($this->usps_rate_type == "ONLINE"){
						$xmlPost .= "<CommercialFlag>Y</CommercialFlag>";
					}elseif($this->usps_rate_type == "PLUS"){
						$xmlPost .= "<CommercialPlusFlag>Y</CommercialPlusFlag>";
					}
					//12-30-2014 - end
					$xmlPost .= "</Package>";
				}
				$xmlPost .= "</IntlRateV2Request>";
			} 

			if($this->usps_server == "STAGING"){
				//$host = 'stg-production.shippingapis.com';
				$host = 'stg-secure.shippingapis.com';
				$protocol = "https";
				$port = 443;
				if(!function_exists("curl_init")) {
					$app->enqueueMessage('PHP cURL library is not installed. It is required in USPS Staging environment.', 'error');
					return;
				}
			}else{
				$host = 'production.shippingapis.com';
				//$host = 'secure.shippingapis.com'; //future use ssl for production - no support for http post
				$protocol = "http";
				$port = 80;
			}
			$path = $this->usps_path;
			$html = "";

			// Using cURL default and required for staging
			if( function_exists( "curl_init" )) {
				$CR = curl_init();
				curl_setopt($CR, CURLOPT_URL, $protocol."://".$host.$path);
				curl_setopt($CR, CURLOPT_POST, 1);
				curl_setopt($CR, CURLOPT_FAILONERROR, true);
				curl_setopt($CR, CURLOPT_POSTFIELDS, $xmlPost);
				curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($CR, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt ($CR, CURLOPT_CONNECTTIMEOUT, 20);
				curl_setopt ($CR, CURLOPT_TIMEOUT, 30);
				if (!empty($this->usps_proxyserver)){
					curl_setopt ($CR, CURLOPT_HTTPPROXYTUNNEL, TRUE);
					curl_setopt ($CR, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
					curl_setopt ($CR, CURLOPT_PROXY, $this->usps_proxyserver);
				}
				$xmlResult = curl_exec( $CR );
				$error = curl_error( $CR );
				curl_close( $CR );
			
			//HTTP Post - support ending when SSL required in production.
			}else{
				$fp = fsockopen($host, $port, $errno, $errstr, $timeout = 60);
				if( !$fp ) {
					$error = _USPS_RESPONSE_ERROR.": $errstr ($errno)";
				}else{
					//send the server request
					fputs($fp, "POST $path HTTP/1.1\r\n");
					fputs($fp, "Host: $host\r\n");
					fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
					fputs($fp, "Content-length: ".strlen($xmlPost)."\r\n");
					fputs($fp, "Connection: close\r\n\r\n");
					fputs($fp, $xmlPost . "\r\n\r\n");

					$xmlResult = '';
					//get the response
					$lineNum = 0;
					while ( !feof($fp) ) {
						$lineNum++;
						if ( $lineNum>500 ) {
							//don't let it run forever, line limit here
							break;
						}
						$line = fgets($fp, 512);
						$line = trim($line);
						if ( strpos( $line ,"HTTP/1")!==false) {
							$inHead = true;
						} elseif ( trim($line)=='') {
							//empty line marks the end of head
							$inHead = false;
							continue;
						}
						if ( $inHead ) {
							//skip
						} else {
							$xmlResult.= trim($line);
						}
						if ( strpos($line,"</RateV4Response>")!==false) {
							//this way we forcefully end the reading not waiting for the eof which may never come
							break;
						}
					}
				}
			}
			
			//Display textarea fields when in debug mode
			if($this->_debug ){
				echo 'XML Post: <br /><textarea cols="120" rows="5">'.$protocol.'://'.$host.$path.'?'.$xmlPost.'</textarea><br />XML Result:<br /><textarea cols="120" rows="12">'.$xmlResult.'</textarea><br />Cart Contents: '.$order_weight.'<br /><br/>';
			}
			$this->logInfo('**REQUEST**: HOST:'.$host.' PATH:'.$path.' '.print_r($xmlPost,true));
			$this->logInfo('**RESPONSE**:'.print_r($xmlResult,true));
			
			//Check for error from response from USPS
			if(!empty($error)) {
				if ($usps_reporterrors) $app->enqueueMessage('USPS Error: '.$error, 'error');
				$this->logInfo($error);
				return;
			}
			//Parse XML response from USPS
			$xmlDoc = new SimpleXMLElement($xmlResult);
			
			//Check for error in response
			if( strstr( $xmlResult, "Error" ) ) {
				$error = $xmlDoc->Package->Error->Description;
				if(empty($error)) $error = $xmlResult; //USPS API can return non-XML on error
				$error = 'USPS Response Error '.$xmlDoc->Package->Error->Number.': '.$error;
				if ($usps_reporterrors) $app->enqueueMessage($error, 'error');
				$this->logInfo($error);
				return;
			}
			
			//Get shipping options that are selected as available in VM from XML response
			$count = 0;
			$packageNumber = 0;
			$this->logInfo(' USPS API RATES RETURNED:');
			if ($domestic){ //domestic shipping response
				foreach ($xmlDoc->Package as $package) {
					$packageNumber++;
					$this->logInfo('  PACKAGE '.$package->attributes()->ID.':');
					foreach ($package->Postage as $postage) {
						$serviceId = $postage->attributes()->CLASSID;
						$serviceName = $postage->MailService;
						$serviceName = trim($this->getCleanServiceName($serviceName)); //remove special characters
						$postageCost = 0;
						if(!empty($postage->CommercialPlusRate) && ($this->usps_rate_type == "PLUS")){
							$postageCost = $postage->CommercialPlusRate;
						}elseif(!empty($postage->CommercialRate) && ($this->usps_rate_type == "ONLINE")){
							$postageCost = $postage->CommercialRate;
						}else{
							$postageCost = $postage->Rate;
						}
						$this->logInfo('    '.$serviceName.'(class='.$serviceId.') rate='.$postageCost.'USD');

						if ($vendorCurrency_code_3 != 'USD') { //convert to vendor currency if not USD (USPS always USD)
							//$paymentCurrency = CurrencyDisplay::getInstance();
							//$postageCost = $paymentCurrency->convertCurrencyTo('USD', $postageCost, TRUE);
							$this->logInfo('    '.$serviceName.' rate stored at store currency='.$postageCost.$vendorCurrency_code_3);
						}
						//check if service already in array to support multi-box
						$j = 0;
						$serviceNumber = -1;
						while ($j < $count && $serviceNumber == -1) {
							if ($this->containersShipCost[$usps_container_name][$j]['name'] == $serviceName){
								$serviceNumber = $j;
							}
							$j++;
						}
						if($serviceNumber == -1){ //new service
							if($packageNumber > 1) continue;//should not allow a new service if wasn't in first package
							$serviceNumber = $count;
							$count++;
						}else{ //exiting service
							$postageCost = floatval($postageCost) + floatval($this->containersShipCost[$usps_container_name][$serviceNumber]['cost']);
							$this->logInfo('     Total with previous packages='.$postageCost.$vendorCurrency_code_3);
						}

						$this->containersShipCost[$usps_container_name][$serviceNumber] = array(
							'id' => $serviceId, //for future use
							'name' => $serviceName,
							'cost' => $postageCost,
						);
					}
				}
												
			}else{ //international shipping response
				foreach ($xmlDoc->Package as $package) {
					$packageNumber++;
					$this->logInfo('  PACKAGE '.$package->attributes()->ID.':');
					foreach ($package->Service as $postage) {
						$serviceId = $postage->attributes()->ID;
						$serviceName = $postage->SvcDescription;
						$serviceName = $this->getCleanServiceName($serviceName); //remove special characters
						$postageCost = 0;
						if(!empty($postage->CommercialPlusPostage) && ($this->usps_rate_type == "PLUS")){
							$postageCost = $postage->CommercialPlusPostage;
						}elseif(!empty($postage->CommercialPostage) && ($this->usps_rate_type == "ONLINE")){
							$postageCost = $postage->CommercialPostage;
						}else{
							$postageCost = $postage->Postage;
						}
						$this->logInfo('    '.$serviceName.'(id='.$serviceId.') rate='.$postageCost.'USD');
						
						if ($vendorCurrency_code_3 != 'USD') { //convert to vendor currency if not USD (USPS always USD)
							$paymentCurrency = CurrencyDisplay::getInstance();
							$postageCost = $paymentCurrency->convertCurrencyTo('USD', $postageCost, TRUE);
							$this->logInfo('    '.$serviceName.' rate stored at store currency='.$postageCost.$vendorCurrency_code_3);
						}
						//check if service already in array to support multi-box
						$j = 0;
						$serviceNumber = -1;
						while ($j < $count && $serviceNumber == -1) {
							if ($this->containersShipCost[$usps_container_name][$j]['name'] == $serviceName){
								$serviceNumber = $j;
							}
							$j++;
						}
						if($serviceNumber == -1){ //new service
							if($packageNumber > 1) continue;//should not allow a new service if wasn't in first package
							$serviceNumber = $count;
							$count++;
						}else{ //exiting service
							$postageCost = floatval($postageCost) + floatval($this->containersShipCost[$usps_container_name][$serviceNumber]['cost']);
							$this->logInfo('     Total with previous packages='.$postageCost.$vendorCurrency_code_3);
						}

						$this->containersShipCost[$usps_container_name][$serviceNumber] = array(
							'id' => $serviceId, //for future use
							'name' => $serviceName,
							'cost' => $postageCost,
						);
					}
				}
			}

		}else{
			$app->enqueueMessage ('MYMUSESHIP_USPS_SHIP_WEIGHT_LT0', 'error');
			return;
		}

		return;
	} //end function getUSPSRates
}
?>
