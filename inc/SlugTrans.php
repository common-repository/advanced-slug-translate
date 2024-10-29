<?php

class SlugTransAstrans
{
	public static $maps = array (
		// 'de' => array ( /* German */
		// 	'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
		// 	'ẞ' => 'SS'
		// ),
		'latin' => array (
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A','Ă' => 'A', 'Æ' => 'AE', 'Ç' =>
			'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
			'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' =>
			'O', 'Ő' => 'O', 'Ø' => 'O', 'Œ' => 'OE' ,'Ș' => 'S','Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U',
			'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' =>
			'a', 'å' => 'a', 'ă' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' =>
			'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 'ø' => 'o', 'œ' => 'oe', 'ș' => 's', 'ț' => 't', 'ù' => 'u', 'ú' => 'u',
			'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y'
		),
		'latin_symbols' => array (
			'©' => '(c)'
		),
	);

	/**
	 * List of words to remove from URLs.
	 */
	public static $remove_list = array (
		'a', 'an', 'as', 'at', 'before', 'but', 'by', 'for', 'from',
		'is', 'in', 'into', 'like', 'of', 'off', 'on', 'onto', 'per',
		'since', 'than', 'the', 'this', 'that', 'to', 'up', 'via',
		'with'
	);

	/**
	 * The character map.
	 */
	private static $map = array ();

	/**
	 * The character list as a string.
	 */
	private static $chars = '';

	/**
	 * The character list as a regular expression (only $language).
	 */
	private static $regex = '';

	/**
	 * The character list as a regular expression (all $language).
	 */
	private static $regexs = '';

	/**
	 * The current language
	 */
	private static $language = '';

	/**
	 * Initializes the character map.
     * @param string $language
	 */
	private static function init ($language = "")
    {
		if (count (self::$map) > 0 && (($language == "") || ($language == self::$language))) {
			return;
		}		
		/* Is a specific map associated with $language ? */
		if (isset(self::$maps[$language]) && is_array(self::$maps[$language])) {
			/* Move this map to end. This means it will have priority over others */
			$m = self::$maps[$language];
			unset(self::$maps[$language]);
			self::$maps[$language] = $m;
		}
		/* Reset static vars */
		self::$language = $language;
		self::$map = array();
		self::$chars = '';

		/* regex only $language */
		$map = self::$maps[$language];
		foreach ($map as $orig => $conv) {
			self::$map[$orig] = $conv;
			self::$chars .= $orig;
		}
		self::$regex = '/[' . self::$chars . ']/u';
		/* regexs all $language */
		/* foreach (self::$maps as $map) {
			foreach ($map as $orig => $conv) {
				self::$map[$orig] = $conv;
				self::$chars .= $orig;
			}
		} */
		self::$regexs = '/[' . self::$chars . ']/u';
	}

	/**
	 * Add new characters to the list. `$map` should be a hash.
     * @param array $map
	 */
	public static function add_chars ($map)
    {
		//if (! is_array ($map)) {
			//throw new LogicException ('$map must be an associative array.');
		//}
		self::$maps[] = $map;
		self::$map = array ();
		self::$chars = '';
	}

	/**
	 * Append words to the remove list. Accepts either single words
	 * or an array of words.
     * @param mixed $words
	 */
	public static function remove_words ($words)
    {
		$words = is_array ($words) ? $words : array ($words);
		self::$remove_list = array_merge (self::$remove_list, $words);
	}

	/**
	 * Transliterates characters to their ASCII equivalents.
     * $language specifies a priority for a specific language.
     * The latter is useful if languages have different rules for the same character.
     * @param string $text
     * @param string $language
     * @return string
	 */
	public static function downcode ($text, $language = "")
    {
    	$text_default = $text;
		self::init ($language);		
		if (preg_match_all (self::$regex, $text, $matches)) {
			for ($i = 0; $i < count ($matches[0]); $i++) {
				$char = $matches[0][$i];
				if (isset (self::$map[$char])) {
					$text = str_replace ($char, self::$map[$char], $text);
				}
			}
		}
		if(count($text) >= count($text_default)){
			return $text;
		}else{
			return $text_default;
		}
		
	}

	/**
	 * Filters a string, e.g., "Petty theft" to "petty-theft"
	 * @param string $text The text to return filtered
	 * @param int $length The length (after filtering) of the string to be returned
	 * @param string $language The transliteration language, passed down to downcode()
	 * @param bool $file_name Whether there should be and additional filter considering this is a filename
	 * @param bool $use_remove_list Whether you want to remove specific elements previously set in self::$remove_list
	 * @param bool $lower_case Whether you want the filter to maintain casing or lowercase everything (default)
	 * @param bool $treat_underscore_as_space Treat underscore as space, so it will replaced with "-"
     * @return string
	 */
	public static function filter ($text, $length = 60, $language = "", $file_name = false, $use_remove_list = true, $lower_case = true, $treat_underscore_as_space = true)
    {
		$text = self::downcode ($text,$language);

		if ($use_remove_list) {
			// remove all these words from the string before urlifying
			$text = preg_replace ('/\b(' . join ('|', self::$remove_list) . ')\b/i', '', $text);
		}

		// if downcode doesn't hit, the char will be stripped here
		$remove_pattern = ($file_name) ? '/[^_\-.\-a-zA-Z0-9\s]/u' : '/[^\s_\-a-zA-Z0-9]/u';
		$text = preg_replace ($remove_pattern, '', $text); // remove unneeded chars
		if ($treat_underscore_as_space) {
		    	$text = str_replace ('_', ' ', $text);             // treat underscores as spaces
		}
		$text = preg_replace ('/^\s+|\s+$/u', '', $text);  // trim leading/trailing spaces
		$text = preg_replace ('/[-\s]+/u', '-', $text);    // convert spaces to hyphens
		if ($lower_case) {
			$text = strtolower ($text);                        // convert to lowercase
		}

		return trim (substr ($text, 0, $length), '-');     // trim to first $length chars
	}

	/**
	 * Alias of `URLify::downcode()`.
	 */
	public static function transliterate ($text)
    {
		return self::downcode ($text);
	}
}