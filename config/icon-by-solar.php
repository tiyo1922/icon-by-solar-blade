<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Icon by Solar Configuration
    |--------------------------------------------------------------------------
    | Credit Title: Icon by Solar
    | Original SVG Artwork by 480 Design (CC BY 4.0).
    */

    // Default style variant: 'outline', 'bold', or 'bold-duotone'
    'default_variant' => 'outline',

    // Default icon size in pixels or CSS units
    'default_size' => 24,

    // Default color ('currentColor' allows Tailwind text-* utilities to apply)
    'default_color' => 'currentColor',

    // Base directory for SVG source files.
    // If null, resolves automatically relative to package or published folder.
    'svg_path' => null,

    // Path to manifest.json file
    'manifest_path' => __DIR__ . '/../resources/manifest.json',

    // Throw an exception when an icon is not found, or output an HTML warning comment
    'throw_on_missing' => false,

    // Enable in-memory caching of loaded and transformed SVGs
    'cache' => true,
];
