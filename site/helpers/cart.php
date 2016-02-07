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
defined('_JEXEC') or die('Restricted access');

class MyMuseCart {

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		// Register the cart
		$session = JFactory::getSession();
		if (!$session->get("cart",0)) {
			$this->cart = array();
			$this->cart["idx"] = 0;
		}else{
			$this->cart = $session->get("cart");
		}
	}
	
	/**
	 * @var cart
	 *
	 * array
	 */
	var $cart = null;
	
	/**
	 * order object
	 *
	 * @var    order
	 */
	var $order = null;
	
	
    /**
    * @var error
    */
 	var $error = null;
 	
 	
    function getError(){
    	return $this->error;
    }
 	
    /**
     * addToCart
     * 
     * @param array $cart The session cart
     * @param array $catid The category ids of the product
     * @param array $productid The product ids
     * @param array $quantity The quantities of the products
     * @param int $parentid Possible parentid
     * @return bool
     */
  
  function addToCart() {

    // Process the cart preparation plugins
    $dispatcher	= JDispatcher::getInstance();
	JPluginHelper::importPlugin('system');
	$results = $dispatcher->trigger('onBeforeAddToCart', array (&$_POST, &$this->cart ));

	$app 			= JFactory::getApplication();
	$jinput 		= $app->input;
    $params 		= MyMuseHelper::getParams();

    $catid 			= $jinput->get('catid',  0, 'INT');
    $parentid 		= $jinput->get('parentid',  0, 'INT');
    $productid 		= $jinput->get('productid',array(), 'ARRAY');
    $quantity 		= $jinput->get('quantity',array(), 'ARRAY');
    $variation 		= $jinput->get('variation',array(), 'ARRAY');
    $item_quantity 	= $jinput->get('item_quantity',array(), 'ARRAY');
    
   // $Itemid = $jinput->get('Itemid',  0,'INT');

    $db	= JFactory::getDBO();   

    if(!@$productid){
        $this->error = JText::_("MYMUSE_PLEASE_SELECT_PRODUCT");
        return false;
    }

    if (!is_array($productid)) {
        $prod_id = $productid;
        $productid = array();
        $productid["0"] = $prod_id;
    }
    
    if(!$quantity){
        foreach($productid as $pid){
        	$quantity[$pid] = 1;
        }
    }
    
    if (!is_array($quantity)) {
        $quant = $quantity;
        $quantity = array();
        $quantity[$productid["0"]] = $quant;
    }

    if ($catid && !is_array($catid)) {
    	$c = $catid;
    	$catid = array();
    	foreach($productid as $pid){
        	$catid[$pid] = $c;
        }
    }
    reset($productid);

    // FOR EACH PRODUCT IN ARRAY

    while(list($key,$val)=each($productid)) {
    	if(!$val){ continue; }
     	$product_id = $val;
     	
     	if(!isset($quantity[$val])){
         	$quantity[$val] =1;
     	}
     	
     	$quant = $quantity[$val];
     	$category_id = $catid[$val];

     	// Check for negative quantity
     	if ($quant < 0) {
          	$this->error = JText::_('MYMUSE_NEGATIVE_QUANTITY');
          	return False;
     	}

     	if (!is_numeric($quant)) {
            $this->error = JText::_('MYMUSE_INVALID_QUANTITY');
            return False; 
     	}

		$q = "SELECT product_in_stock, product_physical ";
     	$q .= "FROM #__mymuse_product ";
     	$q .= "WHERE id= $product_id ";

     	$db->setQuery($q);
     	$res = $db->loadObject();
     	$product_physical = $res->product_physical;
     	// Check to see if checking stock quantity
     	if ($params->get('my_check_stock',0)) {
     		
     		if($res->product_physical){
     			if ($quant > $res->product_in_stock) {
                    if($params->get('my_add_stock_zero',0)) {
                        $quantity[$val]  = $quant = 0;
                        
                    }else{
                        $this->error = JText::_('MYMUSE_EXCEEDS_AVAILABLE_STOCK')." ";
                        $this->error .= JText::_('MYMUSE_AVAILABLE_STOCK')." ".$res->product_in_stock;
                        return False;
                    }
     			}
     		}
        }

        $updated = 0;
        // Check for duplicate and add to current quantity
        for ($i=0;$i<$this->cart["idx"];$i++) {
              if (@$this->cart[$i]["product_id"] == $product_id && 
              		@$variation[$product_id] == @$this->cart[$i]["variation"]) {
                    $updated = 1;
                    $this->cart[$i]["quantity"] += $quantity[$val];
              }
        }
    
        // If we did not update then add the item
        if (!$updated) {
            $this->cart[$this->cart["idx"]]["quantity"] = $quant;
            $this->cart[$this->cart["idx"]]["product_id"] = $product_id;
            $this->cart[$this->cart["idx"]]["catid"] = $category_id;
            $this->cart[$this->cart["idx"]]["product_physical"] = $product_physical;
            $this->cart[$this->cart["idx"]]["variation"] = (isset($variation[$product_id]))? $variation[$product_id] : '';
            
            $this->cart["idx"]++;
        }
    } // end of while loop
    
    //move coupons to the end
    $j = 0;
    $coupon_id = 0;
    $fixed = array();
    $fixed['idx'] = 0;
    for ($i=0;$i<$this->cart["idx"];$i++) {
            if(isset($this->cart[$i]["coupon_id"])){
                    $coupon_id = $this->cart[$i]["coupon_id"];
                    unset($this->cart[$i]["coupon_id"]);
                    continue;
            }
            $fixed[$j]["quantity"] = $this->cart[$i]["quantity"];
            $fixed[$j]["product_id"] = $this->cart[$i]["product_id"];
            $fixed[$j]["catid"] = $this->cart[$i]["catid"];
            $fixed[$j]["product_physical"] = @$this->cart[$i]["product_physical"];
            $fixed[$j]["variation"] = $this->cart[$i]["variation"];
            $j++;
            $fixed['idx']++;
    }
    if($coupon_id){
        $fixed[$j]["coupon_id"] = $coupon_id;
        $fixed['idx']++;
    }
    if(isset($this->cart['extra'])){
    	$fixed['extra'] = $this->cart['extra'];
    }
    $this->cart = $fixed;
    $this->buildOrder(1,1);
    return true; 
  }


    /**
     * updateCart
     * 
     * @param array $cart The session cart
     * @param array $productid The product ids
     * @param array $quantity The quantities of the products
     * @return bool
     */ 
  	function updateCart( ) 
  	{
		$app 		= JFactory::getApplication();
		$jinput 	= $app->input;
    	$params 	= MyMuseHelper::getParams();
    
    	$catid 		= $jinput->get('catid',  0, 'INT');
    	$parentid 	= $jinput->get('parentid',  0, 'INT');
    	$productid 	= $jinput->get('productid',array(), 'ARRAY');
    	$quantity 	= $jinput->get('quantity',array(), 'ARRAY');
    	$variation 	= $jinput->get('variation',array(), 'ARRAY');
    	$Itemid 	= $jinput->get('Itemid',  0, 'INT');

 
    	$db  = JFactory::getDBO();;
        if(!@$productid){
            $this->error = JText::_('MYMUSE_CANT_UPDATE_CART');
            return false;
        }
    
        if (!is_array(@$productid)) {
            $prod_id = $productid;
            $productid = array();
            $productid["0"] = $prod_id;
    
        }
    
        if (!is_array(@$quantity)) {
            $quant = $quantity;
            $quantity = array();
            $quantity[$productid["0"]] = $quant;
    
        }

        reset($productid);
    
        // FOR EACH PRODUCT  IN ARRAY
    
        while(list($key,$val)=each($productid)) {
            $product_id = $val;
            $quant = (isset($quantity[$val]))? $quantity[$val] : 1;
    
            // Check for negative quantity
            if ($quant < 0) {
                $this->error = JText::_('MYMUSE_NEGATIVE_QUANTITY');
                return False;
            }
    
            if (!preg_match("/^[0-9]*$/", $quant)) {
                $this->error = JText::_('MYMUSE_INVALID QUANTITY');
                return False;
            }
    
            // Check to see if checking stock quantity
            if ($params->get('my_check_stock',0)) {
            	$q = "SELECT product_in_stock ";
            	$q .= "FROM #__mymuse_product ";
            	$q .= "WHERE id= $product_id ";
            	$db->setQuery($q);

            	$product_in_stock = $db->loadResult();
            	if ($quant > $product_in_stock) {
                    if($params->get('my_add_stock_zero',0)) {
                        $quant = 0;
                        
                       // echo "pid = $product_id: <br />product_in_stock = $product_in_stock<br />quantity = $quant";
                    }else{
                        $this->error = JText::_('MYMUSE_EXCEEDS_AVAILABLE_STOCK');
                        $this->error .= JText::_('MYMUSE_AVAILABLE_STOCK')." ".$product_in_stock;
                        return False;
                    }
            	}
            }
    
            if ($quant == 0) {
            	if(!$params->get('my_add_stock_zero',1)) {
                    $this->delete($product_id);
                }

            }else {
    
                for ($i=0;$i<$this->cart["idx"];$i++) {
                    if (isset($this->cart[$i]["product_id"]) && $this->cart[$i]["product_id"] == $product_id) {
                        $this->cart[$i]["quantity"] = $quant;
                        $this->cart[$i]["variation"] = isset($variation[$product_id])? $variation[$product_id] : 0;
                    }
                }
            }
        }
        //move coupons to the end
        $j = 0;
        $coupon_id = 0;
        $fixed = array();
        $fixed['idx'] = 0;
        for ($i=0;$i<$this->cart["idx"];$i++) {
        	if(isset($this->cart[$i]["coupon_id"])){
        		$coupon_id = $this->cart[$i]["coupon_id"];
        		unset($this->cart[$i]["coupon_id"]);
        		continue;
        	}

        	$fixed[$j]["quantity"] = $this->cart[$i]["quantity"];
        	$fixed[$j]["product_id"] = $this->cart[$i]["product_id"];
        	$fixed[$j]["catid"] = $this->cart[$i]["catid"];
        	$fixed[$j]["product_physical"] = $this->cart[$i]["product_physical"];
        	$fixed[$j]["variation"] = $this->cart[$i]["variation"];
        	$j++;
        	$fixed['idx']++;
        }
        if($coupon_id){
        	$fixed[$j]["coupon_id"] = $coupon_id;
        	$fixed['idx']++;
        }
        if(isset($this->cart['extra'])){
        	$fixed['extra'] = $this->cart['extra'];
        }
        $this->cart = $fixed;
        $this->buildOrder(1,1);
        return True;
  	}
  
    /**
     * delete remove an item from the cart
     * 
     * @return bool
     */    
  	function delete($product_id) {
  		$jinput = JFactory::getApplication()->input;
  		$variationid  = $jinput->get('variationid', '', 'int');
  		$temp = array();

  		$j = 0;
  		for ($i=0;$i<$this->cart["idx"];$i++) {

  			if (isset($this->cart[$i]['product_id']) && $this->cart[$i]['product_id'] != $product_id){
  				if(isset($this->cart[$i]['variationid']) && $this->cart[$i]['variationid'] != $variationid){
  					$temp[$j++] = $this->cart[$i];
  				}elseif(!isset($this->cart[$i]['variationid']) || !$variationid){
  					$temp[$j++] = $this->cart[$i];
  				}
  			}
  		}
  		$temp["idx"] = $j;
  		$this->cart = $temp;
  		$this->buildOrder(1,1);
  		return True;
  	}


    /**
     * reset
     * clear the cart
     *
     * @return bool
     */   
  	function reset() { 

    	$this->cart = array();
    	$this->cart["idx"]=0;
    	return True;
  	}
  	
  	
    /**
     * shipping_needed
     * chick if shipping_ is needed
     *
     * @return bool
     */   
  	function shipping_needed() { 

  		$shipping_needed = false;
  		for ($i=0;$i<$this->cart["idx"];$i++) {
  			if(!isset($this->cart[$i]["product_id"]) ){ continue;}
  			if($this->cart[$i]['product_physical']){
  				$shipping_needed = true;
  			}
  		}
    	return $shipping_needed;
  	}

  	
  	function couponadd() {
  		$db			=  JFactory::getDBO();
  		$user 		=  JFactory::getUser();
  		$user_id 	= $user->get('id');
  		$app 		= JFactory::getApplication();
  		$jinput 	= $app->input;
  		
  		$coupon_value = $jinput->get('coupon', '');
  		$query = "SELECT * FROM #__mymuse_coupon WHERE code='$coupon_value'
  		AND state='1'";
  		$db->setQuery($query);
  		$coupon = $db->loadObject();
  		if(!isset($coupon->id)){
  			$this->error = JText::_("MYMUSE_COUPON_COULD_NOT_FIND");
  			return false;
  		}

  		//see if it has maxed out uses
  		if($coupon->coupon_max_uses > 0 && $coupon->coupon_uses >= $coupon->coupon_max_uses){
  			$this->error = JText::_("MYMUSE_COUPON_USE_EXCEEDS_MAX_USE");
  			return false;
  		}
  		
  		//see if it has maxed out uses by user
  		$query = "SELECT count(*) as num FROM #__mymuse_order
  		WHERE user_id='$user_id' AND coupon_id='".$coupon->id."'";
  		$db->setQuery($query);
  		$num = $db->loadResult();
  		if($coupon->coupon_max_uses_per_user > 0 && $num >= $coupon->coupon_max_uses_per_user){
  			$this->error = JText::_("MYMUSE_COUPON_USE_EXCEEDS_MAX_USE_BY_USER");
  			return false;
  		}
  		
  		$date = JFactory::getDate();
  		$now = $date->toSQL();

  		//see if it is expired
  		$query = "SELECT id FROM #__mymuse_coupon WHERE code='$coupon_value'
  		AND expiration_date >= '$now' OR expiration_date ='0000-00-00 00:00:00'";
  		$db->setQuery($query);
  		if(!$db->loadResult()){
  			$this->error = JText::_("MYMUSE_COUPON_EXPIRED");
  			return false;
  		}
  		
  		//see if it is started
  		$query = "SELECT id FROM #__mymuse_coupon WHERE code='$coupon_value'
  		AND start_date <= '$now' OR start_date ='0000-00-00 00:00:00'";
  		$db->setQuery($query);
  		if(!$db->loadResult()){
  			$this->error = JText::_("MYMUSE_COUPON_NOT_YET_VALID");
  			return false;
  		}
  		
  		
  		
  		// See if it is for a product in the cart
  		if($coupon->coupon_type == 1 && $coupon->product_id > 0){
  			$good = 0;
  			for ($i=0;$i<$this->cart["idx"];$i++) {
                if ($this->cart[$i]["product_id"] == $coupon->product_id) {
                    $good = 1;
                }
            }
            if(!$good){
            	$this->error = JText::_("MYMUSE_COUPON_NO_MATCHING_PRODUCT");
            	return false;
            }
  		}
  		
  		// put it in the cart 
  		$this->cart[$this->cart["idx"]]["coupon_id"] = $coupon->id;
        $this->cart["idx"]++;
		$this->buildOrder(1,1);
  		return true;
  	}
  	
  	
  	/**
  	 * Get a order object.
  	 *
  	 * Returns the global order object, only creating it if it doesn't already exist.
  	 *
  	 * @return  order object
  	 *
  	 */
  	public  function buildOrder($edit = true, $new = false)
  	{
  		if (!$this->order || $new)
  		{
  			$this->order = $this->_buildOrder($edit);
  		}
  	
  		return $this->order;
  	}
  	
  	protected function _buildOrder($edit =  true )
  	{

		$app 		= JFactory::getApplication();
  		$jinput 	= $app->input;
    	$params 	= MyMuseHelper::getParams();
		
		$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		= $MyMuseShopper->getShopper(); 
		$MyMuseStore  	=& MyMuse::getObject('store','models');
		$store 			= $MyMuseStore->getStore(); 
		$MyMuseProduct 	=& MyMuse::getObject('product','models');
		$user 			= JFactory::getUser();
		$preview_tracks = array();
		$dispatcher		= JDispatcher::getInstance();

		$Itemid			= $jinput->get('Itemid', '');
		$db				= JFactory::getDBO();
		
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'product.php');

		// just check that there is an order_item
		if ($this->cart["idx"] == 0) {
			$this->error = JText::_("MYMUSE_YOUR_CART_IS_EMPTY");
 			return false;
		}
		
	    // FOR THE ORDER
		$order = new stdClass();
		$order->order_subtotal    		= 0.00;
		$order->must_pay_now    		= 0.00;
		$order->tax_total     			= 0.00;
		$order->reservation_fee 		= 0.00;
		$order->reservation_fees 		= array();
		$order->non_res_total 			= 0.00;
		$order->order_shipping 			= '';
		$order->need_shipping 			= 0;
		$order->order_total 			= 0.00;
		$order->discount	 			= 0.00;
		$order->shopper_group_discount  = 0.00;
		

		$order->idx 			= $this->cart["idx"];
		$order->update_form 	= '
            <!-- update form -->' .
//         ' <form action="'.JURI::base().'" method="post" name="update">'
          '<input type="hidden" name="option" value="com_mymuse"/>
          <input type="hidden" name="task" value="updatecart"/>
          <input type="hidden" name="Itemid" value="'.$Itemid.'"/>
          <input type="hidden" name="ship_to_info_id" value="'.@$ship_to_info_id.'" />
          ';
		
		// FOR EACH CART ITEM
		for ($i=0;$i<$this->cart["idx"];$i++) {
			if(isset($this->cart[$i]["coupon_id"])){
				$coupon_id = $this->cart[$i]["coupon_id"];
				continue;
			}
			if(isset($this->cart[$i]["discount"])){
				$order->discount = $this->cart[$i]["discount"];
				continue;
			}
			if(!$this->cart[$i]["variation"]){
				$this->cart[$i]["variation"] = 0;
			}

			$order->items[$i] = $this->getProduct($this->cart[$i]['product_id']);
			if($order->items[$i]->product_downloadable){
				$preview_tracks[$i] = $order->items[$i];
			}
			$ext = '';
			$jason = json_decode($order->items[$i]->file_name);
			if(is_array($jason)){
				
				$order->items[$i]->variation_select = '<select name="variation['.$this->cart[$i]['product_id'].']"
					id = "variationid_'.$this->cart[$i]['product_id'].'" class="inputbox myformatselect cart ">';
										
				//if multiple variations, create select box
				for($j=0; $j < count($jason); $j++){
					$order->items[$i]->variation_select .= '<option value="'.$j.'" ';
					if($j == $this->cart[$i]["variation"]){
						$order->items[$i]->variation_select .= 'SELECTED=SELECTED';
					}	
					$order->items[$i]->variation_select .= '>'.$jason[$j]->file_ext.'</option>'."\n";
				}
				$order->items[$i]->variation_select  .= "</select>";
				
				$order->items[$i]->file_name = $jason[$this->cart[$i]["variation"]]->file_name;
				$order->items[$i]->ext = $jason[$this->cart[$i]["variation"]]->file_ext;
			}else{
				$order->items[$i]->ext = pathinfo($order->items[$i]->file_name, PATHINFO_EXTENSION);
			}
			
			//other cats
			$othercats = array();
			$query = "SELECT c.title FROM #__mymuse_product_category_xref as x
					LEFT JOIN #__categories as c ON c.id=x.catid
				WHERE product_id = '".$this->cart[$i]['product_id']."' AND catid != ".$order->items[$i]->catid."
						AND catid !=".$order->items[$i]->artistid;
			$db->setQuery($query);
			$res = $db->loadObjectList();
			if(count($res)){
				foreach($res as $r){
					$othercats[] = $r->title;
				}
			}
			$othercats = array_unique($othercats);
			$order->items[$i]->othercats = implode(", ",$othercats);
			
			//echo "order->items[$i]->file_name = m".$order->items[$i]->file_name."<br />";
			//echo "order->items[$i]->ext =".$order->items[$i]->ext."<br />";
			$order->items[$i]->product_id = $order->items[$i]->id;
			$order->items[$i]->order_item_total = 0.00;
			$order->items[$i]->not_in_total = 0;
			
			if($order->items[$i]->product_physical){
				$order->need_shipping = 1;
			}
			
			if($order->items[$i]->title == ""){
				$order->items[$i]->title = $item->parent->title;
				
			}

			// Get any reservation fee
			//if (isset($order->items[$i]->parentid) && $order->items[$i]->parentid > 0) {

				//if($order->items[$i]->parent->reservation_fee > 0){
					//$order->reservation_fees[$order->items[$i]->parent->id] = $order->items[$i]->parent->reservation_fee;
					//$order->items[$i]->not_in_total = 1;
					//$order->must_pay_now += $order->items[$i]->parent->reservation_fee;
				//}
				
			//}
			

			$order->items[$i]->quantity = $this->cart[$i]['quantity'];

			$order->update_form .= '
            <input type="hidden" name="productid['.$i.']" value="'.$order->items[$i]->id.'"/>
            ';

			// GET PRICES
			$price = MyMuseModelProduct::getPrice($order->items[$i]);	
			$order->items[$i]->product_item_price = $price['product_price'];
			$order->items[$i]->product_item_subtotal = $price['product_price'] * $order->items[$i]->quantity;
			
			// shopper group discount
			if($price["product_shopper_group_discount_amount"] > 0){
				$order->shopper_group_discount += $price["product_shopper_group_discount_amount"] * $order->items[$i]->quantity;
			}
			
			
			// add to order sub_total
			$order->order_subtotal += $order->items[$i]->product_item_subtotal ;
			if(!$order->items[$i]->not_in_total){
				$order->non_res_total += $order->items[$i]->product_item_subtotal;
			}

			$order->items[$i]->delete_url = "index.php?option=com_mymuse";
			$order->items[$i]->delete_url .= "&task=cartdelete&view=cart";
			$order->items[$i]->delete_url .= "&product_id=".$order->items[$i]->id;
			$order->items[$i]->delete_url .= "&Itemid=$Itemid";
			$order->items[$i]->delete_url = JRoute::_($order->items[$i]->delete_url);

			// Build URL 
			if ($order->items[$i]->parentid){
				$pid = $order->items[$i]->parentid;
				$aid = $order->items[$i]->parent->catid;
			}else{
				$pid = $order->items[$i]->id;
				$aid = $order->items[$i]->catid;
			}

			$order->items[$i]->url = myMuseHelperRoute::getProductRoute($pid, $aid);
			$order->items[$i]->cat_url = myMuseHelperRoute::getCategoryRoute($aid);
			$order->items[$i]->flash = '';
			
		
		
		} //end of cart items
		$order->subtotal_before_discount = $order->order_subtotal;
	

		//RESERVATION FEES
		if(count($order->reservation_fees)){
			foreach($order->reservation_fees as $fee){
				$order->reservation_fee += $fee;
			}
		}

		//COUPONS
		if($params->get('my_use_coupons') && @$coupon_id){
			
			$query = "SELECT * from #__mymuse_coupon where id='".$coupon_id."'";
			$db->setQuery($query);
			if($order->coupon = $db->loadObject()){
				//this function sets the $order->coupon->discount
				$order->coupon->discount = 0;
				MyMuseHelper::getCouponDiscount($order);
				$order->order_subtotal = $order->order_subtotal - $order->coupon->discount;
				$order->coupon_discount = $order->coupon->discount;
				$order->coupon_id = $coupon_id;
				if(!count($order->reservation_fees)){
					$order->must_pay_now = $order->order_subtotal;
				}
			}
		}
		
		//SHOPPER GROUP DISCOUNTS
		$order->shopper_group_name = isset($shopper->shopper_group_name)? $shopper->shopper_group_name : 'default';

		// SHIPPING
		if ($params->get('my_use_shipping') && isset($this->cart['shipping'])) {
				$order->order_shipping = $this->cart['shipping'];
		}
		
		//DISCOUNTS FROM PLUGINS
		JPluginHelper::importPlugin('mymuse');
		$dispatcher	= JDispatcher::getInstance();
		$result = $dispatcher->trigger('onAfterBuildOrder', array(&$order, &$this->cart));
		if(count($result)){
			foreach($result as $res){
				$order->discount += $res;
			}
		}
		$order->order_subtotal = $order->order_subtotal - $order->discount;
		if($order->order_subtotal < 0){
			$order->order_subtotal = 0.00;
		}

		//TAXES
		$order_tax = $MyMuseCheckout->calc_order_tax($order);

		$order->tax_array = array();
		while(list($key,$val) = each($order_tax)){
			if($val< 0){
				$val = 0.00;
			}
			$val = round($val,2);
			$order->tax_total += $val;
			$order->tax_array[$key] = $val;

		}
		
		$order->update_url = '<a class="links" href="" onClick="';
		$order->update_url .= "javascript:document.update.submit(); return false;";
		$order->update_url .= '">';

		//the big total
		$order->order_total  = $order->order_subtotal + @$order->tax_total + 
		@$order->order_shipping->cost;
		if($order->order_total < 0){
			$order->order_total = 0.00;
		}

		if(!$edit){
			$order->do_html = 0;
		}else{
			$order->do_html = 1;
		}

		$order->colspan=3;
		if($params->get("my_show_cart_preview")){
			$order->colspan=4;
		} 
		$order->colspan2 = 1;
		if(@$order->do_html){
			$order->colspan2 = 1;
		}
		$this->order = $order;
		return $order;
	}
	
	/**
	 * getRecommended
	 */
	function getRecommended()
	{
		$db 		= JFactory::getDBO();
		$params 	= MyMuseHelper::getParams();
		$prods 		= array();
		$recommends = array();
		
		for ($i=0;$i<$this->cart["idx"];$i++) {
			if(isset($this->cart[$i]["coupon_id"])){
				continue;
			}
			$query = "SELECT parentid FROM #__mymuse_product
					WHERE id = '".$this->cart[$i]["product_id"]."'";
			$db->setQuery($query);
			if($p = $db->loadResult()){
				$productid = $p;
			}else{
				$productid = $this->cart[$i]["product_id"];
			}
			$query = "SELECT * FROM #__mymuse_product_recommend_xref 
					WHERE product_id = '".$productid."' 
							ORDER BY RAND()";
			$db->setQuery($query);
			$res = $db->loadObjectList();
			if(count($res)){
				foreach($res as $r){
					$prods[] = $r->recommend_id;
				}
			}
			
		}
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'product.php');
		$prods = array_unique($prods);
		$num = min($params->get('my_max_recommended'),count($prods));

		for($i = 0; $i<$num; $i++){
		
			$query = "SELECT * FROM #__mymuse_product
					WHERE id = '".$prods[$i]."'";
			$db->setQuery($query);
		
			$recommends[$i] = $db->loadObject();
			// Build URL
			if($recommends[$i]->parentid){
				$parent = new MymuseTableproduct($db);
				$parent->load($recommends[$i]->parentid);
				$recommends[$i]->parent = $parent;
				$recommends[$i]->list_image = $recommends[$i]->parent->list_image;
				$recommends[$i]->detail_image = $recommends[$i]->parent->detail_image;
				$pid = $recommends[$i]->parentid;
				$aid = $recommends[$i]->parent->catid;
			} else {
				$pid = $recommends[$i]->id;
				$aid = $recommends[$i]->catid;
			}
		
			$recommends[$i]->url = myMuseHelperRoute::getProductRoute ( $pid, $aid );
			$recommends[$i]->cat_url = myMuseHelperRoute::getCategoryRoute ( $aid );
		}

		return $recommends;
	}

	
	/**
	 * getProduct
	 * 
	 * @param int $id The product id
	 * @return object The product object
	 */
	function getProduct($id=null)
	{
		
		$mainframe 	= JFactory::getApplication();
    	$params 	= MyMuseHelper::getParams();;
    	
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		= $MyMuseShopper->getShopper();
		
		$MyMuseProduct 	=& MyMuse::getObject('product','models');
		
		if(!$id){
			$this->error = JText::_("MYMUSE_NO_PRODUCT_ID");
			return false;
		}
	
		$db	= JFactory::getDBO();
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'product.php');
		
		$row = new MymuseTableproduct($db);

		if(!$row->load($id)){
			echo "Error: id not available";
			$this->delete($id);
			return false;
		}

		// take out the file_contents
		$row->file_contents = null;
		if($name = json_decode($row->file_name) && isset($name[0]->file_length)){
			
			
			
			$row->file_length = $name[0]->file_length;
		}
		
		if(isset($shopper->shopper_group_id)){
			$shopper_group_id = $shopper->shopper_group_id;
		}else{
			$shopper_group_id = $params->get("my_default_shopper_group_id");
		}

		// get parent object
		if($row->parentid){

			$parent = new MymuseTableproduct($db);
			$parent->load($row->parentid);
			// Convert the attribs field to an array.
			$registry = new JRegistry;
			$registry->loadString($parent->attribs);
			$parent->attribs = $registry->toArray();
			
			$registry = new JRegistry;
			$registry->loadString($row->attribs);
			$row->attribs = $registry->toArray();
			
			$parent->attribs = array_merge($parent->attribs, $row->attribs);
			$row->attribs = $parent->attribs;
			
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($parent->metadata);
			$parent->metadata = $registry->toArray();

			$row->parent = $parent;
			$artistid = $row->parent->artistid;
			// Get attributes

			$attributes = $MyMuseProduct->getAttributes( $row->id, $row->parentid);
			if($attributes){
				foreach ($attributes as $attribute){
					$row->title .= " (" . $attribute->attribute_value . ") ";
				}
			}

		}else{
			$row->parent = null;
			$artistid = $row->artistid;
			// Convert the attribs field to an array.
			$registry = new JRegistry;
			$registry->loadString($row->attribs);
			$row->attribs = $registry->toArray();
				
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($row->metadata);
			$row->metadata = $registry->toArray();
		}
		//$row->price = MyMuseModelProduct::getPrice($row);
		
		// get the artist object
		require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_categories'.DS.'tables'.DS.'category.php');
		$cat = new CategoriesTableCategory($db);
		$cat->load($artistid);
		$row->artist = $cat;
		$row->category_name = $row->artist->title;

		return $row;
	}
}
