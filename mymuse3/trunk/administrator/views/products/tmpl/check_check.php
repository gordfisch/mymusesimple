<h2>File Check</h2><?php
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_ADMIN_PATH')){
	define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
}



require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );

foreach($this->products as $product){
	echo $product->title."<br />";
	foreach($product->items as $item){
		$artist_alias = MyMuseHelper::getArtistAlias($product->id,1);
		$album_alias = MyMuseHelper::getAlbumAlias($product->id,1);
		$jason = json_decode($product->file_name);
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
				$filenames[] = $product->alias;
			}else{
				$filenames[] = $product->filename;
			}
		}
		foreach($filenames[] as $f){
			echo ">>>".$item->title." : $artist_alias : $album_alias : $f<br />";
		}
	}
}
?>

