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

if(count($this->recommends)){
	$percent = "100";
	if(count($this->recommends) == 2){ $percent = "48"; }
	if(count($this->recommends) == 3){ $percent = "30"; }
	?>
	<div id="container">
	
	<h3><?php echo JText::_('MYMUSE_RELATED_ITEMS'); ?></h3>
	<?php foreach($this->recommends as $item){ ?>
		<div width="<?php echo $percent; ?>%" style="float: left; margin: 10px;">
		<a href="<?php echo $item->url; ?>"><img src="<?php echo $item->list_image; ?>"></a>
			<div>
			<a href="<?php echo $item->url; ?>"><?php  echo $item->title; ?></a>
			</div>
		</div>
	<?php } ?>
	</div>
<?php } ?>
<div style="clear: both;"></div>
