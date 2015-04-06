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

jimport('joomla.application.component.controller');

class MymuseControllerReports extends MymuseuseController
{



	/**
	 * Custom Constructor
	 */

	function __construct()
	{
		JFactory::getApplication()->input->set( 'view', 'reports' );
		parent::__construct();

	}
	
	/**
	 * display the report
	 * @return void
	 */
	function report()
	{

		$db 	= JFactory::getDBO();
		$user   = JFactory::getUser();
        $userid = $user->get('id');
        $query 	= "SELECT id FROM #__mymuse_categories WHERE params LIKE '%owner_id=$userid\n%'";
        $jinput = JFactory::getApplication()->input;
        
        $db->setQuery($query);
   
        if(!$ids = $db->loadObjectList()){
        	$jinput->set('not_auth','1');
        	
        }
        
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
