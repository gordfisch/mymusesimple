<?php
/**
 * @version		$Id:$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author email	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');
jimport( 'joomla.html.parameter' );
JPlugin::loadLanguage( 'plg_mymuse_audio_html5', JPATH_ADMINISTRATOR );

/**
* MyMuse Player  plugin http://www.codebasehero.com/2011/07/html5-music-player-updated/
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseAudio_mp extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	 * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgMymuseAudio_mp(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		$document = &JFactory::getDocument();
        if($this->params->get('my_include_jquery', 0)){
			$js_path = "http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js";
			$document->addScript( $js_path );
        }
        $site_url = preg_replace("#administrator/#","",JURI::base());
        $js_path = $site_url.'plugins'.DS.'mymuse'.DS."audio_mp".DS.'plugin'.DS.'jquery-jplayer'.DS.'jquery.jplayer.js';
        $document->addScript( $js_path );
        $js_path = $site_url.'plugins'.DS.'mymuse'.DS."audio_mp".DS.'plugin'.DS.'ttw-music-player.js';
        $document->addScript( $js_path );
        $document->addStyleSheet( './plugins/mymuse/audio_mp/plugin/css/style.css' );
        $document->addScript('http://www.google.com/jsapi');
        $js = 'google.load("swfobject", "2.2");';
        $document->addScriptDeclaration($js);
	}

	/**
	 * HTML5
	 * onPrepareMyMuseMp3Player
	 */
	function onPrepareMyMuseMp3Player(&$product, $type='each', $height=0, $width=0)
	{
		echo '<!-- audio MP -->';

		$precount = $product->product_physical + @count($product->items);

		$params 	=& MyMuseHelper::getParams();
		$document 	=& JFactory::getDocument();
		$site_url = preg_replace("#administrator/#","",JURI::base());
		//$swf_path = $site_url.'plugins/mymuse/audio_mp/plugin/jquery-jplayer';
		$swf_path = "http://www.jplayer.org/latest/js/Jplayer.swf";
		$swf_player = $swf_path;
		
		$mp3_src = '';
		$extarray = array(
				'mp3' => 'audio/mpeg',
				'm4a' => 'audio/mp4',
				'ogg' => 'application/ogg',
				'oga' => 'application/ogg',
				'webma' => 'audio/webm',
				'wav' => 'audio/wav'
		
		);
		

		//$swf_player = $site_url."plugins".DS."mymuse".DS."mp3player_dewplayer".DS."mp3players".DS."dewplayer-vol.swf";
		//$swf_player = $site_url."plugins".DS."mymuse".DS."audio_html5".DS."Jplayer.swf";
		$height = $height? $height :  $params->get('product_player_height', 50);
		$width  = $width? $width : $params->get('product_player_width', 100);
		
		if($type == 'singleplayer' || $type == 'single'){
			$id = 1;
		}else{
			$id = $product->id;
		}
		
		$html = '
		<div class="ttw-music-player'.$id.'" id="ttw-music-player'.$id.'" style="display:none;"></div>
		    ';
		



		$site_url = preg_replace("#administrator/#","",JURI::base());

		if($type == 'each' || $type == 'singleplayer'){
			return true;
		}
		// ONLY WORKS AS A PLAYLIST!!!!!!!!!!!!!!!
		
		if($type == 'playlist'){
				$list = '';
				$first = 1;

				$playlist = 'var myPlaylist =
				[';
				$supplied = array();
				foreach($product->previews as $t){
					if($t->product_allfiles){
						//continue;
					}
					$preview_file 	= $t->path;
					$ext = MyMuseHelper::getExt($preview_file);
					if($ext == 'ogg'){
						$ext = 'oga';
					}
					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
					if(!$t->rating){
						$t->rating  = 0;
					}
					
					
					$playlist .= '
					{
					title: "'.addslashes($t->title).' ('.MyMuseHelper::ByteSize($t->file_length).')",
					artist: "'.$product->category_title.': '.$product->title.'",
					rating:"'.$t->rating.'",
					buy:"'.$t->id.'",
					price:"'.$t->price['product_price']   .'",				
					cover:"'.JURI::root( true )."/".$product->list_image.'",
					';
					if($ext && $preview_file){
						$playlist .= $ext.':"'.$preview_file.'"';
					}
					if(isset($t->path_2) && $t->path_2 != ''){
						$preview_file 	= $t->path_2;
						$ext = MyMuseHelper::getExt($preview_file);
						if($ext == 'ogg'){
							$ext = 'oga';
						}
						$playlist .= ',
						'.$ext.':"'.$preview_file.'"';
						if(!in_array($ext,$supplied)){
							$supplied[] = $ext;
						}
					}
					
					if(isset($t->path_3) && $t->path_3 != ''){
						$preview_file 	= $t->path_3;
						$ext = MyMuseHelper::getExt($preview_file);
						if($ext == 'ogg'){
							$ext = 'oga';
						}
						$playlist .= ',
						'.$ext.':"'.$preview_file.'"';
						if(!in_array($ext,$supplied)){
							$supplied[] = $ext;
						}
					}
					$playlist .= '
					},';
				}
			
				$playlist = preg_replace("/,$/","",$playlist);
				$playlist .= "
				]; ";
				$document->addScriptDeclaration($playlist);
				$supplied = implode(", ",$supplied);
			
				$ratings_callback = '
				function my_ratings_callback(index, playlistItem, rating)
				{
				 alert("index="+index+" playlistItem = "+playlistItem+ " rating = "+rating)
				}
				';
				$description = preg_replace("/'/","\'",$product->introtext);
				$description = preg_replace('/[\\n\\r]/', ' ', $description);
				$js = "

jQuery(document).ready(function(){
	var description = '".addslashes($description)."';
	
	jQuery('body').ttwMusicPlayer(myPlaylist, {
			autoPlay:false, 
            description:description,
            supplied: '".$supplied."',
            jPlayer:{
            ";
			if($params->get("my_player_errors")){
				$js .= "errorAlerts: true,
				";
			}
            	
                $js .= "swfPath:'".$swf_path ."' //You need to override the default swf path any time the directory structure changes
            }
        });
    });
    $ratings_callback
    
    window.precount = $precount;
";
			$document->addScriptDeclaration($js);

			$text = '';
			
			return $text;
			}
		
	}
}
