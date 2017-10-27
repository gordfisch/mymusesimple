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
class MymuseViewTracks extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
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
			
			// Check for errors.
			if (count ( $errors = $this->get ( 'Errors' ) )) {
				JError::raiseError ( 500, implode ( "\n", $errors ) );
				return false;
			}
			
			// Levels filter.
			$options = array ();
			$options [] = JHtml::_ ( 'select.option', '1', JText::_ ( 'J1' ) );
			$options [] = JHtml::_ ( 'select.option', '2', JText::_ ( 'J2' ) );
			$options [] = JHtml::_ ( 'select.option', '3', JText::_ ( 'J3' ) );
			$options [] = JHtml::_ ( 'select.option', '4', JText::_ ( 'J4' ) );
			$options [] = JHtml::_ ( 'select.option', '5', JText::_ ( 'J5' ) );
			$options [] = JHtml::_ ( 'select.option', '6', JText::_ ( 'J6' ) );
			$options [] = JHtml::_ ( 'select.option', '7', JText::_ ( 'J7' ) );
			$options [] = JHtml::_ ( 'select.option', '8', JText::_ ( 'J8' ) );
			$options [] = JHtml::_ ( 'select.option', '9', JText::_ ( 'J9' ) );
			$options [] = JHtml::_ ( 'select.option', '10', JText::_ ( 'J10' ) );
			
			$this->f_levels = $options;
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

		JToolBarHelper::title(JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_TRACKS'), 'mymuse.png');


        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'track';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('track.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit')) {
			    JToolBarHelper::editList('product.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {
            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('tracks.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('tracks.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			    JToolBarHelper::custom('tracks.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
            } else {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'tracks.delete','JTOOLBAR_DELETE');
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
        if (isset($this->items[0]->state)) {
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
	
		//Sidebar stuff
		JHtmlSidebar::setAction('index.php?option=com_mymuse&view=tracks');
		
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_mymuse'), 'value', 'text', $this->state->get('filter.category_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_MAX_LEVELS'),
			'filter_level',
			JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_AUTHOR'),
			'filter_author_id',
			JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_LANGUAGE'),
			'filter_language',
			JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
		);

		JHtmlSidebar::addFilter(
		'-' . JText::_('JSELECT') . ' ' . JText::_('JTAG') . '-',
		'filter_tag',
		JHtml::_('select.options', JHtml::_('tag.options', true, true), 'value', 'text', $this->state->get('filter.tag'))
		);
	
	
	
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
