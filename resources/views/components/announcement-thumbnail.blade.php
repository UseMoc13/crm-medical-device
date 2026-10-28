@props([
    'announcement',
    'url',
    'alt' => null,
])

@php
    $hex = fn ($value, $default) => is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)
        ? $value
        : $default;

    $fit = in_array($announcement->thumbnail_fit, ['cover', 'fit', 'blur'], true)
        ? $announcement->thumbnail_fit
        : 'cover';

    $position = in_array($announcement->thumbnail_position, ['center', 'top', 'bottom', 'left', 'right'], true)
        ? $announcement->thumbnail_position
        : 'center';

    $backgroundStyle = in_array($announcement->thumbnail_background_style, ['solid', 'gradient', 'transparent'], true)
        ? $announcement->thumbnail_background_style
        : 'solid';

    $solid = $hex($announcement->thumbnail_background, '#17284f');
    $gradientStart = $hex($announcement->thumbnail_gradient_start, '#17284f');
    $gradientEnd = $hex($announcement->thumbnail_gradient_end, '#2ba7a0');

    // Only "Fit" mode uses the solid / gradient background layer.
    $background = null;

    if ($fit === 'fit' && $backgroundStyle !== 'transparent') {
        $background = $backgroundStyle === 'gradient'
            ? "linear-gradient(135deg, {$gradientStart}, {$gradientEnd})"
            : $solid;
    }
@endphp

<div {{ $attributes->class(['ann-thumb', 'ann-thumb--' . $fit]) }}>
    @if($fit === 'blur')
        <img src="{{ $url }}" alt="" class="ann-thumb-blur" aria-hidden="true">
    @endif

    @if($background)
        <div class="ann-thumb-bg" style="background: {{ $background }}"></div>
    @endif

    <img
        src="{{ $url }}"
        alt="{{ $alt ?? $announcement->title }}"
        class="ann-thumb-main ann-thumb-pos-{{ $position }}">
</div>

@once
<style>
    .ann-thumb {
        position: relative;
        isolation: isolate;
        width: 100%;
        aspect-ratio: 16 / 7;
        overflow: hidden;
        background: #17284f;
    }

    .ann-thumb-blur,
    .ann-thumb-bg,
    .ann-thumb-main {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .ann-thumb-blur {
        z-index: 0;
        object-fit: cover;
        filter: blur(22px) brightness(.78);
        transform: scale(1.13);
    }

    .ann-thumb-bg {
        z-index: 0;
    }

    .ann-thumb-main {
        z-index: 2;
        display: block;
        object-fit: cover;
    }

    .ann-thumb--fit .ann-thumb-main,
    .ann-thumb--blur .ann-thumb-main {
        object-fit: contain;
    }

    .ann-thumb-pos-center { object-position: center; }
    .ann-thumb-pos-top { object-position: center top; }
    .ann-thumb-pos-bottom { object-position: center bottom; }
    .ann-thumb-pos-left { object-position: left center; }
    .ann-thumb-pos-right { object-position: right center; }

    @media (max-width: 650px) {
        .ann-thumb {
            aspect-ratio: 16 / 9;
        }
    }
</style>
@endonce
