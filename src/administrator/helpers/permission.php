<?php

//include constants file
//require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS.'defineconstants.php');

defined( '_JEXEC' ) or die( 'Restricted access' );

class MymuseHelperPerm {
	
	
	/*
	 * Calculates global component PERMISSIONS of the current USER
	 *
	 * @access	public
	 * @param	boolean		$force		Forces the recalculation of the PERMISSIONS
	 *
	 * @return array							The array of user PERMISSIONS 
	 * @since	2.0
	 * 
	 */
	static function getPerm($force = false)
	{
		static $permission = null;
		
		if(!$permission || $force) {
			// handle jcomments integration
			$Comments_Enabled 	= JPluginHelper::isEnabled('system', 'jcomments.system') || JPluginHelper::isEnabled('system', 'jcomments');
			
			$user = JFactory::getUser();
			$permission = new stdClass;
			
			// !!! This is the Super User Privelege of GLOBAL Configuration		(==> core.admin ACTION allowed on ROOT ASSET: 'root.1')
			$permission->SuperAdmin = JAccess::check($user->id, 'core.admin', 'root.1');
			
			//!!! ALLOWs USERS to change component's CONFIGURATION						(==> core.admin ACTION allowed on COMPONENT ASSET: e.g. 'com_mymuse')
			$permission->CanConfig 		= $user->authorise('core.admin', 				'com_mymuse');
					
			//!!! ALLOWs USERS in JOOMLA BACKEND :
			//   (a) to view the FLEXIcontent menu item in Components Menu and
			//   (b) to access the FLEXIcontent component screens (whatever they are allowed to see by individual FLEXIcontent area permissions)
			//       NOTE: the initially installed permissions allows all areas to be managed
			$permission->CanManage		= $user->authorise('core.manage', 			'com_mymuse');
			
			// ITEMS/CATEGORIES: category-inherited permissions, (NOTE: these are the global settings, so:)
			// *** 1. the action permissions of individual items are checked seperately per item
			// *** 2. the view permission is checked via the access level of each item
			$permission->CanAdd 			= $user->authorise('core.create', 				'com_mymuse');
			$permission->CanDelete 		= $user->authorise('core.delete', 				'com_mymuse');
			$permission->CanDeleteOwn	= $user->authorise('core.delete.own', 		'com_mymuse');
			$permission->CanEdit 			= $user->authorise('core.edit', 					'com_mymuse');
			$permission->CanEditOwn 	= $user->authorise('core.edit.own', 			'com_mymuse');
			$permission->CanPublish 	= $user->authorise('core.edit.state',			'com_mymuse');
			$permission->CanPublishOwn= $user->authorise('core.edit.state.own',	'com_mymuse');
			
			// ITEMS: component controlled permissions
			$permission->DisplayAllItems	= $user->authorise('mymuse.displayallitems','com_mymuse'); // (backend) List all items (otherwise only items that can be edited)
			//$permission->CanAccLevelItems = $user->authorise('mymuse.acclevelitems',	'com_mymuse'); // (backend) Change Item Access Level
			$permission->CanCopy				 	= $user->authorise('mymuse.copyitems',			'com_mymuse'); // (backend) Item Copy Task
			$permission->CanOrder	 				= $user->authorise('mymuse.orderitems', 		'com_mymuse'); // (backend) Reorder items inside the category
			$permission->CanParams				= $user->authorise('mymuse.paramsitem', 		'com_mymuse'); // (backend) Edit item parameters like meta data and template parameters
			$permission->CanVersion				= $user->authorise('mymuse.versioning',			'com_mymuse'); // (backend) Use item versioning
			
			// CATEGORIES: management tab and usage
			$permission->CanCats 					= $user->authorise('mymuse.managecats',	'com_mymuse'); // (item edit form) view the categories which user cannot assign to items
			$permission->CanUserCats 			= $user->authorise('mymuse.usercats',		'com_mymuse'); // (item edit form) view the categories which user cannot assign to items
			$permission->CanViewTree 			= $user->authorise('mymuse.viewtree',		'com_mymuse'); // (item edit form) view categories as tree instead of flat list
			$permission->MultiCat					= $user->authorise('mymuse.multicat',		'com_mymuse'); // (item edit form) allow user to assign each item to multiple categories
			
			// TAGS: management tab and usage
			$permission->CanTags 			= $user->authorise('mymuse.managetags',	'com_mymuse'); // (backend) Allow management of Item Types
			$permission->CanUseTags		= $user->authorise('mymuse.usetags', 		'com_mymuse'); // edit already assigned Tags of items
			$permission->CanNewTags		= $user->authorise('mymuse.newtags',		'com_mymuse'); // add new Tags to items
			
			// VARIOUS management TABS: types, archives, statistics, templates, tags
			$permission->CanTypes 		= $user->authorise('mymuse.managetypes',			'com_mymuse'); // (backend) Allow management of Item Types
			$permission->CanArchives 	= $user->authorise('mymuse.managearchives', 	'com_mymuse'); // (backend) Allow management of Archives
			$permission->CanTemplates	= $user->authorise('mymuse.managetemplates', 	'com_mymuse'); // (backend) Allow management of Templates
			$permission->CanStats			= $user->authorise('mymuse.managestats', 			'com_mymuse'); // (backend) Allow management of Statistics
			
			// FIELDS: management tab
			$permission->CanFields 				= $user->authorise('mymuse.managefields', 	'com_mymuse'); // (backend) Allow management of Fields
			//$permission->CanAccLevelFields= $user->authorise('mymuse.acclevelfields',	'com_mymuse'); // (backend) Change Field Access Level
			$permission->CanCopyFields 		= $user->authorise('mymuse.copyfields', 		'com_mymuse'); // (backend) Field Copy Task
			$permission->CanOrderFields 	= $user->authorise('mymuse.orderfields', 		'com_mymuse'); // (backend) Reorder fields inside each item type
			$permission->CanAddField			= $user->authorise('mymuse.createfield', 	'com_mymuse'); // (backend) Create fields
			$permission->CanEditField			= $user->authorise('mymuse.editfield', 		'com_mymuse'); // (backend) Edit fields
			$permission->CanDeleteField		= $user->authorise('mymuse.deletefield', 	'com_mymuse'); // (backend) Delete fields
			$permission->CanPublishField	= $user->authorise('mymuse.publishfield', 'com_mymuse'); // (backend) Publish fields
			
			// FILES: management tab
			$permission->CanFiles	 				= $user->authorise('mymuse.managefiles', 		'com_mymuse'); // (backend) Allow management of Files
			$permission->CanUpload	 			= $user->authorise('mymuse.uploadfiles', 		'com_mymuse'); // allow user to upload Files
			$permission->CanViewAllFiles	= $user->authorise('mymuse.viewallfiles',		'com_mymuse'); // allow user to view all Files
			
			// OTHER components permissions
			$permission->CanPlugins	 	= $user->authorise('core.manage', 'com_plugins');
			$permission->CanComments 	= $Comments_Enabled && $user->authorise('core.manage', 'com_jcomments');
			

	
			$permission->CanSaveItem = $user->authorise('core.saveitem',			'com_mymuse'); // (backend) Item Copy Task
			
		}
		
		return $permission;
	}
	
	/*
	 * Lookups the SECTION ids on which the given USER can perform the given ACTION
	 *
	 * @access	public
	 * @param	integer		$uid			The USER id
	 * @param	string		$action		The ACTION name
	 * @param	string		$section	The SECTION for which to lookup SECTION ids
	 * @param	boolean		$force		Forces the recalculation of SECTION ids
	 *
	 * @return array							The array of SECTION ids (category ids OR item ids OR fields ids ...)
	 * @since	2.0
	 * 
	 */
	function checkUserElementsAccess($uid, $action, $section, $force=false)
	{
		// $elements[$uid][$section][$action] is an array of SECTION id(s)
		// that the USER with ID {$uid} can perform the specified ACTION {$action}
		static $elements = array();
		
		// For compatibility reasons, we use 'com_content' assets for both categories and items section
		$extension = ($section == 'category' || $section == 'item' ) ? 'com_content' : 'com_mymuse';
		
		// For compatibility reasons, we use 'article' assets for items
		$dbsection = ($section == 'item') ? 'article' : $section;
		
		// Create the PARTIAL asset NAME excluding the final id part
		$asset_partial = "{$extension}.{$dbsection}";
		
		if(!isset($elements[$uid][$section][$action]) || $force) {
			// Get database and use objects
			$db = &JFactory::getDBO();
			$user = &JFactory::getUser($uid);
			
			// Query the assets table to retrieve the asset names for the specified section
			$query = "SELECT name FROM #__assets WHERE name like '{$asset_partial}.%';";
			$db->setQuery($query);
			$names = $db->loadResultArray();
			if ($db->getErrorNum()) {
				$jAp=& JFactory::getApplication();
				$jAp->enqueueMessage('SQL QUERY ERROR:<br/>'.nl2br($query."\n".$db->getErrorMsg()."\n"),'error');
			}
			
			// Create an empty array that will contain the sections on which the user can perform the given action
			$elements[$uid][$section][$action] = array();
			
			// Find all section ids (e.g. item ids) that user can perform the given action
			foreach($names as $name) {
				$id = str_replace("{$extension}.{$dbsection}.",  "", $name);
				if($user->authorize($action, $name)) {
					$elements[$uid][$section][$action][] = $id;
				}
			}
		}
		
		return $elements[$uid][$section][$action];  // RETURN the ids
	}
	
	
	/*
	 * Lookups the ACTIONS that the given USER can perform on the given ASSET
	 *
	 * @access	public
	 * @param	integer		$uid				The USER ID
	 * @param	string		$section		The SECTION name of the ASSET
	 * @param	string		$id					The ID of the ASSET
	 * @param	boolean		$force			Forces the recalculation of ACTIONS
	 * @param	boolean		$recursive	Indicates to use heritage for calculating allowed ACTIONS
	 *
	 * @return array								The array of ACTION names (create, edit, edit.own ...)
	 * @since	2.0
	 * 
	 */
	function checkAllItemAccess($uid, $section, $id, $force=false, $recursive = false)
	{
		// $actions[$uid][$asset] is an array of ACTION names
		// allowed for USER with ID {$uid} on the ASSET with name {$asset}
		static $actions = array();
		
		// For compatibility reasons, we use 'com_content' assets for both categories and items section
		$extension = ($section == 'category' || $section == 'item' ) ? 'com_content' : 'com_mymuse';
		
		// For compatibility reasons, we use 'article' assets for items
		$dbsection = ($section == 'item') ? 'article' : $section;
		
		// Create the asset name
		$asset = "{$extension}.{$dbsection}.{$id}";
		
			
		if(!isset($actions[$uid][$asset]) || $force) {
			// Get user object
			$user = &JFactory::getUser($uid);
			
			// This string will be removed from action names to make them shorter e.g. will make 'core.edit.own' to be 'edit.own'
			$action_start = ($dbsection == 'category' || $dbsection == 'article' ) ? 'core.' : 'mymuse.';
			
			// Create an empty array that will contain the actions allowed on the asset
			$actions[$uid][$asset] = array();
			
			// Retrieve all available user actions for the section
			$actions_arr = JAccess::getActions('com_mymuse', $dbsection);
			
			// Find all allowed user actions on the asset
			foreach($actions_arr as $action_data) {
				$action_name = $action_data->name;
				if( $user->authorise($action_name, $asset) ) {
					$action_shortname = str_replace($action_start, "", $action_name);
					$actions[$uid][$asset][] = $action_shortname;
				}
			}
		}
		
		return $actions[$uid][$asset];  // RETURN the (shortened) action names
	}
	
	
	//*******************************************************************************
	// THIS function implementation  works properly too, but the above is more proper
	//*******************************************************************************
	
	/*function checkAllItemAccess($uid, $section, $id, $force=false, $recursive = false)
	{
		// $actions[$uid][$asset] is an array of ACTION names
		// allowed for USER with ID {$uid} on the ASSET with name {$asset}
		static $actions = array();
		
		// For compatibility reasons, we use 'com_content' assets for both categories and items section
		$extension = ($section == 'category' || $section == 'item' ) ? 'com_content' : 'com_mymuse';
		
		// For compatibility reasons, we use 'article' assets for items
		$dbsection = ($section == 'item') ? 'article' : $section;
		
		// Create the asset name
		$asset = "{$extension}.{$dbsection}.{$id}";
		
		if(!isset($actions[$uid][$asset]) || $force) {
			$user = &JFactory::getUser($uid);
			$db = &JFactory::getDBO();
			
			// Build the database query to get the rules for the asset.
			$query	= $db->getQuery(true);
			$query->select($recursive ? 'b.rules' : 'a.rules');
			$query->from('#__assets AS a');
			$query->where('name="'.$asset.'"');
			
			// If we want the rules cascading up to the global asset node we need a self-join.
			if ($recursive) {
				$query->leftJoin('#__assets AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');
				$query->order('b.lft');
			}
			
			// Execute the query and load the rules from the result.
			$db->setQuery($query);
			$rules_string	= $db->loadResult(); // $db->loadColumn();
			if ($db->getErrorNum()) {
				$jAp=& JFactory::getApplication();
				$jAp->enqueueMessage('SQL QUERY ERROR:<br/>'.nl2br($query."\n".$db->getErrorMsg()."\n"),'error');
			}
			
			// Instantiate a JRules object for the asset rules
			$rules = new JRules($rules_string);       //SAME AS: $rules = new JRules(); $rules->merge($rules_string);
			
			// Get user's groups (user identities)
			$groups = $user->getAuthorisedGroups();   //SAME AS: $groups = JAccess::getGroupsByUser($uid, $recursive);
			
			// This string will be removed from action names to make them shorter e.g. will make 'core.edit.own' to be 'edit.own'
			$action_start = ($dbsection == 'category' || $dbsection == 'article' ) ? 'core.' : 'mymuse.';
			
			// Create an empty array that will contain the actions allowed on the asset
			$actions[$uid][$asset] = array();
			
			// Retrieve all available user actions for the section
			$actions_arr = JAccess::getActions('com_mymuse', $dbsection);
			
			// Find all allowed user actions on the asset
			foreach($actions_arr as $action_data) {
				$action_name = $action_data->name;
				if($rules->allow($action_name, $groups) || $user->authorise($action_name, 'com_mymuse') ) {
					$action_shortname = str_replace($action_start, "", $action_name);
					$actions[$uid][$asset][] = $action_shortname;
				}
			}
		}
		return $actions[$uid][$asset];
	}*/
	
	
	/**
	 * Lookups the categories (their IDs), that the user has access to perforn the specified action(s)
	 *
	 * @param array		$action_names		The required actions
	 * @param bool		$require_all		True to require --all-- (Logical AND) or false to require --any-- (Logical OR)
	 *
	 * @return array									The category IDs
	 * @since	2.0
	 */
	function getCats($action_names=array('core.create', 'core.edit', 'core.edit.own'), $require_all=true) {
		static $usercats = null;
		$db = & JFactory::getDBO();
		$user = & JFactory::getUser();
		
		$query = 'SELECT c.id '
			. ' FROM #__categories AS c'
			. ' WHERE c.published = 1';
		$db->setQuery($query);
		$allcats = $db->loadResultArray();
		$usercats = array();
		foreach ($allcats as $category_id)
		{
			// Construct asset name for the category
			$asset = 'com_content.category.'.$category_id;
			
			// We start with FALSE for OR and TRUE for AND
			$has_access = $require_all ? false : true;
			
			// Check all actions with Logical OR or Logical AND
			foreach ($action_names as $action_name)
			{
				if ($require_all) {
					$has_access = $has_access | $user->authorise($action_name, $asset);
				} else {
					$has_access = $has_access & $user->authorise($action_name, $asset);
				}
			}
			if ($has_access) $usercats[] = $category_id;
		}
		return $usercats;
	}

}


class FlexiAccess extends JAccess {
	public static function print_asset($asset='core.admin') {
		/*if (empty(self::$assetRules[$asset])) {
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}*/
		echo "<pre style='font-size:12px;'>";
		print_r(self::$assetRules);
		echo "</pre>";
	}	
}

?>
