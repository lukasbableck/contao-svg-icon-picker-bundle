# contao-svg-icon-picker-bundle

This bundle adds a svg icon picker widget to Contao.

## Configuration

Create a field in your DCA/RSCE/...
```php
'icon' => [
	'inputType' => 'svgIconPicker',
	'eval' => [
		'sourceDirectory' => 'files/icons',
		'metadataDirectory' => 'files/icons/metadata',
		'tl_class' => 'clr'
	],
	'sql' => 'blob NULL',
],
```

Put your SVG icons in the specified `sourceDirectory`. \
Icon metadata like search terms and labels can be provided via an icons.json, which has to be placed in the `metadataDirectory`. Providing metadata is completely optional. \
Currently only an icons.json file in the scheme of the [FontAwesome icons.json](https://github.com/FortAwesome/Font-Awesome/blob/7.x/metadata/icons.json) is supported. \
Glyphs are not supported yet.

## Usage 

This extension provides a Twig function to render the SVG icon in your templates.
```twig
{{ svg_icon(icon) }}
```
