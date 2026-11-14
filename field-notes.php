<?php
if (!defined('ABSPATH')) { exit; }

/** Surface roles share the same identity across cached native and shortcode HTML. */
function gpc_field_notes_surface($key, $classes, $id = '') {
    $has = function($class) use($classes) { return in_array($class, $classes, true); };
    foreach (array('gp-form-panel','gp-work-card','gp-formatting-sample','gp-case-evidence') as $class) { if ($has($class)) { return 'day'; } }
    foreach (array('gp-project-brief','gp-contact-aside','gp-case-study-card') as $class) { if ($has($class)) { return 'indigo'; } }
    foreach (array('gp-header','gp-footer','gp-home-hero','gp-page-hero','gp-proof') as $class) { if ($has($class)) { return 'night'; } }
    if (!$has('gp-section')) { return ''; }
    if ($has('gp-cta')) { return 'indigo'; }
    if ($key === 'home') {
        if ($id === 'services') { return 'indigo'; }
        if ($id === 'process') { return 'day'; }
        if ($id === 'about') { return 'night'; }
    }
    if ($has('gp-work-aplus-feature')) { return 'night'; }
    if ($has('gp-selected-work') || $has('gp-formatting-portfolio') || $has('gp-work-samples')) { return 'day'; }
    if ($has('gp-journey')) { return $key === 'about' ? 'day' : 'night'; }
    if ($has('gp-tone-ink') || $has('gp-case-study')) { return 'night'; }
    if ($has('gp-tone-indigo')) { return 'indigo'; }
    $services = array_keys(gpc_service_connections());
    if (in_array($key, $services, true) && $has('gp-tone-paper')) { return 'indigo'; }
    if ($key === 'about' && $has('gp-tone-paper')) { return 'indigo'; }
    if (in_array($key, array('case-studies','case-luma','case-grandfather'), true) && $has('gp-reading') && $has('gp-tone-lilac')) { return 'indigo'; }
    return 'day';
}

/** Attribute-only enhancement. Never reserialise legal text or saved widgets. */
function gpc_field_notes_markup($content, $key) {
    if (!is_string($content) || !$key) { return $content; }
    return preg_replace_callback('~<(div|section|article|aside|header|footer|figure|a)\b([^>]*)>~i', function($match) use($key) {
        if (!preg_match('~(?<!\S)class\s*=\s*(["\x27])([^"\x27]*)\1~i', $match[2], $class)) { return $match[0]; }
        $classes = preg_split('/\s+/', trim($class[2]));
        if (array_intersect($classes, array('gp-theme-day','gp-theme-night','gp-theme-indigo'))) { return $match[0]; }
        $id = preg_match('~(?:^|\s)id\s*=\s*(["\x27])([^"\x27]*)\1~i', $match[2], $identity) ? $identity[2] : '';
        $surface = gpc_field_notes_surface($key, $classes, $id);
        if ($surface === '') { return $match[0]; }
        return '<' . $match[1] . str_replace($class[0], 'class=' . $class[1] . $class[2] . ' gp-theme-' . $surface . $class[1], $match[2]) . '>';
    }, $content);
}
add_filter('the_content', function($content) {
    return gpc_field_notes_markup($content, gpc_journey_context());
}, 48);

add_filter('body_class', function($classes) {
    $key = gpc_current_key();
    if (!$key || is_admin() || is_preview()) { return $classes; }
    if (class_exists('\\Elementor\\Plugin') && isset(\Elementor\Plugin::$instance)) {
        $elementor = \Elementor\Plugin::$instance;
        foreach (array('editor'=>'is_edit_mode','preview'=>'is_preview_mode') as $property=>$method) {
            if (isset($elementor->$property) && is_object($elementor->$property) && method_exists($elementor->$property, $method) && $elementor->$property->$method()) { return $classes; }
        }
    }
    $classes[] = 'gpc-field-notes';
    $classes[] = 'gpc-page-' . $key;
    return array_unique($classes);
});
