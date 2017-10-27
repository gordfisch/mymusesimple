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
class MymuseControllerTrack extends JControllerForm
{

    function __construct() {
    	
    	$input = JFactory::getApplication()->input;
    	$subtype = $input->get('subtype');
    	if(isset($subtype) && $subtype == "file"){
    		$this->view_list = "track";
    	}else{
    		$this->view_list = 'tracks';
    	}
    	
    	parent::__construct();


        
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
		
		
		$this->registerTask( 'save2newfile', 'saveitem' );
		
		
		$this->registerTask( 'deletevariation', 'saveitem' );
		
		$cid = $input->get( 'cid', array(0));
		if($cid[0] > 0){
			$input->set('id',$cid[0]);
		}
		$input->set('view','track');
		
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
        $this->product_id 	= isset($post['product_id'])? $post['product_id'] : 0;
        $form 				= $post['jform'];
		$this->product_sku 	= $form['product_sku'];
		$db 				= JFactory::getDBO();
		
		$input 				= JFactory::getApplication()->input;

		$subtype 			= $post['subtype'];
		$layout 			= @$post['layout'];
		$model 				= $this->getModel();
        $table 				= $model->getTable();

    	// is this the special 'AllFiles'?
		if(isset($form['allfiles']) && $form['allfiles'] == 1){
			$subtype = 'allfiles';
			$table->allfiles = 1;
		}elseif(!isset($post['subtype']) || $post['subtype'] == ""){
			$subtype = 'item';
		}

		$task = $input->get('task');
		if($subtype == "file" || $subtype == "allfiles"){
			
			if ($this->save()) {
				//get the product id
				if(!$this->id){
					$this->msg = JText::_( 'MYMUSE_COULD_NOT_FIND_ID' );
					//do we have an SKU?
					$query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
					$db->setQuery($query);

					if(!$this->id = $db->loadResult()){
						$this->msg .= JText::_( 'MYMUSE_COULD_NOT_FIND_SKU' );
						$this->setRedirect( 'index.php?option=com_mymuse&iew=track&task=track.edit&id='. $this->product_id, $this->msg );
						return false;
					}
				}
			
				
			
				switch ($task )
				{
				case 'apply_allfiles':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_ALL_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.edit_allfiles&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
					break;
				case 'save_allfiles':
					$this->msg = JText::_( 'MYMUSE_ALL_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=track&layout=listtracks&id='. $this->product_id.'&subtype=files', $this->msg );
					break;
				case 'save2newfile':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.addfile&subtype='.$post['subtype'].'&product_id='.$this->product_id, $this->msg );
					break;
				case 'applyfile':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.editfile&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
					break;
				case 'deletevariation':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.editfile&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
					break;
				case 'savefile':
				default:
					$this->msg = JText::_( 'MYMUSE_FILE_SAVED' );;
					$this->setRedirect( 'index.php?option=com_mymuse&view=track&layout=listtracks&id='. $this->product_id.'&subtype='.$post['subtype'], $this->msg );
					break;
				}
			}else {
				
        		$this->msg = $this->getError();
        		JFactory::getApplication()->enqueueMessage($this->msg, 'error');
        		switch ($task )
        		{
        			case 'apply_allfiles':
        				
        				
        			case 'save_allfiles':
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.edit_allfiles&product_id=".$this->product_id.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.new_allfiles&product_id=".$this->product_id.'&subtype='.$post['subtype'], $this->msg );
        				}
        				break;
        			default:
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.editfile&product_id=".$this->product_id.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.addfile&product_id=".$this->product_id.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
						}
						break;
        		}
        	}
 

		}
        
     }   
    
 
    function cancelitem()
    {
        // Checkin the item
        $model = $this->getModel('track');
        $model->checkin();
        $product_id = JRequest::getVar( 'product_id', '', 'post', 'int' );
        $subtype = JRequest::getVar( 'subtype', '' );
        $this->msg = JText::_( 'MYMUSE_ITEM_CANCELLED' );
        if($subtype == 'file'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=track&product_id='.$product_id,$this->msg );
        }elseif($subtype == 'allfiles'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=tracks&&product_id='.$product_id,$this->msg );
        }else{
        	$this->setRedirect( 'index.php?option=com_mymuse&view=track&layout=edit&id='.$product_id,$this->msg );
        }
    }
    
    function productreturn()
    {
    	// Checkin the item
    	$model = $this->getModel('track');
    	$model->checkin();
    	$product_id = JRequest::getVar( 'product_id', '', 'post', 'int' );
    	
    	
    	$this->msg = JText::_( 'MYMUSE_ITEM_CANCELLED' );
    	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$product_id,$this->msg );
    }
	

    function removeitem()
    {

        $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
       
        JArrayHelper::toInteger($cid);
        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_( 'MYMUSE_SELECT_AN_ITEM_TO_DELETE' ) );
        }
		$product_id = JRequest::getVar( 'product_id', '', 'post', 'int' );
		$subtype = JRequest::getVar( 'subtype', '', 'post' );
		$layout = JRequest::getVar( 'layout', '', 'post' );
        $model = $this->getModel('product');

        if(!$model->delete($cid)) {
            echo "<script> alert('Error: ".$model->getError(true)."'); window.history.go(-1); </script>
            ";
            }
        $this->msg = JText::_( 'MYMUSE_ITEM_DELETED' );
        $url = 'index.php?option=com_mymuse&view=tracks&product_id='.$product_id;
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
    
 
    function import_products2()
    {
    	$limit = JRequest::getVar('limit','50');
    	$limitstart = JRequest::getVar('limitstart','0');
    	$myfile = JRequest::getVar('myfile','50');
    	$url = "index.php?option=com_mymuse&task=product.import_products&limit=$limit&limitstart=$limitstart&myfile=$myfile";
    	echo '<meta http-equiv="refresh" content="2;url='.$url.'">';
    	echo "<h1>Product Import</h1>";
    	echo "Limitstart $limitstart Limit: $limit <br />";
    	echo "<a href='index.php?option=com_mymuse&view=mymuse'>Stop</a>";
    
    }
    


}