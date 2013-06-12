<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

class myMuseViewReports extends Jview
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null){
		
		$mainframe = JFactory::getApplication();
		$option = 'com_mymuse';
		$params = MyMuseHelper::getParams();
		
		// Get data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');

		$this->pagination		= $this->get('Pagination');
		$this->orders_total 	= count($this->items);
		$this->lists  			=& $this->get( 'Lists');
		$this->orders_summary 	=& $this->get( 'OrderSummary');
		$this->items_summary  	=& $this->get( 'ItemsSummary');
		$this->catid			= $mainframe->getUserStateFromRequest( 'filter.catid','catid','','int' );
		
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
		JToolBarHelper::title(JText::_('COM_MYMUSE').' : '. JText::_( 'MYMUSE_MYMUSE_REPORTS' ), 'mymuse.png');


		JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/167-reports?tmpl=component');
		


	}

}
