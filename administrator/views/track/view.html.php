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
class MymuseViewTrack extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$this->task 	= $task 	= $input->get('task', 'edit');
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->lists 	= $this->get('Lists');
		$this->params 	= MyMuseHelper::getParams();

		$app 			= JFactory::getApplication();
		$view 			= $input->get('view');
		
        $isNew  		= ($this->item->id < 1);
		$lists['isNew'] = $isNew;
		
		$subtype = 'track';
        // allfiles
        if($task == "new_allfiles" || $task == "track.new_allfiles" || ($this->item->product_id && $this->item->allfiles)){
        	$layout = 'new_allfiles';
        	$this->setLayout('edit_allfiles');
			if(!$this->item->product_id){
        		$this->item->product_id= JRequest::getVar('product_id', 0);
        	}
        	JRequest::setVar('subtype','allfiles');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'allfiles');
  
        }


		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar($subtype,$this->item->product_id);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar($subtype='', $product_id=0)
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

		if($this->item->product_id){
			$title .= ' : <a href="index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->item->product_id.'">'.$this->item->parent->title."</a>";
		}else{
			$title .= " : ".$this->item->title;
		}
		JToolBarHelper::title(JText::_('COM_MYMUSE').' : '. $title, 'mymuse.png');
	
		if($layout == "listtracks"){
			// LIST TRACKS
			if(!$this->params->get('my_use_s3', 0)){
				JToolBarHelper::custom('product.uploadtrack', 'save-new.png', 'save-new_f2.png', 'MYMUSE_UPLOAD_TRACKS', false);
				JToolBarHelper::custom('product.uploadpreview', 'save-new.png', 'save-new_f2.png', 'MYMUSE_UPLOAD_PREVIEWS', false);
			}
			
			
			JToolBarHelper::editList('product.edit', 'MYMUSE_EDIT_TRACK');
			JToolBarHelper::addNew('product.addfile', 'MYMUSE_NEW_TRACK');
			JToolBarHelper::deleteList('','product.removefile','MYMUSE_DELETE_TRACKS');
			
			
			if(!$this->all_files){ 
				JToolBarHelper::addNew('product.new_allfiles', 'MYMUSE_ALL_TRACKS');
			}		  
			JToolBarHelper::apply('product.productreturn', 'MYMUSE_RETURN_TO_PRODUCT');
			
			
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-tracks?tmpl=component');
		}elseif($layout == "listitems"){
			// LIST ITEMS
			JToolBarHelper::apply('product.productreturn', 'MYMUSE_RETURN_TO_PRODUCT');
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-items?tmpl=component');
			
		}elseif ($this->task == "uploadtrack" || $this->task == "uploadpreview" ){
			JToolBarHelper::apply('product.cancelfile', 'MYMUSE_RETURN_TO_TRACKS');
			JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/247-product-items?tmpl=component');
		
		}elseif($subtype == "file" && $product_id){
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
		
		}elseif($subtype == "allfiles" && $product_id){
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
		
		}elseif($subtype == "item" && $product_id){
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
				'a.product_discount' => JText::_('MYMUSE_DISCOUNT'),
				'a.product_in_stock' => JText::_('MYMUSE_PRODUCT_IN_STOCK_LABEL')
		);
	}
}
