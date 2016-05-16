<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Mymuse helper.
 */
class MyMuseUpdateHelper extends JObject
{
	
	/**
	 * var error
	 */
	
	var $error = '';
	
	
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		if(!defined('DS')){
			define('DS',DIRECTORY_SEPARATOR);
		}
	}
	
	
	/**
	 * makeCategory
	 *
	 * @param str $title
	 * @param str $parent_id
	 * @param str $description
	 * @param str $image
	 *
	 * @return mixed Catid on success, false on failure
	 */
	function makeCategory($title='', $parent_id=1,$description='',$image='', $alias = '')
	{
		
		$db = JFactory::getDBO();
		$url = JURI::base();
		if(!preg_match('/administrator/', $url)){
			$url .= 'administrator/';
		}
		
		$url .= "index.php";
	
		$token = JSession::getFormToken();
		$cookie = session_name()."=".session_id();
	
		$query = "SELECT id from #__categories WHERE title = ".$db->quote($title);
		$db->setQuery($query);
	
		if($res = $db->loadResult()){
			return $res;
		}
	
		$title = urlencode($title);
		$description = urlencode($description);
		$image = urlencode($image);

		if(!$title){
			$this->error="Missing Title";
			return false;
		}
		$str = "jform[title]=$title&jform[alias]=$alias&jform[extension]=com_mymuse";
		$str .= "&jform[parent_id]=$parent_id&jform[published]=1&jform[access]=1&jform[language]=*&jform[id]=0";
		$str .= "&jform[description]=$description&jform[created_user_id]=59&jform[note]=&jform[metadesc]=";
		$str .= "&jform[metakey]=&jform[params][category_layout]=&jform[params][image]=$image";
		$str .= "&jform[metadata][author]=&jform[metadata][robots]=&task=category.save&option=com_categories";
		$str .= "&$token=1&extension=com_mymuse";
		$str = preg_replace("/ /","+",$str);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_COOKIE, 		$cookie );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
	
		curl_setopt($ch, CURLOPT_HEADER, true);    // return headers
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);     // follow redirects
		curl_setopt($ch, CURLOPT_ENCODING,"");       // handle all encodings
		curl_setopt($ch, CURLOPT_USERAGENT, "spider"); // who am i
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);     // set referer on redirect
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);      // timeout on connect
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);      // timeout on response
		curl_setopt($ch, CURLOPT_MAXREDIRS , 10);       // stop after 10 redirects
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		// return web page
		$result=curl_exec ($ch);
		curl_close($ch);
	
		$query = "SELECT id from #__categories WHERE title = ".$db->quote(urldecode($title));
		//echo "<br />$query<br />";
		$db->setQuery($query);
	
		if($res = $db->loadResult()){
			return $res;
		}else{
			echo $result; exit;
			$this->error = "Could not create category. ".$result;
			return false;
		}
	}
	
	/**
	 * upgradeProduct
	 *
	 * @param object $p the old product object
	 *
	 * @return mixed productid on success, false on failure
	 */
	
	function upgradeProduct($p)
	{
		
		//Add a main product
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$token = JSession::getFormToken();
		$query = "DELETE from #__mymuse_product WHERE product_sku = ".$db->quote($p->product_sku);
		$db->setQuery($query);
		$db->execute();
		$user = JFactory::getUser();
		$userid = $user->get('id');
	if($p->parentid){
		//print_pre($p); print_pre($_FILES); 
	}
		
		require_once (JPATH_COMPONENT.DS.'controllers'.DS.'product.php');
	
		$controller	= new MymuseControllerProduct(array(
				'base_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/",
				'model_prefix' => 'MymuseModel',
				'model_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/models",
				'table_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/tables"
		)
		);
	
		if($p->image != ""){
			$p->image = 'images/A_MyMuseImages/'.$p->image;
		}
		if($p->images != ""){
			$p->images = 'images/A_MyMuseImages/'.$p->images;
		}
	
		$model = $controller->getModel('product', 'MymuseModel',
				array('table_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/tables"));
		$data = Array(
				'jform' => Array
				(
						'id' => '0',
						'title' => $p->title,
						'alias' => $p->alias,
						'title_alias' => $p->title_alias,
						'articletext' => $p->articletext,
						'state' => $p->state,
						'artistid' => $p->artistid,
						'catid' => $p->catid,
						'price' => $p->price,
						'created_by' => $userid,
						'created_by_alias' => '',
						'created' => '',
						'publish_up' => $p->publish_up,
						'publish_down' => $p->publish_down,
						'list_image' => $p->image,
						'detail_image' => $p->images,
						'urls' => $p->urls,
						'attribs' => Array
						(
								'product_made_date' => $p->product_made_date,
								'product_in_stock' => $p->product_in_stock,
						),
						'version' => $p->version,
						'parentid' => $p->parentid,
						'ordering' => $p->ordering,
						'metakey' => $p->metakey,
						'metadesc' => $p->metadesc,
						'access' => '1',
						'hits' => $p->hits,
						'metadata' => Array
						(
								'robots' => '',
								'author' => '',
								'rights' => '',
								'xreference' => ''
						),
						'product_physical' => $p->product_physical,
						'product_downloadable' => $p->product_downloadable,
						'product_allfiles' => $p->product_allfiles,
						'product_sku' => $p->product_sku,
						'product_made_date' => $p->product_made_date,
						'product_in_stock' => $p->product_in_stock,
						'product_special' => $p->product_special,
						'product_discount' => $p->product_discount,
						'reservation_fee' => $p->reservation_fee,
						'file_length' => $p->file_length,
						'file_name' => $p->file_name,
						'file_downloads' => $p->file_downloads,
						'file_contents' => $p->file_contents,
						'file_type' => $p->file_type,
						'file_preview' => $p->file_preview,
						'file-time' => $p->file_time,
						'featured' => $p->product_special,
						'language' => '*',
						'othercats' => $p->othercats,
				),
	
				'task' => 'product.save',
				'subtype' => 'details',
				'return' => '',
				$token => 1,
				'option' => 'com_mymuse',
				'file_preview' => $p->file_preview,
				'upgrade' => 1
	
		);
		$mime['wav'] = "audio/wav";
		$mime['mp3'] = "audio/mpeg";
		if(isset($_FILES['product_file'])){
			$ext = pathinfo($_FILES['product_file']['tmp_name'], PATHINFO_EXTENSION);
			$data["product_file"] = new CURLFile($_FILES['product_file']['tmp_name'], $mime[$ext], $_FILES['product_file']['name']);
		}
		if(isset($_FILES['product_preview'])){
			$ext = pathinfo($_FILES['product_preview']['tmp_name'], PATHINFO_EXTENSION);
			//$data["product_preview"] = new CURLFile($_FILES['product_preview']['tmp_name'],$mime[$ext], $_FILES['product_preview']['name']);
		}
		//print_pre($data);

		$str = http_build_query($data);
		$url = JURI::base()."index.php";
		$cookie = session_name()."=".session_id();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_COOKIE, 		$cookie );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
		
		curl_setopt($ch, CURLOPT_HEADER, true);    // return headers
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);     // follow redirects
		curl_setopt($ch, CURLOPT_ENCODING,"");       // handle all encodings
		curl_setopt($ch, CURLOPT_USERAGENT, "spider"); // who am i
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);     // set referer on redirect
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);      // timeout on connect
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);      // timeout on response
		curl_setopt($ch, CURLOPT_MAXREDIRS , 10);       // stop after 10 redirects
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		// return web page
		$result=curl_exec ($ch);
		curl_close($ch);
		
		//see if it worked
		$query = "SELECT id from #__mymuse_product WHERE title = ".$db->quote($p->title);
		//echo "<br />$query<br />";
		$db->setQuery($query);
		
		if($res = $db->loadResult()){
			return $res;
		}else{
			echo $result; exit;
			$this->error = "Could not update product. ".$result;
			return false;
		}
		

	}

	/**
	 * makeProduct
	 *
	 * @param array $p the product form input
	 *
	 * @return mixed productid on success, false on failure
	 */
	
	function makeProduct($data)
	{
	
		//Add a main product
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$token = JSession::getFormToken();
	
		//see if it exists
		$query = "SELECT id from #__mymuse_product WHERE title = ".$db->quote($data['jform']['title']);
	
		$db->setQuery($query);
		$res = $db->loadResult();
	
		if($res > 0){
			return $this->upgradeProduct($data);
		}
	
		/**
			$user = JFactory::getUser();
			$userid = $user->get('id');
	
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS.'controllers'.DS.'product.php');
	
			$controller	= new MymuseControllerProduct(array(
			'base_path' => JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS,
			'model_prefix' => 'MymuseModel',
			'model_path' => JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS.'models',
			'table_path' => JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mymuse'.DS.'tables'
			)
			);
			*/
	
	
		$str = http_build_query($data);
		$url = JURI::base()."index.php";
		$cookie = session_name()."=".session_id();
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_COOKIE, 		$cookie );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
	
		curl_setopt($ch, CURLOPT_HEADER, true);    // return headers
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);     // follow redirects
		curl_setopt($ch, CURLOPT_ENCODING,"");       // handle all encodings
		curl_setopt($ch, CURLOPT_USERAGENT, "spider"); // who am i
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);     // set referer on redirect
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);      // timeout on connect
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);      // timeout on response
		curl_setopt($ch, CURLOPT_MAXREDIRS , 1);       // stop after 10 redirects
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		// return web page
		$result=curl_exec ($ch);
		curl_close($ch);
	
		//see if it worked
		$query = "SELECT id from #__mymuse_product WHERE title = ".$db->quote($data['jform']['title']);
	
		$db->setQuery($query);
		$res = $db->loadResult();
	
		if($res > 0){
			return $res;
		}else{
	
			$this->error = "Could not create product. ".$result;
			echo $result;  exit;
			return false;
		}
	
	
	}
	
	
	/**
	 * makeProduct
	 *
	 * @param object $p the product form input
	 *
	 * @return mixed productid on success, false on failure
	 */
	
function makeProductObject($p)
	{

		$db = JFactory::getDBO();
		//Add a main product
		$token = JSession::getFormToken();
		$query = "DELETE from #__mymuse_product WHERE product_sku = ".$db->quote($p->product_sku);

		$db->setQuery($query);
		$db->query();
		$user = JFactory::getUser();
		$userid = $user->get('id');
	

		require_once (JPATH_COMPONENT.DS.'controllers'.DS.'product.php');
	
		$controller	= new MymuseControllerProduct(array(
				'base_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/",
				'model_prefix' => 'MymuseModel',
				'model_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/models",
				'table_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/tables"
		)
		);
	
		if($p->image != ""){
			$p->image = 'images/A_MyMuseImages/'.$p->image;
		}
		if($p->images != ""){
			$p->images = 'images/A_MyMuseImages/'.$p->images;
		}
	
		$model = $controller->getModel('product', 'MymuseModel',
				array('table_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/tables"));
		$_POST = Array(
				'jform' => Array
				(
						'id' => $p->id,
						'title' => $p->title,
						'alias' => $p->alias,
						'title_alias' => $p->title_alias,
						'articletext' => $p->articletext,
						'state' => $p->state,
						'artistid' => $p->artistid,
						'catid' => $p->catid,
						'price' => $p->price,
						'created_by' => $userid,
						'created_by_alias' => '',
						'created' => '',
						'publish_up' => $p->publish_up,
						'publish_down' => $p->publish_down,
						'list_image' => $p->image,
						'detail_image' => $p->images,
						'urls' => $p->urls,
						'attribs' => Array
						(
								'product_made_date' => $p->product_made_date,
								'product_in_stock' => $p->product_in_stock,
						),
						'version' => $p->version,
						'parentid' => $p->parentid,
						'ordering' => $p->ordering,
						'metakey' => $p->metakey,
						'metadesc' => $p->metadesc,
						'access' => '1',
						'hits' => $p->hits,
						'metadata' => Array
						(
								'robots' => '',
								'author' => '',
								'rights' => '',
								'xreference' => ''
						),
						'product_physical' => $p->product_physical,
						'product_downloadable' => $p->product_downloadable,
						'product_allfiles' => $p->product_allfiles,
						'product_sku' => $p->product_sku,
						'product_made_date' => $p->product_made_date,
						'product_in_stock' => $p->product_in_stock,
						'product_special' => $p->product_special,
						'product_discount' => $p->product_discount,
						'reservation_fee' => $p->reservation_fee,
						'file_length' => $p->file_length,
						'file_name' => $p->file_name,
						'file_downloads' => $p->file_downloads,
						'file_contents' => $p->file_contents,
						'file_type' => $p->file_type,
						'file_preview' => $p->file_preview,
						'file_time' => $p->file_time,
						'featured' => $p->product_special,
						'language' => '*',
						'othercats' => $p->othercats,
				),
	
				'task' => 'product.apply',
				'subtype' => 'file',
				'return' => '',
				$token => 1,
				'option' => 'com_mymuse',
				'file_preview' => $p->file_preview,
				'upgrade' => 1
	
		);
		$jform = $_POST['jform'];
		JRequest::setVar('jform',$jform);

		if(!$model->save($jform)){
			$this->error = $model->getError();
			echo $this->error; exit;
			return false;
		}
	
		$query = "SELECT id from #__mymuse_product WHERE title = ".$db->quote($p->title);
		$db->setQuery($query);
		if($id = $db->loadResult()){
			return $id;
		}else{
			$this->error =  "Could not make product ".print_pre($_POST,true);
			return false;
		}
	}
	
	function get_data($url, $file)
	{
		$params = MyMuseHelper::getParams();
		$ch = curl_init();
	
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
		$data = curl_exec($ch);
		curl_close($ch);

		
		
		if(!$data){
			$this->error = "Could not get $url";
			return false;
		}
		if($params->get('my_use_s3') && preg_match("/mp3/",$file)){
			// write it to a tmp file
			$jconfig = JFactory::getConfig();
			$tmpName = $jconfig->get('tmp_path','').DS.'mytmp'.time();
			file_put_contents($tmpName,$data);
			
			require_once (JPATH_COMPONENT.DS.'controllers'.DS.'product.php');
			$controller	= new MymuseControllerProduct(array(
					'base_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/",
					'model_prefix' => 'MymuseModel',
					'model_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/models",
					'table_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/tables"
			)
			);
			$model = $controller->getModel('product', 'MymuseModel',
					array('table_path' => JPATH_ADMINISTRATOR."/components/com_mymuse/tables"));
			$table = $model->getTable();
		
			$table->fileUpload($tmpName, $file);
		}else{
			if(!JFILE::write($file, $data)){
				$this->error = "Could not write file";
				return false;
			}
		}
		return true;
	}
	
	function addSampleData()
	{

		$application = JFactory::getApplication();
		$msg =  "addSampleData <br />";
		if(!function_exists('curl_init')){
			$this->error = "Sorry, we need the cURL library to get sample data.";
			JFactory::getApplication()->enqueueMessage($this->error, 'error');
			return false;
		}
		$db = JFactory::getDBO();
		$url = JURI::base()."index.php";
		$token = JSession::getFormToken();
		$cookie = session_name()."=".session_id();
		$params = MyMuseHelper::getParams();
	
	
		// add a top category
		$title = 'MyMuse';
		$parent = '1';
		$description = 'Top Level Sample MyMuse Category';
		$image = 'mymuse.jpg';
		if(!$topcatid = $this->makeCategory($title,$parent,$description,$image)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Created category MyMuse<br />";
	
	
		// add an artist category
		$title = 'Artists';
		$parent = $topcatid;
		$description = 'All Artist Unite!';
		$image = 'artists.png';
		if(!$artcatid = $this->makeCategory($title,$parent,$description,$image)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Created category Artists<br />";
	
		// add a genre category
		$title = 'Genres';
		$parent = $topcatid;
		$description = 'Genres make the world go round. Can I be in BOTH genres?';
		$image = 'genres.png';
		if(!$genreid = $this->makeCategory($title,$parent,$description,$image)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Created category Genres<br />";
	
		// add an artist category called Iron Brew
		$title = 'Iron Brew';
		$parent = $artcatid;
		$description = 'Iron Brew. Warning: Celtic Nuts.';
		$image = 'ironbrew.jpg';
		if(!$ironbrewid = $this->makeCategory($title,$parent,$description,$image)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Created category Artists->Iron Brew<br />";
	
		// add a genre category called World Beat
		$title = 'World Beat';
		$parent = $genreid;
		$description = 'And the beat goes on.';
		$image = 'worldbeat.jpg';
		if(!$worldbeatid = $this->makeCategory($title,$parent,$description,$image)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Created category Genres->World Beat<br />";
	
	
	
		//Add a main product
		$data = Array(
				'jform' => Array
				(
						'title' => 'Are You My Sister',
						'alias' => '',
						'catid' => "$ironbrewid",
						'product_sku' => "mm001$artcatid",
						'product_physical' => '1',
						'price' => '20.00',
						'state' => '1',
						'access' => '1',
						'featured' => '0',
						'language' => '*',
						'id' => '0',
						'articletext' => '
	
						The great first album
						',
						'attribs' => Array
						(
								'product_made_date' => '2012-05-25 23:43:40',
								'product_in_stock' => ''
						),
	
						'created_by' => '0',
						'created_by_alias' => '',
						'created' => '',
						'publish_up' => '',
						'publish_down' => '',
						'metadata' => Array
						(
								'robots' => '',
								'author' => '',
								'rights' => '',
								'xreference' => ''
						),
	
						'list_image' => 'images/A_MyMuseImages/sister.jpg',
						'detail_image' => 'images/A_MyMuseImages/sister.jpg',
						'version' => '0',
						'othercats' => array('0' => "$worldbeatid")
				),
	
				'task' => 'product.apply',
				'subtype' => 'details',
				'return' => '',
				$token => 1,
				'option' => 'com_mymuse',
	
		);

		if($parentid = $this->makeProduct($data)){
			$msg .= "Created product 'Are You My Sister'<br />";
		}else{
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		
		$artist_alias = "iron-brew";
		$album_alias = "are-you-my-sister";
	
	
		//get some files
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/mymuse.jpg";
		$to = JPATH_ROOT.DS."images".DS."A_MyMuseImages".DS."mymuse.jpg";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/worldbeat.jpg";
		$to = JPATH_ROOT.DS."images".DS."A_MyMuseImages".DS."worldbeat.jpg";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/ironbrew.jpg";
		$to = JPATH_ROOT.DS."images".DS."A_MyMuseImages".DS."ironbrew.jpg";
		if(!$this->get_data($from, $to)){
			echo $this->error;
			$application->enqueueMessage($this->error, 'error');
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/sister.jpg";
		$to = JPATH_ROOT.DS."images".DS."A_MyMuseImages".DS."sister.jpg";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/artists.png";
		$to = JPATH_ROOT.DS."images".DS."A_MyMuseImages".DS."artists.png";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/genres.png";
		$to = JPATH_ROOT.DS."images".DS."A_MyMuseImages".DS."genres.png";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Downloaded some graphics to /images/A_MyMuseImages<br />";
	
	
		// get some mp3's
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/Are_You_My_Sister.mp3";
		$to = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS."Are_You_My_Sister.mp3";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/sister-preview.mp3";
		$to = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS."sister-preview.mp3";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/sister-preview.ogg";
		$to = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS."sister-preview.ogg";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			//return false;
		}
		$msg .= "Downloaded 'Are You My Sister' track and previews<br />";
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/The_Foggy_Dew.mp3";
		$to = $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias.DS."The_Foggy_Dew.mp3";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			exit;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/foggy-preview.mp3";
		$to = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS."foggy-preview.mp3";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
	
		$from = "http://www.joomlamymuse.com/mysoftware/mymuse-downloads/foggy-preview.ogg";
		$to = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS."foggy-preview.ogg";
		if(!$this->get_data($from, $to)){
			$application->enqueueMessage($this->error, 'error');
			echo $this->error;
			return false;
		}
		$msg .= "Downloaded 'Foggy Dew' track and previews<br />";
	
		//make a track for Are You My Sister
		$preview_dir = ($params->get('my_use_s3')? '' : JPATH_ROOT.DS) .$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias;
		$data = Array(
	
			'jform' => Array
			(
					'title' => 'Are You My Sister Song',
					'file_type' =>'audio',
					'title_alias' => 'Are_You_My_Sister.mp3',
					'product_sku' => 'ayms-t1',
					'ordering' => '0',
					'file_name' => '',
					'file_length' => '',
					'file_downloads' => '',
					'price' => '2.00',
					'state' => '1',
					'access' => '1',
					'featured' => '0',
					'language' => '*',
					'id' => '0',
					'file_preview' => 'sister-preview.mp3',
					'file_preview_2' => 'sister-preview.ogg',
					'file_preview_3' => '',
					'articletext' =>'<p>This file is mp3 for download with two previews, one in mp3 and one in ogg</p>',
					'parentid' => "$parentid",
					'catid' => "$ironbrewid",
					'version' => '',
					'product_downloadable' => '1',
			),
	
			'select_file' => 'Are_You_My_Sister.mp3',
			'download_dir' => $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias,
				'preview_dir' => $preview_dir,
				'file_preview' => 'sister-preview.mp3',
				'file_preview_2' => 'sister-preview.ogg',
				'file_preview_3' => '',
				'parentid' => "$parentid",
				'current_preview' => '',
				'current_preview_2' => '',
				'current_preview_3' => '',
				'current_title_alias' => '',
				'view' => 'product',
				'id' => '',
				'subtype' => 'file',
				'option' => 'com_mymuse',
				'task' => 'product.applyfile',
				$token => '1'
			);
		if($track1id = $this->makeProduct($data)){
			$msg .= "Created a track 'Are You My Sister' :: Trackid = $track1id.<br />";
		}else{
			echo "Could not create a sample track. <br />";
			$application->enqueueMessage("Could not create a sample track. <br />".$this->error, 'error');
			echo $this->error;
			return false;
		}
	
		// make a track for Foggy Dew
		$data = Array(

				'jform' => Array
				(
						'title' => 'The Foggy Dew',
						'file_type' =>'audio',
						'title_alias' => '',
						'product_sku' => 'ayms-t2',
						'ordering' => '0',
						'file_name' => '',
						'file_length' => '',
						'file_downloads' => '',
						'price' => '2.00',
						'state' => '1',
						'access' => '1',
						'featured' => '0',
						'language' => '*',
						'id' => '0',
						'file_preview' => 'foggy-preview.mp3',
						'file_preview_2' => 'foggy-preview.ogg',
						'file_preview_3' => '',
						'articletext' => '

						This file is mp3 for download with two previews, one in mp3 and one in ogg
						',
						'parentid' => "$parentid",
						'catid' => "$ironbrewid",
						'version' => '',
						'product_downloadable' => '1',
				),

				'select_file' => 'The_Foggy_Dew.mp3',
				'download_dir' => $params->get('my_download_dir').DS.$artist_alias.DS.$album_alias,
				'preview_dir' => $preview_dir,
				'file_preview' => 'foggy-preview.mp3',
			'file_preview_2' => 'foggy-preview.ogg',
			'file_preview_3' => '',
			'parentid' => "$parentid",
			'current_preview' => '',
			'current_preview_2' => '',
			'current_preview_3' => '',
			'current_title_alias' => '',
			'view' => 'product',
			'id' => '',
			'subtype' => 'file',
			'option' => 'com_mymuse',
			'task' => 'product.applyfile',
			$token => '1'
		);

	
		if($track2id = $this->makeProduct($data)){
			$msg .= "Created a track 'Foggy Dew' :: Trackid = $track2id.<br />";
		}else{
			$application->enqueueMessage("Could not create a sample track. <br />".$this->error, 'error');
			echo $this->error;
			return false;
		}
	
		//checkin
		$query = "UPDATE #__mymuse_product set checked_out=0, checked_out_time='0000-00-00 00:00:00 '";
		$db->setQuery($query);
		$db->query();
	

		$msg .= '<a href="index.php?option=com_mymuse&view=products">Return</a> to Product View';
		$application->enqueueMessage($msg);
		return true;
	}
	
	
}