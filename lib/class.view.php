<?php

/**
 * Class to render HTML li blocs from Page Objects. It's main goal is to support
 * recursivity of Pages and generate indented lists blocs.
 */
class View
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
	protected $view;
	protected $data;
	protected $html;

	public function __construct($tag, $view, $data)
	{
		$this->tag = $tag;
		$this->view = $view;
		$this->data = $data;
		$this->evalHtml();
	}

	protected function evalHtml()
	{
		$viewPath = self::PATH . "$this->tag.$this->view.php";
		extract($this->data);
		ob_start();
		include $viewPath;
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
		if (!$endTag = $this->endTag())
			return false;
		$index = strrpos($this->html, $endTag);
		$start = substr($this->html, 0, $index);
		$end = substr($this->html, $index);
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
		return $this->html;
	}
}

