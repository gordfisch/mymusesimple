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
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Mymuse records.
 */
class MymuseModeltracks extends JModelList
{

 
  	/**
  	* stores _parent_product
  	*
  	* @var array
  	*/
  	var $_parent_product = null;
  	
  	/**
  	* stores _parentid
  	*
  	* @var array
  	*/
  	var $_parentid = null;

  	/**
  	* stores _items
  	*
  	* @var array
  	*/
  	var $_items = null;
  	

        
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
				'title', 'a.title',
				'alias', 'a.alias',
				'product_id',
				'product_title', 'p.title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 
				'category_title',
				'artistid', 'a.artistid',
				'product_sku', 'a.product_sku',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'modified', 'a.modified',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'published', 'a.published',
				'p.published'
			);
		}

        parent::__construct($config);
    }


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = 'a.id', $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);
		
		$category_id = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '', 'string');
		$this->setState('filter.category_id', $category_id);
		
		$artist_id = $app->getUserStateFromRequest($this->context.'.filter.artist_id', 'filter_artist_id', '', 'string');
		$this->setState('filter.artist_id', $artist_id);
		
		$access = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', '', 'string');
		$this->setState('filter.access', $access);
		
		$featured = $app->getUserStateFromRequest($this->context.'.filter.featured', 'filter_featured', '', 'string');
		$this->setState('filter.featured', $featured);

		$product_id = $app->getUserStateFromRequest('com_mymuse.product_id', 'product_id', '', 'int');
		if(!$product_id){
			$product_id = $app->getUserStateFromRequest($this->context.'.filter.product_id', 'filter_product_id', '', 'int');
		}
		$this->setState('filter.product_id', $product_id);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mymuse');
		$this->setState('params', $params);
		
		$authorId = $app->getUserStateFromRequest($this->context.'.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);
		
		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState($ordering, $direction);


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
		$id.= ':' . $this->getState('filter.state');

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
		$input = JFactory::getApplication()->input;

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__mymuse_track` AS a');

		// Join over the product
		$query->select('p.title AS product_title');
		$query->join('LEFT', '#__mymuse_product AS p ON p.id = a.product_id');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');


		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int) $access);
		}

		// Filter by featured.
		if ($featured = $this->getState('filter.featured')) {
			if($featured == "-1"){ $featured = 0;}
			$query->where("a.featured = '" . $featured."'");
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Filter by published 
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$cat_tbl = JTable::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= '.(int) $lft);
			$query->where('c.rgt <= '.(int) $rgt);
		}
		elseif (is_array($categoryId)) {
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('a.catid IN ('.$categoryId.')');
		}

		// Filter by a single or group of artists.
		$baselevel = 1;
		$artistId = $this->getState('filter.artist_id');
		if (is_numeric($artistId)) {
			$art_tbl = JTable::getInstance('Category', 'JTable');
			$art_tbl->load($categoryId);
			$rgt = $art_tbl->rgt;
			$lft = $art_tbl->lft;
			$baselevel = (int) $art_tbl->level;
			$query->where('art.lft >= '.(int) $lft);
			$query->where('art.rgt <= '.(int) $rgt);
		}
		elseif (is_array($artistId)) {
			JArrayHelper::toInteger($artistId);
			$artistId = implode(',', $artistId);
			$query->where('a.artistid IN ('.$artistId.')');
		}
		

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		if (is_numeric($authorId)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by '.$type.(int) $authorId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->escape(substr($search, 7), true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(
						a.title LIKE '.$search.' 
						OR p.title LIKE '.$search.'
						OR a.product_sku LIKE '.$search.'
						)');
			}
		}


		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = '.$db->quote($language));
		}
		
		// filter on product
		if ($product_id = $this->getState('filter.product_id')) {
			$query->where("a.product_id = $product_id");
		}
		

		
		//allfiles??
		$allfiles = $this->getState('filter.allfiles', '');
		if (is_int($allfiles)) {
			$query->where('a.allfiles = '.$allfiles);
		}
		
		

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn	= $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
	//echo $db->replacePrefix((string) $query);
		return $query;
	}
	
	/**
	 * get Items
	 *
	 * @return	objects or 0
	 * @since	3.5
	 */
	public function getItems() {
		if(!$this->_items){
			$this->_items = parent::getItems();
		}
		foreach($this->_items as $item){
			$item->track = json_decode($item->track);

		}
		return $this->_items;
	}

	/**
	 * get parent from product_id
	 *
	 * @return	object or 0
	 * @since	3.5
	 */
	public function getParent() {
		$db = $this->getDbo();
		$input = JFactory::getApplication()->input;
		$product_id = $input->get('product_id', 0);

		if($product_id){
			$query = "SELECT * from #__mymuse_product WHERE id='$product_id'";
			$db->setQuery($query);
			return $db->loadObject();
		}else{
			return (object) array('id' => 0, 'title'=> JText::_('COM_MYMUSE_NO_PRODUCT_SELECTED'));
		}
	}

	/**
	 * Build a list of authors
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	public function getAuthors() {
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__mymuse_track AS c ON c.created_by = u.id');
		$query->group('u.id, u.name');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
	}
	
	/**
	 * Build a list of featured
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	public function getFeatured() {
		
		$u = new stdClass;
		$u->value = -1;
		$u->text = JText::_('COM_MYMUSE_UNFEATURED');
		$f = new stdClass;
		$f->value = 1;
		$f->text = JText::_('COM_MYMUSE_FEATURED');
		$res = array($u,$f);
	
		// Return the result
		return $res;
	}
	
	/**
	* Method to remove a track
	*
	* @access public
	* @return boolean True on success
	
	*/
	function delete($cid = array())
	{
		global $params;;
        $result = false;
        if (count( $cid ))
        {
            JArrayHelper::toInteger($cid);
            $cids = implode( ',', $cid );
         

            $query = 'DELETE FROM #__mymuse_track'
            . ' WHERE id IN ( '.$cids.' )';
            $this->_db->setQuery( $query );
            if(!$this->_db->execute()) {
                $this->setError($this->_db->getErrorMsg());
            	return false;
            }
        }
        return true;
	}

    
}
