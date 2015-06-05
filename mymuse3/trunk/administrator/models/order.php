<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Mymuse model.
 */
class MymuseModelorder extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_MYMUSE';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Order', $prefix = 'MymuseTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_mymuse.order', 'order', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mymuse.edit.order.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single order and its items, payments and shipping
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$params = MyMuseHelper::getParams();
		$this->setState('order.id',JRequest::getVar('id'));
		if ($item = parent::getItem($pk)) {

			// Lets load the items
			$query = "SELECT * FROM #__mymuse_order_item
  				WHERE order_id=".$item->id;

			$db = JFActory::getDBO();
			$db->setQuery( $query );
			$item->items = $db->loadObjectList();
			
			for($i=0; $i<count($item->items); $i++){
				$item->items[$i]->parent_name = '';
				$item->items[$i]->category_name = '';
				$query = "SELECT c.title, p.catid, p.parentid 
						
						FROM #__mymuse_product as p LEFT JOIN #__categories as c ON c.id=p.catid
						WHERE p.id='".$item->items[$i]->product_id."'";
				$db->setQuery( $query );
				$res = $db->loadObject();
				$item->items[$i]->category_name = $res->title;
				$item->items[$i]->parentid = $res->parentid;
				if($item->items[$i]->parentid > 0){
					$query = "SELECT title from #__mymuse_product
							WHERE id = '".$item->items[$i]->parentid."'";
					$db->setQuery( $query );
					$item->items[$i]->parent_name = $db->loadResult();
				}
			}
		
			$item->order_total = 0.00;
			$downloadable = 0;

			$item->downloadlink= '';
			for($i = 0; $i < count($item->items); $i++){

				$item->items[$i]->subtotal = sprintf("%.2f", $item->items[$i]->product_item_price * $item->items[$i]->product_quantity);
				$item->order_total += $item->items[$i]->subtotal;
				if($item->items[$i]->file_name != ''){
					$downloadable++;
				}
			}
  			

  			if($downloadable){
  				if($params->get('my_registration') == "no_reg"){
  					$item->downloadlink = JURI::root()."index.php?option=com_mymuse&task=accdownloads&id=".$item->order_number;
  				}else{
  					$item->downloadlink = JURI::root()."index.php?option=com_mymuse&task=downloads&id=".$item->order_number;
  				}
  				//load any downloads
  				$query = "SELECT * FROM #__mymuse_downloads
  				WHERE order_id=".$item->id;
  				
  				$db = JFActory::getDBO();
  				$db->setQuery( $query );
  				$item->downloads = $db->loadObjectList();
  			}
  			
  		
			// get taxes and total
			$item->tax_array = array();
			$item->tax_total = 0;
			$item->paid_to_date =  0;
			 
			$q = "SELECT * FROM #__mymuse_tax_rate ORDER BY ordering";
			$db->setQuery($q);
			$tax_rates = $db->loadObjectList();
			foreach($tax_rates as $rate){
				$name = trim($rate->tax_name);
				//$regex = TAX_REG_EX;
				$name = preg_replace("/['-\/\s\\\]/","_",$name);
				if($item->$name > 0.00){
					$item->tax_array[$name] = $item->$name;
					$item->tax_total += $item->tax_array[$name];
				}
			}

			$item->order_total	= $item->order_subtotal
			+ $item->order_shipping + $item->tax_total;
			$item->order_total = sprintf("%.2f", $item->order_total);
			
			// get payment history
			$q = "SELECT * from #__mymuse_order_payment
        			WHERE order_id='".$item->id."'
        			ORDER BY date";
			$db->setQuery($q);
			$item->order_payments = $db->loadObjectList();
			if($item->order_payments){
				foreach($item->order_payments as $payment){
					$item->paid_to_date += $payment->amountin - $payment->amountout;
				}
			}
			 
			// get shipping history
			$q = "SELECT * from #__mymuse_order_shipping
        			WHERE order_id='".$item->id."'
        			ORDER BY created";
			$db->setQuery($q);
			$item->order_shipments = $db->loadObjectList();
        			
			// get user details
			$item->user = JFactory::getUser($item->user_id);
			$profile_key = $params->get('my_profile_key', 'mymuse');
			
			// Load the profile data from the database.
			$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
			' WHERE user_id = '.(int) $item->user_id .
			' AND profile_key LIKE \''.$profile_key.'.%\'' .
			' ORDER BY ordering';
			$db->setQuery($query);
			$results = $db->loadRowList();

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->_subject->setError($db->getErrorMsg());
				return false;
			}
			// Merge the profile data.
			$item->user->profile = array();
			foreach ($results as $v) {
				$k = str_replace("$profile_key.", '', $v[0]);
				$item->user->profile[$k] = trim(json_decode($v[1], true),'"');
				
			}
			
			//if we are using no_reg
			if($params->get('my_registration') == "no_reg"){
				$fields = MyMuseHelper::getNoRegFields();
				$registry = new JRegistry;
				$registry->loadString($item->notes);
				foreach($fields as $field){
					if($registry->get($field)){
						$item->user->profile[$field] = $registry->get($field);
						//echo $field." ".$registry->get($field)."<br />";
					}else{
						$item->user->profile[$field] = '';
					}
				}
				if(isset($item->user->profile['first_name'])){
					$item->user->name = $item->user->profile['first_name']." ".@$item->user->profile['last_name'];
				}
				if($item->user->profile['email']){
					$item->user->email = $item->user->profile['email'];
				}
			}
			
			
			if(isset($item->user->profile['shopper_group'])){
				$query = "SELECT * from #__mymuse_shopper_group WHERE id=".(int)$item->user->profile['shopper_group'];
				$db->setQuery($query);
				if($shopper_group = $db->loadObject()){
					$item->user->shopper_group_name = $shopper_group->shopper_group_name;
					$item->user->shopper_group_discount = $shopper_group->discount;
				}
			}else{
				$item->user->shopper_group_name = "default";
				$item->user->shopper_group_discount = 0;
			}
		}

		return $item;
	}
	
	
	public function getLists()
	{
		$params = MyMuseHelper::getParams();
		//currencies
        $query = "SELECT currency_code as value, CONCAT(symbol,': ',currency_name) as text from #__mymuse_currency ORDER BY currency_code ASC";
        $this->_db->setQuery($query);
        $options = $this->_db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_CURRENCY').' -', 'value', 'text'));
	    $value = $params->get('my_currency');
        $lists['currencies'] = JHTML::_('select.genericlist',  $options, 'currency', 'class="inputbox"', 'value', 'text', $value, JText::_( 'MYMUSE_CURRENCY' ));   

		//payment plugins
        JPluginHelper::importPlugin('mymuse');
		$query = "SELECT element as value, name as text
		FROM #__extensions where folder='mymuse' and enabled='1' and element LIKE '%payment%'";
		$this->_db->setQuery($query);
        $options = $this->_db->loadObjectList();
        for($i=0; $i< count($options); $i++){
        	$options[$i]->text = JText::_($options[$i]->text);
        }
        array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_PLUGIN').' -', 'value', 'text'));
	    $value = '';
        $lists['plugins'] = JHTML::_('select.genericlist',  $options, 'payment_plugin', 'class="inputbox"', 'value', 'text', $value, JText::_( 'MYMUSE_PLUGIN' ));   
	    
		return $lists;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$jinput = JFactory::getApplication()->input;
		$post = $jinput->getArray($_POST);
		$params = MyMuseHelper::getParams();

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mymuse_order');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
		$form = JRequest::getVar('jform',array(),'post');
		// payments
        if($form['payment_date']){
        	$payment['order_id'] 			= $form['id'];
        	$payment['date'] 				= $form['payment_date'];
        	$payment['plugin'] 				= JRequest::getVar('payment_plugin');
        	$payment['institution'] 		= JRequest::getVar('payment_institution');
        	$payment['amountin'] 			= JRequest::getVar('payment_amountin');
        	$payment['currency'] 			= JRequest::getVar('currency');
        	$payment['rate'] 				= JRequest::getVar('payment_rate');
        	$payment['fees'] 				= JRequest::getVar('payment_fees');
        	$payment['transaction_id'] 		= JRequest::getVar('payment_transaction_id');
        	$payment['transaction_status'] 	= JRequest::getVar('payment_transaction_status');
        	$payment['description'] 		= JRequest::getVar('payment_description');

        	$MyMuseHelper = new MyMuseHelper;
        	if(!$MyMuseHelper->logPayment($payment)){
        		$this->setError($MyMuseHelper->getError());
        		return false;
        	}
        	
        }
	        //update stock if order is completed
        	$debug = '';
        	$order = $this->getItem();

        	if ($params->get('my_use_stock') && $form['order_status'] == "C") {

        		for($i = 0; $i < count($order->items); $i++) {

        			if($order->items[$i]->file_name == ''){
        				if (!MyMuseHelper::updateStock($order->items[$i]->product_id, $order->items[$i]->product_quantity)) {
        					$db= JFactory::getDBO();
        					$debug .= "Could not update stock\n".$db->getErrorMsg()."\n";
        				}else{
        					$debug .= " Subtracted ".$order->items[$i]->product_quantity. " from ".$order->items[$i]->product_name."\n";
        				}
        			}
        		}
        	}
        	echo $debug;
        	if($params->get('my_debug')){
        		$debug .= "-------END ORDER SAVE-------";
        		MyMuseHelper::logMessage( $debug  );
        	}

        
	}
	
	/**
	 * reset downloads
	 * @return void
	 */
	function resetDownloads()
	{
		$params = MyMuseHelper::getParams();
	
		$id = JRequest::getVar( 'id', '' );
		if(!$id){
			$this->setError("MYMUSE_ORDER_ID_NOT_FOUND");
			return false;
		}
		$db = JFactory::getDBO();
		$query = "UPDATE #__mymuse_order SET order_status='C' WHERE id=$id";
		$db->setQuery($query);
		if(!$db->query()){
			$this->setError("MYMUSE_COULD_NOT_UPDATE_ORDER");
			return false;
		}
		$enddate = time() + $params->get('my_download_expire');
		$query = "UPDATE #__mymuse_order_item
		SET downloads='0',
		end_date='$enddate'
		WHERE order_id=$id
		";
		$db->setQuery($query);
		if(!$db->query()){
			$this->setError("MYMUSE_COULD_NOT_UPDATE_ORDER_ITEMS");
			return false;
		};
	
		return true;
	
	}

}