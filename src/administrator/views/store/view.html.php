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
 * View to edit
 */
class MymuseViewStore extends JViewLegacy
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
		$this->css		= $this->get('Css');
		$this->params 	= MyMuseHelper::getParams();
		
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
	 */
	protected function addToolbar()
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

		JToolBarHelper::title(JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_STORE'), 'mymuse.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('store.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('store.save', 'JTOOLBAR_SAVE');
		}


		if (empty($this->item->id)) {
			JToolBarHelper::cancel('store.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('store.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/support/documentation/documentation-mymusesimple/store-edit?tmpl=component');
		
	}
}
