<?php
/**
 * @version	 $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */





// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$lists =& $this->lists;
$orders_total =& $this->orders_summary->total_orders;
$orders_summary =& $this->orders_summary;
$rows =& $this->items_summary;
JHtml::_('behavior.tooltip');
JHTML::_( 'behavior.calendar' );

?>
		
		<form action="index.php" method="post" name="adminForm" >
		<h2><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></h2>
		<table class="mymuse_cart"">
		<tr>

			<td width="200"><?php echo $this->form->getLabel('catid'); ?></td>
			<td width="100"><?php echo $this->form->getInput('catid'); ?></td>
		</tr>
		<tr>
			<td ><?php echo $this->form->getLabel('filter_order_status'); ?></td>
			<td><?php echo $this->form->getInput('filter_order_status'); ?></td>
		</tr>
		<tr>
			<td ><?php echo $this->form->getLabel('filter_start_date'); ?></td>
			<td><?php echo $this->form->getInput('filter_start_date'); ?></td>
		</tr>
		<tr>
			<td><?php echo $this->form->getLabel('filter_end_date'); ?></td>
			<td><?php echo $this->form->getInput('filter_end_date'); ?></td>
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
		<?php echo JText::_('MYMUSE_ORDER_SUMMARY_EXPLANATION'); ?>
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
<?php 	}else{ ?>
			<h3><?php echo JText::_( 'MYMUSE_NO_ORDER' ); ?></h3>
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