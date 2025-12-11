<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Twig;

use enshrined\svgSanitize\Sanitizer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension {
	public function __construct(
		#[Autowire('%kernel.project_dir%')]
		private readonly string $projectDir,
	) {
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('svg_icon', [$this, 'renderSVG'], ['is_safe' => ['html']]),
		];
	}

	public function renderSVG(string $path): string {
		$svgContent = @file_get_contents(Path::join($this->projectDir, ltrim($path, '/')));
		if (false === $svgContent) {
			$svgContent = @file_get_contents(Path::join($this->projectDir, str_replace('public/', '', ltrim($path, '/'))));
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
