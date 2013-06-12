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

jimport('joomla.application.component.view');
require_once (JPATH_COMPONENT.DS.'models'.DS.'products.php');
/**
 * View to edit
 */
class MymuseViewProduct extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->lists 	= $this->get('Lists');
		
		$this->tracks 	= $this->get('Tracks');
		$this->trackPagination = $this->get('TrackPagination');
		
		$this->items 	= $this->get('Items');
		$this->itemPagination = $this->get('ItemPagination');
		
		
		$this->params 	= MyMuseHelper::getParams();
		$app 			= JFactory::getApplication();
		$subtype 		= $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'details');
		$task 			= JRequest::getVar('task', 'edit');
		$view 			= JRequest::getVar('view');
		

        $isNew  		= ($this->item->id < 1);

		$lists['isNew'] = $isNew;
		
		
		
		///default layout
		$this->setLayout('edit');
		
		//new file || edit file
		if($task == "addfile" || ($this->item->parentid && !$this->item->product_allfiles)){
        	$this->setLayout('edittracks');
        	$filelists = $this->get('FileLists');
        	$this->lists = array_merge($this->lists,$filelists);
        	if(!$this->item->parentid){
        		$this->item->parentid= JRequest::getVar('parentid', 0);
        	}
        	JRequest::setVar('subtype','file');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'file');
  
        }
        // allfiles
        if($task == "new_allfiles" || ($this->item->parentid && $this->item->product_allfiles)){
        	$this->setLayout('edit_allfiles');
			if(!$this->item->parentid){
        		$this->item->parentid= JRequest::getVar('parentid', 0);
        	}
        	JRequest::setVar('subtype','allfiles');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'allfiles');
  
        }
        //item
        if($task == "additem" || ($this->item->parentid && $this->item->product_physical)){
        	$this->setLayout('edititems');
        	$this->attribute_skus = $this->get('Attributeskus');
        	$this->attributes = $this->get('Attributes');
        	$isNew  = (@$items->id < 1);
        	$this->lists['isNew'] = $isNew;
        	JRequest::setVar('subtype','item');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'item');

        }
		
        //It's the parent, set the user state
        if($this->item->id && $this->item->parentid == 0){
        	$app = JFactory::getApplication();
        	$parentid = $app->getUserStateFromRequest("com_mymuse.parentid", 'parentid', $this->item->id);
        }
        if(!$this->item->id  && $this->item->parentid == 0 && $this->item->parentid == 0){
        	$subtype = "details";
        }

		$this->lists['subtype'] 	= $subtype;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar($subtype,$this->item->parentid);
		$layout			= JRequest::getVar('layout');

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar($subtype='', $parentid=0)
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
		    $checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
		$canDo		= MymuseHelper::getActions();
		$title = JText::_('COM_MYMUSE_TITLE_PRODUCT');

		if($this->item->parentid){
			$title .= ' : <a href="index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->item->parent->id.'">'.$this->item->parent->title."</a>";
		}else{
			$title .= " : ".$this->item->title;
		}
		JToolBarHelper::title(JText::_('COM_MYMUSE').' : '. $title, 'mymuse.png');
	
		if($subtype == "file" && $parentid){
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.applyfile', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.savefile', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				JToolBarHelper::custom('product.save2newfile', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancelfile', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancelfile', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/151-product-tracks-new-edit?tmpl=component');			
		}elseif($subtype == "allfiles" && $parentid){
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.apply_allfiles', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.save_allfiles', 'JTOOLBAR_SAVE');
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/170-product-all-files?tmpl=component');		
		}elseif($subtype == "item" && $parentid){
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.applyitem', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.saveitem', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				JToolBarHelper::custom('product.save2newitem', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/155-product-item-new-edit?tmpl=component');
				
		}else{
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				JToolBarHelper::custom('product.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/148-product-new-edit?tmpl=component');
		}
		
	}
}
