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

class myMuseViewReports extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null){
		global $mainframe,$params, $option;

		$user   = JFactory::getUser();
        $userid = $user->get('id');
        $this->Itemid = JRequest::getVar('Itemid');
        $profile = $user->get('profile');
		$catid = $profile['category_owner'];
		
		if(!$catid || !$userid){
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
				$this->state		    = $this->get('State');
                // get all possible order ids filtered by dates and status
				$this->items		    = $this->get('Items');
                //get just our orders containing products with our catids
                $this->orders		    = $this->get('Orders');
				$this->pagination		= $this->get('Pagination');
				$this->orders_total 	= count($this->orders);
				$this->lists  			= $this->get( 'Lists');
				$this->orders_summary 	= $this->get( 'OrderSummary');
				$this->items_summary  	= $this->get( 'ItemsSummary');
				$this->catid			= $mainframe->getUserStateFromRequest( 'filter.catid','catid',$catid,'int' );
			
				// Check for errors.
				if (count($errors = $this->get('Errors'))) {
					JError::raiseError(500, implode("\n", $errors));
					return false;
				}
			} break;
		}
		$this->_prepareDocument();

		parent::display($tpl);

	}
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$pathway = $app->getPathway();
		$title = null;
	
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('MyMuse'));
		}
	
		$title = $this->params->get('page_title', '');
	
		$id = (int) @$menu->query['id'];
	
	
		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->title;
		}
		$this->document->setTitle($title);
	
	}
}
 