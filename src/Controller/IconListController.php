<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Controller;

use Lukasbableck\ContaoSVGIconPickerBundle\Provider\SVGIconProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route(
    '%contao.backend.route_prefix%/svg-icon-picker/icons',
    name: self::class,
    defaults: ['_scope' => 'backend'],
)]
class IconListController {
    public function __construct(
        private readonly SVGIconProvider $iconProvider,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(Request $request): Response {
        $sourceDirectory = $request->query->getString('sourceDirectory');

        try {
            if ('' === $sourceDirectory) {
                throw new \Exception('Missing sourceDirectory parameter.', 400);
            }

            $metadataDirectory = $request->query->getString('metadataDirectory') ?: null;
            $icons = $this->iconProvider->getIcons($sourceDirectory, $metadataDirectory);
            $frameId = 'svg-icon-picker-'.hash('xxh3', $sourceDirectory);

            return new Response($this->twig->render('@Contao/backend/widget/svg_icon_list.html.twig', [
                'icons' => $icons,
                'frameId' => $frameId,
            ]));
        } catch (\Exception $e) {
            return new Response($this->twig->render('@Contao/backend/error/svg_icon_list_error.html.twig', [
                'errorMessage' => $e->getMessage(),
                'frameId' => 'svg-icon-picker-'.hash('xxh3', $sourceDirectory),
            ]), $e->getCode() ?: 500);
        }
    }
}
