<?php
class Params
{
	private $values = [];

	/** @var File */
	private $fileOrigin;

	/** @var Cache */
	private $fileCached;

	const PUSH = 0;
	const OVERRIDE = 1;

	public function __construct($values = [], $filePath)
	{
		$this->values = $values;
		$this->fileOrigin = new File("$filePath.yaml");
		$parentPath = $this->fileOrigin->getParentPath();
		$this->fileCached = new Cache("$parentPath.json");
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
	public function load($numBehavior = self::OVERRIDE)
	{
		if ($this->fileOrigin->exist()) {
			if ($this->fileCached->exist() && $this->fileOrigin->getLastModif() <= $this->fileCached->getLastModif()) {
				$content = $this->fileCached->read();
				$array = json_decode($content, true);
				$this->override($array, $numBehavior);
			} else {
				$paramFileValues = Spyc::YAMLLoad($this->fileOrigin->getPath());
				$this->override($paramFileValues, $numBehavior);
				$this->cache($paramFileValues);
			}
		}
	}

	public function cache($values)
	{
		$this->fileCached->write(json_encode($values));
	}

	/**
	 * Merge the current configuration with the one given in parameter by overriding.
	 */
	public function override($params, $numBehavior = self::OVERRIDE)
	{
		$this->values = self::merge_recursive($this->values, $params, $numBehavior);
	}

	/**
	 * Get a param if exist else null
	 */
	public function get(...$params)
	{
		return $this->fetch($this->values, $params, 'value');
	}

	public function isset(...$params)
	{
		return $this->fetch($this->values, $params, 'isset');
	}

	public function empty(...$params)
	{
		return $this->fetch($this->values, $params, 'value') === null ? true : false;
	}

	private function fetch($array, $keys, $mode)
	{
		$key = array_shift($keys);
		if (($mode == 'value' && !empty($array[$key])) || ($mode == 'isset' && isset($array[$key]))) {
			if (count($keys) > 0) {
				return $this->fetch($array[$key], $keys, $mode);
			}
			return $mode == 'value' ? $array[$key] : true;
		}
		return $mode == 'value' ? null : false;
	}
}
