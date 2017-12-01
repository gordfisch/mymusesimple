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

		if (trim($array['alias']) == '') {
			$array['alias'] = $array['title'];
		}
		$array['alias'] = JApplication::stringURLSafe($array['alias']);


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
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}
		
		//set a default publish up
		if(!isset($this->publish_up) || $this->publish_up == "" || $this->publish_up == "0000-00-00 00:00:00"){
			$this->publish_up = JFactory::getDate()->format('Y-m-d H:i:s');
		}
		
		if($this->publish_down == "1970-01-01 00:00:01" || $this->publish_down == $this->publish_up){
			$this->publish_down = '';
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
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS.'helpers'.DS.'mp3file.php');

		$params 		= MyMuseHelper::getParams();
		$app 			= JFactory::getApplication();
		$input 			= $app->input;
		$task 			= $input->get('task');
		$post 			= JRequest::get('post');
		$form 			= $input->get('jform', array(), 'ARRAY'); 
		$select_files 	= $input->get('select_file', '' ,'array');
		$preview 		= $input->getString('file_preview', '');
		$current_preview = $input->get('current_preview', '');
		$remove_preview = $input->get('remove_preview', '');
		$date			= JFactory::getDate();
		$user			= JFactory::getUser();
		$db 			= JFactory::getDBO();


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


		if(!$isNew && $this->file_name){
			$current_files = json_decode($this->file_name);
		}else{
			$current_files = array();
		}



        // for tracks with parentids
        if($this->parentid){
        	$download_path = MyMuseHelper::getdownloadPath($this->parentid,1);
        }   

 		$done = 0;
 		
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
			$this->file_name = json_encode($current_files);
			$done = 1;
			
		}


		//chosen from select dropdown
		if(is_array( $select_files ) && !$done){
			
			for( $i = 0; $i < count($select_files); $i++ ){
				//rename if necessary
				$select_file = $select_files[$i];
				if($select_file &&  $select_file != @$current_files[$i]->file_name){
					
					
					// tidy up name and copy it to the download dir
					$ext = MyMuseHelper::getExt($select_file);
					$name = preg_replace("/$ext$/","",$select_file);
					if($params->get('my_use_string_url_safe')){
						$file_name = JFilterOutput::stringURLSafe($name).'.'.$ext;
					}else{
						$file_name = $select_file;
					}
				
					$my_download_path = $download_path;

					$new_file = $my_download_path.$file_name;

					$file_alias = JApplication::stringURLSafe($file_name);

					$old_file = $my_download_path.$select_file;

					

					if($old_file != $new_file){
						if(!$this->fileCopy($old_file, $new_file)){
							return false;
						}
						if(!$this->fileDelete($old_file)){
							return false;
						}
					}
					$file_length = filesize($new_file);
				
						
					$track_time = '';
					
					if(isset($new_file) && is_file($new_file)
							&& strtolower(pathinfo($new_file, PATHINFO_EXTENSION)) == "mp3"){
						$m = new mp3file($new_file);

						$a = $m->get_metadata();
						if ($a['Encoding']=='VBR' || $a['Encoding']=='CBR'){
							$track_time = $a["Length mm:ss"];
						}
					}

//echo "params->get('my_use_string_url_safe') =".$params->get('my_use_string_url_safe')."<br />";
//echo "download_path = $download_path <br /> my_download_path = $my_download_path <br />old_file = $old_file <br /> ";
//echo "new_file = $new_file <br /> file_alias = $file_alias file_length = $file_length <br /> track_time = $track_time"; exit;	

					$file_downloads = isset($current[$i]->file_downloads)? $current[$i]->file_downloads : "0";
					//  save this to the file_name
					$current_files[$i] = array(
							'file_name' => $file_name,
							'file_length' => $file_length,
							'file_ext' => $ext,
							'file_alias'=> $file_alias,
							'file_downloads'=> $file_downloads,
							'file_time' => $track_time
					);
				}
			}
			
			$this->file_name = json_encode($current_files);
		}
	
		//all files
		if(isset($form['product_allfiles']) && $form['product_allfiles']){

			$this->product_allfiles = 1;
			for($p = 0; $p < count($params->get('my_formats')); $p++){
				$file_name = JFilterOutput::stringURLSafe($this->alias."-full-release-". $params->get('my_formats')[$p]);
				$current_files[$p] = array(
							'file_name' => $file_name,
							'file_length' => '',
							'file_ext' => $params->get('my_formats')[$p],
							'file_alias'=> $file_name,
							'file_downloads'=> '0',
							'file_time' => ''
					);

			}
			$this->file_name = json_encode($current_files);
			$this->file_type = "audio";
		}
		


		// Preview from select box
		if(isset($preview) && $preview && $preview != $current_preview){
			if($this->parentid){
				$path = MyMuseHelper::getSitePath($this->parentid, 1);
			}elseif ($this->id){
				$path = MyMuseHelper::getSitePath($this->id, 0);
			}
			
			$new = 1;
			 
			$ext = MyMuseHelper::getExt($preview);
			$name = preg_replace("/\.$ext$/","",$preview);
			$file_preview_name = JFilterOutput::stringURLSafe($name).'.'.$ext;

		
			$old_file = $path.$preview;
			$new_file = $path.$file_preview_name;
	
			if($old_file != $new_file){
				
				if($this->fileExists($old_file)){
					if(!$this->fileCopy($old_file, $new_file)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file);
						$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file, 'error');
						return false;
					}
					
				}
			}
			$this->file_preview = $file_preview_name;
		}else{
			$this->file_preview = isset($current_preview)? $current_preview : '';
		}
		if($remove_preview){
			$this->file_preview = '';
		}


		$this->checkin();
		$result = parent::store($updateNulls);

		if($result){

			// clear product_category_xref
			$query = "DELETE FROM #__mymuse_product_category_xref WHERE product_id=".$this->id;
			$db->setQuery($query);
			$db->execute();
			// other categories
			if(isset($form['othercats'])  && count($form['othercats']) && $this->id){
				
				if(in_array($form['catid'], $form['othercats']) ){
					unset($form['othercats'][$form['catid']]);
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
			// clear product_recommend_xref
			$query = "DELETE FROM #__mymuse_product_recommend_xref WHERE product_id=".$this->id;
			$db->setQuery($query);
			$db->execute();

			//now add
			if(isset($form['recommended']) && $this->id){

				foreach($form['recommended'] as $recommend_id){
					$query = "INSERT INTO #__mymuse_product_recommend_xref
        			(product_id, recommend_id) VALUES (".$this->id.",".$recommend_id.")";
					$db->setQuery($query);
					 
					if(!$db->execute()){
						$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCT_RECOMMEND_XREF').$db->getErrorMsg());
						return false;
					}
					 
				}
			}

			//what about the sku?
			if(!isset($this->product_sku) || $this->product_sku == ''){
				$this->product_sku = $this->alias.'-'.$this->id;
			}
			$result = parent::store($updateNulls);
	
			// onMymuseAfterSave  onFinderAfterSave
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');
			//$dispatcher->trigger('onMymuseAfterSave', array('com_mymuse.product', $this, $isNew));
			$res = $dispatcher->trigger('onFinderAfterSave', array('com_mymuse.product', $this, $isNew));
			JPluginHelper::importPlugin('mymuse');
			$res = $dispatcher->trigger('onMyMuseAfterSave', array('com_mymuse.product', $this, $isNew));
			if(isset($res[0])){
				$app->enqueueMessage($res[0], 'Notice');
			}
			
		}

		return $result; 

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

    		// first section is bucket name
    		$parts = explode(DS,$dir);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS).DS;

    		try{
    			$result = $this->_s3->putObject([
    					'Bucket'     => $bucket,
    					'Key'        => $uri,
    					'Body' => '',
    			]);
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$e->getMessage() );
    			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
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
    	if($params->get('my_use_s3',0)){
    		
    		// first section is bucket name
    		$parts = explode(DS,$file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);

    		try{
    			$result = $this->_s3->deleteObject([
    					'Bucket'     => $bucket,
    					'Key'        => $uri
    			]);
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$e->getMessage() );
    			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
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
     * @param   string file to 
     * @param   string file name moving to
     *
     * @return  boolean  True on success.
     */
    public function fileUpload($tmpName, $new_file)
    {
    	if(!file_exists($tmpName)){
    		$this->setError(JText::_("MYMUSE_FILE_DOES_NOT_EXIST").": ".$tmpName);
    		return false;
    	}
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	
    	
    	
    	if($params->get('my_use_s3',0)){
    		$s3 = MyMuseHelperAmazons3::getInstance();
    		// first section is bucket name
    		$parts = explode(DS,$new_file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		try{
    			$result = $this->_s3->putObject([
    					'Bucket'     => $bucket,
    					'Key'        => $uri,
    					'SourceFile' => $tmpName,
    			]);
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$e->getMessage());
    			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
    			echo $this->_s3->getError(); exit;
    			return false;
    		}
    	}else{
    		
    		if(!JFile::upload($tmpName, $new_file)){
    			$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName." ".$new_file);
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
    public function fileCopy($src, $dest)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3',0)){
    		//they are both on s3. Must download one 
    		$oldParts = explode(DS,$old_file);
    		
    		$oldBucket = array_shift($oldParts);
    		$oldBucket = trim($oldBucket, DS);
    		$uri = implode(DS, $oldParts);
    		
    		$jconfig = JFactory::getConfig();
    		$tmpName = $jconfig->get('tmp_path','').DS.array_pop($oldParts );
    		
    		$parts = explode(DS,$dest);
    		$newbucket = array_shift($parts);
    		$newbucket = trim($newbucket, DS);
    		$newname = implode(DS, $parts);
    		

    		try{
    			$result = $this->_s3->copyObject([
    				'Bucket' => $newbucket,
    				'CopySource' => $src,
    				'Key' => $newname,
    			]);
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$e->getMessage() );
    			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
    			return false;
    		}
    		
    		//now delete the old one
    		try{
    			$result = $this->_s3->deleteObject([
    					'Bucket'     => $oldBucket,
    					'Key'        => $uri
    			]);
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$e->getMessage() );
    			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
    			return false;
    		}
    	}else{
    
    		if(!JFile::copy("$src", "$dest")){
    			$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$src." ".$dest);
    			$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$src." ".$dest , 'error');
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
    	
    	if($params->get('my_use_s3',0)){

    		$parts = explode(DS,$file);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		$file = array_pop($parts);
    		$prefix = implode(DS, $parts);
    		
    		
    		try{
    			$objects = $this->_s3->getIterator('ListObjects', array('Bucket' => $bucket, 'Prefix' => $prefix));
    			foreach ($objects as $object) {
    				if($object['Key'] == $uri){
    					return true;
    				}
				}
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$this->_s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$this->_s3->getError() , 'error');
    			return false;
    		}
    		
    		return false;
    		
    	}else{
    		return file_exists($file);
    	}
    }
    
    public function folderMove($src, $dest)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3',0)){
    		if(!$this->folderNew($dest)){
    			return false;
    		}
    		$old_files = array();
    		$new_files = array();
    		// first section is bucket name
    		$parts = explode(DS,$src);
    		$srcBucket = array_shift($parts);
    		$srcBucket = trim($srcBucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		$uri = $uri.'/';
    		
    		$parts = explode(DS,$dest);
    		$targetBucket = array_shift($parts);
    		$targetBucket = trim($targetBucket, DS);
    		$targetUri = implode(DS, $parts);
    		$targetUri = trim($targetUri,DS);

  			try{
  				$objects = $this->_s3->getIterator('ListObjects', array('Bucket' => $srcBucket));
  				foreach ($objects as $object) {
  					$pos = strpos($object['Key'], $uri);
  					
  					if($pos !== false){
  						
  						$old_files[] = $object['Key'];
  						
  						$parts = explode(DS,$object['Key']);
  						$key = array_pop($parts);	
  						//copy the file
  						$targetKey = $targetUri.'/'.$key;
  						$copySource = $srcBucket.'/'.$object['Key'];
  						$new_files[] = $targetBucket.'/'.$targetKey;
  						//echo "object['Key'] = ".$object['Key'].' uri '.$uri.' MATCH<br /><br />';
  						//echo "Bucket = $targetBucket : Key = $targetKey : copySource = ".$copySource." <br /><br />";
  						if (! $this->trackExists ( $targetBucket.'/'.$targetKey )) {
							try {
								$this->_s3->copyObject ( array (
										'Bucket' => $targetBucket,
										'Key' => $targetKey,
										'CopySource' => $copySource 
								) );
							} catch ( S3Exception $e ) {
								// echo $e->getMessage() . "\n";
								$this->setError ( 'S3 Error: ' . $e->getMessage () );
								$application->enqueueMessage ( 'S3 Error: ' . $e->getMessage (), 'error' );
								return false;
							}
							// delete the old
							$result = $this->_s3->deleteObject ( array (
									'Bucket' => $srcBucket,
									'Key' => $object ['Key'] 
							) );
  						}
  					
  						
  					}
  					//echo "object['Key'] ".$object['Key'].' NO MATCH <br />';
  				}

  			} catch (S3Exception $e) {
  				//echo $e->getMessage() . "\n";
  				$this->setError( 'S3 Error: '.$e->getMessage() );
  				$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
  				return false;
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
    	}
    	return true;

    }
    
    public function fileFilesize($src)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3',0)){
    		
    		// first section is bucket name
    		$parts = explode(DS,$src);
    		$bucket = array_shift($parts);
    		$bucket = trim($bucket, DS);
    		$uri = implode(DS, $parts);
    		$uri = trim($uri,DS);
    		
    		try{
    			// HEAD object
    			$result= $this->_s3->headObject(array(
    					'Bucket' => $bucket,
    					'Key' => $uri
    			));
    		} catch (S3Exception $e) {
    			//echo $e->getMessage() . "\n";
    			$this->setError( 'S3 Error: '.$this->_s3->getError() );
    			$application->enqueueMessage('S3 Error: '.$this->_s3->getError() , 'error');
    			return false;
    		}
    		$arr = $result->toArray();
    		return $arr['ContentLength'];
    	}else{
    		return filesize($src);
    	}
    }
}
  
