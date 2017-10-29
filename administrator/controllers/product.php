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
    	$this->view_list = 'products';
    	parent::__construct();

   	 	$this->registerTask( 'additem', 'edititem' );
        $this->registerTask( 'applyitem', 'saveitem' );
        $this->registerTask( 'save2newitem', 'saveitem' );
        
		
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
						$this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->parentid, $this->msg );
						return false;
					}
				}
			
				
			
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
				case 'deletevariation':
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
				
        		$this->msg = $this->getError();
        		JFactory::getApplication()->enqueueMessage($this->msg, 'error');
        		switch ($task )
        		{
        			case 'apply_allfiles':
        				
        				
        			case 'save_allfiles':
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.edit_allfiles&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.new_allfiles&parentid=".$this->parentid.'&subtype='.$post['subtype'], $this->msg );
        				}
        				break;
        			default:
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.editfile&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.addfile&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'], $this->msg );
						}
						break;
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
        }elseif($subtype == 'allfiles'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='.$parentid,$this->msg );
        }else{
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$parentid,$this->msg );
        }
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
    
    
    public function listtracks()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->get('id');
        $url = 'index.php?option=com_mymuse&view=tracks&product_id='.$id;
        $this->setRedirect( $url);
        return;
    }


}