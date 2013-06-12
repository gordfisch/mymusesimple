<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
$height 	= $params->get('product_image_height',50);
?>
	
<?php if(count($list)):?>	
<div id="mod_mymuse_latest_<?php echo $params->get('module_number'); ?>">
<?php if($params->get('display')){  //vertical ?>
<ul class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) : ?>
	<li class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php if ($params->get('show_product_image') && $item->list_image) : ?>
	    <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<img
			height="<?php echo $height; ?>"
			src="<?php echo $item->list_image;?>"
			hspace="6" border="0" alt="<?php echo $item->list_image;?>" /></a><br />
	<?php endif; ?>
	<?php if ($params->get('show_artist_name') && $item->artist_name) : ?>
			<?php echo JText::_('MYMUSE_ARTIST'); ?> : <a href="<?php echo $item->artist_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->artist_name; ?></a><br />
	<?php endif; ?>
	<?php if ($params->get('show_product_name') && $item->product_name) : ?>
			<?php echo JText::_('MYMUSE_ALBUM'); ?> : <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->product_name; ?></a><br />
	<?php endif; ?>
    <?php if ($params->get('type_shown') == 'tracks') : ?>
		<?php echo JText::_('MYMUSE_TRACK'); ?> : <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->title; ?></a><br />
	<?php endif; ?>
	<?php if ($params->get('show_track_preview') && $item->flash) : ?>
			<?php echo $item->flash; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_number') && $params->get('type_search') == "pa.hits") : ?>
			<?php echo JText::_('MYMUSE_HITS'); ?> : <?php echo $item->hits; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_number') && $params->get('type_search') == "p.file_downloads") : ?>
			<?php echo JText::_('MYMUSE_DOWNLOADS'); ?> : <?php echo $item->file_downloads; ?><br />
	<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
<?php }else{ //horizontal ?>
<table>
<tr class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) : ?>
	
	<td>
	<?php if ($params->get('show_product_image') && $item->image) : ?>
	    <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<img
			height="<?php echo $height; ?>"
			src="images/stories/albums/<?php echo $item->image;?>"
			hspace="6" border="0" alt="<?php echo $item->image;?>" /></a><br />
	<?php endif; ?>
	<?php if ($params->get('show_artist_name') && $item->artist_name) : ?>
			<?php echo JText::_('MYMUSE_ARTIST'); ?> : <a href="<?php echo $item->artist_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->artist_name; ?></a><br />
	<?php endif; ?>
	<?php if ($params->get('show_product_name') && $item->product_name) : ?>
			<?php echo JText::_('MYMUSE_ALBUM'); ?> : <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->product_name; ?></a><br />
	<?php endif; ?>
    <?php if ($params->get('type_shown') == 'tracks') : ?>
		<?php echo JText::_('MYMUSE_TRACK'); ?> : <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->title; ?></a><br />
	<?php endif; ?>
	<?php if ($params->get('show_track_preview') && $item->flash) : ?>
			<?php echo $item->flash; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_number') && $params->get('type_search') == "p.hits") : ?>
			<?php echo JText::_('MYMUSE_HITS'); ?> : <?php echo $item->hits; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_number') && $params->get('type_search') == "p.file_downloads") : ?>
			<?php echo JText::_('MYMUSE_DOWNLOADS'); ?> : <?php echo $item->file_downloads; ?><br />
	<?php endif; ?>
	</td>
	
<?php endforeach; ?>
</tr>
</table>

<?php } ?>
</div>
<?php endif; ?>