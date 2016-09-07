<?php
defined('_JEXEC') or die('Restricted access');

/**
 * Renders a multiple item select element
 *
 */
JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldAuthorizenetCreditCards extends JFormFieldList {

	var $type = 'authorizenetcreditcards';

	protected function getOptions() {
		return parent::getOptions();
	}

}
