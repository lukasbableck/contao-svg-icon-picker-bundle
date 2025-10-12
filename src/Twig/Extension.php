<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Twig;

use enshrined\svgSanitize\Sanitizer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension {
	public function getFunctions(): array {
		return [
			new TwigFunction('svg_icon', [$this, 'renderSVG'], ['is_safe' => ['html']]),
		];
	}

	public function renderSVG(string $path): string {
		$svgContent = @file_get_contents($path);
		if (false === $svgContent) {
			$svgContent = @file_get_contents(str_replace('public/', '', $path));
			if (false === $svgContent) {
				throw new \RuntimeException("Could not read SVG file at path: $path");
			}
		}

		$sanitizer = new Sanitizer();
		$svgContent = $sanitizer->sanitize($svgContent);

		if (false === $svgContent) {
			throw new \RuntimeException("SVG content could not be sanitized at path: $path");
		}

		return str_replace('<svg', '<svg class="svg-icon svg-icon-'.basename($path, '.svg').'"', $svgContent);
	}
}
