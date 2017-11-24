<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class MyMuseViewMyMuse extends JViewLegacy
{

	protected $params;
	protected $state;

    /**
     * Hellos view display method
     * @return void
     **/
    function display($tpl = null)
    {
		$this->params = myMuseHelper::getParams();

        JToolBarHelper::title( JText::_( 'MyMuse' ), 'mymuse.png' );
        JToolBarHelper::preferences('com_mymuse');
        JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/en/documentation/72-help-files-3-x/246-welcome-screen?tmpl=component');
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }
}

?>