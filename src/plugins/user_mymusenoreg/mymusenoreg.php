<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
defined('JPATH_BASE') or die;

jimport('joomla.utilities.date');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_ADMIN_PATH')){
	define('MYMUSE_ADMIN_PATH',JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mymuse');
}

require_once( MYMUSE_ADMIN_PATH.DS.'helpers'.DS.'mymuse.php' );



/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */
class plgUserMyMusenoreg extends JPlugin
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
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
	}
	
	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data
	 * @param	array	$options	Array holding options (remember, autoregister, group)
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options = array())
	{

		$instance = $this->_getUser($user, $options);
		
		// Load the profile data from the database.
		$app = JFactory::getApplication();
		$myparams = MyMuseHelper::getParams();
		$profile_key = $myparams->get('my_profile_key', 'mymuse');
		$userId = $instance->id;
		$db = JFactory::getDbo();
		$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
				' WHERE user_id = '.(int) $userId." AND profile_key LIKE '$profile_key.%'" .
				' ORDER BY ordering';
		$db->setQuery( $query);

		$results = $db->loadRowList();
		
		// Check for a database error.
		if ($db->getErrorNum())
		{
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}
		
		// Merge the profile data.
		$instance->profile = array();
		
		foreach ($results as $v)
		{
			$k = str_replace('mymuse.', '', $v[0]);
			$instance->profile[$k] = json_decode($v[1], true);
			if ($instance->profile[$k] === null)
			{
				$instance->profile[$k] = $v[1];
			}
			if($k == "region"){
				
				
				
			}
		}
		$session = JFactory::getSession();
		$session->set('user', $instance);

	
	}

	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_mymuse.noreg'))) {
			return true;
		}

		$myparams = MyMuseHelper::getParams();
		$profile_key = $myparams->get('my_profile_key', 'mymuse');
		
		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0) {

				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles' .
					' WHERE user_id = '.(int) $userId." AND profile_key LIKE '$profile_key.%'" .
					' ORDER BY ordering'
				);
				$results = $db->loadRowList();

				// Check for a database error.
				if ($db->getErrorNum())
				{
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}

				// Merge the profile data.
				$data->profile = array();

				foreach ($results as $v)
				{
					$k = str_replace('mymuse.', '', $v[0]);
					$val = json_decode($v[1], true);
					if($k == "region"){
						if(!isset($_REQUEST['layout'])){
							$val = $this->_getStateName($val);
						}
					}
					if($k == "shipping_region"){
						if(!isset($_REQUEST['layout'])){
							$val = $this->_getStateName($val);
						}
					}
					$data->profile[$k] = $val;
					
					if ($data->profile[$k] === null)
					{
						if($k == "region"){
							if(!isset($_REQUEST['layout'])){
								$v[1] = $this->_getStateName($v[1]);
							}
						}
						$data->profile[$k] = $v[1];
					}
					if ($data->profile[$k] === null)
					{
						if($k == "shipping_region"){
							if(!isset($_REQUEST['layout'])){
								$v[1] = $this->_getStateName($v[1]);
							}
						}
						$data->profile[$k] = $v[1];
					}
				}
			}

			if (!JHtml::isRegistered('users.url')) {
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}
			if (!JHtml::isRegistered('users.calendar')) {
				JHtml::register('users.calendar', array(__CLASS__, 'calendar'));
			}
			if (!JHtml::isRegistered('users.tos')) {
				JHtml::register('users.tos', array(__CLASS__, 'tos'));
			}
		}

		return true;
	}

	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if(substr ($value, 0, 4) == "http") {
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else {
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	public static function calendar($value)
	{
		if (empty($value)) {
			return JHtml::_('users.value', $value);
		} else {
			return JHtml::_('date', $value, null, null);
		}
	}

	public static function tos($value)
	{
		if ($value) {
			return JText::_('JYES');
		}
		else {
			return JText::_('JNO');
		}
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (!in_array($name, array('com_mymuse.noreg'))) {
			return true;
		}
		

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__).'/profiles');
		$form->loadFile('profile', false);

		$fields = MyMuseHelper::getNoRegFields();
	
		$tosarticle = $this->params->get('register_tos_article');
		$tosenabled = $this->params->get('register-require_tos', 0);
		
		// We need to be in the registration form, field needs to be enabled and we need an article ID
		if ($name != 'com_mymuse.noreg' || !$tosenabled || !$tosarticle)
		{
			// We only want the TOS in the registration form
			$form->removeField('tos', 'profile');
		}
		else
		{
			// Push the TOS article ID into the TOS field.
			$form->setFieldAttribute('tos', 'article', $tosarticle, 'profile');
		}
	
 
		foreach ($fields as $field) {

			// Case registration
			if ($name == 'com_mymuse.noreg') {
				// Toggle whether the field is required.
				
				if ($this->params->get('register-require_' . $field, 1) > 0) {
					$form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'profile');
					
				if($field == 'region'){
						$q = "SELECT '' as value, '".JText::_('MYMUSE_SELECT_REGION')."' as region, 0 as country_id  UNION 
								SELECT id as value, state_name as region, country_id FROM #__mymuse_state 
						ORDER by country_id, region";
						$form->setFieldAttribute($field, 'query', $q, 'profile');
					}
				if($field == 'country'){
					$db = JFactory::getDBO();
					$query = "SELECT * from `#__mymuse_store` WHERE id='1'";
					$db->setQuery($query);
					$store = $db->loadObject();
					$sparams = new JRegistry($store->params);
					$country_2_code = $sparams->get('country');
					$query = "SELECT country_3_code FROM #__mymuse_country WHERE 
					country_2_code='$country_2_code'";
		
					$db->setQuery($query);
					$country = $db->loadResult();
					
					$form->setFieldAttribute($field, 'default', $country, 'profile');
					
					$q = "SELECT '' as value, '".JText::_('MYMUSE_SELECT_COUNTRY')."' as country, 0 as ordering 
								UNION SELECT country_3_code as value, country_name as country, ordering 
								FROM #__mymuse_country ORDER by country";
					
					$form->setFieldAttribute($field, 'query', $q, 'profile');
			
						
					$countrystates = $this->listCountryState();
						$javascript = '
		var countrystates = new Array;
		';
		$i = 0;
		foreach ($countrystates as $k=>$items) {
			foreach ($items as $v) {
				$javascript .= "countrystates[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
			}
		}
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($javascript);
		
		$js = "/**
* Changes a dynamically generated list
* @param html obj The name of the list to change
* @param html obj The instigator of the change
* @param array A javascript array of list options in the form [key,value,text]
* @param string The original key that was selected
* @param string The original item value that was selected
*/
	function changeDynaList2( list, source, myarr, orig_key, orig_val) {

		var key = source.options[source.selectedIndex].value;

		// empty the list
		for (i in list.options.length) {
			list.options[i] = null;
		}
		i = 0;
		for (x in myarr) {
			if (myarr[x][0] == key) {
				opt = new Option();
				opt.value = myarr[x][1];
				opt.text = myarr[x][2];
	
				if ((orig_key == key && orig_val == opt.value) || i == 0) {
					opt.selected = true;
				}
				list.options[i++] = opt;
			}
		}
		list.length = i;
	}
	jQuery(document).ready(function(){
		changeDynaList2(jform_profile_region, jform_profile_country, countrystates,0,0); 
	});

		";
						$document->addScriptDeclaration($js);
					}
					
					if($field == 'shipping_region'){
						$q = "SELECT '' as value, '".JText::_('MYMUSE_SELECT_REGION')."' as shipping_region, 0 as country_id  UNION
								SELECT id as value, state_name as shipping_region, country_id FROM #__mymuse_state
						ORDER by country_id, shipping_region";
						$form->setFieldAttribute($field, 'query', $q, 'profile');
					}
					if($field == 'shipping_country'){
						$db = JFactory::getDBO();
						$query = "SELECT * from `#__mymuse_store` WHERE id='1'";
						$db->setQuery($query);
						$store = $db->loadObject();
						$sparams = new JRegistry($store->params);
						$country_2_code = $sparams->get('country');
						$query = "SELECT country_3_code FROM #__mymuse_country WHERE country_2_code='$country_2_code'";
					
						$db->setQuery($query);
						$country = $db->loadResult();

						$form->setFieldAttribute($field, 'default', $country, 'profile');
							
						$q = "SELECT '' as value, '".JText::_('MYMUSE_SELECT_COUNTRY')."' as 
								shipping_country UNION SELECT country_3_code as value, 
								country_name as shipping_country FROM #__mymuse_country 
								ORDER by shipping_country";
						
						$form->setFieldAttribute($field, 'query', $q, 'profile');
					}
				}
				else {
					$form->removeField($field, 'profile');
				}
				if($field == 'shopper_group'){
					$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
				}
				if($field == 'category_owner'){
					$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
				}
			}
			// Case profile in site or admin
			elseif ($name == 'com_admin.profile') {
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0) {
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
					if($field == 'shopper_group' && $name == 'com_users.profile'){
						$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
					}
					if($field == 'category_owner' && $name == 'com_users.profile'){
						$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
					}
				
				}
				else {
					$form->removeField($field, 'profile');
				}
			}
		}

		return true;
	}


	
    /**
     * listCountryState
     * Print a select box
     *
     * @param string $list_name
     * @param string $value
     * @return bool
     */
   function listCountryState($country_select='', $state_select='', $store_country='') {

		$db	= JFactory::getDBO();
		//echo "country = $country_select state = $state_select"; exit;
		$javascript = "onchange=\"changeDynaList( 'state', countrystates, document.adminForm.country.options[document.adminForm.country.selectedIndex].value, 0, 0);\"";
		
		$countries[] = JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_SELECT_COUNTRY').' -');
		$query = "SELECT id, country_3_code as value, country_name as text from #__mymuse_country ORDER BY country_name ASC";
		$db->setQuery($query);
		$dbcountries = $db->loadObjectList();
		$countries = array_merge($countries, $dbcountries);
		$lists['country'] = JHTML::_('select.genericlist',  $countries, 'country', 'class="inputbox" size="1" '.$javascript, 'value', 'text', $country_select);

	
		foreach ($dbcountries as $country)
		{
			$country_list[] = (int) $country->id;

			if ($country_select != '') {
				if ($country->value == $country_select) {
					$contentCountry = $country->text;
				}
			} 
		}

		$countrystates = array ();
		$countrystates[-1] = array ();
		$countrystates[-1][] = JHTML::_('select.option', '-1', JText::_( 'MYMUSE_SELECT_COUNTRY' ), 'id', 'title');
		$country_list = implode('\', \'', $country_list);

		$query = 'SELECT #__mymuse_state.id as code, state_name as title, #__mymuse_state.id as id, country_3_code, country_id' .
				' FROM #__mymuse_state,#__mymuse_country' .
				' WHERE country_id IN ( \''.$country_list.'\' )' .
				' AND #__mymuse_state.country_id=#__mymuse_country.id' .
				' ORDER BY country_id,state_name';

		$db->setQuery($query);
		$state_list = $db->loadObjectList();
		
		foreach ($dbcountries as $country)
		{

			$countrystates[$country->value] = array ();
			$rows2 = array ();
			foreach ($state_list as $state)
			{
				if ($state->country_3_code == $country->value) {
					$rows2[] = $state;
				}
			}
			foreach ($rows2 as $row2) {
				$countrystates[$country->value][] = JHTML::_('select.option', $row2->id, $row2->title, 'id', 'title');
			}
		}

		$countrystates['-1'][] = JHTML::_('select.option', '-1', JText::_( 'MYMUSE_SELECT_STATE' ), 'id', 'title');

		return $countrystates;
		
   }
   
   /**
    * This method will return a user object
    *
    * If options['autoregister'] is true, if the user doesn't exist yet he will be created
    *
    * @param	array	$user		Holds the user data.
    * @param	array	$options	Array holding options (remember, autoregister, group).
    *
    * @return	object	A JUser object
    * @since	1.5
    */
   protected function _getUser($user, $options = array())
   {
   	$instance = JUser::getInstance();
   	if ($id = intval(JUserHelper::getUserId($user['username'])))  {
   		$instance->load($id);
   		return $instance;
   	}
   
   	//TODO : move this out of the plugin
   	jimport('joomla.application.component.helper');
   	$config	= JComponentHelper::getParams('com_users');
   	// Default to Registered.
   	$defaultUserGroup = $config->get('new_usertype', 2);
   
   	$acl = JFactory::getACL();
   
   	$instance->set('id'			, 0);
   	$instance->set('name'			, $user['fullname']);
   	$instance->set('username'		, $user['username']);
   	$instance->set('password_clear'	, $user['password_clear']);
   	$instance->set('email'			, $user['email']);	// Result should contain an email (check)
   	$instance->set('usertype'		, 'deprecated');
   	$instance->set('groups'		, array($defaultUserGroup));
   
   	//If autoregister is set let's register the user
   	$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);
   
   	if ($autoregister) {
   		if (!$instance->save()) {
   			return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
   		}
   	}
   	else {
   		// No existing user and autoregister off, this is a temporary user.
   		$instance->set('tmp_user', true);
   	}
   
   	return $instance;
   }
   
   /** _getStateName
    * 
    * get state name based id
    * 
    * @param int id
    * @return string the state name
    */
   function _getStateName($id=0)
   {

   		if(!$id){
   			return '';
   		}
   		$db = JFactory::getDBO();
   		$query = "SELECT state_name FROM #__mymuse_state WHERE id=$id";
   		$db->setQuery($query);
   		$name = $db->loadResult();
   		return $name;
   }
}
