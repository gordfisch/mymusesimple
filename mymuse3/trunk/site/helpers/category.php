<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * MyMuse Component Category Tree
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.6
 */
class MymuseCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__mymuse_product';
		$options['extension'] = 'com_mymuse';
		parent::__construct($options);
	}
	
	/**
	 * overload the Load method
	 *
	 * @param   integer  $id  Id of category to load
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _load($id)
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$extension = $this->_extension;
		// Record that has this $id has been checked
		$this->_checkedCategories[$id] = true;

		$query = $db->getQuery(true);

		// Right join with c for category
		$query->select('c.*');
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('c.alias');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when);

		$query->from('#__categories as c');
		$query->where('(c.extension=' . $db->Quote($extension) . ' OR c.extension=' . $db->Quote('system') . ')');

		if ($this->_options['access'])
		{
			$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		if ($this->_options['published'] == 1)
		{
			$query->where('c.published = 1');
		}

		$query->order('c.lft');

		// s for selected id
		if ($id != 'root')
		{
			// Get the selected category
			$query->where('s.id=' . (int) $id);
			if ($app->isSite() && $app->getLanguageFilter())
			{
				$query->leftJoin('#__categories AS s ON (s.lft < c.lft AND s.rgt > c.rgt AND c.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')) OR (s.lft >= c.lft AND s.rgt <= c.rgt)');
			}
			else
			{
				$query->leftJoin('#__categories AS s ON (s.lft <= c.lft AND s.rgt >= c.rgt) OR (s.lft > c.lft AND s.rgt < c.rgt)');
			}
		}
		else
		{
			if ($app->isSite() && $app->getLanguageFilter())
			{
				$query->where('c.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
			}
		}

		$subQuery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ' .
			'ON cat.lft BETWEEN parent.lft AND parent.rgt WHERE parent.extension = ' . $db->quote($extension) .
			' AND parent.published != 1 GROUP BY cat.id) ';
		$query->leftJoin($subQuery . 'AS badcats ON badcats.id = c.id');
		$query->where('badcats.id is null');

		// i for item
		if (isset($this->_options['countItems']) && $this->_options['countItems'] == 1)
		{
			if ($this->_options['published'] == 1)
			{
				$query->leftJoin(
				'(SELECT p.id, p.state, x.catid FROM `#__mymuse_product` as p,  `#__mymuse_product_category_xref` as x
    				WHERE p.id = x.product_id
    				AND p.state= 1) AS i ON i.' . $db->quoteName($this->_field) . ' = c.id '
				);
			}
			else
			{
				$query->leftJoin(
				'(SELECT p.id, p.state, x.catid FROM `#__mymuse_product` as p,  `#__mymuse_product_category_xref` as x
    				WHERE p.id = x.product_id
    				) AS i ON i.' . $db->quoteName($this->_field) . ' = c.id '
				);
			}

			$query->select('COUNT(i.' . $db->quoteName($this->_key) . ') AS numitems');
		}

		// Group by
		$query->group('c.id, c.asset_id, c.access, c.alias, c.checked_out, c.checked_out_time,
 			c.created_time, c.created_user_id, c.description, c.extension, c.hits, c.language, c.level,
		 	c.lft, c.metadata, c.metadesc, c.metakey, c.modified_time, c.note, c.params, c.parent_id,
 			c.path, c.published, c.rgt, c.title, c.modified_user_id');

		// Get the results

		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		$childrenLoaded = false;

		if (count($results))
		{
			// Foreach categories
			foreach ($results as $result)
			{
				//arboreta, get any products with this as their main caegory, add it to numitems
				$query = "SELECT count(*) FROM #__mymuse_product WHERE catid=".$result->id."
				AND parentid=0";
				$db->setQuery($query);
				$more = $db->loadResult();
				if($more){
					$result->numitems += $more;
				}
				
				// Deal with root category
				if ($result->id == 1)
				{
					$result->id = 'root';
				}

				// Deal with parent_id
				if ($result->parent_id == 1)
				{
					$result->parent_id = 'root';
				}

				// Create the node
				if (!isset($this->_nodes[$result->id]))
				{
					// Create the JCategoryNode and add to _nodes
					$this->_nodes[$result->id] = new JCategoryNode($result, $this);

					// If this is not root and if the current node's parent is in the list or the current node parent is 0
					if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 1))
					{
						// Compute relationship between node and its parent - set the parent in the _nodes field
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}

					// If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
					// then remove the node from the list
					if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->id]);
						continue;
					}

					if ($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}
				}
				elseif ($result->id == $id || $childrenLoaded)
				{
					// Create the JCategoryNode
					$this->_nodes[$result->id] = new JCategoryNode($result, $this);

					if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id))
					{
						// Compute relationship between node and its parent
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}

					if (!isset($this->_nodes[$result->parent_id]))
					{
						unset($this->_nodes[$result->id]);
						continue;
					}

					if ($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}

				}
			}
		}
		else
		{
			$this->_nodes[$id] = null;
		}
	}
	
	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  array  The children
	 *
	 * @since   11.1
	 */
	public function &getChildren($recursive = false)
	{
		
		if (!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->id, true);
			if ($temp)
			{
				$this->_children = $temp->getChildren();
				$this->_leftSibling = $temp->getSibling(false);
				$this->_rightSibling = $temp->getSibling(true);
				$this->setAllLoaded();
			}
		}
	
		if ($recursive)
		{
			$items = array();
			foreach ($this->_children as $child)
			{
				$items[] = $child;
				$items = array_merge($items, $child->getChildren(true));
			}
			return $items;
		}
	
		return $this->_children;
	}
}