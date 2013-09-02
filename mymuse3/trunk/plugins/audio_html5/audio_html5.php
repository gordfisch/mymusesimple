<?php
/**
 * @version		$Id$
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
* MyMuse Audio HTML5 plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseAudio_html5 extends JPlugin
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
	function plgMymuseAudio_html5(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		$document = JFactory::getDocument();
		
		if($this->params->get('my_include_jquery', 0)){
			$js_path = "http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js";
			$document->addScript( $js_path );
		}
        
        $site_url = preg_replace("#administrator/#","",JURI::base());
        $css_path = $site_url.'plugins/mymuse/audio_html5/skin/jplayer.blue.monday.css';
        $document->addStyleSheet( $css_path );
        
        
        if($this->params->get("my_player_errors")){
        	$js_path = $site_url.'plugins/mymuse/audio_html5/js/jquery.jplayer.inspector.js';
        	$document->addScript( $js_path );
        }
        
        // ui js and css
        $document->addScript( 'http://code.jquery.com/ui/1.9.0/jquery-ui.js' );
        $document->addStyleSheet('http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css');
	}

	/**
	 * HTML5
	 * onPrepareMyMuseMp3Player
	 */
	function onPrepareMyMuseMp3Player(&$track, $type='single', $height=0, $width=0, $index=0, $count=0)
	{
		//load jquery.jplayer.min.js? Not if it has been added already
		$document = JFactory::getDocument();
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
		
	//echo "type = $type, index = $index  count = $count<br />";
		$params 	= MyMuseHelper::getParams();

		$swf_path = JURI::root() .'/plugins/mymuse/audio_html5/Jplayer.swf';
		//$swf_path = "http://www.jplayer.org/latest/js/Jplayer.swf";
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

		$height = $height? $height :  $params->get('product_player_height', 50);
		$width  = $width? $width : $params->get('product_player_width', 100);

		if($type == 'singleplayer' || $type == 'single'){
			$id = 1;
		}else{
			$id = $index + 1;
		}

		$site_url = preg_replace("#administrator/#","",JURI::base());

		if($type == 'singleplayer'){
				// player set to play the first track 
				$supplied = array();
				$trs[0]['src'] = $track->path;
				$ext = MyMuseHelper::getExt($track->path);
				if($ext == "ogg"){
					$ext = "oga";
				}
				$trs[0]['type'] = $extarray[$ext];
				$trs[0]['ext'] = $ext;
				
				$supplied[] = $ext;

					
				if(isset($track->file_preview_2) && $track->file_preview_2 != ''){
					$trs[1]['src'] = $track->path_2;
					$ext = MyMuseHelper::getExt($track->path_2);
					if($ext == "ogg"){
						$ext = "oga";
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
					if($ext == "ogg"){
						$ext = "oga";
					}
					$trs[2]['type'] = $extarray[$ext];
					$trs[2]['ext'] = $ext;

					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
				}
				$supplied = implode(", ",$supplied);

			$js = '';
			$js .= '
	
	
	/*
	 * jQuery UI ThemeRoller
	 *
	 * Includes code to hide GUI volume controls on mobile devices.
	 * ie., Where volume controls have no effect. See noVolume option for more info.
	 *
	 * Includes fix for Flash solution with MP4 files.
	 * ie., The timeupdates are ignored for 1000ms after changing the play-head.
	 * Alternative solution would be to use the slider option: {animate:false}
	 */
	var myPlayer = $("#jquery_jplayer_'.$id.'"),
		myPlayerData,
		fixFlash_mp4, // Flag: The m4a and m4v Flash player gives some old currentTime values when changed.
		fixFlash_mp4_id, // Timeout ID used with fixFlash_mp4
		ignore_timeupdate, // Flag used with fixFlash_mp4
		options = {
			ready: function (event) {
				// Hide the volume slider on mobile browsers. ie., They have no effect.
				if(event.jPlayer.status.noVolume) {
					// Add a class and then CSS rules deal with it.
					$(".jp-gui").addClass("jp-no-volume");
				}
				// Determine if Flash is being used and the mp4 media type is supplied. BTW, Supplying both mp3 and mp4 is pointless.
				fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);
				// Setup the player with media.
				$(this).jPlayer("setMedia", {
          ';
			foreach($trs as $source){
            	$js .= $source['ext'].': "'.$source['src'].'",'."\n";
            }
            $js = preg_replace("/,\\n$/","",$js);
            
        $js .= '  });
        	},
        	timeupdate: function(event) {
				if(!ignore_timeupdate) {
					myControl.progress.slider("value", event.jPlayer.status.currentPercentAbsolute);
				}
			},
			volumechange: function(event) {
				if(event.jPlayer.options.muted) {
					myControl.volume.slider("value", 0);
				} else {
					myControl.volume.slider("value", event.jPlayer.options.volume);
				}
			},
        ';

		$js .= '
        swfPath: "'.$swf_path .'",
        ';
		if($this->params->get("my_player_errors")){
			$js .= "errorAlerts: true,
			";
		}
		
		$js .= 'supplied: "'.$supplied.'",
        cssSelectorAncestor: "#jp_container_'.$id.'",
        wmode: "window",
        keyEnabled: true
      },
      myControl = {
			progress: $(options.cssSelectorAncestor + " .jp-progress-slider"),
			volume: $(options.cssSelectorAncestor + " .jp-volume-slider")
		};
      	// Instance jPlayer
	myPlayer.jPlayer(options);

	// A pointer to the jPlayer data object
	myPlayerData = myPlayer.data("jPlayer");

	// Define hover states of the buttons
	$(".jp-gui ul li").hover(
		function() { $(this).addClass("ui-state-hover"); },
		function() { $(this).removeClass("ui-state-hover"); }
	);

	// Create the progress slider control
	myControl.progress.slider({
		animate: "fast",
		max: 100,
		range: "min",
		step: 0.1,
		value : 0,
		slide: function(event, ui) {
			var sp = myPlayerData.status.seekPercent;
			if(sp > 0) {
				// Apply a fix to mp4 formats when the Flash is used.
				if(fixFlash_mp4) {
					ignore_timeupdate = true;
					clearTimeout(fixFlash_mp4_id);
					fixFlash_mp4_id = setTimeout(function() {
						ignore_timeupdate = false;
					},1000);
				}
				// Move the play-head to the value and factor in the seek percent.
				myPlayer.jPlayer("playHead", ui.value * (100 / sp));
			} else {
				// Create a timeout to reset this slider to zero.
				setTimeout(function() {
					myControl.progress.slider("value", 0);
				}, 0);
			}
		}
	});

	// Create the volume slider control
	myControl.volume.slider({
		animate: "fast",
		max: 1,
		range: "min",
		step: 0.01,
		value : $.jPlayer.prototype.options.volume,
		slide: function(event, ui) {
			myPlayer.jPlayer("option", "muted", false);
			myPlayer.jPlayer("option", "volume", ui.value);
		}
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
        
        $js .= '
});
';

        

			$document->addScriptDeclaration($js);
			
			$text = '
		<section>
		<div id="jquery_jplayer_1" class="jp-jplayer"></div>

		<div id="jp_container_1">
			<div class="jp-gui ui-widget ui-widget-content ui-corner-all">
				<ul>
					<li class="jp-play ui-state-default ui-corner-all"><a href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">play</a></li>
					<li class="jp-pause ui-state-default ui-corner-all"><a href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">pause</a></li>
					<li class="jp-stop ui-state-default ui-corner-all"><a href="javascript:;" class="jp-stop ui-icon ui-icon-stop" tabindex="1" title="stop">stop</a></li>
					<li class="jp-repeat ui-state-default ui-corner-all"><a href="javascript:;" class="jp-repeat ui-icon ui-icon-refresh" tabindex="1" title="repeat">repeat</a></li>
					<li class="jp-repeat-off ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-repeat-off ui-icon ui-icon-refresh" tabindex="1" title="repeat off">repeat off</a></li>
					<li class="jp-mute ui-state-default ui-corner-all"><a href="javascript:;" class="jp-mute ui-icon ui-icon-volume-off" tabindex="1" title="mute">mute</a></li>
					<li class="jp-unmute ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-unmute ui-icon ui-icon-volume-off" tabindex="1" title="unmute">unmute</a></li>
					<li class="jp-volume-max ui-state-default ui-corner-all"><a href="javascript:;" class="jp-volume-max ui-icon ui-icon-volume-on" tabindex="1" title="max volume">max volume</a></li>
				</ul>
				<div class="jp-progress-slider"></div>
				<div class="jp-volume-slider"></div>
				<div class="jp-current-time"></div>
				<div class="jp-duration"></div>
				<div class="jp-clearboth"></div>
			</div>
			<div class="jp-no-solution">
				<span>Update Required</span>
				To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
			</div>
		</div>


			<div id="jplayer_inspector"></div>
		</section>
';
			
			return $text;
		}
		
		
		
		
		
		//SINGLE PLAYER MAKE PLAY BUTTONS//
		if($type=='single'){
			$word = JText::_('MYMUSE_PLAY');
			if($params->get('my_play_downloads')){
				$word = JText::_('MYMUSE_PLAY_PREVIEW');
				if($track->purchased){
					$word = JText::_('MYMUSE_PLAY');
				}
			}
			$text = '<ul>
			<li id="track_play_li_'.$track->id.'" class="jp-play ui-state-default ui-corner-all"><a id="track_play_'.$track->id.'" href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">play</a></li>
			<li id="track_pause_li_'.$track->id.'" class="jp-pause ui-state-default ui-corner-all"><a id="track_pause_'.$track->id.'" href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">pause</a></li>
			</ul>';
			
			$supplied = array();
			$trs[0]['src'] = $track->path;
			$ext = MyMuseHelper::getExt($track->path);
			if($ext == "ogg"){
				$ext = "oga";
			}

			$trs[0]['type'] = @$extarray[$ext];
			$trs[0]['ext'] = $ext;
			
			$supplied[] = $ext;
			
				
			if(isset($track->file_preview_2) && $track->file_preview_2 != ''){
				$trs[1]['src'] = $track->path_2;
				$ext = MyMuseHelper::getExt($track->path_2);
				if($ext == "ogg"){
					$ext = "oga";
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
				if($ext == "ogg"){
					$ext = "oga";
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
			
			$track->title = preg_replace("/\"/","",$track->title);
			$js = '';

			if($index == 0){
				$js .= '
jQuery(document).ready(function(){  ';
			}

			$js .= '

		$("#track_play_'.$track->id.'").click( function(e) {
			var title = "'.$track->title.'";
			$("#jp-title-li").html(title);

            $("#track_play_'.$track->id.'").css("display","none");
            $("#track_play_li_'.$track->id.'").css("display","none");
            $("#track_pause_'.$track->id.'").css("display","block");
            $("#track_pause_li_'.$track->id.'").css("display","block");
            myPlayer.jPlayer("setMedia",{ '.$media.' });
            myPlayer.jPlayer("play");

			return false;
		}); 
		
		$("#track_pause_'.$track->id.'").click( function(e) {

			$("#track_play_'.$track->id.'").css("display","block");
            $("#track_play_li_'.$track->id.'").css("display","block");
            $("#track_pause_'.$track->id.'").css("display","none");
            $("#track_pause_li_'.$track->id.'").css("display","none");
            myPlayer.jPlayer("stop");
			return false;
		})


';
			$document->addScriptDeclaration($js);
			return $text;

			
		}
		
		//Playlist//
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
					if($ext == 'ogg'){
						$ext = 'oga';
					}
					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
					$playlist .= '
					{
					title: "'.addslashes($t->title).'",
					artist: "'.$track->category_title.'",
					poster: "'.$site_url.$track->detail_image.'",
					'.$ext.':"'.$preview_file.'"';
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
jQuery(document).ready(function($){


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
	$js .= '
	});


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
