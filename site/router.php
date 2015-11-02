<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the com_mymuse component
 *
 * @param	array	An array of URL arguments
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 * @since	1.5
 */
function MymuseBuildRoute(&$query)
{
	$segments	= array();

	// get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	$params		= JComponentHelper::getParams('com_mymuse');
	$advanced	= $params->get('sef_advanced_link', 0);

	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	}
	else {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
	}

	if (isset($query['view'])) {
		$view = $query['view'];
	}
	else {
		// we need to have a view in the query or it is an invalid URL
		return $segments;
	}

	// are we dealing with an product or category that is attached to a menu item?
	if (($menuItem instanceof stdClass) && $menuItem->query['view'] == $query['view'] && isset($query['id']) 
			&& isset($menuItem->query['id']) && $menuItem->query['id'] == intval($query['id'])) {
		unset($query['view']);

		if (isset($query['catid'])) {
			unset($query['catid']);
		}
		
		if (isset($query['layout'])) {
			unset($query['layout']);
		}

		unset($query['id']);

		return $segments;
	}

	if ($view == 'category' || $view == 'product')
	{
		if (!$menuItemGiven) {
			$segments[] = $view;
		}

		unset($query['view']);

		if ($view == 'product') {
			if (isset($query['id']) && isset($query['catid']) && $query['catid']) {
				$catid = $query['catid'];
				// Make sure we have the id and the alias
				if (strpos($query['id'], ':') === false) {
					$db = JFactory::getDbo();
					$aquery = $db->setQuery($db->getQuery(true)
						->select('alias')
						->from('#__mymuse_product')
						->where('id='.(int)$query['id'])
					);
					$alias = $db->loadResult();
					$query['id'] = $query['id'].':'.$alias;
				}
			} else {
				// we should have these two set for this view.  If we don't, it is an error
				return $segments;
			}
		}
		else {
			if (isset($query['id'])) {
				$catid = $query['id'];
			} else {
				// we should have id set for this view.  If we don't, it is an error
				return $segments;
			}
		}

		if ($menuItemGiven && isset($menuItem->query['id'])) {
			$mCatid = $menuItem->query['id'];
		} else {
			$mCatid = 0;
		}

		$categories = JCategories::getInstance('Mymuse');
		$category = $categories->get($catid);

		if (!$category) {
			// we couldn't find the category we were given.  Bail.
			return $segments;
		}

		$path = array_reverse($category->getPath());

		$array = array();

		foreach($path as $id) {
			if ((int)$id == (int)$mCatid) {
				break;
			}

			list($tmp, $id) = explode(':', $id, 2);

			$array[] = $id;
		}

		$array = array_reverse($array);

		if (!$advanced && count($array)) {
			$array[0] = (int)$catid.':'.$array[0];
		}

		$segments = array_merge($segments, $array);

		if ($view == 'product') {
			if ($advanced) {
				list($tmp, $id) = explode(':', $query['id'], 2);
			}
			else {
				$id = $query['id'];
			}
			$segments[] = $id;
		}
		unset($query['id']);
		unset($query['catid']);
	}



	return $segments;
}



/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 * @since	1.5
 */
function MymuseParseRoute($segments)
{
	
	$vars = array();
	if(!defined('DS')){
		define('DS',DIRECTORY_SEPARATOR);
	}
	
	if(!defined('MYMUSE_ADMIN_PATH')){
		define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
	}
	require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
	//Get the active menu item.
	$app	= JFactory::getApplication();
	$jinput = $app->input;
	$jinput->set('option', 'com_mymuse');
	$menu	= $app->getMenu();
	$item	= $menu->getActive();
	$params = MyMuseHelper::getParams();
	$advanced = $params->get('sef_advanced_link', 0);
	$db = JFactory::getDBO();

	// Count route segments
	$count = count($segments);

	// Standard routing for products.  If we don't pick up an Itemid then we get the view from the segments
	// the first segment is the view and the last segment is the id of the product or category.
	if (!isset($item)) {
		$vars['view']	= $segments[0];
		$vars['id']		= $segments[$count - 1];

		return $vars;
	}

	// if there is only one segment, then it points to either a product or a category
	// we test it first to see if it is a category.  If the id and alias match a category
	// then we assume it is a category.  If they don't we assume it is an product
	if ($count == 1) {
		
		if($params->get('my_use_alias')){
			//check if this is a product alias.
			if(strpos($segments[0],':')){
				$segments[0] = preg_replace('/:/',"-",$segments[0]);
			}
			$query = 'SELECT id,catid from #__mymuse_product WHERE alias="'.$segments[0].'"';

			$db->setQuery($query);
			if($product = $db->loadObject()){
				$vars['option'] = 'com_mymuse';
				$vars['view'] = 'product';
				$vars['id'] = (int)$product->id;
				$vars['catid'] = (int)$product->catid;
				return $vars;
			}
			
			//check if this is a category alias.
			$query = 'SELECT id from #__categories WHERE alias="'.$segments[0].'"';
			
			$db->setQuery($query);
			if($category = $db->loadObject()){
				$vars['option'] = 'com_mymuse';
				$vars['view'] = 'category';
				$vars['id'] = (int)$category->id;
				return $vars;
			}
		}
				
		// we check to see if an alias is given.  If not, we assume it is an product
		if (strpos($segments[0], ':') === false) {
			$vars['view'] = 'product';
			$vars['id'] = (int)$segments[0];
			return $vars;
		}

		list($id, $alias) = explode(':', $segments[0], 2);

		// first we check if it is a category
		$category = JCategories::getInstance('Mymuse')->get($id);

		if ($category && $category->alias == $alias) {
			$vars['view'] = 'category';
			$vars['id'] = $id;

			return $vars;
		} else {
			$query = 'SELECT alias, catid FROM #__mymuse_product WHERE id = '.(int)$id;
			$db->setQuery($query);
			$product = $db->loadObject();

			if ($product) {
				if ($product->alias == $alias) {
					$vars['view'] = 'product';
					$vars['catid'] = (int)$product->catid;
					$vars['id'] = (int)$id;

					return $vars;
				}
			}
		}
	}

	// if there was more than one segment, then we can determine where the URL points to
	// because the first segment will have the target category id prepended to it.  If the
	// last segment has a number prepended, it is a product, otherwise, it is a category.
	if (!$advanced) {
		$cat_id = (int)$segments[0];

		$product_id = (int)$segments[$count - 1];

		if ($product_id > 0) {
			$vars['view'] = 'product';
			$vars['catid'] = $cat_id;
			$vars['id'] = $product_id;
		} else {
			$vars['view'] = 'category';
			$vars['id'] = $cat_id;
		}

		return $vars;
	}

	// we get the category id from the menu item and search from there
	$id = $item->query['id'];
	$category = JCategories::getInstance('MyMuse')->get($id);

	if (!$category) {
		JError::raiseError(404, JText::_('COM_CONTENT_ERROR_PARENT_CATEGORY_NOT_FOUND'));
		return $vars;
	}

	$categories = $category->getChildren();
	$vars['catid'] = $id;
	$vars['id'] = $id;
	$found = 0;

	foreach($segments as $segment)
	{
		$segment = str_replace(':', '-', $segment);

		foreach($categories as $category)
		{
			if ($category->alias == $segment) {
				$vars['id'] = $category->id;
				$vars['catid'] = $category->id;
				$vars['view'] = 'category';
				$categories = $category->getChildren();
				$found = 1;
				break;
			}
		}

		if ($found == 0) {
			if ($advanced) {
				$db = JFactory::getDBO();
				$query = 'SELECT id FROM #__mymuse_product WHERE catid = '.$vars['catid'].' AND alias = '.$db->Quote($segment);
				$db->setQuery($query);
				$cid = $db->loadResult();
			} else {
				$cid = $segment;
			}

			$vars['id'] = $cid;

			if ($item->query['view'] == 'archive' && $count != 1){
				$vars['year']	= $count >= 2 ? $segments[$count-2] : null;
				$vars['month'] = $segments[$count-1];
				$vars['view']	= 'archive';
			}
			else {
				$vars['view'] = 'product';
			}
		}

		$found = 0;
	}

	return $vars;
}