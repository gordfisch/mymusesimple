<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Mymuse model.
 */
class MymuseModelproduct extends JModelAdmin
{
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
		

		
		parent::__construct($config);
	}
	
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_MYMUSE';
	
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
	 * Returns a reference to the a Table object, always creating it.
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
			$task = JRequest::getVar('task','');
			$parentid= JRequest::getVar('parentid','');
			$id = JRequest::getVar('id','');
			if($task == "addfile" || $task == "additem"){
				$pk = 0;
			}
			if ($item = parent::getItem($pk)) {
				
				// Convert the params field to an array.
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
					
				if($item->parentid){
					$q = "SELECT * FROM #__mymuse_product WHERE id='".$item->parentid."'";
					$this->_db->setQuery($q);
					$this->_parent = $this->_db->loadObject();
					$item->parent = $this->_parent;
				}else{
					//set the parent id for the tracks and items
					$mainframe = JFactory::getApplication();
					$parentid= $mainframe->getUserStateFromRequest( "com_mymuse.parentid", 'id', 0 );
				}
				$item->flash_type = '';
			}
			$this->_item = $item;
	
		}
		return $this->_item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
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
    	$params 			= MyMuseHelper::getParams();
    	$id 				= JRequest::getVar('id', 0);

		
    	$filter_state 		= $app->getUserStateFromRequest( $option.'filter_state', 'filter_state', '', 'word' );
		$filter_catid 		= $app->getUserStateFromRequest( $option.'filter_catid', 'filter_catid', 0, 'int' );
		$filter_artistid 	= $app->getUserStateFromRequest( $option.'filter_artistid', 'filter_artistid', 0, 'int' );
		$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
		$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );

		$filter_item_order 		= $app->getUserStateFromRequest( $option.'filter_item_order', 'filter_item_order', 'a.ordering', 'cmd' );
		$filter_item_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_item_order_Dir', 'filter_item_order_Dir', 'asc', 'word' );
		
		$this->setState('file.ordering', $filter_order);
		$this->setState('file.direction', $filter_order_Dir);
		
		$this->setState('item.ordering', $filter_item_order);
		$this->setState('item.direction', $filter_item_order_Dir);
		
		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;
		$edit = JRequest::getVar('edit', 0);

		//other categories
		$selectedCats = array();
		if($id){
			$query = "SELECT * FROM #__mymuse_product_category_xref WHERE product_id=".$id;
			$this->_db->setQuery($query);
			$cats =  $this->_db->loadObjectList();
			if($cats){
				foreach($cats as $cat){
					$selectedCats[] = $cat->catid;
				}
			}
		}	
		
		$query = "SELECT id,title FROM #__categories WHERE extension='com_mymuse'";
		$this->_db->setQuery($query);
		$lists['other_cats'] = $this->_db->loadObjectList();
		
		
		// Items, Attributes, Files
		$lists['attributes'] 	= array();
		$lists['attribute_sku'] = array();
		$lists['items'] 		= array();
		$lists['files'] 		= array();
	
		//attributes
		$subtype				= JRequest::getVar('subtype', '');
		if($this->_item->parentid){
			//we want the parentid
			$pid = $this->_item->parentid;
		}else{
			$pid = $id;
		}
		$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE
			product_parent_id='.$pid.'
			ORDER BY ordering';

		$this->_db->setQuery($query);
		$lists['attribute_sku'] = $this->_db->loadObjectList();

		// items
		$query = "SELECT a.* from #__mymuse_product as a WHERE parentid=".$pid."
			AND product_downloadable=0 ORDER BY $filter_item_order $filter_item_order_Dir";
		$this->_db->setQuery($query);

		if($lists['items'] = $this->_db->loadObjectList()){

			foreach($lists['items'] as $item){
				foreach($lists['attribute_sku'] as $a_sku){
					$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$item->id.'
						AND product_attribute_sku_id='.$a_sku->id;
				
					$this->_db->setQuery($query);
					$item->attributes[$a_sku->name] = $this->_db->loadResult();
				}
				$query = 'SELECT * from #__mymuse_product_attribute WHERE product_id='.$item->id;
			
				$this->_db->setQuery($query);
				$lists['attributes'][$item->id] = $this->_db->loadObjectList();

			}
		}

		return $lists;
    }
    
    /**
     * Get the tracks for the product
     *
     * @return	mixed	An array of products or false if an error occurs.
     * @since	1.5
     */
    function getTracks()
    {

    	$app = JFactory::getApplication();
    	$option = JRequest::getVar('option','com_mymuse');
    	$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
    	$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
    	$this->setState('file.ordering', $filter_order);
    	$this->setState('file.direction', $filter_order_Dir);

    	$params = MyMuseHelper::getParams();

    	$limit = $this->getState('list.limit');
    	$id = JRequest::getVar('id');
    	$artist_alias = MyMuseHelper::getArtistAlias($this->_item->catid);
    	$album_alias = MyMuseHelper::getAlbumAlias($this->_item->id);

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

    	 
    	if(count($this->_tracks)){
    		 
    		//need count minus any alltracks minus any missing files
    		$i = 0;
    		foreach($this->_tracks as $track){

    			if($track->product_allfiles){
    				continue;
    			}
    			
    			//main file
    			if($params->get('my_encode_filenames')){
    				$name = $track->title_alias;
    			}else{
    				$name = $track->file_name;
    			}
    			$full_filename = $filename = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
    			if(!file_exists($full_filename)){
    				//try with the root
    				$full_filename = JPATH_ROOT.DS.$params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
    			}
    			if(!file_exists($full_filename)){
    				//echo "not found ".$filename." <br />";
    			}elseif(preg_match("#$root#",$params->get('my_download_dir'))){
    				$i++;
    			}else{
    				//echo "$root ".$params->get('my_download_dir')."<br />";
    			}
    			//preview
    			$preview_full_filename = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview;
    			if(!file_exists($preview_full_filename)){
    				//echo "not found ".$preview_full_filename."<br />";;
    			}else{
    				$i++;
    			}


    		}
    		$count = $i;

    		
    		JPluginHelper::importPlugin( 'mymuse' );
    		$dispatcher		= JDispatcher::getInstance();
    		$i = 0;
    		$download_dir = $params->get('my_download_dir');

    		if(stristr(PHP_OS, 'win')){
    			$root = str_replace("\\","/", $root);
    			$download_dir = str_replace("\\","/", $download_dir);
    		}

    		$i = 0;
    		foreach($this->_tracks as $track){
    			if($track->product_allfiles){
    				$track->stream = '';
    				$track->flash= '';
    				continue;
    			}
    			$flash = '';
    			
    			//Audio/Video or some horrid mix of both
    				
    			if($this->_item->flash_type != "mix"){
    				if($this->_item->flash_type == "audio" && $track->file_type == "video"){
    					//oh christ it's a mix
    					$this->_item->flash_type = "mix";
    					$track->flash_type = "mix";
    				}elseif($this->_item->flash_type == "video" && $track->file_type == "audio"){
    					//oh christ it's a mix
    					$this->_item->flash_type = "mix";
    					$track->flash_type = "mix";
    				}else{
    					$this->_item->flash_type = $track->file_type;
    					$track->flash_type = $track->file_type;
    				}
    			}else{
    				$track->flash_type = "mix";
    			}
    			
    				
    			//make flash for admin to listen to Preview
    			if($track->file_preview){
    				$ext = MyMuseHelper::getExt($track->file_preview);

    				$site_url = preg_replace("#administrator/#","",JURI::base());
    				$preview_url= $site_url.str_replace($root,'',$params->get('my_preview_dir'));
    				$track->path = $preview_url."/".$artist_alias."/".$album_alias."/".$track->file_preview;

    				$full_filename = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview;
    				$track->real_path = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview;
    				if($track->file_preview_2){
    					$track->real_path2 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview_2;
    					$track->path_2 = $preview_url."/".$artist_alias."/".$album_alias."/".$track->file_preview_2;
    				}
    				if($track->file_preview_3){
    					$track->real_path3 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview_3;
    					$track->path_3 = $preview_url."/".$artist_alias."/".$album_alias."/".$track->file_preview_3;

    				}
    				$track->flash_type = $track->file_type;

    				if(file_exists($full_filename)){
    					if($track->file_type == "video"){
    						//video
    							
    						$flash = '<!-- Begin Flash Preview Player -->';
    						$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$track, 'single',192, 256, $i, $count));
    						if(isset($results[0]) && $results[0] != ''){
    							$flash .= $results[0];
    						}
    						$flash .= '<!-- End Flash Preview Player -->';
    							
    					}elseif($track->file_type == "audio"){
    						//echo 'audio';
    						$flash = '<!-- Begin Flash Preview Player -->';
    						$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$track, 'single', 25, 200, $i, $count ) );
    						if(isset($results[0]) && $results[0] != ''){
    							$flash .= $results[0];
    						}
    						$flash .= '<!-- End Flash Preview Player -->';
    							
    					}

    					$i++;
    				}
    			}
    			$track->flash = $flash;

    			//make flash for admin to listen to Main File, we'll call it stream
    			$stream = "";
    			if($params->get('my_encode_filenames')){
    				$name = $track->title_alias;
    			}else{
    				$name = $track->file_name;
    			}

    			if($name){
    				$full_filename = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
    				if(!file_exists($full_filename)){
    					//try with the root
    					$full_filename = JPATH_ROOT.DS.$params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
    				}
    				//echo $full_filename."<br />";
    				if(!file_exists($full_filename)){
    					$stream = '';
    				}elseif( !$track->product_allfiles ){
    						
    					// see if it is inside the web root
    					//echo "root = $root<br />download_dir = $download_dir<br />";
    					if(preg_match("#$root#",$download_dir)){

    						$ext = MyMuseHelper::getExt($name);
    						$site_url = preg_replace("#administrator/#","",JURI::base());
    						$track->path = $site_url.str_replace($root,'',$download_dir);
    						$track->path .= "/".$artist_alias."/".$album_alias."/".$name;
    						$track->oldid = $track->id;
    						$track->id = "m".$track->id;
    						$track->file_preview_2 = '';
    						$track->file_preview_3 = '';

    						if($track->file_type == "video"){
    							//video

    							$stream  = '<!-- Begin Fulltrack Player -->';
    							$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$track, 'single',192, 256, $i, $count));
    							if(is_array($results) && $results[0] != ''){
    								$stream  .= $results[0];
    							}
    							$stream  .= '<!-- End Fulltrack Player -->';

    						}elseif($track->file_type == "audio"){
    							//audio
    							$stream  = '<!-- Begin Fulltrack Player -->';
    							$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$track, 'single', 25, 100, $i, $count ));
    							if(is_array($results) && isset($results[0]) && $results[0] != ''){
    								$stream  .= $results[0];
    							}
    							$stream  .= '<!-- End  Fulltrack Player -->';

    						}

    						$i++;
    						$track->id = $track->oldid;
    					}
    				}

    			}
    				
    			if(file_exists($params->get('my_download_dir').DS.'.htaccess')){
    				$stream = '';
    			}
    			$track->stream = $stream;

    		}
    		
    		// get main player, set to play first track
    		reset($this->_tracks);
    		$flash = '';
    		$audio = 0;
    		$video = 0;
    		$track = $this->_tracks[0];
    		if($track->file_preview){
    			if($track->file_type == "video" && !$video){
    				//movie
    				$flash .= '<!-- Begin VIDEO Player -->';
    				$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$track,'singleplayer') );
    				if(is_array($results) && $results[0] != ''){
    					$flash .= $results[0];
    				}
    				$flash .= '<!-- End Player -->';
    				$video = 1;

    			}elseif($track->file_type == "audio" && !$audio){
    				//audio
    				$flash .= '<!-- Begin AUDIO Player -->';
    				$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$track,'singleplayer') );

    				if(is_array($results) && isset($results[0]) && $results[0] != ''){
    					$flash .= $results[0];
    				}
    				$flash .= '<!-- End Player -->';
    				$audio = 1;
    			}
    			$this->_item->flash = $flash;
    			$this->_item->flash_id = $track->id;
    		}
    	}

    	return $this->_tracks;
    }
    
    function getTrackPagination()
    {
    	if (empty($this->_trackPagination)) {
    		return null;
    	}
    	return $this->_trackPagination;
    }
    
    
    /**
     * Get the items for the product
     *
     * @return	mixed	An array of products or false if an error occurs.
     * @since	1.5
     */
    function getItems()
    {
    	
    	$app = JFactory::getApplication();
    	$option = JRequest::getVar('option','com_mymuse');

    	$params = $this->getState()->get('params');
    	$limit = $this->getState('list.limit');
    	$id = JRequest::getVar('id');

    	$root = JPATH_ROOT.DS;
    
    	if ($this->_items === null && $product = $this->getItem()) {
    
    		$model = JModelLegacy::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));
    
    		//$model->setState('filter.category_id', $category->id);
    		$model->setState('filter.published', $this->getState('filter.published'));
    		$model->setState('filter.access', $this->getState('filter.access'));
    		$model->setState('filter.language', $this->getState('filter.language'));
    		$model->setState('list.ordering', $this->getState('item.ordering'));
    		$model->setState('list.start', $this->getState('list.start'));
    		$model->setState('list.limit', $limit);
    		$model->setState('list.direction', $this->getState('item.direction'));
    		$model->setState('list.filter', $this->getState('list.filter'));
    		// filter.subcategories indicates whether to include articles from subcategories in the list or blog
    		$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
    		$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
    		$model->setState('list.links', $this->getState('list.links'));
    
    		$model->setState('filter.downloadable', 0);
    		$model->setState('filter.physical', 1);
    		$model->setState('filter.parentid', $product->id);
    
    
    		if ($limit >= 0) {
    			$this->_items = $model->getItems();
    
    			if ($this->_items  === false) {
    				$this->setError($model->getError());
    			}
    		}
    		else {
    			$this->_items =array();
    		}
    
    		$this->_itemPagination = $model->getPagination();
    		
    		//get attributes
    		$db = JFactory::getDBO();
    		for($i = 0; $i<count($this->_items); $i++){
    		
    			if(!$this->_attribute_skus && $product->id){
    				$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE product_parent_id='.$product->id;
					$db->setQuery($query);
					$this->_attribute_skus = $db->loadObjectList();
    			}
    			$id = $this->_items[$i]->id;
    			if($this->_attribute_skus ){
    				foreach($this->_attribute_skus as $a_sku){
    					$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$id.'
    					AND product_attribute_sku_id='.$a_sku->id;
    					
    					$db->setQuery($query);
    					$this->_items[$i]->attributes[$a_sku->name] = $db->loadResult();
    				}
    			}
    		}
    	}
    	return $this->_items;
    }
    
    function getItemPagination()
    {
    	if (empty($this->_itemPagination)) {
    		return null;
    	}
    	return $this->_itemPagination;
    }
    
    /**
     * Method to set the file lists
     *
     * @access    public
     * @return    array
     */
    function getFileLists()
    {
    	$params =& MyMuseHelper::getParams();

 		// file lists for albums
 	
 		$artist_alias = MyMuseHelper::getArtistAlias($this->_parent->id,'1');
		$album_alias = MyMuseHelper::getAlbumAlias($this->_parent->id);

		jimport( 'joomla.filesystem.folder' );
		
		
		// get the preview lists
		$directory = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
        //create preview dir if not exists

        if( !file_exists($directory) ){
        	if(!JFolder::create($directory)){
        		$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").$directory);
        		return false;
        	}
        	if(!JFile::copy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        	$directory.DS."index.html")){
        		$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").$directory);
        	}
        }

		$files = JFolder::files( $directory );
		$previews 	= array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach ( $files as $file ) {
		   //if ( preg_match( "#mp3|mp4a#i", $file ) ) {
				$previews[] = JHTML::_('select.option',  $file );
			//}
		}
		$lists['previews'] = JHTML::_('select.genericlist',  $previews, 'file_preview', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview );
		$lists['previews_2'] = JHTML::_('select.genericlist',  $previews, 'file_preview_2', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview_2 );
		$lists['previews_3'] = JHTML::_('select.genericlist',  $previews, 'file_preview_3', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview_3 );
		
		// get the tracks lists
		//if(!$params->get('my_encode_filenames')){
			$directory = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias;
			$files = JFolder::files( $directory );
			$myfiles = array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );

			foreach($files as $file){
				$myfiles[] = JHTML::_('select.option',  $file, stripslashes($file) );
			}

			$current = $this->_item->file_name;
			$lists['select_file'] = JHTML::_('select.genericlist',  $myfiles, 'select_file', 'class="inputbox" size="1" ', 'value', 'text', $current);
		//}
		$lists['preview_dir'] = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
		$lists['download_dir'] = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias;
	
		return $lists;
    	
    }
    
	/**
	 * Method to toggle the featured setting of articles.
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
	
	/**
	 * ITEMS SECTION * ITEMS SECTION * ITEMS SECTION * ITEMS SECTION * ITEMS SECTION * ITEMS SECTION * ITEMS SECTION 
	 */

	/**
	 * checkAttributes. Set item attributes
	 * See if any attibutes have been set, if not return false
	 * 
	 * @return boolean
	 */
	function checkAttributes(){

		$pid	= JRequest::getVar( 'parentid', null, 'post', 'int' );
		if(!$pid){
			//see if we can get the parent from the item id
			if(isset($this->_id)){
				$query = "SELECT parentid from #__mymuse_product WHERE id=".$this->_id;
				$this->_db->setQuery($query);
				$pid = $this->_db->loadResult();
			}else{
				$this->setError(JText::_('MYMUSE_COULD_NOT_FIND_PARENT'));
            	return false;
			}
		}
		$query = "SELECT * FROM #__mymuse_product_attribute_sku
		WHERE product_parent_id = ".$pid;
		$this->_db->setQuery($query);
		$this->_attribute_skus = $this->_db->loadObjectList();

		if(count($this->_attribute_skus) < 1){
			$this->setError(JText::_('MYMUSE_CREATE_ATTRIBUTE_FIRST'));
            return false;
		}
		
		return true;
	}
	
	/**
	 * getAttributes
	 * 
	 * @return array
	 */
	function getAttributes(){
		$db = JFactory::getDBO();
		if(!$this->_attribute_skus){
			$this->getAttributeskus();
		}
		$id = JRequest::getVar('id',0);
		if($id){
			foreach($this->_attribute_skus as $a_sku){
				$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$id.'
				AND product_attribute_sku_id='.$a_sku->id;

				$db->setQuery($query);
				$this->_item->attributes[$a_sku->name] = $db->loadResult();
			}
		
			return $this->_item->attributes;
		}
		
		return array();
		
	}

	/**
	 * getAttributeskus
	 *
	 * @return array
	 */
	function getAttributeskus(){
		$db = JFactory::getDBO();
		$pid	= JRequest::getVar( 'parentid', null );
		if(!$pid){
			//see if we can get the parent from the item id
			$item = $this->getItem();
			if(isset($item->id)){
				$query = "SELECT parentid from #__mymuse_product WHERE id=".$item->id;
				$this->_db->setQuery($query);
				$pid = $this->_db->loadResult();
			}else{
				$this->setError(JText::_('MYMUSE_COULD_NOT_FIND_PARENT'));
            	return false;
			}
		}
		$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE product_parent_id='.$pid;
		$db->setQuery($query);
		$this->_attribute_skus = $db->loadObjectList();

		return $this->_attribute_skus;

	}
	
	/**
	 * updateAttributes
	 *
	 * @return boolean
	 */
	public function updateAttributes()
	{
		// Attributes

		$post 					= JRequest::get('post');
		$itemid					= JRequest::getVar('itemid','');
		if(!$itemid){
			return;
		}
		$db = JFactory::getDBO();
		$attribute_values		= $post['attribute_value'];
		$attribute_names		= $post['attribute_name'];
		//print_pre($attribute_values);
		//print_pre($attribute_names);exit;

		$query = "DELETE FROM #__mymuse_product_attribute
			WHERE product_id='".$itemid."'";
		//echo $query;
		$db->setQuery($query);
		$db->execute();

		while(list($key,$val) = each($attribute_values)){

			$query = "INSERT INTO #__mymuse_product_attribute
				(`product_id`,`product_attribute_sku_id`,`attribute_name`,`attribute_value`)
				VALUES 
				(".$itemid.",".$key.",'".$attribute_names[$key]."','".$val."')";
			$db->setQuery($query);
			$db->execute();

		}
		return true;

	}
	

	function getReorderConditions(){
		$subtype = JRequest::getVar('subtype','');
		$parentid = JRequest::getVar('parentid','');
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$where = '';
	
		if($subtype && $parentid){
			$w = ($subtype == "item") ? "product_physical=1" : "product_downloadable=1";

			$where = " parentid=$parentid AND $w ";
		}
	
		return $where;
	
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function saveorder($pks = null, $order = null)
	{
	
		MyMuseHelper::logMessage("here in model product\n");
		$table = $this->getTable('product','MymuseTable');
		$conditions = array();
	
		if (empty($pks))
		{
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}
	
		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);
	
			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
	
	
				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
	
				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;
				MyMuseHelper::logMessage("$condition\n");
	
				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}
	
				if (!$found)
				{
					$key = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}
	
	
		// Clear the component's cache
		$this->cleanCache();
	
		return true;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();
	
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
	
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
	
			if ($table->load($pk))
			{
	
				if ($this->canDelete($table))
				{
	
					$context = $this->option . '.' . $this->name;
	
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
	
					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());
						return false;
					}
	
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
	
					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
	
				}
				else
				{
	
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error)
					{
						JLog::add($error, JLog::WARNING, 'jerror');
						return false;
					}
					else
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
						return false;
					}
				}
	
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
	
		// Clear the component's cache
		$this->cleanCache();
	
		return true;
	}
	
	
}