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
	 * rrealorderids: (1,3,5))
	 *
	 * string
	 */
    var $realorderids =  null;
    
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
	 * catids: the sublevel catids
	 *
	 * array
	 */
	var $_catids = array();
    
    /**
	 * catidsIN: the sublevel catids
	 *
	 * string
	 */
	var $_catidsIN = '';
	 
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
		$userid = $user->get('id');
		
		if(!$userid){
			return;
		}     
		$profile = $user->get('profile');
		$catid = @$profile['category_owner'];
        $this->_catid = (int)$catid;

        //what if they want a sub-cat of the parent
        $subid = JRequest::getVar('catid',0);
        if($subid && $subid != $catid){

            //this really is not the profile catid
            //is it legal? a subcat of the parent
            $categories = JCategories::getInstance('MyMuse');
            $cat = $categories->get($catid);
            $children = $cat->getChildren();
            $good = 0;
            foreach($children as $child){
                if($child->id == $subid){
                    $good = 1;
                }
            }
            if($good){
                $this->_subid = $subid;
                //for the scope of this function
                $catid = $subid;
            }

        }
        //load any subcats
		if($catid){
    		$this->getCats(true);
		}
        //make sql bit for catids
        $catids = '(';
  		if($catid){
  			$catids .= "$catid,";
  		}
  		if(is_array($this->_catids)){
  			foreach($this->_catids as $cat){
  				$catids .= $cat->id.",";
  			}
  		}
  		$catids = preg_replace("/,$/","",$catids);
  		$catids .= ")";    
        
        //make sql bit for orderids
  		$orders =& $this->getItems();
  		$orderids = "(";
  		foreach($orders as $order){
  		    $orderids .= $order->id.",";
  		}
  		$orderids = preg_replace("/,$/","",$orderids);
  		$orderids .= ")";

        //get products that fall under categories
        $query = "SELECT product_id from #__mymuse_product_category_xref 
        WHERE catid IN $catids
        GROUP BY product_id";
        $this->_db->setQuery( $query );
        $prodres = $this->_db->loadObjectList();
        
        $query = "SELECT id as product_id from #__mymuse_product
        WHERE catid IN $catids";
        $this->_db->setQuery( $query );
        $prodres2 = $this->_db->loadObjectList();
        
        $prodres = array_merge($prodres,$prodres2);
        foreach($prodres as $id){
        	$prodarr[] = $id->product_id.",";
        }
        $prodarr = array_unique($prodarr, SORT_NUMERIC);
        sort($prodarr, SORT_NUMERIC);


        //make sql bit for catids
        $_prodids = "(";
        foreach($prodres as $id){
  		    $_prodids .= $id->product_id.",";
  		}
  		$_prodids = preg_replace("/,$/","",$_prodids);
  		$_prodids .= ")";
        
        $this->_catidsIN = $catids; 
        $this->_prodids = $_prodids;
        $this->_orderids = $orderids;
      	/*
        echo "catids "; print_pre($this->_catidsIN);
        echo "prodids "; print_pre($this->_prodids);
        echo "orderids "; print_pre($this->_orderids);
		*/
	}
	
	/**
	 * getCats: fill $this->_catids with the main cat id and all subcat ids
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
            $catid	= $this->_catid;
            if($this->_subid){
                 $catid	= $this->_subid;
            }
			$options = array();
			//$options['countItems'] = $params->get('show_cat_num_articles_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			$categories = JCategories::getInstance('MyMuse', $options);
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
        if(isset($this->realorderids)){
            $where[] = "c.id IN ".$this->realorderids;
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
        
        return $res;
  	
  	}
  	
  	/**
  	* Method to get orders that match
  	*
  	* @access public
  	* @return object
  	*/
  	function getOrders()
  	{ 
        $orderids  = $this->_orderids;
        $_prodids   = $this->_prodids;

        if($orderids == '()' || $_prodids == '()'){
        	return;
        }

  		$query = "SELECT o.id 
  		FROM #__mymuse_order as o
        LEFT JOIN #__mymuse_order_item as i ON i.order_id=o.id
        LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
  		WHERE i.order_id IN $orderids 
        AND (p.id IN $_prodids
        OR p.parentid IN $_prodids)
        GROUP BY o.id";

        
        $this->_db->setQuery( $query );
        $orders = $this->_db->loadObjectList();
        
        $orderids = "(";
  		foreach($orders as $order){
  		    $orderids .= $order->id.",";
  		}
  		$orderids = preg_replace("/,$/","",$orderids);
  		$orderids .= ")";
        
        $this->realorderids = $orderids;
        
        return $orders;
        
    }
    
    
  	/**
  	* Method to get summary of order items for a period, status, matching categories
  	*
  	* @access public
  	* @return object
  	*/
  	function getItemsSummary()
  	{ 	

  		
        $orderids  = $this->_orderids;
        $_prodids   = $this->_prodids;
        if($orderids == '()' || $_prodids == '()'){
        	return;
        }

  		$query = "SELECT sum(product_quantity) as quantity, sum(product_quantity * product_item_price) 
  		as total, i.product_name, a.title as artist_name, p.id as product_id
  		FROM #__mymuse_order_item as i
        LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
        LEFT JOIN #__categories as a ON p.catid=a.id
  		WHERE i.order_id IN $orderids 
        AND (p.id IN $_prodids
        OR p.parentid IN $_prodids)
  		";
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
    	$top_cat	= $this->_catid;
    	$request_catid	= $app->getUserStateFromRequest( $this->context.'catid','catid',$this->_catid,'int' );
    	
    	$in = $top_cat.",";
        $categories = JCategories::getInstance('MyMuse');
        $cat = $categories->get($top_cat);
        $children = $cat->getChildren();

		if($children){
			foreach($children as $cat){
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
			$lists['catid'] = JHTML::_('select.genericlist',  $category, 'catid', 'class="inputbox"', 'id', 'title', $request_catid);
		}else{
			$query = 'SELECT title ' .
					' FROM #__categories' .
					" WHERE extension ='com_mymuse' AND id =$top_cat";
			$this->_db->setQuery($query);
			$lists['catid'] = $this->_db->loadResult();
		}

		
		return $lists;
    }
 



}