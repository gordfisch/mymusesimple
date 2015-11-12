<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access
defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');

/**
 * product Table class
 */
class MymuseTableproduct extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__mymuse_product', 'id', $db);
	}
	
	/**
     * file upload errors
     * 
     * @var array
     */
    var $_upload_errors = array(
        0 => "UPLOAD_ERR_OK",
        1 => "UPLOAD_ERR_INI_SIZE",
        2 => "UPLOAD_ERR_FORM_SIZE",
        3 => "UPLOAD_ERR_PARTIAL",
        4 => "UPLOAD_ERR_NO_FILE",
        6 => "UPLOAD_ERR_NO_TMP_DIR",
        7 => "UPLOAD_ERR_CANT_WRITE",
        8 => "UPLOAD_ERR_EXTENSION"
        );

	public function bind($array, $ignore = '')
	{
	
		$form = JRequest::getVar('jform','','post','', JREQUEST_ALLOWRAW );
		if(isset($form['articletext'])){
			$array['articletext'] = $form['articletext'];
		}
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext'])) {
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos	= preg_match($pattern, $array['articletext']);

			if ($tagPos == 0) {
				$this->introtext	= $array['articletext'];
				$this->fulltext         = '';
			} else {
				list($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}
		}

		if (isset($array['attribs']) && is_array($array['attribs'])) {
			$registry = new JRegistry;
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string)$registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		$app = JFactory::getApplication();
		$this->title = trim($this->title);
		if ($this->title == '') {
			$this->setError(JText::_('MYMUSE_FILE_MUST_HAVE_A_TITLE'));
			return false;
		}

		if (trim($this->alias) == '') {
			$this->alias = $this->title;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '') {
			$this->fulltext = '';
		}

		//if (trim($this->introtext) == '' && trim($this->fulltext) == '' && !$this->parentid) {
		//	$this->setError(JText::_('JGLOBAL_ARTICLE_MUST_HAVE_TEXT'));
		//	return false;
		//}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up) {
			// Swap the dates.
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}
		
		//set a default publish up
		if(!isset($this->publish_up) || $this->publish_up == "" || $this->publish_up == "0000-00-00 00:00:00"){
			$this->publish_up = JFactory::getDate()->format('Y-m-d H:i:s');
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) {
			// Only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();

			foreach($keys as $key) {
				if (trim($key)) {
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}
		
		//check for unique sku
		$query = "SELECT product_sku FROM #__mymuse_product WHERE product_sku='".$this->_db->escape($this->product_sku)."'";
		if($this->id > 0){
			$query .= "AND id !=".$this->id;
		}

		if(!$this->_db->setQuery($query)){
			$this->setError(JText::_('DB Error'). $db->getErrorMsg());
			$app->enqueueMessage(JText::_('DB Error'). $db->getErrorMsg(), 'error');
			return false;
		}
		
		$this->_db->setQuery($query);
		if($this->_db->loadResult()){
			$app->enqueueMessage(JText::_("MYMUSE_FILE_MUST_HAVE_A_UNIQUE_SKU"), 'error');
			return false;
		}
		
		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0) {
			$this->ordering = self::getNextOrder();
		}

		return true;
	}

	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		
		$params = MyMuseHelper::getParams();
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS.'helpers'.DS.'mp3file.php');
		
		$input = JFactory::getApplication()->input;
		$task 	= $input->get('task');
		
		$post 			= JRequest::get('post');
		$form 			= JRequest::getVar('jform', array());
		$subtype 		= JRequest::getVar('subtype');
		$date			= JFactory::getDate();
		$user			= JFactory::getUser();
		
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$db = JFactory::getDBO();

		if(!isset($this->id)){
        	$this->id = $this->_id	= isset( $form['id'] )? $form['id'] : '' ;
		}
		
		$this->file_preview = isset($post['current_preview'])? $post['current_preview'] : $this->file_preview;
		$this->file_preview_2 = isset($post['current_preview_2'])? $post['current_preview_2'] : $this->file_preview_2;
		$this->file_preview_3 = isset($post['current_preview_3'])? $post['current_preview_3'] : $this->file_preview_3;
		$this->version = isset($form['version'])? $form['version'] +1 : 1;

		if ($this->id) {
			// Existing item
			$this->modified		= $date->toSQL();
			$this->modified_by	= $user->get('id');
			$isNew = 0;
		} else {
			// New product.
			if (!intval($this->created)) {
				$this->created = $date->toSQL();
			}

			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
			$this->parentid 	= isset($form['parentid'])? $form['parentid'] : '0' ;
			$isNew = 1;
		}

        // get artist alias
        if($this->parentid){
        	$artist_alias = MyMuseHelper::getArtistAlias($this->parentid, 1);
        	$album_alias = MyMuseHelper::getAlbumAlias($this->parentid);
        }else{
        	$artist_alias = MyMuseHelper::getArtistAlias($this->catid, 0);
        	$album_alias = $this->alias;
        }
        if(isset($post['upgrade']) && $form['title_alias']){
        	$this->title_alias = $form['title_alias'];
        }

 
		// Uploaded product file
		$new = 0;
		/**
		if(isset($_FILES['product_file']['name']) && $_FILES['product_file']['name'] != ""){
			
			if(isset($_FILES['product_file']['error']) && $_FILES['product_file']['error'])
			{
				$this->setError(Jtext::_($this->_upload_errors[$_FILES['product_file']['error']]) );
				return false;
			}
			$this->product_downloadable = 1;
			$new = 1;
			$ext = MyMuseHelper::getExt($_FILES['product_file']['name']);
			$_FILES['product_file']['name'] = preg_replace("/$ext$/","",$_FILES['product_file']['name']);
			$this->file_name = JFilterOutput::stringURLSafe($_FILES['product_file']['name']).'.'.$ext;
			$tmpName  = $_FILES['product_file']['tmp_name'];
			$this->file_length = $_FILES['product_file']['size'];
			$new_file = $tmpName;

			// do we save it to the database?
			if($params->get('my_use_database')){
				$fp      = fopen($tmpName, 'r');
				$this->file_contents = fread($fp, filesize($tmpName));
				fclose($fp);
				//echo "stored in DB";
				
			}else{
				
			    // make name and copy it to the download dir
				if($params->get('my_encode_filenames') ){
					$ext = MyMuseHelper::getExt($this->file_name);
					$name = md5($this->file_name . time()).'.'.$ext;
					$this->title_alias = $name;
				}else{
					$name = $this->file_name;
				}
				
        		$new_file = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
			
				if(!$this->fileUpload($tmpName, $new_file)){
            		return false;
				}
			}
		}
		*/
		
		if(!$isNew && $this->file_name){
			$current_files = json_decode($this->file_name);
			if(!is_array($current_files) && $this->file_name){
				$current_files[] = (object) array('file_name' => $this->file_name);;
			}
		}else{
			$current_files = array();
		}
		
	
		// if they selected a file from drop down
		$select_files = isset( $post['select_file'] )? $post['select_file']: '';
		
		//removing one of the variations
		if($task == 'deletevariation'){
			$variationid = $input->get('variation','');
			
			$new_current = array();
			$new_select = array();
					
			for( $i = 0; $i < count($select_files); $i++ ){
				if($i != $variationid && isset($current_files[$i]) && $select_files[$i]){
					$new_select[] = $select_files[$i];
					$new_current[] = $current_files[$i];
				}
			}
			$select_files = $new_select;
			$current_files = $new_current;
			
		}

		$download_path = MyMuseHelper::getdownloadPath($this->id,'1');
		if(is_array( $select_files )){
			for( $i = 0; $i < count($select_files); $i++ ){
				//rename if necessary
				$select_file = $select_files[$i];
				if($select_file &&  $select_file != @$current_files[$i]->file_name){
					// tidy up name and copy it to the download dir
					$ext = MyMuseHelper::getExt($select_file);
					$name = preg_replace("/$ext$/","",$select_file);
					if($params->get('my_use_sring_url_safe')){
						$file_name = JFilterOutput::stringURLSafe($name).'.'.$ext;
					}else{
						$file_name = $select_file;
					}
					
					if($params->get('my_download_dir_format') == 1){
						//by format
						if($ext == "mp3"){
							$download_path .= "320";
						}else{
							$download_path .= $ext;
						}
					}
					
					if($params->get('my_encode_filenames') ){
						$name = md5($select_file . time()).'.'.$ext;
						$file_alias = $name;
						$new_file = $download_path.DS.$name;
					}else{
						$new_file = $download_path.DS.$file_name;
						$file_alias = '';
					}
					
					$old_file = $download_path.DS.$select_file;

					if($old_file != $new_file){
						
						// DO we put a limit on size?
						//if($params->get('my_use_s3') && $this->file_length > 10240000){ // over 10 megs
						//	$this->file_name = $select_file;
						//	JFactory::getApplication()->enqueueMessage(JText::sprintf('MYMUSE_S3_FILE_TOO_LARGE_TO_COPY', $old_file, $new_file), 'warning');
						//}else{
						if(!$this->fileCopy($old_file, $new_file)){
							return false;
						}
						if(!$this->fileDelete($old_file)){
							return false;
						}
					}

					$file_length = $this->fileFilesize($new_file);

					// TODO: get this to work with s3
					$file_time = '';
					
					if(isset($new_file) && is_file($new_file)
							&& strtolower(pathinfo($new_file, PATHINFO_EXTENSION)) == "mp3"){
						$m = new mp3file($new_file);
						$a = $m->get_metadata();
						if ($a['Encoding']=='VBR' || $a['Encoding']=='CBR'){
							$file_time = $a["Length mm:ss"];
						}
					}
					
					$file_downloads = @$current[$i]->file_downloads;
					//  save this to the file_name
					$current_files[$i] = array(
							'file_name' => $file_name,
							'file_length' => $file_length,
							'file_time' => $file_time,
							'file_ext' => $ext,
							'file_alias'=> $file_alias,
							'file_downloads'=> $file_downloads
					);
				}
			}
			
			$this->file_name = json_encode($current_files);
		}
		
		if(isset($post['product_allfiles']) && $post['product_allfiles'] && !$this->file_name){
			$current_files[0] = array(
							'file_name' => '',
							'file_ext' => 'mp3',
							'file_downloads'=> ''
					);
			$current_files[1] = array(
					'file_name' => '',
					'file_ext' => 'wav',
					'file_downloads'=> ''
			);
			$this->file_name = json_encode($current_files);
		}
		
		// Previews
		//check for errors with upload previews
		if(isset($_FILES['product_preview']['name']) && $_FILES['product_preview']['name'] != ""){
			if(isset($_FILES['product_preview']['error']) && $_FILES['product_preview']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_preview']['error']]) );
				return false;
			}
		}
		if(isset($_FILES['product_preview_2']['name']) && $_FILES['product_preview_2']['name'] != ""){
			if(isset($_FILES['product_preview_2']['error']) && $_FILES['product_preview_2']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_preview_2']['error']]) );
				return false;
			}
		}
		if(isset($_FILES['product_preview_3']['name']) && $_FILES['product_preview_3']['name'] != ""){
			if(isset($_FILES['product_preview_3']['error']) && $_FILES['product_preview_3']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_preview_3']['error']]) );
				return false;
			}
		}
		
		
		$path = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) . $params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS;
		// Previews 1
		if(!$this->managePreview('preview', $path)){
			$this->setError(JText::_("MYMUSE_COULD_NOT_SET_PREVIEW")." preview ".$path);
			return false;
		}
		// Previews 2
		if(!$this->managePreview('preview_2', $path)){
			$this->setError(JText::_("MYMUSE_COULD_NOT_SET_PREVIEW")." preview2 ".$path);
			return false;
		}
		// Previews 3
		if(!$this->managePreview('preview_3', $path)){
			$this->setError(JText::_("MYMUSE_COULD_NOT_SET_PREVIEW")." preview3 ".$path);
			return false;
		}
		//END of previews
		
		
        // if it is the parent. Parentid will be 0
        if(!isset($this->parentid) || !$this->parentid){
			//must be  a parent
        	// get artist alias
        	
        	$artist_alias = MyMuseHelper::getArtistAlias($this->catid);
        	$artistdir = $params->get('my_download_dir').DS.$artist_alias;
        	$albumdir = $params->get('my_download_dir').DS.$artist_alias.DS.$this->alias;
       	
        	//what if directory names have changed?
        	$old_alias = JRequest::getVar('old_alias', '');
        	$old_catid = JRequest::getVar('old_catid', '');
        	if(($old_alias && $old_alias != $this->alias) || ($old_catid && $old_catid != $this->catid) ){
        		// for the source
        		if($old_alias && $old_alias != $this->alias){
        			$src_alias = $old_alias;
        		}else{
        			$src_alias = $this->alias;
        		}
        		if($old_catid && $old_catid != $this->catid){
        			$src_artist_alias = MyMuseHelper::getArtistAlias($old_catid);
        		}else{
        			$src_artist_alias = $artist_alias;
        		}
      	
        		// for the main product dir
        		$src = $params->get('my_download_dir').DS.$src_artist_alias.DS.$src_alias;
        		$dest = $params->get('my_download_dir').DS.$artist_alias.DS.$this->alias;
        		if(!$this->folderMove($src, $dest)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_DIR").$src." ".$dest);
        			return false;
        		}
        		// for the preview dir
        		$src  = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$src_artist_alias.DS.$src_alias;
        		$dest = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
        		if(!$this->folderMove($src, $dest)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_DIR").$src." ".$dest);
        			return false;
        		}
        		$msg = JText::sprintf("MYMUSE_PRODUCT_CHANGED_CATEGORY_SUCCESS", $msg);
        		JFactory::getApplication()->enqueueMessage($msg, 'notice');
        	}
        	
        	//create new dirs if needed 
        	if(!$this->fileExists($artistdir)){
        		if(!$this->folderNew($artistdir)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").$artistdir);
        			return false;
        		}
        		if(!$params->get('my_use_s3')){
        			if(!$this->fileCopy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        			$artistdir.DS."index.html")){
        				$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").$artistdir);
        			}
        		}
        	}
        	if(!$this->fileExists($albumdir)){
        		if(!$this->folderNew($albumdir)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").$albumdir);
        			return false;
        		}
        		if(!$params->get('my_use_s3')){
        			if(!$this->fileCopy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        			$albumdir.DS."index.html")){
        				$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").$albumdir);
        			}
        		}
        	}	
        	
        	
        	
        	//create preview dirs if needed
        	$preview_dir = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
        	if(!$this->fileExists($preview_dir)){
        		//see if artist dir exists
        		$preview_artist_dir= ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias;
        		if(!$this->folderNew($preview_artist_dir)){
        			if(!$this->folderNew($preview_dir)){
        				//$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").' '.$preview_dir);
        				return false;
        			}
        		}
        		
        		if(!$this->folderNew($preview_dir)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").' '.$preview_dir);
        			return false;
        		}
        		if(!$params->get('my_use_s3')){
        			if(!$this->fileCopy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        			$preview_dir.DS."index.html")){
        				$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").' '.$preview_dir);
        			}
        		}
        	}	

        } // end of if parent
       
		$this->checkin();
		if($this->parentid){
			$this->checkin($this->parentid);
		}

		$result = parent::store($updateNulls);
		if($result){
			

			// other categories
			if(isset($form['othercats'])  && !$this->parentid && $this->id){
				// clear product_category_xref
				$query = "DELETE FROM #__mymuse_product_category_xref WHERE product_id=".$this->id;
				$db->setQuery($query);
				$db->execute();
				if(!in_array($form['catid'], $form['othercats'])){
					$form['othercats'][] = $form['catid'];
				}
				foreach($form['othercats'] as $catid){
					$query = "INSERT INTO #__mymuse_product_category_xref
        			(catid,product_id) VALUES (".$catid.",".$this->id.")";
					$db->setQuery($query);
			
					if(!$db->execute()){
						$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCTCAT_XREF').$db->getErrorMsg());
						return false;
					}
			
				}
			}
			
			// recommends
			if(isset($form['recommend']) && $this->id){
			
				// clear product_recommend_xref
				$query = "DELETE FROM #__mymuse_product_recommend_xref WHERE product_id=".$this->id;
				$db->setQuery($query);
				$db->execute();
			
				foreach($form['recommend'] as $recommend_id){
					$query = "INSERT INTO #__mymuse_product_recommend_xref
        			(product_id, recommend_id) VALUES (".$this->id.",".$recommend_id.")";
					$db->setQuery($query);
					 
					if(!$db->execute()){
						$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCT_RECOMMEND_XREF').$db->getErrorMsg());
						return false;
					}
					 
				}
			}
			
			// onMymuseAfterSave  onFinderAfterSave
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');
			//$dispatcher->trigger('onMymuseAfterSave', array('com_mymuse.product', $this, $isNew));
			$res = $dispatcher->trigger('onFinderAfterSave', array('com_mymuse.product', $this, $isNew));
			JPluginHelper::importPlugin('mymuse');
			$res = $dispatcher->trigger('onMyMuseAfterSave', array('com_mymuse.product', $this, $isNew));
		}else{
			
		}
		
		return $result; 

	}
	
	/**
	 * Manage Previews
	 * Remove old one/Upload new one/Select from drop down
     *
     * @param    string preview name ie. preview, preview_2, preview_3
     * @param    string path
     * @return    boolean    True on success.
	 */
	
	private function managePreview($preview, $path)
	{
		$params = MyMuseHelper::getParams();
		 
		$post 			= JRequest::get('post');
		$form 			= JRequest::getVar('jform', array());
		$preview_name 	= 'product_'.$preview;
		$remove_name 	= 'remove_'.$preview;
		$current_name 	= 'current_'.$preview;
		$file_preview_name = 'file_'.$preview;
		$application = JFactory::getApplication();
		
		// remove old one?
		if(isset($post[$remove_name]) && $post[$remove_name] == "on"
				&& $post[$current_name] != ""){
			 
			$old = $path.$post[$current_name];
			
			if($this->fileExists($old)){

				if(!$this->fileDelete($old)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
					return false;
				}
			}
			$this->$file_preview_name = '';
		}
		
		//upload a file
		if(isset($_FILES[$preview_name]) && $_FILES[$preview_name]['size'] >  0){
			$ext = MyMuseHelper::getExt($_FILES[$preview_name]['name']);
			$_FILES[$preview_name]['name'] = preg_replace("/$ext$/","",$_FILES[$preview_name]['name']);
			if($params->get('my_use_sring_url_safe')){
				$this->$file_preview_name = JFilterOutput::stringURLSafe($_FILES[$preview_name]['name']).$ext;
			}else{
				$this->$file_preview_name = $_FILES[$preview_name]['name'].$ext;
			}
			
			$tmpName2  = $_FILES[$preview_name]['tmp_name'];
			 
			$new = $path.$this->$file_preview_name;
			if(!$this->fileUpload($tmpName2, $new)){
				$this->setError(JText::_("MYMUSE_COULD_NOT_UPLOAD_FILE").": ".$new);
				return false;
			}
		}

		
		//if they selected a file from drop down
		$file_preview = isset($post[$file_preview_name])? $post[$file_preview_name] : '';
		if($file_preview
				&& ( !isset($_FILES[$preview_name]['name']) || $_FILES[$preview_name]['name'] == '' )
				&& (!isset($post[$remove_name]) || !$post[$remove_name] == "on")
				&& ($file_preview != $post[$current_name])
		){
			$new = 1;
			 
			$ext = MyMuseHelper::getExt($file_preview);
			$name = preg_replace("/$ext$/","",$file_preview);
			if($params->get('my_use_sring_url_safe')){
				$this->$file_preview_name = JFilterOutput::stringURLSafe($name).$ext;
			}else{
				$this->$file_preview_name = $name.$ext;
			}

			$old_file = $path.$file_preview;
			$new_file = $path.$this->$file_preview_name;
			
			if($old_file != $new_file){
				
				if($this->fileExists($old_file)){
					if(!$this->fileCopy($old_file, $new_file)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file);
						
						// Add a message to the message queue
						$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file, 'error');
						
						return false;
					}else{
						//echo "copied old file: ".$old_file ."to  new file: ". $new_file; exit;
					}
					//if(!$this->fileDelete($old_file)){
					//	$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old_file);
					//	return false;
					//}
				}
			}
		}

		return true;
		 
	}
	
	

    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.  The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param    mixed    An optional array of primary key values to update.  If not
     *                    set the instance property value is used.
     * @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
     * @param    integer The user id of the user performing the operation.
     * @return    boolean    True on success.
     * @since    1.0.4
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks))
        {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k.'='.implode(' OR '.$k.'=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
        }

        // Update the publishing state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE `'.$this->_tbl.'`' .
            ' SET `state` = '.(int) $state .
            ' WHERE ('.$where.')' .
            $checkin
        );
        $this->_db->execute();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
        {
            // Checkin the rows.
            foreach($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        // onMymuseChangeState  onFinderChangeState
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        //$dispatcher->trigger('onMymuseChangeState', array('com_mymuse.product', $pks, $state));
        $res = $dispatcher->trigger('onFinderChangeState', array('com_mymuse.product', $pks, $state));

        
        $this->setError('');
        return true;
    }

    /**
     * Override delete
     * If there is no asset, silently pass that by
     * Deletes this row in database (or if provided, the row of key $pk)
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/JTable/delete
     * @since   11.1
     * @throws  UnexpectedValueException
     */
    public function delete($pk = null)
    {
    	$k = $this->_tbl_key;
    
    	// Implement JObservableInterface: Pre-processing by observers
    	$this->_observers->update('onBeforeDelete', array($pk, $k));
    
    	$pk = (is_null($pk)) ? $this->$k : $pk;
    
    	// If no primary key is given, return false.
    	if ($pk === null)
    	{
    		throw new UnexpectedValueException('Null primary key not allowed.');
    	}
    
    	// If tracking assets, remove the asset first.
    	if ($this->_trackAssets)
    	{
    
    		// Get and the asset name.
    		$savedK = $this->$k;
    
    		$this->$k = $pk;
    		$name = $this->_getAssetName();
    		$asset = self::getInstance('Asset');
    
    		if ($asset->loadByName($name))
    		{
    
    			if (!$asset->delete())
    			{
    					
    				$this->setError($asset->getError());
    				return false;
    			}
    		}
    		else
    		{
    			$this->setError($asset->getError());
    			//no assset? let it go
    			//return false;
    		}
    
    		$this->$k = $savedK;
    	}
    
    	// Delete the row by primary key.
    	$query = $this->_db->getQuery(true)
    	->delete($this->_tbl)
    	->where($this->_tbl_key . ' = ' . $this->_db->quote($pk));
    	$this->_db->setQuery($query);
    
    	// Check for a database error.
    	$this->_db->execute();
    
    	// Implement JObservableInterface: Post-processing by observers
    	$this->_observers->update('onAfterDelete', array($pk));
    	
    	$dispatcher = JEventDispatcher::getInstance();
    	JPluginHelper::importPlugin('finder');
    	$res = $dispatcher->trigger('onFinderAfterDelete', array('com_mymuse.product', $this, $isNew));

    
    	return true;
    }
    
    

    /**
     * Create a new folder
     * 
     * @param   string folder name
     *
     * @return  boolean  True on success.
     */
    public function folderNew($dir)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		// first section is bucket name
    		$parts = explode(DS,$dir);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS).DS;
    		$status = $s3->putObject('', $bucket, $uri);
    		if(!$status){
    			$this->setError( 'S3 Error: '.$s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$s3->getError() , 'error');
    			return false;
    		}
    	}else{
    		$status = JFolder::create($dir);
    		if(!$status){
    			$this->setError( "Could not create $dir");
    			$application->enqueueMessage("Could not create $dir" , 'error');
    			return false;
    		}
    		if(!JFile::copy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
    				$dir.DS."index.html")){
    			$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").": ".$artistdir);
    			$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").": ".$artistdir, 'error');
    			return false;
    		}
    	}
    	 
    	return true;
    }
    
    
    /**
     * Delete a file
     * 
     * @param   string file name
     *
     * @return  boolean  True on success.
     */
    public function fileDelete($file)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		// first section is bucket name
    		$parts = explode(DS,$file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		
    		$status = $s3->deleteObject($bucket, $uri);
    		if(!$status){
    			$this->setError('S3 Error : '.$s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$s3->getError() , 'error');
    			return false;
    		}
    	}else{
    		if(!JFile::delete($file)){
    			$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".file);
    			$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".file , 'error');
    		}
    	}
    	return true;
    }
    
    /**
     * Upload a file
     * 
     * @param   string file to move
     * @param   string file name moving to
     *
     * @return  boolean  True on success.
     */
    public function fileUpload($tmpName, $new_file)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		// first section is bucket name
    		$parts = explode(DS,$new_file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		$input = $s3->inputFile($tmpName);
    		if($bucket == $params->get('my_download_dir')){
    			//we are uploading to the download dir, set additinal request headers
    			$requestHeaders = array("Content-Type" => "application/octet-stream", "Content-Disposition" => "attachment");
    		}

    		$success = $s3->putObject($input, $bucket, $uri, null, null, $requestHeaders);
   
    		if(!@unlink($tmpName)) {
    			JFile::delete($tmpName);
    		}
    		if(!$success){
    			$this->setError('S3 Error: '. $s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$s3->getError() , 'error');
    			return false;
    		}
    	}else{
 
    		if(!JFile::upload($tmpName, $new_file)){
    			$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName." ".$new_file);
    			$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName." ".$new_file , 'error');
    			return false;
    		}
    	}
    	return true;
    	 
    }
    
    /**
     * Copy a file
     * 
     * @param   string file to copy
     * @param   string file name copy to
     *
     * @return  boolean  True on success.
     */
    public function fileCopy($old_file, $new_file)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		//they are both on s3. Must download one 
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		$oldParts = explode(DS,$old_file);
    		
    		$oldBucket = array_shift($oldParts);
    		$oldBucket = trim($oldBucket, DS);
    		$uri = implode(DS, $oldParts);
    		
    		$jconfig = JFactory::getConfig();
    		$tmpName = $jconfig->get('tmp_path','').DS.array_pop($oldParts );
    		
    		if(!$s3->getObject($oldBucket, $uri, $tmpName)){
    			$this->setError('S3 Error: '.$s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$s3->getError() , 'error');
    			return false;
    		}
    		
    		// upload the tmp file
    		$parts = explode(DS,$new_file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$file = implode(DS, $parts);
    		$file = trim($file,DS);
    		$input = $s3->inputFile($tmpName);
    	
    		$success = $s3->putObject($input, $bucket, $file);
    		if(!@unlink($tmpName)) {
    			JFile::delete($tmpName);
    		}
    		if(!$success){
    			$this->setError('S3 Error: '.$s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$s3->getError() , 'error');
    			return false;
    		}
    	}else{
    
    		if(!JFile::copy($old_file, $new_file)){
    			$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file);
    			$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file , 'error');
    			return false;
    		}
    	}
    	return true;
    
    }
    
    /**
     * File Exists?
     * 
     * @param   string file name
     *
     * @return  boolean  True on success.
     */
    public function fileExists($file)
    {
    	$params = MyMuseHelper::getParams();
    	
    	if($params->get('my_use_s3')){
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		$parts = explode(DS,$file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		return $s3->getObject($bucket, $uri);
    		
    	}else{
    		return file_exists($file);
    	}
    }
    
    public function folderMove($src, $dest)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		if(!$this->folderNew($dest)){
    			return false;
    		}
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		// first section is bucket name
    		$parts = explode(DS,$src);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		echo "get files $uri $bucket <br />";
    		$old_files = $s3->listS3Contents($uri, $bucket);

  			$old_folders = array();
    		foreach($old_files as $path=>$info){
    			if($info['size'] > 0 && (substr($path, -1) != '/')){
    				$parts = explode(DS,$path);
    				$file = array_pop($parts);
    				$new_file = trim($dest, DS).DS.$file;

    				if(!$this->fileCopy($bucket.DS.$path, $new_file)){
    					return false;
    				}
    				if(!$this->fileDelete($bucket.DS.$path)){
    					return false;
    				}
    			}else{
    				$old_folders[] = $path;
    			}
    		}
    		
    		foreach($old_folders as $folder){
    			$this->fileDelete($folder);
    		}
    		
    	}else{
    		if ( !JFolder::create($dest) ) {
                //Throw error message and stop script
                $this->setError("Could not create $dest");
                return false;
            }
            $files = JFolder::files($src);
            foreach ($files as $file) {
                JFile::move($src. DS . $file, $dest . DS. $file);
            }
            /**
    		if(!JFolder::move($src,$dest)){
        		$this->setError(JText::sprintf("MYMUSE_PRODUCT_CHANGED_CATEGORY", $msg));
        		
        		return false;
    		}else{
    			$this->setError(JText::sprintf("Folder Moved", $msg));
    		}
            * */
    	}
    	return true;

    }
    
    public function fileFilesize($src)
    {
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		// first section is bucket name
    		$parts = explode(DS,$src);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		$header  = $s3->getObjectInfo($bucket, $uri);
    		return $header['size'];
    	}else{
    		return filesize($src);
    	}
    }
}
