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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHTML::_( 'behavior.calendar' );

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.ordering';

?>
<script  type="text/javascript">
window.addEvent('domready', function() {Calendar.setup({
        inputField     :    "startdate",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "startdate_img",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl" = Bottom Left, 
// "Tl" = Top Left, "Br" = Bottom Right, "Bl" = Botton Left)
        singleClick    :    true
    });});
</script>
<script  type="text/javascript">
window.addEvent('domready', function() {Calendar.setup({
        inputField     :    "enddate",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "enddate_img",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl" = Bottom Left, 
// "Tl" = Top Left, "Br" = Bottom Right, "Bl" = Botton Left)
        singleClick    :    true
    });});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mymuse&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
	<?php
	echo $this->sidebar; 
	?>
	</div>
	<div style="clear: both'"></div>
	<div id="j-main-container" class="span10">

			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('MYMUSE_FILTER'); ?></label>
				<input type="text" name="filter_search" placeholder="<?php echo JText::_('MYMUSE_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('MYMUSE_FILTER'); ?>" />
			</div>
			
			<div class="btn-group pull-left hidden-phone">	
				<select name="filter_order_status" class="inputbox"
				onchange="this.form.submit()">
				<option value="">
					<?php echo JText::_('MYMUSE_ORDER_STATUS');?>
				</option>
				<?php echo JHtml::_('select.options', MGrid::orderStatusOptions(), "value", "text", $this->state->get('filter.order_status'), true);?>
				</select>
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" 
					onclick="document.id('filter_search').value='';
					document.id('startdate').value='';
					document.id('enddate').value=''; 
					this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			
			<div style="clear:both"> </div>
			<div class="btn-group pull-left">
		 		<label class="filter-search-lbl" for="startdate_img"><?php echo JText::_('MYMUSE_START_DATE'); ?></label> 
				<input name="filter_start_date" id="startdate" type="text"
				value="<?php echo $this->state->get('filter.start_date')?>" /> <img
				class="calendar" src="templates/system/images/calendar.png"
				alt="calendar" id="startdate_img"/ > 
			</div>
			<div class="btn-group pull-left hidden-phone">
				<label class="filter-search-lbl" for="enddate_img"><?php echo JText::_('MYMUSE_END_DATE'); ?></label> 
				<input name="filter_end_date" id="enddate" type="text"
				value="<?php echo $this->state->get('filter.end_date')?>" /> <img
				class="calendar" src="templates/system/images/calendar.png"
				alt="calendar" id="enddate_img"/ >
			</div>
	<div style="clear:both"> </div>
	<table class="table table-striped" id="ordersList">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="1%" class="nowrap">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
				<th>
					<?php echo JHTML::_('grid.sort',   'MYMUSE_NAME', 'u.last_name', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort',   'MYMUSE_DATE', 'c.created', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>

				<th>
					<?php echo JHTML::_('grid.sort',   JText::_('MYMUSE_STATUS'), 'c.order_status', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>

				<th>
					<?php echo JHTML::_('grid.sort',   JText::_('MYMUSE_SUBTOTAL'), 'c.order_subtotal', @$lists['order_Dir'], @$lists['order'] ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_mymuse');
			$canEdit	= $user->authorise('core.edit',			'com_mymuse');
			$canCheckin	= $user->authorise('core.manage',		'com_mymuse');
			$canChange	= $user->authorise('core.edit.state',	'com_mymuse');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<a href="index.php?option=com_mymuse&view=order&task=order.edit&id=<?php echo (int) $item->id; ?>"><?php echo (int) $item->id; ?></a>
				</td>
				<td class="center">
					<?php echo $item->shopper; ?>
				</td>
				<td class="center">
					<?php echo $item->created; ?>
				</td>
				 <td class="center">
					<?php echo $item->status_name; ?>
				</td>
				<td class="center">
					<?php echo $item->order_subtotal; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>