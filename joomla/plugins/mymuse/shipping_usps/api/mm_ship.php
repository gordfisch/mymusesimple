<?php
//defined ('_JEXEC') or die('Restricted access');

/**
* PBS Shipping Common API
*
**
* @version v1.2 2017/11/9 by Park Beach Systems, Inc.
* @package PBS Shipping
* @copyright Copyright (C) 2017 Park Beach Systems, Inc. All rights reserved.
* @license GNU General Public License version 3, or later http://www.gnu.org/licenses/gpl.html
*/

//Load packing class
if (!class_exists ('LAFFPack_1_2')) {
	require ('laff-pack.php');
}
jimport ('joomla.plugin.plugin');


class MMShippingPlugin extends JPlugin {
	var $containersToShip = array();
	var $containersShipCost = array();
	var $boxes = array();
	var $allow_multi_box;
	var $shipmethod_rate = array(); //stores rates by shipmethodid
	var $vendoraddress = array();
	var $shipping_restrictions = false; //apply shipping restrictions
	var $allow_modifier = false; 

	protected $autoloadLanguage = true;

	function __construct (& $subject, $config) {
        parent::__construct($subject, $config);

		//$this->_loggable = TRUE;
		//$this->_tablepkey = 'id';
		//$this->_tableId = 'id';
		//$this->tableFields = array_keys ($this->getTableSQLFields ());
		//$varsToPush = $this->getVarsToPush ();
		//$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
		$this->_debug = $this->params->get( 'debug', 0);
		$this->allow_multi_box = $this->params->get( 'ALLOW_MULTI_BOX', 0 );
		$this->max_weight = $this->params->get( 'MAX_WEIGHT', '150' );


    }
	
	/**
	* This method handles the packaging of containers with boxes (products).
	* @param                $cart
	* @return containers
	*/
	function _packContainers($order, $containerDetails) {
		$packedContainers = array(); //array of packed containers
		
		//Setup boxes
		if (empty($this->boxes)){ //do not call if boxes already defined
			$boxes = $this->_buildBoxes($order->items, $containerDetails);
		}else{
			$boxes = $this->boxes;
		}
		if (empty($boxes)) {
			$this->logInfo(' Error: boxes empty');
			return null;
		}
		
		//Setup containers
		$container = $this->_getContainer($containerDetails);
		if (empty($container)) {
			$this->logInfo(' Error: container empty');
			return null;
		}
		
		//If allow multiple boxes then pack into multiple containers, otherwise fit into one.
		if($this->allow_multi_box){
			//Pack container(s): multi-box enabled
			$this->logInfo(' Packing Multi Container');
			while(count($boxes) > 0){
				// Initialize LAFFPack
				try {
					$lp = new LAFFPack_1_2();
					// Start packing our boxes
					$lp->pack($boxes, $container);

					if(count($lp->get_packed_boxes()) == 0){
						$this->logInfo(' Error: remaining boxes could not be packed into a container');
						return null;
					}
					$boxes = $lp->get_remaining_boxes();
					$packedContainers[] = $lp;
					$this->logInfo('  Packed container '.count($packedContainers).'. Weight='.$lp->get_packed_weight().' Boxes left= '.count($boxes));
				} catch (Exception $e) {
					$this->logInfo('LAFFPack Caught exception: ',  $e->getMessage());
					return null;
				}
			}
		}else{
			//Check if boxes fit into container
			$this->logInfo(' Packing Single Container');
			try {
				$lp = new LAFFPack_1_2();
				$lp->pack($boxes, $container);
				if($lp->get_remaining_number_boxes() == 0){
					$this->logInfo('  products DO fit into container');
					$packedContainers[] = $lp;
				}else{
					$this->logInfo('   products DO NOT fit into container');
				}
			} catch (Exception $e) {
				$this->logInfo('LAFFPack Caught exception: ',  $e->getMessage());
				return null;
			}
		}
		return $packedContainers;

	}
	function _buildBoxes($products, $containerDetails) {
		// Define our boxes: Id, Name, Length(IN), Width(IN), Height(IN), Weight(LB), Value(in store currency)
		$this->logInfo(' Building Boxes');
		unset($this->boxes);
		foreach ($products as $product) {
			$ship_modifier_text = '';
			//$length = 0.00001;
			//$width = 0.00001;
			//$height = 0.00001;			
			$length = self::convertDimensionUnit($product->product_length, $product->product_lwh_uom, 'IN');
			$width = self::convertDimensionUnit($product->product_width, $product->product_lwh_uom, 'IN');
			$height = self::convertDimensionUnit($product->product_height, $product->product_lwh_uom, 'IN');
			$weight = self::convertWeightUnit($product->product_weight, $product->product_weight_uom, 'LB');
			$price = $product->price['product_price'];

			//If shipping restrictions then apply to products
			if($this->shipping_restrictions){
				Productshippingattributes::applyToProduct($product);				
				if($product->ship_alone){ //set dimensions to max container size to force new container
					$length = $containerDetails['length'];
					$width = $containerDetails['width'];
					$height = $containerDetails['height'];
					$ship_modifier_text = ' [Ships alone]';
				}		

				if($this->allow_modifier){
					if(strpos($product->ship_modifier, "%")>0){
						//Update the weight based on modifier. 0%=free shipping, 50%=half product weight, 110%=(0.1) * weight
						$modifier = floatval($product->ship_modifier) / 100;
						$weight = $weight * $modifier;
						$ship_modifier_text = $ship_modifier_text.' [Ship modifier='.$modifier . ', weight before: '. $product->product_weight . ' weight after: '.$weight.']';
					}					
				}
				$max_per_package = floatval($product->max_per_package);
				if($max_per_package > 0){
					//echo 'height'.$height;
					//Maximum products per package should divide the height of the shipping container dimension by maximum boxes per container. This logic is temporarily solution until using actual container dimensions
					$length = $containerDetails['length'];
					$width = $containerDetails['width'];
					$height = floatval($containerDetails['height']) / $max_per_package;
					$ship_modifier_text = $ship_modifier_text.' [Ship maximum per box='.$max_per_package.']';
				}					
			}
			for ($q = 0; $q < $product->quantity; $q++) {
				$this->logInfo('  Box: '.$product->title. ' l:'.$length. ' w:'.$width. ' h:'.$height. ' wt:'.$weight. 'LB value:'.$price.''.$ship_modifier_text);
				$this->boxes[] = array(
					'id' => $product->id,
					'name' => $product->title,
					'length' => $length,
					'width' => $width,
					'height' => $height,
					'weight' => $weight,
					'value' => $price,
				);
			}
		}
		return $this->boxes;
	}

	/* This method determines the weight with the added padding.
	* @param                $method
	* @return float
	*/
	function _getBoxPaddedAmount() {
		if($this->usps_padding == ''){ //No padding
			$padding = 1;
		}else{
			$padding = str_replace('%', '', $this->usps_padding) / 100;
		}
		return $padding;
	}


		/* This method returns the USPS Service code for the method.
	* @param                method service name
	* @return array
	*/
	function _getServiceCode($service_name){
		$service_code = 'All'; //default
		if($this->_isDomesticShippingMethod($service_name)){ //if domestic service check rate type
			if($this->usps_rate_type == "ONLINE"){
				$service_code = 'ONLINE';
			}elseif($this->usps_rate_type == "PLUS"){
				$service_code = 'PLUS';
			}
		}
		//If Smart Flat Rate is set then use specific service
		if($this->usps_smart_flatrate){
			switch($service_name){
			//Priority Mail Flat Rate
			case "Priority Mail Small Flat Rate Box": 
			case "Priority Mail Medium Flat Rate Box": 
			case "Priority Mail Large Flat Rate Box":
			case "Priority Mail Flat Rate Envelope":
			case "Priority Mail Padded Flat Rate Envelope":
			case "Priority Mail Small Flat Rate Envelope":
			case "Priority Mail Window Flat Rate Envelope":
			case "Priority Mail Gift Card Flat Rate Envelope":
			case "Priority Mail Legal Flat Rate Envelope":
				$service_code = 'PRIORITY';
				if($this->usps_rate_type == "ONLINE"){
					$service_code = 'PRIORITY COMMERCIAL';
				}elseif($this->usps_rate_type == "PLUS"){
					$service_code = 'PRIORITY CPP';
				}
				break;
			//Priority Mail Express Flat Rate
			case "Priority Mail Express Flat Rate Envelope": 
			case "Priority Mail Express Padded Flat Rate Envelope": 
			case "Priority Mail Express Legal Flat Rate Envelope": 
				$service_code = 'PRIORITY EXPRESS';
				if($this->usps_rate_type == "ONLINE"){
					$service_code = 'PRIORITY EXPRESS COMMERCIAL';
				}elseif($this->usps_rate_type == "PLUS"){
					$service_code = 'PRIORITY EXPRESS CPP';
				}
				break;
			//International FLATRATE 'MailType'
			case "Priority Mail International Small Flat Rate Box":
			case "Priority Mail International Medium Flat Rate Box":
			case "Priority Mail International Large Flat Rate Box": 
			case "Priority Mail Express International Flat Rate Envelope": 
			case "Priority Mail Express International Padded Flat Rate Envelope": 
			case "Priority Mail Express International Legal Flat Rate Envelope": 
				$service_code = 'FLATRATE';
				break;
			}			
		}
		return $service_code;
	}	


	
	function _getContainer($containerDetails) {
		// Define our container. 
		$this->logInfo(' Get Container');
		//Subtract out shipping material weight from total per box
		$maxWeightWithPadding = self::convertWeightUnit($containerDetails['max_weight'], $this->shipment_weightunit, 'LB');
		$maxWeightWithPadding = $maxWeightWithPadding - ($maxWeightWithPadding * $this->_getBoxPaddedAmount());
		//Dimension support: ensure all boxes fit container size.
		$container = array('length' => $containerDetails['length'],'width' => $containerDetails['width'],'height' => $containerDetails['height'], 'max_weight' => $maxWeightWithPadding); 
		
		$this->logInfo('  Container: l:'.$container['length']. ' w:'.$container['width']. ' h:'.$container['height']. ' max_weight:'.$container['max_weight'].'LB');
		
		return $container;
	}



	/**
	 *
	 */
	function getLogFileName() {
		$name=$this->_idName;
		$methodId=0;
		if (isset ($this->_currentMethod) ) {
			$methodId=$this->_currentMethod->$name;
		}

		return $this->_name. '.'.$methodId ;
	}

	/**
	 * log all messages of type ERROR
	 * log in case the debug option is on, and the log option is on
	* @param string $message the message to write
	* @param string $title
	* @param string $type message, deb-ug,  info, error
	* @param boolean $doDebug in payment notification, we don't want to use vmdebug even if the debug option  is on
	 *
	 */
	public function debugLog($message, $title='', $type = 'message', $doDebug=true) {

		if ( isset($this->_currentMethod) and !$this->_currentMethod->log and $type !='error') {
			//Do not log message messages if we are not in LOG mode
			return;
		}

		if ( $type == 'error') {
			$this->sendEmailToVendorAndAdmins();
		}

		$this->logInfo($title.': '.print_r($message,true), $type, true);
	}



		/**
	 * Convert Weight Unit
	 *
	 * @author Valérie Isaksen
	 */
	static function convertWeightUnit ($value, $from, $to) {

		$from = strtoupper($from);
		if($from == 'LBS'){ $from = 'LB';}
		$to = strtoupper($to);
		$value = str_replace (',', '.', $value);
		if ($from === $to) {
			return $value;
		}

		$g = (float)$value;

		switch ($from) {
			case 'KG':
				$g = (float)(1000 * $value);
			break;
			case 'MG':
				$g = (float)($value / 1000);
			break;
			case 'LB':
				$g = (float)(453.59237 * $value);
			break;
			case 'OZ':
				$g = (float)(28.3495 * $value);
			break;
		}
		switch ($to) {
			case 'KG' :
				$value = (float)($g / 1000);
				break;
			case 'G' :
				$value = $g;
				break;
			case 'MG' :
				$value = (float)(1000 * $g);
				break;
			case 'LB' :
				$value = (float)($g / 453.59237);
				break;
			case 'OZ' :
				$value = (float)($g / 28.3495);
				break;
		}
		return $value;
	}

	/**
	 * Convert Metric Unit
	 *
	 * @author Florian Voutzinos
	 */
	static function convertDimensionUnit ($value, $from, $to) {

		$from = strtoupper($from);
		$to = strtoupper($to);
		$value = (float)str_replace (',', '.', $value);
		if ($from === $to) {
			return $value;
		}
		$meter = (float)$value;

		// transform $value in meters
		switch ($from) {
			case 'CM':
				$meter = (float)(0.01 * $value);
				break;
			case 'MM':
				$meter = (float)(0.001 * $value);
				break;
			case 'YD' :
				$meter =(float) (0.9144 * $value);
				break;
			case 'FT' :
				$meter = (float)(0.3048 * $value);
				break;
			case 'IN' :
				$meter = (float)(0.0254 * $value);
				break;
		}
		switch ($to) {
			case 'M' :
				$value = $meter;
				break;
			case 'CM':
				$value = (float)($meter / 0.01);
				break;
			case 'MM':
				$value = (float)($meter / 0.001);
				break;
			case 'YD' :
				$value =(float) ($meter / 0.9144);
				break;
			case 'FT' :
				$value = (float)($meter / 0.3048);
				break;
			case 'IN' :
				$value = (float)($meter / 0.0254);
				break;
		}
		return $value;
	}


	/**
	* This method determines the handing costs for the shipping method.
	* @param                $method
	* @param                $methodSalesPrice
	* @return currency
	*/
	function _getHandlingCost($method, $methodSalesPrice, $cartvalue) {
		//$this->logInfo('getHandlingCost '.$method->USPS_HANDLINGFEE_TYPE.'= '.$method->USPS_HANDLINGFEE);
		if($method->USPS_HANDLINGFEE_TYPE == 'NONE'){
			//No handling fee applied
			return 0;
		}else{
			if (preg_match('/%$/',$method->USPS_HANDLINGFEE)) {
				if($method->USPS_HANDLINGFEE_TYPE == 'PCTCART'){
					//Add percentage based on cart total
					return $cartvalue * (substr($method->USPS_HANDLINGFEE,0,-1)/100);
				}else{ //Add percentage based on shipping method total
					return $methodSalesPrice * (substr($method->USPS_HANDLINGFEE,0,-1)/100);
				}
			} else {
				return $method->USPS_HANDLINGFEE;
			}
		}
	}

}
?>