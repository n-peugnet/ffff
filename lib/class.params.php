<?php
class Params implements ArrayAccess
{
	private $position = 0;
	private $values = [];

	const EXT = '.cache';
	const PUSH = 0;
	const OVERRIDE = 1;

	public function __construct($values = [])
	{
		$this->values = $values;
	}

	public function __isset($key)
	{
		return isset($this->values[$key]);
	}

	/**
	 * Intelligent merge where $a2 overrides $a1 recursively
	 * @param array $a1
	 * @param array $a2
	 */
	static function merge_recursive($a1, $a2, $numBehavior = self::OVERRIDE)
	{
		foreach ($a2 as $key => $value) {
			if (is_array($value)) {
				if (isset($a1[$key]) && is_array($a1[$key])) {
					$value = self::merge_recursive($a1[$key], $a2[$key]);
				}
			}
			if (is_int($key) && $numBehavior == self::PUSH) {
				if (!in_array($value, $a1))
					array_push($a1, $value);
			} else
				$a1[$key] = $value;
		}
		return $a1;
	}

	/**
	 * Loads a configuration from cache if it is up to date, or else, from the parameter file and then cache it.
	 * @param string $paramFile Name of the param file. (default : params.yaml)
	 * @param string $path Path to the directory of the param file.
	 * @param int    $numBehavior Merging behavior of the loading
	 * @param string $tmpDir Name of the temporary directory. (default : tmp)
	 */
	public function load($paramFile, $path = '', $numBehavior = self::OVERRIDE, $tmpDir = 'tmp')
	{
		$cachePath = $tmpDir . DIRECTORY_SEPARATOR . $path;
		$paramFilePath = $path . $paramFile;
		$paramCachePath = $cachePath . $paramFile . self::EXT;
		if (is_file($paramFilePath)) {
			if (is_file($paramCachePath) && (filemtime($paramFilePath) <= filemtime($paramCachePath)))
				$this->override(unserialize(file_get_contents($paramCachePath)), $numBehavior);
			else {
				$paramFileValues = Spyc::YAMLLoad($paramFilePath);
				$this->override($paramFileValues, $numBehavior);
				$this->cache($paramFile, $cachePath, $paramFileValues);
			}
		}
	}

	public function cache($paramFile, $cachePath, $values)
	{
		if (!is_dir($cachePath)) {
			// dir doesn't exist, make it
			mkdir($cachePath, 0777, true);
		}
		file_put_contents($cachePath . $paramFile . self::EXT, serialize($values));
	}

	/**
	 * Merge the current configuration with the one given in parameter by overriding.
	 */
	public function override($params, $numBehavior = self::OVERRIDE)
	{
		$this->values = self::merge_recursive($this->values, $params, $numBehavior);
	}

	public function get($param, $level = -1)
	{
		if (!empty($this->values[$param])) {
			if ($level >= 0) {
				if (!empty($this->values[$param][$level])) {
					return $this->values[$param][$level];
				}
				return false;
			}
			return $this->values[$param];
		}
		return false;
	}

	public function getCustom($param)
	{
		if (!empty($this['custom'][$param]))
			return $this['custom'][$param];
		return false;
	}

	public function getCustomKey($param, $key)
	{
		if ($param = $this->getCustom($param)) {
			if (!empty($param[$key]))
				return $param[$key];
		}
		return false;
	}

	///////////////////////////////////////////////////////
	//                    INTEFACES                      //
	///////////////////////////////////////////////////////

	// ArrayAccess functions
	public function offsetExists($key)
	{
		return isset($this->values[$key]);
	}
	public function offsetGet($key)
	{
		return isset($this->values[$key]) ? $this->values[$key] : false;
	}
	public function offsetSet($key, $value)
	{
		$this->values[$key] = $value;
	}
	public function offsetUnset($key)
	{
		unset($this->values[$key]);
	}
}
?>