<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca	
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
	
	protected $topcat = null;

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		// Initialize variables.
		$html 			= array();
		$attr 			= '';
		$selectedCats 	= array();
	    $app 			= JFactory::getApplication();
	    $jinput 		= $app->input;
		$user   		= JFactory::getUser();
        $profile 		= $user->get('profile');
		$catid 			= @$profile['category_owner'];
		$this->topcat 	= $catid;

		$subid	= $app->getUserStateFromRequest( $this->context.'catid','catid','','int' );

		//what if they want a sub-cat of the parent
		$subid = $jinput->get('catid',0);
		if($subid && $subid != $catid){
			$this->_catid = $subid;
		}
		if($this->_catid){
			$this->value[$this->_catid] = $this->_catid;
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
		$options = (array) $this->getCats($this->topcat, true);

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
	
	public function getCats($catid, $recursive = false)
	{
			
		if (!count($this->_cats)) {
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry();
			 
			if ($active) {
				$params->loadString($active->params);
			}
			 
			$options = array();
			$categories = JCategories::getInstance('MyMuse', $options);
			 
			$this->_parent = $categories->get($catid, $recursive);
			 
			if (is_object($this->_parent)) {
				
				$this->_cats = $this->_parent->getChildren($recursive);
			}
			else {
				$this->_cats = false;
			}
			 
		}
		array_unshift($this->_cats,$this->_parent );
		foreach($this->_cats as $cat){
			$cat->value = $cat->id;
			$cat->text = $cat->title;
		}
		 
		return $this->_cats;
	}
	

}
