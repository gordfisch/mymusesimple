<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Mymuse records.
 */
class MymuseModelproductattributeskus extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
				'name','a.name',
            	'product_parent_id', 'p.product_parent_id',

            );
        }

        parent::__construct($config);
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

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
	   // Load the product filter.
	    $parentid = $app->getUserStateFromRequest("com_mymuse.parentid", 'parentid');
	    $this->setState('parentid', $parentid);
	    
		$product = $app->getUserStateFromRequest($this->context.'.filter.product', 'filter_product',$parentid);
		$this->setState('filter.product', $product);

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
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.product');

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
				'a.*'
			)
		);
		$query->select('p.id as product_parent_id');
		$query->select('p.title as product_title');
		
		$query->from('`#__mymuse_product_attribute_sku` AS a');
    

		$query->join('LEFT', '#__mymuse_product AS p ON p.id = a.product_parent_id');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                //$query->where('()');
			}
		}
		$product = $this->getState('filter.product');
		$query->where('p.id='.$product);
		
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering','a.name');
		$orderDirn	= $this->state->get('list.direction', 'asc');
        if ($orderCol && $orderDirn) {

		    $query->order($db->escape($orderCol.' '.$orderDirn));
        }

		return $query;
	}
	
	public function getLists()
	{
		$app = JFactory::getApplication();
		$lists['products'] 		= array();
		$db		= $this->getDbo();
		$query 	= "SELECT id as value,title as text FROM #__mymuse_product WHERE parentid='0'";
		$db->setQuery($query);
		$products = $db->loadObjectList();
		$product = $this->getState('filter.product');
		$lists['products'] = JHTML::_('select.genericlist',  $products, 'filter_product', 'class="inputbox" size="1" onchange="adminForm.submit()"', 'value', 'text', $product );
		
		return $lists;
	}
	
	public function getParent()
	{
		$app = JFactory::getApplication();
		$parentid = $this->getState('filter.product');
		$db = JFactory::getDBO();
		$query = "SELECT * from #__mymuse_product WHERE id=$parentid";
		$db->setQuery($query);
		$parent = $db->loadObject();
		
		return $parent;
	}
	
	function delete()
	{
		$db		= $this->getDbo();
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		if(!isset($cid[0])){
			return false;
		}

		foreach($cid as $id){
			$query = "DELETE from #__mymuse_product_attribute WHERE product_attribute_sku_id=$id";
			$db->setQuery($query);
			$db->execute();
			
			$query = "DELETE from #__mymuse_product_attribute_sku WHERE id=$id";
			$db->setQuery($query);
			$db->execute();
		}
	}
	

}
