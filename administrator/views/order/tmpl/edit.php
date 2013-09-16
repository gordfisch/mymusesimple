<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.switcher');
//print_pre($this->item);
$print = JRequest::getVar('print',0);
if($print){
	
	$content = '
	window.addEvent("load", function() { 
      window.print(); 
	});

	';
	$document = &JFactory::getDocument();
	$document->addScriptDeclaration($content);

}else{
	echo '<a href="index.php?option=com_mymuse&view=order&layout=edit&id='.$this->item->id.'&tmpl=component&print=1">'.JText::_("Print").'</a>';
}
?>
<script type="text/javascript">

	Joomla.submitbutton = function(task)
	{
		if (task == 'order.cancel' || document.formvalidator.isValid(document.id('order-form'))) {
			Joomla.submitform(task, document.getElementById('order-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>



<form 
action="<?php echo JRoute::_('index.php?option=com_mymuse&layout=edit&id='.(int) $this->item->id); ?>" 
method="post" name="adminForm" id="order-form" class="form-validate">
<div class="row-fluid">
	<!-- Begin Content -->
	<div class="span10 form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
			
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('MYMUSE_DETAILS', true)); ?>	
		<div class="span10"><h2><?php echo JText::_('MYMUSE_ORDER_SUMMARY'); ?></h2></div>
		<div style="clear: both;"></div>
		<div class="pull-left span5">
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('id'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('order_number'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('order_number'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('order_subtotal'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('order_subtotal'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('order_shipping'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('order_shipping'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('order_total'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('order_total'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('order_currency'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('order_currency'); ?>
				</div>
			</div>
		</div>

		<div class="pull-right span5">
		
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('coupon_name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('coupon_name'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('coupon_discount'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('coupon_discount'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('discount'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('discount'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('created'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('created'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('modified'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('modified'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('order_status'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('order_status'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('notes'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('notes'); ?>
				</div>
			</div>

<?php echo $this->form->getInput('checked_out'); ?>
<?php echo $this->form->getInput('checked_out_time'); ?>

            
		</div>
		<input type="hidden" name="old_status" value="<?php echo $this->item->order_status; ?>"" />
		<input type="hidden" name="task" value="" />

	    
	    
	<?php echo JHtml::_('form.token'); ?>
	<div style="clear: both;"></div>

	<fieldset class="adminform">
		<h2><?php echo JText::_('MYMUSE_CUSTOMER') ?></h2>
		<table class="admintable" width="100%">
			<tr VALIGN=top>
				<td width=50%><!-- Begin BillTo -->

				<table class="adminlist">
					<tr>
						<td COLSPAN=2><b><?php echo JText::_('MYMUSE_BILLING_ADDRESS') ?></b></td>
					</tr>

					<tr>
						<td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
						<td><?php echo $this->item->user->name ?></td>
					</tr>
					<?php if(isset($this->item->user->profile['address1'])){ ?>
					<tr valign="top">
						<td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
						<td><?php echo $this->item->user->profile['address1'] ?> <BR>
						<?php echo $this->item->user->profile['address2'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['city'])){ ?>
					<tr>
						<td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
						<td><?php echo $this->item->user->profile['city'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['region']) || isset($this->item->user->profile['region_name'])){ 
							if(!isset($this->item->user->profile['region_name'])){
								if(!is_numeric($data['profile']['region'])){
									$this->item->user->profile['region_name'] = $this->item->user->profile['region'];
								}else{
									$db = JFactory::getDBO();
									$query = "SELECT * FROM #__mymuse_state WHERE id='".$data['profile']['region']."'";
									$db->setQuery($query);
									if($row = $db->loadObject()){
										$data['profile']['region'] = $row->id;
										$data['profile']['region_name'] = $row->state_name;
									}
								}
							}
						?>
					<tr>
						<td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
						<td><?php echo $this->item->user->profile['region_name'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['postal_code'])){ ?>
					<tr>
						<td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
						<td><?php echo $this->item->user->profile['postal_code'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['country'])){ ?>
					<tr>
						<td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
						<td><?php echo $this->item->user->profile['country'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['phone'])){ ?>
					<tr>
						<td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
						<td><?php echo $this->item->user->profile['phone'] ?></td>
					</tr>
					<?php }?>
					
					<tr>
						<td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
						<td><?php echo $this->item->user->email ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_SHOPPER_GROUP') ?>:</td>
						<td><?php echo $this->item->user->shopper_group_name ?> (<?php echo $this->item->user->shopper_group_discount; ?>%)</td>
					</tr>
				</table>
				<!-- End BillTo --></td>

				<td width=50%><?php 
				if($this->params->get('my_use_shipping') && isset($this->item->user->shipto)){
					?>
				<table class="adminlist">
					<tr>
						<td COLSPAN=2><b><?php echo JText::_('MYMUSE_SHIPPING_ADDRESS') ?></b></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_COMPANY') ?>:</td>
						<td><?php echo $this->item->user->shipto->company ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
						<td><?php echo $this->item->user->shipto->first_name ?> <?php echo $this->item->user->shipto->middle_name ?>

						<?php echo $this->item->user->shipto->last_name ?></td>
					</tr>
					<tr VALIGN=TOP>
						<td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
						<td><?php echo $this->item->user->shipto->address_1 ?> <BR>
						<?php echo $this->item->user->shipto->address_2 ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
						<td><?php echo $this->item->user->shipto->city ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
						<td><?php echo $this->item->user->shipto->state ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
						<td><?php echo $this->item->user->shipto->zip ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
						<td><?php echo $this->item->user->shipto->country ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
						<td><?php echo $this->item->user->shipto->phone_1 ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_FAX') ?>:</td>
						<td><?php echo $this->item->user->shipto->fax ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
						<td><?php echo $this->item->user->shipto->email ?></td>
					</tr>
				</table>
				<!-- End ShipTo --> <?php } ?></td>
			</tr>
		</table>
		</fieldset>
		
		
		<fieldset class="adminform">
				<h2><?php echo JText::_('MYMUSE_ORDER_ITEMS') ?></h2>
		<?php $string = '
        		<table class="adminlist" width="600">
		<tr>
		<th><b>'. JText::_('Title') .'</b></th>
        ';
     $colspan = 3;
     if($this->params->get("my_show_sku")){ 
         $string .= '
		<th><b>'. JText::_('MYMUSE_SKU') .'</b></th>
        ';
        $colspan = 4;
     }
     $string .= '
		<th align="right"><b>'. JText::_('MYMUSE_PRICE') .'</b></th>
		<th align="right"><b>'. JText::_('MYMUSE_QUANTITY') .'</b></th>
		<th align="right"><b>'. JText::_('MYMUSE_SUBTOTAL') .'</b></th> 
		<th align="right"><b>'. JText::_('MYMUSE_DOWNLOADS') .'</b></th> 
		<th align="right"><b>'. JText::_('MYMUSE_EXPIRES') .'</b></th> 
		</tr>
		';
		

		  // LOOP THRU order_items
		  for ($i = 0; $i < count($this->item->items); $i++) { 
		      if ($i % 2){
		          $class = "row1";
		      }else{
		          $class = "row0";
		      }
		      if($this->item->items[$i]->end_date){
		      	$end_date = date("Y-m-d h:i:s",$this->item->items[$i]->end_date);
		      }else{
		      	$end_date = '';
		      }

			$string .= '
		    <tr class="'.$class .'">
		        <td>'. $this->item->items[$i]->product_name .'</td>
		        ';
		    if($this->params->get("my_show_sku")){ 
                $string .= '
		        <td>'. $this->item->items[$i]->product_sku .'</td>
                ';
            }
            $string .= '
		        <td align="right">'. MyMuseHelper::printMoney($this->item->items[$i]->product_item_price) .' </td>
		        <td align="right">'. $this->item->items[$i]->product_quantity .'</td>
		        <td align="right">'. MyMuseHelper::printMoney($this->item->items[$i]->subtotal) .'</td>
		        <td align="right">'. $this->item->items[$i]->downloads .'</td>
		        <td align="right">'. $end_date .'</td>
		       </tr>
		       ';
	 	} 

		if($this->item->discount > 0.00){
			$string .= '
			<tr class="'.$class .'">
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('MYMUSE_CART_ORIGINAL_SUBTOTAL').':</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->discount + $this->item->order_subtotal).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>
		    
		    <tr>
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('MYMUSE_SHOPPING_GROUP_DISCOUNT').':
		    	'.$this->item->user->shopper_group_name.' '.$this->item->user->shopper_group_discount.' %</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->discount).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>
		    
		    <tr>
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('MYMUSE_CART_NEW_SUBTOTAL').':</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->order_subtotal).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>
		';
		}

		if($this->params->get('my_use_coupons') && $this->item->coupon_name){
			$string .= '
		    <tr>
		    <td colspan="'.$colspan.'">'.JText::_("MYMUSE_COUPON")." : ". $this->item->coupon_name. ':
		        </td>
		        <td colspan="1" align="right"> - '. MyMuseHelper::printMoney($this->item->coupon_discount) .'
		        </td>
		        <td></td>
		    <td></td>
		    </tr>
		    ';
		}
	
		if ($this->params->get('my_use_shipping') && $this->item->order_shipping > 0) { 
			$string .= '
		    <tr>
		    <td colspan="'.$colspan.'" align="right">' .JText::_('MYMUSE_SHIPPING'). ':</td>
		    <td colspan="1" align="right">'. MyMuseHelper::printMoney($this->item->order_shipping). '
		    </td>
		    <td></td>
		    <td></td>
		    </tr>
		    ';
		}
		
		// TAXES
		if(@$this->item->tax_array){
			reset($this->item->tax_array);
		    while(list($key,$val) = each($this->item->tax_array)){
		    	$key = preg_replace("/_/"," ", $key);
		    	$string .= '
		        <tr>
		        <td colspan="'.$colspan.'" align="right">'. $key. '</td>
		        <td colspan="1" align="right">'. MyMuseHelper::printMoney($val). '</td>
		        <td></td>
		    	<td></td>
		        </tr>
		        ';
		    } 
		}
		
		$string .= '
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('MYMUSE_TOTAL'). ':</b></td>
		    <td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->order_total). ' 
		    ' .$this->item->order_currency.'</b></td>
		    <td></td>
		    <td></td>

		</tr>
		';
		if($this->item->reservation_fee > 0.00){
		$string .= '
			
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"></td>
		    <td colspan="1" align="right" class="textbox2"></td>
		    <td></td>
		    <td></td>
		</tr>
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('MYMUSE_RESERVATION_FEE'). ':</b></td>
		    <td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->reservation_fee). ' </b></td>
		    <td></td>
		    <td></td>
		</tr>
		';
		if($this->item->non_res_fee > 0.00){
			$string .= '
			<tr>
		    	<td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('MYMUSE_OTHER_CHARGES'). ':</b></td>
		    	<td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->non_res_fee). ' </b></td>
		    	<td></td>
		    	<td></td>
			</tr>
			
			';
			}
		$owing = $this->item->order_total - $this->item->paid_to_date; 
		$string .= '
		<tr>
			<td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('MYMUSE_PAID_TO_DATE'). ':</b></td>
			<td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->paid_to_date). ' </b></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('MYMUSE_OWING'). ':</b></td>
		    <td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($owing). ' </b></td>
		    <td></td>
		    <td></td>
		</tr>
		';
		}
		
		$string .= '
		</table>
		';
		echo $string;
		?>
        	</fieldset>

        	<?php if($this->item->downloadlink){ ?>
 		<tr>
            <td>
            <fieldset class="adminform">
			  <h2><?php echo JText::_('MYMUSE_DOWNLOADS') ?></h2>
			  <table class="admintable">
			    <tr>
			    <td width="600" valign="top">
        			<?php echo $this->item->downloadlink; ?> 
        		</td>
        		</tr>
        		<tr>
			    <td width="600" valign="top">
        			<a 
href="index.php?option=com_mymuse&view=order&layout=edit&task=resetDownloads&id=<?php echo $this->item->id; ?>">
<?php echo JText::_('MYMUSE_RESET_DOWNLOADS') ?></a>
        		</td>
        		</tr>
        	  </table>
        </fieldset>
        </tr>
<?php }?>
 </table>
 
</div>

		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'payments', JText::_('MYMUSE_PAYMENTS', true)); ?>
	
		<?php 
		//ORDER PAYMENTS
		?>

        <table cellspacing="0" cellpadding="0" border="0" width="800">
        <tr>
            <td valign="top" width="600">
            <fieldset class="adminform">
			  <h2><?php echo JText::_('MYMUSE_PAYMENTS') ?></h2>
			  <table class="adminlist">
			    <tr>
			  		<th><?php echo JText::_('MYMUSE_ID') ?></th>
			  		<th><?php echo JText::_('MYMUSE_DATE') ?></th>
			  		<th><?php echo JText::_('MYMUSE_METHOD') ?></th>
			  		<th><?php echo JText::_('MYMUSE_AMOUNT') ?></th>
			  		<th><?php echo JText::_('MYMUSE_CURRENCY') ?></th>
			  		<th><?php echo JText::_('MYMUSE_RATE') ?></th>
			  		<th><?php echo JText::_('MYMUSE_FEES') ?></th>
			  		<th><?php echo JText::_('MYMUSE_TRANS_ID') ?></th>
			  		<th><?php echo JText::_('MYMUSE_STATUS') ?></th>
			  		<th><?php echo JText::_('MYMUSE_DESCRIPTION') ?></th>
			  	</tr>
			  	<?php 
			  	if(count($this->item->order_payments)){
			  		foreach($this->item->order_payments as $payment){ 
			  	?>
			    <tr>
			    <td valign="top"><?php echo $payment->id ?></td>
			    <td valign="top"><?php echo $payment->date ?></td>
			    <td valign="top"><?php echo $payment->plugin ?> <?php echo $payment->institution ?></td>
			    <td valign="top"><?php echo $payment->amountin ?></td>
			    <td valign="top"><?php echo $payment->currency ?></td>
			    <td valign="top"><?php echo $payment->rate ?></td>
			    <td valign="top"><?php echo $payment->fees ?></td>
			    <td valign="top"><?php echo $payment->transaction_id ?></td>
			    <td valign="top"><?php echo $payment->transaction_status ?></td>
			    <td valign="top"><?php echo $payment->description ?></td>
        		</tr>
        		<?php } 
			  	} 
			  	
			  	if(!$print){
			  	
			  	?>
			  	<tr>
			    <td valign="top"><?php echo JText::_('ADD') ?></td>
			    <td valign="top" nowrap="nowrap">
				<?php echo $this->form->getLabel('payment_date'); ?>
				<?php echo $this->form->getInput('payment_date'); ?>
				</td>
			    <td valign="top"><?php echo $this->lists['plugins']; ?><br />
			    <?php echo JText::_('MYMUSE_OTHER'); ?><br />
			    <input type="text" name="payment_institution" value="" size="10" />
			    </td>
			    <td valign="top"><input type="text" name="payment_amountin" value="" size="6" /></td>
			    <td valign="top"><?php echo $this->lists['currencies']; ?></td>
			    <td valign="top"><input type="text" name="payment_rate" value="1.00000" size="5" /></td>
			    <td valign="top"><input type="text" name="payment_fees" value="0.00" size="5" /></td>
			    <td valign="top"><input type="text" name="payment_transaction_id" value="" /></td>
			    <td valign="top"><input type="text" name="payment_transaction_status" value="" size="5" /></td>
			    <td valign="top"><textarea name="payment_description" rows="3" cols="30" style="width:200px"></textarea></td>
        		</tr>
        		<?php } ?>
        	  </table>
        </fieldset>
        </tr>
    </table>
    <?php 
    if(!$print){
	
    }
	?>

		<?php echo JHtml::_('bootstrap.endTab'); ?>
		

<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
	</div>