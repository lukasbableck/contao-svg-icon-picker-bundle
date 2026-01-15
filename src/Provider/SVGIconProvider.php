<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\Provider;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SVGIconProvider {
    private array $cache = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function getIcons(string $sourceDirectory, ?string $metadataDirectory): array {
        $sourceDirectory = rtrim($this->projectDir, '/').'/'.ltrim($sourceDirectory, '/');
        if (isset($this->cache[$sourceDirectory])) {
            return $this->cache[$sourceDirectory];
        }
        $this->buildIconCache($sourceDirectory, $metadataDirectory);

        return $this->cache[$sourceDirectory] ?? [];
    }

    private function buildIconCache(string $sourceDirectory, ?string $metadataDirectory): void {
        if (!is_dir($sourceDirectory)) {
            throw new \RuntimeException(\sprintf('The source directory "%s" does not exist.', $sourceDirectory));
        }

        $metadata = $this->getMetadata($metadataDirectory);
        $icons = [];
        foreach (glob($sourceDirectory.'/*.svg') as $file) {
            $filename = basename($file, '.svg');
            $icon = [
                'path' => ltrim(str_replace($this->projectDir, '', $file), '/'),
                'content' => file_get_contents($file),
            ];
            if (null !== $metadata && isset($metadata['icons'][$filename])) {
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

    private function getMetadata(?string $metadataDirectory): ?array {
        if (!$metadataDirectory) {
            return null;
        }

        $metadataDirectory = rtrim($this->projectDir, '/').'/'.trim($metadataDirectory, '/');
        if (!is_dir($metadataDirectory)) {
            throw new \RuntimeException(\sprintf('The metadata directory "%s" does not exist.', $metadataDirectory));
        }

        $icons = [];
        $iconsFile = $metadataDirectory.'/icons.json';
        if (file_exists($iconsFile)) {
            $icons = json_decode(file_get_contents($iconsFile), true);
            if (false === $icons) {
                throw new \RuntimeException(\sprintf('Failed to parse JSON file: %s', $iconsFile));
            }
        }

        return [
            'icons' => $icons,
        ];
    }
}
