<?php
/**
 * @package		akeebasubs
 * @copyright	Copyright (c)2010-2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die();

$Mymuseinclude = include_once JPATH_ADMINISTRATOR.'/components/com_mymuse/helpers/mymuse.php';
if(!$Mymuseinclude) { unset($Mymuseinclude); return; } else { unset($Mymuseinclude); }

class plgMyMusePayment_Paypalproexpress extends JPlugin
{
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	public function __construct(&$subject, $config = array())
	{
		$config = array_merge($config, array(
			'pp'		=> 'paypalproexpress'
		));

		parent::__construct($subject, $config);
	}

	/**
	 * PayPalProExpress Payment form
	 * 
	 * @param object $shopper
	 * @param object $store
	 * @param object $order
	 * @param object $params
	 * @param int $Itemid
	 * 
	 * return string
	 */
	public function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{

		$rootURL = rtrim(JURI::base(),'/');
		$subpathURL = JURI::base(true);
		if(!empty($subpathURL) && ($subpathURL != '/')) {
			$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
		}

		$callbackUrl = JURI::base().'index.php?option=com_mymuse&task=notify&mode=init';
		$cancelUrl = JURI::base().'index.php?option=com_mymuse&task=paycancel';
		$requestData = (object)array(
			'METHOD'							=> 'SetExpressCheckout',
			'USER'								=> $this->getMerchantUsername(),
			'PWD'								=> $this->getMerchantPassword(),
			'SIGNATURE'							=> $this->getMerchantSignature(),
			'VERSION'							=> '93',
			'RETURNURL'							=> $callbackUrl,
			'CANCELURL'							=> $cancelUrl,
			'PAYMENTREQUEST_0_AMT'				=> $order->order_total,
			'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'SALE',
			'PAYMENTREQUEST_0_CURRENCYCODE'		=> strtoupper($store->currency),
			'PAYMENTREQUEST_0_TAXAMT'			=> $order->tax_total,
			'PAYMENTREQUEST_0_ITEMAMT'			=> $order->order_subtotal,	
		);
		/*
		 * do we need this?
		 * 'PAYMENTREQUEST_0_TAXAMT'			=> sprintf('%.2f',$subscription->tax_amount),
			'PAYMENTREQUEST_0_ITEMAMT'			=> sprintf('%.2f',$subscription->net_amount),
			'L_PAYMENTREQUEST_0_NAME0'			=> $level->title,
			'L_PAYMENTREQUEST_0_QTY0'			=> 1,
			'L_PAYMENTREQUEST_0_AMT0'			=> sprintf('%.2f',$subscription->net_amount)
			
		 */


		$requestQuery = http_build_query($requestData);
		$requestContext = stream_context_create(array(
			'http' => array (
				'method' => 'POST',
				'header' => "Connection: close\r\n".
							"Content-Length: " . strlen($requestQuery) . "\r\n",
				'content'=> $requestQuery)
			));
		$responseQuery = file_get_contents(
				$this->getPaymentURL(),
				false,
				$requestContext);

		// Payment Response
		$responseData = array();
		$data = array();
		parse_str($responseQuery, $responseData);
		if(preg_match('/^SUCCESS/', strtoupper($responseData['ACK']))) {
			$data['URL'] = $this->getPaypalURL($responseData['TOKEN']);
		} else {
			$jinput = JFactory::getApplication()->input;
			
			$error_url = 'index.php?option=com_mymuse&view=cart&layout=cart&Itemid='.$Itemid;
			$error_url = JRoute::_($error_url,false);
			JFactory::getApplication()->redirect($error_url,$responseData['L_LONGMESSAGE0'],'error');
		}

		$path = JPluginHelper::getLayoutPath('mymuse', 'payment_paypalproexpress');
		@ob_start();
		include $path;
		$html = @ob_get_clean();

		return $html;
	}


	public function onMyMuseNotify($params, $Itemid = 1)
	{

		$jinput = JFactory::getApplication()->input;
		$data = $jinput->post->getArray();
		
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nPayPalProExpress notify PLUGIN\n";
		
		
		if($data['mode'] == 'init') {
			return $this->formCallback($data);
		} else {
			return $this->IPNCallback($data);
		}
	}

	private function formCallback($data)
	{
		JLoader::import('joomla.utilities.date');
		$isValid = true;

		// Load the relevant subscription row
		if($isValid) {
			$id = $data['sid'];
			$subscription = null;
			if($id > 0) {
				$subscription = F0FModel::getTmpInstance('Subscriptions','AkeebasubsModel')
					->setId($id)
					->getItem();
				if( ($subscription->akeebasubs_subscription_id <= 0) || ($subscription->akeebasubs_subscription_id != $id) ) {
					$subscription = null;
					$isValid = false;
				}
			} else {
				$isValid = false;
			}
			if(!$isValid) $responseData['akeebasubs_failure_reason'] = 'The subscription ID is invalid';
		}

		if($isValid && isset($data['token']) && isset($data['PayerID'])) {
			$level = F0FModel::getTmpInstance('Levels','AkeebasubsModel')
				->setId($subscription->akeebasubs_level_id)
				->getItem();

			$requestData = (object)array(
				'METHOD'							=> 'DoExpressCheckoutPayment',
				'USER'								=> $this->getMerchantUsername(),
				'PWD'								=> $this->getMerchantPassword(),
				'SIGNATURE'							=> $this->getMerchantSignature(),
				'VERSION'							=> '85.0',
				'TOKEN'								=> $data['token'],
				'PAYERID'							=> $data['PayerID'],
				'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'Sale',
				'PAYMENTREQUEST_0_AMT'				=> sprintf('%.2f',$subscription->gross_amount),
				'PAYMENTREQUEST_0_CURRENCYCODE'		=> strtoupper(AkeebasubsHelperCparams::getParam('currency','EUR')),
				'PAYMENTREQUEST_0_INVNUM'			=> $subscription->akeebasubs_subscription_id,
				'PAYMENTREQUEST_0_DESC'				=> '[' . $subscription->akeebasubs_subscription_id . '] ' . $level->title,
				'IPADDRESS'							=> $_SERVER['REMOTE_ADDR']
			);

			$requestQuery = http_build_query($requestData);
			$requestContext = stream_context_create(array(
				'http' => array (
					'method' => 'POST',
					'header' => "Connection: close\r\n".
								"Content-Length: " . strlen($requestQuery) . "\r\n",
					'content'=> $requestQuery)
				));
			$responseQuery = file_get_contents(
					$this->getPaymentURL(),
					false,
					$requestContext);

			// Payment Response
			$responseData = array();
			parse_str($responseQuery, $responseData);
			if(! preg_match('/^SUCCESS/', strtoupper($responseData['ACK']))) {
				$isValid = false;
                $level = F0FModel::getTmpInstance('Levels','AkeebasubsModel')
                    ->setId($subscription->akeebasubs_level_id)
                    ->getItem();
				$error_url = 'index.php?option='.JRequest::getCmd('option').
					'&view=level&slug='.$level->slug.
					'&layout='.JRequest::getCmd('layout','default');
				$error_url = JRoute::_($error_url,false);
				JFactory::getApplication()->redirect($error_url,$responseData['L_LONGMESSAGE0'],'error');
			} else if(! preg_match('/^SUCCESS/', strtoupper($responseData['PAYMENTINFO_0_ACK']))) {
				$isValid = false;
				$responseData['akeebasubs_failure_reason'] = "PayPal error code: " . $responseData['PAYMENTINFO_0_ERRORCODE'];
			}

			if($level->recurring) {
				// Create recurring payment profile
				$nextPayment = new JDate("+$level->duration day");
				$callbackUrl = JURI::base().'index.php?option=com_akeebasubs&view=callback&paymentmethod=paypalproexpress&sid='.$subscription->akeebasubs_subscription_id;
				$recurringRequestData = (object)array(
					'METHOD'			=> 'CreateRecurringPaymentsProfile',
					'NOTIFYURL'			=> $callbackUrl,
					'USER'				=> $this->getMerchantUsername(),
					'PWD'				=> $this->getMerchantPassword(),
					'SIGNATURE'			=> $this->getMerchantSignature(),
					'VERSION'			=> '85.0',
					'PAYMENTACTION'		=> 'Sale',
					'TOKEN'				=> $data['token'],
					'PAYERID'			=> $data['PayerID'],
					'IPADDRESS'			=> $_SERVER['REMOTE_ADDR'],
					'AMT'				=> sprintf('%.2f',$subscription->gross_amount),
					'TAXAMT'			=> sprintf('%.2f',$subscription->tax_amount),
					'CURRENCYCODE'		=> strtoupper(AkeebasubsHelperCparams::getParam('currency','EUR')),
					'DESC'				=> $level->title,
					'PROFILEREFERENCE'	=> $subscription->akeebasubs_subscription_id,
					'PROFILESTARTDATE'	=> $nextPayment->toISO8601(),
					'BILLINGPERIOD'		=> 'Day',
					'BILLINGFREQUENCY'	=> $level->duration
				);

				$recurringRequestQuery = http_build_query($recurringRequestData);
				$recurringRequestContext = stream_context_create(array(
					'http' => array (
						'method' => 'POST',
						'header' => "Connection: close\r\n".
									"Content-Length: " . strlen($recurringRequestQuery) . "\r\n",
						'content'=> $recurringRequestQuery)
					));
				$recurringResponseQuery = file_get_contents(
						$this->getPaymentURL(),
						false,
						$recurringRequestContext);

				// Response of payment profile
				$recurringResponseData = array();
				parse_str($recurringResponseQuery, $recurringResponseData);
				if(! preg_match('/^SUCCESS/', strtoupper($recurringResponseData['ACK']))) {
					$isValid = false;
					$error_url = 'index.php?option='.JRequest::getCmd('option').
						'&view=level&slug='.$level->slug.
						'&layout='.JRequest::getCmd('layout','default');
					$error_url = JRoute::_($error_url,false);
					JFactory::getApplication()->redirect($error_url,$recurringResponseData['L_LONGMESSAGE0'],'error');
				} else {
					$recurringCheckData = (object)array(
						'METHOD'	=> 'GetRecurringPaymentsProfileDetails',
						'USER'		=> $this->getMerchantUsername(),
						'PWD'		=> $this->getMerchantPassword(),
						'SIGNATURE'	=> $this->getMerchantSignature(),
						'VERSION'	=> '85.0',
						'PROFILEID'	=> $recurringResponseData['PROFILEID'],
					);

					$recurringCheckQuery = http_build_query($recurringCheckData);
					$recurringCheckContext = stream_context_create(array(
						'http' => array (
							'method' => 'POST',
							'header' => "Connection: close\r\n".
										"Content-Length: " . strlen($recurringCheckQuery) . "\r\n",
							'content'=> $recurringCheckQuery)
						));
					$recurringCheckQuery = file_get_contents(
							$this->getPaymentURL(),
							false,
							$recurringCheckContext);

					// Response of payment profile
					$recurringCheckData = array();
					parse_str($recurringCheckQuery, $recurringCheckData);
					if(! preg_match('/^SUCCESS/', strtoupper($recurringCheckData['ACK']))) {
						$isValid = false;
						$error_url = 'index.php?option='.JRequest::getCmd('option').
							'&view=level&slug='.$level->slug.
							'&layout='.JRequest::getCmd('layout','default');
						$error_url = JRoute::_($error_url,false);
						JFactory::getApplication()->redirect($error_url,$recurringCheckData['L_LONGMESSAGE0'],'error');
					}
					if(strtoupper($responseData['PAYMENTINFO_0_CURRENCYCODE']) !== strtoupper($recurringCheckData['CURRENCYCODE'])) {
						$isValid = false;
						$responseData['akeebasubs_failure_reason'] = "Currency code doesn't match.";
					}
					if(strtoupper($responseData['PAYMENTINFO_0_AMT']) !== strtoupper($recurringCheckData['AMT'])) {
						$isValid = false;
						$responseData['akeebasubs_failure_reason'] = "Amount doesn't match.";
					}
					if(strtoupper($recurringCheckData['BILLINGPERIOD']) !== "DAY") {
						$isValid = false;
						$responseData['akeebasubs_failure_reason'] = "Recurring period doesn't match.";
					}
					if($recurringCheckData['BILLINGFREQUENCY'] != $level->duration) {
						$isValid = false;
						$responseData['akeebasubs_failure_reason'] = "Recurring duration doesn't match";
					}
				}
			}
		}

		if($isValid && !is_null($subscription)) {
			if($subscription->processor_key == $responseData['PAYMENTINFO_0_TRANSACTIONID']) {
				$isValid = false;
				$responseData['akeebasubs_failure_reason'] = "I will not process the same TRANSACTIONID " . $responseData['PAYMENTINFO_0_TRANSACTIONID'] . " twice";
			}
		}

		if($isValid) {
			if(strtoupper(AkeebasubsHelperCparams::getParam('currency','EUR')) != strtoupper($responseData['PAYMENTINFO_0_CURRENCYCODE'])) {
				$isValid = false;
				$responseData['akeebasubs_failure_reason'] = "Currency code doesn't match.";
			}
		}

		// Check that amount is correct
		$isPartialRefund = false;
		if($isValid && !is_null($subscription)) {
			$mc_gross = floatval($responseData['PAYMENTINFO_0_AMT']);
			$gross = $subscription->gross_amount;
			if($mc_gross > 0) {
				// A positive value means "payment". The prices MUST match!
				// Important: NEVER, EVER compare two floating point values for equality.
				$isValid = ($gross - $mc_gross) < 0.01;
			} else {
				$isPartialRefund = false;
				$temp_mc_gross = -1 * $mc_gross;
				$isPartialRefund = ($gross - $temp_mc_gross) > 0.01;
			}
			if(!$isValid) $responseData['akeebasubs_failure_reason'] = 'Paid amount does not match the subscription amount';
		}

		// Log the IPN data
		$this->logIPN($responseData, $isValid);

		// Fraud attempt? Do nothing more!
		if(!$isValid) {
			$error_url = 'index.php?option='.JRequest::getCmd('option').
				'&view=level&slug='.$level->slug.
				'&layout='.JRequest::getCmd('layout','default');
			$error_url = JRoute::_($error_url,false);
			JFactory::getApplication()->redirect($error_url,$responseData['akeebasubs_failure_reason'],'error');
			return false;
		}

		// Check the payment_status
		switch($responseData['PAYMENTINFO_0_PAYMENTSTATUS'])
		{
			case 'Canceled_Reversal':
			case 'Completed':
				$newStatus = 'C';
				break;

			case 'Created':
			case 'Pending':
			case 'Processed':
				$newStatus = 'P';
				break;

			case 'Denied':
			case 'Expired':
			case 'Failed':
			case 'Refunded':
			case 'Reversed':
			case 'Voided':
			default:
				// Partial refunds can only by issued by the merchant. In that case,
				// we don't want the subscription to be cancelled. We have to let the
				// merchant adjust its parameters if needed.
				if($isPartialRefund) {
					$newStatus = 'C';
				} else {
					$newStatus = 'X';
				}
				break;
		}

		// Update subscription status (this also automatically calls the plugins)
		$updates = array(
				'akeebasubs_subscription_id'	=> $id,
				'processor_key'					=> $responseData['PAYMENTINFO_0_TRANSACTIONID'],
				'state'							=> $newStatus,
				'enabled'						=> 0
		);
		JLoader::import('joomla.utilities.date');
		if($newStatus == 'C') {
			$this->fixDates($subscription, $updates);
		}
		$subscription->save($updates);

		// Run the onAKAfterPaymentCallback events
		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin('akeebasubs');
		$app = JFactory::getApplication();
		$jResponse = $app->triggerEvent('onAKAfterPaymentCallback',array(
			$subscription
		));

		// Redirect the user to the "thank you" page
		$thankyouUrl = JRoute::_('index.php?option=com_akeebasubs&view=message&slug='.$level->slug.'&layout=order&subid='.$subscription->akeebasubs_subscription_id, false);
		JFactory::getApplication()->redirect($thankyouUrl);
		return true;
	}

	private function IPNCallback($data)
	{
		JLoader::import('joomla.utilities.date');

		// Check IPN data for validity (i.e. protect against fraud attempt)
		$isValid = $this->isValidIPN($data);
		if(!$isValid) $data['akeebasubs_failure_reason'] = 'PayPal reports transaction as invalid';

		// Check txn_type; we only accept web_accept transactions with this plugin
		if($isValid) {
			// This is required to process some IPNs, such as Reversed and Canceled_Reversal
			if (!array_key_exists('txn_type', $data))
			{
				$data['txn_type'] = 'workaround_to_missing_txn_type';
			}

			$validTypes = array('workaround_to_missing_txn_type', 'web_accept','recurring_payment','subscr_payment','express_checkout');
			$isValid = in_array($data['txn_type'], $validTypes);

			if(!$isValid)
			{
				$data['akeebasubs_failure_reason'] = "Transaction type ".$data['txn_type']." can't be processed by this payment plugin.";
			}
			else
			{
				$recurring = ($data['txn_type'] == 'recurring_payment');
			}
		}

		// Load the relevant subscription row
		if($isValid) {
			$id = $recurring ? $data['rp_invoice_id'] : $data['invoice'];
			$subscription = null;
			if($id > 0) {
				$subscription = F0FModel::getTmpInstance('Subscriptions','AkeebasubsModel')
					->setId($id)
					->getItem();
				if( ($subscription->akeebasubs_subscription_id <= 0) || ($subscription->akeebasubs_subscription_id != $id) ) {
					$subscription = null;
					$isValid = false;
				}
			} else {
				$isValid = false;
			}
			if(!$isValid) $data['akeebasubs_failure_reason'] = 'The referenced subscription ID ("custom" field) is invalid';
		}

		// Check that mc_gross is correct
		$isPartialRefund = false;
		if($isValid && !is_null($subscription)) {
			$mc_gross = floatval($data['mc_gross']);
			$gross = $subscription->gross_amount;
			if($mc_gross > 0) {
				// A positive value means "payment". The prices MUST match!
				// Important: NEVER, EVER compare two floating point values for equality.
				$isValid = ($gross - $mc_gross) < 0.01;
			} else {
				$isPartialRefund = false;
				$temp_mc_gross = -1 * $mc_gross;
				$isPartialRefund = ($gross - $temp_mc_gross) > 0.01;
			}
			if(!$isValid) $data['akeebasubs_failure_reason'] = 'Paid amount does not match the subscription amount';
		}

		// Check that txn_id has not been previously processed
		if($isValid && !is_null($subscription) && !$isPartialRefund) {
			if($subscription->processor_key == $data['txn_id']) {
				if($subscription->state == 'C') {
					$isValid = false;
					$data['akeebasubs_failure_reason'] = "I will not process the same txn_id twice";
				}
			}
		}

		// Check that mc_currency is correct
		if($isValid && !is_null($subscription)) {
			$mc_currency = strtoupper($data['mc_currency']);
			$currency = strtoupper(AkeebasubsHelperCparams::getParam('currency','EUR'));
			if($mc_currency != $currency) {
				$isValid = false;
				$data['akeebasubs_failure_reason'] = "Invalid currency; expected $currency, got $mc_currency";
			}
		}

		// Log the IPN data
		$this->logIPN($data, $isValid);

		// Fraud attempt? Do nothing more!
		if(!$isValid) return false;

		// Check the payment_status
		switch($data['payment_status'])
		{
			case 'Canceled_Reversal':
			case 'Completed':
				$newStatus = 'C';
				break;

			case 'Created':
			case 'Pending':
			case 'Processed':
				$newStatus = 'P';
				break;

			case 'Denied':
			case 'Expired':
			case 'Failed':
			case 'Refunded':
			case 'Reversed':
			case 'Voided':
			default:
				// Partial refunds can only by issued by the merchant. In that case,
				// we don't want the subscription to be cancelled. We have to let the
				// merchant adjust its parameters if needed.
				if($isPartialRefund) {
					$newStatus = 'C';
				} else {
					$newStatus = 'X';
				}
				break;
		}

		// Update subscription status (this also automatically calls the plugins)
		$updates = array(
			'akeebasubs_subscription_id'	=> $id,
			'processor_key'					=> $data['txn_id'],
			'state'							=> $newStatus,
			'enabled'						=> 0
		);
		JLoader::import('joomla.utilities.date');
		if($newStatus == 'C') {
			$this->fixDates($subscription, $updates);
		}
		// In the case of a successful recurring payment, fetch the old subscription's data
		if($recurring && ($newStatus == 'C') && ($subscription->state == 'C')) {
			$jNow = new JDate();
			$jStart = new JDate($subscription->publish_up);
			$jEnd = new JDate($subscription->publish_down);
			$now = $jNow->toUnix();
			$start = $jStart->toUnix();
			$end = $jEnd->toUnix();
			// Create a new record for the old subscription
			$oldData = $subscription->getData();
			$oldData['akeebasubs_subscription_id'] = 0;
			$oldData['publish_down'] = $jNow->toSql();
			$oldData['enabled'] = 0;
			$oldData['contact_flag'] = 3;
			$oldData['notes'] = "Automatically renewed subscription on ".$jNow->toSql();

			// Calculate new start/end time for the subscription
			$allSubs = F0FModel::getTmpInstance('Subscriptions', 'AkeebasubsModel')
				->paystate('C')
				->level($subscription->akeebasubs_level_id)
				->user_id($subscription->user_id);
			$max_expire = 0;
			if(count($allSubs)) foreach($allSubs as $aSub) {
				$jExpire = new JDate($aSub->publish_down);
				$expire = $jExpire->toUnix();
				if($expire > $max_expire) $max_expire = $expire;
			}

			$duration = $end - $start;
			$start = max($now, $max_expire);
			$end = $start + $duration;
			$jStart = new JDate($start);
			$jEnd = new JDate($end);

			$updates['publish_up'] = $jStart->toSql();
			$updates['publish_down'] = $jEnd->toSql();

			// Save the record for the old subscription
			$table = F0FModel::getTmpInstance('Subscriptions', 'AkeebasubsModel')
				->getTable();
			$table->reset();
			$table->bind($oldData);
			$table->store();
		}
		// Save the changes
		$subscription->save($updates);

		// Run the onAKAfterPaymentCallback events
		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin('akeebasubs');
		$app = JFactory::getApplication();
		$jResponse = $app->triggerEvent('onAKAfterPaymentCallback',array(
			$subscription
		));

		return true;
	}

	/**
	 * Validates the incoming data against PayPal's IPN to make sure this is not a
	 * fraudelent request.
	 */
	private function isValidIPN($data)
	{
		$sandbox = $this->params->get('sandbox',0);
		$hostname = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		$url = 'ssl://'.$hostname;
		$port = 443;

		$req = 'cmd=_notify-validate';
		foreach($data as $key => $value) {
			$value = urlencode($value);
			$req .= "&$key=$value";
		}
		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Host: $hostname:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";


		$fp = fsockopen ($url, $port, $errno, $errstr, 30);

		if (!$fp) {
			// HTTP ERROR
			return false;
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (stristr($res, "VERIFIED")) {
					return true;
				} else if (stristr($res, "INVALID")) {
					return false;
				}
			}
			fclose ($fp);
		}
	}

	private function getPaymentURL()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			return 'https://api-3t.paypal.com/nvp';
		}
	}

	private function getPaypalURL($token)
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
		} else {
			return 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
		}
	}

	private function getMerchantUsername()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return trim($this->params->get('sb_apiuser',''));
		} else {
			return trim($this->params->get('apiuser',''));
		}
	}

	private function getMerchantPassword()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return trim($this->params->get('sb_apipw',''));
		} else {
			return trim($this->params->get('apipw',''));
		}
	}

	private function getMerchantSignature()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return trim($this->params->get('sb_apisig',''));
		} else {
			return trim($this->params->get('apisig',''));
		}
	}
}