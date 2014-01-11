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