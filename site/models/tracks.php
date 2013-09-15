<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving lists of articles.
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.6
 */
class MyMuseModelTracks extends JModelList
{

    protected $_item = null;
    
	protected $_products = null;

	protected $_category = null;
	
	protected $_tracks = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_mymuse.category';
    
    
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
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'price','a.price',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
                'category_name', 'c.title'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = 'category_name', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// List state information
        $pk		= JRequest::getInt('id');
        $this->setState('category.id', $pk);
        $this->setState('list.alpha', JRequest::getString('filter_alpha', ''));
        $this->setState('list.prods', JRequest::getString('products', ''));
        
		//$value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		//$value = $app->getUserStateFromRequest($this->context.'.limitstart', 'limitstart', 0);
		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

		$orderCol	= JRequest::getCmd('filter_order', 'category_name');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'category_name';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder	=  JRequest::getCmd('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);
		$user		= JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		// process show_noauth parameter
		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		}
		else {
			$this->setState('filter.access', false);
		}
        
        		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');
		if ($showSubcategories) {
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$this->setState('filter.subcategories', true);
		}

		$this->setState('layout', JRequest::getCmd('layout'));
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':'.$this->getState('filter.published');
		$id .= ':'.$this->getState('filter.access');
		$id .= ':'.$this->getState('filter.featured');
		$id .= ':'.$this->getState('filter.product_id');
		$id .= ':'.$this->getState('filter.product_id.include');
		$id .= ':'.$this->getState('filter.category_id');
		$id .= ':'.$this->getState('filter.category_id.include');
		$id .= ':'.$this->getState('filter.author_id');
		$id .= ':'.$this->getState('filter.author_id.include');
		$id .= ':'.$this->getState('filter.author_alias');
		$id .= ':'.$this->getState('filter.author_alias.include');
		$id .= ':'.$this->getState('filter.date_filtering');
		$id .= ':'.$this->getState('filter.date_field');
		$id .= ':'.$this->getState('filter.start_date_range');
		$id .= ':'.$this->getState('filter.end_date_range');
		$id .= ':'.$this->getState('filter.relative_date');

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of products subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();

        $alpha = $this->getState('list.alpha','');
        $IN = $this->getState('list.prods','');
        $ordering 	= $this->getState('list.ordering', 'category_name');
        $listDirn	= $this->getState('list.direction', 'ASC');
        if($ordering == 'category_name'){
        	//$ordering = 'category_name, a.title';
        }
        
        // TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS 
        // get child tracks with prices
        $query = "SELECT a.id, a.title, a.title_alias,a.alias, a.introtext, a.fulltext, a.parentid, a.product_physical, 
        a.product_downloadable, a.product_allfiles, a.product_sku,
        a.product_made_date, a.price, a.featured, a.product_discount, a.product_package_ordering, a.product_package,
        a.file_length,a.file_time,
        a.file_name,a.file_preview,a.file_preview_2, a.file_preview_3,a.file_type, a.detail_image,
        p.title as product_title, p.alias as parent_alias, p.catid as artistid, p.product_made_date,
        c.title as category_name,
        ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count
        FROM #__mymuse_product as a
        LEFT JOIN #__mymuse_product as p ON a.parentid = p.id
        LEFT JOIN #__categories as c ON a.catid = c.id
        LEFT JOIN #__mymuse_product_rating AS v ON a.id = v.product_id
        WHERE a.parentid IN ".$IN ."
        AND a.product_downloadable = 1
        AND a.state=1
        ";
        if($alpha != ''){
            $query .= "AND c.title LIKE '$alpha%' ";
        }
        $query .= "ORDER BY $ordering $listDirn
        ";
        //echo $query;
		return $query;
	}


    	/**
	 * Get the products in the category
	 *
	 * @return	mixed	An array of products or false if an error occurs.
	 * @since	1.5
	 */
	function getProducts()
	{
		$params = $this->getState()->get('params');
		$limit = $this->getState('list.limit');
        $limit = 10000;


		if ($this->_products === null && $category = $this->getCategory()) {
			$model = JModelLegacy::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));
			$model->setState('params', JFactory::getApplication()->getParams());
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', '1');
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));

			$model->setState('list.ordering', 'a.id');


			$model->setState('list.start', 0);
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', 'ASC');
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

		}
        
		$IN = '(';
		foreach($this->_products as $p){
			$IN .= $p->id.',';
		}

		$IN = preg_replace("/,$/",'',$IN);
		$IN .= ')';
        $this->setState('list.prods',$IN);
        
		return $this->_products;
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
        
		if (!is_object($this->_category)) {
            
            /**
            if( isset( $this->state->params ) ) {
				$params = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_articles', 1) || !$params->get('show_empty_categories_cat', 0);
			}
			else {
				$options['countItems'] = 0;
			}
            jimport( 'joomla.application.categories' );
            $options = array();
            $categories = JCategories::getInstance('Mymuse', $options);

			$this->_category= $categories->get($this->getState('category.id', 'root'));
            */
     
			$id = $this->getState('category.id', 'root');
            $query = "SELECT * FROM #__categories WHERE id=$id";
            $this->_db->setQuery($query);
            
            $this->_category = $this->_db->loadObject();
            $registry = new JRegistry;
            $registry->loadObject(json_decode($this->_category->params));
            $this->_category->params = $registry;
		}

		return $this->_category;
	}



	/**
	 * Method to get a list of tracks.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 * @since	1.6
	 */
	public function getItems()
	{
		$params = MyMuseHelper::getParams();
		$tracks	= parent::getItems();

		$site_url = preg_replace("#administrator/#","",JURI::base());
		$globalParams = JComponentHelper::getParams('com_mymuse', true);
        $root = JPATH_ROOT.DS;
        $top_cat = $this->getState('filter.category_id');
        $this->_category->flash_type = '';
        $this->_category->flash = '';
        
        // Compute ordered products.
        $user	= JFactory::getUser();
        $myOrders = array();
        if (!$user->get('guest') && $params->get('my_play_downloads')) {
        	$userId	= $user->get('id');
        	$my_download_enable_status = $params->get('my_download_enable_status','C');
        	$query = "SELECT i.product_id from #__mymuse_order_item as i, #__mymuse_order as o
        	WHERE o.id=i.order_id
        	AND o.order_status='$my_download_enable_status'
        	AND o.user_id = $userId";
        
        	$db->setQuery($query);
        	if($res = $db->loadAssocList()){
        		foreach($res as $r){
        			if(!in_array($r['product_id'], $myOrders)){
        				$myOrders[] = $r['product_id'];
        			}
        		}
        	}
        }
        
		//check prices add flash
		while (list($i,$track)= each( $tracks))
		{
            $tracks[$i]->flash = '';
			$tracks[$i]->flash_type = '';
            $product_model = JModelLegacy::getInstance('Product', 'MyMuseModel', array('ignore_request' => true));
            $tracks[$i]->price = $product_model->getPrice($track);
			if($params->get('my_add_taxes')){
				$tracks[$i]->price = MyMuseCheckout::addTax($item->price);
			}
			$track->params = clone $this->getState('params');
			$artist_alias = MyMuseHelper::getArtistAlias($track->artistid);
			$album_alias = MyMuseHelper::getAlbumAlias($track->parentid);

            //get download file
            if($params->get('my_encode_filenames')){
                $track->download_name = $track->title_alias;
            }else{
                $track->download_name = $track->file_name;
            }
            
            $down_dir = str_replace($root,'',$params->get('my_download_dir'));
            $track->download_path = JURI::base().'/'.$down_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->download_name;
            $track->download_real_path = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$track->download_name;
            
            if((!$track->price["product_price"] || $track->price["product_price"] == "FREE")
                    && $params->get('my_play_downloads')){
                $track->file_preview = 1;
            }
            
            //Audio/Video or some horrid mix of both
            
            if($this->_category->flash_type != "mix"){
                if($this->_category->flash_type == "audio" && $track->file_type == "video"){
                    //oh christ it's a mix
                    $this->_category->flash_type = "mix";
                    $track->flash_type = "mix";
                }elseif($this->_category->flash_type == "video" && $track->file_type == "audio"){
                    //oh christ it's a mix
                    $this->_category->flash_type = "mix";
                    $track->flash_type = "mix";
                }else{
                    $this->_category->flash_type = $track->file_type;
                    $track->flash_type = $track->file_type;
                }
            }else{
                $track->flash_type = "mix";
            }
        }
        $dispatcher		=& JDispatcher::getInstance();

        if($params->get('product_player_type') == "each" || 
            $params->get('product_player_type') == "single"){
            reset($tracks);
            $count = count($tracks);
            while (list($i,$track)= each( $tracks )){
                if($track->product_allfiles == 1){
                    continue;
                }
                $artist_alias = MyMuseHelper::getArtistAlias($track->artistid);
                $album_alias = MyMuseHelper::getAlbumAlias($track->parentid);
                $flash = '';
                $track->purchased = 0;
                //echo "artist alias $album_alias $artist_alias";
                if($track->file_preview){

                    $prev_dir = $site_url.str_replace($root,'',$params->get('my_preview_dir'));
                    $track->path = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview;
                    $track->real_path = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview;
                    if($track->file_preview_2){
                        $track->path_2 = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview_2;
                        $track->real_path_2 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview_2;
                    }
                    if($track->file_preview_3){
                        $track->path_3 = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview_3;
                        $track->real_path_3 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$track->file_preview_3;
                    }
                    
                    //should we use the real download file?
                    if($params->get('my_play_downloads', 0) && in_array($track->id, $myOrders)){
                        $track->path = $track->download_path;
                        $track->real_path = $track->download_real_path;
                        $track->purchased = 1;
                    }
                    if($params->get('my_play_downloads', 0) && 
                            (!$track->price["product_price"] || $track->price["product_price"] == "FREE")){
                        $track->path = $track->download_path;
                        $track->real_path = $track->download_real_path;
                        $track->purchased = 1;
                    }
                    
                    //audio or video?
                    $ext = MyMuseHelper::getExt($track->file_preview);

                    if($track->file_type == "video"){
                        //movie
                        $flash = '<!-- Begin Player -->';
                        $results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$track,$params->get('product_player_type'),0,0,$i, $count) );
                        if(is_array($results) && isset($results[0]) && $results[0] != ''){
                            $flash .= $results[0];
                        }
                        $flash .= '<!-- End Player -->';
                    }elseif($track->file_type == "audio"){
                        //audio
                        $flash = '<!-- Begin Player -->';
                        $results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$track,$params->get('product_player_type'),0,0,$i, $count));
                        if(is_array($results) && isset($results[0]) && $results[0] != ''){
                            $flash .= $results[0];
                        }
                        $flash .= '<!-- End Player -->';
                    }

                }else{
                    $flash = '';
                }
                $track->flash = $flash;


            }//end for each track
        }
        
        
        if($params->get('product_player_type') == "single"){
            //get the player itself
            reset($tracks);
            $flash = '';
            $audio = 0;
            $video = 0;
            foreach($tracks as $track){
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
                    $this->_category->flash = $flash;
                    $this->_category->flash_id = $track->id;
                    if($this->_category->flash_type != "mix"){
                        break;
                    }elseif($audio && $video){
                        break;
                    }
                }
            }
        }
        
        if($params->get('product_player_type') == "playlist"){
            //get the main flash for the product
    
            reset($tracks);
            $this->_category->previews = array();
            $audio = 0;
            $site_url = preg_replace("#administrator/#","",JURI::base());
            $prev_dir = $site_url.str_replace($root,'',$params->get('my_preview_dir'));
            $i = 0;
            $type = "";
            foreach($tracks as $track){
                if($track->file_preview){
                    $track->path .= $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview;
                }

                if($track->file_preview_2){
                    $track->path_2 = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview_2;
                }
                if($track->file_preview_3){
                    $track->path_3 = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview_3;
                }
                $this->_category->previews[] = $track;
                if(preg_match("/video/",$track->file_type)){
                    $type = "video";
                }
                if(preg_match("/audio/",$track->file_type)){
                    $type = "audio";
                }
                
            }
            
            if($type == "video"){
                // movie
                $flash = '<!-- Begin Player -->';
                $results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$this->_category,'playlist') );
                if(isset($results[0]) && $results[0] != ''){
                    $flash .= $results[0];
                }
                $flash .= '<!-- End Player -->';
                    
            }elseif($type == "audio"){
                
                $flash = '<!-- Begin Player -->';
                $results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$this->_category,'playlist') );

                if(isset($results[0]) && $results[0] != ''){
                    $flash .= $results[0];
                }
                $flash .= '<!-- End Player -->';
            }
            $this->_category->flash = $flash;
            $this->_category->flash_id = $top_cat;

        }
        
        // free downloads
        if($params->get('my_free_downloads')){
            reset($tracks);
            foreach($tracks as $track){
                if(
                        (!$track->price["product_price"] || $track->price["product_price"] == "FREE")
                        || ($params->get('my_play_downloads') && in_array($track->id, $myOrders))
                        
                    ){
                    $track->free_download = 1;
                    $track->free_download_link = $track->download_path;
                    $track->free_download_link = "index.php?option=com_mymuse&view=store&task=downloadit&id=".$track->id;
                }else{
                    $track->free_download = 0;
                }
            }
        }else{
            $track->free_download = 0;
        }

        $res[0] = $tracks;
        $res[1] = $this->_category;
        $res[2] = $this->getPagination();
		return $res;
	}
    
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		jimport('joomla.html.pagination');
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit);

        $page->setAdditionalUrlParam('option', 'com_mymuse');
        $page->setAdditionalUrlParam('view', 'tracks');
        $page->setAdditionalUrlParam('layout', 'alphatunes');
        $page->setAdditionalUrlParam('id', $this->getState('category.id'));
        if($this->getState('filter_alpha','')){
            $page->setAdditionalUrlParam('filter_alpha', 'blue');
        }
        $Itemid           = JRequest::getVar('Itemid');
        if($Itemid){
            $page->setAdditionalUrlParam('Itemid',$Itemid);
        }
        if($this->getState('list.limit')){
            $page->setAdditionalUrlParam('limit', $this->getState('list.limit'));
        }
		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
    
	public function getStart()
	{
		return $this->getState('list.start');
	}
}