<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/IVTGroup/class.xoctUser.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/Event/class.xoctEvent.php');

/**
 * Class xoctConf
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xoctConf extends ActiveRecord {

	const CONFIG_VERSION = 1;
	const F_CONFIG_VERSION = 'config_version';
	const F_USE_MODALS = 'use_modals';
	const F_CURL_USERNAME = 'curl_username';
	const F_CURL_PASSWORD = 'curl_password';
	const F_WORKFLOW = 'workflow';
	const F_EULA = 'eula';
	const F_CURL_DEBUG_LEVEL = 'curl_debug_level';
	const F_API_BASE = 'api_base';
	const F_ACTIVATE_CACHE = 'activate_cache';
	const F_USER_MAPPING = 'user_mapping';
	const F_GROUP_PRODUCERS = 'group_producers';
	const F_STD_ROLES = 'std_roles';
	const F_ROLE_USER_PREFIX = 'role_user_prefix';
	const F_ROLE_OWNER_EXTERNAL_PREFIX = 'role_ivt_external_prefix';
	const F_ROLE_OWNER_EMAIL_PREFIX = 'role_ivt_email_prefix';
	const F_IDENTIFIER_TO_UPPERCASE = 'identifier_to_uppercase';
	const F_LICENSE_INFO = 'license_info';
	const F_LICENSES = 'licenses';
	const F_UPLOAD_TOKEN = 'upload_token';
	const F_SIGN_ANNOTATION_LINKS = 'sign_annotation_links';
	const F_REQUEST_COMBINATION_LEVEL = 'request_comb_lv';
	const F_EDITOR_LINK = 'editor_link';
	const SEP_EVERYTHING = 1;
	const SEP_EV_ACL_MD = 2;
	const SEP_EV_ACL_MD_PUB = 3;
	const F_NO_METADATA = 'no_metadata';

	/**
	 * @var array
	 */
	public static $roles = array(
		self::F_ROLE_USER_PREFIX,
		self::F_ROLE_OWNER_EXTERNAL_PREFIX,
		self::F_ROLE_OWNER_EMAIL_PREFIX,
	);
	/**
	 * @var array
	 */
	public static $groups = array(
		self::F_GROUP_PRODUCERS,
	);


	public static function setApiSettings() {
		// CURL
		$xoctCurlSettings = new xoctCurlSettings();
		$xoctCurlSettings->setUsername(self::getConfig(self::F_CURL_USERNAME));
		$xoctCurlSettings->setPassword(self::getConfig(self::F_CURL_PASSWORD));
		$xoctCurlSettings->setVerifyPeer(true);
		$xoctCurlSettings->setVerifyHost(true);
		xoctCurl::init($xoctCurlSettings);

		//CACHE
//		xoctCache::setOverrideActive(self::getConfig(self::F_ACTIVATE_CACHE));
		//		xoctCache::setOverrideActive(true);

		// API
		$xoctRequestSettings = new xoctRequestSettings();
		$xoctRequestSettings->setApiBase(self::getConfig(self::F_API_BASE));
		xoctRequest::init($xoctRequestSettings);

		// LOG
		xoctLog::init(self::getConfig(self::F_CURL_DEBUG_LEVEL));

		// USER
		xoctUser::setUserMapping(self::getConfig(self::F_USER_MAPPING) ? self::getConfig(self::F_USER_MAPPING) : xoctUser::MAP_EMAIL);

		// EVENT REQUEST LEVEL
		switch (self::getConfig(self::F_REQUEST_COMBINATION_LEVEL)) {
			default:
			case xoctConf::SEP_EVERYTHING:
				xoctEvent::$LOAD_ACL_SEPARATE = true;
				xoctEvent::$LOAD_PUB_SEPARATE = true;
				xoctEvent::$LOAD_MD_SEPARATE = true;
				break;
			case xoctConf::SEP_EV_ACL_MD:
				xoctEvent::$LOAD_ACL_SEPARATE = false;
				xoctEvent::$LOAD_PUB_SEPARATE = true;
				xoctEvent::$LOAD_MD_SEPARATE = false;
				break;
			case xoctConf::SEP_EV_ACL_MD_PUB:
				xoctEvent::$LOAD_ACL_SEPARATE = false;
				xoctEvent::$LOAD_PUB_SEPARATE = false;
				xoctEvent::$LOAD_MD_SEPARATE = false;
				break;
		}

		// META DATA
		xoctEvent::$NO_METADATA = self::getConfig(self::F_NO_METADATA);
	}


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return 'xoct_config';
	}


	/**
	 * @var array
	 */
	protected static $cache = array();
	/**
	 * @var array
	 */
	protected static $cache_loaded = array();
	/**
	 * @var bool
	 */
	protected $ar_safe_read = false;


	/**
	 * @return bool
	 */
	public static function isConfigUpToDate() {
		return self::getConfig(self::F_CONFIG_VERSION) == self::CONFIG_VERSION;
	}


	public static function load() {
		$null = parent::get();
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function getConfig($name) {
		if (!self::$cache_loaded[$name]) {
			$obj = new self($name);
			self::$cache[$name] = json_decode($obj->getValue());
			self::$cache_loaded[$name] = true;
		}

		return self::$cache[$name];
	}


	/**
	 * @param $name
	 * @param $value
	 */
	public static function set($name, $value) {
		$obj = new self($name);
		$obj->setValue(json_encode($value));

		if (self::where(array( 'name' => $name ))->hasSets()) {
			$obj->update();
		} else {
			$obj->create();
		}
	}


	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        text
	 * @db_length           250
	 */
	protected $name;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $value;


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
}