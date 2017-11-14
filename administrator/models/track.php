<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Mymuse model.
 */
class MymuseModelTrack extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_MYMUSE';
	
	
	/**
	 * @var		product(item) object
	 * @since	1.6
	 */
	protected $_item = null;
	

	/**
	 * @var		array
	 */
	protected $filter_fields = null;
	
	/**
	 * @var		object
	 */
	protected $_params = null;
	

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		$config = array();
		$config['event_after_delete'] ='onMymuseAfterDelete';
		$config['event_after_save'] = 'onMymuseAfterSave';
		$config['event_before_delete'] = 'onMymuseBeforeDelete';
		$config['event_before_save'] = 'onMymuseBeforeSave';
		$config['event_change_state'] = 'onMymuseChangeState';
		
		$this->filter_fields = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'a.product_id', 'product_id',
				'a.ordering','ordering',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'modified', 'a.modified',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'published', 'a.published',
				'author', 'a.author'
		);
		
		$this->_params 	= MyMuseHelper::getParams();
		
		parent::__construct($config);
	}
	

        
	/**
	 * Returns a reference to a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Track', $prefix = 'MymuseTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{


		// Get the form.
		$form = $this->loadForm('com_mymuse.track', 'track', array('control' => 'jform', 'load_data' => $loadData));
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
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mymuse.edit.track.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if(!$this->_item){
			$input = JFactory::getApplication()->input;
			$task = $input->get('task','');
			$id = $input->get('id','');
			
			
			if ($item = parent::getItem($pk)) {
				if(!$item->product_id){
					$mainframe = JFactory::getApplication();
					$item->product_id= $mainframe->getUserStateFromRequest( "com_mymuse.product_id", 'product_id', 0 );
				}

				if($task == "new_allfiles"){
					$item->allfiles = 1;
				}
				if($item->product_id){
					$q = "SELECT * FROM #__mymuse_product WHERE id='".$item->product_id."'";
					$this->_db->setQuery($q);
					$this->_parent = $this->_db->loadObject();
					$item->parent = $this->_parent;
				}
				$item->flash_type = '';
				
				$jason = json_decode($item->track);
				if(is_array($jason)){
					$item->track = $jason;
				}
				
			}
			
			$this->_item = $item;
	

		}
		return $this->_item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mymuse_track');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
	
	
    
  
    function getTrackPagination()
    {
    	if (empty($this->_trackPagination)) {
    		return null;
    	}
    	return $this->_trackPagination;
    }
    

    /**
     * Method to get the file lists.
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {
    	$input = JFactory::getApplication()->input;
    	$product_id = $this->_item->product_id;
    	jimport('joomla.filesystem.file');

 		// file lists for albums
 		$artist_alias = MyMuseHelper::getArtistAlias($product_id,1);
		$album_alias = MyMuseHelper::getAlbumAlias($product_id,1);
	
		$site_url = MyMuseHelper::getSiteUrl($product_id,1);
		$site_path = MyMuseHelper::getSitePath($product_id,1);
		$download_path = MyMuseHelper::getdownloadPath($product_id,1);

		$files = array();
		
		
		// get the preview lists

		if(!JFolder::exists($site_path)){
			JFolder::create($site_path);
		}
		$files = JFolder::files( $site_path );


		$previews 	= array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach ( $files as $file ) {
				$previews[] = JHTML::_('select.option',  $file );
		}
		$lists['previews'] = JHTML::_('select.genericlist',  $previews, 'preview', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->preview );
		$lists['previews_2'] = JHTML::_('select.genericlist',  $previews, 'preview_2', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->preview_2 );
		$lists['previews_3'] = JHTML::_('select.genericlist',  $previews, 'preview_3', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->preview_3 );
		
		
		// get the download tracks lists
		$files = array();

		$directory = MyMuseHelper::getDownloadPath($product_id,'1');

		if($this->_params->get('my_download_dir_format')){
			//by format
			$files = array();
			foreach($this->_params->get('my_formats') as $format){
				if(!JFolder::exists( $directory.DS.$format )){
					JFolder::create( $directory.DS.$format );
				}
				$arr = JFolder::files( $directory.DS.$format );
				if(is_array($arr)){
					$files = array_merge($files,$arr);
				}
			}
		}else{
			if(!JFolder::exists( $directory )){
				JFolder::create( $directory );
			}
			$files = JFolder::files( $directory );
		}

		
		$myfiles = array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach($files as $file){
				$myfiles[] = JHTML::_('select.option',  $file, stripslashes($file) );
		}
		
		$current = $this->_item->track;

		$i = 0;
		if($current){
			for($i = 0; $i < count($current); $i++){
				$lists['select_file'][$i] = JHTML::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text', $current[$i]->file_name);
			}
		}else{
			$lists['select_file'][0] = JHTML::_('select.genericlist',  $myfiles, "select_file[0]", 'class="inputbox" size="1" ', 'value', 'text','');
		}
		for($i = $i++; $i < 9; $i++){
			$lists['select_file'][$i] = JHTML::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text','');
		}




		
		// for display purposes
		$lists['preview_dir'] = $site_path;
		$lists['download_dir'] = $download_path;

	
		return $lists;
    	
    }
    
	/**
	 * Method to toggle the featured setting.
	 *
	 * @param	array	The ids of the items to toggle.
	 * @param	int		The value to toggle to.
	 *
	 * @return	boolean	True on success.
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks)) {
			$this->setError(JText::_('MYMUSE_NO_ITEM_SELECTED'));
			return false;
		}

		try {
			$db = $this->getDbo();

			$db->setQuery(
				'UPDATE #__mymuse_track AS a' .
				' SET featured = '.(int) $value.
				' WHERE id IN ('.implode(',', $pks).')'
			);
			if (!$db->execute()) {
				throw new Exception($db->getErrorMsg());
			} 

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}


		$this->cleanCache();

		return true;
	}
	

	

	function getReorderConditions($table){
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$subtype = $input->get('subtype','');
		$product_id = $input->get('product_id','');
		$db = JFactory::getDBO();
	
		$where = '';
	
		if($subtype && $product_id){
			$w = ($subtype == "item") ? "product_physical=1" : "product_downloadable=1";

			$where = " product_id=$product_id AND $w ";
		}
	
		return $where;
	
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function saveorder($pks = null, $order = null)
	{
	
		$table = $this->getTable('track','MymuseTable');
		$conditions = array();
	
		if (empty($pks))
		{
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}
	
		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);
	
			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
	
	
				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
	
				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;
				MyMuseHelper::logMessage("$condition\n");
	
				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}
	
				if (!$found)
				{
					$key = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}
	
	
		// Clear the component's cache
		$this->cleanCache();
	
		return true;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();
	
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
	
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
	
			if ($table->load($pk))
			{
	
				if ($this->canDelete($table))
				{
	
					$context = $this->option . '.' . $this->name;
	
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
	
					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());
						return false;
					}
	
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
	
					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
	
				}
				else
				{
	
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error)
					{
						JLog::add($error, JLog::WARNING, 'jerror');
						return false;
					}
					else
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
						return false;
					}
				}
	
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
	
		// Clear the component's cache
		$this->cleanCache();
		return true;
	}
	

					 
	function logMessage($message){
		$path = JPATH_ADMINISTRATOR .DS.'components'.DS.'com_mymuse'.DS.'log.html';
	
		$fh = fopen($path, "a");
		fwrite($fh,$message."\n");
		fclose($fh);
		return true;
	}
	

}
