<?php
/**
 * @version		$Id$
* @package		mymuse
* @copyright	Copyright © 2016 - Arboreta Internet Services - All rights reserved.
* @license		GNU/GPL
* @author		Gordon Fisch
* @author mail	info@joomlamymuse.com
* @website		http://www.joomlamymuse.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<script>
jQuery(document).ready(function(){  
	jQuery("#licence").change( function(e) {
		alert(Query("#licence").value);
	})
});
</script>
<h3>Licence</h3>
<?php echo $this->lists['licences']; ?>

<?php print_pre($this->licence); ?>
