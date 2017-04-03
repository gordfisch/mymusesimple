<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	COM_MYMUSE
 */
abstract class JHtmlMymuseAdministrator
{
	/**
	 * Show the feature/unfeature links
	 *
	 * @param   int      $value      The state value
	 * @param   int      $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	
	public static function featured($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');
	
		// Array of image, task, title, action //COM_MYMUSE_TOGGLE_TO_FEATURE  COM_MYMUSE_TOGGLE_TO_UNFEATURE
		$states = array(
				0 => array('unfeatured', 'products.featured', 'COM_MYMUSE_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
				1 => array('featured', 'products.unfeatured', 'COM_MYMUSE_FEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];
	
		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
					. ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><span class="icon-' . $icon . '"></span></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
					. JHtml::tooltipText($state[2]) . '"><span class="icon-' . $icon . '"></span></a>';
		}
	
		return $html;
	}
}
