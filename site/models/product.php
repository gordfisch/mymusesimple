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
	
	static $cart = null;

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
		$jinput = $app->input;

		// Load state from the request.
		$pk = $jinput->get('id');
		$this->setState('product.id', $pk);

		$offset = $jinput->get('limitstart');
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
	 * @param	integer	The id of the product.
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
					'item.select', 'a.id, a.asset_id, a.title, a.alias, a.artistid, a.title_alias, a.introtext, a.fulltext, ' .
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
					'a.urls, a.price, a.reservation_fee, a.product_allfiles, ' .
					'a.product_full_time, a.product_producer, a.product_publisher, a.product_studio'
					)
				);
				$query->from('#__mymuse_product AS a');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');
				
				// Join on category table for artist .
				$query->select('art.title AS artist_title, art.alias AS artist_alias, art.access AS artist_access');
				$query->join('LEFT', '#__categories AS art on art.id = a.artistid');
				
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
				$data->attribs = $registry;

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadString($data->metadata);
				$data->metadata = $registry;

				// Compute selected asset permissions.
				$user	= JFactory::getUser();

				// Technically guest could edit a product, but lets not check that to improve performance a little.
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
			
			//other cats
			$othercats = array();
			$query = "SELECT c.title FROM #__mymuse_product_category_xref as x
					LEFT JOIN #__categories as c ON c.id=x.catid
				WHERE product_id = '".$pk."' AND catid != ".$this->_item[$pk]->catid." 
						AND catid !=".$this->_item[$pk]->artistid;
			$db->setQuery($query);
			$res = $db->loadObjectList();
			if(count($res)){
				foreach($res as $r){
					$othercats[] = $r->title;
				}
			}
			$othercats = array_unique($othercats);
			$this->_item[$pk]->othercats = $othercats;

			
			// TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS 
			// get child tracks with prices
			$track_query = "SELECT id,title,title_alias,introtext,`fulltext`, parentid, catid, product_physical, product_downloadable, product_allfiles, product_sku,
			product_made_date, price, featured, product_discount, product_package_ordering, product_package,file_length,file_time,
			file_name,file_downloads, file_preview,file_preview_2, file_preview_3,file_type, detail_image,access,
			ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count, s.sales
			FROM #__mymuse_product as a
			LEFT JOIN #__mymuse_product_rating AS v ON a.id = v.product_id
			LEFT JOIN (SELECT sum(quantity) as sales, x.product_name, x.product_id FROM
        		(SELECT sum(i.product_quantity) as quantity, i.product_id, p.parentid,
        		i.product_name, product_id as all_id
        		FROM #__mymuse_order_item as i
        		LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
        		GROUP BY i.product_id )
        		as x GROUP BY x.all_id) as s ON s.product_id = a.id
			WHERE parentid='".$pk."'
			AND product_downloadable = 1
			AND state=1
			ORDER BY ordering
			";

			$db->setQuery($track_query);
			$tracks = $db->loadObjectList();
	
			$site_url = MyMuseHelper::getSiteUrl($pk,'1');
			$site_path = MyMuseHelper::getSitePath($pk,'1');
						
			// set up previews and streams
			$this->_item[$pk]->flash = '';
			$this->_item[$pk]->flash_type = '';
			$preview_tracks = array();
			if(count($tracks)){
				$root = JPATH_ROOT.DS;
				while (list($i,$track)= each( $tracks )){
					if($name = json_decode($track->file_name)){
						if(isset($name[0]->file_length)){
							$track->file_length = $name[0]->file_length;
						}
					}
					//other cats
					$othercats = array();
					$query = "SELECT c.title FROM #__mymuse_product_category_xref as x
					LEFT JOIN #__categories as c ON c.id=x.catid
					WHERE product_id = '".$track->id."' AND catid != ".$track->catid;
					$db->setQuery($query);
					
					if($res = $db->loadObjectList()){
						foreach($res as $r){
							$othercats[] = $r->title;
						}
					}
					$othercats = array_unique($othercats);
					$track->othercats = implode(', ',$othercats);
					
					
					$tracks[$i]->price = $this->getPrice($track);

					if($params->get('my_add_taxes')){
						$tracks[$i]->price["product_price"] = MyMuseCheckout::addTax($tracks[$i]->price["product_price"]);
					}
					
					$jason = json_decode($track->file_name);
					if(is_array($jason)){
						$track->file_name = $jason;
					}
					
					if($params->get('my_encode_filenames')){
						$track->download_name = $track->title_alias;
					}else{
						$track->download_name = $track->file_name;
					}
					
					//TO DO work with formats
					//get download file NOTE NOT available while using Amazon s3
					if(!$params->get('my_use_s3',0) && !is_array($track->file_name)){
						$down_dir = str_replace($root,'',$params->get('my_download_dir'));
						$track->download_path = $site_url.$track->download_name;
						$track->download_real_path = $site_path.$track->download_name;
							
						if((!$track->price["product_price"] || $track->price["product_price"] == "FREE")
								&& $params->get('my_play_downloads')){
							$track->file_preview = 1;
						}
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
		
					if($track->file_preview){
						$preview_tracks[] = $track;
					}else{
						$track->flash= '';
					}

					
				}
			
				$dispatcher	= JDispatcher::getInstance();
				if(count($preview_tracks) && ($params->get('product_player_type') == "each" || 
					$params->get('product_player_type') == "single")){
					reset($preview_tracks);
					$count = count($preview_tracks);
					while (list($i,$track) = each( $preview_tracks )){
						$flash = '';
						$track->purchased = 0;
						
						if($track->file_preview){

							//echo $site_url." ".$track->file_preview."<br />";
							$track->path = $site_url.$track->file_preview;
							$track->real_path = $site_path.$track->file_preview;
						
							if($track->file_preview_2){
								$track->path_2 = $site_url.$track->file_preview_2;
								$track->real_path_2 = $site_path.$track->file_preview_2;
							}
							if($track->file_preview_3){
								$track->path_3 = $site_url.$track->file_preview_3;
								$track->real_path_3 = $site_path.$track->file_preview_3;
							}
							
							//should we use the real download file? Not available in AmazonS3
							if(!$params->get('my_use_s3')){
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
							}
							//audio or video?
							
							$ext = MyMuseHelper::getExt($track->file_preview);
							$flash = '<!-- Begin Play -->';
							if(substr_count($track->file_type,"video")){
								//movie
								
								$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$track,$params->get('product_player_type'),0,0,$i, $count) );
								if(is_array($results) && isset($results[0]) && $results[0] != ''){
									$flash .= $results[0];
								}
								
							}elseif(substr_count($track->file_type,"audio")){
								//audio
								
								$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$track,$params->get('product_player_type'),0,0,$i, $count));
								if(is_array($results) && isset($results[0]) && $results[0] != ''){
									$flash .= $results[0];
								}
								
							}
							$flash .= '<!-- End Play -->';

						}else{
							$flash = '';
						}
						$track->flash = $flash;

					}//end for each track
				}
				
				
				if(count($preview_tracks) && $params->get('product_player_type') == "single"){
					// make a controller for the play/pause buttons
					$results = $dispatcher->trigger('onPrepareMyMuseMp3PlayerControl',array(&$preview_tracks) );					
				
					//get the player itself
					reset($preview_tracks);
					$flash = '';
					$audio = 0;
					$video = 0;
					foreach($preview_tracks as $track){
						if($track->file_preview){
							$flash .= '<!-- Begin Player -->';
							if(substr_count($track->file_type,"video") && !$video){
								//movie

								$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$track,'singleplayer') );
								
								if(is_array($results) && isset($results[0]) && $results[0] != ''){
									$flash .= $results[0];
								}
							
								$video = 1;
									
							}elseif(substr_count($track->file_type,"audio") && !$audio){
								//audio
								$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$track,'singleplayer') );

								if(is_array($results) && isset($results[0]) && $results[0] != ''){
									$flash .= $results[0];
								}
								$audio = 1;
							}
							$flash .= '<!-- End Player -->';
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
				
				if(count($preview_tracks) && $params->get('product_player_type') == "playlist"){
					//get the main flash for the product
			
					reset($preview_tracks);
					$this->_item[$pk]->previews = array();
					$audio = 0;
					
					$i = 0;
					$type = "";
					foreach($preview_tracks as $track){
						if($track->file_preview){
							$track->path .= $site_url.$track->file_preview;
						}

						if($track->file_preview_2){
							$track->path_2 = $site_url.$track->file_preview_2;
						}
						if($track->file_preview_3){
							$track->path_3 = $site_url.$track->file_preview_3;
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
				if(isset($tracks) && $params->get('my_free_downloads')){
					reset($tracks);
					foreach($tracks as $track){
						if(
								(!$track->price["product_price"] || $track->price["product_price"] == "FREE")
								|| ($params->get('my_play_downloads') && in_array($track->id, $myOrders))
								
							){
							$track->free_download = 1;
							//$track->free_download_link = $track->download_path;
							$track->free_download_link = "index.php?option=com_mymuse&view=store&task=downloadit&id=".$track->id;
							if($track->access > 1 && !$user->get('id')){
								$view = $params->get('my_registration_redirect', 'login');
								$track->free_download_link = "index.php?option=com_users&view=$view";
							}
						}else{
							$track->free_download = 0;
						}
					}
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
		$app = JFactory::getApplication();
		$jinput = $app->input;

		// Get the page/component configuration
		$params = clone($app->getParams('com_mymuse'));

		// Merge product parameters into the page configuration
		$aparams = new JRegistry($this->_item[$pk]->attribs);
		$params->merge($aparams);

		// Set the popup configuration option based on the request
		$pop = $jinput->get('pop', 0, '', 'int');
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
		// Get the product_parent_id for this product/item
		$product_parent_id = 0;
		if(isset($product->parentid)){
			$product_parent_id = $product->parentid;
			if($product_parent_id > 0){
				$price_info["item"]=true;
			}
		}  
		
		
		
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


			if(0 == $params->get('my_price_by_product') || $product->product_physical ){ 
				//price by track
				$price_info["product_price"] = $product->price;
				
			}elseif(1 == $params->get('my_price_by_product')){ 
				//price by product
				if($product->parentid > 0){
					$query = "SELECT attribs FROM #__mymuse_product WHERE id='".$product->parentid."'";
					$db->setQuery($query);
					if(!$product->attribs = $db->loadResult()){
						$price_info["product_price"] = $product_price = $product->price;
					}
				}
				$registry = new JRegistry;
				$registry->loadString($product->attribs);
				$product->attribs = $registry;
				if($product->product_physical){
					$key = 'product_price_physical';
					$product->price = $product->attribs->get($key);
					$price_info["product_price"] = $product->price;
				}elseif($product->product_allfiles && isset($product->ext)){
					$key = 'product_price_'.$product->ext.'_all';
					$product->price = $product->attribs->get($key);
					$price_info["product_price"] = $product->price;
				}elseif(isset($product->ext)){
					$key = 'product_price_'.$product->ext;
					$product->price = $product->attribs->get($key);
					$price_info["product_price"] = $product->price;
				}else{
					$price_info["product_price"] = $product->price;
				}
				$product_price = $product->price;
				$price_info["product_shopper_group_discount"] = $shopper_group_discount;
				$price_info["product_shopper_group_discount_amount"] = $product_price*$shopper_group_discount/100;
				
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_shopper_group_discount_amount"] = round($price_info["product_shopper_group_discount_amount"],2);
				
				
				return $price_info;
				
			}elseif(2 == $params->get('my_price_by_product') && 1 != $product->product_physical){ 
				//price by licence
				
				$session = JFactory::getSession();
				$jinput = JFactory::getApplication()->input;
				$my_licence  = $jinput->get('my_licence', $session->get("my_licence",0));
				if (!$session->get("cart",0)) {
					self::$cart = array();
					self::$cart["idx"] = 0;
				}else{
					self::$cart = $session->get("cart");
				}

				

				$session = JFactory::getSession();
				$my_licence = $jinput->get('my_licence',$session->get("my_licence",0));
		
				$price_info["product_price"] = $params->get('my_license_'.$my_licence.'_price');
				for ($i = 0; $i < 5; $i++){
					if(null != $params->get('my_license_'.$i.'_name')
							&& null != $params->get('my_license_'.$i.'_price')){
						$price_info['licence'][$i]['name'] = $params->get('my_license_'.$i.'_name');
						$price_info['licence'][$i]['price'] = $params->get('my_license_'.$i.'_price');
						
					}
				}
				$product->price = $price_info["product_price"];
				$product_price = $product->price;
				$price_info["product_shopper_group_discount"] = $shopper_group_discount;
				$price_info["product_shopper_group_discount_amount"] = $product_price*$shopper_group_discount/100;
				
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_shopper_group_discount_amount"] = round($price_info["product_shopper_group_discount_amount"],2);
				
				
				//DISCOUNTS FROM PLUGINS
				JPluginHelper::importPlugin('mymuse');
				$dispatcher	= JDispatcher::getInstance();
				//echo "here";
				$result = $dispatcher->trigger('onCalculatePrice', array(&$price_info, self::$cart));
				if(count($result)){
					//print_pre($price_info);
				}
				
				
				return $price_info;
			}
			
		//}
		$product_price = $product->price;
			
		// see if this product has a discount
		$discount = $product->product_discount;

		
		

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
				//print_pre($price_info);
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
			//print_pre($price_info); 
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
  	
  	/**
  	 * getRecommended
  	 */
  	function getRecommended()
  	{
  		$db 		= JFactory::getDBO();
  		$params 	= MyMuseHelper::getParams();
  		$prods 		= array();
  		$recommends = array();
  		$productid 	= $this->getState('product.id');
  		$product 	= $this->_item[$productid];
  		$cats[]		= $product->catid;
 
  		$query = "SELECT * FROM #__mymuse_product_recommend_xref
				WHERE product_id = '".$productid."'";
  		$db->setQuery($query);
  		$res = $db->loadObjectList();
  		if(count($res)){
  			foreach($res as $r){
  				$cats[] = $r->recommend_id;
  			}
  		}
	
  		//other cats
  		$query = "SELECT * FROM #__mymuse_product_category_xref
				WHERE product_id = '".$productid."'";
  		$db->setQuery($query);
  		$res = $db->loadObjectList();
  		if(count($res)){
  			foreach($res as $r){
  				$cats[] = $r->catid;
  			}
  		}
  		$cats = array_unique($cats);
  		$catsin = implode(",",$cats);
  		
  		
  		
  		//get the products
  		$query = "SELECT id, title, catid, list_image, product_made_date FROM #__mymuse_product
				WHERE catid IN ($catsin) AND parentid = 0
  		AND id != $productid
  		ORDER BY FIELD(catid, $catsin), product_made_date DESC 
  		LIMIT ".$params->get('my_max_recommended');
  		$db->setQuery($query);
  		$recommends = $db->loadObjectList();

  		//$num = min($params->get('my_max_recommended'),count($prods));
  	
  		for($i = 0; $i<count($recommends); $i++){
  			$recommends[$i]->url = myMuseHelperRoute::getProductRoute ( $recommends[$i]->id, $recommends[$i]->catid );
  			$recommends[$i]->cat_url = myMuseHelperRoute::getCategoryRoute ( $recommends[$i]->catid  );
  		}

  		return $recommends;
  	}
  	 
}
