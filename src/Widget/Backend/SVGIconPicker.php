<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Widget\Backend;

use Contao\System;
use Contao\Widget;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Yaml\Yaml;

class SVGIconPicker extends Widget {
	protected $blnSubmitInput = true;
	protected $blnForAttribute = true;
	protected $strTemplate = 'be_widget';

	public function generate(): string {
		if (!$this->sourceDirectory) {
			throw new \RuntimeException('The sourceDirectory option is required for the SVGIconPicker widget.');
		}
		$this->sourceDirectory = System::getContainer()->getParameter('kernel.project_dir').'/'.ltrim($this->sourceDirectory, '/');
		if (!is_dir($this->sourceDirectory)) {
			throw new \RuntimeException(\sprintf('The source directory "%s" does not exist.', $this->sourceDirectory));
		}

		$package = new Package(new JsonManifestVersionStrategy(__DIR__.'/../../../public/manifest.json'));
		$GLOBALS['TL_CSS'][] = $package->getUrl('backend.css');
		$GLOBALS['TL_JAVASCRIPT'][] = $package->getUrl('backend.js');

		$metadata = $this->getMetadata();

		$twig = System::getContainer()->get('twig');

		return $twig->render(
			'@Contao/backend/widget/svg_icon_picker.html.twig',
			[
				'id' => $this->strId,
				'name' => $this->strName,
				'value' => $this->value,
				'iconValue' => $this->value ? file_get_contents(System::getContainer()->getParameter('kernel.project_dir').'/'.ltrim($this->value, '/')) : '',
				'label' => $this->strLabel,
				'required' => $this->blnMandatory,
				'tags' => $this->getAttributes(),
				'icons' => $this->getAllIcons($metadata),
				'categories' => json_encode($metadata['categories'] ?? []),
			]
		);
	}

	private function getAllIcons(?array $metadata): array {
		$icons = [];
		foreach (glob($this->sourceDirectory.'/*.svg') as $file) {
			$filename = basename($file, '.svg');
			$icon = [
				'path' => ltrim(str_replace(System::getContainer()->getParameter('kernel.project_dir'), '', $file), '/'),
				'content' => file_get_contents($file),
			];
			if ($metadata && isset($metadata['icons'][$filename])) {
				$icon['label'] = $metadata['icons'][$filename]['label'] ?? null;
				$icon['searchterms'] = $metadata['icons'][$filename]['search']['terms'] ?? [];
			}
			$icons[$filename] = $icon;
		}

		return $icons;
	}

	protected function validator($varInput) {
		$varInput = parent::validator($varInput);
		if ($varInput && !file_exists(System::getContainer()->getParameter('kernel.project_dir').'/'.ltrim($varInput, '/'))) {
			$this->addError(\sprintf('The SVG icon "%s" does not exist.', $varInput));

			return '';
		}

		return $varInput;
	}

	private function getMetadata() {
		if (!$this->metadataDirectory) {
			return null;
		}
		$metadataPath = System::getContainer()->getParameter('kernel.project_dir').'/'.ltrim($this->metadataDirectory, '/');
		if (!is_dir($metadataPath)) {
			throw new \RuntimeException(\sprintf('The metadata directory "%s" does not exist.', $metadataPath));
		}

		$categories = [];
		$categoriesFile = $metadataPath.'/categories.yml';
		if (file_exists($categoriesFile)) {
			$categories = Yaml::parseFile($categoriesFile);
			if (false === $categories) {
				throw new \RuntimeException(\sprintf('Failed to parse YAML file: %s', $categoriesFile));
			}
		}

		$icons = [];
		$iconsFile = $metadataPath.'/icons.yml';
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
