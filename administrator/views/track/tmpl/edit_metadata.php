<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_mymuse
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('metadesc'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('metadesc'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('metakey'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('metakey'); ?>
				</div>
			</div>
<?php 
echo JLayoutHelper::render('joomla.edit.metadata', $this);
?>