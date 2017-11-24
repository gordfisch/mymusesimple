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

if(count($this->footer)) :
	?>
	<div id="mymuse-container-wrap">
	
	<h3><?php echo JText::_('MYMUSE_WE_RECOMMEND'); ?></h3>
	<?php foreach($this->footer as $item) : 

	?>
		<div class="recommended-item">
		
		<?php if(isset($item->list_image) && $item->list_image != '') {?>
		<div class="recommended-item-image">
		<a href="<?php echo $item->url; ?>"><img src="<?php echo $item->list_image; ?>"></a>
		</div>
		<?php } ?>
		
		<div class="recommended-title">
		<a href="<?php echo $item->url; ?>"><?php  echo $item->title; ?></a>
		</div>

		</div>
	<?php endforeach; ?>
	</div>
<?php endif; ?>
<div style="clear: both;"></div>


