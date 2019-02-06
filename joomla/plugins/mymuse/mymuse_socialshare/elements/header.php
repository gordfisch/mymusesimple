<?php
/*-----------------------------------------------------------------------
# plg_mymuse_socialshare - Social Share for MyMuse component
# -----------------------------------------------------------------------
# author: http://www.arboreta.ca
# copyright Copyright (C) 2015 arboreta. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.arboreta.ca
# Technical Support:  Forum - http://www.joomlamymuse.com/support
#-------------------------------------------------------------------------*/

/*------------------------------------------------------------------------
# based on: plg_jo_k2_socialshare - JO K2 Social Share for k2 component
# -----------------------------------------------------------------------
# author: http://www.joomcore.com
# copyright Copyright (C) 2011 Joomcore.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomcore.com
# Technical Support:  Forum - http://www.joomcore.com/Support
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
class JFormFieldHeader extends JFormField {
	var	$type = 'header';
	function getInput(){
		$html = '<div class="paramHeaderContainer" style="clear: both; border-bottom: 2px solid #96B0CB; color: #336699; font-size: 12px; font-weight: bold; margin: 12px 0 4px;"><div class="paramHeaderContent">'.JText::_($this->value).'</div><div class="k2clr"></div></div>';
		return $html;
	}
}
