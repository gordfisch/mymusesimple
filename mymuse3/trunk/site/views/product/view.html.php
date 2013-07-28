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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the MyMuse Category 
 *
 * @package		Joomla
 * @subpackage MyMuse
 * @since 1.5
 */
class myMuseViewProduct extends JView
{
	protected $item;
	protected $params;
	protected $print;
	protected $state;
	protected $user;

	function display($tpl = null)
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$dispatcher	= JDispatcher::getInstance();

		$this->item		= $this->get('Item');
		$this->print	= JRequest::getBool('print');
		$this->state	= $this->get('State');
		$this->user		= $user;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Create a shortcut for $item.
		$item = &$this->item;

		// Add router helpers.
		$item->slug			= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
		$item->catslug		= $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
		$item->parent_slug	= $item->category_alias ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;

		// TODO: Change based on shownoauth
		$item->readmore_link = JRoute::_(MyMuseHelperRoute::getProductRoute($item->slug, $item->catslug));

		// Merge product params. If this is single-product view, menu params override product params
		// Otherwise, product params override menu item params
		$this->params	= $this->state->get('params');
		$this->params->merge($params = MyMuseHelper::getParams());

		$active	= $app->getMenu()->getActive();
		$temp	= clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {

			$currentLink = $active->link;
			// If the current view is the active item and an product view for this product, then the menu item params take priority
			if (strpos($currentLink, 'view=product') && (strpos($currentLink, '&id='.(string) $item->id))) {
				// $item->params are the product params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) {
					$this->setLayout($active->query['layout']);
				}
			}
			else {
				// Current view is not a single product, so the product params take priority here
				// Merge the menu item params with the product params so that the product params take priority
				$temp->merge($item->params);
				$item->params = $temp;
				// Check for alternative layouts (since we are not in a single-product menu item)
				// Single-product menu item layout takes priority over alt layout for an product
				if(!$item->params->get('product_layout')){
					$item->params->set('product_layout', 'product');
				}
				if ($layout = $item->params->get('product_layout')) {
					$this->setLayout($layout);
				}
			}
		}
		else {
			// Merge so that product params take priority
			$temp->merge($item->params);
			$item->params = $temp;
			// Check for alternative layouts (since we are not in a single-product menu item)
			// Single-product menu item layout takes priority over alt layout for an product
			if ($layout = $item->params->get('product_layout')) {
				$this->setLayout($layout);
			}
		}
		if($this->getLayout() == ''){
			$this->setLayout('product');
		}
		$offset = $this->state->get('list.offset');

		// Check the view access to the product (the model has already computed the values).
		if ($item->params->get('access-view') != true && (($item->params->get('show_noauth') != true &&  $user->get('guest') ))) {

						JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));

				return;

		}

		if ($item->params->get('show_intro', '1')=='1') {
			$item->text = $item->introtext.' '.$item->fulltext;
		}
		elseif ($item->fulltext) {
			$item->text = $item->fulltext;
		}
		else  {
			$item->text = $item->introtext;
		}
		$this->Itemid = JRequest::getVar("Itemid",'');

		//
		// Process the content plugins.
		//
		JPluginHelper::importPlugin('mymuse');
		$results = $dispatcher->trigger('onProductPrepare', array ('com_mymuse.product', &$item, &$this->params, $offset));

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onProductAfterTitle', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onProductBeforeDisplay', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->beforeDisplayProduct = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onProductAfterDisplay', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->afterDisplayProduct = trim(implode("\n", $results));

		// Increment the hit counter of the product.
		if (!$this->params->get('intro_only') && $offset == 0) {
			$model = $this->getModel();
			$model->hit();
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));

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
		$pathway = $app->getPathway();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('MyMuse'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// if the menu item does not concern this product
		if ($menu && ($menu->query['option'] != 'com_mymuse' || $menu->query['view'] != 'product' || $id != $this->item->id))
		{
			// If this is not a single product menu item, set the page title to the product title
			if ($this->item->title) {
				$title = $this->item->title;
			}
			$path = array(array('title' => $this->item->title, 'link' => ''));
			$category = JCategories::getInstance('Content')->get($this->item->catid);
			while ($category && ($menu->query['option'] != 'com_mymuse' || $menu->query['view'] == 'product' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => ContentHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}
			$path = array_reverse($path);
			foreach($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->title;
		}
		$this->document->setTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (!$this->item->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif (!$this->item->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->getCfg('MetaAuthor') == '1')
		{
			$this->document->setMetaData('author', $this->item->author);
		}

		$mdata = $this->item->metadata->toArray();
		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetadata($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($this->item->page_title))
		{
			$this->item->title = $this->item->title . ' - ' . $this->item->page_title;
			$this->document->setTitle($this->item->page_title . ' - ' . JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1));
		}

		if ($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}
	