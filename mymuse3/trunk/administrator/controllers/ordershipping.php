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

jimport('joomla.application.component.controllerform');

/**
 * Ordershipping controller class.
 */
class MymuseControllerOrdershipping extends JControllerForm
{

    function __construct() {
        $this->view_list = 'ordershippings';
        parent::__construct();
    }

}