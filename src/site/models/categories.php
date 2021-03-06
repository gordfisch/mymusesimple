<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
require_once( MYMUSE_PATH.DS.'helpers'.DS.'category.php' );

/**
 * This models supports retrieving lists of product categories.
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.6
 */
class mymuseModelCategories extends JModelList
{
	
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_joomlamymuse.comtegories';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_mymuse';

	private $_parent = null;

	private $_items = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = NULL, $direction = NULL)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$this->setState('filter.extension', $this->_extension);

		// Get the parent id if defined.
		$parentId = $jinput->get('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.extension');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.parentId');

		return parent::getStoreId($id);
	}

	/**
	 * Get the child categories
	 *
	 * @param	bool	$recursive	True if you want to return children recursively.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 * @since	1.6
	 */
	public function getItems($recursive = true)
	{
		if (!count($this->_items)) {
			$app = JFactory::getApplication();

			/////
			$app_params = $app->getParams();
			$params 	= MyMuseHelper::getParams();
			$params->merge($app_params);
			
			$menuParams = new JRegistry;
			if ($menu = $app->getMenu()->getActive()) {
				$menuParams->loadString($menu->params);
			}
		
			$mergedParams = clone $menuParams;
			$mergedParams->merge($params);
			$params = $mergedParams;

			$this->setState('params', $mergedParams);

			$options = array();
			$options['countItems'] = $params->get('show_cat_num_articles_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			
			$categories = JCategories::getInstance('Mymuse', $options);

			$this->_parent = $categories->get($this->getState('filter.parentId', 'root'));

			if (is_object($this->_parent)) {
				
				$this->_items = $this->_parent->getChildren(false);
			}
			else {
				$this->_items = false;
			}
				
			
		}

		return $this->_items;
	}

	public function getParent()
	{
		if (!is_object($this->_parent)) {
			$this->getItems();
		}

		return $this->_parent;
	}
}
