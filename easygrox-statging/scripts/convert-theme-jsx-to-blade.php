#!/usr/bin/env php
<?php

/**
 * Convert theme JSX components to Blade partials.
 * Usage: php scripts/convert-theme-jsx-to-blade.php [theme]
 */

$adminRoot = dirname(__DIR__);
$themesDir = $adminRoot.'/salon-website/themes';
$viewsRoot = $adminRoot.'/resources/views/storefront/themes';

$componentMap = [
    'TopBar'              => 'top-bar',
    'HeroSection'         => 'hero',
    'StickyNav'           => 'sticky-nav',
    'AboutSection'        => 'about',
    'RelaxationSection'   => 'relaxation',
    'SpecialOfferBanner'  => 'special-offer',
    'PremiumBanner'       => 'premium-banner',
    'FooterInfoCards'     => 'footer-info-cards',
    'Footer'              => 'footer',
];

$themes = $argv[1] ?? null;
$themeList = $themes
    ? [StorefrontNormalize($themes)]
    : array_values(array_filter(scandir($themesDir) ?: [], fn ($d) => $d !== '.' && $d !== '..' && is_dir($themesDir.'/'.$d)));

function StorefrontNormalize(string $slug): string
{
    return strtolower(trim($slug));
}

function convertJsxToBlade(string $jsx, string $theme): string
{
    $lines = explode("\n", $jsx);
    $out = [];
    $skipUntilReturn = false;
    $inReturn = false;
    $depth = 0;

    foreach ($lines as $line) {
        $trim = trim($line);

        if (preg_match('/^import\s/', $trim)) {
            continue;
        }
        if (preg_match('/^export default function/', $trim)) {
            continue;
        }
        if (preg_match('/^function\s+\w+/', $trim) && ! str_contains($trim, 'export default')) {
            $skipUntilReturn = true;
            continue;
        }
        if ($skipUntilReturn) {
            if (preg_match('/^\s*return\s*\(/', $line)) {
                $skipUntilReturn = false;
                $inReturn = true;
                $line = preg_replace('/^\s*return\s*\(/', '', $line) ?? $line;
            } else {
                continue;
            }
        }
        if (preg_match('/^\s*if\s*\(!salon\)\s*return\s*null/', $trim)) {
            $out[] = '@if($data[\'salon\'] ?? null)';
            continue;
        }
        if (preg_match('/^\s*const\s+\w+\s*=/', $trim) && ! str_contains($trim, 'assetUrl')) {
            continue;
        }
        if (preg_match('/^\s*useEffect\(/', $trim)) {
            continue;
        }
        if (preg_match('/^\s*useMemo\(/', $trim)) {
            continue;
        }
        if (preg_match('/^\s*useState\(/', $trim)) {
            continue;
        }
        if (preg_match('/^\s*const\s+\{[^}]+\}\s*=\s*useSalon\(\)/', $trim)) {
            continue;
        }

        $line = str_replace('className=', 'class=', $line);
        $line = preg_replace('/assetUrl\([\'"]assets\/([^\'"]+)[\'"]\)/', "{{ \$asset('$1') }}", $line) ?? $line;
        $line = preg_replace('/assetUrl\(`assets\/([^`]+)`\)/', "{{ \$asset('$1') }}", $line) ?? $line;
        $line = preg_replace('/salon\.([\w?]+)/', "{{ \$data['salon']['$1'] ?? '' }}", $line) ?? $line;
        $line = preg_replace('/salon\?\.([\w]+)/', "{{ \$data['salon']['$1'] ?? '' }}", $line) ?? $line;
        $line = preg_replace('/\{salon\.([\w]+)\}/', "{{ \$data['salon']['$1'] ?? '' }}", $line) ?? $line;
        $line = preg_replace('/\{ratingLabel\}/', '{{ $ratingLabel }}', $line) ?? $line;
        $line = preg_replace('/\{weekdayLine\}/', '{{ $weekdayLine }}', $line) ?? $line;
        $line = preg_replace('/\{weekendLine\}/', '{{ $weekendLine }}', $line) ?? $line;
        $line = preg_replace('/\{heroImage\}/', '{{ $heroImage }}', $line) ?? $line;
        $line = preg_replace('/\{([^}]+)\}/', '{{ $1 }}', $line) ?? $line;
        $line = str_replace('fillRule=', 'fill-rule=', $line);
        $line = str_replace('clipRule=', 'clip-rule=', $line);
        $line = str_replace('strokeWidth=', 'stroke-width=', $line);
        $line = str_replace('strokeLinecap=', 'stroke-linecap=', $line);
        $line = str_replace('strokeLinejoin=', 'stroke-linejoin=', $line);
        $line = str_replace('stopColor=', 'stop-color=', $line);
        $line = str_replace('gradientUnits=', 'gradient-units=', $line);
        $line = str_replace('clipPath=', 'clip-path=', $line);
        $line = str_replace('textPath=', 'textPath=', $line);
        $line = str_replace('allowFullScreen', 'allowfullscreen', $line);
        $line = str_replace('referrerPolicy=', 'referrerpolicy=', $line);
        $line = str_replace('&&', '&&', $line);

        if (str_contains($line, '<SalonLogo')) {
            if (preg_match('/variant="(\w+)"/', $line, $m)) {
                $variant = $m[1];
            } else {
                $variant = 'header';
            }
            $out[] = "@include('storefront.partials.salon-logo', ['variant' => '$variant'])";
            continue;
        }
        if (str_contains($line, '</SalonLogo>') || str_contains($line, '/>') && str_contains($line, 'SalonLogo')) {
            continue;
        }
        if (str_contains($line, '<BookButton')) {
            $class = '';
            if (preg_match('/class="([^"]*)"/', $line, $m) || preg_match('/class=\{`([^`]*)`\}/', $line, $m)) {
                $class = $m[1];
            }
            $out[] = "@include('storefront.partials.book-button', ['class' => '$class'])";
            continue;
        }
        if (str_contains($line, '</BookButton>')) {
            continue;
        }

        $line = preg_replace('/onClick=\{[^}]+\}/', '', $line) ?? $line;
        $line = preg_replace('/onError=\{[^}]+\}/', '', $line) ?? $line;
        $line = preg_replace('/onMouseDown=\{[^}]+\}/', '', $line) ?? $line;
        $line = preg_replace('/style=\{[^}]+\}/', '', $line) ?? $line;
        $line = preg_replace('/loading="lazy"/', 'loading="lazy"', $line) ?? $line;
        $line = preg_replace('/\{\.\.\.Array\((\d+)\)\.map\([^)]+\)\}/', '@foreach(range(1, $1) as $i)@endforeach', $line) ?? $line;

        if (preg_match('/^\s*\)\s*;?\s*$/', $trim) && $inReturn) {
            $out[] = '@endif';
            break;
        }

        $out[] = rtrim($line);
    }

    $blade = implode("\n", $out);
    $blade = preg_replace('/@endif\s*@endif/', '@endif', $blade) ?? $blade;

    return $blade;
}

function addPhpPreamble(string $jsxFile, string $blade): string
{
    $name = basename($jsxFile, '.jsx');
    $preamble = '';

    if ($name === 'TopBar') {
        $preamble = "@php\n".
            "\$lines = \$data['salon']['opening_hours_lines'] ?? [];\n".
            "\$weekdayLine = \$lines[0] ?? 'See opening hours below';\n".
            "\$weekendLine = count(\$lines) > 1 ? implode(' · ', array_slice(\$lines, 1, 2)) : '';\n".
            "@endphp\n".
            "@if(\$data['salon'] ?? null)\n";
    } elseif ($name === 'HeroSection') {
        $preamble = "@php\n".
            "\$salonData = \$data['salon'] ?? [];\n".
            "\$heroImage = \$salonData['cover_image_url'] ?? \$asset('26254 1.png');\n".
            "\$ratingLabel = (!empty(\$salonData['avg_rating']) && !empty(\$salonData['review_count']))\n".
            "    ? 'Rated '.\$salonData['avg_rating'].' Stars · '.\$salonData['review_count'].' reviews'\n".
            "    : 'Rated 5 Stars by Clients';\n".
            "@endphp\n".
            "@if(\$salonData)\n";
    } elseif ($name === 'FooterInfoCards') {
        $preamble = "@php\n".
            "\$salonData = \$data['salon'] ?? [];\n".
            "\$contactDetails = array_filter([\$salonData['phone'] ?? null, \$salonData['email'] ?? null]);\n".
            "\$hourLines = !empty(\$salonData['opening_hours_lines']) ? \$salonData['opening_hours_lines'] : ['Contact us for opening hours'];\n".
            "\$locationDetails = !empty(\$salonData['full_address']) ? [\$salonData['full_address']] : [];\n".
            "\$cards = [\n".
            "    ['title' => 'Contact', 'details' => \$contactDetails ?: ['Contact details coming soon']],\n".
            "    ['title' => 'Opening Hours', 'details' => \$hourLines],\n".
            "    ['title' => 'Location', 'details' => \$locationDetails ?: ['Address coming soon']],\n".
            "];\n".
            "@endphp\n".
            "@if(\$salonData)\n";
    } elseif (in_array($name, ['AboutSection', 'Footer'], true)) {
        $preamble = "@if(\$data['salon'] ?? null)\n";
    }

    return $preamble.$blade.(str_contains($blade, '@if') ? "\n@endif" : '');
}

foreach ($themeList as $theme) {
    $srcDir = $themesDir.'/'.$theme.'/components';
    $destDir = $viewsRoot.'/'.$theme.'/partials';

    if (! is_dir($srcDir)) {
        fwrite(STDERR, "Skip {$theme}: no components dir\n");
        continue;
    }

    if (! is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    foreach ($componentMap as $jsxName => $bladeName) {
        $src = $srcDir.'/'.$jsxName.'.jsx';
        if (! is_file($src)) {
            fwrite(STDERR, "[{$theme}] missing {$jsxName}.jsx\n");
            continue;
        }

        $jsx = file_get_contents($src);
        $blade = convertJsxToBlade($jsx, $theme);
        $blade = addPhpPreamble($src, $blade);

        $dest = $destDir.'/'.$bladeName.'.blade.php';
        file_put_contents($dest, $blade);
        echo "[{$theme}] {$bladeName}.blade.php\n";
    }

    $showPath = $viewsRoot.'/'.$theme.'/show.blade.php';
    if (! is_file($showPath)) {
        $show = <<<'BLADE'
@extends('storefront.layouts.theme')

@section('content')
@include("storefront.themes.{$theme}.partials.top-bar")
@include("storefront.themes.{$theme}.partials.hero")
@include("storefront.themes.{$theme}.partials.sticky-nav")
@include("storefront.themes.{$theme}.partials.about")
@include('storefront.partials.dynamic.services')
@include('storefront.partials.dynamic.packages')
@include("storefront.themes.{$theme}.partials.relaxation")
@include("storefront.themes.{$theme}.partials.special-offer")
@include('storefront.partials.dynamic.staff')
@include("storefront.themes.{$theme}.partials.premium-banner")
@include('storefront.partials.dynamic.locations')
@include('storefront.partials.dynamic.testimonials')
@include("storefront.themes.{$theme}.partials.footer-info-cards")
@include("storefront.themes.{$theme}.partials.footer")
@include('storefront.partials.booking-flow')
@endsection
BLADE;
        if (! is_dir(dirname($showPath))) {
            mkdir(dirname($showPath), 0755, true);
        }
        file_put_contents($showPath, $show);
        echo "[{$theme}] show.blade.php\n";
    }
}

echo "Done.\n";
