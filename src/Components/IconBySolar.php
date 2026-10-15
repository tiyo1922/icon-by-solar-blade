<?php

namespace IconBySolar\Blade\Components;

use Illuminate\View\Component;
use IconBySolar\Blade\SvgResolver;

class IconBySolar extends Component
{
    public string $name;
    public string $variant;
    public int|string $size;
    public ?string $color;
    public ?string $secondaryColor;
    public int|float|string|null $secondaryOpacity;
    public ?string $category;

    protected SvgResolver $resolver;

    public function __construct(
        string $name,
        string $variant = 'outline',
        int|string $size = 24,
        ?string $color = null,
        ?string $secondaryColor = null,
        int|float|string|null $secondaryOpacity = null,
        ?string $category = null,
        ?SvgResolver $resolver = null
    ) {
        $this->name = $name;
        $this->variant = $variant;
        $this->size = $size;
        $this->color = $color;
        $this->secondaryColor = $secondaryColor;
        $this->secondaryOpacity = $secondaryOpacity;
        $this->category = $category;
        $this->resolver = $resolver ?? new SvgResolver();
    }

    public function render()
    {
        return function (array $data) {
            $attributes = [];
            if (isset($data['attributes'])) {
                $attributes = $data['attributes']->getAttributes();
            }

            return $this->resolver->render(
                $this->name,
                $this->variant,
                $this->size,
                $this->color,
                $this->secondaryColor,
                $this->secondaryOpacity,
                $this->category,
                $attributes
            );
        };
    }
}
