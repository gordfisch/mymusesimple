<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// No direct access
defined('_JEXEC') or die;
/**
 * For debugging
 * 
 * @param $var
 * @return booleanmGrid
 * 
 * 
 */
function print_pre($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
	return true;
}
define('TAX_REGEX',"[\'-\/\s\\\]");


abstract class JHtmlContentAdministrator
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	static function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png',	'articles.featured',	'COM_CONTENT_UNFEATURED',	'COM_CONTENT_TOGGLE_TO_FEATURE'),
			1	=> array('featured.png',		'articles.unfeatured',	'COM_CONTENT_FEATURED',		'COM_CONTENT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= JHtml::_('image','admin/'.$state[0], JText::_($state[2]), NULL, true);
		if ($canChange) {
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
					. $html.'</a>';
		}

		return $html;
	}
}


jimport( 'joomla.html.parameter' );
/**
 * Mymuse helper.
 */
class MyMuseHelper extends JObject
{

	/**
	 * params from store and component
	 *
	 * @var		object
	 */
	public static $_params = null;
	
	/**
	 * extarray extentions to mime type
	 *
	 * @var		array
	 */
	public $extarray = array(
			'mp3' => 'audio/mpeg',
			'm4a' => 'audio/mp4',
			'ogg' => 'application/ogg',
			'oga' => 'application/ogg',
			'webma' => 'audio/webm',
			'wav' => 'audio/wav',
			'm4v' => 'video/m4v',
			'ogv' => 'video/ogv',
			'webm' => 'video/webmv',
			'webmv' => 'video/webmv',
			'wmv' => 'video/webmv'
	
	);
	
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE'),
			'index.php?option=com_mymuse',
			$vName == 'welcome'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_STORE'),
			'index.php?option=com_mymuse&view=store&task=store.edit&id=1',
			$vName == 'stores'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_CATEGORIES'),
			'index.php?option=com_categories&extension=com_mymuse',
			$vName == 'categories'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_PRODUCTS'),
			'index.php?option=com_mymuse&view=products',
			$vName == 'products'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_SHOPPERGROUPS'),
			'index.php?option=com_mymuse&view=shoppergroups',
			$vName == 'shoppergroups'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_ORDERS'),
			'index.php?option=com_mymuse&view=orders',
			$vName == 'orders'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_TAXRATES'),
			'index.php?option=com_mymuse&view=taxrates',
			$vName == 'taxrates'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_MYMUSE_TITLE_COUPONS'),
			'index.php?option=com_mymuse&view=coupons',
			$vName == 'coupons'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('MYMUSE_MYMUSE_REPORTS'),
			'index.php?option=com_mymuse&view=reports',
			$vName == 'reports'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('MYMUSE_PLUGINS'),
			'index.php?option=com_plugins&view=plugins&filter_folder=mymuse',
			$vName == 'plugins'
		);
		
		

	}
	
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 * @param	int		The product ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0, $productId = 0)
	{
		// Reverted a change for version 2.5.6
		$user	= JFactory::getUser();
		$result	= new JObject;
	
		if (empty($productId) && empty($categoryId)) {
			$assetName = 'com_mymuse';
		}
		elseif (empty($productId)) {
			$assetName = 'com_content.category.'.(int) $categoryId;
		}
		else {
			$assetName = 'com_content.product.'.(int) $productId;
		}
	
		$actions = array(
				'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);
	
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
	
		return $result;
	}
	
	/**
	 * getParams
	 * Gets and sets params by mixing store params and component params
	 * Expands currency settings and gets version from #__extensions
	 * 
	 * @param object  The Store Object
	 * 
	 * @return mixed JParameter object or false
	 */
	public static function getParams($store=0)
	{
		if(!self::$_params){
			jimport( 'joomla.application.component.helper' );
			jimport( 'joomla.html.parameter' );


			if(!$store){
				$db = JFactory::getDBO();
				$query = "SELECT * from #__mymuse_store WHERE id='1'";
				$db->setQuery($query);
				$store = $db->loadObject();
			}
			
			//store params
			$params = new JRegistry($store->params);

			//merge app params
			$app            = JFactory::getApplication();
			if(!$app->isAdmin()){
				$app_params      = $app->getParams();
				$params->merge( $app_params );
			}
			
			//merge component params
			$cparams = JComponentHelper::getParams( 'com_mymuse' );
			$params->merge( $cparams );

			$params->set('my_currency',$params->get('currency'));

			$currency = MyMuseHelper::getCurrency($params->get('currency'));
			$params->set('my_currency_code',$currency['currency_code']);
			$params->set('my_currency_symbol',$currency['symbol']);
			$params->set('my_currency_id',$currency['id']);
			
			$db = JFactory::getDBO();
			$query = "SELECT manifest_cache from #__extensions WHERE element='com_mymuse'";
			$db->setQuery($query);
			if($res = $db->loadResult()){
				$manifest = json_decode( $res, true );
				$params->set('my_version',$manifest['version']);
			}
	
			self::$_params = $params;
		}
		return self::$_params;

	}
	

	/**
	 * Log a message. Should only be done when SHOP_TEST is on
	 * 
	 * @param $message
	 * @return boolean
	 */
	function logMessage($message){
		jimport('joomla.filesystem.file');
		if($fh = fopen(JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'log.txt', "a")){
			fwrite($fh,$message."\n");
			fclose($fh);
		}
		return true;
	}

	/**
	 * returnURL. build a return URL
	 */
	function returnURL()
	{
			$url = JURI::base(true);
			$option         = JRequest::getVar( 'option', '' );
			$task           = JRequest::getVar( 'task', '' );
			$Itemid         = JRequest::getVar( 'Itemid', '' );
			$id 			= JRequest::getVar( 'id','');	
			$controller 	= JRequest::getVar( 'controller','');
			$view			= JRequest::getVar( 'view','');
			$layout			= JRequest::getVar( 'layout','');
			$url .= "/index.php?option=$option";
			if($id){
				$url .= "&id=".$id;
			}
			if($task){
				$url .= "&task=".$task;
			}
			if($view){
				$url .= "&view=".$view;
			}
			if($layout){
				$url .= "&layout=".$layout;
			}
			if($controller){
				$url .= "&controller=".$controller;
			}
			if($Itemid){
				$url .= "&Itemid=".$Itemid;
			}

			$url = base64_encode($url);

			return $url;
	}
 	/**
 	 * Get Currency
 	 * 
 	 * @param string code
 	 * @return array
 	 */
	public static function getCurrency($code=0)
	{

		if(!$code){
			jimport( 'joomla.html.parameter' );
			$db = JFactory::getDBO();
			$query = "SELECT * from #__mymuse_store WHERE id='1'";
			$db->setQuery($query);
			$store = $db->loadObject();
			$params = new JRegistry($store->params);
			$code = $params->get('currency');

		}
		$db = JFactory::getDBO();
		$query = "SELECT * from #__mymuse_currency WHERE currency_code = '".$code."'";

		$db->setQuery($query);
		$curr = $db->loadObject();

		$currency = array(
			'id'			=> $curr->id,
			'currency_name'	=> $curr->currency_name,
			'currency_code' => $curr->currency_code,
			'symbol'		=> $curr->symbol
		);
		return $currency;
	}

	 /**
 	 * printMoney
 	 * format a money string
 	 * 
 	 * @param float amount
 	 * @return string
 	 */
	function printMoney($amount){
		$params 	=& MyMuseHelper::getParams();

		if(!is_numeric($amount)){
			return $amount;
		}

		$str = "";
		if($amount == 0.00 || $amount == 0){
			$str = JText::_('-');
			return $str;
		}
		if($params->get('my_currency_separator') == "" || $params->get('my_currency_separator') == "space" ){
			$my_currency_separator = " ";
		}else{
			$my_currency_separator = $params->get('my_currency_separator');
		}
		
		if($params->get('my_currency_position') == 0){
			$str .= $params->get('my_currency_symbol');
		}
		
		$str .= number_format($amount, 2, $params->get('my_currency_dec_point'), $my_currency_separator);
		if($params->get('my_currency_position') == 1){
			$str .= " ".$params->get('my_currency_symbol');
		}
		
		return $str;
	}
	
     /**
 	 * printMoneyPublic
 	 * format a money string with possible discount
 	 * 
 	 * @param array price
 	 * @return string
 	 */
	function printMoneyPublic($price=array()){
		$params 	=& MyMuseHelper::getParams();
		$string = '';
		/**
		 * $price[item] = 0/1
		 * $price[product_original_price]
		 * $pricd[product_price]
		 * $price[product_discount]
		 * $price[product_shopper_group_discount]
		 * $price[product_shopper_group_discount_amount]
		 */
		if($params->get('my_show_original_price')
			&& ($price['product_discount'] > 0.00 
			|| $price['product_shopper_group_discount_amount'] > 0.00)){
				$string .= '<span class="discount">';
				$string .= MyMuseHelper::printMoney($price['product_original_price']);
				$string .= '</span> ';
		}
		$string .= MyMuseHelper::printMoney($price['product_price']);
		return $string;
	}	
	
	
	/**
	 * logPayment
	 * 
	 * @param $payment array
	 * @return string
	 */
	
	function logPayment($payment){
		$db		= & JFactory::getDBO();
		include_once(JPATH_ADMINISTRATOR.DS."components".DS."com_mymuse".DS."tables".DS."orderpayment.php");
		$table = new MymuseTableorderpayment($db);
		
		if (!$table->bind($payment)) {
            $this->setError($db->getErrorMsg());
            return false;
        }
        
		// Make sure the item payment is valid
        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }
        
		// Store the payment table to the database
        if (!$table->store()) {
            $this->setError($db->getErrorMsg());
            return false;
        }
        return true;
		
	}
	/**
	 * getArtistAlias. alias from artist record
	 * 
	 * @param $id int
	 * @return string
	 */
	
	static function getArtistAlias($id,$parent=0){
		
		$db	= JFactory::getDBO();
		if($parent){
			$query = "SELECT catid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
   			$id = $db->loadResult();
		}
		$query = "SELECT title FROM #__categories 
   		WHERE id='".$id."'";

   		$db->setQuery($query);
   		$alias = JApplication::stringURLSafe($db->loadResult());

		return $alias;
		
	}
	
	/**
	 * getArtistId. get main artistid from product record
	 * 
	 * @param $id int
	 * @param $parent bool
	 * @return string
	 */
	
	function getArtistId($id,$parent=0){

		
		$db	= & JFactory::getDBO();
		if($parent){
			$query = "SELECT catid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
   			$id = $db->loadResult();
		}else{
			$query = "SELECT parentid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
   			$parentid = $db->loadResult();
   			$query = "SELECT catid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
   			$id = $db->loadResult();
		}

		return $id;
		
	}
	
	/**
	 * getAlbumAlias get alias from parent
	 * 
	 * @param $id int
	 * @return string
	 */
	
	static function getAlbumAlias($id){
		
		$db	= JFactory::getDBO();
		$query = "SELECT alias FROM #__mymuse_product 
   			WHERE id='".$id."'";

   		$db->setQuery($query);
   		$alias = $db->loadResult();
		return $alias;
		
	}

	/**
	 * sortObjectsByProperty sort array of objects by a property
	 * 
	 * @param $a array
	 * @param $property string
	 * @param $direction string
	 * @return array
	 */
	function sortObjectsByProperty($a, $property, $direction="asc") {
		$c = array();
		if(!count($a)){
			return $c;
		}
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v->$property);
		}
		if(strtolower($direction) == "desc" ){
			rsort($b);
		}else{
			asort($b);
		}
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	}
	
	/**
	 * findExt get file extension
	 * 
	 * @param $string filename
	 * @return string
	 */
	static function getExt($filename) { 
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		return $ext; 
	}
	
	/**
	 * Get status name from one letter code
	 * 
	 * @param string $code
	 */
	function getStatusName($code){
		
		$db	= & JFactory::getDBO();
		$q = "SELECT name FROM #__mymuse_order_status WHERE ";
		$q .= "code = '".$code."' ";
		$db->setQuery($q);
		$status = $db->loadResult();	
		return $status;
		
	}
	
	/**
	 * Get fields for no registration
	 * 
	 * @return array
	 */
	function getNoRegFields()
	{
		$fields = array(
				'first_name',
				'last_name',
				'email',
				'address1',
				'address2',
				'city',
				'region',
				'region_name',
				'country',
				'postal_code',
				'phone',
				'mobile',
				'tos'
		);
		
		return $fields;
	}
	
	/**
	 * Method to update stock
	 * 
	 * @param int $id product id
	 * @param int $quantity the amount of stock just sold
	 * @return boolean
	 */
	function updateStock($id, $quantity=0)
	{
		if(!$quantity){
			return false;
		}
		if(!$id){
			return false;
		}
		$db = JFactory::getDBO();
		$q = "UPDATE #__mymuse_product SET product_in_stock = product_in_stock - $quantity
		WHERE id=$id";
		$db->setQuery($q);
		if(!$db->query()){
			return false;
		}
		return true;
	}

	/**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   string  $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function sort2($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc')
	{
		$direction = strtolower($direction);
		$images = array('sort_asc.png', 'sort_desc.png');
		$index = intval($direction == 'desc');
	
		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}
	
		$html = '<a href="#" onclick="Joomla.tableOrdering2(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\');" title="'
		. JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';
		$html .= JText::_($title);
	
		if ($order == $selected)
		{
			$html .= JHtml::_('image', 'system/' . $images[$index], '', null, true);
		}
	
		$html .= '</a>';
	
		return $html;
	}
	/**
	* Method to create an icon for saving a new ordering in a grid
	*
	* @param   array   $rows   The array of rows of rows
	* @param   string  $image  The image
	* @param   string  $task   The task to use, defaults to save order
	*
	* @return  string
	*
	* @since   11.1
	*/
	public static function order2($rows, $image = 'filesave.png', $task = 'saveorder')
	{
		// $image = JHtml::_('image','admin/'.$image, JText::_('JLIB_HTML_SAVE_ORDER'), NULL, true);
		$href = '<a href="javascript:saveorder2(' . (count($rows) - 1) . ', \'' . $task . '\')" class="saveorder" title="'
		. JText::_('JLIB_HTML_SAVE_ORDER') . '"></a>';
	
		return $href;
	}
	
	
	/**
	 * Return the icon to move an item UP.
	 *
	 * @param   integer  $i          The row index.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item up or a space.
	 *
	 * @since   11.1
	 */
	public function orderUpIcon2($i, $condition = true, $task = 'orderup', $alt = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb', $limitstart)
	{
	
		if (($i > 0 || ($i + $limitstart > 0)) && $condition)
		{
			return MGrid::orderUp( $i, $task, '', $alt, $enabled, $checkbox, 2);
		}
		else
		{
			return '&#160;';
		}
	}
	
	/**
	 * Return the icon to move an item DOWN.
	 *
	 * @param   integer  $i          The row index.
	 * @param   integer  $n          The number of items in the list.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item down or a space.
	 *
	 * @since   11.1
	 */
	public function orderDownIcon2($i, $n, $condition = true, $task = 'orderdown', $alt = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb', $limitstart, $total)
	{
		if (($i < $n - 1 || $i + $limitstart < $total - 1) && $condition)
		{
			return MGrid::orderDown( $i, $task, '', $alt, $enabled, $checkbox, 2);
		}
		else
		{
			return '&#160;';
		}
	}
	

	
	/**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   string  $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function sort3($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc')
	{
		$direction = strtolower($direction);
		$images = array('sort_asc.png', 'sort_desc.png');
		$index = intval($direction == 'desc');
	
		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}
	
		$html = '<a href="#" onclick="Joomla.tableOrdering3(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\');" title="'
		. JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';
		$html .= JText::_($title);
	
		if ($order == $selected)
		{
			$html .= JHtml::_('image', 'system/' . $images[$index], '', null, true);
		}
	
		$html .= '</a>';
	
		return $html;
	}
	
	
	function order3( $rows, $image='filesave.png', $task="saveorder" )
	{
		// $image = JHtml::_('image','admin/'.$image, JText::_('JLIB_HTML_SAVE_ORDER'), NULL, true);
		$href = '<a href="javascript:saveorder3(' . (count($rows) - 1) . ', \'' . $task . '\')" class="saveorder" title="'
		. JText::_('JLIB_HTML_SAVE_ORDER') . '"></a>';
		
		return $href;
	}
	
	/**
	 * Return the icon to move an item UP.
	 *
	 * @param   integer  $i          The row index.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item up or a space.
	 *
	 * @since   11.1
	 */
	public function orderUpIcon3($i, $condition = true, $task = 'orderup', $alt = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb', $limitstart)
	{
	
		if (($i > 0 || ($i + $limitstart > 0)) && $condition)
		{
			return MGrid::orderUp( $i, $task, '', $alt, $enabled, $checkbox, 3);
		}
		else
		{
			return '&#160;';
		}
	}
	
	/**
	 * Return the icon to move an item DOWN.
	 *
	 * @param   integer  $i          The row index.
	 * @param   integer  $n          The number of items in the list.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item down or a space.
	 *
	 * @since   11.1
	 */
	public function orderDownIcon3($i, $n, $condition = true, $task = 'orderdown', $alt = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb', $limitstart, $total)
	{
		if (($i < $n - 1 || $i + $limitstart < $total - 1) && $condition)
		{
			return MGrid::orderDown( $i, $task, '', $alt, $enabled, $checkbox, 3);
		}
		else
		{
			return '&#160;';
		}
	}
	
	/**
	 * 
	 * @param integer $id order id
	 * @param string $status the new status
	 * @return boolean
	 */
	function orderStatusUpdate($id, $status="P")
	{
		$mainframe = JFactory::getApplication();
		
		require_once( MYMUSE_ADMIN_PATH.DS.'tables'.DS.'order.php' );
		$datenow 	=& JFactory::getDate();
		$params		=& MyMuseHelper::getParams();
		$db			= & JFactory::getDBO();
		$order 		= new MymuseTableorder($db);
		$order->load( $id );
		$order->order_status = $status;
		$order->modified = $datenow->toSql();
		if (!$order->store()) {
			if($params->get('my_debug')){
        			MyMuseHelper::logMessage( "!!ERROR Order Status Update Failed!! id = $id status = $status");
        	}
			JError::raiseError( 500, $db->stderr() );
			return false;
		}
		if($params->get('my_debug')){
        		MyMuseHelper::logMessage( "**orderStatusUpdate id = $id status = $status");
        }
		
		return true;
	}
	 /**
     * ByteSize
     * 
     * @param int $bytes
     * @return string
     */
	function ByteSize($bytes) 
    {
    	$size = $bytes / 1024;
    	if($size < 1024)
    	{
    		$size = number_format($size, 2);
    		$size .= ' KB';
    	}
    	else
    	{
    		if($size / 1024 < 1024)
    		{
    			$size = number_format($size / 1024, 2);
    			$size .= ' MB';
    		}
    		else if ($size / 1024 / 1024 < 1024)
    		{
    			$size = number_format($size / 1024 / 1024, 2);
    			$size .= ' GB';
    		}
    	}
    	return $size;
    } 
    

	/**
	 * Check if email is valid
	 * 
	 * @param string $email
	 * @return unknown_type
	 */
	function is_email_valid($email) {
		if (!eregi("^([a-z]|[0-9]|\.|-|_)+@([a-z]|[0-9]|\.|-|_)+\.([a-z]|[0-9]){2,4}$", $email)) {
			return true;
		} else {
			return false;
		}
	}


	// Prevents SQL injection..
	function add_slashes($data) {
		if (get_magic_quotes_gpc()) {
			$data = stripslashes($data);
		}
		return mysql_real_escape_string($data);
	}


	// Define the client's browser type..
	function get_browser_type() {
		$USER_BROWSER_AGENT = "";

		if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'OPERA';
		} else if (ereg('MSIE ([0-9].[0-9]{1,2})',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'IE';
		} else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'OMNIWEB';
		} else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'MOZILLA';
		} else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'KONQUEROR';
		} else {
			$USER_BROWSER_AGENT = 'OTHER';
		}

		return $USER_BROWSER_AGENT;
	}

	// Define MIME-TYPE according to target Browser..
	function get_mime_type() {
		$USER_BROWSER_AGENT = $this->get_browser_type();

		$mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
		? 'application/octetstream'
		: 'application/octet-stream';

		return $mime_type;
	}
	
	
	
	
	
	/**
	 * treerecurse2 
	 * 
	 * @param $id
	 * @param $indent
	 * @param $list
	 * @param $children
	 * @param $maxlevel
	 * @param $level
	 * @param $type
	 * @return array
	 */
	function treerecurse2( $id, $indent, $list, &$children, $maxlevel=999, $level=0, $type=1 )
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ( $type ) {
					$pre 	= '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else {
					$pre 	= '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ( $v->parent == 0 ) {
					$txt 	= $v->title;
				} else {
					$txt 	= $pre . $v->title;
				}
				$pt = $v->parent_id;
				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count( @$children[$id] );
				$list = MyMuseHelper::treerecurse2( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
			}
		}
		return $list;
	}
	
	function saveProductPrep( &$row )
	{
		// Get submitted text from the request variables
		$text = JRequest::getVar( 'text', '', 'post', 'string', JREQUEST_ALLOWRAW );

		// Clean text for xhtml transitional compliance
		$text		= str_replace( '<br>', '<br />', $text );

		// Search for the {readmore} tag and split the text up accordingly.
		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos	= preg_match($pattern, $text);

		if ( $tagPos == 0 )
		{
			$row->introtext	= $text;
		} else
		{
			list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
		}

		// Filter settings
		jimport( 'joomla.application.component.helper' );
		$config	= JComponentHelper::getParams( 'com_mymuse' );
		$user	= &JFactory::getUser();
		$gid	= $user->get( 'gid' );

		$filterGroups	=  $config->get( 'filter_groups' );

		if (is_array($filterGroups) && in_array( $gid, $filterGroups ))
		{
			$filterType		= $config->get( 'filter_type' );
			$filterTags		= preg_split( '#[,\s]+#', trim( $config->get( 'filter_tags' ) ) );
			$filterAttrs	= preg_split( '#[,\s]+#', trim( $config->get( 'filter_attritbutes' ) ) );
			switch ($filterType)
			{
				case 'NH':
					$filter	= new JFilterInput();
					break;
				case 'WL':
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 0, 0, 0);  // turn off xss auto clean
					break;
				case 'BL':
				default:
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 1, 1 );
					break;
			}
			$row->introtext	= $filter->clean( $row->introtext );
			$row->fulltext	= $filter->clean( $row->fulltext );
		} elseif(empty($filterGroups) && $gid != '25') { // no default filtering for super admin (gid=25)
			$filter = new JFilterInput( array(), array(), 1, 1 );
			$row->introtext	= $filter->clean( $row->introtext );
			$row->fulltext	= $filter->clean( $row->fulltext );
		}
		return true;
	}

	
	function filterCategory($query, $active = NULL)
	{
		// Initialize variables
		$db	= & JFactory::getDBO();

		$categories[] = JHTML::_('select.option', '0', '- '.JText::_('Select Category').' -');
		$db->setQuery($query);
		$categories = array_merge($categories, $db->loadObjectList());

		$category = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

		return $category;
	}

	function filterContent($query, $active = NULL)
	{
		// Initialize variables
		$db	= & JFactory::getDBO();

		$contents[] = JHTML::_('select.option', '0', '- '.JText::_('Select Review').' -');
		$db->setQuery($query);
		$contents = array_merge($contents, $db->loadObjectList());

		$content = JHTML::_('select.genericlist',  $contents, 'contentid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

		return $content;
	}
	
    /**
     * dropdownDisplay()
     *
     * Print an HTML dropdown box named $name using $arr to
     * load the drop down.  If $value is in $arr, then $value
     * will be the selected option in the dropdown.
     *
     * @param string $name
     * @param string $value
     * @param array $arr
     * @return string
     */
     
   function dropdownDisplay($name, $value, $arr, $opt="") {

      
      $string =  "<select class=\"inputbox\" name=\"$name\"  id=\"$name\" $opt>\n";

      while (list($key, $val) = each($arr)) {
         if(strcmp($value, $key) == 0) {
            $string .= "<option value=\"$key\" SELECTED>$val\n";
         }
         else {
            $string .= "<option value=\"$key\">$val</option>\n";
         }
      }

      $string .= "</SELECT>\n";

      return $string;
   }

    /**
     * list_weight_uom
     * Print a select box of weight types
     *
     * @param string $list_name
     * @param string $val
     * @return bool
     */
   function list_weight_uom($list_name,$val="") {
       $list = array("-",
                    "LBS" => "Pounds",
                    "KGS" => "Kilograms",
                    "G" => "Grams");
       $res = MyMuseHelper::dropdownDisplay($list_name, $val, $list);
       return $res;
   }



   
    /**
     * list_currency
     * Print a select box
     *
     * @param string $list_name
     * @param string $value
     * @return bool
     */
   function list_currency($list_name, $value="") {
     $db	= & JFactory::getDBO();
     $str = '';
     $q = "SELECT * from currency ORDER BY currency_name ASC";
     $db->execute($q);
     $str .= "<SELECT class='input' name=$list_name>\n";
     $str .= "<option value=\"\"> - </option>\n";
     while ($db->next_record()) {
       $str .= "<option value=" . $db->f("currency_code");
       if ($value == $db->f("currency_code")) {
     	$str .= " SELECTED";
       }
       $str .= ">" . $db->f("currency_name") . " ".$db->f("currency_symbol") . "</option>\n";
     }
     $str .= "</SELECT>\n";
     return $str;
   }

   

   
   /**
    * 
    */

    function getLimitBox($selected=5){
   		// Initialize variables
		$limits = array ();
		$limits[] = JHTML::_('select.option', '5');
		$limits[] = JHTML::_('select.option', '8');
		$limits[] = JHTML::_('select.option', '10');
		$limits[] = JHTML::_('select.option', '12');
		$limits[] = JHTML::_('select.option', '15');
		$limits[] = JHTML::_('select.option', '20');
		$limits[] = JHTML::_('select.option', '25');
		$limits[] = JHTML::_('select.option', '30');
		$limits[] = JHTML::_('select.option', '50');
		$limits[] = JHTML::_('select.option', '100');
		$limits[] = JHTML::_('select.option', '0', JText::_('all'));
		
		$html = JHTML::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', $selected);
		return $html;
    
    }
    
    /**
     * 
     */
	function getCouponDiscount(&$order){

		if(!isset($order->coupon->id)){
			return ;
		}

		if($order->coupon->coupon_type == 1){
			//it is for a product
			$found = 0;
			for($i=0;$i<count($order->items); $i++){
				if($order->coupon->product_id == $order->items[$i]->product_id){
					$order->coupon->discount_item_price = $order->items[$i]->product_item_price;
					$found = 1;
				}
				
			}
			if($found){
				if($order->coupon->coupon_value_type == 0){
					//it is a flat rate
					$order->coupon->discount = $order->coupon->coupon_value;
				
				}else{
					//it is a percent discount
					$order->coupon->discount = $order->coupon->coupon_value * $order->coupon->discount_item_price/100;
				}
			}else{
				$order->coupon->discount = 0;
			}
		}else{
			//it is for the whole order
			if($order->coupon->coupon_value_type == 0){
				//it is a flat rate
				$order->coupon->discount = $order->coupon->coupon_value;

			}else{
				//it is a percent discount
				$order->coupon->discount = $order->coupon->coupon_value * $order->order_subtotal/100;
			}

		}
			
		
		return true;
		
	}
	
	/**
	 * Return the last json_decode error
	 *
	 * @return  string
	 */
	
	static function getJsonError()
	{
		$message = '';
		if(function_exists('json_last_error')){
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					$message = 'JSON - No errors';
					break;
				case JSON_ERROR_DEPTH:
					$message = 'JSON - Maximum stack depth exceeded';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$message = 'JSON - Underflow or the modes mismatch';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$message = 'JSON - Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					$message = 'JSON - Syntax error, malformed JSON';
					break;
				case JSON_ERROR_UTF8:
					$message = 'JSON - Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				default:
					$message = 'JSON - Unknown error';
					break;
			}
		}
		return $message;
	}
	
}

/**
 * Utility class for creating HTML Grids
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 * @altered for MyMuse
 */
abstract class MGrid
{
	/**
	 * Returns an action on a grid
	 *
	 * @param   integer       $i               The row index
	 * @param   string        $task            The task to fire
	 * @param   string|array  $prefix          An optional task prefix or an array of options
	 * @param   string        $text            An optional text to display
	 * @param   string        $active_title    An optional active tooltip to display if $enable is true
	 * @param   string        $inactive_title  An optional inactive tooltip to display if $enable is true
	 * @param   boolean       $tip             An optional setting for tooltip
	 * @param   string        $active_class    An optional active HTML class
	 * @param   string        $inactive_class  An optional inactive HTML class
	 * @param   boolean       $enabled         An optional setting for access control on the action.
	 * @param   boolean       $translate       An optional setting for translation.
	 * @param   string        $checkbox	       An optional prefix for checkboxes.
	 * @param   integer       $number          The form number (as in AdminForm2)
	 *
	 * @return string         The Html code
	 *
	 * @since   11.1
	 */
	public static function action($i, $task, $prefix = '', $text = '', $active_title = '', $inactive_title = '', $tip = false, $active_class = '',
	$inactive_class = '', $enabled = true, $translate = true, $checkbox = 'cb',$number='')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$text = array_key_exists('text', $options) ? $options['text'] : $text;
			$active_title = array_key_exists('active_title', $options) ? $options['active_title'] : $active_title;
			$inactive_title = array_key_exists('inactive_title', $options) ? $options['inactive_title'] : $inactive_title;
			$tip = array_key_exists('tip', $options) ? $options['tip'] : $tip;
			$active_class = array_key_exists('active_class', $options) ? $options['active_class'] : $active_class;
			$inactive_class = array_key_exists('inactive_class', $options) ? $options['inactive_class'] : $inactive_class;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}
		if ($tip)
		{
			JHtml::_('behavior.tooltip');
		}
		if ($enabled)
		{
			$html[] = '<a class="jgrid' . ($tip ? ' hasTip' : '') . '"';
			$html[] = ' href="javascript:void(0);" onclick="return listItemTask'.$number.'(\'' . $checkbox . $i . '\',\'' . $prefix . $task . '\')"';
			$html[] = ' title="' . addslashes(htmlspecialchars($translate ? JText::_($active_title) : $active_title, ENT_COMPAT, 'UTF-8')) . '">';
			$html[] = '<span class="state ' . $active_class . '">';
			$html[] = $text ? ('<span class="text">' . ($translate ? JText::_($text):$text) . '</span>') : '';
			$html[] = '</span>';
			$html[] = '</a>';
		}
		else
		{
			$html[] = '<a class="jgrid' . ($tip ? ' hasTip' : '') . '"';
			$html[] = ' title="' . addslashes(htmlspecialchars($translate ? JText::_($inactive_title) : $inactive_title, ENT_COMPAT, 'UTF-8')) . '">';
			$html[] = '<span class="state ' . $inactive_class . '">';
			$html[] = $text ? ('<span class="text">' . ($translate ? JText::_($text) : $text) . '</span>') :'';
			$html[] = '</span>';
			$html[] = '</a>';
		}
		return implode($html);
	}

	/**
	 * Returns a state on a grid
	 *
	 * @param   array         $states     array of value/state. Each state is an array of the form
	 *                                    (task, text, title,html active class, HTML inactive class)
	 *                                    or ('task'=>task, 'text'=>text, 'active_title'=>active title,
	 *                                    'inactive_title'=>inactive title, 'tip'=>boolean, 'active_class'=>html active class,
	 *                                    'inactive_class'=>html inactive class)
	 * @param   integer       $value      The state value.
	 * @param   integer       $i          The row index
	 * @param   string|array  $prefix     An optional task prefix or an array of options
	 * @param   boolean       $enabled    An optional setting for access control on the action.
	 * @param   boolean       $translate  An optional setting for translation.
	 * @param   string        $checkbox   An optional prefix for checkboxes.
	 * @param   integer       $number          The form number (as in AdminForm2)
	 *
	 * @return  string       The Html code
	 *
	 * @since   11.1
	 */
	public static function state($states, $value, $i, $prefix = '', $enabled = true, $translate = true, $checkbox = 'cb', $number='')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}
		$state = JArrayHelper::getValue($states, (int) $value, $states[0]);
		$task = array_key_exists('task', $state) ? $state['task'] : $state[0];
		$text = array_key_exists('text', $state) ? $state['text'] : (array_key_exists(1, $state) ? $state[1] : '');
		$active_title = array_key_exists('active_title', $state) ? $state['active_title'] : (array_key_exists(2, $state) ? $state[2] : '');
		$inactive_title = array_key_exists('inactive_title', $state) ? $state['inactive_title'] : (array_key_exists(3, $state) ? $state[3] : '');
		$tip = array_key_exists('tip', $state) ? $state['tip'] : (array_key_exists(4, $state) ? $state[4] : false);
		$active_class = array_key_exists('active_class', $state) ? $state['active_class'] : (array_key_exists(5, $state) ? $state[5] : '');
		$inactive_class = array_key_exists('inactive_class', $state) ? $state['inactive_class'] : (array_key_exists(6, $state) ? $state[6] : '');

		return self::action(
				$i, $task, $prefix, $text, $active_title, $inactive_title, $tip,
				$active_class, $inactive_class, $enabled, $translate, $checkbox, $number
		);
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer       $value         The state value.
	 * @param   integer       $i             The row index
	 * @param   string|array  $prefix        An optional task prefix or an array of options
	 * @param   boolean       $enabled       An optional setting for access control on the action.
	 * @param   string        $checkbox      An optional prefix for checkboxes.
	 * @param   string        $publish_up    An optional start publishing date.
	 * @param   string        $publish_down  An optional finish publishing date.
	 * @param   integer       $number          The form number (as in AdminForm2)
	 *
	 * @return  string  The Html code
	 *
	 * @see     JHtmlJGrid::state
	 * @since   11.1
	 */
	public static function published($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $publish_up = null, $publish_down = null, $number='')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}
		$states = array(1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', false, 'publish', 'publish'),
				0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', false, 'unpublish', 'unpublish'),
				2 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', false, 'archive', 'archive'),
				-2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', false, 'trash', 'trash'));

		// Special state for dates
		if ($publish_up || $publish_down)
		{
			$nullDate = JFactory::getDBO()->getNullDate();
			$nowDate = JFactory::getDate()->toUnix();

			$tz = new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset')));

			$publish_up = ($publish_up != $nullDate) ? JFactory::getDate($publish_up, 'UTC')->setTimeZone($tz) : false;
			$publish_down = ($publish_down != $nullDate) ? JFactory::getDate($publish_down, 'UTC')->setTimeZone($tz) : false;

			// Create tip text, only we have publish up or down settings
			$tips = array();
			if ($publish_up)
			{
				$tips[] = JText::sprintf('JLIB_HTML_PUBLISHED_START', $publish_up->format(JDate::$format, true));
			}
			if ($publish_down)
			{
				$tips[] = JText::sprintf('JLIB_HTML_PUBLISHED_FINISHED', $publish_down->format(JDate::$format, true));
			}
			$tip = empty($tips) ? false : implode('<br/>', $tips);

			// Add tips and special titles
			foreach ($states as $key => $state)
			{
				// Create special titles for published items
				if ($key == 1)
				{
					$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_ITEM';
					if ($publish_up > $nullDate && $nowDate < $publish_up->toUnix())
					{
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
						$states[$key][5] = $states[$key][6] = 'pending';
					}
					if ($publish_down > $nullDate && $nowDate > $publish_down->toUnix())
					{
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
						$states[$key][5] = $states[$key][6] = 'expired';
					}
				}

				// Add tips to titles
				if ($tip)
				{
					$states[$key][1] = JText::_($states[$key][1]);
					$states[$key][2] = JText::_($states[$key][2]) . '::' . $tip;
					$states[$key][3] = JText::_($states[$key][3]) . '::' . $tip;
					$states[$key][4] = true;
				}
			}
			return self::state($states, $value, $i, array('prefix' => $prefix, 'translate' => !$tip), $enabled, true, $checkbox, $number);
		}

		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox, $number);
	}

	/**
	 * Returns a isDefault state on a grid
	 *
	 * @param   integer       $value     The state value.
	 * @param   integer       $i         The row index
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 * @param   integer       $number          The form number (as in AdminForm2)
	 *
	 * @return  The Html code
	 *
	 * @see     JHtmlJGrid::state
	 * @since   11.1
	 */
	public static function isdefault($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$states = array(
				1 => array('unsetDefault', 'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM', 'JDEFAULT', false, 'default', 'default'),
				0 => array('setDefault', '', 'JLIB_HTML_SETDEFAULT_ITEM', '', false, 'notdefault', 'notdefault'),
		);

		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	
	/**
	 * Returns an array of order status filter options.
	 *
	 *
	 * @return  string  The HTML code for the select tag
	 *
	 */
	public static function orderStatusOptions()
	{
		$db =& JFactory::getDBO();
		
		$query = "SELECT * FROM #__mymuse_order_status ORDER BY `ordering`";
		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		// Build the active state filter options.
		$options = array();
		foreach ($results as $res){
			$key = $res->code;
			$val = $res->name;
			$options[] = JHtml::_('select.option', "$key", "$val");
		}

		return $options;
	}
	
	
	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @param   array  $config  An array of configuration options.
	 *                          This array can contain a list of key/value pairs where values are boolean
	 *                          and keys can be taken from 'published', 'unpublished', 'archived', 'trash', 'all'.
	 *                          These pairs determine which values are displayed.
	 *
	 * @return  string  The HTML code for the select tag
	 *
	 * @since   11.1
	 */
	public static function publishedOptions($config = array())
	{
		// Build the active state filter options.
		$options = array();
		if (!array_key_exists('published', $config) || $config['published'])
		{
			$options[] = JHtml::_('select.option', '1', 'JPUBLISHED');
		}
		if (!array_key_exists('unpublished', $config) || $config['unpublished'])
		{
			$options[] = JHtml::_('select.option', '0', 'JUNPUBLISHED');
		}
		if (!array_key_exists('archived', $config) || $config['archived'])
		{
			$options[] = JHtml::_('select.option', '2', 'JARCHIVED');
		}
		if (!array_key_exists('trash', $config) || $config['trash'])
		{
			$options[] = JHtml::_('select.option', '-2', 'JTRASHED');
		}
		if (!array_key_exists('all', $config) || $config['all'])
		{
			$options[] = JHtml::_('select.option', '*', 'JALL');
		}
		return $options;
	}

	/**
	 * Returns a checked-out icon
	 *
	 * @param   integer       $i           The row index.
	 * @param   string        $editorName  The name of the editor.
	 * @param   string        $time        The time that the object was checked out.
	 * @param   string|array  $prefix      An optional task prefix or an array of options
	 * @param   boolean       $enabled     True to enable the action.
	 * @param   string        $checkbox    An optional prefix for checkboxes.
	 *
	 * @return  string  The required HTML.
	 *
	 * @since   11.1
	 */
	public static function checkedout($i, $editorName, $time, $prefix = '', $enabled = false, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$text = addslashes(htmlspecialchars($editorName, ENT_COMPAT, 'UTF-8'));
		$date = addslashes(htmlspecialchars(JHtml::_('date', $time, JText::_('DATE_FORMAT_LC')), ENT_COMPAT, 'UTF-8'));
		$time = addslashes(htmlspecialchars(JHtml::_('date', $time, 'H:i'), ENT_COMPAT, 'UTF-8'));
		$active_title = JText::_('JLIB_HTML_CHECKIN') . '::' . $text . '<br />' . $date . '<br />' . $time;
		$inactive_title = JText::_('JLIB_HTML_CHECKED_OUT') . '::' . $text . '<br />' . $date . '<br />' . $time;

		return self::action(
				$i, 'checkin', $prefix, JText::_('JLIB_HTML_CHECKED_OUT'), $active_title, $inactive_title, true, 'checkedout',
				'checkedout', $enabled, false, $checkbox
		);
	}

	/**
	 * Creates a order-up action icon.
	 *
	 * @param   integer       $i         The row index.
	 * @param   string        $task      An optional task to fire.
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   string        $text      An optional text to display
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 * @param   integer       $number          The form number (as in AdminForm2)
	 *
	 * @return  string  The required HTML.
	 *
	 * @since   11.1
	 */
	public static function orderUp($i, $task = 'orderup', $prefix = '', $text = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb', $number='')
	{

		if (is_array($prefix))
		{
			$options = $prefix;
			$text = array_key_exists('text', $options) ? $options['text'] : $text;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}
		return self::action($i, $task, $prefix, $text, $text, $text, false, 'uparrow', 'uparrow_disabled', $enabled, true, $checkbox, $number);
	}

	/**
	 * Creates a order-down action icon.
	 *
	 * @param   integer       $i         The row index.
	 * @param   string        $task      An optional task to fire.
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   string        $text      An optional text to display
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 * @param   integer       $number          The form number (as in AdminForm2)
	 *
	 * @return  string  The required HTML.
	 *
	 * @since   11.1
	 */
	public static function orderDown($i, $task = 'orderdown', $prefix = '', $text = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb', $number='')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$text = array_key_exists('text', $options) ? $options['text'] : $text;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		return self::action($i, $task, $prefix, $text, $text, $text, false, 'downarrow', 'downarrow_disabled', $enabled, true, $checkbox, $number);
	}
	
}
