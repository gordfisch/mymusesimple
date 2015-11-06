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
 * Products list controller class.
 */
class MymuseControllerProducts extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param	array	$config	An optional associative array of configuration settings.

	 * @return	MymuseControllerProducts
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if (JRequest::getCmd('view') == 'featured') {
			$this->view_list = 'featured';
		}
		parent::__construct($config);

		$this->registerTask('unfeatured',	'featured');
	}
	
    function delete()
    {

        $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($cid);
        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_( 'MYMUSE_SELECT_AN_ITEM_TO_DELETE' ) );
        }
        $model = $this->getModel('products');

        if(!$model->delete($cid)) {
            echo "<script> alert('Error: ".$model->getError()."'); window.history.go(-1); </script>
            ";
            return false;
        }else{
        	$this->msg = JText::_( 'MYMUSE_ITEM_DELETED' );
        	$this->setRedirect( 'index.php?option=com_mymuse&view=products',$this->msg  );
        }
        return true;
    }
	

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return	void
	 */
	function featured()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('featured' => 1, 'unfeatured' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');
		
		$view 	= JRequest::getVar('view','');
		$parentid 	= JRequest::getVar('parentid','');
		$layout = JRequest::getVar('layout','');
		$id = JRequest::getVar('id','');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_mymuse.product.'.(int) $id)) {
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Feature the items.
			if (!$model->featured($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
			
		}

		if($view == 'product' && ($layout == 'listtracks' || $layout == 'listitems') && $parentid){
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid, false));
		}else{
			$this->setRedirect('index.php?option=com_mymuse&view=products');
		}
		
	}
	
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'product', $prefix = 'MymuseModel')
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

		// Initialise variables.
		$ids 	= JRequest::getVar('cid', null, 'post', 'array');
		$inc 	= ($this->getTask() == 'orderup') ? -1 : +1;
		$view 	= JRequest::getVar('view','');
		$parentid 	= JRequest::getVar('parentid','');
		$layout = JRequest::getVar('layout','');
		$subtype = JRequest::getVar('subtype', '');
		if($subtype){
			$subtype = "&subtype=$subtype";
		}
	
		$model = $this->getModel();

		$return = $model->reorder($ids, $inc);
		if ($return === false)
		{
			// Reorder failed.
	
			if($view == 'product' && $layout == 'edit' && $parentid){
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid.$subtype, false), $message, 'error');
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
			if($view == 'product' && $layout == 'edit' && $parentid){
				
				$message = JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid.$subtype, false), $message);
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
		$view 	= JRequest::getVar('view','');
		$parentid 	= JRequest::getVar('parentid','');
		$layout = JRequest::getVar('layout','');
		$subtype = JRequest::getVar('subtype', '');
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
			if($view == 'product' && $layout == 'edit' && $parentid){
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid.$subtype, false), $message, 'error');
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
			if($view == 'product' && $layout == 'edit' && $parentid){
			
				$message = JText::sprintf('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid.$subtype, false), $message);
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
		$view 	= JRequest::getVar('view','');
		$parentid 	= JRequest::getVar('parentid','');
		$layout = JRequest::getVar('layout','');
		$id = JRequest::getVar('id','');
		$subtype = JRequest::getVar('subtype', '');

		// Get items to publish from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

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
		
		if($view == 'product' && $layout == 'edit' && $parentid){
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid.'&subtype='.$subtype, false));
		}else{
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$id, false));
		}
	}
	

	function artistid()
	{
		$db = JFactory::getDBO();
		$query = "SELECT x.catid, x.product_id, c.title
FROM nxgmsc15_mymuse_product_category_xref as x, nxgmsc15_mymuse_product as p, nxgmsc15_categories as c
WHERE x.catid != p.catid AND
x.product_id=p.id AND c.id=x.catid";
	
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$query = 'UPDATE nxgmsc15_mymuse_product SET artistid = "'.$r->catid.'" WHERE
            id="'.$r->product_id.'"';
			$db->setQuery($query);
			if($db->execute()){
				echo "updated ".$r->product_id." to have artistid ".$r->catid." ".$r->title."<br />";
			}
		}
	}
	
	function makePlaylists()
	{
		jimport('joomla.filesystem.file');
		$db = JFactory::getDBO();
		$query = "SELECT id, alias from #__categories WHERE parent_id=30";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		$db->setQuery($query);
		$all = array();
		foreach($res as $r){
			$filename = $r->alias.".js";
			echo "Making list for $filename <br />";
	
			$arr = array();
			$query = "SELECT p.title as name, parent.title as album, a.title as artist,
            CONCAT('/media/audio/previews/',p.file_preview) as url,
            CONCAT('/images/releases/180px/',parent.product_sku,'.jpg') as cover_art_url
            FROM nxgmsc15_mymuse_product as p
            LEFT JOIN nxgmsc15_mymuse_product as parent on p.parentid=parent.id
            LEFT JOIN nxgmsc15_categories as a on parent.artistid=a.id
            WHERE p.parentid>0 AND parent.catid=".$r->id;
            $db->setQuery($query);
	            if($tracks = $db->loadObjectList()){
	            foreach ($tracks as $track){
	            $arr['songs'][] = $track;
	            $all['songs'][] = $track;
	            }
	
	            $jstring = "Amplitude.init(".json_encode($arr).");";
	            $jstring = preg_replace("~,~",",\n",$jstring);
	            $jstring = preg_replace("~\[~","[\n",$jstring);
	            		$jstring = preg_replace("~\{~","{\n",$jstring);
	                $jstring = preg_replace("~\},~","\n},",$jstring);
	                		$jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
	
	                		if($fh = fopen(JPATH_ROOT.DS.'media'.DS.'audio'.DS.'playlists'.DS.$filename, "w")){
	                		fwrite($fh,$jstring);
	                    fclose($fh);
	            }
	            		//print_pre($jstring);
	            		//echo "<br />";
	            		}else{
	            		echo "No tracks for $filename <br />";
	            		//echo $query;
	            }
		}
		//one for all
		echo "Making list for catalog.js <br />";
		$jstring = "Amplitude.init(".json_encode($all).");";
		$jstring = preg_replace("~,~",",\n",$jstring);
        $jstring = preg_replace("~\[~","[\n",$jstring);
	    $jstring = preg_replace("~\{~","{\n",$jstring);
	    $jstring = preg_replace("~\},~","\n},",$jstring);
	    $jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
	
	    if($fh = fopen(JPATH_ROOT.DS.'media'.DS.'audio'.DS.'playlists'.DS.'catalog.js', "w")){
	        fwrite($fh,$jstring);
	        fclose($fh);
		}
	
	}
	
}