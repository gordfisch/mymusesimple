<h2><?php echo JText::_('MYMUSE_CHOOSE_FORMAT'); ?></h2>

<?php
foreach ($this->params->get('my_formats') as $format){
	echo '<a class="button" 
		href="index.php?option=com_mymuse&view=product&layout=listtracks&id='.$this->item->id.'&task=product.uploadtrack&subtype=file&myformat='.$format.'">'.$format.'</a></br>';
}

?>
