<?php
class Params implements SeekableIterator, ArrayAccess, Countable
{
	private $position = 0;
	private $values = [];

	public function __construct($values = [])
	{
		$this->values = self::merge_recursive($this->values, $values);
	}

	/* MÉTHODES DE L'INTERFACE SeekableIterator */
	public function current()
	{
		return $this->values[$this->position];
	}
	public function key()
	{
		return $this->position;
	}
	public function next()
	{
		$this->position++;
	}
	public function rewind()
	{
		$this->position = 0;
	}
	public function seek($position)
	{
		$anciennePosition = $this->position;
		$this->position = $position;

		if (!$this->valid()) {
			trigger_error('La position spécifiée n\'est pas valide', E_USER_WARNING);
			$this->position = $anciennePosition;
		}
	}
	public function valid()
	{
		return isset($this->values[$this->position]);
	}

	/* MÉTHODES DE L'INTERFACE ArrayAccess */
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
	
	/* MÉTHODES DE L'INTERFACE Countable */
	public function count()
	{
		return count($this->values);
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
	static function merge_recursive($a1, $a2)
	{
		foreach ($a2 as $key => $value) {
			if (is_array($value)) {
				if (isset($a1[$key]) && is_array($a1[$key])) {
					$value = self::merge_recursive($a1[$key], $a2[$key]);
				}
			}
			$a1[$key] = $value;
		}
		return $a1;
	}

	public function load($path = '', $paramFile = 'params.yaml', $tmpPath = 'tmp' . DIRECTORY_SEPARATOR)
	{
		$paramFilePath = $path . $paramFile;
		$paramCachePath = $tmpPath . $path . $paramFile . '.cache';
		if (is_file($paramFilePath)) {
			if (is_file($paramCachePath) && (filemtime($paramFilePath) <= filemtime($paramCachePath)))
				self::__construct(unserialize(file_get_contents($paramCachePath)));
			else {
				self::__construct(Spyc::YAMLLoad($paramFilePath));
				$this->cache($paramFile, $tmpPath . $path);
			}
		}
	}

	public function cache($paramFile, $cachePath)
	{
		if (!is_dir($cachePath)) {
			// dir doesn't exist, make it
			mkdir($cachePath, 0777, true);
		}
		file_put_contents($cachePath . $paramFile . '.cache', serialize($this->values));
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
}
?>