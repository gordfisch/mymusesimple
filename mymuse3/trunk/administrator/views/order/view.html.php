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
 * View to edit
 */
class MymuseViewOrder extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $lists;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->params   = MyMuseHelper::getParams();
		$this->lists	= $this->get('Lists');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$task = JRequest::getVar('task', null, 'default', 'cmd');
		switch ($task)
		{
			case "mailcustomer":
			{
				include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'mymuse.class.php' );
				$MyMuseStore	=& MyMuse::getObject('store','models');
				$store			= $MyMuseStore->getStore();
				$extra = '';
				// Process order plugins
    			$dispatcher	=& JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$results = $dispatcher->trigger('onRenderOrder', array ( ));
				if(isset($results[0])){
					$extra = $results[0];
				}
				$currency = MyMuseHelper::getCurrency($this->item->order_currency);
        		$this->item->currency = $currency;
        		$this->item->do_html = 0;
        		
				$this->params->set('my_currency_code',$currency['currency_code']);
				$this->params->set('my_currency_symbol',$currency['symbol']);
				
			} break;
			
			default:
			{
				$this->addToolbar();
			}
			
			
		}
		
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

		JToolBarHelper::title(JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_ORDER'), 'mymuse.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('order.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('order.save', 'JTOOLBAR_SAVE');
		}


		if (empty($this->item->id)) {
			JToolBarHelper::cancel('order.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('order.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::help('', false, 'http://www.mymuse.ca/en/documentation/72-help-files-3-x/235-order-view?tmpl=component');
		
	}
}
