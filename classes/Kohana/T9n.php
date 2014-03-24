<?php

defined('SYSPATH') or die('No direct script access.');

class Kohana_T9n implements \Countable,
  \ArrayAccess,
  \IteratorAggregate,
  \JsonSerializable
{

	/**
	 * @var array
	 */
	protected $translations;

	public function get_translations()
	{
		return $this->translations;
	}

	public function set_translations(array $translations)
	{
		$translations = array_intersect_key($translations, $this->translations);
		$this->translations = $translations + $this->translations;
	}

	public function __construct(array $translations = NULL)
	{
		// fill translation with keys of available languages
		$this->translations = array_fill_keys(
		  array_keys(Lang::instance()->list_languages())
		  , NULL
		);
		if (!empty($translations))
		{
			$this->set_translations($translations);
		}
	}

	public function &__get($lang)
	{
		$this->offsetGet($lang);
	}

	public function __set($lang, $translation)
	{
		$this->offsetSet($lang, $translation);
	}

	public function __isset($lang)
	{
		return isset($this->translations[$lang]);
	}

	public function offsetExists($lang)
	{
		return array_key_exists($lang, $this->translations);
	}

	public function offsetGet($offset)
	{
		// test if language exists
		if (!isset($this->$lang))
			throw new Kohana_Exception('Language not available');
		// return
		return $this->translations[$lang];
	}

	public function offsetSet($lang, $translation)
	{
		// test if language exists
		if (!$this->offsetExists($lang))
			throw new Kohana_Exception('Language not available');
		if (!is_string($translation))
			throw new Kohana_Exception('A translation should be a string');
		$this->translations[$lang] = $translation;
	}

	public function offsetUnset($offset)
	{
		throw new Kohana_Exception("You can not unset a language translation");
	}

	/**
	 * Returns the number of translations available
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->translations);
	}

	/**
	 * Returns the iterator
	 *
	 *
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->translations);
	}

	/**
	 * String representation of T9n useful for serialization
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this);
	}

	/**
	 *
	 * @return
	 */
	public function jsonSerialize()
	{
		return $this->translations;
	}

}
