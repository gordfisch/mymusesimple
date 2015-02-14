<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.modellist');

class MymuseModelReports extends JModelList
{

	/**
     * Store id
     *
     * int
     */
     var $_id = null;
     
 
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
	 * cats: the main and sublevel category objects
	 *
	 * array
	 */
	var $_cats = array();
    
    
    /**
	 * orders order objects
	 *
	 * string
	 */
	var $_orders = null;
    
    /**
	 * orderids: (1,2,3))
	 *
	 * string
	 */
	var $orderids = null;
    
    
    /**
	 * catid: the top level catid
	 *
	 * int
	 */
	var $_catid = null;
    

  
  	function __construct(){
  		
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
    
    	$catid	= $app->getUserStateFromRequest( $this->context.'catid','catid','','int' );
   
    	$this->setState('filter.catid', $catid);
    	if($catid){
    		$this->getCats($catid, true);
    		//make sql bit for catids. get all subcats
    		$catids = '(';
    		$catids .= "$catid,";
    		 
    		if(is_array($this->_cats)){
    			foreach($this->_cats as $cat){
    				$catids .= $cat->id.",";
    			}
    		}
    		$catids = preg_replace("/,$/","",$catids);
    		$catids .= ")";
    	
    		$this->setState('list.catids', $catids);
    	
    		//get products that fall under categories
    		
    		//from product_category_xref 
    		$q = "SELECT product_id from #__mymuse_product_category_xref
    		WHERE catid IN $catids";
    		$db->setQuery( $q );
    		$prodres = $this->_db->loadObjectList();
    		
    		$q = "SELECT p.id as product_id from #__mymuse_product as p
    		WHERE p.catid IN $catids";
    		$db->setQuery( $q );
    		$prodres2 = $this->_db->loadObjectList();
    	
    		$prodres = array_merge($prodres,$prodres2);
    	
    		//get children
    		foreach($prodres as $id){
    			$query = "SELECT p.id as product_id from #__mymuse_product as p
    			WHERE parentid='$id->product_id'";
    			$db->setQuery( $query );
    			$children = $this->_db->loadObjectList();
    			if(count($children)){
    				$prodres = array_merge($prodres,$children);
    			}
    		}
    		//make sql bit for productid.
    		$prodids = '';
    		$seen = array();
    		if($prodres){
    			$prodids = "(";
    			foreach($prodres as $id){
    				if(!in_array($id->product_id, $seen)){
    					
    					$prodids .= $id->product_id.",";
    				}
    				$seen[] = $id->product_id;
    			}
    			$prodids = preg_replace("/,$/","",$prodids);
    			$prodids .= ")";
    		}
    		$this->setState('list.prodids', $prodids);
    	
    	}
    	
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
    	if (is_string($order_status) && $order_status != '0') {
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
    	
    	//filter by cat and product
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
    	//echo "1. $query <br /><br />";
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
    	
    	$orderItems = $this->getItems();
    	$orderids = "";
    	if(is_array($orderItems ) && count($orderItems )){

    		$orderids = "(";
    		foreach($orderItems as $item){
    			$orderids .= '<a href="index.php?option=com_mymuse&view=order&layout=edit&id='.$item->order_id;
    			$orderids .= '">'.$item->order_id."</a>, ";
    		}
    		$orderids = preg_replace("/,$/","",$orderids);
    		$orderids .= ")";
    	}

    	return $orderids;
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
  		
        $query = 'SELECT COUNT(*) as total_orders,  SUM(c.order_subtotal) as total_subtotal, 
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
  		}
  		$prodids   = $this->getState('list.prodids');
  		$catids 	= $this->getState('list.catids');
  		
  		$query = "SELECT sum(product_quantity) as quantity, sum(product_quantity * product_item_price)
  		as total, c.product_name, a.title as artist_name, c.product_id, pp.title as parent
  		FROM #__mymuse_order_item as c
  		LEFT JOIN #__mymuse_product as p ON c.product_id=p.id
  		LEFT JOIN #__mymuse_product as pp ON p.parentid=pp.id
  		LEFT JOIN #__categories as a ON p.catid=a.id
  		WHERE 1
  		";
  		
  		
  		if($order_ids){
  			$query .= "AND c.order_id IN $order_ids
  			";
  		}
  		
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
    	$app	= JFactory::getApplication();
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	
    	// Get the form.
    	$form = $this->loadForm('com_mymuse.reports', 'reports', array());
    	if($start_date){
    		$form->setFieldAttribute('filter_start_date', 'default', $start_date);
    	}
    	if($end_date){
    		$form->setFieldAttribute('filter_end_date', 'default', $end_date);
    	}
    	
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
    	$data = JFactory::getApplication()->getUserState('com_mymuse.edit.reports.data', array());
    
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
    
    
    /*
     * get downloads query
     */
    protected function getDownloadsQuery()
    {
    	// Create a new query object.
    	$db		= $this->getDbo();
    	$query	= $db->getQuery(true);
    	
    	// Select the required fields from the table.
    	$query->select('a.*');
    	$query->from('`#__mymuse_downloads` AS a');
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
    		$query->where("a.date >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("a.date <= '$end_date 23:59:59'");
    	}
    	
    	//filter by cat and product
    	$catid = $this->getState('filter.catid');
    	
    	if($catid){
    		$prodids = $this->getState('list.prodids');
    		if($prodids){
    			$query->where("(a.product_id IN $prodids)");
    		}
    		
    		 
    	}
    	
    	$query->order($db->escape('a.id ASC'));
    	
    	return $query;
    }
    
    /*
     * get downloads table
     */
    public function getDownloads()
    {
    	$db		= $this->getDbo();
    	$query = $this->getDownloadsQuery();
    	$db->setQuery($query);
    	$res = $db->loadObjectList();
  		
    	return $res;
    }
    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getOrderQuery()
    {
    	// Create a new query object.
    	$db		= $this->getDbo();
    	$query	= $db->getQuery(true);
  /*
   * SELECT o.*, u.name, u.email,os.name AS status_name 
FROM `vl6xc_mymuse_order` AS o 
LEFT JOIN vl6xc_users as u on u.id=o.user_id 
LEFT JOIN vl6xc_mymuse_order_status AS os ON os.code=o.order_status 
LEFT JOIN vl6xc_mymuse_order_item AS i ON i.order_id=o.id WHERE (i.product_id IN (1,2,3,4,5,6,548,549,550,560,561,562,564,565,559,556,557,558,9,10,11,12,13,14,16,17,18,19,20,21,566,457,458,459,460,504,26,28,29,30,31,32,33,34,35,36,37,38,39,40,45,44,46,47,48,49,50,51,52,53,54,55,56,57,63,62,64,65,66,67,68,69,70,71,72,73,74,82,81,83,84,85,86,87,88,89,90,91,92,97,96,98,99,100,101,102,103,104,105,106,107,108,115,112,151,152,153,154,156,157,158,159,160,161,162,163,164,165,166,167,168,169,178,239,179,180,181,182,183,184,185,186,187,188,189,193,192,194,195,196,197,198,199,200,201,202,203,204,211,210,212,213,214,215,244,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,238,240,241,242,243,245,246,247,248,249,250,255,254,256,257,258,259,260,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,280,309,310,311,312,313,314,315,316,317,318,319,320,321,322,346,326,354,344,353,348,349,350,351,352,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,374,371,373,375,376,377,378,379,380,381,382,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,410,461,462,463,464,465,466,467,468,471,495,494,493,496,497,498,499,500,
501,502,503,505,506,507,508,509,510,511,512,513,514,515,516,517,518,519,520,521,522,526,544,545,551)) 
GROUP BY i.order_id ORDER BY o.id ASC 
   */  
    	
    	// Select the required fields from the table.
    	$query->select('o.*, u.name, u.email');
    	$query->from('`#__mymuse_order` AS o');
    	$query->join('LEFT',  '#__users as u on u.id=o.user_id');
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=o.order_status');
    
   
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if (is_string($order_status) && $order_status != '0') {
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
    		$query->where("o.created >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("o.created <= '$end_date 23:59:59'");
    	}
    	 
    	//filter by cat and product
    	$catid = $this->getState('filter.catid');
    	 
    	if($catid){
    		$query->join('LEFT', '#__mymuse_order_item AS i ON i.order_id=o.id');    		
    		$prodids = $this->getState('list.prodids');
    		if($prodids){
    			$query->where("(i.product_id IN $prodids)");
    		}
    		$query->group('i.order_id');
    		 
    	}
    
    	$query->order($db->escape('o.id ASC'));
    	
    	return $query;
    }
    
    /**
     * Build an SQL query to load the item table data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getItemQuery()
    {
    	// Create a new query object.
    	$db		= $this->getDbo();
    	$query	= $db->getQuery(true);
    
    	// Select the required fields from the table.
    	$query->select('u.name, u.email, i.*');
    	$query->from('`#__mymuse_order_item` AS i');
    	$query->join('LEFT',  '#__mymuse_order as o ON i.order_id=o.id');
    	$query->join('LEFT',  '#__users as u on u.id=o.user_id');
    	
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=o.order_status');
    
    
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if (is_string($order_status) && $order_status != '0') {
    		$query->where('o.order_status = "'.$order_status.'"');
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
    		$query->where("i.created >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("i.created <= '$end_date 23:59:59'");
    	}
    	 
    	//filter by cat and product
    	$catid = $this->getState('filter.catid');
    	 
    	if($catid){
    		 
    		$prodids = $this->getState('list.prodids');
    		if($prodids){
    			$query->where("(i.product_id IN $prodids)");
    		}
    		//$query->group('i.id');
    		 
    	}
    
    	// Filter by search in title
    	$search = $this->getState('filter.search');
    	if (!empty($search)) {
    		if (stripos($search, 'id:') === 0) {
    			$query->where('i.id = '.(int) substr($search, 3));
    		} else {
    			$search = $db->Quote('%'.$db->escape($search, true).'%');
    			$query->where("u.name LIKE $search");
    		}
    	}
    
    	// Add the list ordering clause.
    	$query->order($db->escape('i.id ASC'));
    //echo "1. $query <br /><br />"; exit;
    	return $query;
    }
    
    
    
    /**
     * download as CSV
     * 
     */
     
     public function getCSV($table='report')
     {

     	$db = JFactory::getDBO();
     	if($table == "mymuse_order"){
     		$query =  $this->getOrderQuery ();
     		
     	}elseif($table == "mymuse_order_item"){
     		$query =  $this->getItemQuery ();
     	}elseif($table == "mymuse_downloads"){
     		$query =  "SELECT * FROM #__mymuse_downloads";
     	}else{
 			$this->populateState();
			$query =  $this->getListQuery ();
     	}
     	
     	//echo $query; exit;
     	$db->setQuery($query);
     	$items = $db->loadObjectList();
		$cols = array_keys ( ( array ) $items[0] );
	
		$csv = fopen ( 'php://output', 'w' );
		fputcsv ( $csv, ( array ) $cols );
		foreach ( $items as $line ) {
			fputcsv ( $csv, ( array ) $line );
		}
		return fclose ( $csv ); 
     	

     }
  
    


}