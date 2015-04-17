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
if($params->get('my_registration') == "no_reg"){
	$fields = MyMuseHelper::getNoRegFields();

	foreach($fields as $field){
		if(!isset($shopper->profile[$field]) && isset($shopper->$field) && $shopper->$field != ''){
			$shopper->profile[$field] = $shopper->$field;
		}
	}
}

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
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                	<td class="myfullname">
                	<?php echo $shopper->name ?>
                	</td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                	<td class="myemail"><?php echo $shopper->email ?></td>
                </tr>
                
            <?php if(isset($shopper->profile)){ ?>
              <?php if(isset($shopper->profile['phone']) && $shopper->profile['phone'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                	<td class="myphone"><?php echo $shopper->profile['phone'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['address1']) && $shopper->profile['address1']){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td class="myaddress">
                	<?php echo $shopper->profile['address1'] ?>
                	
                	<?php echo @$shopper->profile['address2'] ?>
                	</td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['city']) && $shopper->profile['city'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td class="mycity"><?php echo $shopper->profile['city'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['postal_code']) && $shopper->profile['postal_code'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td class="myzip"><?php echo $shopper->profile['postal_code'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['region_name']) && $shopper->profile['region_name'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td class="myregion"><?php echo $shopper->profile['region_name'] ?></td>
                </tr>
              <?php } ?>
              <?php if(isset($shopper->profile['country']) && $shopper->profile['country'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td class="mycountry"><?php echo $shopper->profile['country'] ?></td>
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
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_COMPANY') ?>:</td>
                	<td class="mycompany"><?php echo $shopper->shipto->company ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                	<td>
                	<?php echo $shopper->shipto->first_name ?>
        
                	<?php echo $shopper->shipto->middle_name ?>
        
                	<?php echo $shopper->shipto->last_name ?>
                	</td>
                </tr>
                <tr VALIGN=TOP>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td class="myaddress">
                	<?php echo $shopper->shipto->address_1 ?>
                	<BR>
                	<?php echo $shopper->shipto->address_2 ?>
                	</td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td class="mycity"><?php echo $shopper->shipto->city ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td class="myregion"><?php echo $shopper->shipto->state ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td class="myzip"><?php echo $shopper->shipto->zip ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td class="mycountry"><?php echo $shopper->shipto->country ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                	<td class="myphone"><?php echo $shopper->shipto->phone_1 ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_FAX') ?>:</td>
                	<td class="myfax"><?php echo $shopper->shipto->fax ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                	<td class="myemail"><?php echo $shopper->shipto->email ?></td>
                </tr>
            </table>
            <!-- End ShipTo -->

          <?php 
        }
        ?></td>
            <!-- End Customer Information --> 
        </tr>
        <?php 
       
        if($this->user->id 
        		&& $this->params->get('my_registration') != 'no_reg'
        		&& $this->task != 'notify'
        		&& $this->task != 'thankyou'
        		
        		){ 
        		$url = JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid;
        		$return = base64_encode($url);
        	?>
        <tr>
        	<td colspan="2"><form method="post" action="" id="profile_form">
        	<input type="hidden" name="option" value="com_users">
        	<input type="hidden" name="task" value="profile.edit">
        	<input type="hidden" name="user_id" value="<?php echo $this->user->id;?>">
        	<input type="hidden" name="return" value="<?php echo $return;?>">
        	<input type="submit" name="submit" class="button" value="<?php echo JText::_("MYMUSE_EDIT_PROFILE");?>">
        	</form></td>
        </tr>
        <?php }?>
        </table>
        <br />
