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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once(JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'helpers'.DS.'route.php');

class plgSearchSearchMymuse extends JPlugin
{

	/**
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	

    /**
    * @return array An array of search areas
    */
    function onContentSearchAreas()
    {
        static $areas = array(
            'mymuse' => 'MYMUSE_PRODUCTS'
        );
        return $areas;
        
    }


    /**
    * Product Search method
    * The sql must return the following fields that are used in a common display
    * routine: href, title, section, created, text, browsernav
    * @param string Target search string
    * @param string mathcing option, exact|any|all
    * @param string ordering option, newest|oldest|popular|alpha|category
    * @param mixed An array if the search it to be restricted to areas, null if search all
    */
    function onContentSearch($text, $phrase='', $ordering='', $areas=null)
    {

    	require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';
    	
        $db     = JFactory::getDbo();
        $app    = JFactory::getApplication();
        $user   = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $tag 	= JFactory::getLanguage()->getTag();
        
    
        $searchText = $text;
        if (is_array( $areas )) {
            if (!array_intersect( $areas, array_keys( $this->onContentSearchAreas() ) )) {
                return array();
            }
        }
    

        $searchItems 	= $this->params->get( 'search_product_items', 	1 );
        $linkToCats 	= $this->params->get( 'link_categories_only', 	0 );
        $searchArtists 	= $this->params->get( 'search_artists_only', 	0 );
        $limit 			= $this->params->get( 'search_limit', 		50 );
    
        $nullDate   = $db->getNullDate();
        $date       =& JFactory::getDate();
        $now        = $date->toSql();

    
        $text = trim( $text );
        if ($text == '') {
            return array();
        }
        $section    = JText::_('Search - Products');
    
        $wheres = array();
        switch ($phrase) {
            case 'exact':
                $text		= $db->Quote( '%'.$db->escape( $text, true ).'%', false );
                $wheres2 	= array();
                $wheres2[] 	= 'a.title LIKE '.$text;
                $wheres2[] 	= 'a.introtext LIKE '.$text;
                $wheres2[] 	= 'a.fulltext LIKE '.$text;
                $wheres2[] 	= 'a.metakey LIKE '.$text;
                $wheres2[] 	= 'a.metadesc LIKE '.$text;
                $wheres2[]  = 'c.title LIKE '.$text;
                $wheres2[]  = 'c.description LIKE '.$text;
                $where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
                break;
    
            case 'all':
            case 'any':
            default:
                $words = explode( ' ', $text );
                $wheres = array();
                foreach ($words as $word) {
                    $word		= $db->Quote( '%'.$db->escape( $word, true ).'%', false );
                    $wheres2 	= array();
                    $wheres2[] 	= 'a.title LIKE '.$word;
                    $wheres2[] 	= 'a.introtext LIKE '.$word;
                    $wheres2[] 	= 'a.fulltext LIKE '.$word;
                    $wheres2[] 	= 'a.metakey LIKE '.$word;
                    $wheres2[] 	= 'a.metadesc LIKE '.$word;
                    $wheres2[]  = 'c.title LIKE '.$word;
                    $wheres2[]  = 'c.description LIKE '.$word;
                    $wheres[] 	= implode( ' OR ', $wheres2 );
                }
                $where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
                break;
        }
    
        $morder = '';
        switch ($ordering) {
            case 'oldest':
                $order = 'a.created ASC';
                break;
    
            case 'popular':
                $order = 'a.hits DESC';
                break;
    
            case 'alpha':
                $order = 'a.title ASC';
                break;
    
            case 'category':
                $order = 'b.title ASC, a.title ASC';
                $morder = 'a.title ASC';
                break;
    
            case 'newest':
                default:
                $order = 'a.created DESC';
                break;
        }
    
        $rows = array();
    
        // search products
        if ( $limit > 0 )
        {
            //CASE WHEN CHAR_LENGTH(p.title) THEN CONCAT_WS(": ",a.title,p.title) ELSE a.title END AS title,
            $query = 'SELECT a.id, a.parentid, a.title as title, a.metakey,'
            . ' a.catid, a.created AS created,'
            . ' CONCAT(a.introtext, a.fulltext) AS text,'
            . " CONCAT(".$db->Quote($section).",' : ', c.title) AS section,"
            . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'
            . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug,'
            . ' "2" AS browsernav'
            . ' FROM #__mymuse_product AS a';
            if($searchArtists){
            	//main category only
                 $query .= " INNER JOIN #__categories AS c ON c.id=a.catid";
            }else{
            	// any category
            	$query .= " INNER JOIN #__categories AS c ON c.id=a.catid";
                $query .= ' LEFT JOIN #__mymuse_product_category_xref AS x ON x.product_id=a.id'
                . " LEFT JOIN #__categories AS cc ON c.id=x.catid";
            }

             
            $query .=  ' WHERE ( '.$where.' )';
            if(!$searchItems){
            	$query .= " AND parentid = '0'";
            }
            
            // Filter by language
            if ($app->isSite() && $app->getLanguageFilter()) {
            	$tag = JFactory::getLanguage()->getTag();
            	$query .= ' AND a.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')';
            	$query .= ' AND c.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')';
            }
            
            $query .= ' AND a.state = 1'
            . ' AND c.published = 1'
            . ' AND a.access IN ('.$groups.') '
            . ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
            . ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
            . ' GROUP BY a.id'
            . ' ORDER BY '. $order
            ;

            $db->setQuery( $query, 0, $limit );
            $list = $db->loadObjectList();
            $limit -= count($list);

            if(count($list))
            {
                foreach($list as $key => $item)
                {
                    if($item->parentid > 0){
                        $id = $item->parentid;
                    }else{
                        $id = $item->id;
                    }
                    if($this->params->get('link_categories_only')){
                    	$list[$key]->href = myMuseHelperRoute::getCategoryRoute($item->catid);
                    }else{
                    	$list[$key]->href = myMuseHelperRoute::getProductRoute( $id, $item->catid );
                    }
                    
                }
                $rows[] = $list;
            }
            
        }
      
        $section = JText::_("MYMUSE_CATEGORY");
        //What about the categories
        $query = 'SELECT a.title, a.description AS text, "" AS created, "'.$section.'" as section, "2" AS browsernav, a.id AS catid,  '
        ." CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(':', a.id, a.alias) ELSE a.id END as slug "
        ." FROM #__categories AS a "
		." WHERE (a.title LIKE ".$db->Quote($text)." OR a.description LIKE ".$db->Quote($text).") "
		.' AND a.published IN (1) '
		." AND a.extension = 'com_mymuse' "
		.' AND a.access IN ('.$groups.') '
		." GROUP BY a.id "
		." ORDER BY a.title DESC ";
		
		$db->setQuery( $query, 0, $limit );
        $list2 = $db->loadObjectList();

		if(count($list2)){
			foreach($list2 as $key => $item)
			{
				$list2[$key]->href = myMuseHelperRoute::getCategoryRoute($item->catid);
			}
			$rows[] = $list2;
		}

        $results = array();
        if(count($rows))
        {
            foreach($rows as $row)
            {
                $new_row = array();
                foreach($row AS $key => $article) {
                    //if(searchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey'))) {
                        $new_row[] = $article;
                    //}
                }
                $results = array_merge($results, (array) $new_row);
            }
        }
    
        return $results;
    }
}
