<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access
defined('_JEXEC') or die;

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

		if (trim($this->title) == '') {
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
		$query = "SELECT product_sku FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
		if($this->id > 0){
			$query .= "AND id !=".$this->id;
		}

		$this->_db->setQuery($query);
		if($this->_db->loadResult()){
			$this->setError(JText::_('MYMUSE_FILE_MUST_HAVE_A_UNIQUE_SKU'));
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
	
		$post 			= JRequest::get('post');
		$form 			= JRequest::getVar('jform', array());

		
		if(!isset($this->id)){
        	$this->id 		= isset($form['id'])? $form['id'] : '' ;
		}
		
		
		$subtype 		= JRequest::getVar('subtype'); 
		$date			= JFactory::getDate();
		$user			= JFactory::getUser();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$db = JFactory::getDBO();
		$this->file_preview = isset($post['current_preview'])? $post['current_preview'] : $this->file_preview;
		$this->file_preview_2 = isset($post['current_preview_2'])? $post['current_preview_2'] : $this->file_preview_2;
		$this->file_preview_3 = isset($post['current_preview_3'])? $post['current_preview_3'] : $this->file_preview_3;
		$this->version = isset($form['version'])? $form['version'] +1 : 1;

		if ($this->id) {
			// Existing item
			$this->modified		= $date->toSQL();
			$this->modified_by	= $user->get('id');
		} else {
			// New product.
			if (!intval($this->created)) {
				$this->created = $date->toSQL();
			}

			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
			$this->parentid 	= isset($form['parentid'])? $form['parentid'] : '0' ;
		}

        // get artist alias
        if($this->parentid){
        	$artist_alias = MyMuseHelper::getArtistAlias($this->parentid, 1);
        	$album_alias = MyMuseHelper::getAlbumAlias($this->parentid);
        }else{
        	$artist_alias = MyMuseHelper::getArtistAlias($this->catid, 0);
        	$album_alias = $this->alias;
        }

        // other categories
        if(isset($this->catid)){
        	$form['othercats'][] = $this->catid;
        }else{
        	$form['othercats'][] = $form['catid'];
        }
        if(isset($form['othercats'])  && !$this->parentid && $this->id){
  		
        	$catids	= $form['othercats'];
        	// update product_category_xref
        	$query = "DELETE FROM #__mymuse_product_category_xref WHERE product_id=".$this->id;
        	$db->setQuery($query);
        	$db->execute();

        	foreach($catids as $catid){
        		$query = "INSERT INTO #__mymuse_product_category_xref
					(catid,product_id) VALUES (".$catid.",".$this->id.")";
        		$db->setQuery($query);
     
        		if(!$db->execute()){
        			$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCTCAT_XREF').$db->getErrorMsg());
        			return false;
        		}

        	}
        }
 
		// Uploaded product file
		$new = 0;

		if(isset($_FILES['product_file']['name']) && $_FILES['product_file']['name'] != ""){
			if($_FILES['product_file']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_file']['error']]) );
				return false;
			}
			$this->product_downloadable = 1;
			$new = 1;
			$ext = MyMuseHelper::getExt($_FILES['product_file']['name']);
			$_FILES['product_file']['name'] = preg_replace("/$ext$/","",$_FILES['product_file']['name']);
			$this->file_name = JFilterOutput::stringURLSafe($_FILES['product_file']['name']).'.'.$ext;
			$tmpName  = $_FILES['product_file']['tmp_name'];
			$this->file_length = $_FILES['product_file']['size'];

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
				
				if(!JFile::copy($tmpName, $new_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName." ".$new_file);
            		return false;
				}else{
					//echo "did the copy $tmpName, $new_file <br />"; 
				}
				
			}

		}
		if(isset($post['upgrade']) && $form['title_alias']){
			$this->title_alias = $form['title_alias'];
		}
		
		$select_file = isset($post['select_file'])? $post['select_file']: '';

		//if they selected a file from drop down
		if($select_file && !$new){
			$new = 1;
			// make name and copy it to the download dir
			$ext = MyMuseHelper::getExt($select_file);
			$name = preg_replace("/$ext$/","",$select_file);
			$this->file_name = JFilterOutput::stringURLSafe($name).'.'.$ext;
			if($params->get('my_encode_filenames') ){
				$name = md5($select_file . time()).'.'.$ext;
				$this->title_alias = $name;
				$new_file = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$name;
			}else{
				$new_file = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_name;
			}

			$old_file = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$select_file;
			
			$this->file_length = filesize($params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$select_file);
			//echo "old = $old_file <br />new = $new_file <br />";
			if($old_file != $new_file){
				if(!JFile::copy($old_file, $new_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old." ".$new);
            		return false;
				}
				if(!JFile::delete($old_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old_file);
					return false;
				}
			}
		}

		if($this->parentid  && $new){
			// see if there is an old file to delete
			if(isset($post['current_title_alias']) && $post['current_title_alias'] != "" && $post['current_title_alias'] != $this->file_name){
				$old = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$post['current_title_alias'];
				if(file_exists($old)){
					if(!JFile::delete($old)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
					}
				}
			}
			if(isset($old_file_name) && $old_file_name != $this->file_name){
				$old = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS.$old_file_name;
				if(file_exists($old)){
					if(!JFile::delete($old)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
					}
				}
			}
		}
		
		
		
		//check for errors
		if(isset($_FILES['product_preview']['name']) && $_FILES['product_preview']['name'] != ""){
			if($_FILES['product_preview']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_preview']['error']]) );
				return false;
			}
		}
		if(isset($_FILES['product_preview_2']['name']) && $_FILES['product_preview_2']['name'] != ""){
			if($_FILES['product_preview_2']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_preview_2']['error']]) );
				return false;
			}
		}
		if(isset($_FILES['product_preview_3']['name']) && $_FILES['product_preview_3']['name'] != ""){
			if($_FILES['product_preview_3']['error'])
			{
				JError::raiseError( 500, Jtext::_($this->_upload_errors[$_FILES['product_preview_3']['error']]) );
				return false;
			}
		}
		
		
		// Previews
		if(isset($post['remove_preview']) && $post['remove_preview'] == "on" && $post['current_preview'] != ""){
		
			$old = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$post['current_preview'];
			if(file_exists($old)){
				if(!JFile::delete($old)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
						
				}
			}
			$this->file_preview = '';
			
		}
		if(isset($_FILES['product_preview']) && $_FILES['product_preview']['size'] >  0){
			$ext = MyMuseHelper::getExt($_FILES['product_preview']['name']);
			$_FILES['product_preview']['name'] = preg_replace("/$ext$/","",$_FILES['product_preview']['name']);
			$this->file_preview = JFilterOutput::stringURLSafe($_FILES['product_preview']['name']).'.'.$ext;
			$tmpName2  = $_FILES['product_preview']['tmp_name'];
			
			$new = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_preview;
			//if(!get_magic_quotes_gpc())
			//{
    			//$this->file_preview = addslashes($this->file_preview);
    			//$new = addslashes($new);
			//}
			if(!JFile::copy($tmpName2, $new)){
				$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName2." ".$new);
				return false;
			}
		}
		
		//if they selected a file from drop down
	    $file_preview = isset($post['file_preview'])? $post['file_preview'] : '';
		

		if($file_preview 
				&& ( !isset($_FILES['product_preview']['name']) || $_FILES['product_preview']['name'] == '' )
				&& (!isset($post['remove_preview']) || !$post['remove_preview'] == "on")
				){
			$new = 1;
			
			$ext = MyMuseHelper::getExt($file_preview);
			$name = preg_replace("/$ext$/","",$file_preview);
			$this->file_preview = JFilterOutput::stringURLSafe($name).'.'.$ext;
			$old_file = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$file_preview;
			$new_file = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_preview;

			//echo "old preview = $old_file <br />new preview = $new_file <br />";
			if($old_file != $new_file){
				if(file_exists($old_file)){
					if(!JFile::copy($old_file, $new_file)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file);
						return false;
					}
					if(!JFile::delete($old_file)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old_file);
						//return false;
					}
				}
			}
		}
		


		// Previews 2
		if(isset($post['remove_preview_2']) && $post['remove_preview_2'] == "on" && $post['current_preview_2'] != ""){
		
			$old = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$post['current_preview_2'];
			if(file_exists($old)){
				if(!JFile::delete($old)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
						
				}
			}

			$this->file_preview_2 = '';
			
			
		}
		
		
		if(isset($_FILES['product_preview_2']) && $_FILES['product_preview_2']['size'] >  0){
			$ext = MyMuseHelper::getExt($_FILES['product_preview_2']['name']);
			$_FILES['product_preview_2']['name'] = preg_replace("/$ext$/","",$_FILES['product_preview_2']['name']);
			$this->file_preview_2 = JFilterOutput::stringURLSafe($_FILES['product_preview_2']['name']).'.'.$ext;
			$tmpName2  = $_FILES['product_preview_2']['tmp_name'];
				
			$new = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_preview_2;

			if(!JFile::copy($tmpName2, $new)){
				$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName2." ".$new);
				return false;
			}
		}

		//if they selected a file from drop down
		$file_preview_2 = isset($post['file_preview_2'])? $post['file_preview_2'] : '';
		
		if($file_preview_2  
				&& (!isset($_FILES['product_preview_2']['name']) || $_FILES['product_preview_2']['name'] == '')
				&& (!isset($post['remove_preview_2']) || !$post['remove_preview_2'] == "on")
				){
			$new = 1;
			$ext = MyMuseHelper::getExt($file_preview_2);
			$name = preg_replace("/$ext$/","",$file_preview_2);
			$this->file_preview_2 = JFilterOutput::stringURLSafe($name).'.'.$ext;
			$old_file = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$file_preview_2;
			$new_file = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_preview_2;
		
			//echo "old preview = $old_file <br />new preview = $new_file <br />";
			if($old_file != $new_file){
				if(!JFile::copy($old_file, $new_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old." ".$new);
					return false;
				}
				if(!JFile::delete($old_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old_file);
					return false;
				}
			}
		}
		

	
		// Previews 3
		if(isset($post['remove_preview_3']) && $post['remove_preview_3'] == "on" && $post['current_preview_3'] != ""){

			$old = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$post['current_preview_3'];
			if(file_exists($old)){
				if(!JFile::delete($old)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
		
				}
			}
			$this->file_preview_3 = '';
		}
		
		
		if(isset($_FILES['product_preview_3']) && $_FILES['product_preview_3']['size'] >  0){
			$ext = MyMuseHelper::getExt($_FILES['product_preview_3']['name']);
			$_FILES['product_preview_3']['name'] = preg_replace("/$ext$/","",$_FILES['product_preview_3']['name']);
			$this->file_preview_3 = JFilterOutput::stringURLSafe($_FILES['product_preview_3']['name']).'.'.$ext;
			$tmpName3  = $_FILES['product_preview_3']['tmp_name'];
		
			$new = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_preview_3;
			//if(!get_magic_quotes_gpc())
			//{
			//$this->file_preview = addslashes($this->file_preview);
			//$new = addslashes($new);
			//}
			if(!JFile::copy($tmpName3, $new)){
				$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName3." ".$new);
				return false;
			}
		}
		
		//if they selected a file from drop down
		$file_preview_3 = isset($post['file_preview_3'])? $post['file_preview_3'] : '';
		
		if($file_preview_3  
				&& (!isset($_FILES['product_preview_3']['name']) || $_FILES['product_preview_3']['name'] == '')
				&& (!isset($post['remove_preview_3']) && !$post['remove_preview_3'] == "on")
				){
			$new = 1;
			$ext = MyMuseHelper::getExt($file_preview_3);
			$name = preg_replace("/$ext$/","",$file_preview_3);
			$this->file_preview_3 = JFilterOutput::stringURLSafe($name).'.'.$ext;
			$old_file = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$file_preview_3;
			$new_file = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$this->file_preview_3;
		
			//echo "old preview = $old_file <br />new preview = $new_file <br />";
			if($old_file != $new_file){
				if(!JFile::copy($old_file, $new_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old." ".$new);
					return false;
				}
				if(!JFile::delete($old_file)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old_file);
					return false;
				}
			}
		}	
		
        $this->_id = $this->id;


        // if it is the parent
        if(!isset($this->parentid) || !$this->parentid){
        	// make a download dir for this product

        	// get artist alias
        	$artist_alias = MyMuseHelper::getArtistAlias($this->catid);
        	$artistdir = $params->get('my_download_dir').DS.$artist_alias;
        	$albumdir = $params->get('my_download_dir').DS.$artist_alias.DS.$this->alias;
       	
        	//what if they have changed?
        	$changed = 0;
        	$old_alias = JRequest::getVar('old_alias', '');
        	$old_catid = JRequest::getVar('old_catid', '');
        	if($old_alias && $old_alias != $this->alias){
        		$changed = 1;
        	}
        	// If they changed only the album alias
        	if($changed && $old_catid == $this->catid){
        		// move the old dir to the new name
        		$src = $params->get('my_download_dir').DS.$artist_alias.DS.$old_alias;
        		if(!JFolder::move($src, $albumdir)){
        			$this->setError("Could not rename: ".$src." ".$albumdir);
        			return false;
        		}
        		// move the old preview dir to the new name
        		$preview_dir = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
        		$src = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$old_alias;
        		$ret = JFolder::move($src, $preview_dir);
        		if(!$ret){
        			$this->setError($ret." ".$src." ".$preview_dir);
        			return false;
        		}
        	}
        	
        
        	
        	//create new dirs if needed 
        	if(!file_exists($artistdir)){
        		if(!JFolder::create($artistdir)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").$artistdir);
        			return false;
        		}
        		if(!JFile::copy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        		$artistdir.DS."index.html")){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").$artistdir);
        		}
        	}
        	if(!file_exists($albumdir)){
        		if(!JFolder::create($albumdir)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").$albumdir);
        			return false;
        		}
        		if(!JFile::copy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        		$albumdir.DS."index.html")){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").$albumdir);
        		}
        	}
        	
        	
        	
        	//create preview dir if needed
        	$preview_dir = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
        	if(!file_exists($preview_dir)){
        		//see if artist dir exists
        		$preview_artist_dir= JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias;
        		if(!file_exists($preview_artist_dir)){
        			if(!JFolder::create($preview_artist_dir)){
        				$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").' '.$preview_dir);
        				return false;
        			}
        		}
        		
        		if(!JFolder::create($preview_dir)){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_MAKE_DIR").' '.$preview_dir);
        			return false;
        		}
        		if(!JFile::copy(JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
        		$preview_dir.DS."index.html")){
        			$this->setError(JText::_("MYMUSE_COULD_NOT_COPY_INDEX").' '.$preview_dir);
        		}
        	}
        
        	// update product_category_xref
        	$query = "DELETE FROM #__mymuse_product_category_xref WHERE product_id=".$this->id;
        	$db->setQuery($query);
        	$db->execute();

        	if(isset($catids)){
        		foreach($catids as $catid){
        			if($catid == $this->catid ){
        			
        				continue;
        			}
        			$query = "INSERT INTO #__mymuse_product_category_xref
        			(catid,product_id) VALUES (".$catid.",".$this->id.")";
        			$db->setQuery($query);
        			if(!$db->execute()){
        				$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCTCAT_XREF').$db->getErrorMsg());
        				return false;
        			}

        		}
        	}
        	if($old_catid != '' && $old_catid != $this->catid){
        		//now we are not sure what to move
        		$old_artist_alias = MyMuseHelper::getArtistAlias($old_catid);
        	
        		$msg = "<br />Old dir: ".$params->get('my_download_dir').DS.$old_artist_alias.DS.$old_alias;
        		$msg .= " <br />New: ".$params->get('my_download_dir').DS.$artist_alias.DS.$this->alias;
        		$this->setError(JText::sprintf("MYMUSE_PRODUCT_CHANGED_CATEGORY", $msg));
        		$this->checkin();
        		parent::store($updateNulls);
        		return false;
        		
        	}

        }


		$this->checkin();
		if($this->parentid){
			$this->checkin($this->parentid);
		}

		return parent::store($updateNulls);
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

        $this->setError('');
        return true;
    }




}
