<?php
class View
{
	const PATH = 'tpl/views/';
	protected $tag;
	protected $view;
	protected $element;
	protected $html;
	protected $node;

	public function __construct($tag, $view, $element)
	{
		$this->tag = $tag;
		$this->view = $view;
		$this->element = $element;
		$this->getHtml();
	}

	protected function getHtml()
	{
		$viewPath = self::PATH . "$this->tag.$this->view.php";
		$this->html = file_get_html($viewPath);
		$this->node = $this->html->find($this->tag, 0);
	}

	protected function addClass()
	{
		$this->node->class .= " $this->view";
	}

	public function insert($html)
	{
		$this->node->innertext .= "$html";
	}

	public function render()
	{
		ob_start();
		echo $this->html;
		return ob_get_clean();
	}
}

?>