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

jimport('joomla.application.component.view');
require_once (JPATH_COMPONENT.DS.'models'.DS.'products.php');
/**
 * View to edit
 */
class MymuseViewProduct extends JViewLegacy
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



		$this->params 	= MyMuseHelper::getParams();
		$app 			= JFactory::getApplication();
		$subtype 		= $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'details');
		$task 			= JRequest::getVar('task', 'edit');
		$view 			= JRequest::getVar('view');
		

        $isNew  		= ($this->item->id < 1);
		$lists['isNew'] = $isNew;
		
		
		
		//setlayout
		$jinput = JFactory::getApplication()->input;
		$layout = $jinput->get('layout', 'edit');

		if($layout == "listtracks"){
			$this->tracks 	= $this->get('Tracks');
			$this->trackPagination = $this->get('TrackPagination');
		}
		if($layout == "listitems"){
			$this->items 	= $this->get('Items');
			$this->itemPagination = $this->get('ItemPagination');
		}

		$this->setLayout($layout);
 
		//new file || edit file
		if($task == "addfile" || (isset($this->item->parentid) && $this->item->parentid > 0 && !$this->item->product_allfiles && $subtype == "file")){
		
			$layout = 'edittracks';
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
        	$layout = 'new_allfiles';
        	$this->setLayout('edit_allfiles');
			if(!$this->item->parentid){
        		$this->item->parentid= JRequest::getVar('parentid', 0);
        	}
        	JRequest::setVar('subtype','allfiles');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'allfiles');
  
        }
        //item
        if($task == "additem" || (isset($this->item->parentid) && $this->item->parentid > 0 && $this->item->product_physical)){
        	
        	$layout = 'edititems';
        	$this->setLayout('edititems');
        	$this->attribute_skus = $this->get('Attributeskus');
        	$this->attributes = $this->get('Attributes');
        	if(!count($this->attribute_skus)){
        		//no attributes ye1!!
        		$msg = JText::_("MYMUSE_CREATE_ATTRIBUTE_FIRST");
        		$url = "index.php?option=com_mymuse&view=product&layout=listitems&id=".$this->item->parentid;
        		$app->redirect($url, $msg);
        		exit;
        	}
        	
        	
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
		$layout 	= $this->getLayout();
		
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
	
		if($layout == "listtracks"){
			// LIST TRACKS
			JToolBarHelper::apply('product.productreturn', 'MYMUSE_RETURN_TO_PRODUCT');
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-tracks?tmpl=component');
			
		}elseif($layout == "listitems"){
			// LIST ITEMS
			JToolBarHelper::apply('product.productreturn', 'MYMUSE_RETURN_TO_PRODUCT');
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-items?tmpl=component');
			
			
		}elseif($subtype == "file" && $parentid){
			//TRACK
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
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-tracks?tmpl=component#new-edit-track');			
		
		}elseif($subtype == "allfiles" && $parentid){
			// ALLFILES
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
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-tracks?tmpl=component#tracks-all-tracks');		
		
		}elseif($subtype == "item" && $parentid){
			// ITEMS
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
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/248-product-items?tmpl=component');
				
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
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/238-product-new-edit?tmpl=component');
		}
		
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.state' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.featured' => JText::_('JFEATURED')
		);
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields2()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.state' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.price' => JText::_('MYMUSE_PRICE'),
				'a.product_discount' => JText::_('MYMUSE_DISCOUNT')
		);
	}
}
