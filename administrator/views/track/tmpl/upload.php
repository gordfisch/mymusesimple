<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

/*
# This upload script is original from the component com_mediamu and is only modified to use it with MyMuse 
# ------------------------------------------------------------------------
@author Ljubisa - ljufisha.blogspot.com
@copyright Copyright (C) 2012 ljufisha.blogspot.com. All Rights Reserved.
@license - http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
Technical Support: http://ljufisha.blogspot.com
*/

// no direct access
defined('_JEXEC') or die('Restricted Access');

?>
<div class="jdlists-header-info"><?php echo '<img align="left" src="'.JURI::root().'administrator/components/com_mymuse/assets/images/info22.png" 
width="22" height="22" border="0" alt="" />&nbsp;&nbsp;'.$this->message.'<br /><br />'
.$this->currentDir.'
<br /><br />'.JText::_('MYMUSE_UPLOADER_DESC2'); ?> <br /><br /></div>
    <div class="clr"> </div> 
    <div id="msgCont"></div>
<div id="mediamu_wrapper">
    <div id="uploader_content">
        <?php
         echo $this->loadTemplate('uploader'); ?>
    </div>
</div>


