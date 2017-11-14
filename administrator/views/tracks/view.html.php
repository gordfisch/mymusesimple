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

/**
 * View class for a list of Mymuse Tracks.
 */
class MymuseViewTracks extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $parent;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		$input 	= JFactory::getApplication()->input;
		$layout = $input->get('layout', '');
		

		if($layout == "check"){
			$this->trackss = $this->get('Check');
			$this->sidebar = JHtmlSidebar::render();

			parent::display('check');
		}else{
			$this->state = $this->get ( 'State' );
			$this->items = $this->get ( 'Items' );
			$this->parent = $this->get ( 'Parent' );
			
			$this->pagination = $this->get ( 'Pagination' );
			$this->authors = $this->get ( 'Authors' );
			$this->featured = $this->get ( 'Featured' );
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
			
			// Check for errors.
			if (count ( $errors = $this->get ( 'Errors' ) )) {
				JError::raiseError ( 500, implode ( "\n", $errors ) );
				return false;
			}
			

			// We don't need toolbar in the modal window.
			if ($this->getLayout () !== 'modal') {
				$this->addToolbar ();
				$this->sidebar = JHtmlSidebar::render ();
			}
			

			parent::display ( $tpl );
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'mymuse.php';

		$state	= $this->get('State');
		$canDo	= MymuseHelper::getActions($state->get('filter.category_id'));
		$input = JFactory::getApplication()->input;
		$product_id = $input->get('product_id', 0);

		$jform 		= $input->get('jform', array(), 'ARRAY'); 
		$product_ids = isset($jform['product_id'])? $jform['product_id'] : 0;
		if($product_ids && count($product_ids)){
			$dbo = JFactory::getDBO();
			$titles = array();
			$product_id = $product_ids[0];
			foreach($product_ids as $prod_id){
				$query = "SELECT title from #__mymuse_product WHERE id='$prod_id'";
				$dbo->setQuery($query);
				$titles[] = $dbo->loadResult();
			}
			$this->parent->title = implode(" : ",$titles);
		}


		$title = JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_TRACKS');
		if($this->parent){
			$title .= ' : '.$this->parent->title;
		}
		JToolBarHelper::title($title, 'mymuse.png');


        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'track';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('track.add','JTOOLBAR_NEW');
			    JToolBarHelper::addNew('track.new_allfiles','MYMUSE_ALL_TRACKS');
		    }

		    if ($canDo->get('core.edit')) {
			    JToolBarHelper::editList('track.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {
            if (isset($this->items[0]->published)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('tracks.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('tracks.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			    JToolBarHelper::custom('tracks.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
            }

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    //JToolBarHelper::archiveList('products.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
            	JToolBarHelper::custom('tracks.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
            JToolBarHelper::custom('tracks.check', 'publish.png', 'publish.png', 'MYMUSE_TEST_PRODUCTS', false);
		}
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->published)) {
		    if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			    JToolBarHelper::deleteList('', 'tracks.delete','JTOOLBAR_DELETE');
			    JToolBarHelper::divider();
		    } else if ($canDo->get('core.edit.state')) {
			   JToolBarHelper::trash('tracks.trash','JTOOLBAR_TRASH');
			    JToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_mymuse');
		}
		JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/239-products-list?tmpl=component');
	

	
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
				'category_title' => JText::_('JCATEGORY'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.featured' => JText::_('JFEATURED')
		);
	}
	
}
