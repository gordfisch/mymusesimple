<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the MyMuse component
 *
 */
class MymuseViewCategory extends JViewLegacy
{
	protected $state;
	protected $items;
	protected $category;
	protected $children;
	protected $pagination;

	protected $lead_items = array();
	protected $intro_items = array();
	protected $link_items = array();
	protected $columns = 1;
	protected $cart;
	

	function display($tpl = null)
	{

		$this->MyMuseStore		=& MyMuse::getObject('store','models');
		$this->store			= $this->MyMuseStore->getStore();
		
		$app					= JFactory::getApplication();
		$user					= JFactory::getUser();
		$jinput 				= $app->input;
		$MyMuseCart 			=& MyMuse::getObject('cart','helpers');
		$this->cart 			=& $MyMuseCart->cart;

		// Get some data from the models
		$state					= $this->get('State');
		$params					= $state->params;
		$this->print			= $jinput->get('print',0, 'INT');
		$this->Itemid 			= $jinput->get("Itemid",'');
		$this->sortDirection    = $state->get('list.direction');
		$this->sortColumn       = $state->get('list.ordering');
		$this->filterAlpha      = $jinput->get('filter_alpha', '', 'STRING');
		$layout   				= $jinput->get("layout",'');
		$searchword   			= $jinput->get("searchword",'');
	
		if($layout){
			$this->setLayout($layout);
		}
	
		$menu	= $app->getMenu();
		$item	= $menu->getItem($this->Itemid);

		$top_cat = $jinput->get('id');

		if($params->get('category_layout') == "_:tracks" && $this->getLayout() != "alpha" ){
			$res	= $this->get('Items');
			$items = $res[0];
			$category = $res[1];
			$pagination = $res[2];
		}else{
			$category	= $this->get('Category');
			$items		= $this->get('Items');
			$pagination = $this->get('Pagination');
		}

		// Setup the category parameters.
		$cparams = $category->getParams();
		$category->params = clone($params);
		$category->params->merge($cparams);

		
		$children	= $this->get('Children');
		$parent		= $this->get('Parent');

		$task = $jinput->get('task', 'notask');
		
		$this->total 			= count($items);
		$this->limit 			= $params->get('display_num', 10);
		$this->sortDirection    = $state->get('list.direction');
		$this->sortColumn       = $state->get('list.ordering');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($category == false) {
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		if ($parent == false) {
			//return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}



		// Check whether category access level allows access.
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		//
		// Process the mymuse plugins.
		//
		$dispatcher	= JDispatcher::getInstance();
		$category->event = new stdClass();
		$category->text = $category->description;
		$category->catid = $category->id ;
		$category->list_image = '';
		$category->introtext = $category->description;
		
		$offset = 0;
		
		JPluginHelper::importPlugin('mymuse');
		$results = $dispatcher->trigger('onProductBeforeHeader', array ('com_joomlamymuse.comtegory', &$category, &$this->params, $offset));
		$category->event->beforeDisplayHeader = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onProductAfterTitle', array('com_joomlamymuse.comtegory', &$category, &$this->params, $offset));
		$category->event->afterDisplayTitle = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onProductBeforeDisplay', array('com_joomlamymuse.comtegory', &$category, &$this->params, $offset));
		$category->event->beforeDisplayProduct = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onProductAfterDisplay', array('com_joomlamymuse.comtegory', &$category, &$this->params, $offset));
		$category->event->afterDisplayProduct = trim(implode("\n", $results));

		// PREPARE THE DATA
		// Get the metrics for the structural page layout.
		$numLeading	= $params->def('num_leading_articles', 1);
		$numIntro	= $params->def('num_intro_articles', 4);
		$numLinks	= $params->def('num_links', 4);


		// Compute the product slugs and prepare introtext (runs content plugins).
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			if(isset($items [$i]->id)){
				$item = &$items [$i];
				$item->slug = isset($item->alias) ? ($item->id . ':' . $item->alias) : $item->id;
				
				// No link for ROOT category
				if (isset($item->parent_alias) && $item->parent_alias == 'root') {
					$item->parent_slug = null;
				}
				
				$item->event = new stdClass ();
				
				$dispatcher = JDispatcher::getInstance ();
				
				// Ignore content plugins on links.
				if ($i < $numLeading + $numIntro && isset($item->introtext)) {
					$item->introtext = JHtml::_ ( 'content.prepare', $item->introtext, '', 'com_joomlamymuse.comtegory' );
					
					$results = $dispatcher->trigger ( 'onContentAfterTitle', array (
							'com_mymuse.article',
							&$item,
							&$item->params,
							0 
					) );
					$item->event->afterDisplayTitle = trim ( implode ( "\n", $results ) );
					
					$results = $dispatcher->trigger ( 'onContentBeforeDisplay', array (
							'com_mymuse.article',
							&$item,
							&$item->params,
							0 
					) );
					$item->event->beforeDisplayContent = trim ( implode ( "\n", $results ) );
					
					$results = $dispatcher->trigger ( 'onContentAfterDisplay', array (
							'com_mymuse.article',
							&$item,
							&$item->params,
							0 
					) );
					$item->event->afterDisplayContent = trim ( implode ( "\n", $results ) );
				}
			}
		}

			
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active	= $app->getMenu()->getActive();
			if ((!$active) || ((strpos($active->link, 'view=category') === false) || (
		strpos($active->link, '&id=' . (string) $category->id) === false && strpos($active->link, '&id=' . (string) $category->parent_id) === false))) {
			// Get the layout from the merged category params
			if ($layout = $category->params->get('category_layout')) {
				$this->setLayout($layout);
			}
		}
		// At this point, we are in a menu item, so we don't override the layout
		elseif (!$layout && isset($active->query['layout'])) {
			// We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
			$layout = $active->query['layout'];
			$this->setLayout($active->query['layout']);
		}

		//echo "layout = $layout cat_layout = ".$category->params->get('category_layout');
		
		// For blog layouts, preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interrogate the arrays.
		if (($params->get('layout_type') == 'blog') || ($this->getLayout() == 'blog')) {
			$max = count($items);

			// The first group is the leading articles.
			$limit = $numLeading;
			for ($i = 0; $i < $limit && $i < $max; $i++) {
				$this->lead_items[$i] = &$items[$i];
			}

			// The second group is the intro articles.
			$limit = $numLeading + $numIntro;
			// Order articles across, then down (or single column mode)
			for ($i = $numLeading; $i < $limit && $i < $max; $i++) {
				$this->intro_items[$i] = &$items[$i];
			}

			$this->columns = max(1, $params->def('num_columns', 1));
			$order = $params->def('multi_column_order', 1);

			if ($order == 0 && $this->columns > 1) {
				// call order down helper
				$this->intro_items = ProductHelperQuery::orderDownColumns($this->intro_items, $this->columns);
			}

			$limit = $numLeading + $numIntro + $numLinks;
			// The remainder are the links.
			for ($i = $numLeading + $numIntro; $i < $limit && $i < $max;$i++)
			{
					$this->link_items[$i] = &$items[$i];
			}
		}


		$children = array($category->id => $children);

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assign('maxLevel', $params->get('maxLevel', -1));
		$this->assignRef('state', $state);
		$this->assignRef('items', $items);
		$this->assignRef('category', $category);
		$this->assignRef('children', $children);
		$this->assignRef('params', $params);
		$this->assignRef('parent', $parent);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('user', $user);
		$this->assignRef('searchword', $searchword);

		$this->_prepareDocument();
		$layout = $this->getLayout();
		if($layout == "listdetail"){
			$this->_prepareItems($items);
		}

		parent::display($tpl);
	}
	
	/**
	 * Prepares the items
	 */
	protected function _prepareItems(&$items)
	{
		
		$model = JModel::getInstance('Product', 'MyMuseModel', array('ignore_request' => true));
		for($i=0; $i<count($items); $i++){
			$items[$i] = $model->getItem($items[$i]->id);
		}
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;


		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getItem($this->Itemid);

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$id = (int) @$menu->query['id'];

		if ($menu && ($menu->query['option'] != 'com_mymuse' || $menu->query['view'] == 'article' || $id != $this->category->id)) {
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_mymuse' || 
					$menu->query['view'] == 'article' || 
					$id != @$category->id) 
					
					&& @$category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => MyMuseHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
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

		if ($this->category->metadesc)
		{
			$this->document->setDescription($this->category->metadesc);
		}
		elseif (!$this->category->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->category->metakey)
		{
			$this->document->setMetadata('keywords', $this->category->metakey);
		}
		elseif (!$this->category->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->getCfg('MetaAuthor') == '1') {
			$this->document->setMetaData('author', $this->category->getMetadata()->get('author'));
		}

		$mdata = $this->category->getMetadata()->toArray();

		foreach ($mdata as $k => $v)
		{
			if ($v) {
				$this->document->setMetadata($k, $v);
			}
		}

		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
	
	function _getProductCount($category)
	{
		$total = 0;
		$db = JFactory::getDBO();
		$nullDate	= $db->Quote($db->getNullDate());
		$nowDate	= $db->Quote(JFactory::getDate()->toSql());
		//$catid = $category->id;
		
		$catid[] = $category->id;
		$children = $category->getChildren();
		foreach($children as $child){
			$catid[] = $child->id;
		}

		$catids = implode(",",$catid);
		$query = "SELECT count(*) as total from #__mymuse_product as p
				LEFT JOIN #__mymuse_product_category_xref as x
				ON p.id=x.product_id
				WHERE
				(x.catid IN ($catids) OR p.catid IN ($catids) OR p.artistid IN ($catids) )
		
				AND
				(p.publish_up = " . $nullDate . " OR p.publish_up <= " . $nowDate . ")
				AND (p.publish_down = " . $nullDate . " OR p.publish_down >= " . $nowDate . ")
				AND p.parentid=0
		";
//echo $query;
		$db->setQuery($query);
		$total += $db->loadResult();
			
		return $total;
	}
	
	function _getTrackCount(&$category)
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
