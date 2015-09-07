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

//To change the order of things, copy this to /templates/YOUR_TEMPLATE/html/com_mymuse/cart/cart.php and edit.
// To edit the parts, copy the part, ex: /templates/YOUR_TEMPLATE/html/com_mymuse/cart/cart_order_summary.php and edit.

if(isset($this->checkout_header)){
	echo $this->checkout_header;
}

if(isset($this->shipping_form)){
	echo $this->shipping_form;
}

if(isset($this->order_summary)){
	echo $this->order_summary;
}

if(isset($this->cart_display)){
	echo $this->cart_display;
}

if(isset($this->shopper_info)){
	echo $this->shopper_info;
}

if(isset($this->next_form)){
	echo $this->next_form;
}

if(isset($this->thankyou_form)){
	echo $this->thankyou_form;
}

if(isset($this->makepayment_form)){
	echo $this->makepayment_form;
}

if(isset($this->payment_form)){
	echo $this->payment_form;
}

if(isset($this->checkout_footer)){
	echo $this->checkout_footer;
}
print_pre($this->user);
print_pre($this->order);
