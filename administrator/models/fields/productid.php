<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
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
class JFormFieldProductid extends JFormFieldSQL
{
	/**
	 * The form field type.
	 */
	public $type = 'productid';

	/**
	 * Overrides parent's getinput method
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$jinput = JFactory::getApplication()->input;
		$finals = array();

		$html[] = '<select id="product_id" name="product_id"  size="1"  onchange="this.form.submit();">';
		$product_id = $jinput->get('product_id',array());
		// do the SQL
		$db = JFactory::getDbo();
		$title = JText::_('MYMUSE_SELECT_PRODUCT');
		$query="SELECT 0 AS id, '$title' AS name 
		UNION ALL SELECT id, title as name FROM #__mymuse_product WHERE 1 ORDER BY NAME asc";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach( $rows as $row ){
			$finals[] = $row;
		}

		//find already selected
		$selecteds[] = $product_id;
		// iterate through returned rows
		foreach( $finals as $row ){
			$selected = '';
			if(in_array($row->id, $selecteds)){
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}
			$html[] = '<option value="'.$row->id.'" '.$selected.'>'.$row->name.'</option>';
		}

		// close the HTML select options
		$html[] = '</select>';

		// return the HTML
		return implode($html);
	}
}