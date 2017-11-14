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
class MymuseTableTrack extends JTable
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
		$preview 		= $input->get('preview', '');
		$current_preview = $input->get('current_preview', '');
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
						$my_download_path = $download_path.$ext.DS;
					}
					

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

					$file_length = $this->fileFilesize($new_file);
						
					// TODO: get this to work with s3
					$track_time = '';
					
					if(isset($new_file) && is_file($new_file)
							&& strtolower(pathinfo($new_file, PATHINFO_EXTENSION)) == "mp3"){
						$m = new mp3file($new_file);
						$a = $m->get_metadata();
						if ($a['Encoding']=='VBR' || $a['Encoding']=='CBR'){
							$track_time = $a["Length mm:ss"];
						}
					}
					
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
			
			$this->track = json_encode($current_files);
		}
	
		//all files
		if(isset($form['allfiles']) && $form['allfiles']){

			for($p = 0; $p < count($params->get('my_formats')); $p++){
				$file_name = JFilterOutput::stringURLSafe($form['product_sku']."-full-release-". $params->get('my_formats')[$p]);
				$current_files[$p] = array(
							'file_name' => $file_name,
							'file_length' => '',
							'file_ext' => $params->get('my_formats')[$p],
							'file_alias'=> $file_name,
							'file_downloads'=> '',
							'file_time' => $track_time
					);

			}
			$this->track = json_encode($current_files);
			$this->type = "audio";
		}
		
		// Previews
		
		//from select boxes
		if(isset($preview) && $preview){
			if($this->product_id){
				$path = MyMuseHelper::getSitePath($this->product_id, 1);
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
			$this->preview = $file_preview_name;
		}else{
			$this->preview = isset($current_preview)? $current_preview : '';
		}

		$this->checkin();

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
			
		}
		
		return $result; 

	}
	
	/**
	 * Manage Previews
	 * Remove old one/Select from drop down
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
				
				if($this->fileExists($old_file)){
					if(!$this->fileCopy($old_file, $new_file)){
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
