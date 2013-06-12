<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca	
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
		$id = JRequest::getVar('id',0);
		if($id){
			$db = JFactory::getDBO();
			$query = 'SELECT catid' .
			' FROM #__mymuse_product_category_xref' .
			' WHERE product_id='. $id ;;
			$db->setQuery($query);
			$cats = $db->loadObjectList();

			foreach($cats as $cat){
				$this->value[$cat->catid] = $cat->catid;
			}
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
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions() {
		global $mymusecats;
		$mymusecats = $this->getCategoriesTree();

		$user = JFactory::getUser();
		$cid = JRequest::getVar('cid');
		
		$permission = MymuseHelperPerm::getPerm();

		//$usercats 		= FAccess::checkUserCats($user->gmid);
		$usercats		= array();
		//$viewallcats 	= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'usercats', 'users', $user->gmid) : 1;
		$viewallcats 	= $permission->CanUserCats;
		//$viewtree 		= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'cattree', 'users', $user->gmid) : 1;
		$viewtree 		= $permission->CanViewTree;
		

		$catlist 	= array();
		$top = (int)$this->element->getAttribute('top');
		$published = (bool)$this->element->getAttribute('published');
		$filter = (bool)$this->element->getAttribute('filter');
		if($top == 1) {
			$obj = new stdClass;
			$obj->value = $ROOT_CATEGORY_ID = 1;
			$obj->level = 0;
			$obj->text = JText::_( 'MYMUSE_TOPLEVEL' );
			$catlist[] 	= $obj;
		} else if($top == 2) {
			$obj = new stdClass;
			$obj->value = '';
			$obj->level = 0;
			$obj->text = JText::_( 'MYMUSE_SELECT_CAT' );
			$catlist[] 	= $obj;
		}
		
		foreach ($mymusecats as $item) {
			if ((!$published) || ($published && $item->published)) {
				//if ((JRequest::getVar('controller') == 'categories') && (JRequest::getVar('task') == 'edit') && ($cid[0] == $item->id)) {
				if ((JRequest::getVar('controller') == 'categories') && (JRequest::getVar('task') == 'edit') && ($item->lft >= @$mymusecats[$cid[0]]->lft && $item->rgt <= @$mymusecats[$cid[0]]->rgt)) {
					if($top == 2) {
						if($cid[0] != $item->id) {
							$obj = new stdClass;
							$obj->value = $item->id;
							$obj->text = $item->treename;
							$obj->level = $item->level;
							$catlist[] = $obj;
						}else {
							$catlist[] = JHtml::_('select.option', $item->id, $item->treename, 'value', 'text', true);
						}
					}
				} else if ($filter) {
					if ( !in_array($item->id, $usercats) ) {
						if ($viewallcats) { // only disable cats in the list else don't show them at all
							$catlist[] = JHTML::_( 'select.option', $item->id, $item->treename, 'value', 'text', true );
						}
					} else {
						$item->treename = str_replace("&nbsp;", "_", strip_tags($item->treename));
						// FLEXIaccess rule $viewtree enables tree view
						$catlist[] = JHTML::_( 'select.option', $item->id, ($viewtree ? $item->treename : $item->title) );
					}
				} else {
					$obj = new stdClass;
					$obj->value = $item->id;
					$obj->text = $item->treename;
					$obj->level = $item->level;
					$catlist[] = $obj;
				}
			}
		}

		// Merge any additional options in the XML definition.
		$catlist = array_merge(parent::getOptions(), $catlist);
		return $catlist;
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
