<h2>File Check</h2><?php
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_ADMIN_PATH')){
	define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
}
$params 	= MyMuseHelper::getParams();


require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );
?>
<style>

</style>
<table>
<?php 
foreach($this->products as $product){
	echo "<tr><td colspan='3'>". $product->id ." <b>".$product->title."</b></td></tr>";
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
			if(file_exists($full_path)){
				$class = "alert alert-success";
				$result = JText::_('MYMUSE_FOUND');
			}else{
				$class = "alert alert-danger";
				$result = JText::_('MYMUSE_NOT_FOUND');
			}
			echo "<tr><td>----</td><td>Preview</td><td class='$class'>$full_path</td></tr>";
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
			if(file_exists($full_path)){
				$class = "alert alert-success";
				$result = JText::_('MYMUSE_FOUND');
			}else{
				$class = "alert alert-danger";
				$result = JText::_('MYMUSE_NOT_FOUND');
			}
			echo "<tr><td>----</td><td>Download</td><td class='$class'>$full_path</td></tr>";
		}
		echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	}
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
}
?>
</table>
