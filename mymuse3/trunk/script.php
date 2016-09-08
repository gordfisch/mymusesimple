<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');



/**
 * Script file of MyMuse component
 */
class com_mymuseInstallerScript
{
	
	var $already_installed = 0;
	var $old_version = 0;
	var $css = '';
	var $mymuse_params = '';
	
	
	public function __construct()
	{
		
		
		$helper_path = JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS.'helpers'.DS.'mymuse.php';
		if(file_exists($helper_path)){
			require_once (JPATH_ROOT.DS."administrator".DS."components".DS."com_mymuse".DS.'helpers'.DS.'mymuse.php');
			//$this->mymuse_params = MyMuseHelper::getParams();
			
		}
	}
	
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

		$plugins = array();
		$modules = array();
		$db = JFactory::getDBO();
		
		//first PLUG-INS PLUG-INS
		$manifest = $parent->get('manifest');
		$super = $parent->getParent();
		$add = NULL;
		if(count($manifest->plugins->plugin)){
		
			foreach ($manifest->plugins->plugin as $plugin) {
				$plugins[] = array(
						'name' => (string) $plugin,
						'type' => (string) $plugin['name'],
						'folder' => $super->getPath('source').'/'.(string) $plugin['folder'],
						'installer' => new JInstaller,
						'status' => false);
		
			}
		}
		
		// uninstall plugins
		for ($i = 0; $i < count($plugins); $i++) {
			$plugin =& $plugins[$i];
			$query = "SELECT extension_id FROM #__extensions
			WHERE element ='".$plugins[$i]['type']."'";
			$db->setQuery($query);
			$res = $db->loadResult();
			echo $res." ".$plugins[$i]['type']."<br />";
			if ($plugins[$i]['installer']->uninstall('plugin', $res)) {
				$plugins[$i]['status'] = true;
			}
		}
		
		//second MODULES
		$manifest = $parent->get('manifest');
		$super = $parent->getParent();
		$add = NULL;
		if(count($manifest->modules->module)){
		
			foreach ($manifest->modules->module as $module) {
				$modules[] = array(
						'name' => (string) $module,
						'type' => (string) $module['name'],
						'installer' => new JInstaller,
						'status' => false);
			}
		}
		
		// uninstall Modules
		for ($i = 0; $i < count($modules); $i++) {
			$module =& $modules[$i];
			$query = "SELECT extension_id FROM #__extensions
			WHERE element ='".$modules[$i]['type']."'";
			$db->setQuery($query);
			$res = $db->loadResult();
			echo $res." ".$modules[$i]['type']."<br />";
			if ($modules[$i]['installer']->uninstall('module', $res)) {
				$modules[$i]['status'] = true;
			}
		}
		
		$params = $this->mymuse_params;

		$extensions = array();
		
		//directories??

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
		//echo '<p>' . JText::_('PREFLIGHT_' . $type . '_TEXT') . '</p>'; exit;

		
		$db = JFactory::getDBO();
		$query = "SELECT * from #__extensions WHERE name = 'mymuse' ";
		
		$db->setQuery($query);
		if($res = $db->loadObject()){
				$this->already_installed = 1;
				$manifest = json_decode($res->manifest_cache);
				$this->old_version = $manifest ->version;
				// get the current css file
				if(file_exists(JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'assets'.DS.'css'.DS.'mymuse.css')){
					$this->css = file_get_contents(JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'assets'.DS.'css'.DS.'mymuse.css');
				}
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

		$db = JFactory::getDBO();
		$actions = array();
		
		// add params
		if ($type == 'install') {
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__extensions'));
			$defaults = '{"show_title":"1","show_intro":"1","product_show_product_image":"1","product_product_image_height":"0","product_show_quantity":"0","product_show_fulltext":"1","show_recording_details":"0","product_show_tracks":"1","product_player_type":"single","product_player_width":"","product_player_height":"","product_show_select_column":"1","product_show_filesize":"1","product_show_filetime":"0","product_show_cost_column":"1","product_show_preview_column":"1","product_show_cartadd":"1","product_item_selectbox":"0","show_category":"0","link_category":"0","show_parent_category":"0","link_parent_category":"0","show_author":"0","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"0","show_item_navigation":"0","show_vote":"0","show_readmore":"0","show_readmore_title":"1","show_icons":"0","show_print_icon":"0","show_email_icon":"0","show_hits":"0","show_noauth":"0","show_base_description":"0","categories_description":"","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"0","show_cat_num_articles_cat":"0","category_layout":"_:blog","show_category_title":"1","show_description":"1","show_description_image":"1","category_image_height":"0","maxLevel":"-1","subcat_columns":"2","show_empty_categories":"0","show_no_articles":"1","show_subcat_image":"0","show_subcat_desc":"0","show_cat_num_articles":"1","page_subheading":"","category_show_all_products":"1","category_show_product_image":"1","category_product_image_height":"0","category_show_intro_text":"1","category_product_link_titles":"1","category_show_comment_total":"0","num_leading_articles":"0","num_intro_articles":"10","num_columns":"2","num_links":"4","multi_column_order":"1","show_subcategory_content":"-1","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"Y-m-d","list_show_hits":"1","list_show_price":"1","list_show_author":"0","list_show_sales":"1","orderby_pri":"none","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0","feed_show_readmore":"0","username":"","password":""}'; // JSON format for the parameters
			$query->set($db->quoteName('params') . ' = ' . $db->quote($defaults));
			$query->where($db->quoteName('name') . ' = ' . $db->quote('mymuse')); 
			$db->setQuery($query);
			$db->execute();
		}
		
	
		if($type == "install" || $type == "update"){
			// init vars
			$error = false;
			$extensions = array();
		
			// reseting post installation session variables
			$session  = JFactory::getSession();
			$session->set('mymuse.postinstall', false);
			$session->set('mymuse.allplgpublish', false);
		
			// additional extensions
			//first PLUG-INS PLUG-INS

			$manifest = $parent->get('manifest');
			$super = $parent->getParent();
			$add = NULL;
			if(count($manifest->plugins->plugin)){

				foreach ($manifest->plugins->plugin as $plugin) {
					
					$extensions[] = array(
							'name' => (string) $plugin,
							'type' => (string) $plugin['name'],
							'folder' => $super->getPath('source').'/'.(string) $plugin['folder'],
							'installer' => new JInstaller,
							'status' => false);

				}
			}
			
			//now add MODULES
			if(count($manifest->modules->module)){
					
				$super = $parent->getParent();
				foreach ($manifest->modules->module as $module) {
						
					$extensions[] = array(
							'name' => (string) $module,
							'type' => (string) $module['name'],
							'folder' => $super->getPath('source').'/'.(string) $module['folder'],
							'installer' => new JInstaller,
							'status' => false);
				}
			}
			
			

			// install additional extensions
			for ($i = 0; $i < count($extensions); $i++) {
				$error = false;
				$extension =& $extensions[$i];

				$extension['installer']->setOverwrite(true);
				if ($extension['installer']->install($extension['folder'])) {
					$extension['status'] = true;
				}else{
					echo $extension['name']. "threw an error ".$extension['installer']->getError(); 
					$error = $extension['installer']->getError();
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
<table cellpadding="4" cellspacing="0" border="0" width="800">
	<tr>
		<td valign="top" width="40%"><img
			src="<?php echo 'components/com_mymuse/assets/images/logo325.jpg'; ?>"
			height="325" width="190" alt="MyMuse Logo" align="left" /></td>
		<td valign="top" width="60%"><strong>MyMuse</strong><br /> <span>MyMuse
				for Joomla! 3</span><br /> <font class="small">by <a
				href="http://www.arboreta.ca" target="_blank">Arboreta.ca</a>
		</font><br /> To get started
			<ol>
				<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE');?> <a
					href="index.php?option=com_mymuse&view=store&layout=edit&id=1"><?php echo JText::_('STORE'); ?></a></li>
				<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE');?> <a
					href="index.php?option=com_plugins&view=plugins&filter_folder=mymuse"><?php echo JText::_('COM_MYMUSE_PLUGINS'); ?></a>
				</li>
				<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_CREATE_CATEGORY');?>
						</li>
				<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_USER_PROFILE');?>
						</li>
			</ol></td>
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
			<td align="center"><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
						<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Installed successfully') : JText::_('NOT Installed'); ?>
					</span></td>
		</tr>
				<?php endforeach; ?>
			</tbody>
</table>

<h3>
			<?php echo JText::_('Actions'); ?>
		</h3>





<?php
		// see if db needs updating
		$db = JFactory::getDBO();
		

		$query = "SHOW COLUMNS FROM #__mymuse_country LIKE 'ordering'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_country` ADD `ordering` int(11) NOT NULL AFTER `country_2_code`  ";
			$db->setQuery($query);
			$db->execute();
		}
		
		$query = "SHOW COLUMNS FROM #__mymuse_country LIKE 'plugin'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "SHOW COLUMNS FROM #__mymuse_country LIKE 'zone_id'";
			$db->setQuery($query);
			if($col = $db->loadObject()){
				$query = "ALTER TABLE `#__mymuse_country` CHANGE `zone_id` `plugin` TINYTEXT NOT NULL ";
			}else{
				$query = "ALTER TABLE `#__mymuse_country` ADD `plugin` TINYTEXT NOT NULL AFTER `id`  ";
			}
			$db->setQuery($query);
			$db->execute();
			
			$paypal_countries = '
			"Australia",
			"Austria",
			"Belgium",
			"Bulgaria",
			"Canada",
			"Croatia",
			"Cyprus",
			"Czech Republic",
			"Denmark",
			"Estonia",
			"Finland",
			"France",
			"Germany",
			"Greece",
			"Hong Kong",
			"Hungary",
			"Ireland",
			"Israel",
			"Italy",
			"Japan",
			"Kenya",
			"Latvia",
			"Liechtenstein",
			"Lithuania",
			"Luxembourg",
			"Malta",
			"Mexico",
			"Netherlands",
			"New Zealand",
			"Norway"';
			
			$query = "UPDATE #__mymuse_country set plugin='paypal' WHERE country_name IN ($paypal_countries)";
			$db->setQuery($query);
			$db->execute();	
				
		}
		
		//add EU bloc
		$query = "SHOW COLUMNS FROM #__mymuse_country LIKE 'bloc'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_country` ADD `bloc` TINYTEXT NOT NULL AFTER `id`  ";
			$db->setQuery($query);
			$db->execute();
			$eu_countries = '
				"AUT",
"BEL",
"BGR",
"HRV",
"CYP",
"CZE",
"DNK",
"EST",
"FIN",
"FRA",
"DEU",
"GRC",
"HUN",
"IRL",
"ITA",
"LVA",
"LTU",
"LUX",
"MLT",
"NLD",
"POL",
"PRT",
"ROM",
"SVK",
"SVN",
"ESP",
"SWE",
"GBR"';
			$query = "UPDATE #__mymuse_country set bloc='EU' WHERE country_3_code IN ($eu_countries)";
			$db->setQuery($query);
			$db->execute();
			}
			
			// add table for product recommendations
			$query = "CREATE TABLE IF NOT EXISTS `#__mymuse_product_recommend_xref` (
  		`product_id` int(11) NOT NULL DEFAULT '0',
  		`recommend_id` int(11) NOT NULL DEFAULT '0'
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$db->setQuery($query);
		$db->execute();
		
		//see if mymuse downloads table exists
		$query = "SHOW TABLES LIKE '#__mymuse_downloads'";
		$db->setQuery($query);
		if(!$db->loadResult()){
			//add the table
			$query = "CREATE TABLE IF NOT EXISTS `#__mymuse_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '1',
  `user_name` varchar(64) DEFAULT NULL,
  `user_email` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_id` int(11) NOT NULL DEFAULT '1',
  `product_filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_product_filename` (`product_filename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Download records';";
			$db->setQuery($query);
			$db->execute();
			
		}
		
		//see if mymuse_downloads table needs updating
		$query = "SHOW COLUMNS FROM #__mymuse_downloads LIKE 'user_email'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_downloads` ADD `user_email` VARCHAR(255)";
			$db->setQuery($query);
			$db->execute();
		}
		$query = "SHOW COLUMNS FROM #__mymuse_downloads LIKE 'order_id'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_downloads` ADD `order_id` INT( 11 ) NOT NULL AFTER `user_email`";
			$db->setQuery($query);
			$db->execute();
		}
		
		
		//see if mymuse_orders table needs updating
		$query = "SHOW COLUMNS FROM #__mymuse_order LIKE 'shopper_group_discount'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_order` ADD `shopper_group_discount` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `discount`";
			$db->setQuery($query);
			$db->execute();
		}
		
		$query = "SHOW COLUMNS FROM #__mymuse_order LIKE 'licence'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_order` ADD `licence` varchar(255) NOT NULL AFTER `ordering`  ";
			$db->setQuery($query);
			$db->execute();
		}
		
		//see if notes field needs updating
		$query = "SHOW FIELDS FROM `#__mymuse_order` WHERE Field = 'notes'";
		$db->setQuery($query);
		$res = $db->loadObject();
		if($res->Type != 'text')
		
			$query = "ALTER TABLE `#__mymuse_order` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
			$db->setQuery($query);
			$db->execute();
		}
		
		//Nov 2015 3.4.0 see if mymuse_product table needs updating
		$query = "SHOW COLUMNS FROM #__mymuse_product LIKE 'artistid'";
		$db->setQuery($query);
		if(!$col = $db->loadObject()){
			$query = "ALTER TABLE `#__mymuse_product` ADD `artistid` INT( 11 ) NOT NULL AFTER `catid`";
			$db->setQuery($query);
			$db->execute();
			$query = "UPDATE `#__mymuse_product` SET `artistid` = `catid` WHERE 1";
			$db->setQuery($query);
			$db->execute();
		}
		
		
		
		
		
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
		
		
			
				
		if(!$this->already_installed && $type == "install"){
	
			// update store download dir
			$download_dir =  JPATH_ROOT.DS."images".DS."A_MyMuseDownloads";
			$name = JText::_("MYMUSE_UPDATING_STORE");
			$query = "SELECT params FROM #__mymuse_store WHERE id='1'";
			$db->setQuery($query);
			$store_params = json_decode($db->loadResult(), TRUE);
			if($store_params){
				$store_params['my_download_dir'] = $download_dir;
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
			element='payment_offline' OR
			element='shipping_standard' OR
			element='audio_jplayer' OR
			element='video_jplayer' OR
			element='payment_paypal' OR
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
			//WAS ALREADY INSTALLED
			//restore the css file
			/** so different we want to overwrite 2014-03-05
			 * */
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
			<th class="title"><?php echo JText::_('Status'); ?></th>
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
			<td align="center"><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
						<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Success') : JText::_('NOT Successful'); ?>
					</span></td>
		</tr>
				<?php endforeach; ?>

			</tbody>
</table>
<?php 
		}
	}
}
