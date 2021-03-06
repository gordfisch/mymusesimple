<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Content categories view.
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class mymuseViewCategories extends JViewLegacy
{
	protected $state = null;
	protected $item = null;
	protected $items = null;

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{
		
		// Initialise variables
		$state		= $this->get('State');	
		$items		= $this->get('Items');
		$parent		= $this->get('Parent');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		if ($items === false)
		{
			JError::raiseError(404, JText::_('MYMUSE_ERROR_CATEGORY_NOT_FOUND'));
			return false;
		}

		if ($parent == false)
		{
			JError::raiseError(404, JText::_('MYMUSE_ERROR_PARENT_CATEGORY_NOT_FOUND'));
			return false;
		}

		$params = &$state->params;

		$items = array($parent->id => $items);

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assign('maxLevelcat',	$params->get('maxLevelcat', -1));
		$this->assignRef('params',		$params);
		$this->assignRef('parent',		$parent);
		$this->assignRef('items',		$items);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}
		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
	
	function _getProductCount($category){

		$catid[] = $category->id;
		$children = $category->getChildren();
		foreach($children as $child){
			$catid[] = $child->id;
		}
		$catids = implode(",",$catid);

		$db = JFactory::getDBO();
		$nullDate	= $db->Quote($db->getNullDate());
		$nowDate	= $db->Quote(JFactory::getDate()->toSql());
		
		$query = "SELECT  p.title from #__mymuse_product as p
		LEFT JOIN #__mymuse_product_category_xref as x
		ON p.id=x.product_id
		WHERE
		(x.catid IN ($catids) OR p.catid IN ($catids) OR p.artistid IN ($catids) )
		
		AND
		(p.publish_up = ".$nullDate." OR p.publish_up <= ".$nowDate.")
		AND (p.publish_down = ".$nullDate." OR p.publish_down >= ".$nowDate.")
		AND p.state = 1

		AND p.parentid=0 GROUP BY p.id
		";

	
		$db->setQuery($query);
		$res = $db->loadObjectList();

		$total = count($res);

		return $total;
		
	}
	
	function _getTrackCount($category)
	{
		$total = 0;
		$db = JFactory::getDBO();
		$nullDate	= $db->Quote($db->getNullDate());
		$nowDate	= $db->Quote(JFactory::getDate()->toSql());
		$catid[] = $category->id;
		$children = $category->getChildren();
		foreach($children as $child){
			$catid[] = $child->id;
		}
	
		$catids = implode(",",$catid);
		$query = "SELECT count(*) as total FROM #__mymuse_product as track
	
		LEFT JOIN #__mymuse_product as parent ON parent.id=track.parentid
		LEFT JOIN #__mymuse_product_category_xref as x ON parent.id=x.product_id
		WHERE
		(x.catid IN ($catids) OR parent.catid IN ($catids) OR parent.artistid IN ($catids) )
	
		AND
		(parent.publish_up = ".$nullDate." OR parent.publish_up <= ".$nowDate.")
            AND (parent.publish_down = ".$nullDate." OR parent.publish_down >= ".$nowDate.")
	
            AND parent.state=1
	
            AND
            (track.publish_up = ".$nullDate." OR track.publish_up <= ".$nowDate.")
            AND (track.publish_down = ".$nullDate." OR track.publish_down >= ".$nowDate.")
	
            AND track.state=1
	
            AND parent.parentid=0
	
		";
	
		$db->setQuery($query);
		$total += $db->loadResult();
	
		return $total;
	}
}
