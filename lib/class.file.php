<?php
class File
{
	protected $name;
	protected $path;

	public function ext()
	{
		$pieces = explode('.', $this->name);
		return strtolower(array_pop($pieces));
	}

	public function type()
	{
		$ext = $this->ext();
		$imgExt = ["png", "jpg", "gif", "jpeg", "tiff"];
		$txtExt = ["txt", "md", "php", "js", "gitignore", "css", "html", "xml", "json", "py", "sql"];
		if (array_search($ext, $imgExt) !== false)
			return 'image';
		if (array_search($ext, $txtExt) !== false)
			return 'text';
		return 'unknown';
	}
}

?>