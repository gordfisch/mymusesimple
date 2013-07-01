<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
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
        JToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/169-mymuse-options?tmpl=component');
        
        parent::display($tpl);
    }
}

?>