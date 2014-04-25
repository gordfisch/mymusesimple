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
 * Productattributes list controller class.
 */
class MymuseControllerProductattributeskus extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'productattributeskus', $prefix = 'MymuseModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'MYMUSE_SELECT_AN_ATTRIBUTE_TO_DELETE' ) );
		}
	
	
		$model = $this->getModel();
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>
			";
		}
		$this->msg = JText::_( 'MYMUSE_ATTRIBUTE_DELETED' );
		$this->setRedirect( 'index.php?option=com_mymuse&task=list&view=productattributeskus', $this->msg );
	}
}