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

/**
* MyMuse Player Dewplayer plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseMp3Player_Flowplayer extends JPlugin
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
	function plgMymuseMp3Player_Flowplayer(&$subject, $config)  {
		parent::__construct($subject, $config);
		
        $document = JFactory::getDocument();
        $site_url = preg_replace("#administrator/#","",JURI::base());
		$js_path = $site_url.'plugins'.DS.'mymuse'.DS."mp3player_flowplayer".DS.'mp3players'.DS.'flowplayer-3.2.6.min.js';
		$document->addScript( $js_path );
		$css_url = './components/com_mymuse/assets/css/mymuse.css';
		$document->addStyleSheet( $css_url );
		$document->addStyleSheet( 'plugins/mymuse/mp3player_flowplayer/css/flowplayer.css' );
		
	}

	/**
	 * FlowPlayer flash
	 * onPrepareMyMuseMp3Player
	 */
	function onPrepareMyMuseMp3Player(&$track, $type='each', $height=0, $width=0)
	{
		$params = MyMuseHelper::getParams();
		
		$height = $height? $height :  $params->get('product_player_height');
		$width  = $width? $width : $params->get('product_player_width');
		$my_flowplayer_bgcolor 	= $this->params->get("my_flowplayer_bgcolor");
		if(!$height){
			$height="25";
		}
		if(!$width){
			$width="250";
		}
		
		$site_url 		= preg_replace("#administrator/#","",JURI::base());
		$player_path 	= $site_url."plugins".DS."mymuse".DS."mp3player_flowplayer".DS."mp3players".DS."flowplayer-3.2.7.swf";
		$audio_path		= $site_url."plugins".DS."mymuse".DS."mp3player_flowplayer".DS."mp3players".DS."flowplayer.audio-3.2.1.swf";
		$controls_path	= $site_url."plugins".DS."mymuse".DS."mp3player_flowplayer".DS."mp3players".DS."flowplayer.controls-3.2.5.swf";
		$content_path	= $site_url."plugins".DS."mymuse".DS."mp3player_flowplayer".DS."mp3players".DS."flowplayer.content-3.2.0.swf";
		//$mp3 = $site_url.str_replace('\\','/',$mp3);
		
		
		if( $type == 'single'){
			$id = 1;
		}else{
			$id = $track->id;
		}
		if($type == 'playlist' || $type == 'singleplayer'){
			$document = JFactory::getDocument();
			JHtml::_('jquery.framework');
			if($this->params->get('my_include_jquery', 0)){
				//load same jquery as Joomla, 1.8.3
				$js_path = "http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js";
				$document->addScript( $js_path );
			}
			$js_path = $site_url.'plugins'.DS.'mymuse'.DS."mp3player_flowplayer".DS.'mp3players'.DS.'flowplayer.playlist-3.0.8.min.js';
			$document->addScript( $js_path );
		}


		

		if($type=='each'){

			$text = '<!-- setup player container  -->
			<div id="player'.$id.'" style="display:block;width:'.$width.'px;height:'.$height.'px;"></div>
			<!-- install flowplayer into container -->
			<script>
		jQuery(function() {
			flowplayer("player'.$id.'", "'. $player_path .'", {

				clip: { 
				   url: \''.$track->path.'\',
				   autoPlay: false
	   
				},
				plugins:  {
		
					// and a bit of controlbar skinning  
					controls: {
						backgroundColor:\''.$my_flowplayer_bgcolor.'\',
						height: '.$height.',
						fullscreen: false,
						autoHide: false
					},
					audio: {
						url: \''.$audio_path.'\'
					}
				}
			});
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
jQuery(function() {
	
	// setup player without "internal" playlists
	flowplayer("player'.$id.'", "'.$player_path.'", {

		plugins:  {
		
					controls: {
						backgroundColor:\''.$my_flowplayer_bgcolor.'\',
						height: '.$height.',
						autohide: false,
						fullscreen: false
					},
					audio: {
						url: \''.$audio_path.'\'
					}
				}
		
	// use playlist plugin. 
	}).playlist("div.petrol", {loop:false});
	
});
	jQuery(document).ready(function(){
		jQuery("#jp-title-li").html("'.addslashes($track->title).'");
	});
</script>
<a class="player plain" id="player'.$id.'"  style="float:left; display: block; width:'.$width.'px;height:'.$height.'px;">
<img src="plugins/mymuse/mp3player_flowplayer/images/play_text.png" />
</a>
		

';
			return $text;
		}
		
		
		if($type == 'playlist'){

            $list = '';
            $first = 1;
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
                    $list .= '<a href="'.$t->path.'" ';
                    if($first){
                    	$list .= ' class="first" ';
                    	$first = 0;
                    }
                    
                    $list .= ' style="text-align: left;">
                    '.$t->title.'</a>
                    ';
				}
			}
			$playlist = preg_replace("/,\n$/","",$playlist);
			$playlist .= "
			] ";
			
			$text = '<script>
jQuery(function() {
	
	// setup player with playlist
	flowplayer("player1", "'.$player_path.'", {
		
		plugins:  {
                    
					controls: {
						playlist: true,
						backgroundColor:\''.$my_flowplayer_bgcolor.'\',
						height: '.$height.',
						autohide: false,
						fullscreen: false,
						url: \''.$controls_path.'\'
					},
					audio: {
						url: \''.$audio_path.'\'
					}
				}
		
	// use playlist plugin. 
	}).playlist("div.clips");
	
});
</script>
<div>
		<a class="player petrol" id="player1"  style="float:left; display: block; width:'.$width.'px;height:'.$height.'px;"></a>
</div>

<div>
			<div class="clips petrol" style="float:left">'.$list.'</div>
</div>
<br clear="all"/>
';
			return $text;
		}
		
	}
}