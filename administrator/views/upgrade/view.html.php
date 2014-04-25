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
class MymuseViewUpgrade extends JViewLegacy
{


	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$preflight 		= $this->get('PreFlight');
		$model = $this->getModel();
		if(!$preflight){
			$this->msg = $model->getError();
			$tpl = 'error';
			$this->addToolbar2();
			parent::display($tpl);
			return true;
			
		}
		$this->form		= $this->get('Form');
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

		JToolBarHelper::title(JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_UPGRADE'), 'mymuse.png');
		JToolBarHelper::custom('upgrade.importFromMymuse15', 'upload.png', 'upload_f2.png', 'COM_MYMUSE_TITLE_UPGRADE', true);
		
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar2()
	{
	
		JToolBarHelper::title(JText::_('MYMUSE').' : '.JText::_('COM_MYMUSE_TITLE_UPGRADE'), 'mymuse.png');
		JToolBarHelper::custom('upgrade.notask', 'upload.png', 'upload_f2.png', 'COM_MYMUSE_TITLE_UPGRADE', true);
	
	}
}
