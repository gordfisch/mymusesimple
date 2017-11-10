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
class MymuseTabletrack extends JTable
{
	

	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{

		parent::__construct('#__mymuse_track', 'id', $db);
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
			$this->setError(JText::_('MYMUSE_FILE_MUST_HAVE_A_TITLE').print_pre($this));
			return false;
		}


		if (trim(str_replace('&nbsp;', '', $this->description)) == '') {
			$this->description = '';
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
		if($sku = $this->_db->loadResult()){
			$this->setError(JText::_("MYMUSE_FILE_MUST_HAVE_A_UNIQUE_SKU").' '.$this->product_sku);
			$app->enqueueMessage(JText::_("MYMUSE_FILE_MUST_HAVE_A_UNIQUE_SKU").' '.$this->product_sku, 'error');
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

		$app 			= JFactory::getApplication();
		$input 			= $app->input;
		$task 			= $input->get('task');
		$form 			= $input->get('jform', '', 'array');
		$select_files 	= $input->get('select_file', '' ,'array');
		$date			= JFactory::getDate();
		$user			= JFactory::getUser();
	
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$db = JFactory::getDBO();

		if(!isset($this->id)){
        	$this->id = $this->_id	= isset( $form['id'] )? $form['id'] : $input->get('id','') ;
		}
		$this->product_id 	= isset($form['product_id'])? $form['product_id'] : $input->get('product_id',0) ;
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
			
			$isNew = 1;
		}




		//converting old style file_name to json
		if(!$isNew && $this->track){
			$current_files = json_decode($this->track);
			if(!is_array($current_files) && $this->track){
				$ext = MyMuseHelper::getExt($this->track);
				$current_files['var1'] = (object) array(
						'file_name' => $this->track,
						'file_length' => $this->track_length,
						'file_ext' => $ext,
						'file_alias'=> $this->alias,
						'file_downloads'=> $this->track_downloads
				);
			}
		}else{
			$current_files = array();
		}

		
        // get artist alias
        if($this->product_id){
        	$artist_alias = MyMuseHelper::getArtistAlias($this->product_id, 1);
        	$album_alias = MyMuseHelper::getAlbumAlias($this->product_id, 1);
        	$download_path = MyMuseHelper::getdownloadPath($this->product_id,1);
        	
        }   

 		// if they selected a file from drop down
 		$select_files = isset( $post['select_file'] )? $post['select_file']: '';
 		if(count($select_files)){
 			$arr = array();
 			for( $i = 0; $i < count($select_files); $i++ ){
 				if(isset($select_files[$i]) && $select_files[$i] != ''){
 					$arr[] = $select_files[$i];
 				}
 			}
 			$select_files = $arr;
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
			$this->track = json_encode($current_files);
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
					if($params->get('my_use_sring_url_safe')){
						$file_name = JFilterOutput::stringURLSafe($name).'.'.$ext;
					}else{
						$file_name = $select_file;
					}
				
					if( 1 == $params->get('my_download_dir_format') && !$params->get('my_use_s3',0)){
						//by format
						$download_path .= $ext;
					}
					
					if($params->get('my_encode_filenames') ){
						$name = md5($select_file . time()).'.'.$ext;
						$file_alias = $name;
						$new_file = $download_path.$name;
					}else{
						$new_file = $download_path.$file_name;
						$file_alias = '';
					}
					
					$old_file = $download_path.$select_file;
					
					if($old_file != $new_file){
						if(!$this->trackCopy($old_file, $new_file)){
							return false;
						}
						if(!$this->trackDelete($old_file)){
							return false;
						}
					}

					$file_length = $this->trackFilesize($new_file);
						
					// TODO: get this to work with s3
					$file_time = '';
					
					if(isset($new_file) && is_file($new_file)
							&& strtolower(pathinfo($new_file, PATHINFO_EXTENSION)) == "mp3"){
						$m = new mp3file($new_file);
						$a = $m->get_metadata();
						if ($a['Encoding']=='VBR' || $a['Encoding']=='CBR'){
							$this->track_time = $a["Length mm:ss"];
						}
					}
					
					$file_downloads = isset($current[$i]->file_downloads)? $current[$i]->file_downloads : "0";
					//  save this to the file_name
					$current_files[$i] = array(
							'file_name' => $file_name,
							'file_length' => $file_length,
							'file_ext' => $ext,
							'file_alias'=> $file_alias,
							'file_downloads'=> $file_downloads
					);
				}
			}
			
			$this->track = json_encode($current_files);
		}
		
		//all files
		if(isset($post['allfiles']) && $post['allfiles']){

			for($p = 0; $p < count($params->get('my_formats')); $p++){
				$current_files[$p] = array(
							'file_name' => JFilterOutput::stringURLSafe($form['product_sku']."-full-release-". $params->get('my_formats')[$p]),
							'file_length' => '',
							'file_ext' => $params->get('my_formats')[$p],
							'file_alias'=> '',
							'file_downloads'=> ''
					);

			}
			$this->track = json_encode($current_files);
			$this->track_type = "audio";
		}
		
		// Previews
		
		//from select boxes
		$this->track_preview = isset($post['current_preview'])? $post['current_preview'] : $this->track_preview;
		$this->track_preview_2 = isset($post['current_preview_2'])? $post['current_preview_2'] : $this->track_preview_2;
		$this->track_preview_3 = isset($post['current_preview_3'])? $post['current_preview_3'] : $this->track_preview_3;
		if($params->get('my_use_sring_url_safe')){
			
			
		}
		
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
		
		if($this->parentid){
			$path = MyMuseHelper::getSitePath($this->parentid, 1);
		}elseif ($this->id){
			$path = MyMuseHelper::getSitePath($this->id, 0);
		}
		

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
	

		$this->checkin();
		if($this->parentid){
			$this->checkin($this->parentid);
		}

		$result = parent::store($updateNulls);
		
		if($result){

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
		$application = JFactory::getApplication();
		$params = MyMuseHelper::getParams();
		$jinput = $application->input;
		
		$post 			= $jinput->post->getArray();
		$form 			= $jinput->get('jform', array(), 'ARRAY');
		
	
		
		$preview_name 	= 'product_'.$preview;
		$remove_name 	= 'remove_'.$preview;
		$current_name 	= 'current_'.$preview;
		$file_preview_name = 'file_'.$preview;
		

		// remove old one?
		if(isset($post[$remove_name]) && $post[$remove_name] == "on"
				&& $post[$current_name] != ""){
			 
			$old = $path.$post[$current_name];
			
			if($this->trackExists($old)){

				if(!$this->trackDelete($old)){
					$this->setError(JText::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$old);
					return false;
				}
			}
			$this->$file_preview_name = '';
		}

		//upload a file
		if(isset($_FILES[$preview_name]) && $_FILES[$preview_name]['size'] >  0){
			$ext = MyMuseHelper::getExt($_FILES[$preview_name]['name']);
			$_FILES[$preview_name]['name'] = preg_replace("/\.$ext$/","",$_FILES[$preview_name]['name']);
			if($params->get('my_use_string_url_safe')){
				$this->$file_preview_name = JFilterOutput::stringURLSafe($_FILES[$preview_name]['name']).'.'.$ext;
			}else{
				$this->$file_preview_name = $_FILES[$preview_name]['name'].'.'.$ext;
			}
		
			$tmpName  = $_FILES[$preview_name]['tmp_name'];
			$new = $path.$this->$file_preview_name;
	
			if (file_exists($tmpName)){
			
				if(!$this->trackExists($new)){
					
					if(is_writable(dirname($new))){
						//rename($tmpName, $new);
						if (! $this->trackUpload ( $tmpName, $new )) {
							return false;
						}
						
					}else{
						
						if($this->folderNew(dirname($new))){
							//rename($tmpName, $new);
							if (! $this->trackUpload ( $tmpName, $new )) {
								return false;
							}
							
						}else{
							echo "MYMUSE_FOLDER_NOT_WRITABLE"; exit;
							$application->enqueueMessage(JText::_("MYMUSE_FOLDER_NOT_WRITABLE").": ".dirname($new), 'error');
							return false;
						}
					}
					chmod($new, 0644);
				}else{
					$application->enqueueMessage(JText::_("MYMUSE_FILE_EXISTS").": ".$new, 'error');
					return false;
				}
						
			}else{
					$application->enqueueMessage(JText::_("MYMUSE_FILE_DOES_NOT_EXIST").": ".$tmpName, 'error');
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
			$name = preg_replace("/\.$ext$/","",$file_preview);
			if($params->get('my_use_sring_url_safe')){
				$this->$file_preview_name = JFilterOutput::stringURLSafe($name).'.'.$ext;
			}else{
				$this->$file_preview_name = $name.'.'.$ext;
			}
		
			$old_file = $path.$file_preview;
			$new_file = $path.$this->$file_preview_name;
			
			if($old_file != $new_file){
				
				if($this->trackExists($old_file)){
					if(!$this->trackCopy($old_file, $new_file)){
						$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file);
						$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file, 'error');
						return false;
					}
					
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
    	if($params->get('my_use_s3')){
    		
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
    	
    	
    	
    	if($params->get('my_use_s3')){
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
    public function fileCopy($old_file, $new_file)
    {
    	$application = JFactory::getApplication();
    	$params = MyMuseHelper::getParams();
    	if($params->get('my_use_s3')){
    		//they are both on s3. Must download one 
    		$oldParts = explode(DS,$old_file);
    		
    		$oldBucket = array_shift($oldParts);
    		$oldBucket = trim($oldBucket, DS);
    		$uri = implode(DS, $oldParts);
    		
    		$jconfig = JFactory::getConfig();
    		$tmpName = $jconfig->get('tmp_path','').DS.array_pop($oldParts );
    		
    		$parts = explode(DS,$new_file);
    		$newbucket = array_shift($parts);
    		$newbucket = trim($newbucket, DS);
    		$newname = implode(DS, $parts);
    		

    		try{
    			$result = $this->_s3->copyObject([
    				'Bucket' => $newbucket,
    				'CopySource' => $old_file,
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
    
    		if(!JFile::copy("$old_file", "$new_file")){
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
    	if($params->get('my_use_s3')){
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
  				/*
  				print_pre($old_files);
  				print_pre($new_files);
  				exit;
  				*/
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
    	if($params->get('my_use_s3')){
    		
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
