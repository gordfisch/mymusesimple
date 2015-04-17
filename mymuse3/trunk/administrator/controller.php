<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
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
	
		$language = JFactory::getLanguage();
		$extension = 'com_mymuse';
		$base_dir = JPATH_SITE;

		$language->load($extension, $base_dir, 'en-GB', true);
		$language->load($extension, $base_dir, null, true);
	
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
	
		$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
		$mailfrom = $params->get('contact_email');
		
		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$mailer->setSender(array($mailfrom,$fromname));
		$mailer->addRecipient($user_email);
		$mailer->setSubject($subject);
		$mailer->setBody($message);
		$send = $mailer->Send();
		if ( $send !== true ) {
			echo 'Error sending email: ' . $send->message;
		}
		
		//redirect to edit page
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
		<td valign="top" width="200"><img
			src="components/com_mymuse/assets/images/logo325.jpg"></td>
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
		
	function addEuroTac()
	{
		$sql = "
INSERT INTO `vl6xc_mymuse_tax_rate` (`id`, `state`, `province`, `country`, `tax_rate`, `tax_applies_to`, `tax_name`, `tax_format`, `compounded`, `ordering`, `checked_out`, `checked_out_time`) VALUES
(58, 1, '', 'AUT', 0.2000, 'C', 'VAT (AT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(59, 1, '', 'BEL', 0.2100, 'C', 'VAT (BE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(60, 1, '', 'BGR', 0.2000, 'C', 'VAT (BG)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(61, 1, '', 'CYP', 0.1900, 'C', 'VAT (CY)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(62, 1, '', 'CZE', 0.2100, 'C', 'VAT (CZ)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(63, 1, '', 'HRV', 0.2500, 'C', 'VAT (HR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(64, 1, '', 'DNK', 0.2500, 'C', 'VAT (DK)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(65, 1, '', 'EST', 0.2000, 'C', 'VAT (EE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(66, 1, '', 'FIN', 0.2400, 'C', 'VAT (FI)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(67, 1, '', 'FRA', 0.2000, 'C', 'VAT (FR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(68, 1, '', 'DEU', 0.1900, 'C', 'VAT (DE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(69, 1, '', 'GRC', 0.2300, 'C', 'VAT (GR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(70, 1, '', 'HUN', 0.2700, 'C', 'VAT (HU)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(71, 1, '', 'IRL', 0.2300, 'C', 'VAT (IE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(72, 1, '', 'ITA', 0.2200, 'C', 'VAT (IT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(73, 1, '', 'LTU', 0.2100, 'C', 'VAT (LT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(74, 1, '', 'LUX', 0.1700, 'C', 'VAT (LU)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(75, 1, '', 'MLT', 0.1800, 'C', 'VAT (MT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(76, 1, '', 'NLD', 0.2100, 'C', 'VAT (NL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(77, 1, '', 'POL', 0.2300, 'C', 'VAT (PL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(78, 1, '', 'PRT', 0.2300, 'C', 'VAT (PT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(79, 1, '', 'ROM', 0.2400, 'C', 'VAT (RO)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(80, 1, '', 'SVK', 0.2000, 'C', 'VAT (SK)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(81, 1, '', 'SVN', 0.2200, 'C', 'VAT (SI)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(82, 1, '', 'ESP', 0.2100, 'C', 'VAT (ES)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(83, 1, '', 'SWE', 0.2500, 'C', 'VAT (SE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(84, 1, '', 'GBR', 0.2000, 'C', 'VAT (GB)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(85, 1, '', 'EUU', 0.0000, 'C', 'VAT Exempt', 'RATE', '0', 1, 0, '0000-00-00 00:00:00');
	 		";
	}
}