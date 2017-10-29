<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * tracks list controller class.
 */
class MymuseControllerTracks extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param	array	$config	An optional associative array of configuration settings.

	 * @return	MymuseControllertracks
	 * @see		JController
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}

		$this->registerTask('unfeatured', 'featured');

	}

		/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_mymuse.track.' . (int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			/** @var ContentModelArticle $model */
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}

			if ($value == 1)
			{
				$message = JText::plural('COM_CONTENT_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				$message = JText::plural('COM_CONTENT_N_ITEMS_UNFEATURED', count($ids));
			}
		}

		$view = $this->input->get('view', '');

		if ($view == 'featured')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=featured', false), $message);
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=articles', false), $message);
		}
	}
	
    function delete()
    {

    	$input 	= JFactory::getApplication()->input;
        $cid 	= $input->get('cid');

        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_( 'MYMUSE_SELECT_AN_ITEM_TO_DELETE' ) );
        }
        $model = $this->getModel('tracks');

        if(!$model->delete($cid)) {
        	
        	$this->msg = $model->getError();
            $this->setRedirect( 'index.php?option=com_mymuse&view=tracks',$this->msg  );
            return false;
        }else{
        	$this->msg = JText::_( 'MYMUSE_ITEM_DELETED' );
        	$this->setRedirect( 'index.php?option=com_mymuse&view=tracks',$this->msg  );
        }
        return true;
    }
	

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return	void
	 */

	
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'track', $prefix = 'MymuseModel', $config=array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	

	/**
	 * Changes the order of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function reorder()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$input 		= JFactory::getApplication()->input;
		// Initialise variables.
		$ids 		= $input->post->get('cid', array(), 'array');
		$inc 		= ($this->getTask() == 'orderup') ? -1 : +1;
		$view 		= $input->get('view','');
		$product_id 	= $input->get('product_id','');
		$layout 	= $input->get('layout','');

		
	
		$model = $this->getModel();

		$return = $model->reorder($ids, $inc);
		if ($return === false)
		{
			// Reorder failed.
	
			if($view == 'track' && $layout == 'edit' && $product_id){
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$product_id.$subtype, false), $message, 'error');
				return false;				
			}else{
			
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
				return false;
			}
		}
		else
		{
			// Reorder succeeded.
			if($view == 'track' && $layout == 'edit' && $product_id){
				
				$message = JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$product_id.$subtype, false), $message);
				return true;
			}else{
				
				$message = JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
				return true;
			}
		}
	}
	
	
	
	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$input 		= JFactory::getApplication()->input;
		$view 		= $input->get('view','');
		$product_id 	= $input->get('product_id','');
		$layout 	= $input->get('layout','');
		$subtype 	= $input->get('subtype', '');
		
		if($subtype){
			$subtype = "&subtype=$subtype";
		}
	
		// Get the input
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
	
		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);
	
		// Get the model
		$model = $this->getModel();
		
		// Save the ordering
		$return = $model->saveorder($pks, $order);
	
		if ($return === false)
		{
			// Reorder failed
			if($view == 'track' && $layout == 'edit' && $product_id){
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$product_id.$subtype, false), $message, 'error');
				return false;
			}else{
			
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
				return false;
			}
		}
		else
		{
			// Reorder succeeded.
			if($view == 'track' && $layout == 'edit' && $product_id){
			
				$message = JText::sprintf('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$product_id.$subtype, false), $message);
				return true;
			}else{
				$this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
				return true;
			}
		}
	}
	
	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$input 		= JFactory::getApplication()->input;
		$view 		= $input->get('view','');
		$product_id 	= $input->get('product_id','');
		$layout 	= $input->get('layout','');
		$id 		= $input->get('id','');
		$subtype 	= $input->get('subtype', '');

		// Get items to publish from the request.
		$cid 		= $input->post->get('cid', array(), 'array');
		$data 		= array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task 		= $this->getTask();
		$value 		= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish($cid, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value == 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		
		if($view == 'track' && $layout == 'edit' && $product_id){
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$product_id.'&subtype='.$subtype, false));
		}else{
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$id, false));
		}
	}
	

	
	function check()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_mymuse&view=tracks&layout=check', false));
	}
	
}