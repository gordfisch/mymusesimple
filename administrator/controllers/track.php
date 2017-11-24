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

    /**
     * @var     object
     */
    protected $input = null;
    

    function __construct() {

		$this->view_list = 'tracks';
        $this->input = JFactory::getApplication()->input;

        parent::__construct();

        $this->registerTask( 'savetrack', 'savetrack' );
        $this->registerTask( 'applytrack', 'savetrack' );
        $this->registerTask( 'save2newtrack', 'savetrack' );

        $this->registerTask( 'new_allfiles', 'edit_allfiles' );
        $this->registerTask( 'apply_allfiles', 'save_allfiles' );
    }
    
    


    function edit_allfiles()
    {

        $this->input->set('layout','edit_allfiles');
        $this->edit();

    }

    function save_allfiles()
    {

        $this->input->set('layout','edit_allfiles');
        $this->savetrack();

    }
    
    /**
	 * savetrack
	 * 
	 * store the item to the database
	 * @return void
	 */
    function savetrack($key = NULL, $urlVar = NULL)
    { 
    
        $this->id 			= $this->input->get('id', 0);
        $form 				= $this->input->get('jform', '', 'array');
        $this->product_id   = $form['product_id'];
        if(!$this->product_id){
            $this->msg = JText::_( 'MYMUSE_COULD_NOT_FIND_ID' );
            $this->setRedirect( 'index.php?option=com_mymuse&view=tracks', $this->msg );
            return false;
        }
		$layout 			= $this->input->get('layout', '');
		$model 				= $this->getModel();
        $table 				= $model->getTable();


		$task = $this->input->get('task');

		if ($this->save()) {
			//get the track id		
			switch ($task )
			{


			case 'applytrack':
                $this->msg = JText::_( 'MYMUSE_CHANGES_TO_FILE_SAVED' );
                if($form['allfiles']){
                    $this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.edit_allfiles&id='. $this->id.'&product_id='. $this->product_id, $this->msg );
                }else{
                    $this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.edit&id='. $this->id.'&product_id='. $this->product_id, $this->msg );
                }
                break;
            case 'savetrack':
                $this->msg = JText::_( 'MYMUSE_FILE_SAVED' );
                $this->setRedirect( 'index.php?option=com_mymuse&view=tracks&product_id='. $this->product_id, $this->msg );
             
                break;
			default:
				$this->msg = JText::_( 'MYMUSE_FILE_SAVED' );;
				$this->setRedirect( 'index.php?option=com_mymuse&view=track&task=track.edit&id='. $this->id.'&product_id='. $this->product_id, $this->msg );
				break;
			}
		}else{
			
    		$this->msg = $this->getError();

    		JFactory::getApplication()->enqueueMessage($this->msg, 'error');
    		switch ($task )
    		{

    			default:
                    if($form['allfiles']){
                        if($this->id){
                            $this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.edit_allfiles&product_id=".$this->product_id.'&id='.$this->id, $this->msg );
                        }else{
                            $this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.new_allfiles&product_id=".$this->product_id.'&id='.$this->id, $this->msg );
                        }


                    }else{
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.edit&product_id=".$this->product_id.'&id='.$this->id, $this->msg );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=track&task=track.add&product_id=".$this->product_id.'&id='.$this->id, $this->msg );
    					}
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