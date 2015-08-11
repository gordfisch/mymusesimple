<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

/**
* MyMuse shortcode plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgContentMymuse_shortcode extends JPlugin
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
	function plgMymuseMymuse_shortcode(&$subject, $config)  {
		
		parent::__construct($subject, $config);

	}
		
	/**
	 * Plugin that creates MyMuse Add to Cart and Preview buttons.
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property or the string to be parsed.
	 * @param   mixed    &$params  Additional parameters. 
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		if (is_object($row))
		{
			return $this->_parse($row->text, $params);
		}
	
		return $this->_parse($row, $params);
	}
	
	/**
	 * Creates MyMuse Add to Cart and Preview buttons.
	 *
	 * @param   string  &$text    The string to be cloaked.
	 * @param   mixed   &$params  Additional parameters. Parameter "mode" (integer, default 1)
	 *                             replaces addresses with "mailto:" links if nonzero.
	 *
	 * @return  boolean  True on success.
	 */
	protected function _parse(&$text, &$params)
	{
		if (JString::strpos($text, '{mymuseaddtocart') !== false)
		{
			$string = '';
			$pattern = '/{mymuseaddtocart[\s]*id=([\d]+)[\s]?}/i';
			
			
			$app		= JFactory::getApplication();
			$jinput 	= $app->input;
			$Itemid 	= $jinput->get("Itemid",'');
			
			while(preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)){
				//print_r($matches);
				$id 		= $matches[1][0];
				$string = '
				<form method="post" action="index.php" name="mymuseform_'.$id.'">
				<input type="hidden" name="option" value="com_mymuse" />
				<input type="hidden" name="task" value="addtocart" />
				
				<input type="hidden" name="Itemid" value="'.$Itemid.'" />
				<input type="hidden" name="productid[]" value='.$id.' />
				<input class="button" type="submit" value="'.JText::_('MYMUSE_ADD_TO_CART').'"
				title="'.JText::_('MYMUSE_ADD_TO_CART').'" />
				</form>
				';
				$text = substr_replace($text, $string, $matches[0][1], strlen($matches[0][0]));
			}
		}
		return true;
	}
}