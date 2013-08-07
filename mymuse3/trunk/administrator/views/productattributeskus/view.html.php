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

/**
 * View class for a list of Mymuse.
 */
class MymuseViewProductattributeskus extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->lists		= $this->get('Lists');
		$this->parent 		= $this->get('Parent');
		$this->sortfields 	= $this->getSortFields();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
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
		$link = '<a style="text-decoration:underline" href="index.php?option=com_mymuse&view=product&layout=edit&subtype=item&id='.$this->parent->id.'">';
		$link .= $this->parent->title.'</a>';
		JToolBarHelper::title(JText::_('COM_MYMUSE_TITLE_PRODUCTATTRIBUTES')." : ".$link, 'mymuse.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'productattributesku';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('productattributesku.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit')) {
			    JToolBarHelper::editList('productattributesku.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {

                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'productattributeskus.remove','JTOOLBAR_DELETE');

		}
		JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/153-product-items-list-attributes?tmpl=component');	
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
				'a.product_parent_id' => JText::_('MYMUSE_PRODUCT'),
				'a.name' => JText::_('JGLOBAL_TITLE'),
				'a.id' => JText::_('JGRID_HEADING_ID')

		);
	}
}
