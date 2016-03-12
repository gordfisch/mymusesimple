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
class plgMymuseAudio_html5_nxg extends JPlugin
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
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	function plgMymuseAudio_html5_nxg(&$subject, $config)  {
		parent::__construct($subject, $config);

        $app = JFactory::getApplication();
        if ($app->isAdmin()) return;
		$document = JFactory::getDocument();
		$this->language = $document->language;
		$this->direction = $document->direction;
		$this->getCategories();
        $arr = self::getPlaylist();
        $this->indexes = $arr[0];
        $this->playlist = $arr[1];
        

        $js_path = JURI::root().'plugins/mymuse/audio_amplitude/js/amplitude.min.js';
        $document->addScript( $js_path );
        $css_path = JURI::root().'plugins/mymuse/audio_amplitude/css/amplitude.css';
        $document->addStyleSheet($css_path);
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
		$catid = $this->params('my_amplitude_catid');
		$category = $categories->get($catid);
		$children = $category->getChildren();
		$array[] = $category->id;
		foreach($children as $child){
			$array[] = $child->id;
		}
		$this->catalogs = $array;
		
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
         
			$site_url = JURI::root();
			$document = JFactory::getDocument();
		
			$jinput = JFactory::getApplication()->input;
            $option = $jinput->get('option');

			if($jinput->get('view') == "category" && null !== $jinput->get('id') && array_key_exists($jinput->get('id'),self::$catalogs)){
				$filename = self::$catalogs[$jinput->get('id')];
            }elseif($jinput->get('view') == "product" && null !== $jinput->get('id') && $jinput->get('catid')){
                $filename = self::$catalogs[$jinput->get('catid')];
			}else{
				$filename = "catalog.js";
			}

		//echo "Using playlist: ".$filename. " view = ".$jinput->get('view'). " id = ".$jinput->get('id');
			$path = JPATH_ROOT . "/media/audio/playlists/" . $filename;
			$js_path = $site_url . "media/audio/playlists/" . $filename;
			if (! file_exists ( $path )) {
				$path = JPATH_ROOT . "/media/audio/playlists/catalog.js";
				$js_path = $site_url . "media/audio/playlists/catalog.js";
			}
         
            if($jinput->get('tmpl','') != "component" && $load_js){
                $document->addScript( $js_path);
            }
		//echo $path; exit;
			$playlist = file_get_contents ( $path );
			$playlist = preg_replace ( "~.*?Amplitude.init\(~", "", $playlist );
			$playlist = preg_replace ( "~\);$~", "", $playlist );
			$playlist = preg_replace ( "~//.*\\n~", "", $playlist );
			$playlist = preg_replace ( "~],~", "]", $playlist );
			
			$playarray = json_decode ( $playlist, true );
			if (! $playarray) {
				echo "<br /><br />".self::getJsonError()."<br /><br />";
				echo "<br /><br />".$playlist."<br /><br />";
			}
			
			$new_arr = array ();
			foreach ( $playarray ['songs'] as $index => $song ) {
				$new_arr [$song ['url']] = $index;
			}
			$arr[0] = $new_arr;
			$arr[1] = $playarray ['songs'];
			self::$_playlist = $arr;
		}
		return self::$_playlist;
	}

	/**
	 * amplitude
	 * onPrepareMyMuseMp3Player
	 */
	function onPrepareMyMuseMp3Player(&$track, $type='single', $height=0, $width=0, $index=0, $count=0)
	{
        $arr = self::getPlaylist();
        $this->indexes = $arr[0];
        $this->playlist = $arr[1];
        


		$document = JFactory::getDocument();
		$match = 0;
		$site_url = preg_replace("#administrator/#","",JURI::base());
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
            $preview = "/media/audio/previews/".$track->file_preview;

            if(isset($this->indexes[$preview])){
                $index = $this->indexes[$preview];
            }else{
                $index = '0';
            }

            $html = '
<div class="amplitude-song-container">

    <div class="amplitude-song-container amplitude-play-pause" amplitude-song-index="'.$index.'">
        <div class="play-pause" amplitude-main-play-pause="true"></div>
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
    
    function onMyMuseAfterSave()
    {
        $text = '';
    	jimport('joomla.filesystem.file');
    	$db = JFactory::getDBO();
    	$catid = $this->params('my_amplitude_catid');
    	$params 	= MyMuseHelper::getParams();
    	
        $all = array();
        $first = new StdClass;
        $first->name = " ";
        $first->artist = $this->params('my_amplitude_first_artist');
        $first->album = $this->params('my_amplitude_first_album');
        $first->url=$params->get('my_preview_dir').'/'.$this->params('my_amplitude_first_url');
        $first->cover_art_url=$this->params('my_amplitude_first_cover');
        $all['songs'][] = $first;
        $allcats = array();
    	
        $query = "SELECT id, alias from #__categories WHERE parent_id=$catid";
        $db->setQuery($query);
        $res = $db->loadObjectList();
        
    	foreach($res as $r){
    		$filename = $r->alias.".js";
    		$text .= "Making list for $filename <br />";
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
                echo '<br />';
    			foreach ($tracks as $track){
                    $path = JPATH_ROOT.$track->url;
                    if(!file_exists($path)){
                        //echo "$i ".$path."<br />\n";
                        $i++;
                    }
    				$arr['songs'][] = $track;
    			}
    
    			$jstring = "Amplitude.init(".json_encode($arr).");";
    			$jstring = preg_replace("~,~",",\n",$jstring);
    			$jstring = preg_replace("~\[~","[\n",$jstring);
    			$jstring = preg_replace("~\{~","{\n",$jstring);
    			$jstring = preg_replace("~\},~","\n},",$jstring);
    			$jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    
    			if($fh = fopen(JPATH_ROOT.DS.'media'.DS.'audio'.DS.'playlists'.DS.$filename, "w")){
    				fwrite($fh,$jstring);
    				fclose($fh);
    			}
    			//print_pre($jstring);
    			//echo "<br />";
    		}else{
    			$text .= "No tracks for $filename <br />";
    		//echo $query;
    		}
    	}
    	//one for all
        $track_query = $this->_getQuery($allcats);
        $db->setQuery($track_query);
        if($tracks = $db->loadObjectList()){
            $i = 0;
            foreach ($tracks as $track){
                $all['songs'][] = $track;
            }
        }
    	$text .= "Making list for catalog.js <br />";
		$jstring = "Amplitude.init(".json_encode($all).");";
    	$jstring = preg_replace("~,~",",\n",$jstring);
    	$jstring = preg_replace("~\[~","[\n",$jstring);
    	$jstring = preg_replace("~\{~","{\n",$jstring);
    	$jstring = preg_replace("~\},~","\n},",$jstring);
    	$jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    
    	if($fh = fopen(JPATH_ROOT.DS.'media'.DS.'audio'.DS.'playlists'.DS.'catalog.js', "w")){
    		fwrite($fh,$jstring);
    		fclose($fh);
    	}

        return $text;
    	
    }
    
    function _getQuery($catin)
    {
        $catin = implode(",",$catin);
        $track_query = "SELECT p.title as name, parent.title as album,
            p.product_sku as artist,
        		
            CONCAT('/media/audio/previews/',p.file_preview) as url,
            CONCAT('/images/releases/180px/',parent.product_sku,'_sm.jpg') as cover_art_url
        		
            FROM #__mymuse_product as p
            LEFT JOIN #__mymuse_product as parent on p.parentid=parent.id
            LEFT JOIN #__categories as a on parent.artistid=a.id
            WHERE p.parentid>0 AND parent.catid IN (".$catin.") AND p.product_allfiles=0
            ORDER BY parent.product_made_date DESC, parent.created DESC, p.product_sku"; //
        return $track_query;
    }
}
