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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class myMuseModelReports extends JmodelList
{
	/**
	 * catid
	 *
	 * int
	 */
	var $_catid = null;
	 
	/**
	 * catids
	 *
	 * array
	 */
	var $_catids = array();
	 
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
	 
	
	function __construct(){
		
		parent::__construct();

		$user   = JFactory::getUser();
        $profile = $user->get('profile');
		$catid = @$profile['category_owner'];
		
		if($catid){
    		$this->_catid = (int)$catid;
    		$this->getCats(true);
  
		}

	}
	
	/**
	 * Redefine the function an add some properties to make the styling more easy
	 *
	 * @param	bool	$recursive	True if you want to return children recursively.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 * @since	1.6
	 */
	public function getCats($recursive = false)
	{
		 
		if (!count($this->_catids)) {
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry();
	
			if ($active) {
				$params->loadString($active->params);
			}
	
			$options = array();
			//$options['countItems'] = $params->get('show_cat_num_articles_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			$categories = JCategories::getInstance('MyMuse', $options);
	
	
			$catid	= $this->_catid;
			$this->_parent = $categories->get($catid, $recursive);
	
			if (is_object($this->_parent)) {
				$this->_catids = $this->_parent->getChildren($recursive);
			}
			else {
				$this->_catids = false;
			}
	
		}
	
		return $this->_catids;
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
    
    	$catid	= $app->getUserStateFromRequest( $this->context.'catid','catid',$this->_catid,'int' );
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
    	// gets all the orders within time frame/matching order status
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
  	* Method to get summary of orders for a period, status
  	*
  	* @access public
  	* @return object
  	*/
  	function getOrderSummary()
  	{ 	
  	    // Get the WHERE clauses for the query
  		$where = $this->_buildContentWhere();
  		
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
  	* Method to get summary of order items for a period, status, matching categories
  	*
  	* @access public
  	* @return object
  	*/
  	function getItemsSummary()
  	{ 	

  		$catid	= $this->getState('filter.catid');
  		$orders =& $this->getItems();
 
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
  		
  		$in = '';
  		if($catid){
  			$in = "$catid,";
  		}
  		if($catid == $this->_catid){
  			foreach($this->_catids as $cat){
  				$in .= $cat->id.",";
  			}
  		}
  		$in = preg_replace("/,$/","",$in);
  		if($in){
  			$query .= " AND p.catid IN ($in) ";
  		}
  		
  		$query .= "GROUP BY product_name ORDER BY total DESC";

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
     * Method to set the category lists
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {
		$app = JFactory::getApplication();
    	// categories
    	$lists['catid'] = '';
    	$chosen_cat	= $this->_catid;
    	$catid	= $app->getUserStateFromRequest( $this->context.'catid','catid',$this->_catid,'int' );
    	
    	$in = $chosen_cat.",";
		if($this->_catids){
			foreach($this->_catids as $cat){
				$in .= $cat->id.",";
			}
			$in = preg_replace("/,$/","",$in);
			$query = 'SELECT id, title ' .
					' FROM #__categories' .
					" WHERE extension ='com_mymuse' AND id IN($in) " .
					'ORDER BY title';
			
	
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			
			$category[] = JHTML::_('select.option', '0', JText::_( 'MYMUSE_SELECT_CATEGORY' ), 'id', 'title');;
			for($i=0;$i<count($res);$i++){
				$category[] = $res[$i];
			}
			$lists['catid'] = JHTML::_('select.genericlist',  $category, 'catid', 'class="inputbox"', 'id', 'title', $catid);
		}else{
			$query = 'SELECT title ' .
					' FROM #__categories' .
					" WHERE extension ='com_mymuse' AND id =$chosen_cat";
			$this->_db->setQuery($query);
			$lists['catid'] = $this->_db->loadResult();
		}

		
		return $lists;
    }
 



}
?>
