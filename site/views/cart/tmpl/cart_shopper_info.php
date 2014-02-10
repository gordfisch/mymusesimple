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

$shopper 	= $this->shopper;
$params 	= $this->params;

?>     <!-- Begin 2 column bill-ship to -->
        <h2><?php echo JText::_('MYMUSE_SHOPPER_INFORMATION') ?></h2>

		<table class="mymuse_cart">
        <tr VALIGN=top>
            <td> <!-- Begin BillTo -->

            <table class="mymuse_cart" >
                <tr class="mymuse_cart_top">
                	<td class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                
                <tr>
                	<td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                	<td>
                	<?php echo $shopper->name ?>
                	</td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                	<td><?php echo $shopper->email ?></td>
                </tr>
                
            <?php if(isset($shopper->profile)){ ?>
              <?php if(isset($shopper->profile['phone']) && $shopper->profile['phone'] != ''){ ?> 
                <tr>
                	<td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                	<td><?php echo $shopper->profile['phone'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['address1']) && $shopper->profile['address1']){ ?> 
                <tr>
                	<td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td>
                	<?php echo $shopper->profile['address1'] ?>
                	
                	<?php echo @$shopper->profile['address2'] ?>
                	</td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['city']) && $shopper->profile['city'] != ''){ ?> 
                <tr>
                	<td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td><?php echo $shopper->profile['city'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['postal_code']) && $shopper->profile['postal_code'] != ''){ ?>
                <tr>
                	<td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td><?php echo $shopper->profile['postal_code'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['region_name']) && $shopper->profile['region_name'] != ''){ ?>
                <tr>
                	<td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td><?php echo $shopper->profile['region_name'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['country']) && $shopper->profile['country'] != ''){ ?>
                <tr>
                	<td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td><?php echo $shopper->profile['country'] ?></td>
                </tr>
            <?php  }
				} ?>
                
            </table>
            <!-- End BillTo --> </td>
        
            <td>
        <?php 
        if($params->get('my_use_shipping') && isset($shopper->shipto)){
        ?>
            <table class="mymuse_cart">
                <tr class="mymuse_cart_top">
                <th class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_SHIPPING_ADDRESS') ?></b></th>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_COMPANY') ?>:</td>
                	<td><?php echo $shopper->shipto->company ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                	<td>
                	<?php echo $shopper->shipto->first_name ?>
        
                	<?php echo $shopper->shipto->middle_name ?>
        
                	<?php echo $shopper->shipto->last_name ?>
                	</td>
                </tr>
                <tr VALIGN=TOP>
                	<td><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td>
                	<?php echo $shopper->shipto->address_1 ?>
                	<BR>
                	<?php echo $shopper->shipto->address_2 ?>
                	</td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td><?php echo $shopper->shipto->city ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td><?php echo $shopper->shipto->state ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td><?php echo $shopper->shipto->zip ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td><?php echo $shopper->shipto->country ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                	<td><?php echo $shopper->shipto->phone_1 ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_FAX') ?>:</td>
                	<td><?php echo $shopper->shipto->fax ?></td>
                </tr>
                <tr>
                	<td><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                	<td><?php echo $shopper->shipto->email ?></td>
                </tr>
            </table>
            <!-- End ShipTo -->

          <?php 
        }
        ?></td>
            <!-- End Customer Information --> 
        </tr>
        </table>
        <br />
