@props([
    'name',
    'variant' => 'outline',
    'size' => 24,
    'color' => null,
    'secondaryColor' => null,
    'secondaryOpacity' => null,
    'category' => null,
])

@php
    $resolver = app(\IconBySolar\Blade\SvgResolver::class);
    $html = $resolver->render(
        name: $name,
        variant: $variant,
        size: $size,
        color: $color,
        secondaryColor: $secondaryColor,
        secondaryOpacity: $secondaryOpacity,
        category: $category,
        attributes: $attributes->getAttributes()
    );
@endphp

{!! $html !!}
