<?php

namespace IconBySolar\Blade;

class SvgResolver
{
    protected static ?array $manifest = null;
    protected static array $svgCache = [];
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? (function_exists('config') ? (config('icon-by-solar') ?? []) : []);
    }

    /**
     * Resolves and renders the requested icon.
     */
    public function render(
        string $name,
        string $variant = 'outline',
        int|string|null $size = null,
        ?string $color = null,
        ?string $secondaryColor = null,
        int|float|string|null $secondaryOpacity = null,
        ?string $category = null,
        array $attributes = []
    ): string {
        $variant = $this->normalizeVariant($variant);
        $size = $size ?? ($this->config['default_size'] ?? 24);
        $color = $color ?? ($this->config['default_color'] ?? 'currentColor');

        $manifest = $this->getManifest();
        $canonical = $this->resolveCanonicalName($name, $category, $manifest);

        if (!$canonical || !isset($manifest['icons'][$canonical]['variants'][$variant])) {
            return $this->handleMissingIcon($name, $variant);
        }

        $iconInfo = $manifest['icons'][$canonical];
        $variantInfo = $iconInfo['variants'][$variant];
        $relativePath = $variantInfo['file'];

        $svgContent = $this->loadSvgFile($relativePath);
        if (!$svgContent) {
            return $this->handleMissingIcon($name, $variant, "File not found: {$relativePath}");
        }

        return $this->transformSvg(
            $svgContent,
            $size,
            $color,
            $secondaryColor,
            $secondaryOpacity,
            $attributes,
            $variant
        );
    }

    /**
     * Normalizes variant names (e.g. 'bold_duotone' -> 'bold-duotone').
     */
    public function normalizeVariant(string $variant): string
    {
        $v = strtolower(trim($variant));
        if ($v === 'bold_duotone' || $v === 'bold duotone') {
            return 'bold-duotone';
        }
        return $v;
    }

    /**
     * Resolves an icon name or alias to its canonical key.
     */
    public function resolveCanonicalName(string $name, ?string $category, array $manifest): ?string
    {
        $cleanName = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $cleanName);
        $slug = trim($slug, '-');

        // 1. Direct canonical match
        if (isset($manifest['icons'][$slug])) {
            return $slug;
        }

        // 2. Lookup alias dictionary
        if (isset($manifest['aliases'][$cleanName])) {
            return $manifest['aliases'][$cleanName];
        }
        if (isset($manifest['aliases'][$slug])) {
            return $manifest['aliases'][$slug];
        }

        // 3. Category scoped lookup
        if ($category) {
            $catSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($category)));
            $scoped = "{$catSlug}/{$slug}";
            if (isset($manifest['aliases'][$scoped])) {
                return $manifest['aliases'][$scoped];
            }
        }

        return null;
    }

    /**
     * Loads the manifest JSON.
     */
    public function getManifest(): array
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        $path = $this->config['manifest_path'] ?? (__DIR__ . '/../resources/manifest.json');
        if (!file_exists($path)) {
            // Try searching up to project root
            $rootPath = dirname(__DIR__, 3) . '/manifest.json';
            if (file_exists($rootPath)) {
                $path = $rootPath;
            }
        }

        if (file_exists($path)) {
            static::$manifest = json_decode(file_get_contents($path), true) ?? [];
        } else {
            static::$manifest = ['icons' => [], 'aliases' => []];
        }

        return static::$manifest;
    }

    /**
     * Reads SVG file on demand with in-memory caching.
     */
    protected function loadSvgFile(string $relativePath): ?string
    {
        if (isset(static::$svgCache[$relativePath])) {
            return static::$svgCache[$relativePath];
        }

        // Search locations
        $candidates = [];
        if (!empty($this->config['svg_path'])) {
            $candidates[] = rtrim($this->config['svg_path'], '/') . '/' . $relativePath;
        }

        // 1. Package resources directory
        $candidates[] = dirname(__DIR__) . '/resources/svg/' . $relativePath;
        // 2. Monorepo root
        $candidates[] = dirname(__DIR__, 3) . '/' . $relativePath;
        // 3. Published path in Laravel host application
        if (function_exists('resource_path')) {
            $candidates[] = resource_path('svg/icon-by-solar/' . $relativePath);
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $content = file_get_contents($candidate);
                if ($content !== false) {
                    if ($this->config['cache'] ?? true) {
                        static::$svgCache[$relativePath] = $content;
                    }
                    return $content;
                }
            }
        }

        return null;
    }

    /**
     * Transforms SVG string: adjusts size, color, duotone opacity, and merges attributes.
     */
    protected function transformSvg(
        string $svg,
        int|string $size,
        string $color,
        ?string $secondaryColor,
        int|float|string|null $secondaryOpacity,
        array $attributes,
        string $variant
    ): string {
        // Strip xml declaration if present
        $svg = preg_replace('/<\?xml[^>]*\?>/i', '', $svg);
        $svg = trim($svg);

        // Find root <svg ...> tag
        if (!preg_match('/<svg\b([^>]*)>(.*)<\/svg>/is', $svg, $matches)) {
            return $svg;
        }

        $rootAttrsString = $matches[1];
        $innerSvg = $matches[2];

        // Process color & duotone transformations
        if ($color !== 'original' && $color !== 'source') {
            $isBold = ($variant === 'bold');

            // Handle elements with opacity (duotone secondary)
            $innerSvg = preg_replace_callback('/<([a-z]+)\b([^>]*)>/i', function ($elem) use ($color, $secondaryColor, $secondaryOpacity, $isBold) {
                $tag = $elem[1];
                $attrs = $elem[2];

                $hasOpacity = (bool) preg_match('/\bopacity=(["\'])([^"\']+)\1/i', $attrs, $opMatch);

                if ($hasOpacity) {
                    // Duotone secondary element
                    if ($secondaryOpacity !== null) {
                        $attrs = preg_replace('/\bopacity=(["\'])[^"\']+\1/i', "opacity=\"{$secondaryOpacity}\"", $attrs);
                    }
                    $fillColor = $secondaryColor ?: $color;
                    if (preg_match('/\bfill=(["\'])((?!none|transparent)[^"\']+)\1/i', $attrs)) {
                        $attrs = preg_replace('/\bfill=(["\'])((?!none|transparent)[^"\']+)\1/i', "fill=\"{$fillColor}\"", $attrs);
                    } else {
                        $attrs .= " fill=\"{$fillColor}\"";
                    }
                } else {
                    // Primary element
                    // Check if fill is a cutout (white in bold icon)
                    $isWhite = (bool) preg_match('/\bfill=(["\'])(white|#FEFEFF|#FBFCFF)\1/i', $attrs);
                    if ($isBold && $isWhite) {
                        // Keep inner cutout
                    } elseif (preg_match('/\bfill=(["\'])((?!none|transparent)[^"\']+)\1/i', $attrs)) {
                        $attrs = preg_replace('/\bfill=(["\'])((?!none|transparent)[^"\']+)\1/i', "fill=\"{$color}\"", $attrs);
                    }
                }

                if (preg_match('/\bstroke=(["\'])((?!none)[^"\']+)\1/i', $attrs)) {
                    $attrs = preg_replace('/\bstroke=(["\'])((?!none)[^"\']+)\1/i', "stroke=\"{$color}\"", $attrs);
                }

                return "<{$tag}{$attrs}>";
            }, $innerSvg);
        }

        // Build root attributes
        $rootAttrs = [];
        // Ensure default viewBox
        if (!preg_match('/\bviewBox=/i', $rootAttrsString)) {
            $rootAttrs['viewBox'] = '0 0 24 24';
        }
        $rootAttrs['width'] = $size;
        $rootAttrs['height'] = $size;
        $rootAttrs['fill'] = 'none';
        $rootAttrs['xmlns'] = 'http://www.w3.org/2000/svg';
        $rootAttrs['role'] = 'img';
        $rootAttrs['aria-hidden'] = 'true';

        // Merge existing root attributes (preserving viewBox etc)
        preg_match_all('/([a-z0-9_-]+)=(["\'])(.*?)\2/i', $rootAttrsString, $attrMatches, PREG_SET_ORDER);
        foreach ($attrMatches as $m) {
            $k = strtolower($m[1]);
            if ($k !== 'width' && $k !== 'height') {
                $rootAttrs[$m[1]] = $m[3];
            }
        }

        // Merge passed HTML attributes
        foreach ($attributes as $k => $v) {
            if ($v === true) {
                $rootAttrs[$k] = $k;
            } elseif ($v !== false && $v !== null) {
                if ($k === 'class' && isset($rootAttrs['class'])) {
                    $rootAttrs['class'] .= ' ' . $v;
                } else {
                    $rootAttrs[$k] = (string) $v;
                }
            }
        }

        $attrString = '';
        foreach ($rootAttrs as $k => $v) {
            $attrString .= ' ' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') . '"';
        }

        return "<svg{$attrString}>{$innerSvg}</svg>";
    }

    /**
     * Handles missing icon scenarios.
     */
    protected function handleMissingIcon(string $name, string $variant, ?string $detail = null): string
    {
        if (!empty($this->config['throw_on_missing'])) {
            throw new \InvalidArgumentException("[Icon by Solar] Icon \"{$name}\" with variant \"{$variant}\" not found." . ($detail ? " ({$detail})" : ''));
        }

        $comment = "<!-- [Icon by Solar] Icon \"{$name}\" ({$variant}) not found -->";
        // Render minimal placeholder box to prevent broken layouts
        return $comment . '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-dasharray="2 2" stroke-width="1.5" style="display:inline-block;vertical-align:middle"><rect x="3" y="3" width="18" height="18" rx="4"/></svg>';
    }
}
