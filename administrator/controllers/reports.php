<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

class myMuseControllerReports extends JControllerLegacy
{
	/**
	 * Custom Constructor
	 */

	function __construct()
	{
		parent::__construct();
		$input = JFactory::getApplication()->input;
		$input->set('view','reports');
	}
	
	/**
	 * display the report
	 * @return void
	 */
	function report()
	{
		JRequest::setVar( 'layout', 'report');
		//JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}
	
	/**
	 * display the dowmloads report
	 * @return void
	 */
	function downloads()
	{
		parent::display();
	}
	
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Reports', $prefix = 'MymuseModel', $config=array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	
	/**
	 * download the order table as csv
	 * 
	 */
	public function downloadOrder()
	{
		$this->downloadCSV('mymuse_order');
	}
	
	/**
	 * download the item table as csv
	 *
	 */
	public function downloadItem()
	{
		$this->downloadCSV('mymuse_order_item');
	}
	
	/**
	 * download the sownload table as csv
	 *
	 */
	public function downloadDownload()
	{
		$this->downloadCSV('mymuse_downloads');
	}
	
	
	public function downloadCSV($report='report')
	{
		$date = $date = JFactory::getDate()->format('Y-m-d');
		$filename = $report.'_'.$date.'.csv';
		
		header ( "Content-type: text/csv" );
		header ( "Content-Disposition: attachment; filename=$filename" );
		header ( "Pragma: no-cache" );
		header ( "Expires: 0" );
		
		$this->getModel()->getCsv ($report);
		jexit (); 
	}

	



}
?>
