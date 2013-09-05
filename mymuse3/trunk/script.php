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
 
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

/**
 * Script file of MyMuse component
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
		// $parent->getParent()->setRedirectURL('index.php?option=com_mymuse');
		// first check if PHP5 is running
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
		
			echo '<div class="fc-error">';
			echo 'Please upgrade PHP above version 5.0.0<br />';
			echo '</div>';
			return false;
		}
		

		return true;
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
		
		if($type == "install" || $type == "update"){
			// init vars
			$error = false;
			$extensions = array();
		
			// reseting post installation session variables
			$session  =& JFactory::getSession();
			$session->set('mymuse.postinstall', false);
			$session->set('mymuse.allplgpublish', false);
		
			// additional extensions
			//first plug-ins
			$manifest =& $parent->get('manifest');

			$add = NULL;
			if(count($manifest->extension->plugins)){
			
				$super = $parent->getParent();
				foreach ($manifest->extension->plugins[0]->plugin as $plugin) {
					print_r($plugin);
					$extensions[] = array(
							'name' => (string) $plugin,
							'type' => (string) $plugin['name'],
							'folder' => $super->getPath('source').'/'.(string) $plugin['folder'],
							'installer' => new JInstaller(),
							'status' => false);
				}
			}
	print_r($extensions); exit;
			// install additional extensions
			for ($i = 0; $i < count($extensions); $i++) {
				$extension =& $extensions[$i];
				$extension['installer']->setOverwrite(true);
				if ($extension['installer']->install($extension['folder'])) {
					$extension['status'] = true;
				} else {
					echo $extension['name']. "threw an error, possibly already installed"; exit;
					break;
				}
			}
		
			// rollback on installation errors
			if ($error) {
				$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Error'), 'component');
				for ($i = 0; $i < count($extensions); $i++) {
					if ($extensions[$i]['status']) {
						$extensions[$i]['installer']->abort(JText::_($extensions[$i]['type']).' '.JText::_('Install').': '.JText::_('Error'), $extensions[$i]['type']);
						$extensions[$i]['status'] = false;
					}
				}
			}
		
			?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%"
			class="adminlist">
			<tr>
				<td valign="top"><img
					src="<?php echo 'components/com_mymuse/assets/images/logo325.jpg'; ?>"
					height="325" width="190" alt="MyMuse Logo" align="left" />
				</td>
				<td valign="top" width="100%"><strong>MyMuse</strong><br /> <span>MyMuse
						for Joomla! 3.5</span><br /> <font class="small">by <a
						href="http://www.arboreta.ca" target="_blank">Arboreta.ca</a>
				</font><br /> To get started
					<ol>
						<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_STORE');?></li>
						<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE');?> <a
							href="index.php?option=com_plugins&view=plugins&filter_folder=mymuse">Plugins</a>
						</li>
						<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_CREATE_CATEGORY');?>
						</li>
						<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_USER_PROFILE');?>
						</li>
					</ol>
				</td>
			</tr>
		</table>
		<h3>
			<?php echo JText::_('Additional Extensions'); ?>
		</h3>
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
					<td><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
						<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Installed successfully') : JText::_('NOT Installed'); ?>
					</span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<h3>
			<?php echo JText::_('Actions'); ?>
		</h3>
		
		
		
		
		
				<?php
		
				// DEFAULT DOWNLOAD DIRECTORY
				$name = JText::_("MYMUSE_MAKE_DOWNLOAD_DIR");
				$download_dir =  JPATH_ROOT.DS."images".DS."A_MyMuseDownloads";
				if(!file_exists($download_dir)){
					if(!JFolder::create($download_dir)){
						$alt = JText::_( "MYMUSE_FAILED" );
						$astatus = 0;
						$message = JText::_("MYMUSE_COULD_NOT_MAKE_DIR")."<br />$download_dir";
					}else{
						$alt = JText::_( "MYMUSE_INSTALLED" );
						$astatus = 1;
						$message = JText::_("MYMUSE_DIR_CREATED")." ".$download_dir;
					}
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message = JText::_("MYMUSE_DIR_EXISTS");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
				// DEFAULT PREVIEW DIRECTORY
				$name = JText::_("MYMUSE_MAKE_PREVIEW_DIR");
				$preview_dir =  JPATH_ROOT.DS."images".DS."A_MyMusePreviews";
				if(!file_exists($preview_dir)){
					if(!JFolder::create($preview_dir)){
						$alt = JText::_( "MYMUSE_FAILED" );
						$astatus = 0;
						$message = JText::_("MYMUSE_COULD_NOT_MAKE_DIR")."<br />$preview_dir";
					}else{
						$alt = JText::_( "MYMUSE_INSTALLED" );
						$astatus = 1;
						$message = JText::_("MYMUSE_DIR_CREATED")." ".$preview_dir;
					}
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message = JText::_("MYMUSE_DIR_EXISTS");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
		
				//DIRECTORY FOR GRAPHICS
				$name = JText::_("MYMUSE_MAKE_ALBUM_DIR");
				$album_dir =  JPATH_ROOT.DS."images".DS."A_MyMuseImages";
				if(!file_exists($album_dir)){
					if(!JFolder::create($album_dir)){
						$alt = JText::_( "MYMUSE_FAILED" );
						$astatus = 0;
						$message = JText::_("MYMUSE_COULD_NOT_MAKE_DIR")."<br />$album_dir";
					}else{
						$alt = JText::_( "MYMUSE_INSTALLED" );
						$astatus = 1;
						$message = JText::_("MYMUSE_DIR_CREATED")." ". $album_dir;
					}
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message = JText::_("MYMUSE_DIR_EXISTS");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
		
				// copy index.html to Download Dir
				$name = Jtext::_("index.html to Download Dir");
				if(!JFile::copy (JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
						$download_dir.DS."index.html")){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message = JText::_("MYMUSE_COULD_NOT_COPY_FILE");
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message = JText::_("MYMUSE_FILE_COPIED");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
				// copy htaccess to Download Dir
				if(stristr(PHP_OS, 'win')){
					//skip the htaccess
				}else{
					$name = Jtext::_("htaccess to Download Dir");
					if(!JFile::copy (JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."htaccess.txt",
							$download_dir.DS.".htaccess")){
						$alt = JText::_( "MYMUSE_FAILED" );
						$astatus = 0;
						$message = JText::_("MYMUSE_COULD_NOT_COPY_FILE");
					}else{
						$alt = JText::_( "MYMUSE_INSTALLED" );
						$astatus = 1;
						$message = JText::_("MYMUSE_FILE_COPIED");
					}
					$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
				}
		
		
				// copy index.html to Preview Dir
				$name = Jtext::_("index.html to Preview Dir");
				if(!JFile::copy (JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
						$preview_dir.DS."index.html")){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message = JText::_("MYMUSE_COULD_NOT_COPY_FILE");
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message = JText::_("MYMUSE_FILE_COPIED");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
		
		
				// copy index.html to Album Dir
				$name = Jtext::_("MYMUSE_COPY_INDEX_TO_ALBUM_DIR");
				if(!JFile::copy (JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."index.html",
						$album_dir.DS."index.html")){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message = JText::_("MYMUSE_COULD_NOT_COPY_FILE");
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message = JText::_("MYMUSE_FILE_COPIED");
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
				//MOVE LOGO
				$name = JText::_("MYMUSE_COPY_LOGO")." /images/logo150sq.jpg";
				$logo = JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS."assets".DS."images".DS."logo150sq.jpg";
				if(!file_exists($logo)){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message =  JText::_("MYMUSE_COPY_LOGO_FAILED")." File does not exist: ".$logo;
				}
				elseif(!JFile::copy ($logo,
						JPATH_ROOT.DS."images".DS."logo150sq.jpg")){
					$alt = JText::_( "MYMUSE_FAILED" );
					$astatus = 0;
					$message =  JText::_("MYMUSE_COPY_LOGO_FAILED"). $logo." ".JPATH_ROOT.DS."images".DS."logo150sq.jpg";
				}else{
					$alt = JText::_( "MYMUSE_INSTALLED" );
					$astatus = 1;
					$message =  JText::_("MYMUSE_COPY_LOGO_SUCCESS");
		
				}
				$actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
		
			}
				
		if(!$this->already_installed && $type == "install"){

			// update store download dir
			$download_dir =  JPATH_ROOT.DS."images".DS."A_MyMuseDownloads";
			$name = JText::_("MYMUSE_UPDATING_STORE");
			$query = "SELECT params FROM #__mymuse_store WHERE id='1'";
			$db->setQuery($query);
			$store_params = json_decode($db->loadResult(), TRUE);
			if($store_params){
				$store_params['my_download_dir'] = $db->getEscaped($download_dir);
				$registry = new JRegistry;
				$registry->loadArray($store_params);
				$new_params = (string)$registry;
				

				$query = "UPDATE #__mymuse_store set ";
				$query .= "params='$new_params' WHERE id=1
				";

				$db->setQuery($query);
				if(!$db->query()){
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
			if(!$db->query()){
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
			//WAS ALREADY INSTALLED
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
					<td><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
						<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Success') : JText::_('NOT Successful'); ?>
					</span>
					</td>
				</tr>
				<?php endforeach; ?>

			</tbody>
		</table>
		<?php 
		}
	}
}
