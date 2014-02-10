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
 // no direct access
defined('_JEXEC') or die('Restricted access');
?>
        <form action="index.php?Itemid=<?php echo $this->Itemid; ?>" method="post" name="adminForm">
        <input type="hidden" name="option" value="com_mymuse">
        <input type="hidden" name="task" value="<?php echo $this->task; ?>">
        <?php if ($this->params->get('my_muse_use_shipping')){ ?>
        <input type="hidden" name="shipmethodid" value="<?php echo $this->shipmethodid; ?>">
		<?php } ?>
		<table class="mymuse_cart">
			<tr>
				<td><input type="submit" class="button" name="<?php echo $this->task; ?>" value="<?php echo $this->button; ?>"></td>
			</tr>
		</table>
		</form>