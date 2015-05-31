<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Mymuse model.
 */
class MymuseModelcoupon extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_MYMUSE';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Coupon', $prefix = 'MymuseTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_mymuse.coupon', 'coupon', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mymuse.edit.coupon.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {

			//Do any procesing on fields here if needed

		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mymuse_coupon');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
	
	
	   /**
     * Method to set the coupon lists
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {

		$item	= $this->getItem();
    	$currencies[] = JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_SELECT_CURRENCY').' -');
    	$query = "SELECT id as value, CONCAT(symbol,' ',currency_name) as text from #__mymuse_currency ORDER BY currency_code ASC";
    	$this->_db->setQuery($query);
    	$currencies = array_merge($currencies, $this->_db->loadObjectList());
    	$lists['currency'] = JHTML::_('select.genericlist',  $currencies, 'jform[currency_id]', 'class="inputbox" size="1" ', 'value', 'text', $item->currency_id);
    		
    	$products[] = JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_SELECT_PRODUCT').' -');
    	$query = "SELECT id as value, CONCAT(title,': ',product_sku) as text from #__mymuse_product
			WHERE parentid='0' ORDER BY title ASC";
    	$this->_db->setQuery($query);
    	$parents = $this->_db->loadObjectList();
    	foreach($parents as $parent){
    		$products[] = $parent;
    		$query = "SELECT id as value, CONCAT('&nbsp;-&nbsp;',title,': ',product_sku) as text from #__mymuse_product
				WHERE parentid='".$parent->value."' ORDER BY title ASC";
    		$this->_db->setQuery($query);
    		$children = $this->_db->loadObjectList();
    		foreach($children as $child){
    			$products[] = $child;
    		}
    	}

    	$lists['products'] = JHTML::_('select.genericlist',  $products, 'jform[product_id]', 'class="inputbox" size="1" ', 'value', 'text', $item->product_id);


		return $lists;
    }
    
	

}