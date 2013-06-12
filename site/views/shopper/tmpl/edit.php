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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$store			= $this->store;
$shopper		= $this->shopper;
$user			= $this->user;
$lists			= $this->lists; 
$return			= $this->return; 
$countrystates	= $this->countrystates;
$Itemid 		= $this->Itemid;
$params			= $this->params;
		?>
		
		
		<script type="text/javascript">
        <!--

		var countrystates = new Array;
		<?php
		$i = 0;
		foreach ($countrystates as $k=>$items) {
			foreach ($items as $v) {
				echo "countrystates[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
			}
		}
		?>

		
        function check_registration(that){
        

            //ALL FIELDS
            //username
            //password_1
            //password_2
            //first_name
            //last_name
            //address_1
            //address_2
            //city
            //zip
            //phone_1
            //fax
            //email

            
<?php if($params->get('my_registration') == "full" || $params->get('my_registration') == "joomla"){ ?>

            //check for username
            if(that.username != null && that.username.value == ""){
                    alert("<?php echo JText::_('MYMUSE_USERNAME_IS_REQUIRED') ?>");
                    return false;
            }

            //check for password matches
            if(that.password.value != that.password2.value){
                    alert("<?php echo JText::_('MYMUSE_PASSWORDS_DO_NOT_MATCH') ?>");
                    return false;
            }

<?php } ?>
            
            //check for user_email
            if(that.email != null && that.email.value == ""){
                    alert("<?php echo JText::_('MYMUSE_EMAIL_IS_REQUIRED') ?>");
                    return false;
            }
            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

            if(reg.test(that.email.value) == false) {
               alert('<?php echo JText::_('MYMUSE_EMAIL_INVALID') ?>');
               return false;
            }
                     
            //check for first_name
            if(that.first_name != null && that.first_name.value == ""){
                    alert("<?php echo JText::_('MYMUSE_FIRST_NAME_IS_REQUIRED') ?>");
                    return false;
            }
            //check for last_name
            if(that.last_name != null && that.last_name.value == ""){
                    alert("<?php echo JText::_('MYMUSE_LAST_NAME_IS_REQUIRED') ?>");
                    return false;
            }
            //check for country
            if(that.country != null && that.country.value == "0"){
                    alert("<?php echo JText::_('MYMUSE_COUNTRY_IS_REQUIRED') ?>");
                    return false;
            }
<?php if($params->get('my_state_required')){ ?>
            //check for state
            if(that.state != null && that.state.value == "0"){
                    alert("<?php echo JText::_('MYMUSE_STATE/PROVINCE_IS_REQUIRED') ?>");
                    return false;
            }
<?php } ?>
<?php if($params->get('my_registration') == "full"){ ?>
   

            //check for address_1
            if(that.address_1 != null && that.address_1.value == ""){
                    alert("<?php echo JText::_('MYMUSE_ADDRESS_IS_REQUIRED') ?>");
                    return false;
            }
            //check for city
            if(that.city != null && that.city.value == ""){
                    alert("<?php echo JText::_('MYMUSE_CITY_IS_REQUIRED') ?>");
                    return false;
            }

            //check for zip
            if(that.zip != null && that.zip.value == ""){
                    alert("<?php echo JText::_('MYMUSE_ZIP/POSTAL_CODE_IS_REQUIRED') ?>");
                    return false;
            }
            
<?php } ?>

<?php if($params->get('my_termsofservice')){ ?>
			//check for termsofservice
			if(that.terms_of_service != null && that.terms_of_service.checked == false){
        		alert("<?php echo JText::_('MYMUSE_TERMSOFSERVICE_REQUIRED') ?>");
        		return false;
			}

<?php } ?>
            
        return true;
        
        }
        
        // -->
        </SCRIPT>
		<!-- Registration form -->
        <form ACTION="index.php" method="post" name="adminForm" onSubmit="return check_registration(this);">
        <div class="componentheading"><?php echo JText::_('MYMUSE_EDIT_PROFILE') ?></div>
		<div class="message"><?php echo JText::_('MYMUSE_MAKE_CHANGES_AND_SAVE') ?></div>
		
		
		<table cellpadding="3" cellspacing="0" border="0" width="100%" class="contentpane">
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_FIRST_NAME'); ?>:<span class="mymuse_msg">*</span></td>
				<td><input class="inputbox" CLASS="submit" type="text" name=first_name
					maxlength="32" size="22" value="<?php echo $shopper->first_name ?>" /></td>
			</tr>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_LAST_NAME'); ?>:<span class="mymuse_msg">*</span></td>
				<td><input class="inputbox" type="text" name=last_name maxlength="32"
					size="22" value="<?php echo $shopper->last_name ?>" /></td>
			</tr>
			
<?php if($params->get('my_registration') == "full" || $params->get('my_registration') == "joomla"){ ?>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_USERNAME'); ?>:<span class="mymuse_msg">*</span></td>
				<td width=400><input class="inputbox" type="text" name="username"
					maxlength="32" size="22" value="<?php echo $user->username ?>" /></td>
			</tr>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_PASSWORD'); ?>:<span class="mymuse_msg">*</span></td>
				<td><input class="inputbox" type="password" name="password"
					MAXLENGTH="32" SIZE="22" value="" /></td>
			</tr>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_CONFIRM_PASSWORD'); ?>:<span class="mymuse_msg">*</span></td>
				<td><input class="inputbox" type="password" name=password2
					maxlength="32" size="22" value="" /></td>
			</tr>
<?php } ?>			
			
			<tr>
            	<td align=right nowrap><?php echo JText::_('MYMUSE_EMAIL'); ?>:<span class="mymuse_msg">*</span></td>
            	<td><input class="inputbox" type="text" name="email" maxlength="32" size="28" value="<?php echo $user->email ?>" /></td>
            </tr>
            
<?php if($params->get('my_registration') == "full"){ ?>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_COMPANY'); ?>:</td>
				<td><input class="inputbox" type="text" name=company maxlength="64"
					size="22" value="<?php echo $shopper->company ?>" /></td>
			</tr>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_ADDRESS'); ?>:<span class="mymuse_msg">*</span></td>
				<td><input class="inputbox" type="text" name=address_1 maxlength="64"
					size="22" value="<?php echo $shopper->address_1 ?>" /></td>
			</tr>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_ADDRESS_2'); ?>:</td>
				<td><input class="inputbox" type="text" name=address_2 maxlength="64"
					size="22" value="<?php echo $shopper->address_2 ?>" /></td>
			</tr>
			<tr>
            	<td align=right nowrap><?php echo JText::_('MYMUSE_CITY'); ?>:<span class="mymuse_msg">*</span></td>
            	<td><input class="inputbox" type="text" name="city" maxlength="32" size="22" value="<?php echo $shopper->city ?>" /></td>
            </tr>
            
<?php } ?>

			<tr>
				<td align=right nowrap><?php echo JText::_( 'MYMUSE_COUNTRY' ) ?>:<span class="mymuse_msg">*</span></td>
				<td><?php  echo $lists['country']; ?></td>
			</tr>
			<tr>
				<td align=right nowrap><?php echo JText::_( 'MYMUSE_STATE/PROVINCE' ) ?>:<span class="mymuse_msg">*</span></td>
				<td><?php  echo $lists['state']; ?></td>
			</tr>
			
<?php if($params->get('my_registration') == "full"){ ?>
			
			<tr>
            	<td align=right nowrap><?php echo JText::_('MYMUSE_PHONE'); ?>:</td>
            	<td><input class="inputbox" type="text" name="phone_1" maxlength="32" size="22" value="<?php echo $shopper->phone_1 ?>" /></td>
            </tr>
            <tr>
            	<td align=right nowrap><?php echo JText::_('MYMUSE_ZIP'); ?>:<span class="mymuse_msg">*</span></td>
            	<td><input class="inputbox" type="text" name="zip" maxlength="32" size="22" value="<?php echo $shopper->zip ?>" /></td>
            </tr>
            <tr>
            	<td align=right nowrap><?php echo JText::_('MYMUSE_FAX'); ?>:</td>
            	<td><input class="inputbox" type="text" name="fax" maxlength="32" size="22" value="<?php echo $shopper->fax ?>" /></td>
            </tr>
<?php } ?>
<?php if($params->get('my_termsofservice') == "1"){ ?>
			<tr>
				<td align=right nowrap><?php echo JText::_('MYMUSE_ACCEPT_TERMSOFSERVICE'); ?> 
				<?php if($params->get('my_termsofservice_link') != ""){ ?>
					<a href="<?php echo $params->get('my_termsofservice_link'); ?>">
				<?php } ?>
				<?php echo JText::_('MYMUSE_TERMS_OF_SERVICE'); ?>
				<?php if($params->get('my_termsofservice_link') != ""){ ?>
				</a>
				<?php } ?> :
				</td>
				<td><input class="inputbox" type="checkbox" name="terms_of_service" value="1" 
				<?php if($shopper->terms_of_service == "1"){ echo "CHECKED=CHECKED"; } ?> /></td>
			</tr>


<?php } ?>			

            <!-- UserMeta -->
            <tr>
            	<td>* required</td>
            	<td><input class="button" type="submit" name="Register" value="<?php echo JText::_('MYMUSE_SAVE') ?>" /></td>
            </tr>
        
        </table>
        <?php if($this->return){ ?>
        	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
        <?php } ?>
        <input type="hidden" name="id" value="<?php  echo $user->id; ?>" />
        <input type="hidden" name="shopperid" value="<?php  echo $shopper->id; ?>" />
        <input type="hidden" name="option" value="com_mymuse" />
        <input type="hidden" name="task" value="shoppersave" />
        <?php echo JHTML::_( 'form.token' ); ?>
        </FORM>
