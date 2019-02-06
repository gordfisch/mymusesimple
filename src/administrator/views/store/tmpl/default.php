<?php defined('_JEXEC') or die('Restricted access'); 
$params = $this->form->getFieldsets('params');
print_pre($params);
?>

		<fieldset class="adminform">
				<legend><?php echo JText::_('MYMUSE_FOR_JOOMLA'); ?></legend>
		<table>
		<tr>
			<td align="left" width="100%"><h3><?php echo JText::_("MYMUSE_WELCOME"); ?> <?php echo JText::_("MYMUSE_VERSION"); ?> <?php echo $params->get('my_version'); ?></h3><br />
			<?php echo JText::_("MYMUSE_TAGLINE"); ?></td>
		</tr>

		<tr>
			<td align="left" width="100%">
			<p><b><?php echo JText::_("MYMUSE_QUICKSTART"); ?></b></p>
			<ul>
			
			<li><b><a href="index.php?option=com_mymuse&controller=store&task=edit&cid[]=1"><?php echo JText::_("MYMUSE_STORE"); ?></a></b> 
			<?php echo JText::_("MYMUSE_STORE_DESC"); ?>
			</li>
			<li><b><a href="index.php?option=com_plugins"><?php echo JText::_("MYMUSE_PAYMENT"); ?></a></b> 
			<?php echo JText::_("MYMUSE_PAYMENT_DESC"); ?><br />
			<?php echo JText::_("MYMUSE_PAYPAL_DESC"); ?><br />
			<?php echo JText::_("MYMUSE_STORE_PAYPAL_IPN"); ?>
			</li>
			
			<li><b><a href="index.php?option=com_modules"><?php echo JText::_("MYMUSE_MODULES"); ?></a></b> 
			<?php echo JText::_("MYMUSE_MODULES_DESC"); ?></li>
			
			<li><b><?php echo JText::_("MYMUSE_PARAMETERS"); ?></b> 
			<?php echo JText::_("MYMUSE_PARAMETERS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_sections&scope=com_mymuse"><?php echo JText::_("MYMUSE_SECTIONS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_SECTIONS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&controller=category"><?php echo JText::_("MYMUSE_CATEGORIES_ARTISTS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_CATEGORIES_ARTISTS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&controller=product"><?php echo JText::_("MYMUSE_PRODUCTS_ALBUMS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_PRODUCTS_ALBUMS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&controller=tax_rate"><?php echo JText::_("MYMUSE_TAXES"); ?></a></b> 
			<?php echo JText::_("MYMUSE_TAXES_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&controller=shipping"><?php echo JText::_("MYMUSE_SHIPPING"); ?></a></b> 
			<?php echo JText::_("MYMUSE_SHIPPING_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&controller=shopper"><?php echo JText::_("MYMUSE_SHOPPERS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_SHOPPERS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&controller=order"><?php echo JText::_("MYMUSE_ORDERS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_ORDERS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_menus&task=view&menutype=mainmenu"><?php echo JText::_("MYMUSE_MENUS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_MENUS_DESC"); ?></li>
	
			<li><b><?php echo JText::_("MYMUSE_HELP"); ?></b> 
			<?php echo JText::_("MYMUSE_HELP_DESC"); ?>
			<br /><br /></li>
			<li><?php echo JText::_("MYMUSE_CONTACT"); ?> <a href="mailto:info@joomlamymuse.com">info@joomlamymuse.com</a> <?php echo JText::_("MYMUSE_WEBSITE"); ?> <a href="http://www.joomlamymuse.com">www.joomlamymuse.com</a>
			</ul>
			</td>
		</tr>
		
		<tr>
			<td align="left" width="100%">
			<p><b><?php echo JText::_("MYMUSE_SAMPLE_DATA"); ?></b></p>
			<ul>
			<li><b><a href="index.php?option=com_mymuse&task=addSampleData"><?php echo JText::_("MYMUSE_ADD_SAMPLE_DATA"); ?></a></b> 
			</li>
			<li><b><a href="index.php?option=com_mymuse&task=addGenres"><?php echo JText::_("MYMUSE_ADD_GENRES"); ?></a></b> 
			</li>
			</ul>
			</td>
		</tr>
		</table>
		</fieldset>
