<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Mymuse records.
 */
class MymuseModelorders extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',

            );
        }

        parent::__construct($config);
    }


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$order_status = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status', '', 'string');
		$this->setState('filter.order_status', $order_status);
	
		$start_date = $app->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', '', 'string');
		$this->setState('filter.start_date', $start_date);
		
		$end_date = $app->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', '', 'string');
		$this->setState('filter.end_date', $end_date);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mymuse');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.order_status');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__mymuse_order` AS a');


		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the users for the order owner.
		$query->select('u.name AS shopper');
		$query->join('LEFT', '#__users AS u ON u.id=a.user_id');

		// Join over the order_status for the status name.
		$query->select('os.name AS status_name');
		$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=a.order_status');


		// Filter by order_status
		$order_status = $this->getState('filter.order_status');
		if (is_string($order_status) && $order_status != '') {
			$query->where('a.order_status = "'.$order_status.'"');
		} else if ($order_status === '') {
			//$query->where('(a.order_status IN (SELECT code from #__mymuse_order_status))');
		}
                    
		//filter by date
		$start_date = $this->getState('filter.start_date');
		$end_date = $this->getState('filter.end_date');
		$datenow =& JFactory::getDate();
		$now = $datenow->toFormat("%Y-%m-%d");
		
		if($start_date== $now && $end_date == $now ){
			$start_date = '';
			$end_date = '';
		}
		
		$where = array();
		if($start_date){
			$query->where("a.created >= '$start_date 00:00:00'");
		}
		if($end_date){
			$query->where("a.created <= '$end_date 00:00:00'");
		}

		
		
		
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
                $query->where("u.name LIKE $search");
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
		    $query->order($db->getEscaped($orderCol.' '.$orderDirn));
        }

		return $query;
	}
}
