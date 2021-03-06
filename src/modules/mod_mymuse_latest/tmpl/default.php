<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
$height 	= $params->get('product_image_height',0);
?>
<style>
.mymuse_latest .jp-play, .mymuse_latest .jp-pause {
	width: 16px;
}
</style>
<?php if(count($list)):?>	
<div id="mod_mymuse_latest_<?php echo $params->get('module_number'); ?>">
<?php if($params->get('display')){  //vertical ?>

<?php 
if($params->get('type_shown') == "tracks" && $params->get('show_track_preview')){
	?>
	<!-- The jPlayer div must not be hidden. Keep it at the root of the body element to avoid any such problems. -->
			<div id="jquery_jplayer_m" class="cp-jplayer"></div>

			<!-- The container for the interface can go where you want to display it. Show and hide it as you need. -->

			<div id="cp_container_1" class="cp-container">
				<div class="cp-buffer-holder"> <!-- .cp-gt50 only needed when buffer is > than 50% -->
					<div class="cp-buffer-1"></div>
					<div class="cp-buffer-2"></div>
				</div>
				<div class="cp-progress-holder"> <!-- .cp-gt50 only needed when progress is > than 50% -->
					<div class="cp-progress-1"></div>
					<div class="cp-progress-2"></div>
				</div>
				<div class="cp-circle-control"></div>
				<ul class="cp-controls">
					<li><a class="cp-play" tabindex="1">play</a></li>
					<li><a class="cp-pause" style="display:none;" tabindex="1">pause</a></li> <!-- Needs the inline style here, or jQuery.show() uses display:inline instead of display:block -->
				</ul>
			</div>


			<div id="jplayer_inspector_m"></div>
			<?php 
}
?>
<div style="clear: both;"></div>
<ul class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) : ?>
	<li class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php if ($params->get('show_product_image') && $item->list_image) : ?>
	    <a href="<?php echo $item->product_link; ?>" class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
			<img
			<?php if($height){
				?>style="width: <?php echo $height; ?>px"
			<?php }?>
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
			<?php echo $item->title; ?></a>
	<?php endif; ?>
	
	<?php if ($params->get('type_shown') == 'tracks') : ?>
	<br />
	<?php endif; ?>
	
	<?php if ($params->get('show_number') && $params->get('type_search') == "pa.hits") : ?>
			<?php echo JText::_('MYMUSE_HITS'); ?> : <?php echo $item->hits; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_number') && $params->get('type_search') == "p.file_downloads") : ?>
			<?php echo JText::_('MYMUSE_DOWNLOADS'); ?> : <?php echo $item->file_downloads; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_number') && $params->get('type_search') == "s.sales") : ?>
			<?php echo JText::_('MYMUSE_SALES'); ?> : <?php echo $item->sales; ?><br />
	<?php endif; ?>
	<?php if ($params->get('show_track_preview') && $item->flash) : ?>
			<?php echo $item->flash; ?>
	<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
<?php }else{ //horizontal ?>

<table cellpadding="5">
<tr class="mymuse_latest<?php echo $params->get('moduleclass_sfx'); ?>">
	<td valign="top">
	<?php 
if($params->get('type_shown') == "tracks" && $params->get('show_track_preview')){
	?>
	<!-- The jPlayer div must not be hidden. Keep it at the root of the body element to avoid any such problems. -->
			<div id="jquery_jplayer_m" class="cp-jplayer"></div>

			<!-- The container for the interface can go where you want to display it. Show and hide it as you need. -->

			<div id="cp_container_1" class="cp-container">
				<div class="cp-buffer-holder"> <!-- .cp-gt50 only needed when buffer is > than 50% -->
					<div class="cp-buffer-1"></div>
					<div class="cp-buffer-2"></div>
				</div>
				<div class="cp-progress-holder"> <!-- .cp-gt50 only needed when progress is > than 50% -->
					<div class="cp-progress-1"></div>
					<div class="cp-progress-2"></div>
				</div>
				<div class="cp-circle-control"></div>
				<ul class="cp-controls cp-horizontal">
					<li><a class="cp-play" tabindex="1">play</a></li>
					<li><a class="cp-pause" style="display:none;" tabindex="1">pause</a></li> <!-- Needs the inline style here, or jQuery.show() uses display:inline instead of display:block -->
				</ul>
			</div>


			<div id="jplayer_inspector_m"></div>
			<?php 
}
?>
	</td>
	
<?php foreach ($list as $item) : ?>
	

	<td>
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