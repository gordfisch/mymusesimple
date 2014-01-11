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
class plgMymuseVidplayer_Dewplayer extends JPlugin
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
	function plgMymuseVidplayer_Dewplayer(&$subject, $config)  {
		parent::__construct($subject, $config);
		
        
        $document = &JFactory::getDocument();
        $site_url = preg_replace("#administrator/#","",JURI::base());
		$js_path = $site_url.'plugins'.DS.'mymuse'.DS.'vidplayer_dewplayer'.DS.'vidplayers'.DS.'dewplayervid.js';
		$document->addScript( $js_path );

	}

	/**
	 * Dewtube video player
	 * onPrepareMyMuseVidPlayer
	 */
	function onPrepareMyMuseVidPlayer(&$track, $type='each', $height=0, $width=0)
	{
		$params 	=& MyMuseHelper::getParams();

		$height = $height? $height :  $params->get('product_player_height',300);
		$width  = $width? $width : $params->get('product_player_width',420);
		$fullscreen = $this->params->get('my_dewtube_allowFullScreen')? 'true' : 'false';
		$background = $this->params->get('my_dewtube_bgcolor');

		if($type == 'singleplayer' || $type == 'single' || $type=='playlist'){
			$id = 1;
		}else{
			$id = $track->id;
		}

		$site_url = preg_replace("#administrator/#","",JURI::base());
		$player_path = $site_url."plugins".DS."mymuse".DS."vidplayer_dewplayer".DS."vidplayers".DS."dewtube.swf";


		if($type == 'each' || $type == 'singleplayer'){
			$text = '<div id="dewtube_player"><object type="application/x-shockwave-flash" 
			data="'. $player_path .'" 
			id="flash_'.$id.'"
			';
			if($width){ 
				$text .= 'width="'.$width.'"
				';
			}
			if($height){
				$text .= 'height="'.$height.'"
				';
			}
			$text .= '">
			<param name="allowFullScreen" value="'.$fullscreen.'" />
			<param name="movie" value="'.$player_path.'" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="'.$background.'" />
			';
			if($track->detail_image != ''){
				$text .= '<param name="image" value="'.$site_url.$track->detail_image.'" />
				';
			}
			
			
			
			$text .= '<param name="flashvars" value="movie='. $track->path .'&height='.$height.'&width='.$width.'" />
			</object></div>
			';		
			return $text;
		}
		if($type=='single'){
			$text = 
			'<input class="button" type="button" 
			onclick="setvid(\''. $track->path  .'\',\''.$id.'\',\''.addslashes($track->title).'\','.$height.','.$width.');" 
			value="'. JText::_('MYMUSE_PLAY') .'" />';
			
			return $text;
			
		}

		//PLAYLIST//
		if($type == 'playlist'){

			$text = '<!-- setup player container  -->
			<table><tr><td valign="top">
			<div class="clips" style="float:left">
			';
			foreach($track->previews as $t){
				if(!$t->product_allfiles){ 
				$text .= '<a href="javascript: setvid2(\''. $t->path  .'\','.$height.','.$width.');">
				'.$t->title.'</a><br />
				';
				}
			}
			$text .= '</div></td>
			<td valign="top">
			<div id="dewtube_player">
			<object type="application/x-shockwave-flash" 
			data="'. $player_path .'" 
			';
			if($width){ 
				$text .= 'width="'.$width.'"
				';
			}
			if($height){
				$text .= 'height="'.$height.'"
				';
			}
			$text .= '">
			id="dewplayerpls" 
			name="dewplayerpls">
			<param name="allowFullScreen" value="'.$fullscreen.'" />
			<param name="movie" value="'.$player_path.'" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="'.$background.'" />
			<param name="flashvars" value="movie='.$track->previews[0]->path.'&height='.$height.'&width='.$width.'" />
			</object>
			</div>
			</td></tr></table>
			<br clear="all"/>';
			
			return $text;
		}
		
	}
		

}