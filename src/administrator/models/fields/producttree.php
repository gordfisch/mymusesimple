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
class JFormFieldProducttree extends JFormFieldSQL
{
	/**
	 * The form field type.
	 */
	public $type = 'producttree';

	/**
	 * Overrides parent's getinput method
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$jinput = JFactory::getApplication()->input;
		$finals = array();

		$html[] = '<select id="recommended" name="jform[recommended][]" class="inputbox" size="8" multiple="true">';
		$id = $jinput->get('id','0');
		// do the SQL
		$db = JFactory::getDbo();
		$query="SELECT 0 AS id, 'None selected' AS name 
		UNION ALL SELECT id, title as name FROM #__mymuse_product WHERE parentid='0'
				AND id NOT IN ($id) ORDER BY NAME asc";
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		foreach( $rows as $row ){
			$finals[] = $row;
			$query="SELECT id, title as name FROM #__mymuse_product WHERE parentid='".$row->id."'
			AND id NOT IN ($id) ORDER BY NAME asc";
			$db->setQuery($query);
			$tracks = $db->loadObjectList();
			foreach($tracks as $track){
				$track->name = " - ".$track->name;
				$finals[] = $track;
			}
		}
		//find already selected
		$selecteds = array();
		$query = "SELECT * FROM #__mymuse_product_recommend_xref WHERE product_id = '$id'";
		$db->setQuery($query);
		
		if($res = $db->loadObjectList()){
			foreach($res as $r){
				$selecteds[] = $r->recommend_id;
			}
		}
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