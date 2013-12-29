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

jimport('joomla.application.component.controllerform');

/**
 * Product controller class.
 */
class MymuseControllerProduct extends JControllerForm
{

    function __construct() {
    	
    	$subtype = JRequest::getVar('subtype','');
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
		
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		if($cid[0] > 0){
			JRequest::setVar('id',$cid[0]);
		}
		

    }
    

    /**
	 * saveitem
	 * 
	 * store the item to the database
	 * @return void
	 */
    function saveitem()
    { 
    	
        $post = JRequest::get('post');
     
        $this->id = isset($post['id'])? $post['id'] : null ;
        $this->parentid = isset($post['parentid'])? $post['parentid'] : 0;
        $form = $post['jform'];
		$this->product_sku = $form['product_sku'];
		$db = JFactory::getDBO();

		$subtype = $post['subtype'];
		$layout = @$post['layout'];
		$model =& $this->getModel();
        $table =& $model->getTable();

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
				$query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
				
				$db->setQuery($query);
				if(!$this->id = $db->loadResult()){
					$this->msg = JText::_( 'MYMUSE_COULD_NOT_FIND_ID' );
					$this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->parentid, $this->msg );
					return false;
				}
			
				$task = JRequest::getVar('task', null, 'default', 'cmd');
				//echo $task; exit;
				switch ($task )
				{
				case 'apply_allfiles':
					$this->msg = JText::_( 'MYMUSE_CHANGES_TO_ALL_FILE_SAVED' );
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.addfile&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
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
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.addfile&id='. $this->id.'&subtype='.$post['subtype'], $this->msg );
					break;
				case 'savefile':
				default:
					$this->msg = JText::_( 'MYMUSE_FILE_SAVED' );;
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&subtype='.$post['subtype'], $this->msg );
					break;
				}
			}else {
        		$this->msg = JText::_( 'MYMUSE_ERROR_SAVING_FILE' ).": ".$this->getError();
        		//echo $this->msg; print_pre($_POST); exit;
        		if($this->id){
        			$task = "editfile";
        		}else{
        			$task = "addfile";
				}
        		$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.$task&parentid='.$this->parentid.'&subtype='.$post['subtype'], $this->msg );
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
        $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$parentid,$this->msg );
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
    	MyMuseHelper::logMessage("here ware Ajax\n");
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
    


}