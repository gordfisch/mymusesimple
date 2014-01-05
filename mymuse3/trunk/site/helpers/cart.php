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
		$session = &JFactory::getSession();
		if (!$session->get("cart",0)) {
			$this->cart = array();
			$this->cart["idx"] = 0;
		}else{
			$this->cart = $session->get("cart");
		}
	}
	
	/**
	 * cart
	 *
	 * array
	 */
	var $cart = null;
	
	
    /**
    * var error
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
    $dispatcher	=& JDispatcher::getInstance();
	JPluginHelper::importPlugin('system');
	$results = $dispatcher->trigger('onBeforeAddToCart', array (&$_POST, &$this->cart ));

	$mainframe 	=& JFactory::getApplication();
    $params 	=& MyMuseHelper::getParams();

    $catid = JRequest::getVar('catid',  0, '', 'int');
    $parentid = JRequest::getVar('parentid',  0, '', 'int');
    $productid = JRequest::getVar('productid',  0, '', 'array');
    $quantity = JRequest::getVar('quantity',  0, '', 'array');
    $item_quantity = JRequest::getVar('item_quantity',  0, '', 'array');
   // $Itemid = JRequest::getVar('Itemid',  0, '', 'int');

    $db	= & JFactory::getDBO();   

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

    // FOR EACH PRODUCT  IN ARRAY

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
                        $quant = 0;
                    }else{
                        $this->error = JText::_('MYMUSE_EXCEEDS_AVAILABLE_STOCK')." ";
                        $this->error .= JText::_('MYMUSE_AVAILABLE_STOCK')." ".$res->product_in_stock;
                        return False;
                    }
     			}
     		}
        }

        $updated = 0;
        // Check for duplicate and do not add to current quantity
        for ($i=0;$i<$this->cart["idx"];$i++) {
              if (@$this->cart[$i]["product_id"] == $product_id) {
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
		$mainframe 	=& JFactory::getApplication();
    	$params 	=& MyMuseHelper::getParams();
    
    	$catid = JRequest::getVar('catid',  0, '', 'int');
    	$parentid = JRequest::getVar('parentid',  0, '', 'int');
    	$productid = JRequest::getVar('productid',  0, '', 'array');
    	$quantity = JRequest::getVar('quantity',  0, '', 'array');
    	$Itemid = JRequest::getVar('Itemid',  0, '', 'int');

 
    	$db  = & JFactory::getDBO();;
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
            $quant = $quantity[$val];
    
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
                        
                        //echo "pid = $product_id: <br />product_in_stock = $product_in_stock<br />quantity = $quant";
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

        return True;
  	}
  
    /**
     * delete remove an item from the cart
     * 
     * @return bool
     */    
  	function delete($product_id) {

  		$temp = array();

  		$j = 0;
  		for ($i=0;$i<$this->cart["idx"];$i++) {

  			if ($this->cart[$i]['product_id'] != $product_id) {
  				$temp[$j++] = $this->cart[$i];
  			}
  		}
  		$temp["idx"] = $j;
  		$this->cart = $temp;
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
  			if(@$this->cart[$i]["coupon_id"]){ continue;}
  			if($this->cart[$i]['product_physical']){
  				$shipping_needed = true;
  			}
  		}
    	return $shipping_needed;
  	}

  	
  	function couponadd() {
  		$db			= & JFactory::getDBO();
  		$user 		= & JFactory::getUser();
  		$user_id 	= $user->get('id');
  		
  		$coupon_value = JRequest::getVar('coupon', '');
  		$query = "SELECT * FROM #__mymuse_coupon WHERE code='$coupon_value'
  		AND state='1'";
  		$db->setQuery($query);
  		$coupon = $db->loadObject();
  		if(!isset($coupon->id)){
  			$this->error = JText::_("MYMUSE_COUPON_COULD_NOT_FIND");
  			return false;
  		}

  		//see if it has maxed out uses
  		if($coupon->coupon_max_uses > 0 && $coupon->uses >= $coupon->coupon_max_uses){
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
  		
  		$date =& JFactory::getDate();
  		$now = $date->format();
  		
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
  		if($coupon->type == 1 && $coupon->product_id > 0){
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

  		return true;
  	}
  	
  	
  	function buildOrder($edit=true )
  	{
		$mainframe 	=& JFactory::getApplication();
    	$params 	=& MyMuseHelper::getParams();
		
		$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		=& $MyMuseShopper->getShopper(); 
		$MyMuseStore  	=& MyMuse::getObject('store','models');
		$store 			= $MyMuseStore->getStore(); 
		$MyMuseProduct 	=& MyMuse::getObject('product','models');
		$user 			= &JFactory::getUser();
		

		$Itemid		= JRequest::getVar('Itemid', '');
		$db	= & JFactory::getDBO();
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

			$order->items[$i] = $this->getProduct($this->cart[$i]['product_id']);
	
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
			if (isset($order->items[$i]->parentid) && $order->items[$i]->parentid > 0) {

				if($order->items[$i]->parent->reservation_fee > 0){
					$order->reservation_fees[$order->items[$i]->parent->id] = $order->items[$i]->parent->reservation_fee;
					$order->items[$i]->not_in_total = 1;
					$order->must_pay_now += $order->items[$i]->parent->reservation_fee;
				}
				
			}
			

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
				$order->discount += $price["product_shopper_group_discount_amount"] * $order->items[$i]->quantity;
			}
			
			//TEST OF DISCOUNT
			//if($order->items[$i]->quantity >= 5){
			//	$order->discount += 2.00 * $order->items[$i]->quantity;
			//}
			
			
			// add to order sub_total
			$order->order_subtotal += $order->items[$i]->product_item_subtotal ;
			if(!$order->items[$i]->not_in_total){
				$order->non_res_total += $order->items[$i]->product_item_subtotal;
			}


			
			$order->items[$i]->delete_url .= "index.php?option=com_mymuse";
			$order->items[$i]->delete_url .= "&task=cartdelete";
			$order->items[$i]->delete_url .= "&product_id=".$order->items[$i]->id;
			$order->items[$i]->delete_url .= "&Itemid=$Itemid";

			

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

		
		
		} //end of cart items
		
		// TEST OF DISCOUNT
		//$order->order_subtotal = $order->order_subtotal - $order->discount; 
		
		//RESERVATION FEES
		if(count($order->reservation_fees)){
			foreach($order->reservation_fees as $fee){
				$order->reservation_fee += $fee;
			}
		}

		//COUPONS
		if($params->get('my_use_coupons') && @$coupon_id){
			$order->coupon->discount = 0;
			$query = "SELECT * from #__mymuse_coupon where id='".$coupon_id."'";
			$db->setQuery($query);
			if($order->coupon = $db->loadObject()){
				//this function sets the $order->coupon->discount
				MyMuseHelper::getCouponDiscount($order);
				$order->order_subtotal = $order->order_subtotal - $order->coupon->discount;
				if(!count($order->reservation_fees)){
					$order->must_pay_now = $order->order_subtotal;
				}
			}
		}
		
		//SHOPPER GROUP DISCOUNTS
		$order->shopper_group_name = $shopper->shopper_group_name;
		if(isset($shopper->discount) && $shopper->discount > 0){
			$order->shopper_group_discount = $shopper->discount;
		}

		// SHIPPING
		if ($params->get('my_use_shipping') && isset($this->cart['shipping'])) {
				$order->order_shipping = $this->cart['shipping'];
		}

		//TAXES
		if ($params->get('my_tax_shipping') && isset($order->order_shipping->cost)) {
			$order_taxable = $order->order_subtotal + $order->order_shipping->cost;
		}else{
			$order_taxable = $order->order_subtotal;
		}
		$order_tax = $MyMuseCheckout->calc_order_tax($order_taxable);

		$order->tax_array = array();
		while(list($key,$val) = each($order_tax)){
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

		if(!$edit){
			$order->do_html = 0;
		}else{
			$order->do_html = 1;
		}

		$order->colspan=3;
		$order->colspan2 = 1;
		if(@$order->do_html){
			$order->colspan2 = 1;
		}

		$dispatcher	=& JDispatcher::getInstance();
		$result = $dispatcher->trigger('onAfterBuildOrder', array(&$order, &$this->cart));
		if(count($result)){
			$order = $result[0];
		}

		return $order;
	}
	
	

	
	/**
	 * getProduct
	 * 
	 * @param int $id The product id
	 * @return object The product object
	 */
	function getProduct($id=null)
	{
		$mainframe 	=& JFactory::getApplication();
    	$params 	=& MyMuseHelper::getParams();;
    	
		$MyMuseShopper  =& MyMuse::getObject('shopper','models');
		$shopper 		=& $MyMuseShopper->getShopper();
		
		$MyMuseProduct 	=& MyMuse::getObject('product','models');
		
		if(!$id){
			$this->error = JText::_("MYMUSE_NO_PRODUCT_ID");
			return false;
		}
	
		$db	= & JFactory::getDBO();
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'product.php');
		
		$row = new MymuseTableproduct($db);

		if(!$row->load($id)){
			echo "Error: ".$db->ErrorMsg(); exit;
			return false;
		}

		// take out the file_contents
		$row->file_contents = null;
		
		if(isset($shopper->shopper_group_id)){
			$shopper_group_id = $shopper->shopper_group_id;
		}else{
			$shopper_group_id = $params->get("my_default_shopper_group_id");
		}

		// get parent object
		if($row->parentid){
			$parent = new MymuseTableproduct($db);
			$parent->load($row->parentid);
			$row->parent = $parent;
			$artistid = $row->parent->catid;
			// Get attributes

			$attributes = $MyMuseProduct->getAttributes( $row->id, $row->parentid);
			if($attributes){
				foreach ($attributes as $attribute){
					$row->title .= " (" . $attribute->attribute_value . ") ";
				}
			}

		}else{
			$artistid = $row->catid;
		}
		$row->price = MyMuseModelProduct::getPrice($row);
		
		
		// get the artist object
		require_once( 'administrator'.DS.'components'.DS.'com_categories'.DS.'tables'.DS.'category.php');
		$cat = new CategoriesTableCategory($db);
		$cat->load($artistid);
		$row->artist = $cat;
		$row->category_name = $row->artist->title;
		
		return $row;
	}
}

?>