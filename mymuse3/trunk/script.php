<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of HelloWorld component
 */
class com_mymuseInstallerScript
{
	
	var $already_installed = 0;
	var $old_version = 0;
	var $css = '';
	
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		//$parent->getParent()->setRedirectURL('index.php?option=com_mymuse');
	}
 
	/**
	 * method to uninstall the component
	 * Executes additional uninstallation processes
	 * 
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		//initialize
		require_once (JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS.'helpers'.DS.'mymuse.php');
		$params =& MyMuseHelper::getParams();
		$extensions = array();
		
		//uninstall dirs
		function recursiveDelete($str){
			if(is_file($str)){
				return JFile::delete($str);
			}
			elseif(is_dir($str)){
				$scan = glob(rtrim($str,'/').'/*');
				foreach($scan as $index=>$path){
					recursiveDelete($path);
				}
				return JFile::delete($str);
			}
		}
		$i = 0;
		$dir =  $params->get('my_download_dir');
		
		//specifically remove .htaccess
		if(file_exists($dir.DS.".htaccess")){
			$extensions[$i]['msg'] = '';
			$extensions[$i]['name'] = "Remove .htaccess";
			$extensions[$i]['type'] = "File in download Dir";
			$extensions[$i]['status'] = 0;
			if(JFile::delete($dir.DS.".htaccess"))
			{
				$extensions[$i]['status'] = 1;
			}else{
				$extensions[$i]['msg'] = '';
			}
			
			$i++;
		}
		
		
		$extensions[$i]['name'] = "Remove Previews";
		$extensions[$i]['type'] = "Directory";
		$extensions[$i]['status'] = 0;
		$extensions[$i]['msg'] = '';
		$dir =  JPATH_ROOT.DS.$params->get('my_preview_dir');
		if(stristr(PHP_OS, 'win')){
			$dir = str_replace("/", "\\", $dir);
		}
		if(recursiveDelete($dir))
		{
			$extensions[$i]['status'] = 1;
		}else{
			$extensions[$i]['msg'] = " $dir";
		}
		$i++;
		
		$extensions[$i]['name'] = "Remove Downloads";
		$extensions[$i]['type'] = "Directory";
		$extensions[$i]['status'] = 0;
		$extensions[$i]['msg'] = '';
		$dir =  $params->get('my_download_dir');
		if(recursiveDelete($dir))
		{
			$extensions[$i]['status'] = 1;
		}else{
			$extensions[$i]['msg'] = " $dir";;
		}
		$i++;
		
		$extensions[$i]['name'] = "Remove Graphics";
		$extensions[$i]['type'] = "Directory";
		$extensions[$i]['status'] = 0;
		$extensions[$i]['msg'] = '';
		$dir =  JPATH_ROOT.DS."images".DS."A_MyMuseImages";
		
		if(recursiveDelete($dir))
		{
			$extensions[$i]['status'] = 1;
		}else{
			$extensions[$i]['msg'] = " $dir";
		}
		$i++;
		
		
		?>
		<h3><?php echo JText::_('Remove Directories'); ?></h3>
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title"><?php echo JText::_('Extension'); ?></th>
					<th width="60%"><?php echo JText::_('Status'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($extensions as $i => $ext) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="key"><?php echo $ext['name']; ?> (<?php echo JText::_($ext['type']); ?>)</td>
						<td>
							<?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
							<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Uninstalled successfully') : JText::_('Uninstall FAILED'); ?>
							<?php echo $ext['msg'] ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php 

	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		//echo '<p>' . JText::sprintf('UPDATE %s', $parent->get('manifest')->version) . '</p>';
		$thisextension = strtolower( "com_mymuse" );
		$thisextensionname = substr ( $thisextension, 4 );

	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		//echo '<p>' . JText::_('PREFLIGHT_' . $type . '_TEXT') . '</p>';
		$db = JFactory::getDBO();
		$query = "SELECT * from #__extensions WHERE name = 'mymuse' ";
		
		$db->setQuery($query);
		if($res = $db->loadObject()){
				$this->already_installed = 1;
				$manifest = json_decode($res->manifest_cache);
				$this->old_version = $manifest ->version;
				// get the current css file
				$this->css = file_get_contents(JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'assets'.DS.'css'.DS.'mymuse.css');
				
		}
		$parent->already_installed = $this->already_installed;
		$parent->old_version = $this->old_version;
		//echo "this->already_installed = ".$this->already_installed."<br />";
		//echo "this->old_version = ".$this->old_version."<br />";
		
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)

		//update store table
		$db = JFactory::getDBO();
		$actions = array();
		if(!$this->already_installed && $type == "install"){
			
			$download_dir =  JPATH_ROOT.DS."images".DS."A_MyMuseDownloads";
			$name = JText::_("MYMUSE_UPDATING_STORE");
			$query = "SELECT params FROM #__mymuse_store WHERE id='1'";
			$db->setQuery($query);
			$store_params = json_decode($db->loadResult(), TRUE);
			if($store_params){
				$store_params['my_download_dir'] = $db->escaped($download_dir);
				$registry = new JRegistry;
				$registry->loadArray($store_params);
				$new_params = (string)$registry;
				

				$query = "UPDATE #__mymuse_store set ";
				$query .= "params='$new_params' WHERE id=1
				";

				$db->setQuery($query);
				if(!$db->execute()){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message =  JText::_("MYMUSE_PROBLEM_UPDATING_STORE").$db->_errorMsg;
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message =  JText::_("MYMUSE_STORE_UPDATED");
				}

			}else{
				if(function_exists(json_last_error)){
					switch (json_last_error()) {
						case JSON_ERROR_NONE:
							$message = 'JSON - No errors';
							$astatus = 1;
							break;
						case JSON_ERROR_DEPTH:
							$message = 'JSON - Maximum stack depth exceeded';
							$astatus = 0;
							break;
						case JSON_ERROR_STATE_MISMATCH:
							$message = 'JSON - Underflow or the modes mismatch';
							$astatus = 0;
							break;
						case JSON_ERROR_CTRL_CHAR:
							$message = 'JSON - Unexpected control character found';
							$astatus = 0;
							break;
						case JSON_ERROR_SYNTAX:
							$message = 'JSON - Syntax error, malformed JSON';
							$astatus = 0;
							break;
						case JSON_ERROR_UTF8:
							$message = 'JSON - Malformed UTF-8 characters, possibly incorrectly encoded';
							$astatus = 0;
							break;
						default:
							$message = 'JSON - Unknown error';
							$astatus = 0;
							break;
					}
				}
			}
				
			$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
			
			//UPDATE PLUGINS
			$name = JText::_("MYMUSE_ENABLE_PLUGINS");
			$query = "UPDATE #__extensions SET enabled=1 WHERE
			element='paymentoffline' OR
			element='shipping_standard' OR
			element='audio_html5' OR
			element='vidplayer_html5' OR
			element='paymentpaypal' OR
			element='search_mymuse'
			";
			$db->setQuery($query);
			if(!$db->execute()){
				$alt = JText::_( "MYMUSE_FAILED" );
				$astatus = 0;
				$message =  JText::_("MYMUSE_ENABLE_PLUGINS_FAILED");
			}else{
				$alt = JText::_( "MYMUSE_INSTALLED" );
				$astatus = 1;
				$message =  JText::_("MYMUSE_ENABLE_PLUGINS_SUCCESS");
			}
			$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
			
		}else{
			//restore the css file
			$name = JText::_("MYMUSE_SAVE_CSS");
			$myFile = JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'assets'.DS.'css'.DS.'mymuse.css';
			if($this->css != ""){
				if(!JFILE::write($myFile, $this->css)){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message =  JText::_("MYMUSE_SAVE_CSS_FAILED");
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message =  JText::_("MYMUSE_SAVE_CSS_SUCCESS");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
			}
			
			//see if we need to update the database
			//ALTER TABLE `#__mymuse_product` ADD `file_time` varchar(32) NOT NULL AFTER `file_length`
			$good = 0;
			$name = JText::_("MYMUSE_DB_UPDATED");
			$query = "SHOW COLUMNS FROM `#__mymuse_product` ";
			$db->setQuery($query);
			$fields = $db->loadResultArray();
			while(list($key,$val) = each($fields)){
				if(preg_match("/file_time/",$val)){
					$good = 1;
				}
			}
			if(!$good){
				$query = "ALTER TABLE `#__mymuse_product` ADD `file_time` varchar(32) NOT NULL AFTER `file_length`";
				$db->setQuery($query);
				if($db->execute()){
					$alt = JText::_( "MYMUSE_INSTALLED" );;
					$astatus = 1;
					$message = JText::_("MYMUSE_DB_UPDATED_DESC");
					$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
				}
			}
			
			$name = "Update Order Table";
			$query = "ALTER TABLE `#__mymuse_order` CHANGE `coupon_discount` `coupon_discount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'";
			$db->setQuery($query);
			if($db->execute()){
				$alt = JText::_( "MYMUSE_INSTALLED" );;
				$astatus = 1;
				$message = JText::_("Update coupon_discount");
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
			}
		}
		
		if(count($actions)){
		?>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('Post Install Actions'); ?></th>
			<th width="60%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</tfoot>
	<tbody>
	<?php 
		$i = 0;
			foreach ($actions as $ext) : ?>
					<tr class="row<?php echo $i % 2; $i++; ?>">
						<td class="key"><?php echo $ext['name']; ?> (<?php echo JText::_($ext['message']); ?>)</td>
						<td>
							<?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
							<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Success') : JText::_('NOT Successful'); ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
		
			</tbody>
		</table>
	<?php 
		}
	}
}
