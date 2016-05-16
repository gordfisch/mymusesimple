<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author email	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');
jimport( 'joomla.html.parameter' );


/**
* MyMuse Audio amplitude plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymuseAudio_amplitude extends JPlugin
{

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

    var $playlist = array();
    var $indexes = array();
    
	public static $catalogs = array ();
	public static $_playlist = null;
	public static $_my_amplitude_playlist_path = null;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	function plgMymuseAudio_amplitude(&$subject, $config)  {
		parent::__construct($subject, $config);
		
		if(!defined('DS')){
			define('DS',DIRECTORY_SEPARATOR);
		}
		 
		if(!defined('MYMUSE_ADMIN_PATH')){
			define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS);
		}
		
		require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );

        $app = JFactory::getApplication();
        if ($app->isAdmin()) return;
		$document = JFactory::getDocument();
		$this->language = $document->language;
		$this->direction = $document->direction;
		$this->getCategories();
		
		$js_path = JURI::root().'plugins/mymuse/audio_amplitude/js/amplitude.min.js';
		$document->addScript( $js_path );
		
		$css_path = JURI::root().'plugins/mymuse/audio_amplitude/css/amplitude.css';
		$document->addStyleSheet($css_path);
		
		$sprite_path = $this->params->get('my_amplitude_sprite_path');
		$more_style = '
				div#play-pause.amplitude-paused, div#play-pause.amplitude-playing, div.play-pause.amplitude-paused, 
				div.play-pause.amplitude-playing, div#next, .play-pause {
    			background: url("'.JURI::base( true ).$sprite_path.'");
    			background-repeat: no-repeat;
			}';
		//$document->addStyleDeclaration($more_style);
		
        $playlist_path = rtrim($this->params->get('my_amplitude_playlist_path'),'/');
        $playlist_path .= DS;
        self::$_my_amplitude_playlist_path = $playlist_path;
        
        $arr = self::getPlaylist();
        $this->indexes = $arr[0];
        $this->playlist = $arr[1];
        
        
	}

	/**
	 * getCategories
	 * Gets categories and children
	 *
	 * @return array
	 */
	
	function getCategories()
	{
		$categories = JCategories::getInstance('MyMuse');
		$catid = $this->params->get('my_amplitude_catid');
		$category = $categories->get($catid);
		$children = $category->getChildren();
		$array[$category->id] = $category->alias;
		foreach($children as $child){
			$array[$child->id] = $child->alias;
		}
		self::$catalogs = $array;
		
	}

	/**
	 * getPlaylist
	 * Gets playlist for amplitute player and creates two arrays to do indexing and printing
	 * loads javascript playlist for amplitude
	 *
	 * @return array
	 */
	public static function getPlaylist($load_js = true){
		if(!self::$_playlist){
         
			$document = JFactory::getDocument();
		
			$jinput = JFactory::getApplication()->input;
            $option = $jinput->get('option');

			if($jinput->get('view') == "category" && null !== $jinput->get('id') && array_key_exists($jinput->get('id'),self::$catalogs)){
				$filename = isset(self::$catalogs[$jinput->get('id')])? self::$catalogs[$jinput->get('id')].'.js' : 'catalog.js';
				$catid = $jinput->get('id');
            }elseif($jinput->get('view') == "product" && null !== $jinput->get('id') && $jinput->get('catid')){
                $filename = isset(self::$catalogs[$jinput->get('catid')])? self::$catalogs[$jinput->get('catid')].'.js' : 'catalog.js';
                $catid = $jinput->get('catid');
			}else{
				$filename = "catalog.js";
				$catid = 'none';
			}
		
		//print_pre(self::$catalogs); echo "category input: ".$catid;
	
			$path = JPATH_ROOT . self::$_my_amplitude_playlist_path . $filename;
			$url = JURI::root(). self::$_my_amplitude_playlist_path . $filename;
		//echo "Using playlist: ".$path. " view = ".$jinput->get('view'). " id = ".$jinput->get('id'). " catid = ".$jinput->get('catid');
			if (! file_exists ( $path )) {
				echo "<br /><br />No playlist: ".$path."<br />";
				echo "Please save a product in MyMuse to generate playlists<br />";
				return null;
			}
         
            if($jinput->get('tmpl','') != "component" && $load_js){
                $document->addScript( $url);
            }
            
		//echo $path; exit;

			$playlist = file_get_contents ( $path );
			$playlist = preg_replace ( "~.*?Amplitude.init\(~", "", $playlist );
			$playlist = preg_replace ( "~\);$~", "", $playlist );
			$playlist = preg_replace ( "~//.*\\n~", "", $playlist );
			$playlist = preg_replace ( "~],~", "]", $playlist );
			
			$playarray = json_decode ( $playlist, true );
			if (! $playarray) {
				echo "<br /><br />".MyMuseHelper::getJsonError()."<br /><br />";
				echo "<br /><br />No playlist: ".$playlist."<br /><br />";
			}
			
			$new_arr = array ();
			foreach ( $playarray ['songs'] as $index => $song ) {
				$new_arr [$song ['url']] = $index;
			}
			$arr[0] = $new_arr;
			$arr[1] = $playarray ['songs'];
			
			self::$_playlist = $arr;
			//print_pre($arr);
           
		}
		return self::$_playlist;
	}

	/**
	 * amplitude
	 * onPrepareMyMuseMp3Player
	 */
	function onPrepareMyMuseMp3Player(&$track, $type='single', $height=0, $width=0, $index=0, $count=0)
	{
        $arr 			= self::getPlaylist();
        $this->indexes 	= $arr[0];
        $this->playlist = $arr[1];
      

		$document 	= JFactory::getDocument();
		$match 		= 0;
		$params 	= MyMuseHelper::getParams();

		if($type == 'singleplayer' || $type == 'single'){
			$id = 1;
		}else{
			$id = $index + 1;
		}

		if($type == 'singleplayer'){
            return '';
        }

		//SINGLE PLAYER MAKE PLAY BUTTONS//
		if($type=='single'){
            //get index
			$site_url = MyMuseHelper::getSiteUrl($track->parentid,1);
            $preview = $site_url.$track->file_preview;

            if(isset($this->indexes[$preview])){
                $index = $this->indexes[$preview];
            }else{
                $index = '0';
            }
//echo "index = $index <br />";
            $html = '
<div class="amplitude-song-container">
    <div class="amplitude-song-container amplitude-play-pause " amplitude-song-index="'.$index.'">
        <div class="play-pause" amplitude-main-play-pause="false"></div>
        <div class="playlist-meta">
            <div class="now-playing-title" style="display:none;">'.$this->playlist[$index]['name'].'</div>
            <div class="album-information" style="display:none;">'.$this->playlist[$index]['artist'].'</div>
        </div>
    </div>
</div>
';
            return $html;
            
			
		}
		
		if($type == 'playlist'){
			return '';
        }
		
	}
	
	function onPrepareMyMuseMp3PlayerControl(&$tracks)
	{

		return true;
	}
    
	/**
	 * amplitude
	 * onMyMuseAfterSave
	 * 
	 * Create catalog for amplitude
	 */
    function onMyMuseAfterSave()
    {

    	
        $text = '';
    	jimport('joomla.filesystem.file');
    	$db 		= JFactory::getDBO();
    	$catid 		= $this->params->get('my_amplitude_catid');
    	$params 	= MyMuseHelper::getParams();
   
        $playlist_path = JPATH_ROOT . rtrim($this->params->get('my_amplitude_playlist_path'),'/');
        if(!JFolder::exists($playlist_path)){
        	JFolder::create($playlist_path);
        }
        $playlist_path .= DS;
        $album_art_path = JPATH_ROOT . rtrim($this->params->get('my_amplitude_album_art_path'),'/');
        $album_art_path .= DS;
        
        $all = array();
        $first 					= new StdClass;
        $first->name 			= " ";
        $first->artist 			= $this->params->get('my_amplitude_first_artist');
        $first->album 			= $this->params->get('my_amplitude_first_album');
        $first->url				= JURI::root() .$this->params->get('my_amplitude_first_url');
        $first->cover_art_url	= JURI::root() .$this->params->get('my_amplitude_first_cover');
        $all['songs'][] 		= $first;
        $allcats = array();

        

        $query = "SELECT alias from #__categories WHERE id=$catid";
        $db->setQuery($query);
        $alias = $db->loadResult();
        
        $query = "SELECT id, alias from #__categories WHERE parent_id=$catid";
        $db->setQuery($query);
        $res = $db->loadObjectList();
        
        $res[] = (object) array('id' => $catid, 'alias' => $alias);

    	foreach($res as $r){
    		$filename = $r->alias.".js";
    		$text .= "Making playlist for $filename <br />";
    		$arr = array();
    		$arr['songs'][] = $first;
            //see if they have children
            $catin = array();
            $catin[] = $r->id;
            $allcats[] = $r->id;
            $child_query = "SELECT id, alias from #__categories WHERE parent_id=".$r->id;
            $db->setQuery($child_query);
    		if($childres = $db->loadObjectList()){
                foreach($childres as $child){
                    $catin[] = $child->id;
                    $allcats[] = $child->id;
                }
            }
            $track_query = $this->_getQuery($catin);
    		$db->setQuery($track_query);
    
    		if($tracks = $db->loadObjectList()){
    			
                $i = 0;
    			foreach ($tracks as $track){
    				$site_url = MyMuseHelper::getSiteUrl($track->parentid,1);
					$track->url = $site_url.$track->url;
					if(!$track->cover_art_url){
						$track->cover_art_url = $first->cover_art_url;
					}else{
						$track->cover_art_url = $site_url.$track->cover_art_url;
					}
					unset($track->parentid);
    				$arr['songs'][] = $track;
    			}
    			//print_pre($tracks); exit;
    			$jstring = "Amplitude.init(".json_encode($arr).");";
    			$jstring = preg_replace("~,~",",\n",$jstring);
    			$jstring = preg_replace("~\[~","[\n",$jstring);
    			$jstring = preg_replace("~\{~","{\n",$jstring);
    			$jstring = preg_replace("~\},~","\n},",$jstring);
    			$jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    
    			if($fh = fopen($playlist_path . $filename, "w")){
    				fwrite($fh,$jstring);
    				fclose($fh);
    				$text .= $playlist_path . $filename." <br />";
    			}else{
    				$text .= "Could not open file: ".$playlist_path . $filename;
    			}
    			//print_pre($jstring);
    			//echo "<br />";
    		}else{
    			$text .= "No tracks for $filename <br />";
    			//$text .= $track_query;
    		//echo $query;
    		}
    	}
    	
    	
    	//one for all
        $track_query = $this->_getQuery($allcats);
        $db->setQuery($track_query);
        if($tracks = $db->loadObjectList()){
            $i = 0;
            foreach ($tracks as $track){
            	$site_url = MyMuseHelper::getSiteUrl($track->parentid,1);
            	$track->url = $site_url.$track->url;
            	if(!$track->cover_art_url){
            		$track->cover_art_url = $first->cover_art_url;
            	}
            	unset($track->parentid);
                $all['songs'][] = $track;
            }
        }
    	$text .= "Making playlist for catalog.js <br />";
    	
		$jstring = "Amplitude.init(".json_encode($all).");";
    	$jstring = preg_replace("~,~",",\n",$jstring);
    	$jstring = preg_replace("~\[~","[\n",$jstring);
    	$jstring = preg_replace("~\{~","{\n",$jstring);
    	$jstring = preg_replace("~\},~","\n},",$jstring);
    	$jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    	
    	
    	$file =  $playlist_path . "catalog.js";
    	if($fh = fopen($file, "w")){
    		fwrite($fh,$jstring);
    		fclose($fh);
    		$text .= $file." <br />";
    		//$text .= $jstring." <br />";
    	}else{
    		$text .=  "Could not open file for writing $file";
    	}

        return $text;
    	
    }
    
    function _getQuery($catin)
    {
        $catin = implode(",",$catin);
        
        
        $track_query = "SELECT p.title as name, p.parentid, parent.title as album,
            a.title as artist,
        		
            p.file_preview as url,
            parent.list_image as cover_art_url
        		
            FROM #__mymuse_product as p
            LEFT JOIN #__mymuse_product as parent on p.parentid=parent.id
            LEFT JOIN #__categories as a on parent.artistid=a.id
            WHERE p.parentid>0 
        		AND (parent.catid IN (".$catin.") OR parent.artistid IN (".$catin.") )
        		AND p.product_allfiles=0
            ORDER BY parent.product_made_date DESC, parent.created DESC, p.title"; //
        return $track_query;
    }
}
