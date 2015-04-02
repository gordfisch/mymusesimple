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
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.modellist');

/**
 * This models supports retrieving a category, the articles associated with the category,
 * sibling, child and parent categories.
 *
 * @package		Joomla.Site
 * @subpackage	com_mymuse
 * @since		1.5
 */
class MyMuseModelCategory extends JModelList
{
	/**
	 * Category items data
	 *
	 * @var array
	 */
	protected $_item = null;

	protected $_products = null;

	protected $_siblings = null;

	protected $_children = null;

	protected $_parent = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_mymuse.category';

	/**
	 * The category that applies.
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $_category = null;

	/**
	 * The list of other newfeed categories.
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_categories = null;

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'modified', 'a.modified',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'author', 'a.author',
				'price', 'a.price',
				'product_discount', 'a.product_discount',
				'images', 'a.images',
				'urls', 'a.urls',
				'sales', 's.sales',
				'product_made_date', 'a.product_made_date'

			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initiliase variables.
		$app	= JFactory::getApplication('site');
		$pk		= JRequest::getInt('id');

		$this->setState('category.id', $pk);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params 	= MyMuseHelper::getParams();
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive()) {
			$menuParams->loadString($menu->params);
		}

		$params = clone $params;
		$params->merge($menuParams);

		$this->setState('params', $params);
		$user		= JFactory::getUser();
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		if ((!$user->authorise('core.edit.state', 'com_mymuse')) &&  (!$user->authorise('core.edit', 'com_mymuse'))){
			// limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
			// Filter by start and end dates.
			$nullDate = $db->Quote($db->getNullDate());
			$nowDate = $db->Quote(JFactory::getDate()->toSQL());

			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
			$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}
		else {
			$this->setState('filter.published', array(0, 1, 2));
		}


		$this->setState('filter.access', true);


		// Optional filter text
		$this->setState('list.filter', JRequest::getString('filter-search'));

		// filter.order
		$itemid = JRequest::getInt('id', 0) . ':' . JRequest::getInt('Itemid', 0);
		$orderCol = $app->getUserStateFromRequest('com_mymuse.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_mymuse.category.list.' . $itemid . '.filter_order_Dir',
			'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$this->setState('list.start', JRequest::getVar('limitstart', 0, '', 'int'));

		// set limit for query. If list, use parameter. If blog, add blog parameters for limit.
		if ((JRequest::getCmd('layout') == 'blog') || $params->get('layout_type') == 'blog') {
			$limit = $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links');
			$this->setState('list.links', $params->get('num_links'));
		}
		else {
			$limit = $app->getUserStateFromRequest('com_mymuse.category.list.' . $itemid . '.limit', 'limit', $params->get('display_num'));
		}

		$this->setState('list.limit', $limit);

		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');
		if ($showSubcategories) {
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$this->setState('filter.subcategories', true);
		}



		$this->setState('filter.language', $app->getLanguageFilter());

		$this->setState('layout', JRequest::getCmd('layout'));

	}

	/**
	 * Get the products in the category
	 *
	 * @return	mixed	An array of products or false if an error occurs.
	 * @since	1.5
	 */
	function getItems()
	{
		$params = $this->getState()->get('params');
		$limit = $this->getState('list.limit');
		$app	= JFactory::getApplication('site');
		$app	= JFactory::getApplication('site');
		$itemid = JRequest::getInt('id', 0) . ':' . JRequest::getInt('Itemid', 0);
		
		if ($this->_products === null && $category = $this->getCategory()) {
			$model = JModelList::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));
			$model->setState('params', $params);
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', $this->getState('filter.published'));
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));
			$ordering = $this->getState('list.ordering');
			
			$orderCol = $app->getUserStateFromRequest('com_mymuse.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
			if (!in_array($orderCol, $this->filter_fields)) {
				$model->setState('list.ordering', ProductHelperQuery::orderbySecondary($params->get('orderby_sec', 'rdate'), $params->get('order_date')));
			}else{
				$model->setState('list.ordering',$orderCol);
				
			}
			
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));
			// filter.subcategories indicates whether to include products from subcategories in the list or blog
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));	
			$model->setState('filter.max_category_levels', $this->getState('filter.max_category_levels'));
			$model->setState('list.links', $this->getState('list.links'));

			if ($limit >= 0) {
				$this->_products = $model->getItems();

				if ($this->_products  === false) {
					$this->setError($model->getError());
				}
			}
			else {
				$this->_products =array();
			}

			$this->_pagination = $model->getPagination();
		}

		return $this->_products;
	}

	/**
	 * Build the orderby for the query
	 *
	 * @return	string	$orderby portion of query
	 * @since	1.5
	 */
	protected function _buildContentOrderBy()
	{
		$app		= JFactory::getApplication('site');
		$db			= $this->getDbo();
		$params		= $this->state->params;
		$itemid		= JRequest::getInt('id', 0) . ':' . JRequest::getInt('Itemid', 0);
		$orderCol	= $app->getUserStateFromRequest('com_mymuse.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$orderDirn	= $app->getUserStateFromRequest('com_mymuse.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$orderby	= ' ';

		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = null;
		}

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC', ''))) {
			$orderDirn = 'ASC';
		}

		if ($orderCol && $orderDirn) {
			$orderby .= $db->escape($orderCol) . ' ' . $db->escape($orderDirn) . ', ';
		}

		$productOrderby		= $params->get('orderby_sec', 'rdate');
		$productOrderDate	= $params->get('order_date');
		$categoryOrderby	= $params->def('orderby_pri', '');
		$secondary			= ProductHelperQuery::orderbySecondary($productOrderby, $productOrderDate) . ', ';
		$primary			= ProductHelperQuery::orderbyPrimary($categoryOrderby);

		
		$orderby .= $db->escape($primary) . ' ' . $db->escape($secondary) . ' a.created ';

		return $orderby;
	}

	public function getPagination()
	{
		if (empty($this->_pagination)) {
			return null;
		}
		return $this->_pagination;
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @param	int		An optional ID
	 *
	 * @return	object
	 * @since	1.5
	 */
	public function getCategory()
	{
		if (!is_object($this->_item)) {
			if( isset( $this->state->params ) ) {
				$cparams = $this->state->params;
				$options = array();
				$options['countItems'] = $cparams->get('show_cat_num_articles', 1) || !$cparams->get('show_empty_categories_cat', 0);
			}
			else {
				$options['countItems'] = 0;
			}

			$categories = JCategories::getInstance('Mymuse', $options);
			$this->_item = $categories->get($this->getState('category.id', '0'));
			
			$params = new JRegistry($this->_item->params);
			$params->merge( $cparams );

			// Compute selected asset permissions.
			if (is_object($this->_item)) {
				$user	= JFactory::getUser();
				$userId	= $user->get('id');
				$asset	= 'com_mymuse.category.'.$this->_item->id;

				// Check general create permission.
				if ($user->authorise('core.create', $asset)) {
					$this->_item->getParams()->set('access-create', true);
				}

				// TODO: Why aren't we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent = false;

				if ($this->_item->getParent()) {
					$this->_parent = $this->_item->getParent();
				}

				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling = $this->_item->getSibling(false);
			}
			else {
				$this->_children = false;
				$this->_parent = false;
			}
		}

		return $this->_item;
	}

	/**
	 * Get the parent categorie.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	public function getParent()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_parent;
	}

	/**
	 * Get the left sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getLeftSibling()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_leftsibling;
	}

	/**
	 * Get the right sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getRightSibling()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getChildren()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}
		$db = JFactory::getDBO();

		// Order subcategories
		if (sizeof($this->_children)) {
			$params = $this->getState()->get('params');
			if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha') {
				jimport('joomla.utilities.arrayhelper');
				JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : -1);
			}
			
			$nullDate	= $db->Quote($db->getNullDate());
			$nowDate	= $db->Quote(JFactory::getDate()->toSql());
			foreach($this->_children as $child){
				$query = "SELECT count(*) as total from #__mymuse_product as p 
				LEFT JOIN #__mymuse_product_category_xref as x
				ON p.id=x.product_id 
				WHERE 
				x.catid=".$child->id." AND
				(p.publish_up = ".$nullDate." OR p.publish_up <= ".$nowDate.")
				AND (p.publish_down = ".$nullDate." OR p.publish_down >= ".$nowDate.")
				AND p.parentid=0 
				";
			//echo $query."<br />";
				$db->setQuery($query);
				$total = $db->loadResult();
				$child->product_total = $total;
			
			}
			
		}
		
		return $this->_children;
	}


    



	/**
	 * Method to get child category data for the current category
	 *
	 */
	function getChildcats()
	{
		// Initialize some variables
		$user	=& JFactory::getUser();

		// Load the Category data
		if ($this->_loadCategory() && $this->_loadChildcats())
		{
			// Make sure the category is published
			if (!$this->_category->published)
			{
				JError::raiseError(404, JText::_("MYMUSE_RESOURCE_NOT_FOUND"));
				return false;
			}

			// check whether category access level allows access
			if ($this->_category->access > $user->get('aid', 0))
			{
				JError::raiseError(403, JText::_("MYMUSE_ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_childcats;
	}



	/**
	 * Method to load child category data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadChildcats()
	{
		global $mainframe, $params;

		if (empty($this->_category))
		{
			return false; // TODO: set error -- can't get products when we don't know the category
		}

		// Lets load the products if they don't already exist
		if (empty($this->_childcats))
		{
			$user	 =& JFactory::getUser();



			$noauth	 	= !$params->get('show_noauth');
			$gid	 	= (int) $user->get('aid', 0);
			$now	 	= $mainframe->get('requestTime');
			$nullDate 	= $this->_db->getNullDate();
			$section	= $this->_category->section;

			// Get the parameters of the active menu item
			$menu	=& JSite::getMenu();
			$item   = $menu->getActive();
			$params	=& $menu->getParams($item->id);

			if ($user->authorize('com_mymuse', 'edit', 'content', 'all'))
			{
				$xwhere = '';
				$xwhere2 = ' AND b.state >= 0';
			}
			else
			{
				$xwhere = ' AND c.published = 1';
				$xwhere2 = ' AND b.state = 1' .
						' AND ( publish_up = '.$this->_db->Quote($nullDate).' OR publish_up <= '.$this->_db->Quote($now).' )' .
						' AND ( publish_down = '.$this->_db->Quote($nullDate).' OR publish_down >= '.$this->_db->Quote($now).' )';
			}

			// show/hide empty categories
			$empty = null;
			if (!$params->get('empty_cat'))
			{
				$empty = ' HAVING COUNT( b.id ) > 0';
			}

			// Get the list of sibling categories [categories with the same parent]
			$query = 'SELECT c.*, COUNT( b.id ) AS numitems' .
					' FROM #__mymuse_categories AS c' .
					' LEFT JOIN #__mymuse_product_category_xref AS cxref ON cxref.catid = c.id' .
					' LEFT JOIN #__mymuse_product AS b ON b.id = cxref.product_id' .

	
					$xwhere2.
					($noauth ? ' AND b.access <= '. (int) $gid : '') .
					' WHERE c.parent_id= '. $this->_db->Quote($this->_category->id).
					$xwhere.
					($noauth ? ' AND c.access <= '. (int) $gid : '').
					' GROUP BY c.id'.$empty.
					' ORDER BY c.ordering';
			$this->_db->setQuery($query);

            $rows = $this->_db->loadObjectList();
            foreach($rows as $row){
                $row->url = myMuseHelperRoute::getCategoryRoute($row->id, $section, $params);
            }

			$this->_childcats = $rows;
			
		}
		return true;
	}
    
}
