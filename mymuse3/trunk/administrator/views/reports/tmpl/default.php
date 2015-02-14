<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */




// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
	$lists =& $this->lists;
	$orders_total =& $this->orders_summary->total_orders;
	$orders_summary =& $this->orders_summary;
	$rows =& $this->items_summary;
	
	JHtml::_('behavior.tooltip');
	JHTML::_( 'behavior.calendar' );
	JHtml::_('behavior.formvalidation');
	JHtml::_('behavior.keepalive');
	JHtml::_('formbehavior.chosen', 'select');

		?>
<form action="index.php" method="post" name="adminForm" id="adminForm">	
	<div id="j-sidebar-container" class="span2">
	<?php
	echo $this->sidebar;
	?>
	</div>

<div id="j-main-container" class="span10">	
	<div class="clearfix"> </div>
		
		<h2><?php echo JText::_('Filter'); ?></h2>

		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="view" value="reports" />
		<input type="hidden" name="task" value="" />
		
	<div class="pull-left span5">	
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('catid'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('catid'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('filter_order_status'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('filter_order_status'); ?>
			</div>
		</div>
	</div>
	<div class="pull-right span5">	
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('filter_start_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('filter_start_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('filter_end_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('filter_end_date'); ?>
			</div>
		</div>
	</div>
<div style="clear: both;"></div>			
			

		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		
		
<?php if($orders_total >0){ ?>
		<h2><?php echo JText::_('MYMUSE_ORDER_SUMMARY'); ?></h2>
		<b><?php echo JText::_('MYMUSE_ORDER_SUMMARY_EXPLANATION'); ?></b><br />
		<?php echo $this->orderlinks; ?>
		<table class="paramlist admintable">
		<tr>
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_TOTAL_NO_ORDERS' ); ?>:</td>
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
			<td width="200" class="paramlist_key"><?php echo JText::_( 'MYMUSE_COUPON_DISCOUNT' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_coupon_discount;?></td>
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
<?php } ?>


<?php if(count( $rows )){ ?>
		<h2><?php echo JText::_('MYMUSE_ITEMS_SUMMARY'); ?></h2>
		
		<table  width="800">
		<thead>
			<tr>
				<th></th>
				<th class="title">
					<?php echo JText::_('MYMUSE_PRODUCT').":".JText::_('COM_MYMUSE_TITLE_ITEMS'); ?>
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
				<td><?php echo $i+1; ?></td>
				<td>
					<?php 
					if($row->parent){
							echo $row->parent." - ";
					}else{
							echo 'CD - ';
					}
						?> <?php echo $row->product_name; ?>
				</td>
				<td>
					<?php echo $row->artist_name; ?>
				</td>
				<td align="center">
					<?php echo $row->quantity; ?>
				</td>
				<td align="right">
					<?php echo MyMuseHelper::printMoney($row->total); ?>
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
			</tr>
		</tbody>
		</table>
<?php } ?>
<div style="height: 100px"></div>
</div>