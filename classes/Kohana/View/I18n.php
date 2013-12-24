<?php

/**
 * I18n Kohana View
 */
class Kohana_View_I18n extends View
{

	/**
	 * Sets the view filename. Add $lang parameter
	 *
	 *     $view->set_filename($file);
	 *
	 * @param   string  $file   view filename
	 * @return  View
	 * @throws  View_Exception
	 */
	public function set_filename($file, $lang = NULL)
	{
		if ($lang === NULL)
		{
			// Use the global target language
			$lang = I18n::$lang;
		}

		// compute the I18n view file
		$file_i18n = 'i18n' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $file;

		// Load the translation view for this language
		if (Kohana::find_file('views', $file_i18n)) {
			$file = $file_i18n;
		}

		// call parent method
		return parent::set_filename($file);
	}

	/**
	 * Override the View constructor to add $lang parameter
	 *
	 * @param string $file
	 * @param array $data
	 * @param string $lang
	 * @return type
	 */
	public function __construct($file = NULL, array $data = NULL, $lang = NULL) {
		if ($file !== NULL)
		{
			$this->set_filename($file, $lang);
		}
		// call parent method
		// use NULL for the file parameter in order not to set the value again
		return parent::__construct(NULL, $data);
	}

	/**
	 * Override the View factory to add $lang parameter
	 *
	 * @param string $file
	 * @param array $data
	 * @param string $lang
	 * @return View_I18n
	 */
	public static function factory($file = NULL, array $data = NULL, $lang = NULL) {
		return new View_I18n($file, $data, $lang);
	}

	/**
	 *
	 * @param string $file
	 * @param string $lang
	 * @return string
	 */
	public function render($file = NULL, $lang = NULL) {
		if ($file !== NULL)
		{
			$this->set_filename($file, $lang);
		}
		// call parent method
		// use NULL for the file parameter in order not to set the value again
		return parent::render();
	}


}
