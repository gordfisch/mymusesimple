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
		$input = JFactory::getApplication()->input;
		$this->task 	= $task 	= $input->get('task', 'edit');
		
		if($task == "addfile" || $task == "additem" || $task == "new_allfiles"){
			$input->set('id',0);
		}

		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->lists 	= $this->get('Lists');
		 

		$this->params 	= MyMuseHelper::getParams();

		$app 			= JFactory::getApplication();
		$subtype 		= $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'details');
		
		$view 			= $input->get('view');
		
        $isNew  		= ($this->item->id < 1);
		$lists['isNew'] = $isNew;
		
		//setlayout
		$layout = $input->get('layout', 'edit');
		
		//listtracks
		if($layout == "listtracks"){
			$this->tracks 	= $this->get('Tracks');
			//See if there is an all files zip
			$this->all_files = 0;
			for ($i=0, $n=count( $this->tracks ); $i < $n; $i++){
				if($this->tracks[$i]->product_allfiles == "1"){
					$this->all_files = 1;
				}
			}
			$this->trackPagination = $this->get('TrackPagination');
		}
		if($layout == "listitems"){
			$this->items 	= $this->get('Items');
			$this->itemPagination = $this->get('ItemPagination');
		}

		$this->setLayout($layout);
		
		//new file || edit file
		if($task == "addfile" || $task == "editfile" || 
				(isset($this->item->parentid) && $this->item->parentid > 0 
						&& !$this->item->product_allfiles && $subtype == "file")){
			if($task == "addfile"){
				$input->set('id','0');
			}
			$layout = 'edittracks';
        	$this->setLayout('edittracks');
        	$filelists = $this->get('FileLists');

        	$this->lists = array_merge($this->lists,$filelists);
        	
        	if(!$this->item->parentid){
        		$this->item->parentid= JRequest::getVar('parentid', 0);
        	}
        	$input->set('subtype','file');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'file');
        	
        }
        
        // allfiles
        elseif($task == "new_allfiles" || $task == "product.new_allfiles" || ($this->item->parentid && $this->item->product_allfiles)){
        	$layout = 'new_allfiles';
        	$this->setLayout('edit_allfiles');
			if(!$this->item->parentid){
        		$this->item->parentid= JRequest::getVar('parentid', 0);
        	}
        	JRequest::setVar('subtype','allfiles');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'allfiles');
  
        }
     
        //item
        elseif($task == "additem" || $task == "product.additem" || (isset($this->item->parentid) && $this->item->parentid > 0 && $this->item->product_physical == 1)){
        
        	$layout = 'edititems';
        	$this->setLayout('edititems');
        	$this->attribute_skus = $this->get('Attributeskus');
        	$this->attributes = $this->get('Attributes');
        	if(!count($this->attribute_skus)){
        		//no attributes yet!!
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

        //upload screen
        elseif($task == "uploadtrack" || $task == "uploadpreview" ){
        	
        	JHtml::_('jquery.framework');
        	require_once (JPATH_COMPONENT.DS.'helpers'.DS.'pluploadscript.php');
        	
        	$this->setLayout('upload');
        	$this->item ->artist_alias = MyMuseHelper::getArtistAlias($this->item->id, 1);
        	$this->item->album_alias = MyMuseHelper::getAlbumAlias($this->item->id);
        	$language = JFactory::getLanguage();
        	$lang = $language->getTag();
        	if($this->task == "uploadtrack" ){
        		$this->currentDir = $this->params->get('my_download_dir') . DS . $this->item ->artist_alias . DS . $this->item->album_alias . DS;
        		$this->message = JText::_("MYMUSE_UPLOAD_TRACKS");
        	}else{
        		$this->currentDir = JPATH_ROOT . DS . $this->params->get('my_preview_dir') . DS . $this->item ->artist_alias . DS . $this->item->album_alias . DS;
        		$this->message = JText::_("MYMUSE_UPLOAD_PREVIEWS");
        	}
        	
        	$langfiles        = JPATH_COMPONENT_ADMINISTRATOR.'/assets/plupload/js/i18n/';
        	$PLdataDir        = JURI::root() . "administrator/components/com_mymuse/assets/plupload/";
        	$document         = JFactory::getDocument();

        	
        	$PLuploadScript   = new PLuploadScript($PLdataDir, $this->currentDir);
        	
        	$runtimeScript    = $PLuploadScript->runtimeScript;
        	$runtime          = $PLuploadScript->runtime;
        	//add default PL css
        	$document->addStyleSheet($PLdataDir . 'css/plupload.css');
        	
        	//add PL styles and scripts
        	$document->addStyleSheet($PLdataDir . 'js/jquery.plupload.queue/css/jquery.plupload.queue.css', 'text/css', 'screen');
        	//$document->addScript($PLdataDir . 'js/jquery.min.js');
        	$document->addScript($PLdataDir . 'js/plupload.full.min.js');
        	
        	// load plupload language file
        	if ($lang){
        		if (JFile::exists($langfiles . $lang.'.js')){
        			$document->addScript($PLdataDir . 'js/i18n/'.$lang.'.js');
        		} else {
        			$document->addScript($PLdataDir . 'js/i18n/en-GB.js');
        		}
        	}
        	$document->addScript($PLdataDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');
        	$document->addScriptDeclaration( $PLuploadScript->getScript() );
        	
        	//set variables for the template
        	$this->enableLog =$this->params->get("my_plupload_enable_uploader_log");
        	$this->runtime = $runtime;
        	
        	
        	$this->addToolbar($subtype,$this->item->parentid);
        	parent::display($tpl);
        	return true;
        }
        
        $jason = json_decode($this->item->file_name);
        if(is_array($jason)){
        	$this->item->file_name = $jason;
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
			$title .= ' : <a href="index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->item->parentid.'">'.$this->item->parent->title."</a>";
		}else{
			$title .= " : ".$this->item->title;
		}
		JToolBarHelper::title(JText::_('COM_MYMUSE').' : '. $title, 'mymuse.png');
	
		if($layout == "listtracks"){
			// LIST TRACKS
			JToolBarHelper::custom('product.uploadtrack', 'save-new.png', 'save-new_f2.png', 'MYMUSE_UPLOAD_TRACKS', false);
			JToolBarHelper::custom('product.uploadpreview', 'save-new.png', 'save-new_f2.png', 'MYMUSE_UPLOAD_PREVIEWS', false);
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
		
		}elseif($subtype == "file" && $parentid){
			//TRAC
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
				'a.product_discount' => JText::_('MYMUSE_DISCOUNT'),
				'a.product_in_stock' => JText::_('MYMUSE_PRODUCT_IN_STOCK_LABEL')
		);
	}
}
