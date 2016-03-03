<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

/// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Content Component Route Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class myMuseHelperRoute
{
	protected static $lookup;
	
	/**
	 * @param	int	The route of the content item
	 */
	public static function getProductRoute($id, $catid = 0, $language = 0)
	{
	

		require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS.'helpers'.DS.'mymuse.php' );
		
		$db 	= JFactory::getDBO();
		$params = MyMuseHelper::getParams();
		
		//make sure we link to the parent
		$q = "SELECT parentid from #__mymuse_product WHERE id ='".$id."'";
		$db->setQuery($q);
		$parentid = $db->loadResult();
		if($parentid > 0){
			$id = $parentid;
		}
		// now we can link to the parent
		$needles = array(
				'product'  => array((int) $id)
		);
		

		
		//if not use alias
		//Create the link
		$link = 'index.php?option=com_mymuse&view=product&id='. $id;
		
		if ((int)$catid > 1)
		{
			$categories = JCategories::getInstance('mymuse');
			$category = $categories->get((int)$catid);
			if($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}
		/*
			if ($language && $language != "*" && JLanguageMultilang::isEnabled()) {
				$db		= JFactory::getDBO();
				$query	= $db->getQuery(true);
				$query->select('a.sef AS sef');
				$query->select('a.lang_code AS lang_code');
				$query->from('#__languages AS a');
				//$query->where('a.lang_code = ' .$language);
				$db->setQuery($query);
				$langs = $db->loadObjectList();
				foreach ($langs as $lang) {
					if ($language == $lang->lang_code) {
						$language = $lang->sef;
						$link .= '&lang='.$language;
					}
				}
			}
			*/
			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
				$needles['language'] = $language;
			}
			
			
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem()) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}



	public static function getCategoryRoute($catid)
	{
		require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS.'helpers'.DS.'mymuse.php' );
		
	
		$db 	= JFactory::getDBO();
		$params = MyMuseHelper::getParams();
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
			$category = $catid;
		}
		else
		{
			$id = (int) $catid;
			$category = JCategories::getInstance('mymuse')->get($id);
		}
		


		if($id < 1)
		{
			$link = '';
		}
		else
		{
			$needles = array(
				'category' => array($id)
			);

			if ($item = self::_findItem($needles))
			{
				$link = 'index.php?Itemid='.$item;
			}
			else
			{
				//Create the link
				$link = 'index.php?option=com_mymuse&view=category&id='.$id;
				if($category)
				{
					//echo "category path = "; print_pre($category->getPath());
					$catids = array_reverse($category->getPath());
					$needles = array(
						'category' => $catids,
						'categories' => $catids
					);
					if ($item = self::_findItem($needles)) {
						$link .= '&Itemid='.$item;
					}
					elseif ($item = self::_findItem()) {
						$link .= '&Itemid='.$item;
					}
				}
			}
		}


		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$language 	= isset($needles['language']) ? $needles['language'] : '*';
		$params 	= MyMuseHelper::getParams();

		// Prepare the reverse lookup array.
		/*
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_mymuse');
			$items		= $menus->getItems('component_id', $component->id);
			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view])) {
						self::$lookup[$view] = array();
					}
					if (isset($item->query['id'])) {
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
				}
			}
		}
	*/
		
		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();
		
			$component  = JComponentHelper::getComponent('com_mymuse');
		
			$attributes = array('component_id');
			$values     = array($component->id);
		
			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}
		
			$items = $menus->getItems($attributes, $values);
	
			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
		
					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}
		
					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}
		
		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach($ids as $id)
					{
						if(preg_match("/:/", $id)){
							list($id,$alias) = explode(":",$id);
						}
						if (isset(self::$lookup[$language][$view][(int)$id])) {
							return self::$lookup[$language][$view][(int)$id];
						}
					}
				}
			}
		}
		else
		{
			if($params->get('product_artist_alternate_itemid', 0)){
				//return $params->get('product_artist_alternate_itemid');
			}
			$active = $menus->getActive();
			if ($active && $active->component == 'com_mymuse') {
				return $active->id;
			}
		}

		return null;
	}
}