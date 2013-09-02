<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca

 * Modified from install.php file
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * Original install.php file
 * @package   Zoo Component
 * @author    YOOtheme http://www.yootheme.com
 * @copyright Copyright (C) 2007 - 2009 YOOtheme GmbH
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only

*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
$VERSION = $this->manifest->version;
list($MAJOR, $MINOR,$RELEASE) = explode(".", $VERSION);

$thisextension = strtolower( "com_mymuse" );
$thisextensionname = substr ( $thisextension, 4 );
$status = new JObject;
/**
 * Executes additional installation processes
 *
 * @since 1.0
 */


// first check if PHP5 is running
if (version_compare(PHP_VERSION, '5.0.0', '<')) {

	echo '<div class="fc-error">';
	echo 'Please upgrade PHP above version 5.0.0<br />';
	echo '</div>';
	return false;
}

// init vars
$error = false;
$extensions = array();



// reseting post installation session variables
$session  =& JFactory::getSession();
$session->set('mymuse.postinstall', false);
$session->set('mymuse.allplgpublish', false);

// fix joomla 1.5 bug
//$this->parent->getDBO =& $this->parent->getDBO();

// additional extensions
$add_array =& $this->manifest->xpath('additional');
$add = NULL;
if(count($add_array)) $add = $add_array[0];
if (is_a($add, 'JXMLElement') && count($add->children())) {
    $exts =& $add->children();
    foreach ($exts as $ext) {
		$extensions[] = array(
			'name' => $ext->data(),
			'type' => $ext->name(),
			'folder' => $this->parent->getPath('source').'/'.$ext->getAttribute('folder'),
			'installer' => new JInstaller(),
			'status' => false);
    }
}

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
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
	<tr>
		<td valign="top">
    		<img src="<?php echo 'components/com_mymuse/assets/images/logo325.jpg'; ?>" height="325" width="190" alt="MyMuse Logo" align="left" />
		</td>
		<td valign="top" width="100%">
       	 	<strong>MyMuse</strong><br/>
       	 	<span>MyMuse for Joomla! 2.5</span><br />
        	<font class="small">by <a href="http://www.arboreta.ca" target="_blank">Arboreta.ca</a></font><br/>
        	To get started
        	<ol>
        		<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_STORE');?></li>
        		<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE');?> <a href="index.php?option=com_plugins&view=plugins&filter_folder=mymuse">Plugins</a></li>
        		<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_CREATE_CATEGORY');?></li>
        		<li><?php echo JText::_('MYMUSE_INSTALL_CONFIGURE_USER_PROFILE');?></li>
        	</ol>
		</td>
	</tr>
</table>
<h3><?php echo JText::_('Additional Extensions'); ?></h3>
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
					<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Installed successfully') : JText::_('NOT Installed'); ?></span>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h3><?php echo JText::_('Actions'); ?></h3>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('Actions'); ?></th>
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
// ... more actions
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
		$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
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
		$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		

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
		$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		
		
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
		$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );

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
			$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
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
		$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
		

		
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
		$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );

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
	$status->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
	

	
	$i = 0;
	foreach ($status->actions as $ext) : ?>
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


