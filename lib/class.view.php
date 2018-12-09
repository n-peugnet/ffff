<?php

/**
 * Class to render HTML li blocs from Page Objects. It's main goal is to support
 * recursivity of Pages and generate indented lists blocs.
 */
class View extends File
{
	const PATH = 'tpl/views/';
	const VOID_ELEMENTS = [
		'area',
		'base',
		'br',
		'col',
		'command',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr'
	];
	protected $tag;
	protected $data;
	protected $html = null;

	public function __construct($tag, $view, $data = [])
	{
		parent::__construct(self::PATH . "$tag.$view.php");
		$this->tag = $tag;
		$this->data = $data;
	}

	public function getHtml()
	{

		if ($this->html === null) {
			$this->evalHtml();
		}
		return $this->html;
	}

	protected function evalHtml()
	{
		extract($this->data);
		ob_start();
		include $this->path;
		$this->html = ob_get_clean();
	}

	protected function endTag()
	{
		if (in_array($this->tag, self::VOID_ELEMENTS))
			return false;
		return "</$this->tag>";
	}

	protected function split()
	{
		if (!$endTag = $this->endTag()) {
			return false;
		}
		$html = $this->getHtml();
		$index = strrpos($html, $endTag);
		$start = substr($html, 0, $index);
		$end = substr($html, $index);
		return [$start, $end];
	}

	public function insert($html)
	{
		if ($pieces = $this->split()) {
			array_splice($pieces, 1, 0, [$html]);
			$this->html = implode($pieces);
		}
	}

	public function __toString()
	{
		return $this->getHtml();
	}
}

