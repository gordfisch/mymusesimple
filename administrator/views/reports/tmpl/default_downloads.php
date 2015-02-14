<?php
/**
 * @version     $$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */




// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
	$lists =& $this->lists;
	$orders_total =& $this->orders_summary->total_orders;
	$rows =& $this->downloads;
	
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
		
		
<?php if(count( $rows )){ ?>
		<h2><?php echo JText::_('MYMUSE_DOWNLOADS_REPORT'); ?></h2>
		<table  width="800">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('MYMUSE_ID'); ?>
				</th>
				<th>
					<?php echo JText::_('MYMUSE_USER_ID'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_USER_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_CONTACT_EMAIL_LABEL'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_DATE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_PRODUCT_ID'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('MYMUSE_UPLOADER_FILENAME'); ?>
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
				<td><?php echo $row->id; ?></td>
				<td>
					 <?php echo $row->user_id; ?>
				</td>
				<td>
					<?php echo $row->user_name; ?>
				</td>
				<td align="center">
					<?php echo $row->user_email; ?>
				</td>
				<td align="right">
					<?php echo $row->date; ?>
				</td>
				<td align="right">
					<?php echo $row->product_id; ?>
				</td>
				<td align="right">
					<?php echo $row->product_filename; ?>
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
<?php } ?>
</div>