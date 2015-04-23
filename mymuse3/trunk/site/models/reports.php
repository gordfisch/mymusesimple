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
	 * orderids: (1,2,3))
	 *
	 * string
	 */
	var $orderids = null;
    
    
    /**
	 * prodids: (1,2,3))
	 *
	 * string
	 */
	var $_prodids = null;
    
	/**
	 * catid: the top level catid
	 *
	 * int
	 */
	var $_catid = null;
    
    /**
	 * subid: a legal sub catid
	 *
	 * int
	 */
	var $_subid = null;

	/**
	 * cats: the category and sublevel objects
	 *
	 * array
	 */
	var $_cats = array();
	
	/**
	 * catids string for SQL
	 *
	 * string
	 */
	var $_catids = array();
    
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
	 

	 
	
	function __construct(){
		
		$app 			= JFactory::getApplication();
		$jinput 		= $app->input;
		$user   		= JFactory::getUser();
		$profile 		= $user->get('profile');
		$catid 			= @$profile['category_owner'];
		$this->_catid 	= (int)$catid;
		$db	 			= JFactory::getDbo();
		
		$subid			= $app->getUserStateFromRequest( $this->context.'catid','catid','','int' );
		
		//what if they want a sub-cat of the parent
		$subid = $jinput->get('catid',0);
		if($subid && $subid != $catid){
			$this->_catid = $subid;
		}
		
		$options = array();
		$categories = JCategories::getInstance('MyMuse', $options);
		 
		$this->_parent = $categories->get($this->_catid, true);
		 
		if (is_object($this->_parent)) {
			$this->_cats = $this->_parent->getChildren(true);
		}
		else {
			$this->_cats = false;
		}
			
		//make sql bit for catids. get all subcats
		$catids = '(';
		$catids .= $this->_catid.",";
		 
		if(is_array($this->_cats)){
			foreach($this->_cats as $cat){
				$catids .= $cat->id.",";
			}
		}
		$catids = preg_replace("/,$/","",$catids);
		$catids .= ")";
		$this->_catids = $catids;
		
		 
		//get products that fall under categories
		//from product_category_xref
		$q = "SELECT product_id from #__mymuse_product_category_xref
		WHERE catid IN $catids";
		$db->setQuery( $q );
		$prodres = $db->loadColumn();
		 
		
		//from products
		$q = "SELECT id as product_id from #__mymuse_product
		WHERE catid IN $catids";
		$db->setQuery( $q );
		$prodres2 = $db->loadColumn();
		
		$prodres = array_merge($prodres,$prodres2);
	
		$prodids = "(".implode(',',array_values($prodres)).")";

		

		//get child tracks
		$q = "SELECT id as product_id from #__mymuse_product
		WHERE parentid in $prodids";
		$db->setQuery( $q );
		$children = $db->loadColumn();		
        
		$prodres = array_merge($prodres,$children);
		$prodids = "(".implode(',',array_values($prodres)).")";
		
		$this->_prodids = $prodids;   
		
		parent::__construct();
     
	}
	
  	/**
  	 * getCats: fill $this->_catids with the main cat id and all subcat ids
  	 *
  	 * @param	bool	$recursive	True if you want to return children recursively.
  	 *
  	 * @return	mixed	An array of data items on success, false on failure.
  	 * @since	1.6
  	 */
  	public function getCats($catid, $recursive = false)
  	{
  			
  		if (!count($this->_cats)) {
  			$app = JFactory::getApplication();
  			$menu = $app->getMenu();
  			$active = $menu->getActive();
  			$params = new JRegistry();
  	
  			if ($active) {
  				$params->loadString($active->params);
  			}
  	
  			$options = array();
  			$categories = JCategories::getInstance('MyMuse', $options);
  	
  			$this->_parent = $categories->get($catid, $recursive);
  	
  			if (is_object($this->_parent)) {
  				$this->_cats = $this->_parent->getChildren($recursive);
  				//foreach($this->_cats as $cat){
  				//	echo "<br />".$cat->title;
  				//}
  			}
  			else {
  				$this->_cats = false;
  			}
  			
  	
  		}

  		return $this->_cats;
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
    	$db	 = $this->getDbo();
    	
        // List state information.
    	parent::populateState('a.id', 'asc');
    
    	$catid	= $this->_catid;
    	$this->setState('filter.catid', $this->_catid);

    	$this->setState('list.catids', $this->_catids);
    	
    	$this->setState('list.prodids', $this->_prodids);
    	
    	$order_status = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status', '', 'string');
    	$this->setState('filter.order_status', $order_status);

    	
    	$start_date = $app->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', '', 'string');
    	$this->setState('filter.start_date', $start_date);

    	
    	$end_date = $app->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', '', 'string');
    	$this->setState('filter.end_date', $end_date);

    	
        $this->setState('list.limit', 1000000000);

    	// Load the parameters.
    	$params = JComponentHelper::getParams('com_mymuse');
    	$this->setState('params', $params);
    	
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
    	$id.= ':' . $this->getState('filter.start_date');
    	$id.= ':' . $this->getState('filter.end_date');
    
    	return parent::getStoreId($id);
    }
    
    /**
     * Build an SQL query to load the list data.
     * Filter on start date, end date, order status and category 
     * Find order items with products that belong to the category (or sub-category) 
     * 
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
    					'i.*'
    			)
    	);
    	$query->from('`#__mymuse_order_item` AS i');
    	$query->join('LEFT',  '#__mymuse_order as a ON i.order_id=a.id');
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=a.order_status');
    
    
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if ($order_status && is_string($order_status) && $order_status != '0') {
    		$query->where('a.order_status = "'.$order_status.'"');
    	} else if ($order_status === '') {
    		//$query->where('(a.order_status IN (SELECT code from #__mymuse_order_status))');
    	}
    
    	//filter by date
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	$datenow = JFactory::getDate();
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
    		$query->where("a.created <= '$end_date 23:59:59'");
    	}
    	
    	//filter by products in cat
    	$catid = $this->getState('filter.catid');
 
    	if($catid){
    	
    		$prodids = $this->getState('list.prodids');
    		if($prodids){
    			$query->where("(i.product_id IN $prodids)");
    		}
    		$query->group('i.order_id');
    	
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
  // echo "1. $query <br /><br />";
    	return $query;
    }

    

    function _buildContentWhere()
    {

		$catid			= $this->getState('filter.catid');
		$start_date 	= $this->getState('filter.start_date');
		$end_date 		= $this->getState('filter.end_date');
		$order_status 	= $this->getState('filter.order_status');
	
		$datenow = JFactory::getDate();
		$now = $datenow->format("%Y-%m-%d");
		if($start_date == $now && $end_date == $now ){
			$start_date = '';
			$end_date = '';
		}
		
    	$where = array();
    	$where[] = "1";
		if($start_date){
			$where[] = "c.created >= '$start_date 00:00:00'";
		}
		if($end_date){
			$where[] = "c.created <= '$end_date 23:59:59'";
		}
		if ( $order_status  ) {
			$where[] = "c.order_status = '$order_status '";
		}
		
		$orderItems = $this->getItems();
		$orderids = "";
		$seen = array();
		if(is_array($orderItems) && count($orderItems)){

			$orderids = "(";
			foreach($orderItems as $item){
				if(!in_array($item->order_id,$seen  )){
					$orderids .= $item->order_id.",";
					$seen[] = $item->order_id;
				}
			}
			$orderids = preg_replace("/,$/","",$orderids);
			$orderids .= ")";
		}
		if($orderids){
			$where[] = "c.id IN ".$orderids;
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

    	return $where;
    }

     
    function getOrderlinks()
    {
    	
    		$orders = $this->getItems();
    		$orderids = "";
    		if(is_array($orders ) && count($orders)){
    			 
    			$orderids = "(";
    			foreach($orders as $order){
    				$orderids .= '<a href="index.php?option=com_mymuse&view=order&layout=edit&id='.$order->order_id;
    				$orderids .= '">'.$order->order_id."</a>, ";
    			}
    			$orderids = preg_replace("/,$/","",$orderids);
    			$orderids .= ")";
    		}
    		$this->orderids = $orderids;
   
    	return $this->orderids;
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
  		
  		$q = "SELECT * FROM #__mymuse_tax_rate ORDER BY ordering";
        $this->_db->setQuery($q);
        $tax_rates = $this->_db->loadObjectList();
        $tax_array = array();
  		
        $query = 'SELECT COUNT(*) as total_orders, SUM(c.order_subtotal) as total_subtotal, 
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
        
       //echo "2. Order Summary: $query <br /><br />";
       // print_pre($res);
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
  		$where = $this->_buildContentWhere();
  		
  		$orders = $this->getItems();
  		$order_ids = '';
  		if(is_array($orders ) && count($orders)){
  			$order_ids = "(";
  			foreach($orders as $order){
  				$order_ids .= $order->order_id.",";
  			}
  			$order_ids = preg_replace("/,$/","",$order_ids);
  			$order_ids .= ")";
  		}else{
  			return null;
  		}
  		$prodids   = $this->getState('list.prodids');
  		$catids 	= $this->getState('list.catids');
  		
  		$query = "SELECT sum(product_quantity) as quantity, sum(product_quantity * product_item_price)
  		as total, c.product_name, a.title as artist_name, c.product_id
  		FROM #__mymuse_order_item as c
  		LEFT JOIN #__mymuse_product as p ON c.product_id=p.id
  		LEFT JOIN #__categories as a ON p.catid=a.id
  		WHERE 1
  		";

  		$query .= "AND c.order_id IN $order_ids ";

  		
  		if($catids){
  			//$query .= " AND a.id IN ".$catids." ";
  		}
  		if($prodids){
  			$query .= " AND c.product_id IN $prodids
  			";
  		}
  		
  		
  		$query .= " GROUP BY product_name ORDER BY total DESC";
  	//echo "3. Items:  $query <br />";
  		$this->_db->setQuery( $query );
        $res = $this->_db->loadObjectList();
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
    	//echo "catids = ".$this->_catids." <br />";
    	//echo "prodids = ".$this->_prodids."<br />";

    	// Get the form.
    	$form = $this->loadForm('com_mymuse.reports', 'reports', array('load_data' => $loadData));
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
    	$app	= JFactory::getApplication();
    	$data['catid'] = $this->_catid;
    	 
    	$order_status = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status', '', 'string');
    	$data['filter_order_status'] = $order_status;
    	 
    	$start_date = $app->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', '', 'string');
    	$data['filter_start_date'] = $start_date;
    	 
    	$end_date = $app->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', '', 'string');
    	$data['filter_end_date'] = $end_date;
    	
    	if (empty($data)) {
    		$data = '';
    	}
    
    	return $data;
    }
    
    /**
     * Returns a reference to a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	1.6
     */
    public function getTable($type = 'Orderitem', $prefix = 'MymuseTable', $config = array())
    {
    	return JTable::getInstance($type, $prefix, $config);
    }


}