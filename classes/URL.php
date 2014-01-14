<?php defined('SYSPATH') or die('No direct script access.');

class URL extends Kohana_URL {

	/**
	 *
	 * Warning!!! I am still unsure that this function is a good idea
	 * Warning!!! It might be removed
	 *
	 * Extension of URL::site that adds a parameter for setting a language key.
	 * The current language (Lang::instance()->lang()) is used by default.
	 *
	 *     echo URL::site_lang('foo/bar'); // default language
	 *     echo URL::site_lang('foo/bar', 'fr');  // custom language
	 *     echo URL::site_lang('foo/bar', FALSE);  // NO language
	 *
	 * @param   string  site URI to convert
	 * @param   mixed   language key to prepend to the URI, or FALSE to not prepend a language
	 * @param   mixed   protocol string or boolean, add protocol and domain?
	 * @param   boolean Include the index_page in the URL
	 * @return  string
	 */
	public static function site_lang($uri = '', $lang = TRUE, $protocol = NULL, $index = TRUE)
	{
		if (empty($uri)) {
			$uri = Request::initial()->uri();
		}
		if ($lang === TRUE)
		{
			// Prepend the current language to the URI
			$uri = Lang::instance()->lang().'/'.ltrim($uri, '/');
		}
		elseif (is_string($lang))
		{
			// Prepend a custom language to the URI
			$uri = $lang.'/'.ltrim($uri, '/');
		}

		return parent::site($uri, $protocol, $index);
	}

}
