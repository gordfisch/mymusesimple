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
$results = $this->results;
?>
<table width="90%">
	<tr>
	<?php foreach($results as $r){ ?>
		<td valign="top"><?php echo $r; ?></td>
	<?php } ?>
	</tr>
</table>