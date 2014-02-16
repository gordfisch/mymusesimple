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

jimport('joomla.application.component.modelitem');

/**
 * MyMuse Product Model
 *
 * @package		Joomla
 * @subpackage	MyMuse
 * @since 1.5
 */
class MyMuseModelProduct extends JModelItem
{

	
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_mymuse.product';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('product.id', $pk);

		$offset = JRequest::getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user		= JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_mymuse')) &&  (!$user->authorise('core.edit', 'com_mymuse'))){
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}




	/**
	 * Method to get product data.
	 *
	 * @param	integer	The id of the article.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		$this->populateState();
		$params = MyMuseHelper::getParams();
		$params->merge($this->state->get('params'));

		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {

			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select($this->getState(
					'item.select', 'a.id, a.asset_id, a.title, a.alias, a.title_alias, a.introtext, a.fulltext, ' .
					// If badcats is not null, this means that the article is inside an unpublished category
					// In this case, the state is set to 0 to indicate Unpublished (even if the article state is Published)
					'CASE WHEN badcats.id is null THEN a.state ELSE 0 END AS state, ' .
					' a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// use created if modified is 0
				'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified, ' .
					'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, ' .
					'a.list_image, a.detail_image, a.attribs, a.version, a.parentid, a.ordering, ' .
					'a.metakey, a.metadesc, a.access, a.hits, a.metadata, a.featured, a.language, a.product_physical, ' .
					'a.product_downloadable, a.product_sku, a.product_made_date, a.product_in_stock, a.product_discount, ' .
					'a.urls, a.price, a.reservation_fee, ' .
					'a.product_full_time, a.product_producer, a.product_publisher, a.product_studio'
					)
				);
				$query->from('#__mymuse_product AS a');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');
				
				// Join on country table.
				$query->select('co.country_name AS product_country');
				$query->join('LEFT', '#__mymuse_country AS co on co.country_2_code = a.product_country');
				
				
				// Join on user table.
				$query->select('u.name AS author');
				$query->join('LEFT', '#__users AS u on u.id = a.created_by');
		
				// Join on contact table
				$subQuery = $db->getQuery(true);
				$subQuery->select('contact.user_id, MAX(contact.id) AS id, contact.language');
				$subQuery->from('#__contact_details AS contact');
				$subQuery->where('contact.published = 1');
				$subQuery->group('contact.user_id, contact.language');
				$query->select('contact.id as contactid' );
				$query->join('LEFT', '(' . $subQuery . ') AS contact ON contact.user_id = a.created_by');
				
				
				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
					$query->where('(contact.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') OR contact.language IS NULL)');
				}
				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
				$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				// Join on voting table
				$query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count');
				$query->join('LEFT', '#__mymuse_product_rating AS v ON a.id = v.product_id');

				$query->where('a.id = ' . (int) $pk);

				// Filter by start and end dates.
				$nullDate = $db->Quote($db->getNullDate());
				$date = JFactory::getDate();

				$nowDate = $db->Quote($date->toSql());

				$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
				$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

				// Join to check for category published state in parent categories up the tree
				// If all categories are published, badcats.id will be null, and we just use the article state
				$subquery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
				$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
				$subquery .= 'WHERE parent.extension = ' . $db->quote('com_mymuse');
				$subquery .= ' AND parent.published <= 0 GROUP BY cat.id)';
				$query->join('LEFT OUTER', $subquery . ' AS badcats ON badcats.id = c.id');

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published)) {
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					throw new Exception($error);
				}

				if (empty($data)) {
					return JError::raiseError(404, JText::_('MYMUSE_PRODUCT_NOT_FOUND'));
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
					return JError::raiseError(404, JText::_('MYMUSE_PRODUCT_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadString($data->attribs);

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadString($data->metadata);
				$data->metadata = $registry;

				// Compute selected asset permissions.
				$user	= JFactory::getUser();

				// Technically guest could edit an article, but lets not check that to improve performance a little.
				if (!$user->get('guest')) {
					$userId	= $user->get('id');
					$asset	= 'com_mymuse_product.article.'.$data->id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset)) {
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by) {
							$data->params->set('access-edit', true);
						}
					}
				}

				// Compute view access permissions.
				if ($access = $this->getState('filter.access')) {
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else {
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null) {
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else {
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$data->price = $this->getPrice($data);
				if($params->get('my_add_taxes')){
					$data->price["product_price"] = MyMuseCheckout::addTax($data->price["product_price"]);
				}
				$this->_item[$pk] = $data;
			}
			catch (JException $e)
			{
				if ($e->getCode() == 404) {
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else {
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
			
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

			// TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS 
			// get child tracks with prices
			$track_query = "SELECT id,title,title_alias,introtext,`fulltext`, parentid, product_physical, product_downloadable, product_allfiles, product_sku,
			product_made_date, price, featured, product_discount, product_package_ordering, product_package,file_length,file_time,
			file_name,file_preview,file_preview_2, file_preview_3,file_type, detail_image,
			ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count
			FROM #__mymuse_product as a
			LEFT JOIN #__mymuse_product_rating AS v ON a.id = v.product_id
			WHERE parentid='".$pk."'
			AND product_downloadable = 1
			AND state=1
			ORDER BY ordering
			";

			$db->setQuery($track_query);
			$tracks = $db->loadObjectList();
		
			$artist_alias = MyMuseHelper::getArtistAlias($pk,'1');
			$album_alias = MyMuseHelper::getAlbumAlias($pk);
			$site_url = preg_replace("#administrator/#","",JURI::base());
			
			// set up flash previews and streams
		
			$this->_item[$pk]->flash = '';
			$this->_item[$pk]->flash_type = '';
			if(count($tracks)){
				$root = JPATH_ROOT.DS;
				while (list($i,$track)= each( $tracks )){
					$tracks[$i]->price = $this->getPrice($track);
					if($params->get('my_add_taxes')){
						$tracks[$i]->price["product_price"] = MyMuseCheckout::addTax($tracks[$i]->price["product_price"]);
					}
					
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
					
					if($this->_item[$pk]->flash_type != "mix"){
						if($this->_item[$pk]->flash_type == "audio" && $track->file_type == "video"){
							//oh christ it's a mix
							$this->_item[$pk]->flash_type = "mix";
							$track->flash_type = "mix";
						}elseif($this->_item[$pk]->flash_type == "video" && $track->file_type == "audio"){
							//oh christ it's a mix
							$this->_item[$pk]->flash_type = "mix";
							$track->flash_type = "mix";
						}else{
							$this->_item[$pk]->flash_type = $track->file_type;
							$track->flash_type = $track->file_type;
						}
					}else{
						$track->flash_type = "mix";
					}
				}

				$dispatcher	= JDispatcher::getInstance();
				if($params->get('product_player_type') == "each" || 
					$params->get('product_player_type') == "single"){
					reset($tracks);
					$count = count($tracks);
					while (list($i,$track)= each( $tracks )){
						$flash = '';
						$track->purchased = 0;
						
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
					// make a controller for the play/pause buttons
					$results = $dispatcher->trigger('onPrepareMyMuseMp3PlayerControl',array(&$tracks) );
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
							$this->_item[$pk]->flash = $flash;
							$this->_item[$pk]->flash_id = $track->id;
							if($this->_item[$pk]->flash_type != "mix"){
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
					$this->_item[$pk]->previews = array();
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
						$this->_item[$pk]->previews[] = $track;
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
						$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$this->_item[$pk],'playlist') );
						if(isset($results[0]) && $results[0] != ''){
							$flash .= $results[0];
						}
						$flash .= '<!-- End Player -->';
							
					}elseif($type == "audio"){
						
						$flash = '<!-- Begin Player -->';
						$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$this->_item[$pk],'playlist') );

						if(isset($results[0]) && $results[0] != ''){
							$flash .= $results[0];
						}
						$flash .= '<!-- End Player -->';
					}
					$this->_item[$pk]->flash = $flash;
					$this->_item[$pk]->flash_id = $pk;

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
				
				
			} 
			

			$this->_item[$pk]->tracks = $tracks;

			// get child items with prices
			$query = "SELECT * FROM #__mymuse_product as p
			WHERE p.parentid='".$pk."'
			AND product_downloadable = 0
			and state=1
			ORDER BY ordering
			";
			$db->setQuery($query);
			$items = $db->loadObjectList();

			
			//attributes
			$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE 
			product_parent_id='.$this->_item[$pk]->id.'
			ORDER BY ordering';
			$db->setQuery($query);
			$this->_item[$pk]->attribute_sku = $db->loadObjectList();
	
			while (list($i,$item)= each( $items )){
				foreach($this->_item[$pk]->attribute_sku as $a_sku){
					$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$item->id.'
					AND product_attribute_sku_id='.$a_sku->id;
					$db->setQuery($query);
					$items[$i]->attributes[$a_sku->name] = $db->loadResult();
				}
	
					
				$query = 'SELECT a.*,b.name from #__mymuse_product_attribute as a 
				LEFT JOIN #__mymuse_product_attribute_sku as b on b.id=a.product_attribute_sku_id
				WHERE a.product_id='.$item->id;
				
				$db->setQuery($query);
				$tmp[$item->id] = $db->loadObjectList();
				foreach($tmp[$item->id] as $att){
					$attributes[$item->id][$att->product_attribute_sku_id] = $att;
				}

				$items[$i]->price = $this->getPrice($item);
				if($params->get('my_add_taxes')){
					$items[$i]->price["product_price"] = MyMuseCheckout::addTax($items[$i]->price["product_price"]);
				}
				

			}
			
			
			if(count($items) && $params->get('product_item_selectbox',0)){ 
				
				$newitems = array();
				$titles = array();
				foreach($items as $i => $item){
					//group by title
					$titles[strtolower($item->title)] = $item->title;
				}
				$i = 0;
				foreach($titles as $title){
					$newitem = new stdClass;
					$newitem->title = $title;
					$hidden = '';
					$newitem->pidselect=$i;
					$newitem->select = '<select name="productid[]" id="pidselect'.$i.'"';
					$i++;
					if($params->get('product_show_quantity')){
						$newitem->select .= 'onchange="updateq('.$item->id.')"';
					}
					$newitem->select .= '>';
					$newitem->select .= '<option value="">'.JText::_("MYMUSE_SELECT").'</option>
					';
					foreach($items as $item){
						if($item->title == $title){
					
							$newitem->select .= '<option value="'.$item->id.'">'.$item->title.": ";
							foreach($this->_item[$pk]->attribute_sku as $a_sku){
								$newitem->select .= $item->attributes[$a_sku->name]." ";
							}
							$newitem->select .= MyMuseHelper::printMoneyPublic($item->price).'</option>
							';
							$hidden .= '<input type="hidden" name="quantity['.$item->id.']" value="1"
							id="quantity'.$item->id.'">
							';
						}
					}
					$newitem->select .=  '</select>';
					$newitem->select .= $hidden;
					$newitems[] = $newitem;
				}

				$items = $newitems;
			}		

					
			$this->_item[$pk]->items = $items;
			
			//get maincategory
			$query = "SELECT * from #__categories WHERE id='".$this->_item[$pk]->catid."'";
			$db->setQuery($query);
			$this->_item[$pk]->artist = $db->loadObject();
			

			$this->_item[$pk]->artist->link = myMuseHelperRoute::getCategoryRoute($this->_item[$pk]->catid);
			
		}

		return $this->_item[$pk];
	}

	/**
	 * Method to increment the hit counter for the product
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function hit()
	{

		if ($this->getState('product.id'))
		{
			$db = JFactory::getDBO();
			$query = "UPDATE #__mymuse_product set hits = hits+1 WHERE id=".(int) $this->getState('product.id');
			$db->setQuery($query);
			$db->query();
			return true;
		}
		return false;
	}



	/**
	 * Method to load mymuse_product product parameters
	 *
	 * @access	private
	 * @return	void

	 */
	function _loadProductParams()
	{
		$mainframe = JFactory::getApplication();

		// Get the page/component configuration
		$params = clone($mainframe->getParams('com_mymuse'));

		// Merge product parameters into the page configuration
		$aparams = new JRegistry($this->_item[$pk]->attribs);
		$params->merge($aparams);

		// Set the popup configuration option based on the request
		$pop = JRequest::getVar('pop', 0, '', 'int');
		$params->set('popup', $pop);

		// Are we showing introtext with the product
		if (!$params->get('show_intro') && !empty($this->_item[$pk]->fulltext)) {
			$this->_item[$pk]->text = $this->_item[$pk]->fulltext;
		} else {
			$this->_item[$pk]->text = $this->_item[$pk]->introtext . chr(13).chr(13) . $this->_item[$pk]->fulltext;
		}

		// Set the product object's parameters
		$this->_item[$pk]->parameters = & $params;
	}


	
	/**
     * getAttributes
     * 
     * @param int $item_id
     * @param int $product_id
     * @param string $attribute_name
     * @return object
     */
  function getAttributes($item_id="",$product_id="",$attribute_name="") {

  	$db = JFactory::getDBO();
    if ($item_id and $product_id) {
      $q  = "SELECT * FROM #__mymuse_product_attribute as pa, #__mymuse_product_attribute_sku as pas  \n";
      $q .= "WHERE pa.product_id = '$item_id'  \n";
      $q .= "AND pas.product_parent_id =$product_id  \n";
      if ($attribute_name) {
        $q .= "AND pa.attribute_name"." = '$attribute_name'  \n";
      }
      $q .= "AND pa.product_attribute_sku_id=pas.id  \n";
      $q .= "ORDER BY ordering, pa.attribute_name  \n";
    } elseif ($item_id) {
      $q  = "SELECT * FROM #__mymuse_product_attribute  \n";
      $q .= "WHERE product_id = $item_id ";
      if ($attribute_name) {
        $q .= "AND attribute_name = '$attribute_name'  \n";
      }
    } elseif ($product_id) {
      $q  = "SELECT * FROM #__mymuse_product_attribute_sku  \n";
      $q .= "WHERE product_parent_id =$product_id  \n";
      if ($attribute_name) {
        $q .= "AND #__mymuse_product_attribute.attribute_name = '$attribute_name'  \n";
      }
      $q .= " ORDER BY ordering,attribute_name \n";
      //$q .= " ORDER BY attribute_list ";
    } else {
      $this->error = JText::_("MYMUSE_ERROR_GET_ATTRIBUTE");
      return false;
    }

	$db->setQuery($q);
	$res = $db->loadObjectList();
    return $res;
  }
	
   /**
     * getPrice
     * 
     * @param object $product
     * @return mixed Array or false: array [product_price] [special_shopper_group] [product_discount] [product_shopper_group_discount]
     */
	static function getPrice(&$product) {

		$params 		= MyMuseHelper::getParams();
		$MyMuseShopper 	= MyMuse::getObject('shopper','models');
		$shopper 		= $MyMuseShopper->getShopper();

		$db	= JFactory::getDBO();
		$shopper_group_discount = 0;
		$discount = 0;
		$price_info = array();
		$price_info["item"]=false;

		$default_shopper_group_id = $params->get("my_default_shopper_group_id",1);
		$product_id = $product->id;
		if(is_array($product->price)){
			// we've been here already
			return $product->price;
		}else{
			$product_price = $product->price;
		}
		
			
		// see if this product has a discount
		$discount = $product->product_discount;

		
		// Get the shopper group id for this shopper
		$shopper_group_id = @$shopper->shopper_group->id;
		if($shopper_group_id == ""){
			$shopper_group_id = $default_shopper_group_id;
			$q = "SELECT * FROM #__mymuse_shopper_group WHERE  \n";
			$q .= "id='";
			$q .= $shopper_group_id . "'";
			//echo $q;
			$db->setQuery($q);
			$shopper->shopper_group = $db->loadObject();
		}
		$shopper_group_discount = $shopper->shopper_group->discount;

		// Get the product_parent_id for this product/item
		$product_parent_id = 0;
		if(isset($product->parentid)){
			$product_parent_id = $product->parentid;
			if($product_parent_id > 0){
				$price_info["item"]=true;
			}
		}

		// DEBUG
		//echo "product:$product_id product_price: $product_price discount:$discount, shopper group id = $shopper_group_id, shopper group discount = $shopper_group_discount<BR>";
		//print_pre($product_price);

		// Getting prices
		//
		// If the shopper group has a price then show it, otherwise
		// show the default price.


		// IT'S FOR A SPECIAL SHOPPER GROUP?
		if($shopper_group_id != $default_shopper_group_id){

			if ($product_price && $product_price > 0) {
				$price_info["special_shopper_group"] = True;
				$price_info["product_original_price"] = $product_price;
				$price_info["product_price"] = $product_price - ($product_price*$shopper_group_discount/100)-$discount;
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_discount"] = $discount;
				$price_info["product_shopper_group_discount"] = $shopper_group_discount;
				$price_info["product_shopper_group_discount_amount"] = $product_price*$shopper_group_discount/100;
				
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_shopper_group_discount_amount"] = round($price_info["product_shopper_group_discount_amount"],2);
				
				return $price_info;
			}
		}


		// MAYBE IT'S AN ITEM, FIRST SEE IF IT HAS ITS OWN PRICE
		if(isset($product_parent_id) && $product_parent_id > 0){

			//if (isset($product_price) && $product_price > 0) {
			if (isset($product_price)) {
				$price_info["product_original_price"] = $product_price;
				$price_info["product_price"]=$product_price - ($product_price*$shopper_group_discount/100)-$discount;
				$price_info["product_discount"] = $discount;
				$price_info["product_shopper_group_discount"] = $shopper_group_discount;
				$price_info["product_shopper_group_discount_amount"] = sprintf("%.2f",$product_price*$shopper_group_discount/100);
				//$price_info["product_price"] = sprintf("%.2f",$price_info["product_price"]);
				if($price_info["product_price"] == 0.00){
					$price_info["product_price"] = 0;
				}
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_shopper_group_discount_amount"] = round($price_info["product_shopper_group_discount_amount"],2);
				
				return $price_info;
			}
		}

		 
		// Get default price
		if ($product_price && $product_price > 0) {
			$price_info["default"] = True;
			$price_info["product_original_price"] = $product_price;
			$price_info["product_price"]= $product_price - ($product_price * $shopper_group_discount/100) - $discount;
			$price_info["product_discount"] = $discount;
			$price_info["product_shopper_group_discount"] = $shopper_group_discount;
			$price_info["product_shopper_group_discount_amount"] = sprintf("%.2f",$product_price*$shopper_group_discount/100);
			
			$price_info["product_price"] = round($price_info["product_price"],2);
			$price_info["product_shopper_group_discount_amount"] = round($price_info["product_shopper_group_discount_amount"],2);
			
			return $price_info;
		}


		
		// No price found, must be FREE
		$price_info["default"] = True;
		$price_info["product_original_price"] = $product_price;
		$price_info["product_price"]=$product_price - ($product_price*$shopper_group_discount/100)-$discount;
		$price_info["product_discount"] = $discount;
		$price_info["product_shopper_group_discount"] = $shopper_group_discount;
		$price_info["product_shopper_group_discount_amount"] = sprintf("%.2f",$product_price*$shopper_group_discount/100);
		
		$price_info["product_price"] = round($price_info["product_price"],2);
		$price_info["product_shopper_group_discount_amount"] = round($price_info["product_shopper_group_discount_amount"],2);
		
		return $price_info;
		
  	}
  	
  	
  	public function storeVote($pk = 0, $rate = 0)
  	{
  		
  		if ( $rate >= 1 && $rate <= 5 && $pk > 0 )
  		{
  			$userIP = $_SERVER['REMOTE_ADDR'];
  			$db = JFactory::getDbo();
  			$query = 'SELECT *' .
  					' FROM #__mymuse_product_rating' .
  					' WHERE product_id = '.(int) $pk;
  			$db->setQuery($query);

  			$rating = $db->loadObject();

  			if (!$rating)
  			{
  				// There are no ratings yet, so lets insert our rating
  				$query = 'INSERT INTO #__mymuse_product_rating ( product_id, lastip, rating_sum, rating_count )' .
  						' VALUES ( '.(int) $pk.', '.$db->Quote($userIP).', '.(int) $rate.', 1 )';
  				$db->setQuery($query);

  				if (!$db->query()) {
  					$this->setError($db->getErrorMsg());
  					return false;
  				}
  			} else {
  				if ($userIP != ($rating->lastip))
  				{
  					
  					
  					$query = 		'UPDATE #__mymuse_product_rating' .
  							' SET rating_count = rating_count + 1, rating_sum = rating_sum + '.(int) $rate.', lastip = '.$db->Quote($userIP) .
  							' WHERE product_id = '.(int) $pk;
  					$db->setQuery($query);
  					if (!$db->query()) {
  						$this->setError($db->getErrorMsg());
  						return false;
  					}
  				} else {
  					return false;
  				}
  			}
  			return true;
  		}
  		JError::raiseWarning( 'SOME_ERROR_CODE', JText::sprintf('MYMUSE_INVALID_RATING', $rate), "MyMuseModelProduct::storeVote($rate)");
  		return false;
  	}
}
	
