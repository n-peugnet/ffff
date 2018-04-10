<?php
class MarkdownFF_Parser extends Markdown_Parser
{
	protected $page;

	function __construct($page)
	{
		$this->page = $page;
		parent::__construct();
	}

	function _doAnchors_inline_callback($matches)
	{
		$whole_match = $matches[1];
		$link_text = $this->runSpanGamut($matches[2]);
		$url = $matches[3] == '' ? $matches[4] : $matches[3];
		$title = &$matches[7];

		$url = $this->encodeAttribute($url);
		$type = FFRouter::analizeUrl($url);
		$url = $this->page->url($url, $type);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .= " title=\"$title\"";
		}
		if ($type == FFRouter::DISTANT)
			$result .= " target=\"_blank\"";

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}

	function _doImages_inline_callback($matches)
	{
		$whole_match = $matches[1];
		$alt_text = $matches[2];
		$url = $matches[3] == '' ? $matches[4] : $matches[3];
		$title = &$matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeAttribute($url);
		$url = $this->page->url($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .= " title=\"$title\""; # $title already quoted
		}
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}

	function _doHeaders_callback_setext($matches)
	{
		# Terrible hack to check we haven't found an empty list item.
		if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
			return $matches[0];

		$level = $matches[2] {
			0} == '=' ? 1 : 2;
		$text = $this->runSpanGamut($matches[1]);
		$id = strtolower(str_replace(' ', '-', $text));
		$block = "<h$level id=\"$id\">" . $text . "</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	function _doHeaders_callback_atx($matches)
	{
		$level = strlen($matches[1]);
		$text = $this->runSpanGamut($matches[2]);
		$id = strtolower(str_replace(' ', '-', $text));
		$block = "<h$level id=\"$id\">" . $text . "</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
}
?>