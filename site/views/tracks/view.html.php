<?php
/**
 * @version     $Id$
 * @package     com_mymuse3.0
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the MyMuse component
 *
 */
class mymuseViewtracks extends JViewLegacy
{
	protected $state;
	protected $items;
	protected $category;
	protected $pagination;
    protected $cart;
 
    
	function display($tpl = null)
	{
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$db     = JFactory::getDBO();

		// Get some data from the models
		$state		= $this->get('State');
		$params		= $state->params;

		$category	= $this->get('Category');
        $products   = $this->get('Products');// sets list.prods for tracks query

        $MyMuseCart =& MyMuse::getObject('cart','helpers');
        $this->cart =& $MyMuseCart->cart;
        
        $this->sortDirection    = $state->get('list.direction');
        $this->sortColumn       = $state->get('list.ordering');
        $this->Itemid           = JRequest::getVar('Itemid');
        $filter_alpha           = JRequest::getString('filter_alpha', '');
        $this->task             = JRequest::getString('task', 'view');

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active	= $app->getMenu()->getActive();

		if ( $this->getLayout() != "alphatunes" && ( !$active || ((strpos($active->link, 'view=category') === false) || (strpos($active->link, '&id=' . (string) $category->id) === false)))) {
			// Get the layout from the merged category params
			if ($layout = $category->params->get('category_layout')) {
				$this->setLayout($layout);
			}
		}
		// At this point, we are in a menu item, so we don't override the layout
		elseif (isset($active->query['layout'])) {
				
			// We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		//items!
        $result = $this->get('Items');

        $items = $result[0];
        //print_pre($items);
        $category->flash = $result[1]->flash;
        $pagination = $result[2];
        
        if($params->get('show_alphabet')){
			$alpha = array ();
			$alphabet = explode ( ":", JText::_ ( 'MYMUSE_ALPHABET' ) );
			$IN = $state->get ( 'list.prods', '' );
			$featured = $params->get ( 'featured', '0' );
			
			foreach ( $alphabet as $letter ) {
				$query = "SELECT count(*) as total
            FROM #__mymuse_product as a
            LEFT JOIN #__mymuse_product as p ON a.parentid = p.id
            LEFT JOIN #__categories as c ON a.catid = c.id
            LEFT JOIN #__mymuse_product_rating AS v ON a.id = v.product_id
            WHERE a.product_downloadable = 1
            AND a.state=1
            AND c.title LIKE '$letter%'
            AND (a.catid = " . $category->id . " OR a.catid IN ( 
			SELECT sub.id FROM #__categories as sub 
			INNER JOIN #__categories as this ON sub.lft > this.lft 
			AND sub.rgt < this.rgt WHERE this.id = " . $category->id . ") 
			OR a.id IN (0)) 
            ";
				if ($featured) {
					$query .= " AND a.featured=1
            	";
				}
				
				$db->setQuery ( $query );
				$total = $db->loadResult ();
				/**
				 * $total = 0;
				 * foreach($items as $item){
				 * //echo $item->category_name."<br />";
				 * if(strtolower($letter) == strtolower(mb_substr($item->category_name, 0, 1))){
				 * $total = 1;
				 *
				 * }
				 * }
				 */
				
				$class = "";
				if ($filter_alpha == $letter) {
					$class = "selected";
				}
				if ($total) {
					$alpha [] = '<a class="letter ' . $class . '" href="' . JRoute::_ ( 'index.php?option=com_mymuse&view=tracks&layout=alphatunes&id=' . $category->id . '&filter_alpha=' . $letter . '&Itemid=' . $this->Itemid ) . '">' . $letter . '</a>';
				} else {
					$alpha [] = '<span class="letter">' . $letter . '</span>';
				}
			}
			$this->assignRef ( 'alpha', $alpha );
			$this->filterAlpha = JRequest::getVar ( 'filter_alpha', '' );
        }
		$this->total = $this->get('Total');
		$this->limit = $params->get('display_num', 10);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($category == false) {
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}


		// Setup the category parameters.
		$cparams = $category->params;
		$category->params = clone($params);
		$category->params->merge($cparams);

		// Check whether category access level allows access.
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}



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
		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$id = (int) @$menu->query['id'];
/**
		if ($menu && ($menu->query['option'] != 'com_mymuse' || $menu->query['view'] == 'article' || $id != $this->category->id)) {
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_mymuse' || $menu->query['view'] == 'article' || $id != $category->id) && $category->id > 1)
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
*/
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


		$mdata = json_decode($this->category->metadata, true);

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
}