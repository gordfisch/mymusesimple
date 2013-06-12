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
jimport('joomla.filesystem.file');
JPlugin::loadLanguage( 'plg_mymuse_mp3player_dewplayer', JPATH_ADMINISTRATOR );

/**
* MyMuse Player Dewplayer plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/

class plgMymuseMp3Player_Dewplayer extends JPlugin
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
	function plgMymuseMp3Player_Dewplayer(&$subject, $config)  {
		parent::__construct($subject, $config);
		
        
        $document = &JFactory::getDocument();
        $site_url = preg_replace("#administrator/#","",JURI::base());
		$js_path = $site_url.'plugins'.DS.'mymuse'.DS."mp3player_dewplayer".DS.'mp3players'.DS.'dewplayer.js';
		$document->addScript( $js_path );
		
		
	}

	/**
	 * DewPlayer flash
	 * onPrepareMyMuseMp3Player
	 */
	function onPrepareMyMuseMp3Player(&$track, $type='each', $height=0, $width=0)
	{
		$params 	=& MyMuseHelper::getParams();

		$height = $height? $height :  $params->get('product_player_height');		
		$width  = $width? $width : $params->get('product_player_width');
		
		$gettype = array(
			'single' => 'dewplayer.swf',
			'each' => 'dewplayer-vol.swf',
			'playlist' => 'dewplayer-playlist.swf',
			'singleplayer' => 'dewplayer.swf',
		);
		if($type == 'singleplayer' || $type == 'single'){
			$id = 1;
		}else{
			$id = $track->id;
		}
		$my_dewplayer_player = $gettype[$type];
		
		$site_url = preg_replace("#administrator/#","",JURI::base());
		$player_path = $site_url."plugins".DS."mymuse".DS."mp3player_dewplayer".DS."mp3players".DS.$my_dewplayer_player;

		if($type == 'each' || $type == 'singleplayer'){
			$height = $height? $height :  '20';
			$width  = $width? $width : '200';
			
			$text = '<object type="application/x-shockwave-flash" 
			data="'. $player_path .'" 
			id="flash_'.$id.'"
			width="'.$width.'" height="'.$height.'">
			<param name="movie" value="'. $player_path .'" />
			<param name="flashvars" value="mp3='. $track->path .'" />
			<param name="wmode" value="transparent" />
			</object>
			';		
			return $text;
		}
		if($type=='single'){
			$text = 
			'<input class="button" type="button" 
			onclick="mymuseset(\''. $track->path  .'\',\''.$id.'\',\''.addslashes($track->title).'\');" 
			value="'. JText::_('MYMUSE_PLAY') .'" />';
			
			return $text;
			
		}
		
		if($type == 'playlist'){
			$height =  count($track->previews) * 20 + 30;
			$width  = $width? $width : '200';
			//var_dump($track); exit;
			//write out the xml file
			$xml = '<playlist version="1">
<title>MyMuse Playlist</title>
<creator>MyMuse</creator>
<link>http://www.mymuse.ca</link>
<info>The Best Playlist</info>
<image />
<trackList>
';
			foreach($track->previews as $t){
				if(!$t->product_allfiles){
				$xml .= '<track>
				<location>'.$t->path.'</location>
<creator>'.$track->category_title.'</creator>
<album>'.$track->title.'</album>
<title>'.$t->title.'</title>
<annotation />
<duration />
<image />
<info/>
<link/>
</track>
';
				}
			}
			$xml .= '</trackList>
</playlist>';
			$site_url = preg_replace("#administrator/#","",JURI::base());
			$xml_path = JPATH_BASE.DS.'plugins'.DS.'mymuse'.DS."mp3player_dewplayer".DS.'mp3players'.DS.'playlist.xml';
			$xml_url = $site_url.DS.'plugins'.DS.'mymuse'.DS."mp3player_dewplayer".DS.'mp3players'.DS.'playlist.xml';
			if(!JFILE::write($xml_path, $xml)){
				$this->error = "Could not write file $xml_path";
				return false;
			}
			

			
			$text = '<object type="application/x-shockwave-flash" 
			data="'. $player_path .'" 
			width="'.$width.'" height="'.$height.'" 
			id="dewplayerpls" 
			name="dewplayerpls">
			<param name="movie" value="'. $player_path .'" />
			<param name="flashvars" value="xml='.$xml_url.'" />
			</object>
			';
			
			return $text;
		}
		
	}
}
