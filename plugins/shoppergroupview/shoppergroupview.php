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

jimport( 'joomla.plugin.plugin');

/**
* MyMuse ShopperGroupView plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseShopperGroupView extends JPlugin
{
	
		/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	  * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgMymuseShpperGroupView(&$subject, $config)  {
		parent::__construct($subject, $config);
		

	}
		
		
	/**
	 * Shopper Group View
	 *shopper, $this->store, $params, $Itemid
	 */
	function onBeforeDisplayProduct($shopper, $store, $params)
	{
		// loading plugin parameters
        $this->_plugin = JPluginHelper::getPlugin('mymuse', 'shoppergroupview');
        
		$layout = "product";
		if(isset($shopper->shopper_group_id) && $shopper->shopper_group_id){
			$p = "default_product_view_".$shopper->shopper_group_id;
			$layout = $this->params->get($p);
		}
		return $layout;
	
	}
} ?>