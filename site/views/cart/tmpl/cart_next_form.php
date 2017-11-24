 <?php 
 /**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
 // no direct access
defined('_JEXEC') or die('Restricted access');
?>
    <form action="<?php echo JRoute::_('index.php?option=com_mymuse&view=cart&task='.$this->task.'&Itemid='.$this->Itemid); ?>" method="post" name="adminForm">
   <?php if ($this->params->get('my_muse_use_shipping')){ ?>
        <input type="hidden" name="shipmethodid" value="<?php echo $this->shipmethodid; ?>">
	<?php } ?>
		<table class="mymuse_cart">
			<?php if(strpos($this->task, "confirm") !== false):?>
			<tr>
				<td></td>
			</tr>
			<?php endif;?>
			<tr>
				<td><input type="submit" class="button" name="<?php echo $this->task; ?>" value="<?php echo $this->button; ?>"></td>
			</tr>
		</table>
		</form>
