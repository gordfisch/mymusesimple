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

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_ADMIN_PATH')){
	define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
}
if(!defined('MYMUSE_PATH')){
	define('MYMUSE_PATH',JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS);
}
require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'shopper.php' );
require_once( MYMUSE_PATH.DS.'helpers'.DS.'checkout.php' );
require_once( MYMUSE_PATH.DS.'helpers'.DS.'route.php');
require_once( MYMUSE_PATH.DS.'helpers'.DS.'query.php');
require_once( MYMUSE_PATH.DS.'helpers'.DS.'cart.php');
require_once( MYMUSE_PATH.DS.'models'.DS.'product.php');
require_once( MYMUSE_PATH.DS.'models'.DS.'products.php');
require_once( MYMUSE_PATH.DS.'models'.DS.'store.php');
require_once( MYMUSE_PATH.DS.'models'.DS.'shopper.php');


class myMuse
{


	/**
	 * Returns a reference to a global MyMuse object, only creating it if it
	 * doesn't already exist. The default is to look in the helpers directory.
	 *
	 * This method must be invoked as:
	 * 		<pre>  MyMuseShopper 	=& MyMuse::getObject('shopper','models');</pre>
	 *
	 * @access	public
	 * @param	string	$client		type of class.
	 * @param	string	$type 		An optional type, default helpers
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return	MyMuse	The MyMuse object.
	 * @since	1.5
	 */
	function &getObject($client, $type='helpers', $config = array(), $prefix = 'MyMuse', $renew = '')
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		if (empty($instances[$client]) || $renew == "renew")
		{

			$path = JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.$type.DS.$client.'.php';
			if(file_exists($path))
			{
				require_once $path;

				// Create an object
				if($type == 'models'){
					$classname = $prefix.'Model'.ucfirst($client);
				}else{
					$classname = $prefix.ucfirst($client);
				}
				$instance = new $classname($config);
			}
			else
			{
				$error = JError::raiseError(500, 'Unable to load application: '.$client);
				return $error;
			}

			$instances[$client] =& $instance;
		}

		return $instances[$client];
	}
	
}
?>