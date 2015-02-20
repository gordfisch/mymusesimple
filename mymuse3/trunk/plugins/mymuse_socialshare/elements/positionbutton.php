<?php
/*-----------------------------------------------------------------------
# plg_mymuse_socialshare - Social Share for MyMuse component
# -----------------------------------------------------------------------
# author: http://www.arboreta.ca
# copyright Copyright (C) 2015 arboreta. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.arboreta.ca
# Technical Support:  Forum - http://www.mymuse.ca/support
#-------------------------------------------------------------------------*/

/*------------------------------------------------------------------------
# Based on: plg_jo_k2_socialshare - JO K2 Social Share for k2 component
# -----------------------------------------------------------------------
# author: http://www.joomcore.com
# copyright Copyright (C) 2011 Joomcore.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomcore.com
# Technical Support:  Forum - http://www.joomcore.com/Support
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');
class JFormFieldpositionbutton extends JFormField
{
	protected $type = 'positionbutton';

	protected function getInput()
	{
		$ordering = array();
		$ordering[] = array('value' => '1', 'text' => JText::_('1'));
		$ordering[] = array('value' => '2', 'text' => JText::_('2'));
		$ordering[] = array('value' => '3', 'text' => JText::_('3'));
		$ordering[] = array('value' => '4', 'text' => JText::_('4'));
		$ordering[] = array('value' => '5', 'text' => JText::_('5'));
		$ordering[] = array('value' => '6', 'text' => JText::_('6'));
		$ordering[] = array('value' => '7', 'text' => JText::_('7'));
		$ordering[] = array('value' => '8', 'text' => JText::_('8'));
		$ordering[] = array('value' => '9', 'text' => JText::_('9'));
		$ordering[] = array('value' => '10', 'text' => JText::_('10'));
		$html = JHTML::_('select.genericlist',  $ordering, $this->name, 'class="inputbox"', 'value', 'text', $this->value);
		
		return $html;
	}
}

