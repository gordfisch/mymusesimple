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
 * taxrate Table class
 */
class MymuseTabletaxrate extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__mymuse_tax_rate', 'id', $db);
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
     * Overloaded store function
     * Add the tax rate to the order table
     */
    public function store($updateNulls = false) {
    
    	$post = JRequest::get( 'post' );
    	$form = $post['jform'];


        $regex = TAX_REGEX;
        $name = preg_replace("/$regex/","_",$form['tax_name']);
        preg_match_all("/[^a-zA-Z_]/",$name,$m);

        if (@$m[0]){
        	$this->setError(JText::_( 'MYMUSE_ERROR_SAVING_TAX_RATE' ).$m[0][0]);
        	return false;
        }elseif($form["old_tax_name"] == ""){
        	$db = JFactory::getDBO();
        	$query = "ALTER TABLE `#__mymuse_order` ADD `$name` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'";
        	$db->setQuery($query);
        	if(!$db->execute()){
        		$this->setError("Error with saving tax : ".$db->getErrorMsg());
        		return false;
        	}
        }
        if($form["old_tax_name"] != '' && $form["old_tax_name"] != $form["tax_name"]){
        	$db = JFactory::getDBO();
        	$name = preg_replace("/$regex/","_",$form['old_tax_name']);
        	$query = "ALTER TABLE `#__mymuse_order` DROP `".$name."` ";
        	$db->setQuery($query);
        	if(!$db->execute()){
        		$this->setError("Error removing old tax : ".$db->getErrorMsg());
        		return false;
        	}
        	$name = preg_replace("/$regex/","_",$form['tax_name']);
        	$query = "ALTER TABLE `#__mymuse_order` ADD `$name` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'";
        	$db->setQuery($query);
        	if(!$db->execute()){
        		$this->setError("Error with saving tax : ".$db->getErrorMsg());
        		return false;
        	}
        }
    
    	return parent::store();
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

    public function delete($pk = NULL)
    {
    	if(!$pk){
    		return false;
    	}
    	$db = JFactory::getDBO();
    	$query = "SELECT tax_name FROM #__mymuse_tax_rate WHERE id='$pk'";
    	$db->setQuery($query);
    	$name = $db->loadResult();
    	
    	$query = "ALTER TABLE `vl6xc_mymuse_order` DROP `$name`";
    	$db->setQuery($query);
    	$db->execute();
    	
    	$query = "DELETE FROM #__mymuse_tax_rate WHERE id='$pk'";
    	$db->setQuery($query);
    	$db->execute();
    	return true;
    	
    }


}
