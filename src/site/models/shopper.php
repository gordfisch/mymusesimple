<?php
/**
 * @version		$Id: shopper.php 1990 2018-06-20 01:35:38Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

class mymuseModelShopper extends JModelForm
{
	/**
	 * Shopper id
	 *
	 * @var int
	 */
	var $_id = null;
	
	/**
	 * var object The shopper
	 */
	var $_shopper = null;
	
	/**
	 * var string error
	 */
	var $error = null;
	
	/**
	 * var object data
	 */
	var $data = null;
	
	
	/**
	 * __construct
	 * 
	 * 
	 */
	function __construct( )
	{
		parent::__construct();
		$user	= JFactory::getUser();
		$this->getShopper();
	}
	
    
	/**
	 * getShopper
	 * 
	 * @return mixed object The shopper object or false
	 */
	function &getShopper()
	{

		// Lets load the data if it doesn't already exist

        if (empty( $this->_shopper ))
        {
        	$params = MyMuseHelper::getParams();
        	$user	= JFactory::getUser();
        	$jinput = JFactory::getApplication()->input;
        	$task 	= $jinput->get('task');
        	$db 	= JFactory::getDBO();
        	$session = JFactory::getSession();
        	$guestcheckout = $session->get('guestcheckout');
        	$MyMuseCart		=& MyMuse::getObject('cart','helpers');
        	$cart = $MyMuseCart->cart;
        	$shipping_needed = 0;
        	for ($i=0;$i<$cart["idx"];$i++) {
        		if(isset($cart[$i]["product_phisical"]) && $cart[$i]["product_physical"] && $params->get('my_use_shipping')){
        			$shipping_needed = 1;
        		}
        	}

        	//if this is no reg coming in for a download
        	if(($params->get('my_registration') == "no_reg" || $params->get('my_registration') == "full_guest") 
        			&& !$user->get('id') 
        			&& ($task == 'accdownloads' || $task == 'downloads') ){
        		$id = $jinput->get('id','');
        		if(!$id){
        			$this->setError(JText::_('MYMUSE_NO_DOWNLOAD_KEY'));
        			return false;
        		}
        		$query = "SELECT notes from #__mymuse_order where order_number='$id'";
        		$db->setQuery($query);
        		$notes = $db->loadResult();
        		if(!$notes){
        			$this->setError(JText::_('MYMUSE_NO_MATCHING_ORDER'));
        			return false;
        		}
        			
        		if(!$this->make_no_register()){
        			return false;
        		}
        		$registry = new JRegistry;
        		$registry->loadString($notes);
        		$fields = MyMuseHelper::getNoRegFields();
        	
        		foreach ($fields as $field){
        			if($registry->get($field)){
        				$_POST['jform']['profile'][$field] = $registry->get($field);
        			}
        		}
        		$this->savenoreg();

        		return $this->_shopper;
        	}
        	
        	
        	// guest or regular user
        	if($task == 'guestcheckout' || $guestcheckout){
				$my_profile_key = 'mymusenoreg';
        	}else{
        		$my_profile_key = $params->get('my_profile_key','mymuse');
        	}
        	
			if($user->get('id') > 0)
			{
				
				$this->_shopper = clone $user;
				$this->_id = $user->get('id');
				$this->_shopper->user_id = $user->get('id');
	
				$profile = (null !== $this->_shopper->get('profile'))? $this->_shopper->get('profile') : array();
		
				if(count($profile) < 1) {
					//try to load their profile
					
					if(!$this->loadProfile($this->_shopper)){
						$this->_shopper->perms = 0;
						return $this->_shopper;
					}else{
						$profile = $this->_shopper->profile;
					}
				}
				
				
				
				
				if(!isset($profile['shopper_group'])){
					$profile['shopper_group'] = 1;
				}
				$this->_shopper->perms = 1;
				
				//is there a profile to fill in?
				if(($params->get('my_registration') == "full" || $params->get('my_registration') == "full_guest")
						&& $my_profile_key != ''){
					
					//I want to see if any fields that are required have not been filled in
					$plugin = JPluginHelper::getPlugin('user', $my_profile_key);

    				$profile_params = new JRegistry();
    				if(isset($plugin->params)){
    					$profile_params->loadString($plugin->params);

    					$fields = array_keys(json_decode($plugin->params, true));

    					foreach ($fields as $f) {
    						$field = preg_replace("/register-require_/",'',$f);
    						if (
    								$profile_params->get('register-require_' . $field, 1) == 2 &&
    								(!isset($profile[$field]) || $profile[$field] == "")
    						) {
    							//this guy needs to update profile
								$this->setError(JText::_('MYMUSE_MISSING').$field);
    							$this->_shopper->perms = 0;
    							return $this->_shopper;
    						}
    					}
    				}


				}elseif($params->get('my_registration') == "jomsocial"){
					$this->_shopper->perms = 1;
					
					//I want to see if any fields that are required have not been filled in
					$query = "SELECT id,name FROM #__community_fields WHERE 
					required = 1 AND registration = 1 and type != 'group'";
					$db->setQuery($query);
					$fields = $db->loadObjectList();
					$user_id = $user->get('id');
					foreach($fields as $field){
						$query = "SELECT value FROM #__community_fields_values WHERE
						user_id=$user_id and field_id=".$field->id;
						$db->setQuery($query);
						$value = $db->loadResult();
						if(!$value || $value = ""){
							$this->_shopper->perms = 0;
						}
					}
				}elseif($params->get('my_registration') == "cb"){
					$this->_shopper->perms = 1;
					
					//I want to see if any fields that are required have not been filled in
					$query = "SELECT id,name FROM #__comprofiler_fields WHERE 
					required = 1 AND registration = 1 and table = 'comprofiler'";
					$db->setQuery($query);
					$fields = $db->loadObjectList();
					$user_id = $user->get('id');
					foreach($fields as $field){
						$query = "SELECT ".$field->name." as value FROM #__comprofiler WHERE
						user_id=$user_id";
						$db->setQuery($query);
						$value = $db->loadResult();
						if(!$value || $value = ""){
							$this->_shopper->perms = 0;
						}
					}
				}elseif($params->get('my_registration') == "no_reg"){ 
					
					$fields = MyMuseHelper::getNoRegFields();
					
					//I want to see if any fields that are required have not been filled in
					$plugin = JPluginHelper::getPlugin('user', 'mymusenoreg');
					$profile_params = new JRegistry();
					$needed = 0;
					if(isset($plugin->params)){
						$profile_params->loadString($plugin->params);
						foreach ($fields as $field) {
							if(isset($profile[$field])){
								$value = $profile[$field];
								if (
									$profile_params->get('register-require_' . $field, 1) == 2 &&
									(!isset($value) || $value == "") &&
									!preg_match('/shipping/', $field)
								) {
									//this guy needs to update profile
									$needed++;
								}
								if (	
									$profile_params->get('register-require_' . $field, 1) == 2 &&
									$shipping_needed && preg_match('/shipping/', $field) &&
									(!isset($value) || $value == "")
								) {
									//this guy needs to update profile
									$needed++;
								}
							}
						}
					}

					
					if($needed){
						$this->_shopper->perms = 0;
					}
					reset($fields);
					foreach($fields as $field){
						if($user->get($field)){
							$this->_shopper->$field = $user->get($field);
							$_REQUEST[$field] = $user->get($field);
						}
					}

				
				}
				
				if(!isset($profile['shopper_group']) || $profile['shopper_group'] < 1){
						$profile['shopper_group'] = 1;
				}
				$query = 'SELECT *'
				. ' FROM #__mymuse_shopper_group'
				. ' WHERE id = '.$profile['shopper_group']
				;

				$db->setQuery( $query );
				$this->_shopper->shopper_group = $db->loadObject();
				$this->_shopper->discount = $this->_shopper->shopper_group->discount;
				$this->_shopper->shopper_group_name = $this->_shopper->shopper_group->shopper_group_name;
				
			}else{
				$this->_shopper = new stdClass;
				$this->_shopper->id = 0;
				$this->_shopper->shopper_group = new stdClass;
				$this->_shopper->shopper_group->discount = 0;
				$this->_shopper->shopper_group->id = $params->get("my_default_shopper_group_id");
				$this->_shopper->shopper_group_name = 'default';
				$this->_shopper->state = null;
				$this->_shopper->country = null;
				$this->_shopper->perms = null;
				$this->_shopper->user_id = null;
				
			}
		}

		return $this->_shopper;
	}
	/**
	 * getShopperByUser
	 * 
	 * @param object $user The user object
	 * @return mixed object The shopper object or false
	 */
	function getShopperByUser($userid = 0)
	{
		$params = MyMuseHelper::getParams();
		// Lets load the data if it doesn't already exist
        if ( $userid  )
        {
			$user = JFactory::getUser($userid);
			$this->_shopper = $user;
			$this->_shopper->user_id = $userid;
			$this->_shopper->perms = 1;
			
			// Load the profile data from the database.
			$myparams = MyMuseHelper::getParams();
			$profile_key = $myparams->get('my_profile_key', 'mymuse');
			$db = JFactory::getDbo();
			if($params->get('my_registration') == "full" && $profile_key != ''){
				$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
						' WHERE user_id = '.(int) $userid." AND profile_key LIKE '$profile_key.%'" .
						' ORDER BY ordering';
				$db->setQuery( $query);
					
				$results = $db->loadRowList();

				// Merge the profile data.
				$this->_shopper->profile = array();
					
				foreach ($results as $v)
				{
					$k = str_replace('mymuse.', '', $v[0]);
					$this->_shopper->profile[$k] = json_decode($v[1], true);
					if ($this->_shopper->profile[$k] === null)
					{
						$this->_shopper->profile[$k] = $v[1];
					}
				}
			}elseif($params->get('my_registration') == "jomsocial" && !isset($this->_shopper->profile)){
				//can we get their address? try the defaults

			}elseif($params->get('my_registration') == "cb" && !isset($this->_shopper->profile)){
				//can we get their address?
				
			}
		}

		return $this->_shopper;

	}
	
	/**
	 * This method should load profile data into the user object
	 *
	 * @param	array	$user		Holds the user data
	 * @param	array	$options	Array holding options (remember, autoregister, group)
	 *
	 * @return	boolean	True on success
	 */
	public function loadProfile(&$shopper, $options = array())
	{
	
		$jinput = JFactory::getApplication()->input;
		$task 	= $jinput->get('task');
		$session = JFactory::getSession();
		
		if($shopper->username == 'buyer'){
			$shopper->profile = $session->get('myprofile');
			//->get('com_users')->get('registration')->get('data')->get('profile');
			$shopper->profile['loaded'] = 1;
			return true;
		}
		

		// Load the profile data from the database.
		$app = JFactory::getApplication();
		$myparams = MyMuseHelper::getParams();
		$profile_key = $myparams->get('my_profile_key', 'mymuse');
		$userId = $shopper->get('id');
		$db = JFactory::getDbo();
		$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
				' WHERE user_id = '.(int) $userId." AND profile_key LIKE '$profile_key.%'" .
				' ORDER BY ordering';
		$db->setQuery( $query);

		if(!$results = $db->loadRowList()){

			//return false;
		}
		
		// Check for a database error.
		if ($db->getErrorNum())
		{
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}
	
		// Merge the profile data.
		$shopper->profile = array();
		if($results) {
			foreach ( $results as $v ) {
				$k = str_replace ( $profile_key . '.', '', $v [0] );
				$shopper->profile [$k] = json_decode ( $v [1], true );
				if ($shopper->profile [$k] === null) {
					$shopper->profile [$k] = $v [1];
				}
			}
		}
		if(!isset($shopper->profile['name'])){
			$shopper->profile['name'] = $shopper->name;
		}
		if(!isset($shopper->profile['email'])){
			$shopper->profile['email'] = $shopper->email;
		}
		if(isset($shopper->profile['region']) && !isset($shopper->profile['region_name']) && $profile_key != 'mymuse'){
			$shopper->profile['region_name'] = $shopper->profile['region'];
		}
		if(!isset($shopper->profile['shopper_group'])){
			$shopper->profile['shopper_group'] = 1;
		}

		$session->set('user', $shopper);
		$session->set('myprofile', $shopper->profile);
		
		return true;
	}
	
	
	/*
	 * Validate form
	 * Put post variables from form into session
	 * log them in
	*/
	function savenoreg()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		$params = MyMuseHelper::getParams();
		$jinput = $app->input;
		$user	= JFactory::getUser();
		$fields = MyMuseHelper::getNoRegFields();
		$myparams = MyMuseHelper::getParams();
		$application = JFactory::getApplication();
		// Get the user data.
		$post = $jinput->get('jform', array(), 'ARRAY');
		// Save the data in the session.
		$app->setUserState('com_users.registration.data', $post);

		$MyMuseCart		=& MyMuse::getObject('cart','helpers');
        $cart = $MyMuseCart->cart;
        $shipping_needed = 0;
        for ($i=0;$i<$cart["idx"];$i++) {
        	if(isset($cart[$i]["product_physical"]) && $cart[$i]["product_physical"] && $myparams->get('my_use_shipping')){
        		$shipping_needed = 1;
        	}
        }
		$fields = MyMuseHelper::getNoRegFields();
		//I want to see if any fields that are required have not been filled in
		$plugin = JPluginHelper::getPlugin('user', 'mymusenoreg');
		$profile_params = new JRegistry();
		$needed = 0;


		foreach ($fields as $field) {
			if(isset($post['profile'][$field])){
				$value = $post['profile'][$field];
				//echo "field = $field, value = $value <br>";
				if (
					$profile_params->get('register-require_' . $field, 1) == 2 &&
					(!isset($value) || $value == "")

				) {
					//check the shipping news
					if(strstr($field, 'shipping') && !$shipping_needed){
						continue;
					}

					//this guy needs to update profile
					$application->enqueueMessage('Please enter a value for '.$field, 'warning');
					$needed++;


				}
			}
		}

		if($needed){
			return false;
		}



		if($user->get('id')){
			return true;
		}
		$db	= JFactory::getDBO();
		$query = "SELECT * FROM #__users WHERE username='buyer'";
		$db->setQuery($query);
		$guest = $db->loadObject();

		if(!$guest){
			if(!$this->createGuestUser()){
				return false;
			}
			$db->setQuery($query);
			$guest = $db->loadObject();
		}
		if(!$guest){
			$this->setError(JText::_("MYMUSE_COULD_NOT_FIND_GUEST"));
			return false;
		}
		

		

		// Validate the posted data.
		$form	= $this->getForm();
		if (!$form) {
			$app->enqueueMessage("No form. ".$this->getError(), 'warning');	
			return false;
		}

		$data	= $this->validate($form, $post);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $this->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			if(count($errors)){
				return false;
			}
		}


		//save the currrent cart
		$MyMuseCart = MyMuse::getObject('cart','helpers');
		$currentCart = $MyMuseCart->cart;
		
		//perform the login action
		$credentials = array();
		$credentials['username'] = 'buyer';
		$credentials['password'] = $myparams->get('my_noreg_password');
		$options = array();
		$error = $app->login($credentials, $options);
		//print_pre($credentials); print_pre($myparams); exit;
		$queue = $app->getMessageQueue();
		if(count($queue)){
			//return false;
			print_pre($queue); exit;
		}
		
		//put the cart back in
		$session = JFactory::getSession();
		$session->set("cart",$currentCart);
		$MyMuseCart->cart = $currentCart;
		
		$user	= JFactory::getUser('buyer');
		
		//put values into session
		if(isset($post['profile']['region']) && !isset($post['profile']['region_name']) ){
			$db = JFactory::getDBO();
		
			$query = "SELECT * FROM #__mymuse_state WHERE id='".$post['profile']['region']."'";
			$db->setQuery($query);
			if($row = $db->loadObject()){
				$post['profile']['region_name'] = $row->state_name;
			}
		}
		
		if(isset($post['profile']['shipping_region']) && !isset($post['profile']['shipping_region_name']) ){
			$db = JFactory::getDBO();
		
			$query = "SELECT * FROM #__mymuse_state WHERE id='".$post['profile']['shipping_region']."'";
			$db->setQuery($query);
			if($row = $db->loadObject()){
				$post['profile']['shipping_region_name'] = $row->state_name;
			}
		}
		// set name
		if(isset($post['profile']['first_name'])){
			$post['profile']['name'] = $post['profile']['first_name']." ".@$post['profile']['last_name'];
		}

		if(isset($post)){

			$session->set('myprofile', $post['profile']);

		}
		
		//$session->set('user', $user);
		
		return true;
	}
	
	
	function cancel()
	{
		$this->setRedirect( 'index.php' );
	}



	function getOrders()
	{
		$MyMuseCheckout =& MyMuse::getObject('checkout','helpers');
		$user		= JFactory::getUser();
		$user_id 	= $user->get('id');
		$db			= JFactory::getDBO();
		$query = "SELECT * from #__mymuse_order WHERE user_id=$user_id ORDER BY created DESC";
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		foreach($orders as $key => $order){
			$orders[$key] = $MyMuseCheckout->getOrder($order->id);
			$orders[$key]->url = "index.php?option=com_mymuse&task=vieworder&orderid=".$order->id;
		}

		return $orders;
		
	}
	

		
	function createGuestUser()
	{
		$myparams = MyMuseHelper::getParams();
 		$data = array('name' => 'Guest Buyer',
		'username' => 'buyer',
		'password1' => $myparams->get('my_noreg_password'),
		'password2' => $myparams->get('my_noreg_password'), 
		'email1' => 'guest@joomlamymuse.com',
		'email2' => 'guest@joomlamymuse.com' 
 		);
 		$config = JFactory::getConfig();
 		$db		= $this->getDbo();
 		$params = JComponentHelper::getParams('com_users');
 		
 		// Initialise the table with JUser.
 		$user = new JUser;
 		
 		// Prepare the data for the user object.
 		$data['email']		= $data['email1'];
 		$data['password']	= $data['password1'];
 		$useractivation = $params->get('useractivation');
 		
 		// Get the default new user group, Registered if not specified.
 		$system	= $params->get('new_usertype', 2);
 		$data['groups'][] = $system;

 		//print_pre($data); exit;
 		// Bind the data.
 		if (!$user->bind($data)) {
 			$this->setError(JText::sprintf('MYMUSE_REGISTRATION_BIND_FAILED', $user->getError()));
 			return false;
 		}
 		
 		// Load the users plugin group.
 		JPluginHelper::importPlugin('user');
 		
 		// Store the data.
 		if (!$user->save()) {
 			$this->setError(JText::sprintf('MYMUSE_REGISTRATION_SAVE_FAILED', $user->getError()));
 			return false;
 		}

 		return $user->id;

		
	}
	
	/*
	 * make_no_register
	*
	* Get guest user and log them in, creating user if need be
	*
	* return boolen
	*/
	function make_no_register()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$user = JFactory::getUser();
		$params	= JComponentHelper::getParams('com_users');
		$myparams = MyMuseHelper::getParams();
		
		if($user->get('id')){
			return true;
		}
		$db	= JFactory::getDBO();
		$query = "SELECT * FROM #__users WHERE username='buyer'";
		$db->setQuery($query);
		$guest = $db->loadObject();
		if(!$guest){
			if(!$this->createGuestUser()){
				return false;
			}
			$db->setQuery($query);
			$guest = $db->loadObject();
		}
		if(!$guest){
			$this->setError(JText::_("MYMUSE_COULD_NOT_FIND_GUEST"));
			return false;
		}

		$credentials = array();
		$credentials['username'] = 'buyer';
		$credentials['password'] = $myparams->get('my_noreg_password');
		$options = array();
		;

		//preform the login action
		$error = $app->login($credentials, $options);
		
		if(!JError::isError($error)){
			return true;
		}else{
			$this->setError(JText::_($error->code));
			return false;
		}
		
	}
	
	/**
	 * Method to get the registration form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for users plugins to extend the data.
	 *
	 * @return	mixed		Data object on success, false on failure.
	 * @since	1.6
	 */
	public function getData()
	{
		if ($this->data === null) {
	
			$this->data	= new stdClass();
			$app	= JFactory::getApplication();
			$params	= JComponentHelper::getParams('com_users');
	
			// Override the base user data with any data in the session.
			$temp = (array)$app->getUserState('com_users.registration.data', array());
			foreach ($temp as $k => $v) {
				$this->data->$k = $v;
			}
	
			// Get the groups the user should be added to after registration.
			$this->data->groups = array();
	
			// Get the default new user group, Registered if not specified.
			$system	= $params->get('new_usertype', 2);
	
			$this->data->groups[] = $system;
	
			// Unset the passwords.
			unset($this->data->password1);
			unset($this->data->password2);
	
			// Get the dispatcher and load the users plugins.
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('user');
	
			// Trigger the data preparation event.
			$results = $dispatcher->trigger('onContentPrepareData', array('com_mymuse.noreg', $this->data));
	
			// Check for errors encountered while preparing the data.
			if (count($results) && in_array(false, $results, true)) {
				$this->setError($dispatcher->getError());
				$this->data = false;
			}
		}
	
		return $this->data;
	}
	
	/**
	 * Method to get the registration form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_mymuse.noreg', 'registration', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
	
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		return $this->getData();
	}
	
	/**
	 * Override preprocessForm to load the user plugin group instead of content.
	 *
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		$userParams	= JComponentHelper::getParams('com_users');
	
		//Add the choice for site language at registration time
		if ($userParams->get('site_language') == 1 && $userParams->get('frontend_userparams') == 1)
		{
			$form->loadFile('sitelang', false);
		}
	
		parent::preprocessForm($form, $data, $group);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$app	= JFactory::getApplication();
		$params	= $app->getParams('com_users');
	
		// Load the parameters.
		$this->setState('params', $params);
	}
	
	

}
