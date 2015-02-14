<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
	$lists =& $this->lists;
	$orders_total =& $this->orders_total;
	$orders_summary =& $this->orders_summary;
	$rows =& $this->items_summary;
	
		JHTML::_('behavior.tooltip');
		
		?>
		

		<table class="paramlist admintable">
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_ORDER_STATUS' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $lists['order_status'];?></td>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_START_DATE' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $lists['filter_date_start'];?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_SHOPPER_NAME' ); ?>:</td>
			<td width="100" class="paramlist_value"><input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" /></td>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_END_DATE' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $lists['filter_date_end'];?></td>
		</tr>
		</table>
		<h2>Order Summary</h2>
		<table class="paramlist admintable">
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_TOTAL_ORDERS' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_total;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_TOTAL_SUBTOTAL' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_subtotal;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_TOTAL_SHIPPING' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_shipping;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_TOTAL_DISCOUNTS' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_discount;?></td>
		</tr>
	<?php foreach($orders_summary->tax_array as $tax){ ?>
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( $tax ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->$tax;?></td>
		</tr>
	<?php } ?>	
		</table>

		<h2>Items Summary</h2>
		
		<table  width="500">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('MYMUSE_PRODUCT'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_NUMBER SOLD'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_TOTAL_SALES'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php
		$k = 0;
		for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
			$row =& $rows[$i];
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $row->product_name; ?>
				</td>
				<td align="center">
					<?php echo $row->quantity; ?>
				</td>
				<td align="center">
					$<?php echo $row->total; ?>
				</td>

				<?php
				$k = 1 - $k;
				?>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>

