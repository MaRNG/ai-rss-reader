<?php

declare(strict_types=1);

namespace App\Model\Feed;

use HTMLPurifier;
use HTMLPurifier_Config;


/**
 * Sanitizace HTML z feedů (HTML Purifier) a odvození čistého textu.
 */
final class HtmlSanitizer
{
	private ?HTMLPurifier $purifier = null;


	public function __construct(
		private readonly string $cacheDir,
	) {
	}


	public function sanitize(string $html, ?string $baseUrl = null): string
	{
		$config = $this->createConfig($baseUrl);
		return trim($this->getPurifier($config)->purify($html, $config));
	}


	public function toText(string $html): string
	{
		$html = preg_replace('~<(br|/p|/div|/li|/h[1-6]|/blockquote|/pre)\b[^>]*>~i', "\$0\n", $html) ?? $html;
		$text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = preg_replace('~[ \t\x{00A0}]+~u', ' ', $text) ?? $text;
		$text = preg_replace('~\s*\n\s*~', "\n", $text) ?? $text;
		return trim($text);
	}


	/** Krátký jednořádkový perex z HTML nebo textu */
	public function excerpt(string $html, int $length = 400): string
	{
		$text = preg_replace('~\s+~u', ' ', $this->toText($html)) ?? '';
		return mb_strlen($text) > $length
			? rtrim(mb_substr($text, 0, $length - 1)) . '…'
			: $text;
	}


	private function createConfig(?string $baseUrl): HTMLPurifier_Config
	{
		$config = HTMLPurifier_Config::createDefault();
		if (!is_dir($this->cacheDir)) {
			@mkdir($this->cacheDir, 0o777, true);
		}
		$config->set('Cache.SerializerPath', $this->cacheDir);
		$config->set('HTML.Allowed', implode(',', [
			'p', 'br', 'hr', 'a[href|title]', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'small', 'mark',
			'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
			'blockquote', 'q', 'pre', 'code', 'kbd', 'figure', 'figcaption',
			'img[src|alt|title|width|height]',
			'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
		]));
		$config->set('HTML.TargetBlank', true);
		$config->set('HTML.TargetNoopener', true);
		$config->set('HTML.TargetNoreferrer', true);
		$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
		$config->set('AutoFormat.RemoveEmpty', true);
		$config->set('Attr.AllowedFrameTargets', ['_blank']);
		if ($baseUrl) {
			$config->set('URI.Base', $baseUrl);
			$config->set('URI.MakeAbsolute', true);
		}
		$config->set('HTML.DefinitionID', 'siftly');
		$config->set('HTML.DefinitionRev', 1);
		// HTML5 prvky, které HTML Purifier (HTML 4) nezná; definice se cachuje podle DefinitionID/Rev
		if ($def = $config->maybeGetRawHTMLDefinition()) {
			$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
			$def->addElement('figcaption', 'Inline', 'Flow', 'Common');
			$def->addElement('mark', 'Inline', 'Inline', 'Common');
		}
		return $config;
	}


	private function getPurifier(HTMLPurifier_Config $config): HTMLPurifier
	{
		return $this->purifier ??= new HTMLPurifier($config);
	}
}
