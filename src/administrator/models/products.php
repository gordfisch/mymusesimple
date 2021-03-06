<?php
/**
 * @version     $Id: products.php 1919 2017-10-23 14:53:26Z gfisch $
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
class MymuseModelproducts extends JModelList
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
  	* stores _attribute_skus
  	*
  	* @var array
  	*/
  	var $_attribute_skus = null;
  	
  	/**
  	* stores _attributes
  	*
  	* @var array
  	*/
  	var $_attributes = null;
  	
        
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
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'artistid', 'a.artistid',
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
				'author', 'a.author'
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

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mymuse');
		$this->setState('params', $params);
		
		$authorId = $app->getUserStateFromRequest($this->context.'.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);
		
		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language');
		$this->setState('filter.language', $language);

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
		$query->from('`#__mymuse_product` AS a');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
		
		// Join over the artist categories.
		$query->select('art.title AS artist_title');
		$query->join('LEFT', '#__categories AS art ON art.id = a.artistid');

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

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
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
		
		// Filter on the level.
		if ($level = $this->getState('filter.level')) {
			$query->where('c.level <= '.((int) $level + (int) $baselevel - 1));
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
						OR a.alias LIKE '.$search.'
						OR a.product_sku LIKE '.$search.'
						)');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = '.$db->quote($language));
		}
		
		// It must be a parent?
		if ($parentid = $this->getState('filter.parentid')) {
			$query->where("a.parentid = $parentid");
		}else{
			$query->where('a.parentid = 0');
		}
		
		//downloadable??
		if ($downloadable = $this->getState('filter.downloadable')) {
			$query->where('a.product_downloadable = 1');
		}
		
		//allfiles??
		$allfiles = $this->getState('filter.allfiles', '');
		if (is_int($allfiles)) {
			$query->where('a.product_allfiles = '.$allfiles);
		}
		
		//physical??
		if ($physical = $this->getState('filter.physical')) {
			$query->where('a.product_physical = 1');
		}
		

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
		    $query->order($orderCol.' '.$orderDirn);
        }

		return $query;
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
		$query->join('INNER', '#__content AS c ON c.created_by = u.id');
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
	* Method to remove a product
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
         
            // Check if it has children!
            foreach ($cid as $id){
            	$query = "SELECT title FROM #__mymuse_product WHERE parentid='$id'";
            	$this->_db->setQuery($query);
            	$row = $this->_db->loadObject();
            	
            	if($row && $row->title){
            		$this->setError(JText::_( 'MYMUSE_PRODUCT_HAS_CHILDREN' ).$row->title );
            		return false;
            	}
            }
      
            // Let's get rid of associated entries

            // first the product_category_xref
            $query = "DELETE FROM #__mymuse_product_category_xref WHERE"
            . " product_id IN (". $cids  .")";
            $this->_db->setQuery($query);
            if (!$this->_db->execute())
            {
            	$this->setError($this->_db->getErrorMsg());
            	return false;
            }

            // next the product_recommend_xref
            $query = "DELETE FROM #__mymuse_product_recommend_xref WHERE"
            . " product_id IN (". $cids  .")";
            $this->_db->setQuery($query);
            if (!$this->_db->execute())
            {
            	$this->setError($this->_db->getErrorMsg());
            	return false;
            }

            
            // finally the product
            $query = 'DELETE FROM #__mymuse_product'
            . ' WHERE id IN ( '.$cids.' )';
            $this->_db->setQuery( $query );
            if(!$this->_db->execute()) {
                $this->setError($this->_db->getErrorMsg());
            	return false;
            }
        }
        return true;
	}
	
	/**
	 * check_products
	 *
	 *
	 *  @return object
	 */
	
	function getCheck()
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'mymuse.php' );
		$params = MyMuseHelper::getParams();
		$products = $this->getItems();
		$missing = array();

		foreach ($products as $product){
			
			$model = new MymuseModelproducts;
			$model->setState('filter.downloadable', 1);
			$model->setState('filter.allfiles', 0);
			$model->setState('filter.parentid', $product->id);
			$product->items = $model->getItems();
			$table 			= JTable::getInstance('Product', 'MymuseTable', array());
			
			foreach($product->items as $item){
				$filenames = array();
				$previews = array();
				$path = MyMuseHelper::getDownloadPath($product->id,1);
				$preview_path = MyMuseHelper::getSitePath($product->id,1);
				
				if($item->file_preview != ''){
					$previews[] = $item->file_preview;
				}
				if($item->file_preview_2 != ''){
					$previews[] = $item->file_preview_2;
				}
				if($item->file_preview_3 != ''){
					$previews[] = $item->file_preview_3;
				}
				if($item->file_preview_4 != ''){
					$previews[] = $item->file_preview_4;
				}
				foreach($previews as $p){
					$full_path = $preview_path.$p;
					if($table->fileExists($full_path)){
						$item->preview[$p]['class'] = "alert alert-success";
						$item->preview[$p]['result'] = JText::_('MYMUSE_FOUND');
					}else{
						$item->preview[$p]['class'] = "alert alert-danger";
						$item->preview[$p]['result'] = JText::_('MYMUSE_NOT_FOUND');
						$missing[] = $full_path;
					}

				}
				$jason = json_decode($item->file_name);
				if(is_array($jason)){
					foreach($jason as $j){
						if($params->get('my_encode_filenames')){
							$filenames[] = $j->file_alias;
						}else{
							$filenames[] = $j->file_name;
						}
					}
				}else{
					if($params->get('my_encode_filenames')){
						$filenames[] = $item->alias;
					}else{
						$filenames[] = $item->file_name;
					}
				}
				
				foreach($filenames as $f){
					$full_path = $path.$f;
					if($table->fileExists($full_path)){
						$item->download[$f]['class'] = "alert alert-success";
						$item->download[$f]['result'] = JText::_('MYMUSE_FOUND');
					}else{
						$item->download[$f]['class'] = "alert alert-danger";
						$item->download[$f]['result'] = JText::_('MYMUSE_NOT_FOUND');
						$missing[] = $full_path;
					}
				}
			}
		}
		$products['missing'] = $missing;
		return $products;
	
	}

    
}
