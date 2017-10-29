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

		$this->view_list = 'tracks';
        parent::__construct();
    }
    
    
    
    /**
	 * saveitem
	 * 
	 * store the item to the database
	 * @return void
	 */
    function save()
    { 

        $this->id 			= $this->input->get('id', 0);
        $this->product_id 	= $this->input->get('product_id', 0);
        $form 				= $this->input->getArray('jform');
		$this->product_sku 	= $form['product_sku'];
		$db 				= JFactory::getDBO();
		
		$layout 			= $this->input->get('layout', 0);
		$model 				= $this->getModel();
        $table 				= $model->getTable();

    	// is this the special 'AllFiles'?
		if(isset($form['allfiles']) && $form['allfiles'] == 1){
			$subtype = 'allfiles';
			$table->allfiles = 1;
		}

		$task = $this->input->get('task');

		if ($this->save()) {
			//get the track id
			if(!$this->id){
				$this->msg = JText::_( 'MYMUSE_COULD_NOT_FIND_ID' );
				//do we have an SKU?
				$query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
				$db->setQuery($query);

				if(!$this->id = $db->loadResult()){
					$this->msg .= JText::_( 'MYMUSE_COULD_NOT_FIND_SKU' );
					$this->setRedirect( 'index.php?option=com_mymuse&iew=track&task=track.edit&product_id='. $this->product_id, $this->msg );
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
			
			default:
				$this->msg = JText::_( 'MYMUSE_FILE_SAVED' );;
				$this->setRedirect( 'index.php?option=com_mymuse&view=track&layout=listtracks&id='. $this->product_id.'&subtype='.$post['subtype'], $this->msg );
				break;
			}
		}else{
			
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
    
 
    function canceltrack()
    {
        // Checkin the item
        $model      = $this->getModel('track');
        $model->checkin();
        $product_id = $this->input->get('product_id', 0);
        $form       = $this->input->getArray('jform');

        $this->msg  = JText::_( 'MYMUSE_ITEM_CANCELLED' );
        $this->setRedirect( 'index.php?option=com_mymuse&view=tracks&product_id='.$product_id,$this->msg );
        
    }
    
    function productreturn()
    {
    	// Checkin the item
    	$model = $this->getModel('track');
    	$model->checkin();
    	$product_id = $this->input->get('product_id', 0);
    	
    	
    	$this->msg = JText::_( 'MYMUSE_ITEM_CANCELLED' );
    	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$product_id,$this->msg );
    }
	
	
    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        /** @var ContentModelArticle $model */
        $model = $this->getModel('Track', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_mymuse&view=tracks' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
    
    

   

}