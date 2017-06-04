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
	if(!defined('DS')){
		define('DS',DIRECTORY_SEPARATOR);
	}
	
	if(!defined('MYMUSE_ADMIN_PATH')){
		define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
	}
	require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
	$params = MyMuseHelper::getParams();

	


	// get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	$advanced	= $params->get('sef_advanced_link', 0);
	$segments = array();
	$dbo = JFactory::getDbo();
	$q = 'SELECT id from #__menu WHERE home=1';
	$dbo->setQuery($q);
	$home_id = $dbo->loadResult();
	if($params->get('top_menu_item') == $home_id){
		$params->set('top_menu_item', '');
	}

	if($params->get('top_menu_item','') && $params->get('top_menu_item') != $home_id){
		if(!isset($query['Itemid'])){
			$query['Itemid']= $params->get('top_menu_item','');
		}
		/*elseif($query['Itemid'] == $params->get('top_menu_item','')){
			$q = 'SELECT alias from #__menu WHERE id="'.$params->get('top_menu_item').'"';
			$dbo->setQuery($q);
			$alias = $dbo->loadResult();
			$segments[] = $alias;
		}*/
	}

	if($params->get('top_menu_item','') && $params->get('my_use_alias','') && isset($query['product_id'])){
		$q = 'SELECT alias from #__mymuse_product WHERE id="'.$query['product_id'].'"';
		$dbo->setQuery($q);
		if($alias = $dbo->loadResult()){
			$segments[] = $alias;
		}
	}


	
	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	}
	else {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
	}

	// we need to have a view in the query or it is an invalid URL
	if (isset($query['view'])) {
		$view = $query['view'];
	}
	else {

		return $segments;
	}
	

    if(isset($query['task']) && $query['task'] == "checkout"){
    	unset($query['task']);
    	unset($query['view']);
        $segments[] = "checkout";
        return $segments;
    }
    if(isset($query['task']) && $query['task'] == "shipping"){
    	unset($query['task']);
    	unset($query['view']);
        $segments[] = "shipping";
        return $segments;
    }
    if(isset($query['task']) && $query['task'] == "addtocart"){
    	unset($query['task']);
    	unset($query['view']);
        $segments[] = "addtocart";
        return $segments;
    }
    if(isset($query['task']) && $query['task'] == "updatecart"){
    	unset($query['task']);
    	unset($query['view']);
        $segments[] = "updatecart";
        return $segments;
    }
    if(isset($query['task']) && $query['task'] == "cartdelete"){
    	unset($query['task']);
    	unset($query['view']);
        $segments[] = "cartdelete";
        return $segments;
    }
    if(isset($query['task']) && $query['task'] == "showcart"){
    	unset($query['task']);
    	unset($query['view']);
        $segments[] = "showcart";
        return $segments;
    }
    if(isset($query['task']) && $query['task'] == "register"){

    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "register";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "confirm"){
    
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "confirm";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "thankyou"){

    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "thankyou";
    	return $segments;
    }	
    if(isset($query['task']) && $query['task'] == "vieworder"){
    
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "vieworder";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "accdownloads"){
    	
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "accdownloads";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "downloads"){
    
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "downloads";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "vieworder"){
    
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "vieworder";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "paycancel"){
    
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "paycancel";
    	return $segments;
    }
    if(isset($query['task']) && $query['task'] == "downloadfile"){
    
    	unset($query['task']);
    	unset($query['view']);
    	$segments[] = "downloadfile";
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
	//Array([option]=com_mymuse,[view]=product,[id]=1,[catid]=9,[lang]=en-GB,[Itemid]=90)
	if ($view == 'category' || $view == 'product')
	{
		if($params->get('top_menu_item','')){
			$query['Itemid'] = $params->get('top_menu_item');
		}
		
		if (!$menuItemGiven) {
			$segments[] = $view;
		}

		unset($query['view']);
		
		if ($view == 'product') {
			
			
			
			if($params->get('top_menu_item','') && $params->get('my_use_alias','')){
					$aquery = $dbo->setQuery($dbo->getQuery(true)
						->select('alias')
						->from('#__mymuse_product')
						->where('id='.(int)$query['id'])
					);
					$alias = $dbo->loadResult();
					$segments[] = $alias;
					unset($query['id']);
					unset($query['catid']);
					return $segments;
			}
			
			
			
			if (isset($query['id']) && isset($query['catid']) && $query['catid']) {
				$catid = $query['catid'];
				
				// Make sure we have the id and the alias
				if (strpos($query['id'], ':') === false) {
					
					$aquery = $dbo->setQuery($dbo->getQuery(true)
						->select('alias')
						->from('#__mymuse_product')
						->where('id='.(int)$query['id'])
					);
					$alias = $dbo->loadResult();
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
		if($params->get('my_use_alias')){
			//echo "use the alias only";
		}elseif (!$advanced && count($array)) {
			$array[0] = (int)$catid.':'.$array[0];
		}

		$segments = array_merge($segments, $array);

		if ($view == 'product') {
			//print_pre($segments);
			if ($advanced || $params->get('my_use_alias')) {
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
	$task = $jinput->get('task','');
	if($task == "user.logout"){
		return $segments;
	}
	
	$jinput->set('option', 'com_mymuse');
	$menu	= $app->getMenu();
	$item	= $menu->getActive();
	
	$params = MyMuseHelper::getParams();
	$advanced = $params->get('sef_advanced_link', 0);
	$dbo = JFactory::getDBO();
	
	

	//print_pre($segments);
	//print_pre($item);exit;
	// Count route segments
	$count = count($segments);
	
	// Standard routing for products.  If we don't pick up an Itemid then we get the view from the segments
	// the first segment is the view and the last segment is the id of the product or category.
	if (!isset($item)) {
        if($params->get('my_default_itemid','')){
            $item	= $menu->getItem($params->get('my_default_itemid'));
        }
    }
    if (!isset($item)) {
    	if($params->get('top_menu_item','')){
    		$item	= $menu->getItem($params->get('top_menu_item'));
    	}
    }
    if (!isset($item)) {
		$vars['view			}']	= $segments[0];
		$vars['id']		= $segments[$count - 1];
		//print_pre($vars);exit;
		return $vars;
	}

	// if there is only one segment, then it points to either a product or a category
	// we test it first to see if it is a category.  If the id and alias match a category
	// then we assume it is a category.  If they don't we assume it is an product
    // or it could be a cart thing
	if ($count == 1) {
		
        //shipping|addtocart|updatecart|cartdelete|showcart|checkout
        if($segments[0] == "checkout"){
            $vars['option'] = 'com_mymuse';
            $vars['view'] = 'cart';
            $vars['task'] = 'checkout';
            
            return $vars;
        }
        if($segments[0] == "shipping"){
            $vars['option'] = 'com_mymuse';
            $vars['view'] = 'cart';
            $vars['task'] = 'shipping';
            
            return $vars;
        }
        if($segments[0] == "addtocart"){
            $vars['option'] = 'com_mymuse';
            $vars['view'] = 'cart';
            $vars['task'] = 'addtocart';
            
            return $vars;
        }
        if($segments[0] == "updatecart"){
            $vars['option'] = 'com_mymuse';
            $vars['view'] = 'cart';
            $vars['task'] = 'updatecart';
            
            return $vars;
        }
        if($segments[0] == "cartdelete"){
            $vars['option'] = 'com_mymuse';
            $vars['view'] = 'cart';
            $vars['task'] = 'cartdelete';
            
            return $vars;
        }
        if($segments[0] == "showcart"){
            $vars['option'] = 'com_mymuse';
            $vars['view'] = 'cart';
            $vars['task'] = 'showcart';

            return $vars;
        }
        if($segments[0] == "register"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'shopper';
        	$vars['task'] = 'register';
        	$vars['layout'] = 'register';
        
        	return $vars;
        }
        if($segments[0] == "confirm"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'cart';
        	$vars['task'] = 'confirm';
      
        	return $vars;
        }
        if($segments[0] == "thankyou"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'cart';
        	$vars['task'] = 'thankyou';
        
        	return $vars;
        }
        if($segments[0] == "vieworder"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'cart';
        	$vars['task'] = 'vieworder';
        
        	return $vars;
        }
        if($segments[0] == "downloads"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'store';
        	$vars['task'] = 'downloads';
   
        	return $vars;
        }
        if($segments[0] == "accdownloads"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'store';
        	$vars['task'] = 'downloads';
        
        	return $vars;
        }
		if($segments[0] == "vieworder"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'cart';
        	$vars['task'] = 'vieworder';
        	$vars['layout'] = 'cart';
        	return $vars;
        }
        if($segments[0] == "paycancel"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'cart';
        	$vars['task'] = 'paycancel';
        	$vars['layout'] = 'cart';
        	return $vars;
        }
        if($segments[0] == "downloadfile"){
        	$vars['option'] = 'com_mymuse';
        	$vars['view'] = 'store';
        	$vars['task'] = 'downloadfile';
        	return $vars;
        }
        

		if($params->get('my_use_alias')){
			
			if(strpos($segments[0],':')){
                $orig_segments = $segments;
				$segments[0] = preg_replace('/:/',"-",$segments[0]);
			}
		
			//check if this is a category alias.
			$query = 'SELECT id from #__categories WHERE alias="'.$segments[0].'" and extension="com_mymuse"';
			
			$dbo->setQuery($query);
			if($category = $dbo->loadObject()){
				$vars['option'] = 'com_mymuse';
				$vars['view'] = 'category';
				$vars['id'] = (int)$category->id;
				return $vars;
			}
			
			//check if this is a product alias.
			$query = 'SELECT id,catid from #__mymuse_product WHERE alias="'.$segments[0].'"';

			$dbo->setQuery($query);
			if($product = $dbo->loadObject()){
				$vars['option'] = 'com_mymuse';
				$vars['view'] = 'product';
				$vars['id'] = (int)$product->id;
				$vars['catid'] = (int)$product->catid;
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
			$dbo->setQuery($query);
			$product = $dbo->loadObject();

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
	if($params->get('my_use_alias') && $count == 2){
		//check if this is a product alias.
		if(strpos($segments[0],':')){
			$orig_segments = $segments;
			$segments[0] = preg_replace('/:/',"-",$segments[0]);
			$segments[1] = preg_replace('/:/',"-",$segments[1]);
		}elseif(strpos($segments[1],':')){
			$orig_segments = $segments;
			$segments[0] = preg_replace('/:/',"-",$segments[0]);
			$segments[1] = preg_replace('/:/',"-",$segments[1]);

		}
		$query = 'SELECT id,artistid from #__mymuse_product WHERE alias="'.$segments[0].'"';
	
		$dbo->setQuery($query);
		if($product = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'product';
			$vars['id'] = (int)$product->id;
			$vars['catid'] = (int)$product->artistid;
	
			return $vars;
		}
		
		$query = 'SELECT id,artistid from #__mymuse_product WHERE alias="'.$segments[1].'"';
		
		$dbo->setQuery($query);
		if($product = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'product';
			$vars['id'] = (int)$product->id;
			$vars['catid'] = (int)$product->artistid;
		
			return $vars;
		}
			
		//check if this is a category alias. Check last segment first
		$query = 'SELECT id from #__categories WHERE alias="'.$segments[1].'" and extension="com_mymuse"';
		$dbo->setQuery($query);
		if($category = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'category';
			$vars['id'] = (int)$category->id;
			return $vars;
		}
		
		$query = 'SELECT id from #__categories WHERE alias="'.$segments[0].'" and extension="com_mymuse"';
	
		$dbo->setQuery($query);
		if($category = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'category';
			$vars['id'] = (int)$category->id;
			return $vars;
		}
	}
	if($count == 2){
		
		//check if this is a category alias. Check last segment first
		$query = 'SELECT id from #__categories WHERE alias="'.$segments[1].'" and extension="com_mymuse"';
		
		$dbo->setQuery($query);
		if($category = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'category';
			$vars['id'] = (int)$category->id;
			return $vars;
		}
		
		$query = 'SELECT id from #__categories WHERE alias="'.$segments[0].'" and extension="com_mymuse"';
		
		$dbo->setQuery($query);
		if($category = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'category';
			$vars['id'] = (int)$category->id;
			return $vars;
		}
	}
	
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
				$dbo = JFactory::getDBO();
				$query = 'SELECT id FROM #__mymuse_product WHERE catid = '.$vars['catid'].' AND alias = '.$dbo->Quote($segment);
				$dbo->setQuery($query);
				$cid = $dbo->loadResult();
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
