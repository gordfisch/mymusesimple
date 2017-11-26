<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

class myMuseViewStore extends JViewLegacy
{
	protected $state = null;
	protected $item = null;
	protected $items = null;
	protected $pagination = null;
	protected $params;

	protected $lead_items = array();
	protected $intro_items = array();
	protected $link_items = array();
	protected $columns = 1;

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{
		
		$jinput 	= JFactory::getApplication()->input;
		$task 		= $jinput->get('task','');
		$state 		= $this->get('State');
		$store 		= $this->get('Store');
		$params 	= MyMuseHelper::getParams();
		$this->params = $params;
		$this->params->merge($state->params);
		$Itemid 	= $jinput->get('Itemid');
		$user 		= JFactory::getUser();

		// Present a list of downloadable files
        if($task == "downloads"){
        	
        	// make sure we have an id
        	$id = $jinput->get('id',0);
        	if(!$id){
        		$message = JText::_('MYMUSE_NO_DOWNLOAD_KEY');
        		$this->assignRef( 'message', $message );
        		$tpl = "message";
        		parent::display($tpl);
        		return false;
        	}

        	$db	= JFactory::getDBO();
        	$query = "SELECT * FROM #__mymuse_order WHERE order_number = '$id'";
        	$db->setQuery($query);
        	$row = $db->loadObject();

        	//Make sure we have an order
        	if(!$row){
        		$message = JText::_('MYMUSE_NO_MATCHING_ORDER');
        		$this->assignRef( 'message', $message );
        		$tpl = "message";
        		parent::display($tpl);
        		return false;
        	}
        	
        	// make sure it's the same person who ordered!
        	$MyMuseShopper 	=& MyMuse::getObject('shopper','models');
			$shopper = $MyMuseShopper->getShopper();
        	if($row->user_id != $MyMuseShopper->_shopper->user_id){
        		$message = JText::_('MYMUSE_USER_ORDER_OWNER_MISMATCH');
        		if($params->get('my_debug')){
        				$message .= $row->user_id.' : '.$MyMuseShopper->_shopper->user_id;
        		}
        		$this->assignRef( 'message', $message );
        		$tpl = "message";
        		parent::display($tpl);
        		return false;
        	}
        	
        	//if it is a reload, update the database
        	$item_id = $jinput->get('item_id',0);

        	$jinput->set('layout','store');
        	$jinput->set('view','store');
			$MyMuseCheckout 		=& MyMuse::getObject('checkout','helpers');
			$MyMuseCart 			=& MyMuse::getObject('cart','helpers');
        	$MyMuseShopper->order 	= $MyMuseCheckout->getOrder($row->id);

        	for($i = 0; $i < count($MyMuseShopper->order->items); $i++){
        		if($MyMuseShopper->order->items[$i]->file_name == ''){
        			// see if it's an 'ALL FILES
        			$query = "SELECT product_allfiles from #__mymuse_product 
        			WHERE id='".$MyMuseShopper->order->items[$i]->product_id."'";
        			$db->setQuery($query);
        			$all_files = $db->loadResult();
        			if($all_files){
        				unset($MyMuseShopper->order->items[$i]);
        			}
        		}
        	}
        	
        	if($params->get('my_registration') == "no_reg"){
        		$current =  JRoute::_("index.php?option=com_mymuse&view=cart&task=accdownloads&id=".$id."&item_id=");
        	}else{
				$current =  JRoute::_("index.php?option=com_mymuse&view=cart&task=downloads&id=".$id."&item_id=");
        	}
			
			$this->assignRef( 'current', $current );
        	$this->assignRef( 'order', $MyMuseShopper->order );
        	$this->assignRef( 'params', $params );
        	$this->assignRef( 'id', $id );
        	
        	$tpl = "downloads";
        	parent::display($tpl);
        	return true;
        }
        
        
        // trying to download a file!!
        if($task == "downloadfile"){
        	$jinput 	= JFactory::getApplication()->input;
        	$id = $jinput->get('id',0);

        	// make sure we have a download key
        	if(!$id){
        		$message = JText::_('MYMUSE_NO_DOWNLOAD_KEY');
        		$jinput->set('msg',$message);
        		return false;
        	}
        	
        	// make sure we have an order item id
        	$item_id = $jinput->get('item_id',0);
        	if(!$item_id){
        		$message = JText::_('MYMUSE_NO_ORDERITEM_ID');
        		$jinput->set('msg',$message);
        		return false;
        	}

        	
        	$db	= JFactory::getDBO();
			$query = "SELECT * FROM #__mymuse_order WHERE order_number = '$id'";
			$db->setQuery($query);
			$row = $db->loadObject();
		
			//Make sure we have an order
        	if(!$row){
        		$message = JText::_('MYMUSE_NO_MATCHING_ORDER ');
        		$jinput->set('msg',$message);
        		return false;
        	}
	
        	// make sure it's the same person who ordered!
        	$MyMuseShopper 	=& MyMuse::getObject('shopper','models');
        	$user = JFactory::getUser($MyMuseShopper->_shopper->user_id);
        	$user_id = $user->get('id');

        	if(isset($user->first_name) && isset($user->last_name) ){
        		$user->set('name', $user->first_name.' '.$user->last_name);
        	}

        	if($row->user_id != $MyMuseShopper->_shopper->user_id){
        		$message = JText::_('MYMUSE_USER_ORDER_OWNER_MISMATCH');
        		$jinput->set('msg',$message);
        		return false;
        	}
        	
        	//are we using no-registration?
        	if($params->get('my_registration') == "no_reg"){
				$fields = MyMuseHelper::getNoRegFields();
				
				$registry = new JRegistry;
				$registry->loadString($row->notes);
				foreach($fields as $field){
					if($registry->get($field)){
						$user->profile[$field] = $registry->get($field);
						//echo $field." ".$registry->get($field)."<br />";
					}else{
						$user->profile[$field] = '';
					}
				}
				if(isset($user->profile['first_name'])){
					$user->name = $user->profile['first_name']." ".@$user->profile['last_name'];
				}
				if($user->profile['email']){
					$user->email = $user->profile['email'];
				}
			}
			
        	//make sure the order is confirmed
        	if(! $row->order_status == $this->params->get('my_download_enable_status'))
        	{
        		$message = JText::_('MYMUSE_USER_ORDER_NOT_CONFIRMED');
        		$jinput->set('msg',$message);
        		return false;
        	}
	
        	$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
			$MyMuseCart 	=& MyMuse::getObject('cart','helper');
			
        	$order = $MyMuseCheckout->getOrder($row->id);
     
        	for($i = 0; $i < count($order->items); $i++){
        		if($order->items[$i]->id == $item_id){
        			$order_item = $order->items[$i];
        		}
        	}
        	
        	// check number of downloads
        	if($params->get('my_download_max') && intval($order_item->downloads) >= $params->get('my_download_max')){
        		$message = JText::_('MYMUSE_MAX_NUMBER_OF_DOWNLOADS_REACHED');
        		$jinput->set('msg',$message);
        		return false;
        	}
        			
        	// check expiry date
        	if($order_item->end_date 
        			&& $order_item->end_date <= time() 
        			&& $prarams->get('my_download_expire') != "-"){
        		$message = JText::_('MYMUSE_DOWNLOAD_EXPIRED');
        		$jinput->set('msg',$message);
        		return false;
        	}

        	//get product and other vars
			$query = "SELECT * FROM #__mymuse_product WHERE id = '".$order_item->product_id."'";
			$db->setQuery($query);
			$product = $db->loadObject();
			
			$artist_alias = MyMuseHelper::getArtistAlias($product->parentid,1);
			$album_alias = MyMuseHelper::getAlbumAlias($product->parentid,1);
			
			$download_path = MyMuseHelper::getDownloadPath($product->parentid,1);
			$realname = stripslashes($order_item->file_name);
			
			$jason = json_decode($product->file);
			if(is_array($jason)){
				foreach($jason as $j){
					if($j->file_name == $realname){
						if($params->get('my_encode_filenames')){
							$filename = $j->file_alias;
						}else{
							$filename = $realname;
						}
					}
			
				}
			}else{
				if($params->get('my_encode_filenames')){
					$filename = $product->alias;
				}else{
					$filename = $realname;
				}
			}
			$product->realname = $realname;
						
        	$object	=& MyMuse::getObject('httpdownload','helpers');
        	
        	//is it s3? redirect
        	if($params->get('my_use_s3',0)){
        		
        		 require_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/amazons3.php';
        		 $s3 = MyMuseHelperAmazons3::getInstance();
				 
        		 $bucket = $params->get('my_download_dir');
        		 $start = strlen($params->get('my_download_dir'));
				 $download_path = substr(trim($download_path,'/'), $start);
        		 $uri = $download_path.$filename;
        		
        		 $lifetime = $params->get('my_s3time');
        		 
        		 $expires = time() + $lifetime;
        		 $minutes = $lifetime / 60 - 1;
        		 
        		 //echo "lifetime = $lifetime";
        		 // echo '+'.$minutes.' minutes'; exit;
        		 
        		 $cmd = $s3->getCommand('GetObject', [
        		 		'Bucket' => $bucket,
        		 		'Key'    => $uri
        		 ]);
        		
        		 $request = $s3->createPresignedRequest($cmd, '+'.$minutes.' minutes');
        		 $s3URL = (string) $request->getUri();
      
        		 $app = JFactory::getApplication();
        		 
        		 //log and redirect
        		 $this->_logDownload($user, $product, $order_item);
        		 $app->redirect($s3URL);
        		 exit;
        		 
        	}

        	// is it an allfiles and zip is on?
        	if($product->product_allfiles && $params->get('my_use_zip',0)){
                // Get current directory

        		$query = "SELECT id,file_name, parentid from #__mymuse_product WHERE parentid='".$product->parentid."'
				AND product_downloadable='1' AND product_allfiles !='1' ORDER BY ordering ";

        		$db->setQuery($query);
        		$prods = $db->loadObjectList();
        		$parts = explode('-',$product->realname );
        		$ext = array_pop($parts);
        		$files = array();
        		$path = MyMuseHelper::getDownloadPath($product->parentid, 1);
        		$exclude = array();
        		
    			foreach($prods as $prod){
    				if(count($params->get('my_formats')) > 1){
    					foreach($params->get('my_formats') as $format){
    						if($format == $ext){
    							$exclude[] = $format;
    						}
    					}
    				}
    				if(1 == $params->get('my_download_dir_format')){
    					$path.= $ext.DS;
    				}
    				$jason = json_decode($prod->file_name);
    				if(is_array($jason)){
    					foreach($jason as $j){
    						if($ext == $j->file_ext
    							||	!in_array($j->file_ext, $exclude)
    							
    							){
    							$files[] = $j->file_name;
    						}
    					}
    				}else{
    						$files[] = $j->file_name;
    				}
    				
    			}
                chdir($path);
        		$zip	=& MyMuse::getObject('createzip','helpers');
        		$overwrite = false;
        		$destination = $path.$filename.".zip";
        		
        		$zip->create_zip($files,$destination,$overwrite);
        		sleep(3);
        		//$zip->forceDownload($destination);
       
        		if(!$object->set_byfile($destination,$filename.".zip")){
        			//Download from a file
        			$message = JText::_('MYMUSE_DOWNLOAD_UNABLE_TO_LOAD_FILE')." ".$destination." ".$filename;
        			if($params->get('my_debug')){
        				$message .= $name;
        			}
        			echo $message; exit;
        			$jinput->set('msg',$message);
        			return false;
        		}else{
        			$object->use_resume = true; //Enable Resume Mode
        			$object->download(); //Download File
        		}
     		
        		$this->_logDownload($user, $product, $order_item);
        		
        		exit;
        		
        	}
        
        	// download data from the database
        	if($params->get('my_use_database')){
        		if(!$object->set_bydata($product->file_contents)){
        			$message = JText::_('MYMUSE_DOWNLOAD_UNABLE_TO_LOAD_DATA');
        			if($params->get('my_debug')){
        				$message .= $product->file_name;
        			}
        			$jinput->set('msg',$message);
        			return false;

        		}else{

        			$object->use_resume = true; //Enable Resume Mode
        			$object->set_filename(stripslashes($product->file_name)); //Set download name
        			//$mime = $product->file_type;
        			if($product->product_allfiles == '1'){
        				$mime = "application/zip";
        			}else{
        				$mime = "application/otect-stream";
        			}

        			$object->set_mime($mime); //File MIME (Default: application/otect-stream)
        			$object->download(); //Download File

        		}
        	}else{
        		//download a file from the filesystem
        		if(!$filename){
        			$message = JText::_('MYMUSE_NO_FILENAME_FOUND'). " ".$filename;
        			if($params->get('my_debug')){
        				$message .= $filename;
        			}
        			$jinput->set('msg',$message);
        			return false;
        		}
				
       
        		$download_path = MyMuseHelper::getDownloadPath($order_item->product_id, 0);
        		if($params->get('my_download_dir_format') == 1){
        			//by format
        			$ext = MyMuseHelper::getExt($filename);
        			$download_path .= $ext.DS;
        		
       		}
        		
        		$full_filename = $download_path.$filename;

        		if(!file_exists($full_filename)){
        			echo $full_filename; exit;
        			$message = JText::_('MYMUSE_NO_FILE_FOUND')." ";
        			if($params->get('my_debug')){
        				$message .= ": ".$full_filename;
        			}
        			$jinput->set('msg',$message);
        			return false;
        		}
        		
        		if(!$object->set_byfile($full_filename,$filename)){ 
        			//Download from a file
        			$message = JText::_('MYMUSE_DOWNLOAD_UNABLE_TO_LOAD_FILE')." ".$filename;
        			if($params->get('my_debug')){
        				$message .= $full_filename;
        			}
        			$jinput->set('msg',$message);
        			return false;
        		}else{
        			$object->use_resume = true; //Enable Resume Mode
        			$object->download(); //Download File
        		}
        	}
        	
        	// All is good
        	
        	
        	$this->_logDownload($user, $product, $order_item);

        	exit;
        	
        }

        /*
         * Download a free or purchased file
         * the link will appear on product pages
         * May have an order item or it may be free
         */
        if($task == "downloadit")
        {
        	// make sure we have an id
        	$id = $jinput->get('id',0);
        	$mymuse = $jinput->get('mymuse',0);
        	$free = 0;
        	$owned = 0;
        	$db	= JFactory::getDBO();
        	$user = JFactory::getUser();
        	$user_id = $user->get('id');
        	$format = $jinput->get('format','');

        	
        	if(!$id){
        		$message = JText::_('MYMUSE_NO_DOWNLOAD_KEY');
        		$this->assignRef( 'message', $message );
        		$tpl = "message";
        		parent::display($tpl);
        		return false;
        	}
        	
        	$query = "SELECT * FROM #__mymuse_product WHERE id = '".$id."'";
        	$db->setQuery($query);
        	$product = $db->loadObject();
        	$product->price = MyMuseModelProduct::getPrice($product);
        	
        	$jason = json_decode($product->file_name);
        	
        	$artist_alias = MyMuseHelper::getArtistAlias($product->parentid,1);
        	$album_alias = MyMuseHelper::getAlbumAlias($product->parentid,1);
  	
        	// see if it is owned
        	$query = "SELECT o.order_status as status, i.file_name FROM #__mymuse_order as o, #__mymuse_order_item as i
        		WHERE i.product_id=$id 
        		AND i.order_id=o.id 
        		AND o.user_id=$user_id
        		AND o.order_status='C'";
        	$db->setQuery($query);
        	$res = $db->loadObject();
        	if(is_object($res) && $res->status == "C"){
        		$owned = 1;
        		$realname = $res->file_name;
        		if(is_array($jason)){
        			foreach($jason as $j){
        				if($j->file_name == $realname){
        					if($params->get('my_encode_filenames')){
        						$filename = $j->file_alias;
        					}else{
        						$filename = $realname;
        					}
        				}
        				
        			}
        		}else{
        			$filename = $realname;
        		}
        	}
        		
        	if(!$owned){
        		// See if it is free
        		
        		if(1 == $params->get('my_price_by_product', 0) && isset($product->price[$format]) ){
        				$price = $product->price[$format];
        			}else{
        				$price = $product->price;
        		}
        	
        		if(!isset($price) || 
        				$price['product_price'] == 0.00 || 
        				$price['product_price'] == '' || 
        				!$price['product_price'] ||
        				$mymuse
        				){

        			$free = 1;
        			if(is_array($jason)){
        				foreach($jason as $file){
        					if($file->file_ext == $format){
        						if($params->get('my_encode_filenames')){
        							$filename = $file->file_alias;
        							$realname = $file->file_name;
        						}else{
        							$filename = $realname = $file->file_name;
        						}
        					}
        				}
        				
        			}else{
        				//old style
        				if($params->get('my_encode_filenames')){
        					$filename = $product->title_alias;
        					$realname = $product->file_name;
        				}else{
        					$filename = $product->file_name;
        					$realname = $product->file_name;
        				}
        			}
        		}
        	}

        	if(!$free && !$owned){
        		$message = JText::_('MYMUSE_NOT_AVAILABLE');
        		$this->assignRef( 'message', $message );
        		$tpl = "message";
        		parent::display($tpl);
        		return false;
        	}
        	
        
        	$product->realname = $realname;
        	$object	=& MyMuse::getObject('httpdownload','helpers');
        	 
        	if($params->get('my_use_s3',0)){
        		require_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/amazons3.php';
        		$s3 = MyMuseHelperAmazons3::getInstance();

        		$bucket = $params->get('my_download_dir');
        		$uri = $artist_alias.DS.$album_alias.DS.$filename;
        		$lifetime = $params->get('my_s3time');
        		//getAuthenticatedURL($bucket, $uri, $lifetime = null, $hostBucket = false, $https = false, $realname = '')
        		$s3URL = $s3->getAuthenticatedURL($bucket, $uri, $lifetime, false, false, $realname);
        		$app =& JFactory::getApplication();
        		$app->redirect($s3URL);
        		return false;
        	}
        	
        	// download data from the database
        	if($params->get('my_use_database')){
        		if(!$object->set_bydata($product->file_contents)){
        			$message = JText::_('MYMUSE_DOWNLOAD_UNABLE_TO_LOAD_DATA');
        			if($params->get('my_debug')){
        				$message .= $product->file_name;
        			}
        			$this->assignRef( 'message', $message );
        			$tpl = "message";
        			parent::display($tpl);
        			return false;
        	
        		}else{
        	
        			$object->use_resume = true; //Enable Resume Mode
        			$object->set_filename(stripslashes($realname)); //Set download name
        			//$mime = $product->file_type;
        			if($product->product_allfiles == '1'){
        				$mime = "application/zip";
        			}else{
        				$mime = "application/otect-stream";
        			}
        	
        			$object->set_mime($mime); //File MIME (Default: application/otect-stream)
        			$object->download(); //Download File
        	
        		}
        	}else{
        		//download a physical file
        		if(!$filename){
        			$message = JText::_('MYMUSE_NO_FILENAME_FOUND');
        			if($params->get('my_debug')){
        				$message .= $filename;
        			}
        			$this->assignRef( 'message', $message );
        			$tpl = "message";
        			parent::display($tpl);
        			return false;
        		}
        		

        		$download_dir = MyMuseHelper::getDownloadPath($product->parentid, 1);
        		if(1 == $params->get('my_download_dir_format',0)){ //downloads by format
        			$download_dir .= $format.DS;
        		}
        		
        		
        		$full_filename = $download_dir.$filename;
        		$full_filename1 = $full_filename;
        		if(!file_exists($full_filename)){
        			//try with the root
        			$full_filename = JPATH_ROOT.DS.$download_dir.$filename;
        		}
        		if(!file_exists($full_filename)){
        			$message = JText::_('MYMUSE_NO_FILE_FOUND')." ";
        			if($params->get('my_debug')){
        				$message .= ": ".$full_filename1;
        			}
        			$this->assignRef( 'message', $message );
        			$tpl = "message";
        			parent::display($tpl);
        			return false;
        		}
        	
        		if(!$object->set_byfile($full_filename,$realname)){ //Download from a file
        			$message = JText::_('MYMUSE_DOWNLOAD_UNABLE_TO_LOAD_FILE')." ".$full_filename;
        			if($params->get('my_debug')){
        				$message .= $name;
        			}
        			$this->assignRef( 'message', $message );
        			$tpl = "message";
        			parent::display($tpl);
        			return false;
        		}else{
        			$object->use_resume = true; //Enable Resume Mode
        			$object->download(); //Download File
        			
        		}
        	}
        	// All is good
        	$this->_logDownload($user, $product);
        	exit;
        } // end of downloadit

        
        //JUST VIEW THE STORE
		// Initialise variables.
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$items 		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		//
		// Process the mymuse plugins.
		//
		$dispatcher	= JDispatcher::getInstance();
		$store->event = new stdClass();
		$store->text = $store->description;
		$store->catid = 1;
		$store->list_image = '';
		$store->introtext = $store->description;
	
		$offset = 0;
		
		JPluginHelper::importPlugin('mymuse');
		$results = $dispatcher->trigger('onProductBeforeHeader', array ('com_mymuse.product', &$store, &$this->params, $offset));
		$store->event->beforeDisplayHeader = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onProductAfterTitle', array('com_mymuse.product', &$store, &$this->params, $offset));
		$store->event->afterDisplayTitle = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onProductBeforeDisplay', array('com_mymuse.product', &$store, &$this->params, $offset));
		$store->event->beforeDisplayProduct = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onProductAfterDisplay', array('com_mymuse.product', &$store, &$this->params, $offset));
		$store->event->afterDisplayProduct = trim(implode("\n", $results));
						

		// PREPARE THE DATA FOR FEATURED PRODUCTS

		// Get the metrics for the structural page layout.
		$numLeading = $params->def('num_leading_articles', 1);
		$numIntro = $params->def('num_intro_articles', 4);
		$numLinks = $params->def('num_links', 4);

		// Compute the article slugs and prepare introtext (runs content plugins).
		foreach ($items as $i => & $item)
		{
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$item->catslug = ($item->category_alias) ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
			// No link for ROOT category
			if ($item->parent_alias == 'root') {
				$item->parent_slug = null;
			}

			$item->event = new stdClass();

			$dispatcher = JDispatcher::getInstance();

			// Ignore content plugins on links.
			if ($i < $numLeading + $numIntro)
			{
				$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'com_content.featured');

				$results = $dispatcher->trigger('onContentAfterTitle', array('com_mymuse.product', &$item, &$item->params, 0));
				$item->event->afterDisplayTitle = trim(implode("\n", $results));
				
				$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_mymuse.product', &$item, &$item->params, 0));
				$item->event->beforeDisplayContent = trim(implode("\n", $results));
				
				$results = $dispatcher->trigger('onContentAfterDisplay', array('com_mymuse.product', &$item, &$item->params, 0));
				$item->event->afterDisplayContent = trim(implode("\n", $results));
			}
		}

		// Preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interogate the arrays.
		$max = count($items);

		// The first group is the leading articles.
		$limit = $numLeading;
		for ($i = 0; $i < $limit && $i < $max; $i++)
		{
			$this->lead_items[$i] = &$items[$i];
		}

		// The second group is the intro articles.
		$limit = $numLeading + $numIntro;
		// Order articles across, then down (or single column mode)
		for ($i = $numLeading; $i < $limit && $i < $max; $i++)
		{
			$this->intro_items[$i] = &$items[$i];
		}

		$this->columns = max(1, $params->def('num_columns', 1));
		$order = $params->def('multi_column_order', 1);

		if ($order == 0 && $this->columns > 1)
		{
			// call order down helper
			$this->intro_items = ProductHelperQuery::orderDownColumns($this->intro_items, $this->columns);
		}

		// The remainder are the links.
		for ($i = $numLeading + $numIntro; $i < $max; $i++)
		{
			$this->link_items[$i] = &$items[$i];
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assignRef('params', $params);
		$this->assignRef('items', $items);
		$this->assignRef('store', $store);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('user', $user);

		$this->_prepareDocument();

		parent::display($tpl);
	}
	
	/**
	 * log download to database
	 */

	protected  function _logDownload($user, $product, $order_item = '')
	{
		$db = JFactory::getDBO();
		$user_id = $user->get('id');
		$user_name = $user->get('name');
		$user_email = $user->get('email');
		$product_id = $product->id;
		$filename = $product->realname;
		$date = JFactory::getDate()->format('Y-m-d H:i:s');
		$order_id = (isset($order_item->order_id))? $order_item->order_id : '';
		
        /**
		//update the product
		$query = "UPDATE #__mymuse_product SET file_downloads = file_downloads +1 WHERE id=".$product->id;
		$db->setQuery($query);
		$db->execute();
        */
		
		// update the order_item
		if($order_item){
			$query = "UPDATE #__mymuse_order_item SET downloads = downloads +1 WHERE id=".$order_item->id;
			$db->setQuery($query);
			$db->execute();
		}
		//update the product file_name json entry
		$jason = json_decode($product->file);
		if(is_array($jason)){
			for($i = 0; $i < count($jason); $i++){
				if($jason[$i]->file_name == $filename){
					$jason[$i]->file_downloads = $jason[$i]->file_downloads + 1;
				}
			}
			$file = json_encode($jason);
			$query = "UPDATE #__mymuse_product SET file='$file' WHERE id=".$product->id;
			$db->setQuery($query);
			$db->execute();
		}
		
		//add to downloads table
		$query = "INSERT INTO #__mymuse_downloads (`user_id`,`user_name`,`user_email`,`order_id`,`date`,`product_id`,`product_filename`)
				VALUES ('$user_id','$user_name','$user_email','$order_id', '$date','$product_id','$filename')";
		MyMuseHelper::logMessage( $query  );
		$db->setQuery($query);
		if($db->execute()){
			return true;
		}else{
			$msg = $db->getErrorMessage();
			JFactory::getApplication()->enqueueMessage($msg, 'warning');
			return false;
		}
	}
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Add feed links
		if ($this->params->get('show_feed_link', 1))
		{
			// TODO: fix, figure this out.
			/**
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
			*/
		}
	}
}
