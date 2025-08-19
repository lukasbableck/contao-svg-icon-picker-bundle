<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Widget\Backend;

use Contao\System;
use Contao\Widget;
use Lukasbableck\ContaoSVGIconPickerBundle\Provider\SVGIconProvider;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

class SVGIconPicker extends Widget {
	protected $blnSubmitInput = true;
	protected $blnForAttribute = true;
	protected $strTemplate = 'be_widget';

	public function generate(): string {
		if (!$this->sourceDirectory) {
			throw new \RuntimeException('The sourceDirectory option is required for the SVGIconPicker widget.');
		}

		$twig = System::getContainer()->get('twig');
		$provider = System::getContainer()->get(SVGIconProvider::class);

		$icons = $provider->getIcons($this->sourceDirectory, $this->metadataDirectory);

		$this->addAssets();

		return $twig->render(
			'@Contao/backend/widget/svg_icon_picker.html.twig',
			[
				'id' => $this->strId,
				'name' => $this->strName,
				'value' => $this->value,
				'label' => $this->strLabel,
				'required' => $this->blnMandatory,
				'sourceDirectory' => $this->sourceDirectory,
				'tags' => $this->getAttributes(),
				'icons' => $icons,
			]
		);
	}

	private function addAssets(): void {
		$package = new Package(new JsonManifestVersionStrategy(__DIR__.'/../../../public/manifest.json'));
		$GLOBALS['TL_CSS'][] = $package->getUrl('backend.css');
		$GLOBALS['TL_JAVASCRIPT'][] = $package->getUrl('backend.js');
	}

	protected function validator($varInput) {
		$varInput = parent::validator($varInput);
		if ($varInput && !file_exists(rtrim(System::getContainer()->getParameter('kernel.project_dir'), '/').'/'.ltrim($varInput, '/'))) {
			$this->addError(\sprintf('The SVG icon "%s" does not exist.', $varInput));

			return '';
		}

		return $varInput;
	}
}
