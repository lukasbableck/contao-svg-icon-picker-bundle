<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Provider;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

class SVGIconProvider {
	private array $cache = [];

	public function __construct(
		#[Autowire('%kernel.project_dir%')]
		private readonly string $projectDir
	) {
	}

	public function getIcons(string $sourceDirectory, ?string $metadataDirectory): array {
		$sourceDirectory = rtrim($this->projectDir, '/').'/'.ltrim($sourceDirectory, '/');
		if (!is_dir($sourceDirectory)) {
			throw new \RuntimeException(\sprintf('The source directory "%s" does not exist.', $sourceDirectory));
		}

		if (isset($this->cache[$sourceDirectory])) {
			return $this->cache[$sourceDirectory];
		}

		if (!isset($this->cache[$sourceDirectory])) {
			$this->buildIconCache($sourceDirectory, $metadataDirectory);
		}

		return $this->cache[$sourceDirectory];
	}

	private function buildIconCache(string $sourceDirectory, ?string $metadataDirectory): void {
		$metadata = $this->getMetadata($metadataDirectory);
		$icons = [];
		foreach (glob($sourceDirectory.'/*.svg') as $file) {
			$filename = basename($file, '.svg');
			$icon = [
				'path' => ltrim(str_replace($this->projectDir, '', $file), '/'),
				'content' => file_get_contents($file),
			];
			if ($metadata && isset($metadata['icons'][$filename])) {
				$icon['label'] = $metadata['icons'][$filename]['label'] ?? null;
				$icon['searchterms'] = $metadata['icons'][$filename]['search']['terms'] ?? [];
			}
			$icons[$filename] = $icon;
		}
		if (empty($icons)) {
			throw new \RuntimeException(\sprintf('No SVG icons found in directory "%s".', $sourceDirectory));
		}
		$this->cache[$sourceDirectory] = $icons;
	}

	private function getMetadata(string $metadataDirectory) {
		if (!$metadataDirectory) {
			return null;
		}

		$metadataDirectory = rtrim($this->projectDir, '/').'/'.ltrim($metadataDirectory, '/');
		if (!is_dir($metadataDirectory)) {
			throw new \RuntimeException(\sprintf('The metadata directory "%s" does not exist.', $metadataDirectory));
		}

		$categories = [];
		$categoriesFile = $metadataDirectory.'/categories.yml';
		if (file_exists($categoriesFile)) {
			$categories = Yaml::parseFile($categoriesFile);
			if (false === $categories) {
				throw new \RuntimeException(\sprintf('Failed to parse YAML file: %s', $categoriesFile));
			}
		}

		$icons = [];
		$iconsFile = $metadataDirectory.'/icons.yml';
		if (file_exists($iconsFile)) {
			$icons = Yaml::parseFile($iconsFile);
			if (false === $icons) {
				throw new \RuntimeException(\sprintf('Failed to parse YAML file: %s', $iconsFile));
			}
		}

		return [
			'categories' => $categories,
			'icons' => $icons,
		];
	}
}
