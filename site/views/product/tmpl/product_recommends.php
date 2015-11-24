<?php 
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(count($this->recommends)){ ?>
	<div id="mymuse-container-wrap">
	<h3><?php echo JText::_('MYMUSE_RELATED_ITEMS'); ?></h3>
	<?php foreach($this->recommends as $item){ ?>
	<div id="mymuse-container">	
		<div class="related-item">
		<a href="<?php echo $item->url; ?>"><img src="<?php echo $item->list_image; ?>"></a>
		</div>
	<?php } ?>
    
	</div>
<?php } ?>
<div style="clear: both;"></div>
</div>
