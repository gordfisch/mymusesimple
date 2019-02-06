<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Mymuse model.
 */
class MymuseModelproduct extends JModelAdmin
{
	/**
	 * @var		object	The parent object
	 * @since	1.6
	 */
	protected $_parent = null;
	
	/**
	 * @var		product(item) object
	 * @since	1.6
	 */
	protected $_item = null;
	
	/**
	 * @var		array of product(track) objects
	 * @since	1.6
	 */
	protected $_tracks = null;
	
	/**
	 * @var		array of product(item) objects
	 * @since	1.6
	 */
	protected $_items = null;
	
	/**
	 * @var		object
	 * @since	1.6
	 */
	protected $_trackPaginition = null;
	
	/**
	 * @var		object
	 * @since	1.6
	 */
	protected $_itemPaginition = null;
	
	/**
	 * @var		array
	 */
	protected $filter_fields = null;
	
	/**
	 * @var		array
	 */
	protected $_attribute_skus = null;
	
	/**
	 * @var		object
	 */
	protected $_params = null;
	
	/**
	 * @var		array
	 */
	protected $_previews = null;
	
	/**
	 * @var		object
	 */
	protected $_s3 = null;
	
	
	
	
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		$config = array();
		$config['event_after_delete'] ='onMymuseAfterDelete';
		$config['event_after_save'] = 'onMymuseAfterSave';
		$config['event_before_delete'] = 'onMymuseBeforeDelete';
		$config['event_before_save'] = 'onMymuseBeforeSave';
		$config['event_change_state'] = 'onMymuseChangeState';
		
		$this->filter_fields = array(
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
				'author', 'a.author'
		);
		
		$this->_params 			= MyMuseHelper::getParams();
		
		parent::__construct($config);
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
	public function getTable($type = 'Product', $prefix = 'MymuseTable', $config = array())
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
		$form = $this->loadForm('com_mymuse.product', 'product', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_mymuse.edit.product.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}
		if(isset($data->product_made_date) && $data->product_made_date == "0000-00-00"){
			$data->product_made_date = '';
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
    public function getItem($pk = null)
	{
		if(!$this->_item){
			$input = JFactory::getApplication()->input;
			$task = $input->get('task','');
			$parentid= $input->get('parentid','');
			$id = $input->get('id','');
			
			if($task == "addfile" || $task == "additem" || $task == "new_allfiles"){
				$pk = 0;
				$input->set('id',0);
			}
			
			if ($item = parent::getItem($pk)) {
				//print_pre($item); exit; 
				// Convert the attribs field to an array.
				$registry = new JRegistry;
				$registry->loadString($item->attribs);
				$item->attribs = $registry->toArray();

				// Convert the metadata field to an array.
				$registry = new JRegistry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();

				$item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;
					
				if($parentid && $parentid != $id){
					$item->parentid = $parentid;
				}
					
				if($task == "new_allfiles"){
					$item->product_allfiles = 1;
				}
				if($item->parentid){
					$q = "SELECT * FROM #__mymuse_product WHERE id='".$item->parentid."'";
					$this->_db->setQuery($q);
					$this->_parent = $this->_db->loadObject();
					$item->parent = $this->_parent;
					$item->catid = $item->parent->catid;
				}else{
					//set the parent id for the tracks and items
					$mainframe = JFactory::getApplication();
					$parentid= $mainframe->getUserStateFromRequest( "com_mymuse.parentid", 'id', 0 );
				}
				$item->flash_type = '';
				
				$jason = json_decode($item->file_name);
				if(is_array($jason)){
					$item->file_name = $jason;
				}elseif($item->file_name != ''){
					$jason = (object) array('file_name' => $item->file_name);
					$item->file_name = array();
					$item->file_name[] = $jason;
				}
				
			}
		
			$this->_item = $item;

		}
		return $this->_item;
	}


   /**
     * Get the tracks for the product
     *
     * @return	mixed	An array of products or false if an error occurs.
     * @since	1.5
     */
    function getTracks()
    {

    	$app 				= JFactory::getApplication();
    	$input 				= $app->input;
    	$option 			= $input->get('option','com_mymuse');
    	$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
    	$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
    	$this->setState('file.ordering', $filter_order);
    	$this->setState('file.direction', $filter_order_Dir);
    	$this->setState('list.ordering', $filter_order);
    	$this->setState('list.direction', $filter_order_Dir);
    	$table = $this->getTable('product','MymuseTable');

    	$limit 				= $this->getState('list.limit');
    	$id 				= $input->get('id');
    	

    	$root = JPATH_ROOT;
  
    	if ($this->_tracks === null && $product = $this->getItem()) {
    		JLoader::import( 'products', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_mymuse' . DS . 'models' );
    		$model = JModelLegacy::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));

    		//$model->setState('filter.category_id', $category->id);
    		$model->setState('filter.published', $this->getState('filter.published'));
    		$model->setState('filter.access', $this->getState('filter.access'));
    		$model->setState('filter.language', $this->getState('filter.language'));
    		$model->setState('list.ordering', $this->getState('file.ordering'));
    		$model->setState('list.start', $this->getState('list.start'));
    		$model->setState('list.limit', $limit);
    		$model->setState('list.direction', $this->getState('file.direction'));
    		$model->setState('list.filter', $this->getState('list.filter'));
    		// filter.subcategories indicates whether to include articles from subcategories in the list or blog
    		$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
    		$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
    		$model->setState('list.links', $this->getState('list.links'));
    
    		$model->setState('filter.downloadable', 1);
    		$model->setState('filter.parentid', $product->id);


    		if ($limit >= 0) {
    			$this->_tracks = $model->getItems();

    			if ($this->_tracks  === false) {
    				$this->setError($model->getError());
    			}
    		}
    		else {
    			$this->_track =array();
    		}
    
    		$this->_trackPagination = $model->getPagination();
    	}

  
    	return $this->_tracks;
    }
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mymuse_product');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
	
	
	/**
     * Method to set the product lists
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {
    	global $option;
    	$app 				= JFactory::getApplication();
    	$input 				= $app->input;
    	$id 				= $input->get('id', 0);



		$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
		$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );


		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		$query = "SELECT id,title FROM #__categories WHERE extension='com_mymuse'";
		$this->_db->setQuery($query);
		$lists['other_cats'] = $this->_db->loadObjectList();
		

		return $lists;
    }
    
     /**
     * Method to get the file lists.
     *
     * @access    public
     * @return    array
     */
    public function getFileLists()
    {
    	$input = JFactory::getApplication()->input;
    	$parentid = $this->_item->parentid;
    	jimport('joomla.filesystem.file');

 		// file lists for albums
 		$artist_alias = MyMuseHelper::getArtistAlias($parentid,1);
		$album_alias = MyMuseHelper::getAlbumAlias($parentid,1);

		$site_url = MyMuseHelper::getSiteUrl($parentid,1);
		$site_path = MyMuseHelper::getSitePath($parentid,1);
		$download_path = MyMuseHelper::getdownloadPath($parentid,1);

		$files = array();

		
		// get the preview lists
		if($this->_params->get('my_use_s3')){

			try{
				$result = $this->_s3->listObjects([
					'Bucket' => $this->_params->get('my_preview_dir'), 
					'Prefix' => $site_path
				]);
			} catch (S3Exception $e) {

				$this->setError( 'S3 Error: '.$this->_s3->getError() );
				$application->enqueueMessage('S3 Error: '.$this->_s3->getError() , 'error');
				return false;
			}
			$everything = $result['Contents'];
			$folder = trim($site_path,'/');
			$dirLength = strlen($folder);
			if(count($everything)) {
				foreach($everything as $info) {
						// print_pre($info); exit;
					if (array_key_exists ( 'Size', $info ) && (substr ( $info ['Key'], - 1 ) != '/')) {
						$path = $info ['Key'];
						if (substr ( $info ['Key'], 0, $dirLength ) == $folder) {
							$path = substr ( $info ['Key'], $dirLength );
						}
						$path = trim ( $path, '/' );
						$files [] = $path;
					}
				}
			}
			if(1 == $this->_params->get('my_previews_in_one_dir')){
				$new_files = array();
				foreach($files as $file){
					$pos = strpos($file, '/');
					if($pos === false){
						$new_files[] = $file;
					}
				}
				$files = $new_files;
			}

		}else{
			if(!JFolder::exists($site_path)){
				JFolder::create($site_path);
			}
			$files = JFolder::files( $site_path );
		}

		$previews 	= array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach ( $files as $file ) {
				$previews[] = JHTML::_('select.option',  $file );
		}
		$lists['previews'] = JHTML::_('select.genericlist',  $previews, 'file_preview', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview );

		
		
		// get the download tracks lists
		$files = array();


		$directory = rtrim(MyMuseHelper::getDownloadPath($parentid,'1'), '/');

		if($this->_params->get('my_download_dir_format')){
			//by format. We only have the one mp3

			if(!JFolder::exists( $directory )){
				JFolder::create( $directory );
			}
			$files = JFolder::files( $directory );
		}
		
		$myfiles = array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach($files as $file){
				$myfiles[] = JHTML::_('select.option',  $file, stripslashes($file) );
		}
		
		$current = $this->_item->file_name;

		$i = 0;
		if($current){
			for($i = 0; $i < count($current); $i++){
				$lists['select_file'][$i] = JHTML::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text', $current[$i]->file_name);
			}
		}else{
			$lists['select_file'][0] = JHTML::_('select.genericlist',  $myfiles, "select_file[0]", 'class="inputbox" size="1" ', 'value', 'text','');
		}
		for($i = $i++; $i < 9; $i++){
			$lists['select_file'][$i] = JHTML::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text','');
		}
		
		// for display purposes
		$lists['preview_dir'] = $site_path;
		$lists['download_dir'] = $download_path;

	
		return $lists;
    	
    }
    

    
	/**
	 * Method to toggle the featured setting of products.
	 *
	 * @param	array	The ids of the items to toggle.
	 * @param	int		The value to toggle to.
	 *
	 * @return	boolean	True on success.
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks)) {
			$this->setError(JText::_('MYMUSE_NO_ITEM_SELECTED'));
			return false;
		}

		try {
			$db = $this->getDbo();

			$db->setQuery(
				'UPDATE #__mymuse_product AS a' .
				' SET featured = '.(int) $value.
				' WHERE id IN ('.implode(',', $pks).')'
			);
			if (!$db->execute()) {
				throw new Exception($db->getErrorMsg());
			} 

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}


		$this->cleanCache();

		return true;
	}
	


	

}
