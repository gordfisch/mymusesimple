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
# This script is original from the component com_mediamu and is only modified to use it with MyMuse 
# ------------------------------------------------------------------------
@author Ljubisa - ljufisha.blogspot.com
@copyright Copyright (C) 2012 ljufisha.blogspot.com. All Rights Reserved.
@license - http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
Technical Support: http://ljufisha.blogspot.com
*/
$item = $this->item;
// no direct access
 defined('_JEXEC') or die('Restricted Access');

?>
		<script type="text/javascript">
		<!--

		function submitbutton(pressbutton)
		{

			var form = document.adminForm;

			if (pressbutton == 'cancelitem') {
				submitform( pressbutton );
				return;
			}

			// do field validation

			if (form.title.value == ""){
				alert( "<?php echo JText::_( 'MYMUSE_FILE_MUST_HAVE_A_TITLE', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
<form action="" method="post" id="adminForm" name="adminForm">
	<div id="uploader">
		<p><?php JText::printf('MYMUSE_ERROR_RUNTIME_NOT_SUPORTED', $this->runtime) ?></p>
	</div>
    <!-- we need here the 'task' field to get NOT an error message like: 'TypeError: b.task is undefined' -->
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="uploaddir" value="<?php echo $this->currentDir; ?>" />
    <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
    		<input type="hidden" name="parentid" value="<?php echo $item->id ?>" />
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="subtype" value="file" />
		<input type="hidden" name="layout" value="listtracks" />
		<input type="hidden" name="option" value="com_mymuse" />
</form>
<?php if($this->enableLog) : ?>
<button id="log_btn"><?php echo JText::_('MYMUSE_UPLOADER_LOG_BTN'); ?></button>
<div id="log"></div>
<?php endif; ?>