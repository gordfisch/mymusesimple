<?php
/**
 * @version     $Id:$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 * 
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('sql');

/**
 * Supports an HTML select list of options driven by SQL
 */
class JFormFieldOrderstatus extends JFormFieldSQL
{
	/**
	 * The form field type.
	 */
	public $type = 'orderstatus';

	/**
	 * Overrides parent's getinput method
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$html[] = '<select id="filter_order_status" name="filter_order_status" class="inputbox">';
		$filter_order_status = JRequest::getVar('filter_order_status',0);
		// do the SQL
		$db = JFactory::getDbo();
		$query="SELECT 0 AS id, 'None selected' AS name 
		UNION ALL SELECT code as id, name FROM #__mymuse_order_status";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// iterate through returned rows
		foreach( $rows as $row ){
			$selected = '';
			if($row->id == $filter_order_status){
				$selected = ' selected="selected"';
			}
			$html[] = '<option value="'.$row->id.'" '.$selected.'>'.$row->name.'</option>';
		}

		// close the HTML select options
		$html[] = '</select>';

		// return the HTML
		return implode($html);
	}
}