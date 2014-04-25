<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class myMuseControllerReports extends myMuseController
{



	/**
	 * Custom Constructor
	 */

	function __construct()
	{
		JRequest::setVar( 'view', 'reports' );
		parent::__construct();
		
	}
	
	/**
	 * display the report
	 * @return void
	 */
	function report()
	{

		JRequest::setVar( 'layout', 'report');
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}
	
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display()
    {
        parent::display();
    }

	



}
?>
