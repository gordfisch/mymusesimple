<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modMyMuseLatestHelper
{
	
	function getResults($params)
	{
		$db = JFactory::getDBO();
		$jnow			=& JFactory::getDate();
		$now			= $jnow->toSql();
		$nullDate		= $db->getNullDate();
		$results 		= array();
		$MyMuseStore	=& MyMuse::getObject('store','models');
		$player 		=  $params->get('track_player');
		$root = JPATH_ROOT;


		$mm_params  =& MyMuseHelper::getParams();
		//print_r($mm_params); exit;
		$type = $params->get('type_shown','tracks');
		$maximum_shown = $params->get('maximum_shown',5);
		$datenow =& JFactory::getDate();
		$search = $params->get('type_search');
		if($type =="albums"){
			if($search == "pa.hits"){ $search = "p.hits"; }
			$query = 'SELECT p.id, p.title as product_name, p.list_image, p.parentid, p.hits,
			c.id as artist_id, c.title as artist_name
			from #__mymuse_product as p
			LEFT JOIN #__categories as c on c.id=p.catid
			WHERE c.published=1
			AND p.state=1
			AND ( p.publish_up = '.$db->Quote($nullDate).' OR p.publish_up <= '.$db->Quote($now).' )
			AND ( p.publish_down = '.$db->Quote($nullDate).' OR p.publish_down >= '.$db->Quote($now).' )
			AND p.parentid=0
			ORDER BY '.$search.' DESC LIMIT 0,'.$maximum_shown;
		}else{
			$query = 'SELECT p.id, p.title, p.file_preview, p.parentid, p.file_downloads, p.file_type,
			pa.title as product_name, pa.list_image, pa.hits,
			c.id as artist_id, c.title as artist_name
			from #__mymuse_product as p
			LEFT JOIN #__categories as c on c.id=p.catid
			LEFT JOIN #__mymuse_product as pa on pa.id=p.parentid
			WHERE c.published=1
			AND p.product_downloadable=1
			AND p.state=1
			AND ( p.publish_up = '.$db->Quote($nullDate).' OR p.publish_up <= '.$db->Quote($now).' )
			AND ( p.publish_down = '.$db->Quote($nullDate).' OR p.publish_down >= '.$db->Quote($now).' )
			AND p.parentid > 0
			ORDER BY '.$search.' DESC LIMIT 0,'.$maximum_shown;
		}

		$db->setQuery($query);
		if(!$results = $db->loadObjectList()){
			return $results;
		}

		for($i=0; $i < count($results); $i++){
			$id = ($results[$i]->parentid)? $results[$i]->parentid : $results[$i]->id;
			$results[$i]->product_link = myMuseHelperRoute::getProductRoute($id,$results[$i]->artist_id );
			$results[$i]->artist_link = myMuseHelperRoute::getCategoryRoute($results[$i]->artist_id);

			$results[$i]->flash = '';
			if($params->get('type_shown') == "tracks" && $results[$i]->file_preview){
				$artist_alias = MyMuseHelper::getArtistAlias($results[$i]->parentid,'1');
				$album_alias = MyMuseHelper::getAlbumAlias($results[$i]->parentid);
				$path = str_replace($root,'',$mm_params->get('my_preview_dir'));
				$path .= DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview;
				$site_url = preg_replace("#administrator/#","",JURI::base());
				if($results[$i]->file_type == "audio"){
					$player_path = $site_url."plugins".DS."mymuse".DS."mp3player_dewplayer".DS."mp3players".DS.'dewplayer-mini.swf';
				}else{
					$player_path = $site_url."plugins".DS."mymuse".DS."vidplayer_dewplayer".DS."vidplayers".DS.'dewtube.swf';		
				}
					
				$id = $results[$i]->id;
				$results[$i]->flash = '<!-- Begin Flash Player -->';
				$results[$i]->flash .= '<object type="application/x-shockwave-flash" 
			data="'. $player_path .'" 
			id="flash_'.$id.'">
			<param name="movie" value="'. $player_path .'" />
			';
			if($results[$i]->file_type == "audio"){
			$results[$i]->flash .= '<param name="flashvars" value="mp3='. $path .'" />
				<param name="wmode" value="transparent" />
				';
			}else{
				$results[$i]->flash .= '<param name="allowFullScreen" value="true" />
				<param name="quality" value="high" />
				<param name="bgcolor" value="#000000" />
				<param name="flashvars" value="movie='. $path .'&height=100&width=120" />
				';
			}
				
			$results[$i]->flash .= '</object>';
				
				
				
				
				$results[$i]->flash .= '<!-- End Flash Player -->';

			}
		}
		return $results;
	}


}
?>
