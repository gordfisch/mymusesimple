 <?php 
 /**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
 // no direct access
defined('_JEXEC') or die('Restricted access');

$shopper 	= $this->shopper;
$params 	= $this->params;

if(!isset($shopper->profile['name']) && 
		( isset($shopper->profile['first_name']) && isset($shopper->profile['last_name']) )
	){
	$shopper->profile['name'] = $shopper->profile['first_name'].' '.$shopper->profile['last_name'];
	
}
//if($this->user->username == "buyer"){
	$fields = MyMuseHelper::getNoRegFields();
	foreach($fields as $field){
		if(!isset($shopper->profile[$field]) && isset($shopper->$field) && $shopper->$field != ''){
			$shopper->profile[$field] = $shopper->$field;
			
		}
	}
//}
$colspan = 1;
if($params->get('my_use_shipping') && isset($this->order->need_shipping)
			&& $this->order->need_shipping && isset($shopper->profile['shipping_first_name'])){
	$colspan = 2;
}
?>     <!-- Begin 2 column bill-ship to -->
        <h2><?php echo JText::_('MYMUSE_SHOPPER_INFORMATION') ?></h2>
		<table class="mymuse_cart">
        <tr>
            <td style="vertical-align:top"> <!-- Begin BillTo -->

            <table class="mymuse_cart" >
                <tr class="mymuse_cart_top">
                	<td class="mymuse_cart_top" colspan="2"><b><?php echo JText::_('MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_FULL_NAME') ?>:</td>
                	<td class="myfullname">
                	<?php echo $shopper->profile['name'] ?>
                	</td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_EMAIL') ?>:</td>
                	<td class="myemail"><?php echo $shopper->profile['email'] ?></td>
                </tr>
                
            <?php if(isset($shopper->profile)){ ?>
            
              <?php if(isset($shopper->profile['phone']) && $shopper->profile['phone'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_PHONE') ?>:</td>
                	<td class="myphone"><?php echo $shopper->profile['phone'] ?></td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['mobile']) && $shopper->profile['mobile'] != ''){ ?> 
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_MOBILE') ?>:</td>
                	<td class="myphone"><?php echo $shopper->profile['mobile'] ?></td>
                </tr>
              <?php } ?>
              
              <?php if(isset($shopper->profile['address1']) && $shopper->profile['address1'] !=''){ ?> 
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
            <?php  } ?>
            
            <?php if(isset($shopper->profile['postal_code']) && $shopper->profile['postal_code'] != ''){ ?>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td class="myzip"><?php echo $shopper->profile['postal_code'] ?></td>
                </tr>
              <?php } ?>
              
			<?php } //end if profile?>
                
            </table>
            <!-- End BillTo --> 
            </td>
        
            
    
        <?php 
        if($params->get('my_use_shipping') && isset($this->order->need_shipping) 
        		&& $this->order->need_shipping && isset($shopper->profile['shipping_first_name'])
        		&& $shopper->profile['shipping_first_name'] != ''){
        ?>
        <td style="vertical-align:top">
            <table class="mymuse_cart">
                <tr class="mymuse_cart_top">
                <td class="mymuse_cart_top" COLSPAN="2"><b><?php echo JText::_('MYMUSE_SHIPPING_ADDRESS') ?></b></td>
                </tr>
				<tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_NAME') ?>:</td>
                	<td class="mycity"><?php echo $shopper->profile['shipping_first_name']." ".$shopper->profile['shipping_last_name'] ?></td>
                </tr>
                <tr VALIGN=TOP>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ADDRESS') ?>:</td>
                	<td class="myaddress">
                	<?php echo $shopper->profile['shipping_address1'] ?>
                	<br />
                	<?php echo $shopper->profile['shipping_address2'] ?>
                	</td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_CITY') ?>:</td>
                	<td class="mycity"><?php echo $shopper->profile['shipping_city'] ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_STATE') ?>:</td>
                	<td class="myregion"><?php echo $shopper->profile['shipping_region_name'] ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_COUNTRY') ?>:</td>
                	<td class="mycountry"><?php echo $shopper->profile['shipping_country'] ?></td>
                </tr>
                <tr>
                	<td class="mobile-hide"><?php echo JText::_('MYMUSE_ZIP') ?>:</td>
                	<td class="myzip"><?php echo $shopper->profile['shipping_postal_code'] ?></td>
                </tr>
            </table>
            <!-- End ShipTo -->
		</td>
          <?php 
        }
        ?>
            <!-- End Customer Information --> 
        </tr>

        <?php 
       
        if($this->user->id 
        		&& $this->params->get('my_registration') != 'no_reg'
        		&& $this->user->username != 'buyer'
        		&& $this->task != 'notify'
        		&& $this->task != 'thankyou'
        		&& $this->task != 'confirm'
        		&& $this->task != 'makemail'
        		){ 
        		$url = JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid;
        		$return = base64_encode($url);
        	?>
        <tr>
        	<td colspan="2"><form method="post" action="" id="profile_form">
        	<input type="hidden" name="option" value="com_users">
        	<input type="hidden" name="view" value="profile">
        	<input type="hidden" name="layout" value="edit">
        	<input type="hidden" name="user_id" value="<?php echo $this->user->id;?>">
        	<input type="hidden" name="return" value="<?php echo $return;?>">
        	<input type="submit" name="submit" class="button" value="<?php echo JText::_("MYMUSE_EDIT_PROFILE");?>">
        	</form></td>
        </tr>
        <?php }?>
        </table>
        <br />
