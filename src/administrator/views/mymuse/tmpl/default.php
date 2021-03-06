<?php defined('_JEXEC') or die('Restricted access'); 
?>

	<div id="j-sidebar-container" class="span2">
	<?php
	echo $this->sidebar; 
	?>
	</div>

	<div id="j-main-container" class="span10">
	<div class="clearfix"></div>
		<fieldset class="adminform">
				<legend><?php echo JText::_('MYMUSE_FOR_JOOMLA'); ?></legend>
		<table>
		<tr>
			<td align="left" width="100%"><h2><?php echo JText::_("MYMUSE_WELCOME"); ?> 
			<?php echo JText::_("MYMUSE_VERSION"); ?> <?php echo $this->params->get('my_version'); ?></h2>
			<br />
			<?php echo JText::_("MYMUSE_TAGLINE"); ?></td>
		</tr>
		<!-- tr>
			<td align="middle" width="100%"><h3><?php echo LiveUpdate::getIcon(); ?></h3></td>
		</tr>
		<tr -->
			<td align="left" width="100%">
			<p><b><?php echo JText::_("MYMUSE_QUICKSTART"); ?></b></p>
			<ul>
			<li><b><a href="index.php?option=com_mymuse&task=addSampleData"><?php echo JText::_("MYMUSE_ADD_SAMPLE_DATA"); ?></a></b> 

			</li>
			<li><b><a href="index.php?option=com_mymuse&view=store&layout=edit&id=1"><?php echo JText::_("MYMUSE_STORE"); ?></a></b> 
			<?php echo JText::_("MYMUSE_STORE_DESC"); ?>
			</li>
			<li><b><a href="index.php?option=com_plugins&view=plugins&filter_folder=mymuse"><?php echo JText::_("MYMUSE_PLUGINS"); ?></a></b> 
			
			<?php echo JText::_("MYMUSE_PLUGINS_DESC"); ?><br />

			<b><a href="index.php?option=com_plugins&view=plugins&filter_folder=user"><?php echo JText::_("MYMUSE_PLUGINS_USER"); ?></a></b> 
			<?php echo JText::_("MYMUSE_PLUGINS_USER_DESC"); ?><br />
			</li>
			
			
			<li><b><a  href="index.php?option=com_config&amp;view=component&amp;component=com_mymuse&amp;"><?php echo JText::_("MYMUSE_PARAMETERS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_PARAMETERS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_categories&extension=com_mymuse"><?php echo JText::_("MYMUSE_CATEGORIES_ARTISTS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_CATEGORIES_ARTISTS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&view=products"><?php echo JText::_("MYMUSE_PRODUCTS_ALBUMS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_PRODUCTS_ALBUMS_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&view=taxrates"><?php echo JText::_("MYMUSE_TAXES"); ?></a></b> 
			<?php echo JText::_("MYMUSE_TAXES_DESC"); ?></li>
			
			<li><b><a href="index.php?option=com_mymuse&task=addEuroTax"><?php echo JText::_("MYMUSE_ADD_EURO_TAXES"); ?></a></b> 
			</li>
			
			<li><b><a href="index.php?option=com_menus&task=view"><?php echo JText::_("MYMUSE_MENUS"); ?></a></b> 
			<?php echo JText::_("MYMUSE_MENUS_DESC"); ?></li>
	
			<li><b><?php echo JText::_("MYMUSE_HELP"); ?></b> 
			<?php echo JText::_("MYMUSE_HELP_DESC"); ?>
			<br /><br /></li>
			<li><?php echo JText::_("MYMUSE_CONTACT"); ?> <a href="mailto:info@joomlamymuse.com">info@joomlamymuse.com</a> <?php echo JText::_("MYMUSE_WEBSITE"); ?> <a targete="_new" href="https://www.joomlamymuse.com">www.joomlamymuse.com</a>
			</ul>
			</td>
		</tr>
		

		</table>
		</fieldset>
	
	</div>
</form>