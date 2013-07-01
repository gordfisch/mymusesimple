<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Mymuse model.
 */
class MymuseModelUpgrade extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_MYMUSE';

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	2.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
	
		// Get the form.
		$form = $this->loadForm('com_mymuse.upgrade', 'upgrade', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
	
		return $form;
	}
	
	/**
	 * Method to check preflight.
	 *	
	 * @return	boolean	
	 * @since	205
	 */
	public function getPreFlight()
	{
		$return = true;
		$query = "show tables";
		$this->_db->setQuery($query);
		$columns = $this->_db->loadColumn();
		$arr = array();
		foreach($columns as $column){
			$arr[$column] = $column;
		}
		$mainframe =& JFactory::getApplication();
		$dbprefix= $mainframe->getUserStateFromRequest( "com_mymuse.dbprefix", 'dbprefix', 'jos' );
		
		if($dbprefix != 'jos'){
			$myFile = JPATH_COMPONENT.DS.'models'.DS.'forms'.DS.'upgrade.xml';
			$form = file_get_contents($myFile);
			$form = preg_replace("/jos/","$dbprefix", $form);
		
			if(!JFILE::write($myFile, $form)){
				$app = JFactory::getApplication();
				$app->enqueueMessage('could not update form with dbprefix', 'notice');
			}
		}
		
		
		if(!array_key_exists($dbprefix."_sections", $arr)){
			$this->setError($dbprefix."_sections does not exist", $arr);
			return false;
		}
		if(!array_key_exists($dbprefix."_mymuse_categories", $arr)){
			$this->setError($dbprefix."_mymuse_categories does not exist");
			return false;
		}
		if(!array_key_exists($dbprefix."_mymuse_product", $arr)){
			$this->setError($dbprefix."_mymuse_product does not exist");
			return false;
		}
		if(!array_key_exists($dbprefix."_mymuse_product_category_xref", $arr)){
			$this->setError($dbprefix."_mymuse_product_category_xref does not exist");
			return false;
		}
		if(!array_key_exists($dbprefix."_mymuse_product_price", $arr)){
			$this->setError($dbprefix."_mymuse_product_price does not exist");
			return false;
		}
		$table = $dbprefix."_mymuse_categories";
		$query = "ALTER TABLE `$table` ADD `newcatid` INT( 11 ) NOT NULL ";
		$this->_db->setQuery($query);
		$this->_db->execute();
		
		$table = $dbprefix."_mymuse_product";
		$query = "ALTER TABLE `$table` ADD `newprodid` INT( 11 ) NOT NULL ";
		$this->_db->setQuery($query);
		$this->_db->execute();
		
		//fix menus get new and old extension id
		$query = "SELECT id FROM #__extensions WHERE name='mymuse' AND type='component'";
		$this->_db->setQuery($query);
		$newid = $this->_db->loadResult();
		
		$query = "SELECT id FROM ".$dbprefix."_components WHERE name='mymuse' AND parent=0";
		$this->_db->setQuery($query);
		$oldid = $this->_db->loadResult();
		
		$query = "UPDATE #__menu SET component_id='".$newid."' WHERE menutype='mainmenu'
		AND `link` LIKE '%mymuse%'";
		$this->_db->setQuery($query);
		if(!$this->_db->execute()){
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		return $return;
	}


}