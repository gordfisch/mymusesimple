<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
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
		
		//Amazon S3
		// getBucket($bucket, $prefix = null, $marker = null, $maxKeys = null, $delimiter = null, $returnCommonPrefixes = false)
		if($this->_params->get('my_use_s3')){
			require_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/amazons3.php';
			$this->_s3 = MyMuseHelperAmazons3::getInstance();
			$this->_previews = $this->_s3->getBucket($this->_params->get('my_preview_dir'));
		}
		
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

    	$filter_state 		= $app->getUserStateFromRequest( $option.'filter_state', 'filter_state', '', 'word' );
		$filter_catid 		= $app->getUserStateFromRequest( $option.'filter_catid', 'filter_catid', 0, 'int' );
		$filter_artistid 	= $app->getUserStateFromRequest( $option.'filter_artistid', 'filter_artistid', 0, 'int' );
		$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
		$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );

		$filter_item_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
		$filter_item_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
		
		$this->setState('file.ordering', $filter_order);
		$this->setState('file.direction', $filter_order_Dir);
		
		$this->setState('item.ordering', $filter_order);
		$this->setState('item.direction', $filter_item_order_Dir);
		
		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;
		$edit = $input->get('edit', 0);

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
		$subtype				= $input->get('subtype', '');
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
			AND product_downloadable=0 ";
		if($filter_item_order){
			$query .= "ORDER BY $filter_item_order ";
		}
		if($filter_item_order && $filter_item_order_Dir){
			$query .= "$filter_item_order_Dir";
		}
			
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
  
    	$artist_alias 		= MyMuseHelper::getArtistAlias($this->_item->catid);
    	$album_alias 		= MyMuseHelper::getAlbumAlias($this->_item->id);
    	 
    	$site_url = MyMuseHelper::getSiteUrl($this->_item->id,'1');
    	$site_path = MyMuseHelper::getSitePath($this->_item->id,'1');
    	$download_path = MyMuseHelper::getdownloadPath($this->_item->id,'1');
    	
    	
    	if(count($this->_tracks)){
    		
    		//need count minus any alltracks minus any missing files
    		$i = 0;
    		foreach($this->_tracks as $track){

    			
    			
    			//main file
    			$jason = json_decode($track->file_name);
    			if(is_array($jason)){
    				$track->file_name = '';
    				foreach($jason as $j){
    					$track->file_name .= $j->file_name."<br />";
    				}
    			}
                
                if($track->product_allfiles){
    				continue;
    			}
                
    			if($this->_params->get('my_encode_filenames')){
    				$name = $track->title_alias;
    			}else{
    				$name = $track->file_name;
    			}
    			if($this->_params->get('my_download_dir_format') == 1){
    				//by format
    				$ext = MyMuseHelper::getExt($name);
    				$download_path .= $ext.DS;
    			}
    			$full_filename = $filename = $site_path.$name;
    			if(!$this->_params->get('my_use_s3') && file_exists($full_filename) ){
    				$i++;
    			}
    			
    			//preview
    			$path = ($this->_params->get('my_use_s3')? $artist_alias.DS.$album_alias.DS : $site_path);
    			$preview_full_filename = $path.$track->file_preview;
    			
    			if(!$this->_params->get('my_use_s3')  && file_exists($preview_full_filename)
    				||
    					($this->_previews && array_key_exists($preview_full_filename, $this->_previews))
    					){
    				$i++;
    			}
    		}

    		$count = $i;

    		JPluginHelper::importPlugin( 'mymuse' );
    		$dispatcher		= JDispatcher::getInstance();
    		$i = 0;
    		$download_dir = $this->_params->get('my_download_dir');

    		if(stristr(PHP_OS, 'win') && !$this->_params->get('my_use_s3')){
    			$root = str_replace("\\","/", $root);
    			$download_dir = str_replace("\\","/", $download_dir);
    		}

    		$i = 0;
    		$preview_tracks = array();
    		foreach($this->_tracks as $track){
    			if($track->file_preview){
    				$preview_tracks[$i] = $track;
    				$i++;
    			}else{
    				$track->stream = '';
    				$track->flash= '';
    			}
    		}

    		$i = 0;

    		foreach($preview_tracks as $track){
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
    				
    				
    				$track->path = $site_url.$track->file_preview;
    				$track->real_path = ($this->_params->get('my_use_s3')? '' : JPATH_ROOT.DS) . $this->_params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview;
    				$s3_path = $artist_alias.DS.$album_alias.DS.$track->file_preview;
    				
    				
    				if($track->file_preview_2){
    					$track->real_path2 = ($this->_params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$this->_params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview_2;
    					$track->path_2 = $site_url.$track->file_preview_2;
    				}
    				if($track->file_preview_3){
    					$track->real_path3 = ($this->_params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$this->_params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview_3;
    					$track->path_3 = $site_url.$track->file_preview_3;

    				}
    				$track->flash_type = $track->file_type;
				
    				if((!$this->_params->get('my_use_s3')  && file_exists($track->real_path))
    						|| 
    						($this->_previews && array_key_exists($s3_path, $this->_previews))
    						){
    				//if($table->fileExists($track->real_path)){
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
    			
    			//$time_end = microtime(true);
    			//$time = $time_end - $time_start;
    			//echo "Did nothing in $time seconds\n<br />";
    			
    			$track->flash = $flash;

    			//make flash for admin to listen to Main File, we'll call it stream
    			$stream = "";
    			$htaccess = "";
    			if($this->_params->get('my_encode_filenames')){
    				$name = $track->title_alias;
    			}else{
    				$name = $track->file_name;
    			}
    			if(file_exists($this->_params->get('my_download_dir').DS.'.htaccess')){
    				$htaccess = 1;
    			}

    			if($name && !$htaccess){
    				$full_filename = $this->_params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
    				if(!file_exists($full_filename)){
    					//try with the root
    					$full_filename = JPATH_ROOT.DS.$this->_params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
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
    						$track->path .= "/".$name;
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
    				
    			
    			$track->stream = $stream;

    		}

    		// if there were previews
    		if($i){
    			// make a controller for the play/pause buttons
    			$results = $dispatcher->trigger('onPrepareMyMuseMp3PlayerControl',array(&$preview_tracks) );

    			// get main player, set to play first track
    			reset($preview_tracks);
    			$flash = '';
    			$audio = 0;
    			$video = 0;
    			$done = 0;
    			if(isset($preview_tracks[0])){
    				$track = $preview_tracks[0];
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
    	$input = $app->input;
    	$option = 'com_mymuse';

    	$this->_params = $this->getState()->get('params');
    	$limit = $this->getState('list.limit');
    	$id = $input->get('id');

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
     * Method to get the file lists.
     *
     * @access    public
     * @return    array
     */
    function getFileLists()
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
			$s3 = MyMuseHelperAmazons3::getInstance();
			$folder = $artist_alias.'/'.$album_alias;
			$everything = $s3->listS3Contents($folder, $this->_params->get('my_preview_dir'));
			$folder = trim($folder,'/');
			$dirLength = strlen($folder);
			if(count($everything)) foreach($everything as $path => $info) {
				if(array_key_exists('size', $info) && (substr($path, -1) != '/')) {
					if(substr($path, 0, $dirLength) == $folder) {
						$path = substr($path, $dirLength);
					}
					$path = trim($path,'/');
					$files[] = $path;
				}
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
		$lists['previews_2'] = JHTML::_('select.genericlist',  $previews, 'file_preview_2', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview_2 );
		$lists['previews_3'] = JHTML::_('select.genericlist',  $previews, 'file_preview_3', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview_3 );
		
		// get the tracks lists
		$files = array();
		if($this->_params->get('my_use_s3')){
			$folder = $artist_alias.'/'.$album_alias;
			//echo $this->_params->get('my_download_dir')." ".$folder;
			$everything = $s3->listS3Contents($folder, $this->_params->get('my_download_dir'));
			$folder = trim($folder,'/');
			$dirLength = strlen($folder);
			if(count($everything)) foreach($everything as $path => $info) {
				if(array_key_exists('size', $info) && (substr($path, -1) != '/')) {
					if(substr($path, 0, $dirLength) == $folder) {
						$path = substr($path, $dirLength);
					}
					$path = trim($path,'/');
					$files[] = $path;
				}
			}
		}else{
			$directory = MyMuseHelper::getDownloadPath($parentid,'1');
			if($this->_params->get('my_download_dir_format')){
				//by format
				$files = array();
				foreach($this->_params->get('my_formats') as $format){
					if(!JFolder::exists( $directory.DS.$format )){
						JFolder::create( $directory.DS.$format );
					}
					$arr = JFolder::files( $directory.DS.$format );
					if(is_array($arr)){
						$files = array_merge($files,$arr);
					}
				}
			}else{
				if(!JFolder::exists( $directory )){
					JFolder::create( $directory );
				}
				$files = JFolder::files( $directory );
			}
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

		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$pid	= $input->get( 'parentid', null, 'post', 'int' );
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
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$id 	= $input->get('id',0);
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
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$pid	= $input->get( 'parentid', null );
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
	

	function getReorderConditions($table){
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$subtype = $input->get('subtype','');
		$parentid = $input->get('parentid','');
		$db = JFactory::getDBO();
	
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
	
	/**
	 * ARBORETA
	 * IMPORT Products
	 *
	 */
	 
	function readCSV($csvFile){
		$file_handle = fopen($csvFile, 'r');
		while (!feof($file_handle) ) {
			$line_of_text[] = fgetcsv($file_handle, 1024);
		}
		fclose($file_handle);
		return $line_of_text;
	}
	
	function remove_accents($str)
	{
		$from = array(
				"á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï",
				"ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç", "Á", "À", "Â",
				"Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô",
				"Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç"
		);
		$to = array(
				"a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i",
				"o", "o", "o", "o", "o", "u", "u", "u", "u", "c", "A", "A", "A",
				"A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O",
				"O", "O", "U", "U", "U", "U", "C"
		);
		return    str_replace($from, $to, $str);
	}
	
	 
	function import_products()
	{
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$limit = JRequest::getVar('limit','50');
		$limitstart = JRequest::getVar('limitstart','0');
	
		$count = $limitstart;
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$path = JPATH_SITE.DS.'staging';
		$path_downloads = 'Download';
		$path_preview = 'Preview';
		$mycsv = $path.DS.'import.csv';
		
		$q = "SELECT id from #__categories where alias='artists'";
		$db->setQuery($q);
		$catid_artist = $db->loadResult();
		$q = "SELECT id from #__categories where alias='genres'";
		$db->setQuery($q);
		$catid_genre = $db->loadResult();
		if(!$limitstart){
			JFile::delete(JPATH_ADMINISTRATOR .DS.'components'.DS.'com_mymuse'.DS.'log.html');
		}
		$artists = JFolder::folders($path, '.');
		

		//$files = JFolder::files($path, '.', false, true );
		
		$clear = JRequest::getVar('clear','0');
	
		if($clear){
			
			
			$good = 0;
			$q = "SELECT id from #__categories where parent_id='$catid_artist' OR parent_id='$catid_genre'";
		echo $q."<br />";
			$db->setQuery($q);
			$cats = $db->loadObjectList();
			$cat_ids = '(';
			foreach ($cats as $cat){
				$cat_ids .= $cat->id.",";
				$good = 1;
			}
			$cat_ids = preg_replace("/,$/",'',$cat_ids);
			$cat_ids .= ")";
		echo "clear2 ".$cat_ids;
			if($good){
				$q = "DELETE FROM #__mymuse_product WHERE 1 ";
				//AND catid IN $cat_ids";
			echo $q."<br />";
				$db->setQuery($q);
				if(!$db->query()){
					echo $db->error;
				}
				$q = "ALTER TABLE #__mymuse_product AUTO_INCREMENT = 1";
				if(!$db->query()){
				    echo $db->error;
				 }
	
				$q = "DELETE FROM #__mymuse_product_category_xref WHERE 1
				AND catid IN $cat_ids";;
			echo $q."<br />";
				$db->setQuery($q);
				if(!$db->query()){
					echo $db->error;
				}
	
				$q = "DELETE FROM #__categories WHERE id IN $cat_ids";
				$db->setQuery($q);
			echo $q."<br />";
				if(!$db->query()){
					echo $db->error;
				}
				
				$q = "DELETE FROM `#__assets` WHERE `name` LIKE '%mymuse_product%'";
				$db->setQuery($q);
			echo $q."<br />";
				if(!$db->query()){
					echo $db->error;
				}
				$q = "DELETE FROM `#__assets` WHERE `name` LIKE '%mymuse.category%'";
				$db->setQuery($q);
				echo $q."<br />";
				if(!$db->query()){
					echo $db->error;
				}
			}
			?>
<a href="index.php?option=com_mymuse">Go Back</a>
<?php 
			exit;

		}

		$from = array(
				"á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï",
				"ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç", "Á", "À", "Â",
				"Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô",
				"Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç"
		);
		$to = array(
				"a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i",
				"o", "o", "o", "o", "o", "u", "u", "u", "u", "c", "A", "A", "A",
				"A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O",
				"O", "O", "U", "U", "U", "U", "C"
		);
		
		$artist_array = array();
		$product_array = array();
		$date = date('Y/M/d h:i:s');
		
		$string = "\n\n\n##########################\n$date\n##############################\n<table>";
		$this->logMessage($string);
		$string = '';
		
		
		if(!$limitstart){ // just starting
			// make artist categories if needed from list of folders in /staging/
			foreach ( $artists as $i => $artist ) {
				// see if artist category exist
				$artist_alias = JApplication::stringURLSafe ( $artist );
				$query = "SELECT id FROM #__categories WHERE alias =" . $db->quote ( $artist_alias );
				
				$db->setQuery ( $query );
				if (! $artist_id = $db->loadResult ()) {
					// make top cat
					$artist_id = $this->makeCategory ( $artist, $catid_artist );
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">Category Made: " . $artist . " $artist_id</span></td></tr>";
				} else {
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">Category Exists: " . $artist . " $artist_id</span></td></tr>";
				}
				$artist_array [$artist_alias] = $artist_id;
			}
			$this->logMessage ( $string );
		}
		
		
		
		$csv = $this::readCSV($mycsv);
		
		$quit = 0;

		for($i = $limitstart; $i < $limit+$limitstart; $i++ ){
	
			if($quit > 10){
				echo "All done <a href='index.php?option=com_mymuse&view=products'>Back to Products</a>";
				$this->logMessage("</table>");
				exit;
			}
			if(!isset($csv[$i][1]) || $csv[$i][1] == ''){
				$quit++;
				continue;
			}
			if($csv[$i][0] == "Files Ready?" || $csv[$i][0] == "N" || $csv[$i][2] == "Album"){
				continue;
			}
			$string = '';
			$entry = $csv[$i];

			$this->logMessage("<tr><td>$i</td><td><span style=\"color: ##1D854C;\">".implode(":",$entry)."</span></td></tr>");
			$have_payload = '';
			$have_demo = '';
			/**
			 *     [0] => Array
        (
        Files Ready?	Composer/ Artist	Album	Track Title	Catalogue Number	Preview Filename	Download Filename	Length	Description	Web category 1	Web category 2	Web category 3

            [0] => Notes
            [1] => Artist
            [2] => Album
            [3] => Title
            [4] => Catalogue
            [5] => Preview File
            [6] => Download File
            [7] => Time
            [8] => Description
            [9] => Genre1
            [10] => Genre1
            [11] => Genre3
            
        )
        */
			$notes = $entry[0];
			$artist = $entry[1];
			$entry[2] = ($entry[2] != '')? $entry[2] : $entry[1];
			$album = $entry[2];
			$song_title = $entry[3];
			$catalog = $entry[4];
			$preview = $entry[5];
			$download = $entry[6];
			$time = $entry[7];
			$description = $entry[8];
			$genre1 = $entry[9];
			$genre2 = $entry[10];
			$genre3 = $entry[11];


	
			$string .= "<tr><td>$i</td><td>".$artist.": ".$album." : $song_title</td></tr>";
			$product_name = $album;
			$product_alias = JApplication::stringURLSafe($product_name);
			
			//artist exist?
			if(isset($artist_array[$product_alias])){
				$artist_id = $artist_array[$product_alias];
			}else{
				$artist_alias = JApplication::stringURLSafe($artist);
				$query = "SELECT id FROM #__categories WHERE alias =".$db->quote($artist_alias);
			
				$db->setQuery($query);
				if(!$artist_id = $db->loadResult()){
					//make top cat
					$artist_id = $this->makeCategory($entry[1], $catid_artist);
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">Category Made: ".$artist." $artist_id</span></td></tr>";
					//echo "created $artist <br />";
				}else{
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">Category Exists: ".$artist." $artist_id</span></td></tr>";
					//echo "HAD $artist <br />";
				}
				$artist_array[$artist_alias] = $artist_id;
			}
			
			if($overwrite){
				
			}
			
			//download file
			if($download && file_exists($path.DS.$product_name.DS."Download".DS.$download)){
				$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">PAYLOAD: ".$product_name.DS."Download".DS.$download."</span></td></tr>";
				$filename = $path.DS.$product_name.DS."Download".DS.$download;
				$ext =  JFile::getExt($download);
				$name = JApplication::stringURLSafe(JFile::stripExt($download)).'.'.$ext;
				
				$have_payload = $path.DS.$product_name.DS."Download".DS.$name;
				if(file_exists($have_payload)){
					$string .= "<tr><td>$i</td><td><span style=\"color: ##1D854C;\">PAYLOAD EXISTS: ".$have_payload."</span></td></tr>";
				}else{
					JFile::copy($filename, $have_payload);
					$string .= "<tr><td>$i</td><td><span style=\"color: ##1D854C;\">PAYLOAD copied to: ".$have_payload."</span></td></tr>";
					
				}
		
			}else{
				$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">NO PAYLOAD FILE: ".$path.DS.$product_name.DS."Download".DS.$download."</span></td></tr>";
			}
			
			$this->logMessage($string);
			$string = '';
		
			//preview
			if($preview && file_exists($path.DS.$product_name.DS."Preview".DS.$preview)){
				$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;;\">PREVIEW EXISTS: ".$product_name.DS."Preview".DS.$preview."</span></td></tr>";
				$filename = $path.DS.$product_name.DS."Preview".DS.$preview;
				$ext =  JFile::getExt($preview);
				$name = JApplication::stringURLSafe(JFile::stripExt($preview)).'.'.$ext;
				$have_demo = $path.DS.$product_name.DS."Preview".DS.$name;
				if(file_exists($have_demo)){
					$string .= "<tr><td>$i</td><td><span style=\"color: ##1D854C;;\">Preview Exists: ".$have_demo."</span></td></tr>";
				}else{
					JFile::copy($filename, $have_demo);
					$string .= "<tr><td>$i</td><td><span style=\"color: ##1D854C;;\">Preview copied: ".$have_demo."</span></td></tr>";
				}
				//echo "Found Preview $have_demo <br />";
			}else{
				$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">NO PREVIEW FILE: ".$path.DS.$product_name.DS."Preview".DS.$preview."</span></td></tr>";
				//echo "NO PREVIEW FILE $download <br />";
			}
			$this->logMessage($string);
			$string = '';
			
			
			// MAKING OF PRODUCTS AND TRACKS//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			if($have_payload != ''){
				//see if parent product exists
				$query = "SELECT id FROM #__mymuse_product WHERE title= ".$db->quote($product_name)."
				AND parentid=0";
				$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">$query</span></td></tr>";
				
				$db->setQuery($query);
				if(!$product_id = $db->loadResult()){
					
					//echo "make product ".$album."<br />";
					$product_id = $this->makeProduct($entry, $artist_id, 0, 0);
					if(is_numeric($product_id)){
						$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">Product Made: $product_name $artist_id $product_id</span></td></tr>";

					}else{
						$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">Could not make product : ".$product_name." ".$product_id."</span></td></tr>";
						$string .= "</table>";
						$this->logMessage($string);

						return false;
					}
				}else{
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">HAD product: ".$product_name."</span></td></tr>";
				}
				$this->logMessage($string);
				$string = '';
				
				
				// see if track exists
				if($have_payload != ''){
					$query = "SELECT id FROM #__mymuse_product WHERE  title = ".$db->quote($song_title)."
					AND parentid=$product_id";
					//echo $query. "<br />";
					$db->setQuery($query);
					//echo $db->loadResult(). "<br />"; 
					if(!$track_id = $db->loadResult()){
						//make track
						$entry['artist_id'] = $artist_id;
						$entry['parentid'] = $product_id;
						//echo "making a track <br />"; print_pre($entry); 
						$track_id = $this->makeProduct($entry, $artist_id, $product_id, 1, $have_payload, $have_demo);
						if(is_numeric($track_id)){
							$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;;\">Made Track : ".$song_title." ".$artist_id." $track_id</span></td></tr>";
						}else{
							$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">Could Not Make Track : ".$song_title."</span></td></tr>";
							$string .= "</table>";
							$this->logMessage($string);
							return false;
						}
						
					}else{
						$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">HAD Track: ".$song_title."</span></td></tr>";
						
					}
				}
				
				if(JFile::delete($have_payload)){
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">DELETED LOCAL file: ".$have_payload."</span></td></tr>";
				}else{
					$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">COULD NOT LOCAL DELETE file: ".$have_payload."</span></td></tr>";
				}
				if(JFile::delete($have_demo)){
					$string .= "<tr><td>$i</td><td><span style=\"color: #2222FF;\">DELETED LOCAL file: ".$have_demo."</span></td></tr>";
				}else{
					$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">COULD NOT DELETE LOCAL file: ".$have_demo."</span></td></tr>";
				}
				
			}else{
				
				$string .= "<tr><td>$i</td><td><span style=\"color: #FF0000;\">NO PAYLOAD FILE!!!!!</span></td></tr>";
			}
			//spacer
			$string .= "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
			$this->logMessage($string);
			$string = '';
		}
		
	 
		$oldlimitstart =$limitstart;
		$limitstart = $limitstart+50;
		$url = "index.php?option=com_mymuse&&task=product.import_products2&limit=$limit&limitstart=$limitstart&myfile=$myfile";
		$this->logMessage("<tr><td>$i</td><td><span style=\"color: #2222FF;\">Products $oldlimitstart to $limitstart saved</span></td></tr>");
		$this->logMessage("<tr><td>$i</td><td><span style=\"color: #2222FF;\">$url</span></td></tr>");
		$this->logMessage( "</table>" );

	
		$app->redirect($url);
					 
	}
					 
	function logMessage($message){
		$path = JPATH_ADMINISTRATOR .DS.'components'.DS.'com_mymuse'.DS.'log.html';
	
		$fh = fopen($path, "a");
		fwrite($fh,$message."\n");
		fclose($fh);
		return true;
	}
	
	function makeCategory($title, $parent_id){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');
		$helper = new MyMuseUpdateHelper;
		$id = $helper->makeCategory($title, $parent_id);
		return $id;
	}
	
	function makeProduct($entry, $artistid, $parent_id = 0, $downloadable = 0, $payloadpath = '', $demopath = '')
	{
		$db = JFactory::getDBO();
		if($payloadpath){
			$_FILES['product_file']['name'] = basename($payloadpath);
			$_FILES['product_file']['tmp_name'] = $payloadpath;
			$_FILES['product_file']['size'] = filesize($payloadpath);
		}
		if($demopath){
			$_FILES['product_preview']['name'] = basename($demopath);
			$_FILES['product_preview']['tmp_name'] = $demopath;
            $_FILES['product_preview']['size'] = filesize($demopath);
		}
		/**
		 *     [0] => Array
		 (
		 Files Ready?	Composer/ Artist	Album	Track Title	Catalogue Number	Preview Filename	Download Filename	Length	Description	Web category 1	Web category 2	Web category 3
		
		 [0] => Notes
		 [1] => Artist
		 [2] => Album
		 [3] => Title
		 [4] => Catalogue
		 [5] => Preview File
		 [6] => Download File
		 [7] => Time
		 [8] => Description
		 [9] => Genre1
		 [10] => Genre1
		 [11] => Genre3
		
		 )
		 */
		//cats and othercats
		$q = "SELECT id from #__categories where alias='genres'";
		$db->setQuery($q);
		$catid_genre = $db->loadResult();
		$catid = '';
		$othercats = array();
		$othercats[] = $artistid;
		$string = '';
		
		if($entry[9]){
			$genre_alias = JApplication::stringURLSafe($entry[9]);
			$query = "SELECT id FROM #__categories WHERE alias =".$db->quote($genre_alias);
				
			$db->setQuery($query);
			if(!$genre_id = $db->loadResult()){
				//make top cat
				$genre_id = $this->makeCategory($entry[9], $catid_genre);
				$string .= "<tr><td></td><td><span style=\"color: #2222FF;\">Genre Made: ".$entry[9]." $genre_id</span></td></tr>";
				
			}else{
				$string .= "<tr><td></td><td><span style=\"color: #2222FF;\">Genre Exists: ".$entry[9]." $genre_id</span></td></tr>";
			}
			$catid = $genre_id;
			$othercats[] = $genre_id;
		}
		
		if($entry[10]){
			$genre_alias = JApplication::stringURLSafe($entry[10]);
			$query = "SELECT id FROM #__categories WHERE alias =".$db->quote($genre_alias);
		
			$db->setQuery($query);
			if(!$genre_id = $db->loadResult()){
				//make top cat
				$genre_id = $this->makeCategory($entry[10], $catid_genre);
				$string .= "<tr><td></td><td><span style=\"color: #2222FF;\">Genre Made: ".$entry[10]." $genre_id</span></td></tr>";
					
			}else{
				$string .= "<tr><td></td><td><span style=\"color: #2222FF;\">Genre Exists: ".$entry[10]." $genre_id</span></td></tr>";
			}
			$othercats[] = $genre_id;
		}
		
		if($entry[11]){
			$genre_alias = JApplication::stringURLSafe($entry[11]);
			$query = "SELECT id FROM #__categories WHERE alias =".$db->quote($genre_alias);
		
			$db->setQuery($query);
			if(!$genre_id = $db->loadResult()){
				//make top cat
				$genre_id = $this->makeCategory($entry[11], $catid_genre);
				$string .= "<tr><td></td><td><span style=\"color: #2222FF;\">Genre Made: ".$entry[11]." $genre_id</span></td></tr>";
					
			}else{
				$string .= "<tr><td></td><td><span style=\"color: #2222FF;\">Genre Exists: ".$entry[11]." $genre_id</span></td></tr>";
			}
			$othercats[] = $genre_id;
		}

		$this->logMessage($string);
		if(!$catid){
			$catid = $artistid;
		}
		if($parent_id){
			$title = $entry[3];
			$sku = $entry[4];
			$description = $entry[8];
		}else{
			$title = $entry[2];
			$sku = $entry[1].'-'.rand(1,1000);;
			$description = '';
		}
		$alias = JApplication::stringURLSafe($title);
		
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');
		$helper = new MyMuseUpdateHelper;
		$p = new stdClass;
		$p->id = 0;
		$p->title = $title;
		$p->alias = $alias;
		$p->title_alias = '';
		$p->articletext = $description;
		$p->state = 1;
		$p->artistid = $artistid;
		$p->catid = $catid;
		$p->price = '14.99';
	
		$p->publish_up = '';
		$p->publish_down = '';
		$p->image ='';
		$p->images ='';
		$p->urls ='';
		$p->product_in_stock = 0;
		$p->version = 1;
		$p->parentid = $parent_id;
		$p->ordering = 0;
		$p->metakey ='';
		$p->metadesc ='';
		$p->hits = 0;
		$p->product_physical = 0;
		$p->product_downloadable = $downloadable;
		$p->product_allfiles = 0;
		$p->product_sku = $sku;
		$p->product_made_date = "2016-02-14 23:43:40";
		$p->product_special = 0;
		$p->product_discount = 0;
		$p->reservation_fee = 0;
		$p->file_length = '';
		$p->file_name = '';
		$p->file_downloads = '';
		$p->file_contents = '';
		$p->file_type = 'audio';
		$p->file_preview = '';
		$p->file_time = $entry[7];
		$p->othercats= $othercats;
//print_pre($entry);
//print_pre($p);	
		$id = $helper->makeProductObject($p);
		if(!$id){
			return $helper->error;
		}
		unset($_FILES);
		unset($_POST);
		return $id;
	}
	

}
