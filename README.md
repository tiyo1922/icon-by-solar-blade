# icon-by-solar/blade

Official Laravel Blade icon package for **Icon by Solar**. Easily use 3,738 icons across 3 curated styles (**Bold**, **Bold Duotone**, and **Outline**) directly inside your Blade views with zero React or JavaScript dependencies.

**Credit Title:** **Icon by Solar**  
*(Original SVG icon designs by 480 Design under CC BY 4.0; Laravel Blade package under MIT).*

---

## Features

- **Idiomatic Blade Syntax:** `<x-icon-by-solar name="home" variant="outline" size="24" class="text-blue-600" />`.
- **Zero JS/React Runtime:** 100% PHP and Blade server-rendered.
- **On-Demand Loading:** Only reads the requested SVG from disk; never loads all 3,738 icons into memory.
- **Tailwind CSS Ready:** Applies `currentColor` by default so Tailwind `text-*` classes style icons seamlessly.
- **True Duotone Support:** Retains design opacity and allows custom `secondary-color` and `secondary-opacity`.
- **Automatic Fallback:** Missing icons render a graceful dashed placeholder and HTML warning comment without breaking the layout.
- **HTML Attributes Forwarding:** Pass any attribute (`id`, `data-*`, `aria-label`, `style`, `class`) and it merges into the root `<svg>`.

---

## Environment Support

- **PHP:** `^8.1 || ^8.2 || ^8.3 || ^8.4`
- **Laravel Framework (illuminate/view & support):** `^9.0 || ^10.0 || ^11.0 || ^12.0`
- **Composer:** `>= 2.0`
- **Self-Contained:** Includes all 3,738 SVG assets in `resources/svg` with zero monorepo dependencies.

---

## Installation

### Official Registry (After Packagist Publication)

```bash
composer require icon-by-solar/blade
```

### Local Workspace / Monorepo / Pre-Publication

Add the package as a local path repository in your application's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "/path/to/icon-by-solar/packages/laravel-blade",
        "options": {
            "symlink": true
        }
    }
]
```

Then install via Composer:

```bash
composer require icon-by-solar/blade:@dev
```

---

### Configuration (Optional)

Publish the configuration file:

```bash
php artisan vendor:publish --tag="icon-by-solar-config"
```

This creates `config/icon-by-solar.php` where you can set default variant, default size, default color, and missing icon behavior.

---

## Usage

### 1. Basic Icon (Outline Style by Default)

```blade
<x-icon-by-solar name="home" size="24" class="text-gray-700" />
```

### 2. Bold Style with Custom Size & Color

```blade
<x-icon-by-solar
    name="bell-bing"
    variant="bold"
    size="32"
    color="#0342C9"
/>
```

### 3. Bold Duotone Style with Tailwind Utility

```blade
<x-icon-by-solar
    name="chat-square-arrow"
    variant="bold-duotone"
    size="24"
    class="text-emerald-500"
/>
```

### 4. Customizing Duotone Secondary Layer

```blade
<x-icon-by-solar
    name="shield-check"
    variant="bold-duotone"
    size="28"
    color="#1E40AF"
    secondary-color="#93C5FD"
    secondary-opacity="0.7"
/>
```

### 5. Component Tag Aliases

You can use either `<x-icon-by-solar ...>` or `<x-solar-icon ...>` interchangeably:

```blade
<x-solar-icon name="camera" variant="outline" size="20" />
```

---

## Component Attributes Reference

| Attribute | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `name` | `string` | *(Required)* | Canonical icon name or alias (e.g. `home`, `chat-square-arrow`, `bell-bing`). |
| `variant` | `string` | `'outline'` | Style variant: `outline`, `bold`, or `bold-duotone` (or `bold_duotone`). |
| `size` | `int\|string` | `24` | Width & height in px or CSS units. |
| `color` | `string` | `'currentColor'` | Primary SVG color. Set to `'original'` to preserve source colors. |
| `secondary-color` | `string` | `null` | Custom color for secondary duotone elements. |
| `secondary-opacity` | `float\|string` | `null` | Opacity override for duotone elements (e.g. `0.5`). |
| `category` | `string` | `null` | Explicit category name for collision disambiguation. |
| `class` | `string` | `null` | CSS class string (e.g. `text-indigo-600 hover:text-indigo-800`). |

---

## License & Attribution

- **Icon Artwork:** CC BY 4.0 by **480 Design**.
- **Package Code:** MIT License.
- **Credit Title:** **Icon by Solar**.
