<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');
/**
 * upgrade controller
 */
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');

class MymuseControllerUpgrade extends JControllerAdmin
{

	
	/**
	* array tables //
	*/
	var $tables = array(
			"1" => "secCats",
			"2" => "products",
			"3" => "taxRate",
			"4" => "orders",
			"5" => "orderItem",
			"6" => "orderPayment",
			"7" => "orderShipping",
			"8" => "productAttibuteSKU",
			"9" => "productAttibute",
			"10" => "store",
			"11" => "shopperGroup",
			"12" => "coupons",
			"13" => "shopper",
			"14" => "finish"
		);
	

	function notask(){
		$mainframe =& JFactory::getApplication();
		$dbprefix= $mainframe->getUserStateFromRequest( "com_mymuse.dbprefix", 'dbprefix', 'jos' );
		$this->setRedirect( 'index.php?option=com_mymuse&view=upgrade', $this->msg );
		return true;
	}
	/**
	 * importFromMymuse15
	 * @return void
	 */
	function importFromMymuse15()
	{
		$mainframe =& JFactory::getApplication();
		$dbprefix= $mainframe->getUserStateFromRequest( "com_mymuse.dbprefix", 'dbprefix', 'jos' );
		$func = JRequest::getVar('table', 'secCats');

		if($func == "finish"){
			$this->msg = "<h2>All done!</h2> ";
			$this->setRedirect( 'index.php?option=com_mymuse&view=upgrade&task=done', $this->msg );
			return true;
		}
				
		if(!$this->$func($dbprefix)){
			$this->msg .= "Error while working on $func: ".$this->getError()."<br />";
			foreach($this->tables as $index=>$table){
				if($table == $func){
					$i = $index;
				}
			}
			$next =  $this->tables[$i+1];
			$this->msg .= '<a href="index.php?option=com_mymuse&task=upgrade.importFromMymuse15&limit=30&limitstart=0&table='.$next.'">Continue?</a>';
			$this->setRedirect( 'index.php?option=com_mymuse&view=upgrade', $this->msg );
			return false;
		}
		$continue = JRequest::getVar('continue', 0);
		
		if(!$continue){
			foreach($this->tables as $index=>$table){
				if($table == $func){
					$i = $index;
				}
			}
			$next =  $this->tables[$i+1];
		}else{
			//let's continue
			$next = $func;
		}
		
		if($next == "finish"){
			$this->msg = "All done! ".$this->msg;
			$this->setRedirect( 'index.php?option=com_mymuse&view=upgrade&task=done', $this->msg );
		}elseif($continue){
			$limit = JRequest::getVar('limit','30');
			$limitstart = JRequest::getVar('limitstart','0');
			$limitstart = $limitstart  + $limit;
			$this->msg = "Continue with $next <br />".$this->msg;
			$this->setRedirect( 'index.php?option=com_mymuse&view=upgrade&task=upgrade.importFromMymuse15_2&continue=1&table='.$func.'&limitstart='.$limitstart.'&limit='.$limit, $this->msg );
				
		}else{
			$this->msg = $this->msg." <br />Starting with $next ";
			$this->setRedirect( 'index.php?option=com_mymuse&view=upgrade&task=upgrade.importFromMymuse15_2&limitstart=0&table='.$next, $this->msg );
		}
		return true;
		

	}
	
	/**
	 * 
	 *
	 */
	
	function importFromMymuse15_2()
	{
		$limit = JRequest::getVar('limit','30');
		$limitstart = JRequest::getVar('limitstart','0');
		$table = JRequest::getVar('table','secCats');
		$continue = JRequest::getVar('continue', 0);
		$url = "index.php?option=com_mymuse&task=upgrade.importFromMymuse15&limit=$limit&limitstart=$limitstart&table=$table";
		if($continue){
			$url .= "&continue=1";
		}
		$to = $limitstart + $limit;
		echo '<meta http-equiv="refresh" content="6;url='.$url.'">';
		echo "Working on $table : start $limitstart finish $to";
	
	}
	
	
	function secCats($dbprefix)
	{
		$helper = new MyMuseUpdateHelper;

		// sections and categories
		$this->msg =  "<br /><h2>Sections and Categories</h2>";
		$limit = JRequest::getVar('limit','30');
		$limitstart = JRequest::getVar('limitstart','0');
		
		$i = $limitstart;
		$i++;
		$jform = JRequest::getVar('jform','');
	
		$oldartistcat = $jform['oldartistcat'];
		$oldgenrecat = $jform['oldgenrecat'];
		$artistcat = $jform['artistcat'];
		$genrecat = $jform['genrecat'];
		
		$catx = array();
		$secx[$oldartistcat] = $artistcat;
		$secx[$oldgenrecat] = $genrecat;
		
		$db = JFactory::getDBO();
		$query = "SELECT * from ".$dbprefix."_mymuse_categories
		LIMIT $limitstart , $limit";
		
		$db->setQuery($query);
		if(!$oldcats = $db->loadObjectList()){
			//we are all done categories
			
			//fix menus with categories
			//index.php?option=com_mymuse&view=category&layout=columns&id=1
			$query = "SELECT id, newcatid from ".$dbprefix."_mymuse_categories ";
			$db->setQuery($query);
			$oldcats = $db->loadObjectList();
			foreach($oldcats as $oldcat){
				
				$query = "UPDATE #__menu SET 
				link='index.php?option=com_mymuse&view=category&layout=columns&id=".$oldcat->newcatid."'
				WHERE link='index.php?option=com_mymuse&view=category&layout=columns&id=".$oldcat->id."'
				";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError("Could not fix menu $query: ".$db->getErrorMsg());
					return false;
				}
				$query = "UPDATE #__menu SET
				link='index.php?option=com_mymuse&view=category&layout=list&id=".$oldcat->newcatid."'
				WHERE link='index.php?option=com_mymuse&view=category&layout=list&id=".$oldcat->id."'
				";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError("Could not fix menu $query: ".$db->getErrorMsg());
					return false;
				}
			}

			
			JRequest::setVar('continue', 0);
			JRequest::setVar('limitstart', 0);
			$this->msg  = "All done categories<br />";
			return true;
		}
		foreach($oldcats as $oldcat){
			$title = $oldcat->title;
			$alias = $oldcat->alias;
			$parent_id = $secx[$oldcat->section];
			$image  = preg_replace('#artists/#', '', $oldcat->image);
			$description = $oldcat->description;
			if(!$catx[$oldcat->id] = $helper->makeCategory($title, $parent_id, $description, $image, $alias)){
				$this->setError("Could not make category $title: ".$helper->error());
				return false;
			}else{
				$this->msg .= "$i. Made category $title. <br />";
			}
			$i++;
			
			$query = "UPDATE ".$dbprefix."_mymuse_categories SET newcatid='".$catx[$oldcat->id]."'
			WHERE id='".$oldcat->id."'";
			$db->setQuery($query);
			$db->execute();
		}
		
		$continue = JRequest::setVar('continue', 1);
		return true;
		
	}
	
	function products($dbprefix)
	{
		$helper = new MyMuseUpdateHelper;
		$continue = 1;
		$this->msg = "<h2>Working on Products</h2>";
		$db = JFactory::getDBO();		
		$prodx = array();
		
		$query = "SELECT id,newcatid from ".$dbprefix."_mymuse_categories";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$catx[$r->id] = $r->newcatid;
		}
		
		$query = "SELECT id,newprodid from ".$dbprefix."_mymuse_product WHERE newprodid > 0";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$prodx[$r->id] = $r->newprodid;
		}
		
		$limit = JRequest::getVar('limit','30');
		$limitstart = JRequest::getVar('limitstart','0');
		
		$i = $limitstart;
		$i++;
		$rm = '<hr id="system-readmore" /><hr id="system-readmore" />';
		
		$query = "SELECT a.*, p.product_price as price
		FROM ".$dbprefix."_mymuse_product as a
		LEFT JOIN ".$dbprefix."_mymuse_product_price as p on p.product_id=a.id
		WHERE p.shopper_group_id=1
		GROUP BY a.id ORDER BY a.parentid asc, a.id asc LIMIT $limitstart , $limit
		";
		$db->setQuery($query);
		if(!$oldprods = $db->loadObjectList()){
			//we are all done products
			
			//fix menus with products
			//index.php?option=com_mymuse&view=product&layout=product&id=86
			$query = "SELECT id, newprodid from ".$dbprefix."_mymuse_products ";
			$db->setQuery($query);
			$oldprods = $db->loadObjectList();
			foreach($oldprods as $oldprod){

				$query = "UPDATE #__menu SET
				link='index.php?option=com_mymuse&view=product&layout=product&id=".$oldprod->newprodid."'
				WHERE link='index.php?option=com_mymuse&view=product&layout=product&id=".$oldprod->id."'
				";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError("Could not fix menu $query: ".$db->getErrorMsg());
					return false;
				}

			}
			$continue = JRequest::setVar('continue', 0);
			$this->msg  = "All done products<br />";
			return true;
		}
		
		

		foreach($oldprods as $oldprod){
			if(!$oldprod->artistid){
				$query = "SELECT artistid from ".$dbprefix."_mymuse_product WHERE id='".$oldprod->parentid."'";
				$db->setQuery($query);
				$oldprod->artistid = $db->loadResult();
			}
			if(!$oldprod->artistid){
				$this->msg .= "$i. Could not find OLD category/artist id for ".$oldprod->title;
				$this->msg .= print_r($catx, true);
				$this->msg .= print_r($oldprod, true);
				return false;
				$i++;
				continue;
			}
			$oldprod->catid = $catx[$oldprod->artistid];
			if(!$oldprod->catid){

				$this->msg .= "$i. Could not find NEW category/artist id for ".$oldprod->title;
				$this->msg .= print_r($catx, true);
				$this->msg .= print_r($oldprod, true);
				return false;
				$i++;
				continue;
			}
				
			if($oldprod->parentid > 0){
				$oldprod->parentid = $prodx[$oldprod->parentid];
			}
				
			if($oldprod->fulltext != "" && $oldprod->introtext != ""){
				$oldprod->articletext = $oldprod->introtext.$rm.$oldprod->fulltext;
			}
			if($oldprod->fulltext != "" && $oldprod->introtext == ""){
				$oldprod->articletext = $oldprod->fulltext;
			}
			if($oldprod->fulltext== "" && $oldprod->introtext != ""){
				$oldprod->articletext = $oldprod->introtext;
			}
			$othercats = array();
			$query = "SELECT * from ".$dbprefix."_mymuse_product_category_xref WHERE product_id='".$oldprod->id."'";
			$db->setQuery($query);
			if($res = $db->loadObjectList()){
				foreach($res as $r){
					$othercats[] = $catx[$r->catid];
				}
			}
			$oldprod->othercats = $othercats;
			if(!$oldprod->product_sku){
				$oldprod->product_sku = $oldprod->alias;
			}
		
			//call the func already
			if(!$prodx[$oldprod->id] = $helper->upgradeProduct($oldprod)){
				echo "Could not make product: " .$oldprod->title." ".$this->getError();
				return false;
			}else{
				$this->msg .= "$i. Made product ".$oldprod->title."  parentid:".$oldprod->parentid." <br />";
			}
			$query = "UPDATE ".$dbprefix."_mymuse_product SET newprodid='".$prodx[$oldprod->id]."'
			WHERE id='".$oldprod->id."'";
			$db->setQuery($query);
			$db->execute();
			
			//$this->msg .= "$query <br />";
			$query = "SELECT * from ".$dbprefix."_mymuse_product_category_xref WHERE product_id=".$oldprod->id;
			$db->setQuery($query);
			if($res = $db->loadObjectList()){
				foreach($res as $r){
					$query = "INSERT INTO #__mymuse_product_category_xref (catid,product_id) VALUES 
					(". $catx[$r->catid] .",". $prodx[$oldprod->id] .")";
					$db->setQuery($query);
					$db->execute();
				}
			}
			$i++;
		}
		//$this->msg .= print_r($prodx, true);
		//echo $this->msg; exit;
		$continue = JRequest::setVar('continue', 1);
		return true;
	}
	

	
	
	function taxRate($dbprefix)
	{
		$i = 1;
		$db = JFactory::getDBO();
		$table = $dbprefix."_mymuse_tax_rate";
		$query = "ALTER TABLE `$table` 
		ADD `checked_out` INT( 11 ) NOT NULL ,
		ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		CHANGE `state` `province` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
		ADD `state` INT( 1 ) NOT NULL DEFAULT '1' AFTER `id`
		";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Problem altering $table <br />";
			$this->msg .= $db->getErrorMsg();
			//return false;
		}
	
		$query = "INSERT INTO `#__mymuse_tax_rate`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		// put taxes into orders table
		$query = "SELECT tax_name FROM $table";
		$db->setQuery($query);
		$tax_names = $db->loadObjectList();
		foreach($tax_names as $t){
			$query = "ALTER table #__mymuse_order ADD `".$t->tax_name."` DECIMAL(10,2) NOT NULL DEFAULT '0.00'";
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
		
		
	function orders($dbprefix)
	{
		$db = JFactory::getDBO();
		$i = 1;
		$table = $dbprefix."_mymuse_order";

		
		$query = "ALTER TABLE `$table` ADD `checked_out` INT( 11 ) NOT NULL ";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		$query = "ALTER TABLE `$table` ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		$query = "ALTER TABLE `$table` ADD `ordering` INT( 11 ) NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		$query = "ALTER TABLE `$table` DROP `ReferenceNum`";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		$query = "ALTER TABLE `$table` DROP `TxnNumber`";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		$query = "ALTER TABLE `$table` DROP `ship_method_id`";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		
		// move the data over
		$query = "INSERT INTO `#__mymuse_order`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
			$query = "SHOW COLUMNS FROM $table";
			$db->setQuery($query);
			$old = $db->loadObjectList();
			foreach($old as $o){
				$oldcols[] = $o->Field;
			}
			$query = "SHOW COLUMNS FROM #__mymuse_order";
			$db->setQuery($query);
			$new = $db->loadObjectList();
			foreach($new as $n){
				$newcols[] = $n->Field;
			}
			$this->msg .= "old columns <br />";
			$this->msg .= print_r($oldcols, true);
			$this->msg .= "new columns <br />";
			$this->msg .= print_r($newcols, true);
			
			return false;
		}
		

		return true;
	}
	
	function orderItem($dbprefix)
	{
		$db = JFactory::getDBO();
		$i = 1;
		$table = $dbprefix."_mymuse_order_item";
		$query = "alter table ".$dbprefix."_mymuse_order_item ADD `product_in_stock` int(1) DEFAULT NULL";
		$db->setQuery($query);
		if(!$db->execute()){
			$this->msg .= "$i. Possible problem copying $table <br />";
			$this->msg .= $db->getErrorMsg()."<br />";
		}
		
		$query = "INSERT INTO `#__mymuse_order_item`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		
		$query = "SELECT id,newprodid from ".$dbprefix."_mymuse_product";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$prodx[$r->id] = $r->newprodid;
		}
		
		$query = "SELECT * FROM #__mymuse_order_item";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$query = "UPDATE #__mymuse_order_item
			SET product_id ='". $prodx[$r->product_id]  ."' WHERE
			id=".$r->id;
			$db->setQuery($query);
			$db->execute();
			$i++;
		}
		$i--;
		$this->msg .= "Update $i product numbers in $table<br />";
		
		return true;	
	}
	
	function orderPayment($dbprefix)
	{
		$db = JFactory::getDBO();
		$i = 1;
		$table = $dbprefix."_mymuse_order_payments";
		$query = "ALTER TABLE `$table` ADD `checked_out` INT( 11 ) NOT NULL ,
		ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		ADD `ordering` INT( 11 ) NOT NULL DEFAULT '0'
		";
		$db->setQuery($query);
		$db->execute();
		
		$query = "INSERT INTO `#__mymuse_order_payment`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		
		return true;
	}
	
	function orderShipping($dbprefix)
	{
		$db = JFactory::getDBO();
		$i = 1;
		$table = $dbprefix."_mymuse_order_shipping";
		$query = "ALTER TABLE `$table` ADD `checked_out` INT( 11 ) NOT NULL ,
		ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		ADD `ordering` INT( 11 ) NOT NULL DEFAULT '0'
		";
		$db->setQuery($query);
		$db->execute();

		
		$query = "INSERT INTO `#__mymuse_order_shipping`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		
		return true;
	
	}
	
	function productAttibuteSKU($dbprefix)
	{
		$db = JFactory::getDBO();
		$i = 1;
		
		$query = "SELECT id,newprodid from ".$dbprefix."_mymuse_product";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$prodx[$r->id] = $r->newprodid;
		}
		
		$table = $dbprefix."_mymuse_product_attribute_sku";
		$query = "INSERT INTO `#__mymuse_product_attribute_sku`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		
		$query = "SELECT * FROM #__mymuse_product_attribute_sku";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$query = "UPDATE #__mymuse_product_attribute_sku
			SET product_parent_id ='". $prodx[$r->product_parent_id]  ."' WHERE
			id=".$r->id;
			$db->setQuery($query);
			$db->execute();
			$i++;
		}
		$i--;
		$this->msg .= "Update $i product numbers in $table<br />";
		
		return true;
	}
	
	function productAttibute($dbprefix)
	{
		$db = JFactory::getDBO();
		$i = 1;
		$query = "SELECT id,newprodid from ".$dbprefix."_mymuse_product";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$prodx[$r->id] = $r->newprodid;
		}
		
		$table = $dbprefix."_mymuse_product_attribute";
		$query = "INSERT INTO `#__mymuse_product_attribute`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		
		$query = "SELECT * FROM #__mymuse_product_attribute";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$query = "UPDATE #__mymuse_product_attribute
			SET product_id ='". $prodx[$r->product_id]  ."' WHERE
			id=".$r->id;
			$db->setQuery($query);
			$db->execute();
			$i++;
		}
		$i--;
		$this->msg .= "Update $i product numbers in $table<br />";
		
		return true;
	}
	

	
	function store($dbprefix)
	{
		$i = 1;
		$helper 	= new MyMuseUpdateHelper;
		$this->msg 	= "<h2>Working on Store</h2>";
		$table 		= $dbprefix."_mymuse_store";
		$db 		= JFactory::getDBO();
		$query 		= "SELECT * FROM ".$dbprefix."_mymuse_store WHERE id=1";
		$db->setQuery($query);
		$s = $db->loadObject();
		$newparams 	=& MyMuseHelper::getParams();
		$profile_key 	= $newparams->get('my_profile_key', 'mymuse');
		
		



		$sp = explode("\n", $s->params);
		foreach($sp as $p){
			if($p){
				list($key,$val) = explode("=",$p);
				$op[$key] = $val;
			}
		}

		if($op['my_registration'] == 'noreg'){
			$op['my_registration'] = 'joomla';
		}
		
		/////////////////////////////////////////////////////////////////
		$params = Array
		(
				'contact_first_name' => $s->contact_first_name,
				'contact_last_name' => $s->contact_last_name,
				'contact_title' => $s->contact_title,
				'contact_email' => $s->contact_email,
				'phone' => $s->phone,
				'fax' => $s->fax,
				'address_1' => $s->address_1,
				'address_2' => $s->address_2,
				'city' => $s->city,
				'province' => $s->state,
				'country' => $s->country,
				'zip' => $s->zip,
				'currency' => $s->currency,
				'store_thumb_image' => $s->store_thumb_image,
				'my_downloads_enable' => $op['my_downloads_enable'],
				'my_download_max' => $op['my_download_max'],
				'my_download_expire' => $op['my_download_expire'],
				'my_download_enable_status' => $op['my_download_enable_status'],
				'my_download_dir' => $op['my_download_dir'],
				'my_preview_dir' => $op['my_preview_dir'],
				'my_encode_filenames' => $op['my_encode_filenames'],
				'my_free_downloads' => $op['my_free_downloads'],
				'my_use_shipping' => $op['my_use_shipping'],
				'my_use_stock' => $op['my_use_stock'],
				'my_check_stock' => $op['my_check_stock'],
				'my_add_stock_zero' => $op['my_add_stock_zero'],
				'my_saveorder' => $op['my_saveorder'],
				'my_currency_separator' => $op['my_currency_separator'],
				'my_currency_dec_point' => $op['my_currency_dec_point'],
				'my_currency_position' => $op['my_currency_position'],
				'my_registration' => $op['my_registration'],
				'my_profile_key' => $profile_key,
				'my_cc_webmaster' => $op['my_cc_webmaster'],
				'my_webmaster' => $op['my_webmaster'],
				'my_webmaster_name' => $op['my_webmaster_name'],
				'my_continue_shopping' => $op['my_continue_shopping'],
				'my_date_format' => $op['my_date_format'],
				'my_email_msg' => '',
				'my_ownergid' => '',
				'my_owner_percent' => '',
				'my_shop_test' => $op['my_shop_test'],
				'my_debug' => $op['my_debug'],
		);
		
		$registry = new JRegistry;
		$registry->loadArray($params);
		$new_params = (string)$registry;
		
		$query = "
		UPDATE `#__mymuse_store` SET 
`title`=".$db->quote($s->title).",
`alias`='".$s->alias."',
`scope`='store',
`description`=".$db->quote($s->description).",

`published`='1',
`checked_out`='0',
`checked_out_time`='',
`ordering`='1',
`access`='0',
`count`='0',
`params`=".$db->quote($new_params).",
`state`='1' 
WHERE `id`='1'";

		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Updated $table in new DB <br />";
		}else{
			$this->msg .= "$i. Problem updating $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		return true;
	}
	
	function shopperGroup($dbprefix)
	{
		$i = 1;
		$db = JFactory::getDBO();
		$table = $dbprefix."_mymuse_shopper_group";
		$query = "ALTER TABLE `$table` ADD `checked_out` INT( 11 ) NOT NULL ,
		ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		ADD `state` INT( 1 ) NOT NULL DEFAULT '1'
		";
		$db->setQuery($query);
		$db->execute();
		
		$query = "DELETE FROM `#__mymuse_shopper_group` WHERE 1";
		$db->setQuery($query);
		$db->execute();
		
		$query = "INSERT INTO `#__mymuse_shopper_group`
		SELECT *
		FROM `$table`";
		$db->setQuery($query);
		if($db->execute()){
			$this->msg .= "$i. Copied $table to new DB <br />";
		}else{
			$this->msg .= "$i. Problem copying $table <br />";
			$this->msg .= $db->getErrorMsg();
			return false;
		}
		
		return true;
	}
	
	
	function shopper($dbprefix)
	{
		$this->msg 	= "<h2>Working on Shoppers</h2>";
		$db = JFactory::getDBO();
		$table = $dbprefix."_mymuse_shopper";
		$newparams 	=& MyMuseHelper::getParams();
		$profile_key 	= $newparams->get('my_profile_key', 'mymuse');
		
		$limit = JRequest::getVar('limit','30');
		$limitstart = JRequest::getVar('limitstart','0');
		
		$i = $limitstart;
		$i++;
		
		$query = "SELECT a.*,u.email
		FROM $table as a 
		LEFT JOIN ".$dbprefix."_users as u ON u.id=a.user_id
		WHERE 1
		ORDER BY id asc LIMIT $limitstart , $limit
		";

		$db->setQuery($query);
		if(!$oldshoppers = $db->loadObjectList()){
			//we are all done shoppers
			$continue = JRequest::setVar('continue', 0);
			$this->msg  = "All done shoppers<br />";
			return true;
		}
		$fields = array(
					'address1' => 'address_1',
					'address2' => 'address_2',
					'city' => 'city',
					'region' => 'state',
					'country' => 'country',
					'postal_code' => 'zip',
					'phone' => 'phone_1',
					'mobile' => 'phone_2',
					'fax' => 'fax',
					'tos' => 'terms_of_service',
					'shopper_group' => 'shopper_group_id',
			);
		
		foreach($oldshoppers as $s){
			// get the new userid

			$query = "SELECT id from #__users WHERE email='".$s->email."'";
		
			$db->setQuery($query);
			if(!$userId = $db->loadResult()){
				$this->msg .= 'Could not find a new user for email '.$s->email." ".$s->last_name." ".$s->first_name."<br />";
				continue;
			}
			
			
			$data['profile'] = array();
			reset($fields);
			foreach($fields as $k => $v){
				$data['profile'][$profile_key.".".$k] = $s->{$v};
			}
			
			$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE '$profile_key.%'"
			);
			
			if (!$db->execute()) {
				$this->setError("Could not delete old data: ".$db->getErrorMsg());
				return false;
			}
			
			$tuples = array();
			$order	= 1;
			
			foreach ($data['profile'] as $k => $v)
			{
				$tuples[] = '('.$userId.', '.$db->quote("$profile_key.".$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
			}
			$query = 'INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples);
			
			$db->setQuery($query);
			
			if (!$db->execute()) {
				$this->setError("Could not insert data: ".$db->getErrorMsg());
				return false;
			}
			$this->msg .= "$i Inserted ".$s->email." into user_profile <br />";
			$i++;
		}
		
		
		
		$continue = JRequest::setVar('continue', 1);
		return true;

	}
	
	function coupons($dbprefix)
	{
		$table = $dbprefix."_mymuse_coupon";
		$db = JFactory::getDBO();
		$query = "SELECT id,newprodid from ".$dbprefix."_mymuse_product";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		foreach($res as $r){
			$prodx[$r->id] = $r->newprodid;
		}
	
		$user = JFactory::getUser();
		$userid = $user->get('id');
	
		$query = "SELECT * from $table";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		$i = 0;
		foreach($res as $r){
			$i++;
			$query = "INSERT INTO `#__mymuse_coupon` (`id`, `title`, `state`, `code`, `coupon_type`,
			`product_id`, `coupon_value`, `coupon_value_type`, `currency_id`, `description`,
			`params`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`,
			`checked_out`, `checked_out_time`, `start_date`, `expiration_date`, `published`,
			`coupon_uses`, `coupon_max_uses`, `coupon_max_uses_per_user`, `ordering`)
			VALUES
			(NULL,
			'".$r->title."',
			'0',
			'".$r->code."',
			'".$r->coupon_type."',
			'".$prodx[$r->product_id]."',
			'".$r->coupon_value."',
			'".$r->coupon_value_type."',
			'".$r->currency_id."',
			'".$r->description."',
			'',
			'".$r->created_date."',
			'".$userid."',
			'',
			'".$r->modified_date."',
			'',
			'',
			'0000-00-00 00:00:00',
			'".$r->start_date."',
			'".$r->expiration_date."',
			'".$r->published."',
			'".$r->coupon_uses."',
			'".$r->coupon_max_uses."',
			'".$r->coupon_max_uses_per_user."',
			'0')
			";
			$db->setQuery($query);
			if($db->execute()){
				$this->msg .= "$i. Made coupon ".$r->title." <br />";

			}else{
				$this->msg .= "$i. Problem with coupon ".$r->title." <br />";
				$this->msg .= $db->getErrorMsg();
				return false;
			}
		}
		return true;
	}
	

	

}