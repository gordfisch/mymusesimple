<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');
jimport( 'joomla.html.parameter' );
JPlugin::loadLanguage( 'plg_mymuse_vidplayer_html5', JPATH_ADMINISTRATOR );

/**
* MyMuse Player html5 plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseVidplayer_html5 extends JPlugin
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
	function plgMymuseVidplayer_html5(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		$document = &JFactory::getDocument();
		
		if($this->params->get('my_include_jquery', 0)){
			//load same jquery as Joomla, 1.8.3
			$js_path = "http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js";
			$document->addScript( $js_path );
		}

        $site_url = preg_replace("#administrator/#","",JURI::base());
		$this->language = $document->language;
		$this->direction = $document->direction;
		
        if($this->direction == "rtl"){
        	$css_path = $site_url.'plugins/mymuse/audio_html5/skin/jplayer.blue.monday.rtl.css';
        }else{
        	$css_path = $site_url.'plugins/mymuse/audio_html5/skin/jplayer.blue.monday.css';
        }
        $document->addStyleSheet( $css_path );
        
        if($this->params->get("my_player_errors")){
        	$js_path = $site_url.'plugins/mymuse/audio_html5/js/jquery.jplayer.inspector.js';
        	$document->addScript( $js_path );
        }

	}

	/**
	 * HTML5 video player
	 * onPrepareMyMuseVidPlayer
	 */
	function onPrepareMyMuseVidPlayer(&$track, $type='each', $height=0, $width=0, $index=0, $count=0)
	{
		
		//load jquery.jplayer.min.js? Not if it has been added already
		$document = &JFactory::getDocument();
		$match = 0;
		$site_url = preg_replace("#administrator/#","",JURI::base());
		while(list($url,$arr) = each($document->_scripts)){
			if(preg_match("/jquery.jplayer.min.js/", $url)){
				//echo "<br />audio url: ".$url;
				$match = 1;
			}
		}
		if(!$match){
			$js_path = $site_url.'plugins/mymuse/audio_html5/js/jquery.jplayer.min.js';
			$document->addScript( $js_path );
		}

		//echo "count = $count index = $index <br />";
		$params 	=& MyMuseHelper::getParams();
		
		//$swf_path = JPATH_ROOT.'/plugins/mymuse/audio_html5/Jplayer.swf';
		$swf_path = "http://www.jplayer.org/latest/js/Jplayer.swf";
		$extarray = array(
				'mp3' => 'audio/mpeg',
				'm4a' => 'audio/mp4',
				'ogg' => 'application/ogg',
				'oga' => 'application/ogg',
				'webma' => 'audio/webm',
				'wav' => 'audio/wav',
				'm4v' => 'video/m4v',
				'ogv' => 'video/ogv',
				'webm' => 'video/webmv',
				'webmv' => 'video/webmv',
				'wmv' => 'video/webmv'
		
		);

		$height = $height? $height :  $params->get('product_player_height', 360);
		$width  = $width? $width : $params->get('product_player_width', 640);

		if($type == 'singleplayer' || $type == 'single'){
			$id = 1;
		}else{
			$id = $index + 1;
		}
		if($track->flash_type == "mix"){
			$id = "video";
		}

		$site_url = preg_replace("#administrator/#","",JURI::base());

		if($type == 'each' || $type == 'singleplayer'){

				$supplied = array();
				
				$trs[0]['src'] = $track->path;
				$ext = MyMuseHelper::getExt($track->path);
				if($ext == "webm"){
					$ext = "webmv";
				}
				$trs[0]['type'] = $extarray[$ext];
				$trs[0]['ext'] = $ext;
				
				$supplied[] = $ext;

					
				if(isset($track->file_preview_2) && $track->file_preview_2 != ''){
					$trs[1]['src'] = $track->path_2;
					$ext = MyMuseHelper::getExt($track->path_2);
				if($ext == "webm"){
					$ext = "webmv";
				}
					$trs[1]['type'] = $extarray[$ext];
					$trs[1]['ext'] = $ext;

					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
				}
				if(isset($track->file_preview_3) && $track->file_preview_3 != ''){
					$trs[2]['src'] = $track->path_3;
					$ext = MyMuseHelper::getExt($track->path_3);
				if($ext == "webm"){
					$ext = "webmv";
				}
					$trs[2]['type'] = $extarray[$ext];
					$trs[2]['ext'] = $ext;

					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
				}
				$supplied = implode(", ",$supplied);

				$js = '

jQuery(document).ready(function($){
      $("#jquery_jplayer_'.$id.'").jPlayer({
        ready: function () {
			$(this).jPlayer("setMedia", {
          ';
			foreach($trs as $source){
            	$js .= $source['ext'].': "'.$source['src'].'",'."\n";
            }
            if($track->detail_image != ''){
            	$js .= 'poster: "'.$site_url.$track->detail_image.'"'."\n";
            }
            $js = preg_replace("/,\\n$/","",$js);
            
        $js .= '  });
        },
        ';
       if($type == 'each'){
		$js .= '
		play: function() { // To avoid both jPlayers playing together.
			$(this).jPlayer("pauseOthers");
		},
		repeat: function(event) { // Override the default jPlayer repeat event handler
			if(event.jPlayer.options.loop) {
				$(this).unbind(".jPlayerRepeat").unbind(".jPlayerNext");
				$(this).bind($.jPlayer.event.ended + ".jPlayer.jPlayerRepeat", function() {
					$(this).jPlayer("play");
				});
			} else {
				$(this).unbind(".jPlayerRepeat").unbind(".jPlayerNext");
				$(this).bind($.jPlayer.event.ended + ".jPlayer.jPlayerNext", function() {
					$("#jquery_jplayer_'.$id.'").jPlayer("play", 0);
				});
			}
		},
		';
       }
		$js .= '
        swfPath: "'.$swf_path .'",
        ';
		if($this->params->get("my_player_errors")){
			$js .= "errorAlerts: true,
			";
		}
		
		$js .= 'supplied: "'.$supplied.'",
        cssSelectorAncestor: "#jp_container_'.$id.'",
        wmode: "window"
        
      });
      
    	';
	
        if(($count && ($index +1) == $count) || $count == 0){
        	
        	if($this->params->get("my_player_errors")){
        		for($i = 0; $i < $count; $i++){
        			$m = $i+1;
        			$js .= '$("#jplayer_inspector_'.$m.'").jPlayerInspector({jPlayer:$("#jquery_jplayer_'.$m.'")});
        			';
        		}
        	}
        }
		if($count == 0 && $this->params->get("my_player_errors")){
        	$js .= '$("#jplayer_inspector_'.$id.'").jPlayerInspector({jPlayer:$("#jquery_jplayer_'.$id.'")});
        	';
        }
        
        //if(($count && ($index +1) == $count) || $count == 0){
        	$js .= '
        });
        //end of player
        	';
        //}

			$document->addScriptDeclaration($js);
			
			$text = '
		<div id="jquery_jplayer_'.$id.'" class="jp-jplayer"></div>

		<div id="jp_container_'.$id.'" class="jp-audio">
			<div class="jp-type-single">
				<div class="jp-gui jp-interface">
					<ul class="jp-controls">
						<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
						<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
						<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
						<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
						<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
						<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
					</ul>
					<div class="jp-progress">
						<div class="jp-seek-bar">
							<div class="jp-play-bar"></div>
						</div>
					</div>
					<div class="jp-volume-bar">
						<div class="jp-volume-bar-value"></div>
					</div>
					<div class="jp-time-holder">
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>

						<ul class="jp-toggles">
							<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
							<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
						</ul>
					</div>
				</div>
				<div class="jp-title">
					<ul>
						<li id="jp-title-li">'.$track->title.'</li>
					</ul>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>
		
			<div id="jplayer_inspector_'.$id.'"></div>
';
			
			return $text;
		}
		
		
		
		
		
		//SINGLE ADD THE PLAY BUTTONS//
		if($type=='single'){

			$text = '<a href="javascript:void(0);" id="track_'.$track->id.'">'. JText::_('MYMUSE_PLAY') .'</a> ';
			
			$supplied = array();
			$trs[0]['src'] = $track->path;
			$ext = MyMuseHelper::getExt($track->path);
			if($ext == "webm"){
				$ext = "webmv";
			}
			$trs[0]['type'] = $extarray[$ext];
			$trs[0]['ext'] = $ext;
			
			$supplied[] = $ext;
			
				
			if(isset($track->file_preview_2) && $track->file_preview_2 != ''){
				$trs[1]['src'] = $track->path_2;
				$ext = MyMuseHelper::getExt($track->path_2);
				if($ext == "webm"){
					$ext = "webmv";
				}
				$trs[1]['type'] = $extarray[$ext];
				$trs[1]['ext'] = $ext;
			
				if(!in_array($ext,$supplied)){
					$supplied[] = $ext;
				}
			}
			if(isset($track->file_preview_3) && $track->file_preview_3 != ''){
				$trs[2]['src'] = $track->path_3;
				$ext = MyMuseHelper::getExt($track->path_3);
				if($ext == "webm"){
					$ext = "webmv";
				}
				$trs[2]['type'] = $extarray[$ext];
				$trs[2]['ext'] = $ext;
			
				if(!in_array($ext,$supplied)){
					$supplied[] = $ext;
				}
			}
			$supplied = implode(", ",$supplied);
			
			$media = '';
			foreach($trs as $source){
				$media .= $source['ext'].': "'.$source['src'].'",'."\n";
				
			}
			
			$media = preg_replace("/,\\n$/","",$media);		
			//$media .= ', title: "'.$track->title.'"';
			
			
			$js = '';
			$js .= '
jQuery(document).ready(function($){
		$("#track_'.$track->id.'").click( function(e) {
			var title = "'.$track->title.'";
			$("#jp-title-li").html(title);
			$("#jquery_jplayer_'.$id.'").jPlayer("setMedia",{ '.$media.' }).jPlayer("play");
			
			return false;
		}); 
	});

';
			$document->addScriptDeclaration($js);
			return $text;

			
		}
		
		//PLAYLIST
		if($type == 'playlist'){
			
			$js_path = $site_url.'plugins/mymuse/audio_html5/js/jplayer.playlist.min.js';
			$document->addScript( $js_path );
			
			$list = '';
			$first = 1;
	
			$playlist = '
				[';
				$supplied = array();
				foreach($track->previews as $t){
					if($t->product_allfiles){
						continue;
					}
					$preview_file 	= $t->path;
					$ext = MyMuseHelper::getExt($preview_file);
					if($ext == "webm"){
						$ext = "webmv";
					}
					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
					$playlist .= '
					{
					title: "'.addslashes($t->title).'",
					artist: "'.$track->category_title.'",
					';
					if($t->detail_image != ''){
            			$playlist .= 'poster: "'.$site_url.$t->detail_image.'",'."\n";
           		 	}
					$playlist .= $ext.':"'.$preview_file.'"';
					if(isset($t->path_2) && $t->path_2 != ''){
						$preview_file 	= $t->path_2;
						$ext = MyMuseHelper::getExt($preview_file);
						if($ext == "webm"){
							$ext = "webmv";
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
						if($ext == "webm"){
							$ext = "webmv";
						}
						$playlist .= ',
						'.$ext.':"'.$preview_file.'"';
						if(!in_array($ext,$supplied)){
							$supplied[] = $ext;
						}
					}
					$playlist .= '
					},';
					$list .= '<a href="'.$t->path.'" ';
					if($first){
						$list .= ' class="first" ';
						$first = 0;
					}
						
					$list .= ' style="text-align: left;">
					'.$t->title.'</a>
					';
	
				}
				$playlist = preg_replace("/,$/","",$playlist);
				$playlist .= "
				] ";
					
				$supplied = implode(", ",$supplied);
				$js = '
		( function($) {		
$(document).ready(function(){

	new jPlayerPlaylist({
		jPlayer: "#jquery_jplayer_1",
		cssSelectorAncestor: "#jp_container_1"
	}, '.$playlist.', {
		swfPath: "'.$swf_path .'",
		';
		if($this->params->get("my_player_errors")){
				$js .= "errorAlerts: true,
				";
			}
		
		$js .= 'supplied: "'.$supplied.'"
	});
';
		if($this->params->get("my_player_errors")){
			$js .= '$("#jplayer_inspector_1").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
			';
		}
$js .= '	});
} ) ( jQuery );

';
			$document->addScriptDeclaration($js);

			$text = '				<div id="jp_container_1" class="jp-video jp-video-270p">
			<div class="jp-type-playlist">
				<div id="jquery_jplayer_1" class="jp-jplayer"></div>
				<div class="jp-gui">
					<div class="jp-video-play">
						<a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
					</div>
					<div class="jp-interface">
						<div class="jp-progress">
							<div class="jp-seek-bar">
								<div class="jp-play-bar"></div>
							</div>
						</div>
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
						<div class="jp-controls-holder">
							<ul class="jp-controls">
								<li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
								<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
								<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
								<li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
								<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
								<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
								<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
								<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
							</ul>
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>
							<ul class="jp-toggles">
								<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
								<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
								<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
								<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
								<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
								<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
							</ul>
						</div>
						<div class="jp-title">
							<ul>
								<li></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="jp-playlist">
					<ul>
						<!-- The method Playlist.displayPlaylist() uses this unordered list -->
						<li></li>
					</ul>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		

			<div id="jplayer_inspector_1"></div>
';
			
			return $text;
		}
	}
}