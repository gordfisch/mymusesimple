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

jimport( 'joomla.application.component.view' );

class myMuseViewReports extends Jview
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null){
		global $mainframe,$params, $option;

		$user   = JFactory::getUser();
        $userid = $user->get('id');
        $profile = $user->get('profile');
		$catid = $profile['category_owner'];

        if(!$catid){
        	JRequest::setVar('not_auth','1');
        	JRequest::setVar( 'layout', 'no_auth');
        	JRequest::setVar( 'task', 'no_auth');
        	parent::display('no_auth');
        	return;
        }else{
			JRequest::setVar( 'layout', 'report');
        }
		
        $task = JRequest::getVar('task', null, 'default', 'cmd');

		switch ($task)
		{
			case 'no_auth':
			{
				JRequest::setVar('layout', 'no_auth');
				parent::display('no_auth');
				return;
				
			}
			
			default:
			{

				$mainframe = JFactory::getApplication();
				$option = 'com_mymuse';
				$this->params = MyMuseHelper::getParams();
				
				// Get data from the model
				$this->state		= $this->get('State');
				$this->items		= $this->get('Items');
			
				$this->pagination		= $this->get('Pagination');
				$this->orders_total 	= count($this->items);
				$this->lists  			=& $this->get( 'Lists');
				$this->orders_summary 	=& $this->get( 'OrderSummary');
				$this->items_summary  	=& $this->get( 'ItemsSummary');
				$this->catid			= $mainframe->getUserStateFromRequest( 'filter.catid','catid',$catid,'int' );
			
				// Check for errors.
				if (count($errors = $this->get('Errors'))) {
					JError::raiseError(500, implode("\n", $errors));
					return false;
				}
			} break;
		}

		parent::display($tpl);

	}

}
?>
