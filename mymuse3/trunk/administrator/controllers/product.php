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

jimport('joomla.application.component.controllerform');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

/**
 * Product controller class.
 */
class MymuseControllerProduct extends JControllerForm
{

    function __construct() {
    	
    	$input = JFactory::getApplication()->input;
    	$subtype = $input->get('subtype');
    	if(isset($subtype) && $subtype == "file"){
    		$this->view_list = "product";
    	}else{
    		$this->view_list = 'products';
    	}
    	
    	parent::__construct();

   	 	$this->registerTask( 'additem', 'edititem' );
        $this->registerTask( 'applyitem', 'saveitem' );
        $this->registerTask( 'save2newitem', 'saveitem' );
        
        $this->registerTask( 'addfile', 'edititem' );
        $this->registerTask( 'editfile', 'edititem' );
        $this->registerTask( 'save2newfile', 'saveitem' );
        $this->registerTask( 'savefile', 'saveitem' );
        $this->registerTask( 'applyfile', 'saveitem' );
        $this->registerTask( 'publishfile', 'publishitem' );
        $this->registerTask( 'unpublishfile', 'publishitem' );
        $this->registerTask( 'cancelfile', 'cancelitem' );
        $this->registerTask( 'removefile', 'removeitem' );
        
		$this->registerTask( 'new_allfiles', 'edititem' );
		$this->registerTask( 'edit_allfiles', 'edititem' );
		$this->registerTask( 'save_allfiles', 'saveitem' );
		$this->registerTask( 'apply_allfiles', 'saveitem' );
		
		$this->registerTask( 'addattribute', 'editattribute' );
		
		$this->registerTask( 'save2newfile', 'saveitem' );
		
		$this->registerTask( 'uploadtrack', 'uploadscreen' );
		$this->registerTask( 'uploadpreview', 'uploadscreen' );
		
		$this->registerTask( 'deletevariation', 'saveitem' );
		
		$cid = $input->get( 'cid', array(0));
		if($cid[0] > 0){
			$input->set('id',$cid[0]);
		}
		$input->set('view','product');
		
    }
    
    
    
    /**
	 * saveitem
	 * 
	 * store the item to the database
	 * @return void
	 */
    function saveitem()
    { 
    	$input 				= JFactory::getApplication()->input;
        $post 				= JRequest::get('post');
     
        $this->id 			= isset($post['id'])? $post['id'] : null ;
        $this->parentid 	= isset($post['parentid'])? $post['parentid'] : 0;
        $form 				= $post['jform'];
		$this->product_sku 	= $form['product_sku'];
		$db 				= JFactory::getDBO();
		
		$input 				= JFactory::getApplication()->input;

		$subtype 			= $post['subtype'];
		$layout 			= @$post['layout'];
		$model 				= $this->getModel();
        $table 				= $model->getTable();

    	// is this the special 'AllFiles'?
		if(isset($form['product_allfiles']) && $form['product_allfiles'] == 1){
			$subtype = 'allfiles';
			$table->product_allfiles = 1;
		}elseif(!isset($post['subtype']) || $post['subtype'] == ""){
			$subtype = 'item';
		}


		if($subtype == "file" || $subtype == "allfiles"){
			
			if ($this->save()) {
				//get the product id
				$query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$db->escape($this->product_sku)."'";			
				$db->setQuery($query);
				if(!$this->id = $db->loadResult()){
					$this->msg = JText::_( 'MYMUSE_COULD_NOT_FIND_ID' );
					$this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->parentid, $this->msg );
					return false;
				}
			
				$task = $input->get('task');
			
				switch ($task )
				{
				case 'apply_allfiles':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_ALL_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit_allfiles&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
					break;
				case 'save_allfiles':
					$this->msg = JText::_( 'MYMUSE_ALL_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&subtype=files', $this->msg );
					break;
				case 'save2newfile':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.addfile&subtype='.$post['subtype'].'&parentid='.$this->parentid, $this->msg );
					break;
				case 'applyfile':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.editfile&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
					break;
				case 'savefile':
				default:
					$this->msg = JText::_( 'MYMUSE_FILE_SAVED' );;
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&subtype='.$post['subtype'], $this->msg );
					break;
				}
			}else {

        		$this->msg = JText::_( 'MYMUSE_ERROR_SAVING_FILE' ).": ".$table->getError();
        	
        		JFactory::getApplication()->enqueueMessage($this->msg, 'notice');
        		if($this->id){
        			$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.editfile&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
        		}else{
        			$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.addfile&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
				}

        	}
 
		//save an item
		}elseif ($this->save()) {
			
			//get the product id
			$query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
			$db->setQuery($query);

			if(!$this->id = $db->loadResult()){
				$this->msg = JText::_( 'MYMUSE_COULD_NOT_FIND_ID' );
				$this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->parentid, $this->msg );
				return false;
			}
			//now we have an id, update the attributes
			JRequest::setVar('itemid',$this->id);
			$model->updateAttributes();

        	switch ( JRequest::getVar('task', null, 'default', 'cmd'))
			{
				
				case 'save2newitem':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_ITEM_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.additem&subtype=item&parentid='.$this->parentid, $this->msg );
					break;
					
				case 'applyitem':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_ITEM_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edititem&id='. $this->id."&subtype=item", $this->msg );
					break;

				case 'saveitem':
				default:
					$this->msg = JText::_( 'MYMUSE_ITEM_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='. $this->parentid."&subtype=item", $this->msg );
					break;
				}

        } else {
        	$this->msg = JText::_( 'MYMUSE_ERROR_SAVING_ITEM' ).' : '.$this->getError();
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->parentid."&subtype=item", $this->msg );
        }
        
        

    }
    
 
    function cancelitem()
    {
        // Checkin the item
        $model = $this->getModel('product');
        $model->checkin();
        $parentid = JRequest::getVar( 'parentid', '', 'post', 'int' );
        $subtype = JRequest::getVar( 'subtype', '' );
        $this->msg = JText::_( 'MYMUSE_ITEM_CANCELLED' );
        if($subtype == 'file'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='.$parentid,$this->msg );
        }elseif($subtype == 'item'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='.$parentid,$this->msg );
        }else{
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$parentid,$this->msg );
        }
    }
    
    function productreturn()
    {
    	// Checkin the item
    	$model = $this->getModel('product');
    	$model->checkin();
    	$parentid = JRequest::getVar( 'parentid', '', 'post', 'int' );
    	
    	
    	$this->msg = JText::_( 'MYMUSE_ITEM_CANCELLED' );
    	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$parentid,$this->msg );
    }
	

    function removeitem()
    {

        $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
       
        JArrayHelper::toInteger($cid);
        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_( 'MYMUSE_SELECT_AN_ITEM_TO_DELETE' ) );
        }
		$parentid = JRequest::getVar( 'parentid', '', 'post', 'int' );
		$subtype = JRequest::getVar( 'subtype', '', 'post' );
		$layout = JRequest::getVar( 'layout', '', 'post' );
        $model = $this->getModel('product');

        if(!$model->delete($cid)) {
            echo "<script> alert('Error: ".$model->getError(true)."'); window.history.go(-1); </script>
            ";
            }
        $this->msg = JText::_( 'MYMUSE_ITEM_DELETED' );
        $url = 'index.php?option=com_mymuse&view=product&task=edit&id='.$parentid;
        if($layout){
        	$url .= "&layout=$layout";
        }
        $this->setRedirect( $url,$this->msg  );
    }
	
    /**
     * Method to save the submitted ordering values for records via AJAX.
     * brought in by arboreta from libraries/legacy/controller/admin.php
     * for saving tracks
     *
     * @return  void
     *
     * @since   3.0
     */
    public function saveOrderAjax()
    {
    	//MyMuseHelper::logMessage("here we are Ajax\n");
    	// Get the input
    	$pks = $this->input->post->get('cid', array(), 'array');
    	$order = $this->input->post->get('order', array(), 'array');
    
    	// Sanitize the input
    	JArrayHelper::toInteger($pks);
    	JArrayHelper::toInteger($order);
    
    	// Get the model
    	$model = $this->getModel();
    	//$model = $this->getModel('Products', 'MyMuseModel', array('ignore_request' => true));;
    
    	// Save the ordering
    	$return = $model->saveorder($pks, $order);
    
    	if ($return)
    	{
    		echo "1";
    	}
    
    	// Close the application
    	JFactory::getApplication()->close();
    }
    
    
    public function uploadscreen()
    {
    	parent::display();  
    }
    
    public function cancelupload()
    {
    	
    }
    
    /**
     *
     * File upload handler
     *
     * @return string JSON response
     */
    public function upload()
    {

    	$app = JFactory::getApplication();
    	$params 	= MyMuseHelper::getParams();
    
    	// 5 minutes execution time
    	@set_time_limit(5 * 60);
    
    	//enable valid json response when debugging is disabled
    	if(!$params->get('my_debug'))
    	{
    		error_reporting(0);
    	}
    
    	$session    = JFactory::getSession();
    	$user       = JFactory::getUser();
    
    	$cleanupTargetDir = false; //remove old files
    	$maxFileAge = 5 * 3600; // Temp file age in seconds
    
    	//directory for file upload
    	$targetDirWithSep  = $app->input->get('uploaddir',$params->get('my_download_dir'), 'string');
    	
    	//check for snooping
    	$targetDirCleaned  = JPath::check($targetDirWithSep);
    	//finally
    	$targetDir = $targetDirCleaned;

    	// Get parameters
    	$chunk = $app->input->get('chunk', 0, 'request');
    	$chunks = $app->input->get('chunks', 0, 'request');
    
    	//current file name
    	$fileNameFromReq = $app->input->get('name', '');
    	// Clean the fileName for security reasons
    	$fileName = JFile::makeSafe($fileNameFromReq);
    
    	//check file extension
    	$ext_images = $params->get('my_plupload_image_file_extensions');
    	$ext_other  = $params->get('my_plupload_other_file_extensions');
    
    	//echo "fileName = $fileName <br />";
    	
    	//prepare extensions for validation
    	$exts = $ext_images . ',' . $ext_other;
    	$exts_lc = strtolower($exts);
    	$exts_arr = explode(',', $exts_lc);
    
    	//check token
    	if(!$session->checkToken('request'))
    	{
    		$this->_setResponse(400, JText::_('JINVALID_TOKEN'));
    	}
    
    	//check user perms
    	if(!$user->authorise('core.create', 'com_mymuse'))
    	{
    		$this->_setResponse(400, JText::_('MYMUSE_ERROR_PERM_DENIED'));
    	}
    
    	//directory check
    	if(!file_exists($targetDir) && !is_dir($targetDir) && strpos(COM_MEDIAMU_BASE_ROOT, $targetDir) !== false)
    	{
    		$this->_setResponse(101, JText::_('MYMUSE_ERROR_UPLOAD_INVALID_PATH'));
    	}
    
    	//file type check
    	if(!in_array(JFile::getExt($fileName), $exts_arr))
    	{
    		$this->_setResponse(100, JText::_('MYMUSE_ERROR_UPLOAD_INVALID_FILE_EXTENSION'));
    	}
    
    	// Make sure the fileName is unique but only if chunking is disabled
    	if ($chunks < 2 && file_exists($targetDir . DS . $fileName))
    	{
    		$ext = strrpos($fileName, '.');
    		$fileName_a = substr($fileName, 0, $ext);
    		$fileName_b = substr($fileName, $ext);
    
    		$count = 1;
    		while (file_exists($targetDir . DS . $fileName_a . '_' . $count . $fileName_b))
    		{
    			$count++;
    		}
    
    		$fileName = $fileName_a . '_' . $count . $fileName_b;
    	}
    
    	$filePath = $targetDir . DS . $fileName;
    
    	/**
    	// Remove old temp files
    	if ($cleanupTargetDir && ($dir = opendir($targetDir)))
    	{
    		while (($file = readdir($dir)) !== false)
    		{
    			$tmpfilePath = $targetDir . DS . $file;
    
    			// Remove temp file if it is older than the max age and is not the current file
    			if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part"))
    			{
    				JFile::delete($tmpfilePath);
    			}
    		}
    
    		closedir($dir);
    	}
    	else
    	{
    		$this->_setResponse(100, 'Failed to open directory. '.$dir);
    	}
    */
    	// Look for the content type header
    	if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
    	{
    		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
    	}
    
    
    	if (isset($_SERVER["CONTENT_TYPE"]))
    	{
    		$contentType = $_SERVER["CONTENT_TYPE"];
    	}
    
    	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
    	if (strpos($contentType, "multipart") !== false)
    	{
    		if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
    		{
    			// Open temp file
    			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    			if ($out)
    			{
    				// Read binary input stream and append it to temp file
    				$in = fopen($_FILES['file']['tmp_name'], "rb");
    
    				if ($in)
    				{
    					while ($buff = fread($in, 4096))
    					{
    						fwrite($out, $buff);
    					}
    				}
    				else
    				{
    					$this->_setResponse (101, "Failed to open input stream.");
    				}
    
    				fclose($in);
    				fclose($out);
    				JFile::delete($_FILES['file']['tmp_name']);
    			}
    			else
    			{
    				$this->_setResponse (102, "Failed to open output stream.");
    			}
    		}
    		else
    		{
    			$this->_setResponse (103, "Failed to move uploaded file");
    		}
    	}
    	else
    	{
    		// Open temp file
    		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    
    		if ($out)
    		{
    			// Read binary input stream and append it to temp file
    			$in = fopen("php://input", "rb");
    
    			if ($in)
    			{
    				while ($buff = fread($in, 4096))
    				{
    					fwrite($out, $buff);
    				}
    			}
    			else
    			{
    				$this->_setResponse (101, "Failed to open input stream.");
    			}
    
    			fclose($in);
    			fclose($out);
    		}
    		else
    		{
    			$this->_setResponse (102, "Failed to open output stream.");
    		}
    	}
    
    	// Check if file has been uploaded
    	if (!$chunks || $chunk == $chunks - 1)
    	{
    		// Strip the temp .part suffix off
    		@rename("{$filePath}.part", $filePath);
    	}
    
    	$this->_setResponse(0, $filePath, false);
    
    }
    
    /**
     *
     * Set the JSON response and exists script
     *
     * @param int $code Error Code
     * @param string $msg Error Message
     * @param bool $error
     */
    private function _setResponse($code, $msg = null, $error = true)
    {
    	if($error)
    	{
    		$jsonrpc = array (
    				"error"     => 1,
    				"code"      => $code,
    				"msg"       => $msg
    		);
    	}
    	else
    	{
    		$jsonrpc = array (
    				"error"     => 0,
    				"code"      => $code,
    				"msg"       => "File uploaded! ".$msg
    		);
    	}
    
    	die(json_encode($jsonrpc));
    
    }
    


}