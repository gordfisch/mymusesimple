<?php 
$url= "index.php?option=com_mymuse&view=product&layout=listtracks&id=".$this->item->id;
?>
<div class="row-fluid">
<div class="span12">
<div id="toolbar" class="btn-toolbar">
<div id="toolbar-apply" class="btn-wrapper">
<button class="btn btn-small btn-success" onclick="Joomla.submitbutton('product.apply')">
<span class="icon-apply icon-white"></span>
<?php echo JText::_('JAPPLY')?>
</button>
</div>
</div>
</div>
</div>
<h3><?php echo JText::_('MYMUSE_SAVE_THEN_ADD_TRACKS')?></h3>

<h2><a href="<?php echo $url;?>"><?php echo JText::_('MYMUSE_SHOW_TRACKS_LABEL')?></a></h2>





