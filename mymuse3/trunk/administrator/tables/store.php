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

/**
 * store Table class
 */
class MymuseTablestore extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__mymuse_store', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
			
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}
		return parent::bind($array, $ignore);
	}
	
	

    /**
    * Overloaded check function
    */
    public function check() {

        //If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }

        return parent::check();
    }


    
    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param   boolean  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   11.1
     */
    public function store($updateNulls = false)
    {

    	jimport('joomla.filesystem.file');
    	$this->checkin();
    	$myparams = MyMuseHelper::getParams();
    	$jinput = JFactory::getApplication()->input;
    	$form 	= $jinput->get('jform',array(), 'ARRAY');
    	
    	//save the css file
    	$mymuse_css = $jinput->get('mymuse_css','', 'RAW');

    	if($mymuse_css){
    		$myFile = JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'assets'.DS.'css'.DS.'mymuse.css';
    		if(!JFILE::write($myFile, $mymuse_css)){
    			$app = JFactory::getApplication();
    			$app->enqueueMessage(JText::_('MYMUSE_COULD_NOT_OPEN_CSS_FILE').' '.$myFile, 'notice');
    		}
    	}
    	
    	
    	//if encode filenames has changed
    	$my_encode_filenames = $form['params']['my_encode_filenames'];
    	
    	if($my_encode_filenames !== $myparams->get('my_encode_filenames') ){
    		
    		if(!$this->change_encoding($my_encode_filenames)){
    			$this->setError("Encode Filenames has changed and I wasn't able to change the files!");
    			return false;
    		}
    	}


    	//if my_noreg_password has changed
    	$my_noreg_password = $form['params']['my_noreg_password'];

    	if($my_noreg_password !== $myparams->get('my_noreg_password')){
    		
    		$user	= JFactory::getUser('buyer');
    		$user = new JUser($user->id);
    		//print_pre($user); exit;
    		$data = array('name' => 'Guest Buyer',
    				'password'=>  $my_noreg_password,
    				'email' => 'guest@mymuse.ca',
					'username' => 'buyer',
					'password1' => $my_noreg_password,
					'password2' => $my_noreg_password, 
					'email1' => 'guest@mymuse.ca',
					'email2' => 'guest@mymuse.ca' 
 			);
    		// Bind the data.
    		if (!$user->bind($data))
    		{
    			$this->setError(JText::sprintf('COM_USERS_PROFILE_BIND_FAILED', $user->getError()));
    			return false;
    		}
    		$user->groups = null;
    		// Store the data.
    		if (!$user->save())
    		{
    			$this->setError($user->getError());
    			return false;
    		}
    		
    	}

    	return parent::store($updateNulls);
    }
    	
    
    /**
     * Change encoding of files
     * 
     * @param    integer The new state of encode filenames.
     * @return    boolean    True on success.
     */
    function change_encoding($my_encode_filenames)
    {
    	$db = JFactory::getDBO();
    	$params = MyMuseHelper::getParams();
    	JLoader::import('joomla.filesystem.folder');
    	JLoader::import('joomla.filesystem.file');
    	
    	if($my_encode_filenames) // we want to encode them
    	{
    	
    	}else{ // we want to change back to regular names
    		$query = "SELECT p.id, p.title, p.alias, p.title_alias, p.file_name,  c.alias as cat_alias, parent.alias as parent_alias
			FROM `#__mymuse_product` AS p
			LEFT JOIN `#__mymuse_product` AS parent ON parent.id = p.parentid
			LEFT JOIN `#__categories` AS c ON c.id = parent.catid
			WHERE p.product_downloadable =1
			AND p.product_allfiles = 0
			ORDER BY cat_alias, parent_alias, p.title ";
    		
    		$db->setQuery($query);
    		$products = $db->loadObjectList();
    		foreach($products as $product){
    			$path = MyMuseHelper::getDownloadPath($id);
    			$old_file = $path.DS.$product->title_alias;
    			$new_file = $path.DS.$product->file_name;
    			if(!JFile::copy("$old_file", "$new_file")){
    				$this->setError(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file);
    				$application->enqueueMessage(JText::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$old_file." ".$new_file , 'error');
    				return false;
    			}
    		} 
    		
    	}
    	
    	
    }
    
    
    
    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.  The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param    mixed    An optional array of primary key values to update.  If not
     *                    set the instance property value is used.
     * @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
     * @param    integer The user id of the user performing the operation.
     * @return    boolean    True on success.
     * @since    1.0.4
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks))
        {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k.'='.implode(' OR '.$k.'=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
        }

        // Update the publishing state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE `'.$this->_tbl.'`' .
            ' SET `state` = '.(int) $state .
            ' WHERE ('.$where.')' .
            $checkin
        );
        $this->_db->execute();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
        {
            // Checkin the rows.
            foreach($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');
        return true;
    }




}
