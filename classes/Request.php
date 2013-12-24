<?php

defined('SYSPATH') or die('No direct script access.');

class Request extends Kohana_Request
{

	/**
	 * Extension of the main request singleton instance. If none given, the URI will
	 * be automatically detected. If the URI contains no language segment, the user
	 * will be redirected to the same URI with the default language prepended.
	 * If the URI does contain a language segment, I18n and locale will be set.
	 * Also, a cookie with the current language will be set. Finally, the language
	 * segment is chopped off the URI and normal request processing continues.
	 *
	 * @param   string   URI of the request
	 * @return  Request
	 * @uses    Request::detect_uri
	 */
	public static function process(Request $request, $routes = NULL) {
		// process url with Lang
		if ($request === Request::initial())
			Lang::instance()->process($request);
		// Continue normal request processing with the URI without language
		return parent::process($request, $routes);
	}

}
