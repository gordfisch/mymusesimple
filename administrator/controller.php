<?php
/**
 * @version     $Id$
 * @package     com_mymuse2.5
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


// No direct access
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.application.component.controller');

class MymuseController extends JControllerLegacy
{
	/*var error */
	var $error = null;
	
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/mymuse.php';

		// Load the submenu.
		$view = JRequest::getCmd('view', 'mymuse');

		if($view != "product"){
			MymuseHelper::addSubmenu($view);
		}
		$view		= JRequest::getCmd('view', 'mymuse');
        JRequest::setVar('view', $view);

		parent::display();

		return $this;
	}
	
	/**
	 * reset_downloads
	 * method to rest the downloads for a customer, then mail them the download linkdisplay
	 * 
	 * @return void
	 */
	function resetDownloads()
	{
		$params = MyMuseHelper::getParams();
		//reset
		$model = $this->getModel('order');
		$id = JRequest::getVar( 'id','' );

		if(!isset($id)){
			$this->msg = "Could not find Order number";
			$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id=', $this->msg );
			return false;
		}
	
		if(!$model->resetDownloads()){
			$this->msg = $model->getError();
			$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='.$id, $this->msg );
		}

		//email customer
		$date = date('Y-m-d h:i:s');
		if($params->get('my_debug')){
			$debug = $date."\n#####################\nORDER RESET\n";
			$debug .= "ORDER: ".$id. "\nSTATUS: C\n" ;
			MyMuseHelper::logMessage( $debug  );
		}
	
		JRequest::setVar( 'view', 'order' );
		JRequest::setVar( 'layout', 'order_customer');
		JRequest::setVar( 'task', 'mailcustomer'  );
		include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'mymuse.class.php' );
		$MyMuseStore	=& MyMuse::getObject('store','models');
		$store			= $MyMuseStore->getStore();
		$params = new JRegistry( $store->params );
	
		$language =& JFactory::getLanguage();
		$extension = 'com_mymuse';
		$base_dir = JPATH_SITE;
		$language_tag = $language->_lang;
		$language->load($extension, $base_dir, $language_tag, true);
	
	
		include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'templates'.DS.'mail_html_header.php' );
	
	
		ob_start();
		parent::display();
		$message .= ob_get_contents();
		ob_end_clean();
		$message  = $header.$message.$footer;
	
		$order = $model->getItem($id);
	
		$user 			=& JFactory::getUser($order->user_id);;
		$user_email 	= $user->email;
	
		// SEND MAIL TO BUYER
		$subject = Jtext::_('MYMUSE_ORDER_STATUS_CHANGED')." ".$store->title;
		$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
	
		$fromname = $store->contact_first_name." ".$store->contact_last_name;
		$mailfrom = $store->contact_email;
	
		JMail::sendMail($mailfrom, $fromname, $user_email, $subject, $message, 1);
		//redirect to edit edit
		$this->msg = "Downloads Reset";
		$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='.$id, $this->msg);
	}
	
	function addSampleData()
	{
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');
		$helper = new MyMuseUpdateHelper;
		if(!$helper->addSampleData()){
			$this->msg = "ERROR: ". $helper->error;
		
		}else{	
			$this->msg = "Sample Products Added";
		}
		//redirect to product
		$this->setRedirect( 'index.php?option=com_mymuse&view=products', $this->msg);
	}
	
	/**
	 * Method to add genres
	 *
	 * @access    public
	 */
	function addGenres()
	{
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');
		$db = JFactory::getDBO();
	
		$query = "SELECT id from #__categories WHERE title='Genres'";
		$db->setQuery($query);
		$parent_id = $db->loadResult();
	
		if(!$parent_id){
			$this->msg = JText::_("MYMUSE_CREATE_GENRES_CATEGORY");
			$this->setRedirect( 'index.php?option=com_mymuse', $this->msg);
			return false;
		}
	
		$genres = array(
				'Avant Garde',
				'Blues',
				'Classical',
				'Country',
				'Easy Listening',
				'Electronic',
				'Folk',
				'Hip-Hop/Rap',
				'Holiday',
				'Jazz',
				'Kids/Family',
				'Latin',
				'Metal/Punk',
				'Moods',
				'New Age',
				'Pop',
				'Raggae',
				'Rock',
				'Spiritual',
				'Spoken Word',
				'Urban/R&B',
				'World'
	
		)
		?>
				<table width="700">
				<tr>
					<td valign="top" width="200"><img src="components/com_mymuse/assets/images/logo325.jpg"></td>
					<td>
					
					<ul>
				<?php
	
				$i = 0;
				foreach($genres as $genre){
					$i++;
					echo "<li><strong>".JText::_("Creating genre: ")."$genre</strong><br />";
					$res = MyMuseUpdateHelper::makeCategory($genre, $parent_id);
					if(!$res){
						echo JText::_("Problem with creating category: $genre ");
					}else{
						echo JText::_("Created Catalog Category '$genre'");
						$catalog_cat_id = $db->insertid();
					}
					echo "<br />";
					echo "</li>
					";
				}
			
		
				echo "
				</ul>
				</td>
				</tr>
				</table>
				";
		    }
		
	

}