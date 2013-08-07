<?php 
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.modellist');

class MymuseModelReports extends JModelList
{
	function __construct(){
		parent::__construct();

		$this->_task = JRequest::getVar('task', null, 'default', 'cmd');
		$array = JRequest::getVar('cid',  0, '', 'array');
		if($array[0]){
    		$this->setId((int)$array[0]);
		}

	}

	/**
     * Store id
     *
     * int
     */
     var $_id = null;
     
	/**
     * Store task
     *
     * string
     */
     var $_task = null;
     
    /**
     * Store data array
     *
     * @$item->array
     */
     var $_data = null;
 
    /**
     * Store lists array
     *
     * @array
     */
     var $_lists = array();
    
 	/**
  	 * Pagination object
	 *
	 * @$item->object
	 */
     var $_pagination = null;
  
 	/**
  	* stores total
  	*
  	* @var integer
  	*/
  	var $_total = null;
  	
  	/**
  	* stores shopper
  	*
  	* @var object
  	*/
  	var $_shopper = null;
     
    /**
     * Method to set the store identifier
     *
     * @access    public
     * @param    int Store identifier
     * @return    void
     */
    function setId($id)
    {
    	// Set id and wipe data
    	$this->_id      = $id;
    	$this->_data    = null;
    }
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
    	// Initialise variables.
    	$app = JFactory::getApplication('administrator');
    
    	$catid	= $app->getUserStateFromRequest( $this->context.'catid','catid','','int' );
    	$this->setState('filter.catid', $catid);
    
    	$order_status = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status', '', 'string');
    	$this->setState('filter.order_status', $order_status);
    
    	$start_date = $app->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', '', 'string');
    	$this->setState('filter.start_date', $start_date);
    
    	$end_date = $app->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', '', 'string');
    	$this->setState('filter.end_date', $end_date);
    
    	// Load the parameters.
    	$params = JComponentHelper::getParams('com_mymuse');
    	$this->setState('params', $params);
    
    	// List state information.
    	parent::populateState('a.id', 'asc');
    }
    
    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '')
    {
    	// Compile the store id.
    	$id.= ':' . $this->getState('filter.catid');
    	$id.= ':' . $this->getState('filter.order_status');
    
    	return parent::getStoreId($id);
    }
    
    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery()
    {
    	// Create a new query object.
    	$db		= $this->getDbo();
    	$query	= $db->getQuery(true);
    
    	// Select the required fields from the table.
    	$query->select(
    			$this->getState(
    					'list.select',
    					'a.*',
    					'u.first_name, u.last_name '
    			)
    	);
    	$query->from('`#__mymuse_order` AS a');
    
    	// Join over the users for the checked out user.
    	$query->select('uc.name AS editor');
    	$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
    
    	// Join over the users for the order owner.
    	$query->select('u.name AS shopper');
    	$query->join('LEFT', '#__users AS u ON u.id=a.user_id');
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=a.order_status');
    
    
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if (is_string($order_status) && $order_status != '') {
    		$query->where('a.order_status = "'.$order_status.'"');
    	} else if ($order_status === '') {
    		//$query->where('(a.order_status IN (SELECT code from #__mymuse_order_status))');
    	}
    
    	//filter by date
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	$datenow =& JFactory::getDate();
    	$now = $datenow->format("%Y-%m-%d");
    
    	if($start_date== $now && $end_date == $now ){
    		$start_date = '';
    		$end_date = '';
    	}
    
    	$where = array();
    	if($start_date){
    		$query->where("a.created >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("a.created <= '$end_date 00:00:00'");
    	}
    
    	// Filter by search in title
    	$search = $this->getState('filter.search');
    	if (!empty($search)) {
    		if (stripos($search, 'id:') === 0) {
    			$query->where('a.id = '.(int) substr($search, 3));
    		} else {
    			$search = $db->Quote('%'.$db->escape($search, true).'%');
    			$query->where("u.name LIKE $search");
    		}
    	}
    
    	// Add the list ordering clause.
    	$orderCol	= $this->state->get('list.ordering');
    	$orderDirn	= $this->state->get('list.direction');
    	if ($orderCol && $orderDirn) {
    		$query->order($db->escape($orderCol.' '.$orderDirn));
    	}
   
    	return $query;
    }

    

    function _buildContentWhere()
    {

		$catid			= $this->getState('filter.catid');
		$start_date 	= $this->getState('filter.start_date');
		$end_date 		= $this->getState('filter.end_date');
		$order_status 	= $this->getState('filter.order_status');
	
		$datenow =& JFactory::getDate();
		$now = $datenow->format("%Y-%m-%d");
		if($start_date == $now && $end_date == $now ){
			$start_date = '';
			$end_date = '';
		}
		
    	$where = array();
		if($start_date){
			$where[] = "c.created >= '$start_date 00:00:00'";
		}
		if($end_date){
			$where[] = "c.created <= '$end_date 23:59:59'";
		}
		if ( $order_status  ) {
			$where[] = "c.order_status = '$order_status '";
		}


		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

    	return $where;
    }
    /**
     * Returns the query
     * @return string The query to be used to retrieve the rows from the database
     */
    function _buildQueryOne()
    {
        $query = 'SELECT *'
		. ' FROM #__mymuse_order  '
		. ' WHERE id='.$this->_id;
        return $query;
    }
     

  
  	/**
  	* Method to get summary of orders for a period, status, shopper
  	*
  	* @access public
  	* @return object
  	*/
  	function getOrderSummary()
  	{ 	
  	    // Get the WHERE clauses for the query
  		$where = $this->_buildContentWhere();
  		
  		// query by shopper
  		$q_by_s = "SELECT SUM(c.order_subtotal) as total_subtotal, 
		SUM(c.order_shipping) as total_shipping, u.first_name, u.last_name
  		FROM #__mymuse_order AS c LEFT JOIN #__mymuse_shopper as u ON c.shopper_id = u.id "
  		. $where 
  		. " GROUP BY shopper_id";
  		
  		$q = "SELECT * FROM #__mymuse_tax_rate ORDER BY ordering";
        $this->_db->setQuery($q);
        $tax_rates = $this->_db->loadObjectList();
        $tax_array = array();
  		
        $query = 'SELECT SUM(c.order_subtotal) as total_subtotal, 
		SUM(c.order_shipping) as total_shipping, 
		SUM(c.discount) as total_discount ,
		SUM(c.coupon_discount) as total_coupon_discount, 
		SUM(c.reservation_fee) as total_reservation_fee
		';
        foreach($tax_rates as $rate){
        	$name = trim($rate->tax_name);
        	$name = preg_replace("/['-\/\s\\\]/","_",$name);
        	$query .= ", SUM(c.$name) as $name ";
        	$tax_array[] = $name;
        }
        
        
		$query .= ' FROM #__mymuse_order AS c'
        . $where;
            
        $this->_db->setQuery( $query );
        $res = $this->_db->loadObject();
        $res->tax_array = $tax_array;
        
        //echo "$query <br />";
        //print_pre($res);
        return $res;
  	
  	}
  	
  	
  	/**
  	* Method to get summary of order items for a period, status, shopper
  	*
  	* @access public
  	* @return object
  	*/
  	function getItemsSummary()
  	{ 	

  		$catid	= $this->getState('filter.catid');
  		$orders =& $this->getItems();
  		if(!count($orders)){
  			return null;
  		}
 
  		$order_ids = "(";
  		foreach($orders as $order){
  		    $order_ids .= $order->id.",";
  		}
  		$order_ids = preg_replace("/,$/","",$order_ids);
  		$order_ids .= ")";
  		
  		$query = "SELECT sum(product_quantity) as quantity, sum(product_quantity * product_item_price) 
  		as total, i.product_name, a.title as artist_name, p.id as product_id
  		FROM #__mymuse_order_item as i
  		LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
  		LEFT JOIN #__categories as a ON p.catid=a.id 
  		WHERE i.order_id IN $order_ids 
  		";
  		
  		if($catid){
  			$query .= "AND a.id=$catid ";
  		}
  		
  		$query .= "GROUP BY product_name ORDER BY total DESC";
  		//echo "$query <br />";
  		$this->_db->setQuery( $query );
        $res = $this->_db->loadObjectList();
       
        for($i=0;$i<count($res);$i++){
        	if($res[$i]->artist_name == ""){
        		$query = "SELECT a.title as artist_name FROM #__categories as a, #__mymuse_product as p 
					LEFT JOIN #__mymuse_product as p2 ON p2.id=p.parentid
					WHERE  p.id='".$res[$i]->product_id."' AND p2.catid=a.id"; 
        		$this->_db->setQuery( $query );
        		$res[$i]->artist_name = $this->_db->loadResult();
        		
        		
        	}
        }
        
  		//print_pre($res);
        return $res;;
  	}
  	
  	
    /**
  	* Method to get the shopper and possible shiptto entries
  	*
  	* @access public
  	* @return object
  	*/
  	function getShopper()
  	{
  		// Lets load the shopper if it does not exist
  		if (empty($this->_shopper) && isset($this->_data[0]->id))
  		{
  			$query = "SELECT s.*,u.email FROM #__mymuse_shopper as s  
  				LEFT JOIN #__users as u ON u.id=s.user_id
  				WHERE s.id=".$this->_data[0]->shopper_id;
  			$this->_db->setQuery( $query );
  			$this->_shopper = $this->_db->loadObject();
  			
  			if($this->_data[0]->ship_info_id && ($this->_data[0]->ship_info_id != $this->_data[0]->shopper_id)){
  				$query = "SELECT s.*,u.email FROM #__mymuse_shopper as s  
  					LEFT JOIN #__users as u ON u.id=s.user_id
  					WHERE s.id=".$this->_data[0]->ship_info_id;
  				$this->_db->setQuery( $query );
  				$this->_shopper->shipto = $this->_db->loadObject();
  			}
  		}
 		return $this->_shopper;
  	}
  	
    /**
  	* Method to get the order items
  	*
  	* @access public
  	* @return objects
  	*/
  	function getOrderItems()
  	{
  		// Lets load the items if they do not exist
  		if (empty($this->_data[0]->items))
  		{
  			$query = "SELECT * FROM #__mymuse_order_item 
  				WHERE order_id=".$this->_id;
  			$this->_db->setQuery( $query );
  			$this->_data[0]->items = $this->_db->loadObjectList();
  			$this->_data[0]->order_total = 0.00;
  		  	for($i = 0; $i < count($this->_data[0]->items); $i++){

				$this->_data[0]->items[$i]->subtotal = sprintf("%.2f", $this->_data[0]->items[$i]->product_item_price * $this->_data[0]->items[$i]->product_quantity);
				$this->_data[0]->order_total += $this->_data[0]->items[$i]->subtotal;
  			}
  			
  		}
  		$this->_data[0]->order_total = sprintf("%.2f", $this->_data[0]->order_total);
 		return $this->_data[0]->items;
  	}
  	


    /**
     * Method to get a pagination object for orders
     *
     * @access public
     * @return object
     */
    function getPagination()
    {
    	// Lets load the pagination if it doesn't already exist
    	if (empty($this->_pagination))
    	{
    		jimport('joomla.html.pagination');
    		$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
    	}
    	return $this->_pagination;
    }
  
    /**
     * Method to set the store lists
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {


		
    	// categories
    	$chosen_cat	= $this->getState('filter.catid');

		$query = 'SELECT id, title ' .
			' FROM #__categories' .
			' WHERE extension ="com_mymuse" ';


		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		$category[] = JHTML::_('select.option', '0', JText::_( 'MYMUSE_SELECT_CATEGORY' ), 'id', 'title');;
		for($i=0;$i<count($res);$i++){
			$category[] = $res[$i];
		}
		$lists['catid'] = JHTML::_('select.genericlist',  $category, 'catid', 'class="inputbox"', 'id', 'title', $chosen_cat);
		
		return $lists;
    }
 



}