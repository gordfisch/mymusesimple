<?php
/**
 * @version     $Id$
 * @package     com_mymuse3.5
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Gord Fisch info@mymuse.ca
 *
 * Amazon S3 is a trademark of Amazon.com, Inc. or its affiliates.
 */

// Protection against direct access
defined('_JEXEC') or die();

if(!defined('MYMUSE_CACERT_PEM')) {
	define('MYMUSE_CACERT_PEM', JPATH_ADMINISTRATOR.'/components/com_mymuse/assets/cacert.pem');
}

require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_mymuse'.DS.'helpers'.DS.'awsapi'.DS.'aws-autoloader.php');
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


class MyMuseHelperAmazons3 extends JObject
{
	// ACL flags
	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
	const ACL_PUBLIC_READ_WRITE = 'public-read-write';
	const ACL_AUTHENTICATED_READ = 'authenticated-read';
	const ACL_BUCKET_OWNER_READ = 'bucket-owner-read';
	const ACL_BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control';

	public static $useSSL = true;

	private static $__accessKey; // AWS Access key
	private static $__secretKey; // AWS Secret key
	private static $__default_bucket = null;
	private static $__default_acl = 'private'; // Default ACLs to use: private
	private static $__default_time = 900; // Default timeout for signed URLs: 15 minutes
	private static $__default_region = 'us-west';
	

	/**
	 * Singleton implemetation
	 */
	public static function &getInstance($accessKey = null, $secretKey = null, $useSSL = true)
	{
		
		static $instance = null;
		
		
		$params = MyMuseHelper::getParams();

		if(!$params->get('my_use_s3',0)){
			//JFactory::getApplication()->enqueueMessage(JText::_('MYMUSE_NO_S3_WEBSITE'), 'error');
			return false;
		}
		if(!$params->get('my_s3region',0)){
			JFactory::getApplication()->enqueueMessage(JText::_('MYMUSE_NO_S3_REGION'), 'error');
			return false;
		}
		$region = $params->get('my_s3region',0);
		
		
		if(!is_object($instance)) {
			
			if(empty($accessKey) && empty($secretKey)) {

					$accessKey	= $params->get('my_s3access','');
					$secretKey	= $params->get('my_s3secret','');
					$useSSL		= $params->get('my_s3ssl',true);
				
			}
			
			$instance = new Aws\S3\S3Client([
					'version' => '2006-03-01',
					'region'  => $region,
					'credentials' => [
        				'key'    => $accessKey,
        				'secret' => $secretKey
    				]
			]);

			self::$__default_bucket = $params->get('my_download_dir', '');
			self::$__default_acl = $params->get('my_s3perms','private');
			self::$__default_time = $params->get('my_s3time', 900);
			self::$__default_region = $region;
			self::$__accessKey = $accessKey; 
			self::$__secretKey = $secretKey; 
			
		}

		return $instance;
	}

}