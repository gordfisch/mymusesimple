<?php
/**
 * @version	 $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */





// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$lists =& $this->lists;
$orders_total =& $this->orders_total;
$orders_summary =& $this->orders_summary;
$rows =& $this->items_summary;
JHtml::_('behavior.tooltip');
JHTML::_( 'behavior.calendar' );

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
		<form action="index.php" method="post" name="adminForm" >
		<h2><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></h2>
		<table class="mymuse_cart"">
		<tr>
			<td width="200"><?php echo JText::_( 'MYMUSE_ORDER_STATUS' ); ?> :</td>
			<td width="100"> <select name="filter_order_status" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('MYMUSE_ORDER_STATUS');?></option>
                    <?php echo JHtml::_('select.options', MGrid::orderStatusOptions(), "value", "text", $this->state->get('filter.order_status'), true);?>
                </select></td>
			
		</tr>
		<tr>

			<td width="200"><?php echo JText::sprintf( 'MYMUSE_CATEGORY','' ); ?>:</td>
			<td width="100"><?php echo $lists['catid']; ?></td>
		</tr>
		<tr>
				<td ><label class="filter-search-lbl" for="startdate_img"><?php echo JText::_('MYMUSE_START_DATE'); ?></label></td>
			
			<td><input name="filter_start_date" id="startdate" type="text" value="<?php echo $this->state->get('filter.start_date')?>" /> 
				<img class="calendar" 
				src="media/system/images/calendar.png" 
				alt="calendar" id="startdate_img" / ></td>
			
		</tr>
		<tr>
			<td><label class="filter-search-lbl" for="enddate_img"><?php echo JText::_('MYMUSE_END_DATE'); ?></label></td>
			<td><input name="filter_end_date" id="enddate" type="text" value="<?php echo $this->state->get('filter.end_date')?>" /> 
				<img class="calendar" 
				src="media/system/images/calendar.png" 
				alt="calendar" id="enddate_img" / ></td>
		</tr>
		<tr>
			<td colspan="2" valign="top">
			<button onclick="this.form.submit();"><?php echo JText::_( 'MYMUSE_CREATE_REPORT' ); ?></button>
			</td>
		</table>
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="view" value="reports" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		<input type="hidden" name="boxchecked" value="0" />

		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		
		
<?php 
if($orders_total >0 && count( $rows )){ ?>
		<h2><?php echo JText::_('MYMUSE_ORDER_SUMMARY'); ?></h2>
		<table class="mymuse_cart">
		<tr>
			<td width="200"><?php echo JText::_( 'MYMUSE_TOTAL_NO_ORDERS' ); ?>:</td>
			<td width="100" align="right"><?php echo $orders_total; ?></td>
		</tr>
		<tr>
			<td width="200"><?php echo JText::_( 'MYMUSE_TOTAL_SUBTOTAL' ); ?>:</td>
			<td width="100" align="right"><?php echo MyMuseHelper::printMoney($orders_summary->total_subtotal); ?></td>
		</tr>
		<tr>
			<td width="200"><?php echo JText::_( 'MYMUSE_TOTAL_SHIPPING' ); ?>:</td>
			<td width="100" align="right"><?php echo MyMuseHelper::printMoney($orders_summary->total_shipping); ?></td>
		</tr>
		<tr>
			<td width="200"><?php echo JText::_( 'MYMUSE_TOTAL_DISCOUNTS' ); ?>:</td>
			<td width="100" align="right"><?php echo MyMuseHelper::printMoney($orders_summary->total_discount); ?></td>
		</tr>
	<?php foreach($orders_summary->tax_array as $tax){ ?>
		<tr>
			<td width="200"><?php echo JText::_( $tax ); ?>:</td>
			<td width="100" align="right"><?php echo MyMuseHelper::printMoney($orders_summary->$tax); ?></td>
		</tr>
	<?php } ?>	
		</table>
<?php } ?>


<?php if(count( $rows )){ ?>
		<h2><?php echo JText::_('MYMUSE_ITEMS_SUMMARY'); ?></h2>
		
		<table class="mymuse_cart">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('MYMUSE_PRODUCT'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_ARTIST'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_NUMBER_SOLD'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_TOTAL_SALES'); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'MYMUSE_TOTAL_ARTIST' ); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php
		$k = 0;
		$total = 0.00;
		for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
			$row =& $rows[$i];
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="220">
					<?php echo $row->product_name; ?>
				</td>
				<td width="220">
					<?php echo $row->artist_name; ?>
				</td>
				<td align="center" width="80">
					<?php echo $row->quantity; ?>
				</td>
				<td align="right"  width="80">
					<?php echo MyMuseHelper::printMoney($row->total);?>
				</td>
				<td align="right" width="80">
					<?php echo MyMuseHelper::printMoney($row->total*$this->params->get('my_owner_percent')/100); ?>
				</td>

				<?php
				$k = 1 - $k;
				$total += $row->total;
				?>
			</tr>
			<?php
		}
		?>
			<tr>
				<td colspan="3" align="right"><?php echo Jtext::_('MYMUSE_TOTAL'); ?></td>
				<td align="right"><?php echo MyMuseHelper::printMoney($total); ?></td>
				<td align="right"><?php echo MyMuseHelper::printMoney($total*$this->params->get('my_owner_percent')/100); ?>
			</tr>
		</tbody>
		</table>
<?php } ?>