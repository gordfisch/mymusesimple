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

jimport('joomla.application.component.controlleradmin');

/**
 * Taxrates list controller class.
 */
class MymuseControllerTaxrates extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'taxrate', $prefix = 'MymuseModel', $config = Array())
	{
		$config = array('ignore_request' => true);
		$model = parent::getModel($name, $prefix, $config );
		return $model;
	}
	

}