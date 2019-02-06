<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com	
 * 
 * based on the file categorytree.php found in flexicontent. It seemed to be based on a 
 * joomla core file called category parent. At least, the head of that file had these comments:
 * version		Id: categoryparent.php 18808 2010-09-08 05:44:54Z eddieajau 
 * copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * license		GNU General Public License version 2 or later; see LICENSE.txt
 * 
 */
 

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_mymuse
 * @since		1.6
 */
class JFormFieldCategoryTree extends JFormFieldList{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'CategoryParent';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		// Initialize variables.
		$html = array();
		$attr = '';
		$selectedCats = array();
		$jinput = JFactory::getApplication()->input;
		//product id
		$id = $jinput->get('id',0);
		
		if($id){
			$db = JFactory::getDBO();
			$query = 'SELECT catid' .
			' FROM #__mymuse_product_category_xref' .
			' WHERE product_id='. $id ;
			$db->setQuery($query);
			$cats = $db->loadObjectList();

			foreach($cats as $cat){
				$this->value[$cat->catid] = $cat->catid;
			}
		}
		
		//a catid coming in
		$catid = JRequest::getVar('catid', 0);
		if($catid){
			$this->value[$catid] = $catid;
		}

		if(!is_array($this->value)) $this->value = array($this->value);
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {			
			$html[] = '<select name="" '.trim($attr).'>';
			foreach($options as $opt) {
				$disabled = '';
				$selected = '';
				if( @$opt->disable )
					$disabled = ' disabled="disabled"';
				if(in_array($opt->value, $this->value))
					$selected = ' selected="selected"';
				$html[] = '<option value="'.$opt->value.'"'.$disabled.$selected.'>'.$opt->text.'</option>';
			}
			$html[] = '</select>';
			foreach($this->value as $v)
				$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$value.'"/>';
		}
		// Create a regular list.
		else {
			
			//$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<select name="'.$this->name.'" '.trim($attr).'>';
			foreach($options as $opt) {
				$disabled = '';
				$selected = '';
				if( @$opt->disable )
					$disabled = ' disabled="disabled"';
				if(in_array($opt->value, $this->value))
					$selected = ' selected="selected"';
				$html[] = '<option value="'.$opt->value.'"'.$disabled.$selected.'>'.$opt->text.'</option>';
			}
			$html[] = '</select>';
		}

		return implode("\n", $html);
	}
	
	/**
	 * Method to get a list of categories that respects access controls and can be used for
	 * either category assignment or parent category assignment in edit screens.
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$published = $this->element['published'] ? $this->element['published'] : array(0, 1);
		$name = (string) $this->element['name'];

		// Let's get the id for the current item, either category or content item.
		$jinput = JFactory::getApplication()->input;
		// Load the category options for a given extension.

		// For categories the old category is the category id or 0 for new category.
		if ($this->element['parent'] || $jinput->get('option') == 'com_categories')
		{
			$oldCat = $jinput->get('id', 0);
			$oldParent = $this->form->getValue($name, 0);
			$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('extension', 'com_content');
		}
		else
			// For items the old category is the category they are in when opened or 0 if new.
		{
			$thisItem = $jinput->get('id', 0);
			$oldCat = $this->form->getValue($name, 0);
			$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('option', 'com_content');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value, a.title AS text, a.level, a.published')
			->from('#__categories AS a')
			->join('LEFT', $db->quoteName('#__categories') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter by the extension type
		if ($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
		{
			$query->where('(a.extension = ' . $db->quote($extension) . ' OR a.parent_id = 0)');
		}
		else
		{
			$query->where('(a.extension = ' . $db->quote($extension) . ')');
		}
		// If parent isn't explicitly stated but we are in com_categories assume we want parents
		if ($oldCat != 0 && ($this->element['parent'] == true || $jinput->get('option') == 'com_categories'))
		{
			// Prevent parenting to children of this item.
			// To rearrange parents and children move the children up, not the parents down.
			$query->join('LEFT', $db->quoteName('#__categories') . ' AS p ON p.id = ' . (int) $oldCat)
				->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');

			$rowQuery = $db->getQuery(true);
			$rowQuery->select('a.id AS value, a.title AS text, a.level, a.parent_id')
				->from('#__categories AS a')
				->where('a.id = ' . (int) $oldCat);
			$db->setQuery($rowQuery);
			$row = $db->loadObject();
		}

		// Filter language
		if (!empty($this->element['language']))
		{

			$query->where('a.language = ' . $db->quote($this->element['language']));
		}

		// Filter on the published state

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$query->where('a.published IN (' . implode(',', $published) . ')');
		}

		$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.extension, a.parent_id, a.published')
			->order('a.lft ASC, a.title ASC');	//a.lft ASC

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage);
		}

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			// Translate ROOT
			if ($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
			{
				if ($options[$i]->level == 0)
				{
					$options[$i]->text = JText::_('JGLOBAL_ROOT_PARENT');
				}
			}
			if ($options[$i]->published == 1)
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
			}
			else
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . '[' . $options[$i]->text . ']';
			}
		}

		// Get the current user object.
		$user = JFactory::getUser();

		// For new items we want a list of categories you are allowed to create in.
		if ($oldCat == 0)
		{
			foreach ($options as $i => $option)
			{
				// To take save or create in a category you need to have create rights for that category
				// unless the item is already in that category.
				// Unset the option if the user isn't authorised for it. In this field assets are always categories.
				if ($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
				{
					unset($options[$i]);
				}
			}
		}
		// If you have an existing category id things are more complex.
		else
		{
			// If you are only allowed to edit in this category but not edit.state, you should not get any
			// option to change the category parent for a category or the category for a content item,
			// but you should be able to save in that category.
			foreach ($options as $i => $option)
			{
				if ($user->authorise('core.edit.state', $extension . '.category.' . $oldCat) != true && !isset($oldParent))
				{
					if ($option->value != $oldCat)
					{
						unset($options[$i]);
					}
				}
				if ($user->authorise('core.edit.state', $extension . '.category.' . $oldCat) != true
					&& (isset($oldParent))
					&& $option->value != $oldParent
				)
				{
					unset($options[$i]);
				}

				// However, if you can edit.state you can also move this to another category for which you have
				// create permission and you should also still be able to save in the current category.
				if (($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
					&& ($option->value != $oldCat && !isset($oldParent))
				)
				{
					{
						unset($options[$i]);
					}
				}
				if (($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
					&& (isset($oldParent))
					&& $option->value != $oldParent
				)
				{
					{
						unset($options[$i]);
					}
				}
			}
		}
		if (($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
			&& (isset($row) && !isset($options[0]))
			&& isset($this->element['show_root'])
		)
		{
			if ($row->parent_id == '1')
			{
				$parent = new stdClass;
				$parent->text = JText::_('JGLOBAL_ROOT_PARENT');
				array_unshift($options, $parent);
			}
			array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	
	function getCategoriesTree()
	{
		global $mymusecats;
		$db		= JFactory::getDBO();
		$query 	= "SELECT lft,rgt FROM #__categories WHERE id=1 ";
		$db->setQuery($query);
		$obj 	= $db->loadObject();
		// get the category tree and append the ancestors to each node		
		$query	= 'SELECT id, parent_id, published, access, title, level, lft, rgt,'
				. ' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug'
				. ' FROM #__categories as c'
				. ' WHERE c.extension="com_mymuse" AND lft > ' . $obj->lft . ' AND rgt < ' . $obj->rgt
				. ' ORDER BY parent_id, lft'
				;
		$db->setQuery($query);
		$cats = $db->loadObjectList();

		//establish the hierarchy of the categories
		$children = array();
		$parents = array();
		
		//set depth limit
   		$levellimit = 10;
		
		foreach ($cats as $child) {
			$parent = $child->parent_id;
			if ($parent) $parents[] = $parent;
			$list 	= @$children[$parent] ? $children[$parent] : array();
			array_push($list, $child);
			$children[$parent] = $list;
		}
		
		$parents = array_unique($parents);

		//get list of the items
		$mymusecats = JFormFieldCategoryTree::_getCatAncestors($ROOT_CATEGORY_ID=1, '', array(), $children, true, max(0, $levellimit-1));

		foreach ($mymusecats as $cat) {
			$cat->ancestorsonlyarray	= $cat->ancestors;
			$cat->ancestorsonly			= implode(',', $cat->ancestors);
			$cat->ancestors[] 			= $cat->id;
			$cat->ancestorsarray		= $cat->ancestors;
			$cat->ancestors				= implode(',', $cat->ancestors);
			$cat->descendantsarray		= JFormFieldCategoryTree::_getDescendants(array($cat));
			$cat->descendants			= implode(',', $cat->descendantsarray);
		}
		return $mymusecats;
	}
	
	
	/**
    * Get the ancestors of each category node
    *
    * @access private
    * @return array
    */
	function _getCatAncestors( $id, $indent, $list, &$children, $title, $maxlevel=9999, $level=0, $type=1, $ancestors=null )
	{
		if (!$ancestors) $ancestors = array();
		
		if (@$children[$id] && $level <= $maxlevel) {
			foreach ($children[$id] as $v) {
				$id = $v->id;
				
				if ((!in_array($v->parent_id, $ancestors)) && $v->parent_id != ($ROOT_CATEGORY_ID=1)) {
					$ancestors[] 	= $v->parent_id;
				} 
				
				if ( $type ) {
					$pre    = '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;';
				} else {
					$pre    = '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($title) {
					if ( $v->parent_id == 0 ) {
						$txt    = ''.$v->title;
					} else {
						$txt    = $pre.$v->title;
					}
				} else {
					if ( $v->parent_id == 0 ) {
						$txt    = '';
					} else {
						$txt    = $pre;
					}
				}

				$pt = $v->parent_id;
				$list[$id] = $v;
				$list[$id]->treename 		= "$indent$txt";
				$list[$id]->title 			= $v->title;
				$list[$id]->slug 			= $v->slug;
				$list[$id]->ancestors 		= $ancestors;
				$list[$id]->childrenarray 	= @$children[$id];

				$list[$id]->children 		= count( @$children[$id] );

				$list = JFormFieldCategoryTree::_getCatAncestors( $id, $indent.$spacer, $list, $children, $title, $maxlevel, $level+1, $type, $ancestors );
			}
		}
		return $list;
	}
	
	/**
    * Get the descendants of each category node
    *
    * @access private
    * @return array
    */
	function _getDescendants($arr, &$descendants = array())
	{
		foreach($arr as $k => $v)
		{
			$descendants[] = $v->id;
		
			if ($v->childrenarray) {
				JFormFieldCategoryTree::_getDescendants($v->childrenarray, $descendants);
			}
		}
		return $descendants;
	}
}
