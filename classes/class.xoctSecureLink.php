<?php

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/Request/class.xoctRequest.php');

/**
 * Class xoctSecureLink
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xoctSecureLink {

	/**
	 * @var array
	 */
	protected static $cache = array();


	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	public static function sign($url) {
		// this should not be necessary anymore, since you can activate/deactivate the url signing in the config
//		if (!xoctEvent::$LOAD_PUB_SEPARATE) {
//			return $url;
//		}
		if (!$url) {
			return '';
		}
		if (isset($cache[$url])) {
			return $cache[$url];
		}

		$data = json_decode(xoctRequest::root()->security()->sign($url));

		if ($data->error) {
			return '';
		}
		$cache[$url] = $data->url;

		return $data->url;
	}
}

?>
