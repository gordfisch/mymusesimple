<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// no direct access
defined('_JEXEC') or die;

?>
<h1>Could not do the Upgrade!</h1>
<h2><?php echo $this->msg; ?></h2>
<form action="<?php echo JRoute::_('index.php?option=com_mymuse'); ?>" 
method="post" name="adminForm" id="upgrade-form">
<table>
<tr>
	<td>
Enter the database prefix, without the _<br /> 
Example: sjg<br /> 
(found in your old_site/configuration.php file)</td>
<td><input type="text" name="dbprefix" size="6"></td>

</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>