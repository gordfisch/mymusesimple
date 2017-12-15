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
 * View class for a list of Mymuse.
 */
class MymuseViewProducts extends JViewLegacy
{
	/**
	 * The item authors
	 *
	 * @var  stdClass
	 */
	protected $authors;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 */
	public $filterForm;


	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;


	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */

	public function display($tpl = null)
	{

		$input = JFactory::getApplication()->input;
		$layout = $input->get('layout', '');

		if($layout == "check"){
			$this->products = $this->get('Check');
			$this->sidebar = JHtmlSidebar::render();

			parent::display('check');
		}else{
			$this->state = $this->get ( 'State' );
			$this->items = $this->get ( 'Items' );
			
			$this->pagination = $this->get ( 'Pagination' );
			$this->authors = $this->get ( 'Authors' );
			$this->featured = $this->get ( 'Featured' );
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
			
			// Check for errors.
			if (count ( $errors = $this->get ( 'Errors' ) )) {
				throw new Exception(implode("\n", $errors), 500);
			}
			

			// We don't need toolbar in the modal window.
			if ($this->getLayout () !== 'modal') {
				$this->addToolbar ();
				$this->sidebar = JHtmlSidebar::render ();
			}
			$this->getSubCats ( $this->items );
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

		JToolBarHelper::title(JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_PRODUCTS'), 'mymuse.png');


        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'product';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('product.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit')) {
			    JToolBarHelper::editList('product.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {
            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('products.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('products.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			    JToolBarHelper::custom('products.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
            } else {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'products.delete','JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    //JToolBarHelper::archiveList('products.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
            	JToolBarHelper::custom('products.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
            JToolBarHelper::custom('products.check', 'publish.png', 'publish.png', 'MYMUSE_TEST_PRODUCTS', false);
		}
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
		    if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			    JToolBarHelper::deleteList('', 'products.delete','JTOOLBAR_DELETE');
			    JToolBarHelper::divider();
		    } else if ($canDo->get('core.edit.state')) {
			   JToolBarHelper::trash('products.trash','JTOOLBAR_TRASH');
			    JToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_mymuse');
		}
		JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/support/documentation/documentation-mymusesimple/products-list?tmpl=component');

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
	
	/*
	 * getSubCats
	* find cross referenced categories for each product
	* object $items
	*
	* return objects $items
	*/
	function getSubCats(&$items)
	{
		$db = JFactory::getDBO();
		for($i=0; $i < count($items); $i++){
			$query = "SELECT c.title FROM #__mymuse_product_category_xref as x
			LEFT JOIN #__categories as c on x.catid=c.id
			WHERE x.product_id=".$items[$i]->id;
			$items[$i]->subcats = "";
			$db->setQuery($query);
			if($res = $db->loadObjectList()){
				foreach($res as $r){
					$items[$i]->subcats .= $r->title.",";
				}
			}
			$items[$i]->subcats = preg_replace("/,$/","",$items[$i]->subcats);
			
			$registry = new JRegistry;
			$items[$i]->attribs = $registry->loadString($items[$i]->attribs);
		}
		 
	}
}
