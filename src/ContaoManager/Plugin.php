<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Lukasbableck\ContaoSVGIconPickerBundle\ContaoSVGIconPickerBundle;

class Plugin implements BundlePluginInterface {
    public function getBundles(ParserInterface $parser): array {
        return [BundleConfig::create(ContaoSVGIconPickerBundle::class)->setLoadAfter([ContaoCoreBundle::class])];
    }
}
