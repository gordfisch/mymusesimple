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
<form action="index.php" method="post" name="adminForm">
<input type="hidden" name="option" value="com_mymuse">
<input type="hidden" name="task" value="couponadd">
<input type="hidden" name="Itemid" value="Itemid">
<div class="componentheading"><?php echo Jtext::_('MYMUSE_ENTER_A_COUPON'); ?></div>
<table class="mymuse_cart" >
	<tr class="mymuse_cart_top">
		<td colspan="2" class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_ENTER_COUPON_CODE'); ?></b></td>
	</tr>
	<tr>
		<td><input type="text" class="input" name="coupon" value="" size="50"></td>
		<td><input type="submit" class="button" name="confirm" value="<?php echo JText::_('MYMUSE_SUBMIT'); ?>"></td>
	</tr>
</table>
</form>