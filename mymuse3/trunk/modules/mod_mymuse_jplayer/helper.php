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

	static function getResults($params)
	{
		$db 			= JFactory::getDBO();
		$jnow			= JFactory::getDate();
		$now			= $jnow->toSql();
		$nullDate		= $db->getNullDate();
		$results 		= array();
		$MyMuseStore	=& MyMuse::getObject('store','models');
		$player 		=  $params->get('track_player');
		$root 			= JPATH_ROOT;
		$MyMuseHelper 	= new MyMuseHelper;
		$document 		= JFactory::getDocument();

		$type = $params->get('type_shown','tracks');
		
		$maximum_shown = $params->get('maximum_shown',5);
		$datenow = JFactory::getDate();
		$search = $params->get('type_search','p.created');
		
		$jinput 	= JFactory::getApplication()->input;

		if($jinput->get('view') == 'product' && $jinput->get('id')){
			$product_ids = $jinput->get('id');
		}
		if($jinput->get('view') == 'categories' && $jinput->get('id')){
			$catid = $jinput->get('id');
			//mymuseModelCategories

		}
		if($jinput->get('view') == 'category' && $jinput->get('id')){
			$catid = $jinput->get('id');
			//MyMuseModelCategory
			$model = JModelList::getInstance('Category', 'MyMuseModel', array('ignore_request' => false));
			//$model->populateState();
			
			if(!$state = $model->getState()){
				echo "no state";
				exit;
			}
			$params = $state->get('params');
			$params->set('category_layout',"_:tracks");
			$model->setState('params', $params);
			$tracks = $model->getItems();
			$results = $tracks[0];
			//print_pre($results);
			return $results;
		}

		
		
		echo "product_ids = $product_ids";
		
		if($product_ids && $type =="tracks"){
			
			//type = tracks
			$query = 'SELECT p.id, p.title, p.file_preview, p.file_preview_2, p.file_preview_3, p.parentid, p.file_downloads, p.file_type,
			pa.title as product_name, pa.list_image, pa.hits,
			c.id as artist_id, c.title as artist_name, s.sales as sales
			from #__mymuse_product as p
			LEFT JOIN #__categories as c on c.id=p.catid
			LEFT JOIN #__mymuse_product as pa on pa.id=p.parentid
			
			LEFT JOIN (SELECT sum(i.product_quantity) as sales, i.product_id, p.parentid,
			i.product_name, CASE WHEN parentid > 0 THEN parentid ELSE product_id END as all_id
			FROM #__mymuse_order_item as i
			LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
			GROUP BY i.product_id,i.product_name ) as s ON s.product_id = p.id
			
			WHERE c.published=1
			AND p.product_allfiles = 0
			AND p.product_downloadable=1
			AND p.state=1
			AND ( p.publish_up = '.$db->Quote($nullDate).' OR p.publish_up <= '.$db->Quote($now).' )
			AND ( p.publish_down = '.$db->Quote($nullDate).' OR p.publish_down >= '.$db->Quote($now).' )
			AND p.parentid > 0
			AND pa.state=1
			AND pa.id IN('.$product_ids.');
			';
			
		}else{
			//type = tracks
			$query = 'SELECT p.id, p.title, p.file_preview, p.file_preview_2, p.file_preview_3, p.parentid, p.file_downloads, p.file_type,
			pa.title as product_name, pa.list_image, pa.hits,
			c.id as artist_id, c.title as artist_name, s.sales as sales
			from #__mymuse_product as p
			LEFT JOIN #__categories as c on c.id=p.catid
			LEFT JOIN #__mymuse_product as pa on pa.id=p.parentid
				
			LEFT JOIN (SELECT sum(i.product_quantity) as sales, i.product_id, p.parentid,
			i.product_name, CASE WHEN parentid > 0 THEN parentid ELSE product_id END as all_id
			FROM #__mymuse_order_item as i
			LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
			GROUP BY i.product_id,i.product_name ) as s ON s.product_id = p.id

			WHERE c.published=1
			AND p.product_allfiles = 0
			AND p.product_downloadable=1
			AND p.state=1
			AND ( p.publish_up = '.$db->Quote($nullDate).' OR p.publish_up <= '.$db->Quote($now).' )
			AND ( p.publish_down = '.$db->Quote($nullDate).' OR p.publish_down >= '.$db->Quote($now).' )
			AND p.parentid > 0
			AND pa.state=1
			';
			if($search == 'p.featured'){
				$query .= 'AND p.featured = 1 ';
			}
			$query .= '
					
			ORDER BY '.$search.' DESC, artist_name ASC LIMIT 0,'.$maximum_shown;
		}
		//echo $query;
		$db->setQuery($query);
		if(!$results = $db->loadObjectList()){
			return $results;
		}
		
		
		
		
		for($i=0; $i < count($results); $i++){
			$id = ($results[$i]->parentid)? $results[$i]->parentid : $results[$i]->id;
			$results[$i]->product_link = myMuseHelperRoute::getProductRoute($id,$results[$i]->artist_id );
			$results[$i]->artist_link = myMuseHelperRoute::getCategoryRoute($results[$i]->artist_id);
			$results[$i]->flash = '';
			
			if($params->get('type_shown', 'tracks') == "tracks" && $results[$i]->file_preview){
				$trs = array();
				
				if(1 == $params->get('my_previews_in_one_dir')){
					$site_url = $params->get('my_use_s3')? $params->get('my_s3web') : preg_replace("#administrator/#","",JURI::base()).$params->get('my_preview_dir').'/';
				}else{
					$artist_alias = MyMuseHelper::getArtistAlias($results[$i]->parentid, '1');
					$album_alias = MyMuseHelper::getAlbumAlias($results[$i]->parentid, '1');
				
					$site_url = $params->get('my_use_s3')? $params->get('my_s3web') : preg_replace("#administrator/#","",JURI::base());
					$site_url .= $params->get('my_use_s3')? '' :  $params->get('my_preview_dir');
					$site_url .=  '/'.$artist_alias.'/'.$album_alias.'/';
				}
				
				


				$results[$i]->path = $site_url.$results[$i]->file_preview;
				//$results[$i]->real_path = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview;
				
				if($results[$i]->file_preview_2){
					$results[$i]->path_2 = $site_url.$results[$i]->file_preview_2;
					//$results[$i]->real_path_2 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview_2;
				}
				if($results[$i]->file_preview_3){
					$results[$i]->path_3 = $site_url.$results[$i]->file_preview_3;
					//$results[$i]->real_path_3 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview_3;
				}

							
				
				$id = $results[$i]->id;

				$results[$i]->flash .= 	 '<ul>
				
				<li id="mod_track_pause_li_'.$id.'" class="jp-pause ui-state-default ui-corner-all"><a id="mod_track_pause_'.$id.'" href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">'.JText::_('MYMUSE_PAUSE').'</a></li>
				<li id="mod_track_play_li_'.$id.'" class="jp-play ui-state-default ui-corner-all"><a id="mod_track_play_'.$id.'" href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">'.JText::_('MYMUSE_PLAY').'</a></li>
						</ul>';

				$supplied = array();
				$trs[0]['src'] = $results[$i]->path;
				$ext = pathinfo($results[$i]->path, PATHINFO_EXTENSION);
				if($ext == "ogg"){
					$ext = "oga";
				}
				if(!isset($MyMuseHelper->extarray[$ext])){
					continue;
				}
				$trs[0]['type'] = @$MyMuseHelper->extarray[$ext];
				$trs[0]['ext'] = $ext;
					
				$supplied[] = $ext;
					

				if(isset($results[$i]->file_preview_2) && $results[$i]->file_preview_2 != ''){
					$trs[1]['src'] = $results[$i]->path_2;
					$ext = pathinfo($results[$i]->path_2, PATHINFO_EXTENSION);
					if($ext == "ogg"){
						$ext = "oga";
					}
					$trs[1]['type'] = $MyMuseHelper->extarray[$ext];
					$trs[1]['ext'] = $ext;

					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
				}
				if(isset($results[$i]->file_preview_3) && $results[$i]->file_preview_3 != ''){
					$trs[2]['src'] = $results[$i]->path_3;
					$ext = MyMuseHelper::getExt($results[$i]->path_3);
					if($ext == "ogg"){
						$ext = "oga";
					}
					$trs[2]['type'] = $MyMuseHelper->extarray[$ext];
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
				//$media .= ', title: "'.$results[$i]->title.'"';
					
				$results[$i]->title = preg_replace("/\"/","",$results[$i]->title);
				$js = '';


				$js .= '
			jQuery(document).ready(function(){ 
				jQuery("#mod_track_play_'.$results[$i]->id.'").click( function(e) {
					var title = "'.$results[$i]->title.'";
					jQuery("#jp-title-li").html(title);

					jQuery("#mod_track_play_'.$results[$i]->id.'").css("display","none");
					jQuery("#mod_track_play_li_'.$results[$i]->id.'").css("display","none");
					jQuery("#mod_track_pause_'.$results[$i]->id.'").css("display","block");
					jQuery("#mod_track_pause_li_'.$results[$i]->id.'").css("display","block");
					myCirclePlayer.player.jPlayer("setMedia",{ '.$media.' });
					myCirclePlayer.player.jPlayer("play");

					return false;
				});

				jQuery("#mod_track_pause_'.$results[$i]->id.'").click( function(e) {

					jQuery("#mod_track_play_'.$results[$i]->id.'").css("display","block");
					jQuery("#mod_track_play_li_'.$results[$i]->id.'").css("display","block");
					jQuery("#mod_track_pause_'.$results[$i]->id.'").css("display","none");
					jQuery("#mod_track_pause_li_'.$results[$i]->id.'").css("display","none");
					myCirclePlayer.player.jPlayer("stop");
					return false;
				})

			});
				';
				//$document->addScriptDeclaration($js);
				$results[$i]->flash .= '<!-- End Player -->';
			}
		}

		return $results;
	}


}
?>
