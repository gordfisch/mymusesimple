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


/**
* MyMuse Player Dewplayer plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseVidplayer_Flowplayer extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	function plgMymuseVidplayer_Flowplayer(&$subject, $config)  {
		parent::__construct($subject, $config);
		
        
        $document = JFactory::getDocument();
        $site_url = preg_replace("#administrator/#","",JURI::base());
		$js_path = $site_url.'plugins/mymuse/vidplayer_flowplayer/vidplayers/flowplayer-3.2.6.min.js';
		$document->addScript( $js_path );
		if($this->params->get('my_include_jquery', 0)){
			$js_path = "http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js";
			$document->addScript( $js_path );
		}
		$document->addStyleSheet( './plugins/mymuse/vidplayer_flowplayer/css/flowplayer.css' );

	}

	/**
	 * Flowplayer vid player
	 * onPrepareMyMuseVidPlayer
	 */
	function onPrepareMyMuseVidPlayer(&$track, $type='each', $height=0, $width=0)
	{
		$params = MyMuseHelper::getParams();
		
		$site_url = preg_replace("#administrator/#","",JURI::base());
		$player_path = $site_url."plugins/mymuse/vidplayer_flowplayer/vidplayers/flowplayer-3.2.7.swf";
		$controls_path	= $site_url."plugins/mymuse/vidplayer_flowplayer/vidplayers/flowplayer.controls-3.2.5.swf";

		$height = $height? $height :  $params->get('product_player_height');
		$width  = $width? $width : $params->get('product_player_width');;
		$my_flowplayer_allowFullScreen 	= $this->params->get("my_flowplayer_allowFullScreen",0)? 'true' : 'false';
		$my_flowplayer_bgcolor 	= $this->params->get("my_flowplayer_bgcolor");
		
		
		
		if($type == 'singleplayer' || $type == 'single' || $type == 'playlist'){
			$id = 1;
		}else{
			$id = $track->id;
		}
		if($type == 'playlist' || $type == 'singleplayer'){
			$document = &JFactory::getDocument();
			
			$js_path = $site_url.'plugins/mymuse/mp3player_flowplayer/mp3players/flowplayer.playlist-3.0.8.min.js';
			$document->addScript( $js_path );
		}
		
		//$track->path = 'http://content.bitsontherun.com/videos/bkaovAYt-injeKYZS.mp4';
		if($type=='each'){

			$text = '<!-- setup player container  -->
			<div id="player'.$id.'" style="display:block;width:'.$width.'px;height:'.$height.'px;"></div>
			<!-- install flowplayer into container -->
			<script>
			$f("player'.$id.'", "'. $player_path .'", {

				clip: { 
				   url: \''.$track->path.'\',
				   autoPlay: false,
        		   autoBuffering: true,
                   scaling: "fit",
        		   fullscreen:'.$my_flowplayer_allowFullScreen.'
	   
				},
				plugins:  {
		
					// and a bit of controlbar skinning  
					controls: {
						url: \''.$controls_path.'\',
						backgroundColor:\''.$my_flowplayer_bgcolor.'\',
                        autoHide: false,
						tooltips: {				
                			buttons: true,
                			fullscreen: \'Enter Fullscreen mode\'
            			}
					}
					
				}
			});
			</script>';
			return $text;
		}
		
		
		
		
		/**
		 * Single player with buttons for each track
		 */
		
		
		if($type == 'single'){
			$text = '
			<!-- single playlist entry -->
	<a href="'.$track->path.'" style="text-align: left;" onClick=\'jQuery("#jp-title-li").html("'.addslashes($track->title).'");\'>
		'.$track->title.'

	</a>
			
			';
			return $text;
		}
		if($type == 'singleplayer'){
			
			$text = '

			<script>
jQuery(document).ready(function($){
	
	// setup player without "internal" playlists
	$f("player'.$id.'", "'.$player_path.'", {

		plugins:  {
		
					// and a bit of controlbar skinning  
					controls: {
						url: \''.$controls_path.'\',
						backgroundColor:\''.$my_flowplayer_bgcolor.'\',
                        scaling: "fit",
						tooltips: {				
                			buttons: true,
                			fullscreen: \'Enter Fullscreen mode\'
            			}
					}
				}
		
	// use playlist plugin. again loop is true
	}).playlist("div.petrol", {loop:false});
	
});
jQuery(document).ready(function(){
		jQuery("#jp-title-li").html("'.addslashes($track->title).'");
	});
</script>
<a class="player petrol" id="player'.$id.'"  style="float:left; display: block; width:'.$width.'px;height:'.$height.'px;">
<img src="plugins/mymuse/mp3player_flowplayer/images/play_text.png" />
</a>

';
			return $text;
		}
		
		
		if($type == 'playlist'){
			$playlist = '
			[';
			foreach($track->previews as $t){
				if(!$t->product_allfiles){
					$preview_file 	= $t->path;
					$playlist .= "
					{
						url: '".$t->path."',
						title: '".addslashes($t->title)."'
					},\n";
				}
			}
			$playlist = preg_replace("/,\n$/","",$playlist);
			$playlist .= "
			] ";
			
			$text = '
		
<script>
// wait for the DOM to load using jQuery
jQuery(document).ready(function($){


			
	flowplayer("player1", "'. $player_path .'", {
		// clip properties common to all playlist entries
		clip: {
			subTitle: \'mymuse\',
			autoPlay: false,
            autoBuffering: true,
        	fullscreen:'.$my_flowplayer_allowFullScreen.'
		},
			
		playlist: '.$playlist.',
		plugins:  {
			// and a bit of controlbar skinning  
			controls: {
				url: \''.$controls_path.'\',
				backgroundColor:\''.$my_flowplayer_bgcolor.'\',
                autoHide: false,
                scaling: "fit",
				tooltips: {				
                	buttons: true,
                	fullscreen: \'Enter Fullscreen mode\'
            	}
			}
		}
	
	});

	flowplayer("player1").playlist("div.clips:first", {loop:false});
});		
			</script>
<!-- setup player container  -->
<table><tr><td valign="top">
<div class="clips petrol" style="float:left">
	<!-- single playlist entry as a "template" -->
	<a href="${url}">
		${title} 
	</a>
</div></td>
<td valign="top">
<!-- the player using splash image -->
<a class="player plain" id="player1" style="float:left">
	<img src="plugins/mymuse/mp3player_flowplayer/images/play_text.png" />
</a>
</td></tr></table>
<br clear="all"/>
			
			';
			return $text;
		}
		
	}
		
}