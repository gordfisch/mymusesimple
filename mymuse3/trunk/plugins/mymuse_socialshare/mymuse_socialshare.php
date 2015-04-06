<?php
/*-----------------------------------------------------------------------
# plg_mymuse_socialshare - Social Share for MyMuse component
# -----------------------------------------------------------------------
# author: http://www.arboreta.ca
# copyright Copyright (C) 2015 arboreta. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.arboreta.ca
# Technical Support:  Forum - http://www.mymuse.ca/support
#-------------------------------------------------------------------------*/

/*------------------------------------------------------------------------
# Based on: plg_jo_k2_socialshare - JO K2 Social Share for k2 component
# -----------------------------------------------------------------------
# author: http://www.joomcore.com
# copyright Copyright (C) 2011 Joomcore.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomcore.com
# Technical Support:  Forum - http://www.joomcore.com/Support
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.error.error' );
jimport('joomla.plugin.plugin');
jimport( 'joomla.utilities.string' );

class PlgMymuseMymuse_socialshare extends JPlugin 
{
	var $pluginName = 'MyMuse socialshare';
	var $pluginNameHumanReadable = 'Social share on MyMuse product items';
	
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
	function PlgMymuseMymuse_socialshare(&$subject, $params) {
  
		parent::__construct($subject, $params);
	}
	

	function onProductBeforeHeader($context, &$item, &$params, $limitstart) {
		//above header
		if($this->params->get('positiondisplay') == 0) {
			return $this->mymusesocialshare($item, $params, $limitstart) ;
		}
	}
	
	function onProductAfterTitle($context, &$item, &$params, $limitstart) {
		if($this->params->get('positiondisplay') == 1) {
			return $this->mymusesocialshare($item, $params, $limitstart) ;
		}
	}
	
	function onProductBeforeDisplay($context,  &$item, &$params, $limitstart) {
		if($this->params->get('positiondisplay') == 2) {
			return $this->mymusesocialshare($item, $params, $limitstart) ;
		}
	}
	
	function onProductAfterDisplay($context, &$item, &$params, $limitstart) {
		if($this->params->get('positiondisplay') == 3) {
			return $this->mymusesocialshare($item, $params, $limitstart) ;
		}
	}
	
	function mymusesocialshare($item, $params, $limitstart) {
		
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$jinput = $app->input;
						
		$ex_categories = $this->params->get('ex_categories', '');
		if($ex_categories) {
			$categoriesArray = explode(",",$ex_categories);
			if(strlen(array_search($item->catid,$categoriesArray))){
				return;
			}
		}
 		
 		$ex_articles = $this->params->get('ex_articles');
		if($ex_articles){
			$articlesArray = explode(",",$ex_articles);
			if(strlen(array_search($item->id,$articlesArray))){
				return ;
			}
		}		
		require_once(JPATH_BASE.'/components/com_mymuse/helpers/route.php');
		if($item->id) {
			$link = JRoute::_(myMuseHelperRoute::getProductRoute($item->id,$item->catid));
			$jURI = JURI::getInstance();
			$link = $jURI->getScheme()."://".$jURI->getHost().$link;
		} else {
			$jURI = JURI::getInstance();
			$link = $jURI->toString();
		}
				
		if($item->list_image !=''){
			$imageUrl = JURI::getInstance()->toString(array('scheme', 'host', 'port')).$item->list_image;
		}else{
			preg_match_all('/src="([^"]+)"/i', @$item->text, $matches);
			if(!empty($matches[1][0])) {
				$imageUrl = JURI::root() . $matches[1][0]; 		
			}	
		}
				
		if(@$imageUrl !=''){		
			$document->addCustomTag( '<meta property="og:image" content="'.$imageUrl.'" />' );
			$document->addCustomTag( '<link href="'.$imageUrl.'" rel="image_src">' );
		}
		
		$document->addCustomTag( '<meta property="og:title" content="'.@$item->title.'" />' );
		$document->addCustomTag( '<meta property="og:url" content="'.$link.'" />' );
		//$document->addCustomTag( '<meta content="'.@strip_tags($item->text).'" property="og:description">' );
		
		$view = $jinput->get('view');
		$layout = $jinput->get('layout');
		$task = $jinput->get('task');
		$option = $jinput->get('option');
		
		$righttoleft = $this->params->get('righttoleft');
		$lang_fb = $this->params->get('lang_like');
		$showlike = $this->params->get('showlike');
		$liketype = $this->params->get('liketype');
		$positionlike = $this->params->get('positionlike');
		
		$showsend = $this->params->get('showsend');
		$positionsend = $this->params->get('positionsend');
		
		$lang_tweet = $this->params->get('lang_tweet');
		$showtwitter = $this->params->get('showtwitter');
		$twittertype = $this->params->get('twittertype');
		$positiontwitter = $this->params->get('positiontwitter');
		
		$lang_google = $this->params->get('lang_google');
		$showgoogle = $this->params->get('showgoogle');
		$googletype = $this->params->get('googletype');
		$positiongoogle = $this->params->get('positiongoogle');
		
		$showlinkedin = $this->params->get('showlinkedin');
		$linkedintype = $this->params->get('linkedintype');
		$positionbuttonlinkedin = $this->params->get('positionbuttonlinkedin');
		
		$showpinit = $this->params->get('showpinit');
		$pinittype = $this->params->get('pinittype');
		$positionbuttonpinit = $this->params->get('positionbuttonpinit');
		
		$showsubmit = $this->params->get('showsubmit');
		$submittype = $this->params->get('submittype');
		$positionbuttonsubmit = $this->params->get('positionbuttonsubmit');
		
		$showaddthis = $this->params->get('showaddthis');
		$addthistype = $this->params->get('addthistype');
		$positionbuttonaddthis = $this->params->get('positionbuttonaddthis');
		
		$showvklike = $this->params->get('showvklike');
		$vkliketype = $this->params->get('vkliketype');
		$vkappid = $this->params->get('vkappid');
		$positionbuttonvklike = $this->params->get('positionbuttonvklike');
		
		$showvkshare = $this->params->get('showvkshare');
		$vksharetype = $this->params->get('vksharetype');
		$vksharetext = $this->params->get('vksharetext');
		$positionbuttonvkshare = $this->params->get('positionbuttonvkshare');
		
		$positiondisplay = $this->params->get('positiondisplay');
		$showcategory = $this->params->get('showcategory');
		
		$mymuse_socialshare = array();		
		if($showlike){
			$mymuse_socialshare[$positionlike] = '
			<div class="jo_like">
				<div class="fb-like" data-href="'.$link.'" data-send="false" data-layout="'.$liketype.'" data-width="75" data-show-faces="false"></div>
			</div>';	
		}
		if($showsend){
			$mymuse_socialshare[$positionsend] = '<div class="jo_send"><div class="fb-send" data-href="'.$link.'"></div></div>';
		}
		if($showtwitter){
			$mymuse_socialshare[$positiontwitter] = '
			<div class="jo_twitter">
				<a href="http://twitter.com/share" class="twitter-share-button"  data-url="'.$link.'" data-count="'.$twittertype.'" data-lang="'.$lang_tweet.'" data-related="" >Tweet</a>
				<script>
					!function(d,s,id){
						var js,fjs=d.getElementsByTagName(s)[0];
						if(!d.getElementById(id)){
						      	js=d.createElement(s);
						      	js.id=id;js.src="//platform.twitter.com/widgets.js";
						      	fjs.parentNode.insertBefore(js,fjs);	      	
						}
					}(document,"script","twitter-wjs");
				</script>
			</div>';
		}
		if($showgoogle){
			$mymuse_socialshare[$positiongoogle] = '
			<div class="jo_google">
				<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
				  	{lang: "'.$lang_google.'"}
				</script>
				<div class="g-plusone" data-size="'.$googletype.'" data-href="'.$link.'"></div>
			</div>';
		}
		if($showlinkedin){
			$mymuse_socialshare[$positionbuttonlinkedin] = '
			<div class="jo_linkedin">
				<script type="text/javascript" src="http://platform.linkedin.com/in.js"></script><script type="in/share" data-counter="'.$linkedintype.'"></script></a>
			</div>';
		}
		if($showpinit){
			$mymuse_socialshare[$positionbuttonpinit] = '
			<div class="jo_pinit">
				<a href="http://pinterest.com/pin/create/button/?url='.urlencode($link).'&media='.urlencode($item->list_image).'&description='.urlencode($item->introtext).'" class="pin-it-button" count-layout="'.$pinittype.'"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>					
				<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
			</div>';
		}
		
		if($showsubmit){
			$mymuse_socialshare[$positionbuttonsubmit] = '
			<div class="jo_submit">
				<su:badge layout="'.$submittype.'" location="'.$link.'"></su:badge>
				<script type="text/javascript">
				  (function() {
				    var li = document.createElement("script"); li.type = "text/javascript"; li.async = true;
				    li.src = ("https:" == document.location.protocol ? "https:" : "http:") + "//platform.stumbleupon.com/1/widgets.js";
				    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(li, s);
				  })();
				</script>
			</div>';
		}
		
		if($showaddthis){
			$mymuse_socialshare[$positionbuttonaddthis] = '
			<div class="jo_addthis">
				<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=YOUR-PROFILE-ID"></script>
				<a class="'.$addthistype.'"></a>
			</div>';
		}
		
		if($showvklike){
			$mymuse_socialshare[$positionbuttonvklike] = '
			<div class="jo_vklike">
				<script type="text/javascript" src="//vk.com/js/api/openapi.js?62"></script>
				<script type="text/javascript">
				  VK.init({apiId: '.$vkappid.', onlyWidgets: true});
				</script>
				<div id="vk_like"></div>
				<script type="text/javascript">
				VK.Widgets.Like("vk_like", {type: "'.$vkliketype.'"});
				</script>
			</div>';
		}
		
		if($showvkshare){
			$mymuse_socialshare[$positionbuttonvkshare] = '
			<div class="jo_vkshare">
				<script type="text/javascript" src="http://vk.com/js/api/share.js?11" charset="windows-1251"></script>
				<script type="text/javascript"><!--
				document.write(VK.Share.button({url: "'.$link.'"},{type: "'.$vksharetype.'", text: "'.$vksharetext.'"}));
				--></script>
			</div>';
		}
		
		ksort($mymuse_socialshare);
		if ($righttoleft == 'rtl') {
			$mymuse_socialshare = array_reverse($mymuse_socialshare);
		}
		
		$mymusesocialcss = '
			.mymuse_socialshare{
				float: left;
				width: 100%;
				padding: 7px 0px;
			}
			.mymuse_socialshare iframe{				
				max-width: none;
			}
			.jo_like, .jo_send, .jo_twitter, .jo_google, .jo_linkedin, .jo_pinit, .jo_submit, .jo_addthis, .jo_vklike,  .jo_vkshare{
				float: left;
				margin: 5px;
			}
			.jo_vkshare tr, .jo_vkshare td{
				border: none;
			}
			
			';	
		
						
		if($liketype == 'button_count'){
			$mymusesocialcss .='				
				.jo_like, .jo_send, .jo_twitter, .jo_google, .jo_linkedin, .jo_pinit, .jo_submit, .jo_addthis, .jo_vklike,  .jo_vkshare{
					height: 20px;	
				}
				.jo_like{
					width: 80px;
				}';
		}else{
			$mymusesocialcss .='
				.jo_like, .jo_send, .jo_twitter, .jo_google, .jo_linkedin, .jo_pinit, .jo_submit, .jo_addthis, .jo_vklike,  .jo_vkshare{
					height: 65px;
				}
				.jo_like{
					width: 55px;
				}';
		}	
		
		if($pinittype == 'vertical'){
			$mymusesocialcss .='
				.jo_pinit{
					margin-top: 25px;
					height: 46px;
				}';
		}
		
		if($twittertype == 'Horizontal Count'){
			$mymusesocialcss .='
				.jo_twitter{
					width: 90px;
				}';
		}else{
			$mymusesocialcss .='
				.jo_twitter{
					width: 70px;
				}';
		}
		
		if($googletype == 'medium'){
			$mymusesocialcss .='
				.jo_google{
					width: 70px;
				}';
		}else{
			$mymusesocialcss .='
				.jo_google{
					width: 60px;
				}';
		}
		
		if($linkedintype == 'right'){
			$mymusesocialcss .='
				.jo_linkedin{
					width: 100px;
				}';
		}else{
			$mymusesocialcss .='
				.jo_linkedin{
					width: 70px;
				}';
		}	
		
		if($vkliketype =='button'){
			$mymusesocialcss .='
				.jo_vklike{
					width: 85px;
				}';
		}elseif($vkliketype){
			$mymusesocialcss .='
				.jo_vklike{
					width: 85px;
				}';				
		}
	
		$document->addStyleDeclaration($mymusesocialcss);
				
		if($showlike || $showsend){
			$html ='
				<div class="mymuse_socialshare">
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return;
						js = d.createElement(s); js.id = id;
						js.src = "//connect.facebook.net/'.$lang_fb.'/all.js#xfbml=1&appId=282604855153215";
						fjs.parentNode.insertBefore(js, fjs);
					}(document, "script", "facebook-jssdk"));
				</script>
			';
		}
		$html .= implode($mymuse_socialshare);
		$html .= '</div><div style="clear:both;"></div>';
		
		if($showcategory == 0 && $option == 'com_k2' && $view == 'itemlist'){
	  		$html = '';
	  	}
	  	
		return $html;
	}
    

	
	
}
?>
