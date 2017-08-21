<?php
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_ADMIN_PATH')){
	define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
}
$params 	= MyMuseHelper::getParams();

$missing = $this->products['missing'];
unset($this->products['missing']);
require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
?>
<style>

</style>

<form action="<?php echo JRoute::_('index.php?option=com_mymuse&view=products'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="j-sidebar-container" class="span2">
	<?php
	echo $this->sidebar; 
	?>
	</div>
	<div style="clear: both'"></div>
	<div id="j-main-container" class="span10">
	
	<h2>File Check</h2>
<table>
<?php 
foreach($this->products as $product){
	echo "<tr><td colspan='3'>". $product->id .' <b><a href="index.php?option=com_mymuse&view=product&layout=listtracks&id='.$product->id.'">'.$product->title."</b></td></tr>";
	foreach($product->items as $item){
		echo "<tr><td>&nbsp;</td><td>". $item->id ."</td><td>". $item->title ."</td></tr>";
		
		$filenames = array();
		$previews = array();
		$path = MyMuseHelper::getDownloadPath($product->id,1);
		$preview_path = MyMuseHelper::getSitePath($product->id,1);
		
		if($item->file_preview != ''){
			$previews[] = $item->file_preview;
		}
		if($item->file_preview_2 != ''){
			$previews[] = $item->file_preview_2;
		}
		if($item->file_preview_3 != ''){
			$previews[] = $item->file_preview_3;
		}
		if($item->file_preview_4 != ''){
			$previews[] = $item->file_preview_4;
		}
		foreach($previews as $p){
			$full_path = $preview_path.$p;
			echo "<tr><td>----</td><td>Preview</td><td class='".$item->preview[$p]['class']."'>$full_path</td></tr>";
		}
		
		
		
		$jason = json_decode($item->file_name);
		if(is_array($jason)){
			foreach($jason as $j){
				if($params->get('my_encode_filenames')){
					$filenames[] = $j->file_alias;
				}else{
					$filenames[] = $j->file_name;
				}
			}
		}else{
			if($params->get('my_encode_filenames')){
				$filenames[] = $item->alias;
			}else{
				$filenames[] = $item->file_name;
			}
		}
		foreach($filenames as $f){
			$full_path = $path.$f;
			echo "<tr><td>----</td><td>Download</td><td class='".$item->download[$f]['class']."'>$full_path</td></tr>";
		}
		echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	}
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
}
?>
</table>

<?php if(count($missing)){ ?>
<h2>Missing!</h2>
	<?php foreach($missing as $m){ 
		echo '<p class="alert alert-danger">'.$m.'</p>';
		
	}?>
	
<?php }?>
</div>