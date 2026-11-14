<?php
if (!defined('ABSPATH')) { exit; }

/** Exact titles approved in the owner's copy/technical review. Slugs stay unchanged. */
function gpc_review_seo_titles() {
    $titles = array(
        'home'=>'Book Cover Design & Formatting | Grant Publishing Co.',
        'services'=>'Book Publishing & Marketing Services | Grant Publishing Co.',
        'about'=>'About AbdulQudus Tella | Grant Publishing Co.',
        'case-studies'=>'Book Design & Listing Case Studies | Grant Publishing Co.',
        'book-formatting'=>'Book Formatting for Kindle & Print | Grant Publishing Co.',
        'feedback'=>'Client Reviews | Grant Publishing Co.',
        'contact'=>'Contact Grant Publishing Co. | Book Publishing Help',
        'faq'=>'Book Publishing FAQs | Grant Publishing Co.',
        'insights'=>'Book Marketing Insights for Authors | Grant Publishing Co.',
    );
    foreach (array_keys(gpc_service_connections()) as $key) { if (!isset($titles[$key])) { $titles[$key] = gpc_pages()[$key]['title'] . ' | Grant Publishing Co.'; } }
    return $titles;
}
/** Known, owner-supplied media only. Unknown and subsequently replaced assets stay intact. */
function gpc_review_asset_url($url) {
    if (!is_string($url) || $url === '') { return $url; }
    $aliases = array(
        'ChatGPT-Image-Oct-9-2026-10_22_20-AM-1.png'=>'grant-footer-logo.png',
        'ChatGPT-Image-Oct-9-2026-10_22_24-AM-3.png'=>'grant-header-logo.png',
        'ChatGPT-Image-Oct-9-2026-10_22_26-AM-4.png'=>'grant-icon.png',
        'cropped-ChatGPT-Image-Oct-9-2026-10_22_24-AM-3-32x32.png'=>'grant-site-icon-32.png',
        'cropped-ChatGPT-Image-Oct-9-2026-10_22_24-AM-3-192x192.png'=>'grant-site-icon-192.png',
        'cropped-ChatGPT-Image-Oct-9-2026-10_22_24-AM-3-180x180.png'=>'grant-apple-touch-icon-180.png',
        'cropped-ChatGPT-Image-Oct-9-2026-10_22_24-AM-3-270x270.png'=>'grant-site-icon-270.png',
    );
    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST)); $path = (string) wp_parse_url($url, PHP_URL_PATH);
    if ($host !== 'grantpublishingco.com' || strpos($path, '/wp-content/uploads/2026/10/') !== 0) { return $url; }
    $file = basename($path);
    return isset($aliases[$file]) && is_readable(dirname(__DIR__) . '/assets/' . $aliases[$file]) ? gpc_asset_url($aliases[$file]) : $url;
}
function gpc_review_share_image() {
    $home = gpc_find_page('home');
    $image = $home ? trim((string) get_post_meta($home->ID, '_yoast_wpseo_opengraph-image', true)) : '';
    if ($image === '' && $home && function_exists('YoastSEO')) {
        // Read Yoast's raw image selection, not its rendered head or this page's image.
        // This also handles a home page that uses an automatically selected image.
        $meta = YoastSEO()->meta->for_post($home->ID);
        $images = $meta ? $meta->open_graph_images : array();
        foreach ((array) $images as $candidate) { if (!empty($candidate['url'])) { $image = $candidate['url']; break; } }
    }
    if ($image === '') { $social = get_option('wpseo_social', array()); $image = is_array($social) ? ($social['og_default_image'] ?? '') : ''; }
    return gpc_review_asset_url($image ?: gpc_asset_url('grant-footer-logo.png'));
}
function gpc_review_seo_fields($key) {
    $titles = gpc_review_seo_titles(); if (!isset($titles[$key])) { return array(); }
    $fields = array('_yoast_wpseo_title'=>$titles[$key]);
    if (in_array($key, array('case-studies','faq'), true)) {
        $fields['_yoast_wpseo_opengraph-title'] = $titles[$key];
        $fields['_yoast_wpseo_opengraph-image'] = gpc_review_share_image();
        $fields['_yoast_wpseo_opengraph-image-id'] = '0';
    }
    return $fields;
}
function gpc_refresh_review_seo($id) {
    if (function_exists('YoastSEO')) {
        $builder = YoastSEO()->classes->get('Yoast\\WP\\SEO\\Builders\\Indexable_Builder');
        if (is_object($builder) && method_exists($builder, 'build_for_id_and_type')) { $builder->build_for_id_and_type($id, 'post'); }
    }
    clean_post_cache($id);
    do_action('litespeed_purge_url', get_permalink($id));
}
/** One scoped migration after installation by an administrator. No public visit writes data. */
function gpc_apply_review_seo() {
    if (!current_user_can('manage_options')) { return false; }
    $lock_key = 'gpc_copy_review_seo_lock'; $old = get_option($lock_key, false);
    if (is_array($old) && ($old['started_at'] ?? time()) < time() - 300 && get_option($lock_key, false) === $old) { delete_option($lock_key); }
    $lock = array('started_at'=>time(), 'token'=>wp_generate_uuid4());
    if (!add_option($lock_key, $lock, '', false)) { return false; }
    try {
        $results = array(); $ok = true;
        foreach (gpc_review_seo_titles() as $key=>$title) {
            $page = gpc_find_page($key);
            if (!$page || $page->post_status !== 'publish' || !gpc_matches_page($page, $key) || !current_user_can('edit_post', $page->ID)) { $ok = false; $results[] = $key . ': existing page or permissions need review; nothing changed.'; continue; }
            if (get_post_meta($page->ID, '_gpc_copy_review_seo_version', true) === '4.9.0') { continue; }
            try {
                $fields = gpc_review_seo_fields($key);
                $backup = get_post_meta($page->ID, '_gpc_copy_review_seo_backup', true);
                if (!is_array($backup) || !isset($backup['original'])) {
                    $original = array();
                    foreach ($fields as $name=>$value) { $original[$name] = array('exists'=>metadata_exists('post', $page->ID, $name), 'value'=>get_post_meta($page->ID, $name, true)); }
                    $backup = array('saved_at'=>current_time('mysql'), 'original'=>$original, 'applied'=>$fields);
                    if (!add_post_meta($page->ID, '_gpc_copy_review_seo_backup', wp_slash($backup), true)) { throw new \RuntimeException('Backup unavailable'); }
                } elseif (isset($backup['applied']) && is_array($backup['applied'])) { $fields = $backup['applied']; }
                foreach ($fields as $name=>$value) {
                    update_post_meta($page->ID, $name, wp_slash($value));
                    if ((string) get_post_meta($page->ID, $name, true) !== $value) { throw new \RuntimeException('SEO field could not be saved'); }
                }
                gpc_refresh_review_seo($page->ID);
                update_post_meta($page->ID, '_gpc_copy_review_seo_version', '4.9.0');
                $results[] = $key . ': approved SEO fields saved with their original values backed up. Other Yoast fields and page content preserved.';
            } catch (\Throwable $error) { $ok = false; $results[] = $key . ': SEO update incomplete. Backup retained; retry from Grant Website Setup.'; }
        }
        if ($results) { update_option('gpc_copy_review_seo_results', $results, false); }
        return $ok;
    } finally { if (get_option($lock_key, false) === $lock) { delete_option($lock_key); } }
}
add_action('admin_init', 'gpc_apply_review_seo', 98);
add_action('admin_post_gpc_apply_review_seo', function() { gpc_admin_post_guard('gpc_apply_review_seo'); gpc_apply_review_seo(); gpc_setup_results(get_option('gpc_copy_review_seo_results', array())); });
function gpc_restore_review_seo() {
    if (!current_user_can('manage_options')) { return array('You cannot restore SEO fields.'); }
    $results = array();
    foreach (gpc_review_seo_titles() as $key=>$title) {
        $page = gpc_find_page($key); if (!$page || !current_user_can('edit_post', $page->ID)) { continue; }
        $backup = get_post_meta($page->ID, '_gpc_copy_review_seo_backup', true);
        if (!is_array($backup) || !isset($backup['original'], $backup['applied'])) { continue; }
        $changed = false;
        foreach ($backup['original'] as $name=>$original) {
            if (!array_key_exists($name, $backup['applied']) || (string) get_post_meta($page->ID, $name, true) !== $backup['applied'][$name]) { $results[] = $key . ': later manual edit preserved for ' . $name . '.'; continue; }
            if ($original['exists']) { update_post_meta($page->ID, $name, wp_slash($original['value'])); } else { delete_post_meta($page->ID, $name); }
            $changed = true;
        }
        if ($changed) { gpc_refresh_review_seo($page->ID); $results[] = $key . ': original SEO values restored. Backup retained.'; }
        // Keep the migration marker so a later dashboard visit cannot undo restoration.
    }
    return $results ?: array('No backed-up SEO fields needed restoration.');
}
add_action('admin_post_gpc_restore_review_seo', function() { gpc_admin_post_guard('gpc_restore_review_seo'); gpc_setup_results(gpc_restore_review_seo()); });

/** Literal approved defaults replace the global title template only when no manual value exists. */
function gpc_review_title_fallback($title) {
    $key = gpc_current_key(); $titles = gpc_review_seo_titles();
    if (!$key || in_array($key, array('privacy-policy','terms-of-service'), true)) { return $title; }
    if (!isset($titles[$key]) || trim((string) get_post_meta(get_queried_object_id(), '_yoast_wpseo_title', true)) !== '') { return gpc_us_english($title); }
    return $titles[$key];
}
add_filter('wpseo_title', 'gpc_review_title_fallback', 30);
add_filter('pre_get_document_title', function($title) { if (defined('WPSEO_VERSION')) { return $title; } $titles = gpc_review_seo_titles(); return $titles[gpc_current_key()] ?? $title; }, 30);
add_filter('wpseo_opengraph_title', function($title) {
    $key = gpc_current_key(); $titles = gpc_review_seo_titles();
    return in_array($key, array('case-studies','faq'), true) && trim((string) get_post_meta(get_queried_object_id(), '_yoast_wpseo_opengraph-title', true)) === '' ? $titles[$key] : ($key && !in_array($key, array('privacy-policy','terms-of-service'), true) ? gpc_us_english($title) : $title);
}, 30);
add_filter('wpseo_twitter_title', function($title) { return gpc_current_key() && !in_array(gpc_current_key(), array('privacy-policy','terms-of-service'), true) ? gpc_us_english($title) : $title; }, 30);
foreach (array('wpseo_opengraph_image','wpseo_twitter_image') as $filter) {
    add_filter($filter, function($url) { return $url === '' && in_array(gpc_current_key(), array('home','case-studies','faq'), true) ? gpc_review_share_image() : gpc_review_asset_url($url); }, 30);
}
add_filter('get_site_icon_url', 'gpc_review_asset_url', 30);
foreach (array('wpseo_metadesc','wpseo_opengraph_desc','wpseo_twitter_description') as $filter) {
    add_filter($filter, function($text) { return gpc_current_key() && !in_array(gpc_current_key(), array('privacy-policy','terms-of-service'), true) ? gpc_us_english($text) : $text; }, 40);
}

/** Block-template resolution also registers a viewport callback. Keep one owner per shell. */
add_filter('template_include', function($template) {
    $canvas = basename($template) === 'canvas.php' && strpos(str_replace('\\', '/', $template), '/elementor/') !== false;
    $grant = in_array(basename($template), array('full-page.php','draft-page.php'), true) && dirname($template) === __DIR__;
    if (($canvas && gpc_current_key()) || $grant) { remove_action('wp_head', '_block_template_viewport_meta_tag', 0); }
    return $template;
}, PHP_INT_MAX);
add_filter('elementor/template/viewport_tag', function($tag) { return gpc_current_key() ? '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">' : $tag; });

/** Extend Yoast's graph rather than emitting a competing Organization. No rating schema. */
function gpc_review_schema_media($value) {
    if (!is_array($value)) { return $value; }
    foreach ($value as $key=>$item) { $value[$key] = is_array($item) ? gpc_review_schema_media($item) : (in_array($key, array('url','contentUrl','thumbnailUrl'), true) && is_string($item) ? gpc_review_asset_url($item) : $item); }
    return $value;
}
function gpc_review_schema_graph($graph) {
    if (!gpc_current_key() || !is_array($graph)) { return $graph; }
    $graph = gpc_review_schema_media($graph);
    $organization = null; $person = null; $contact = gpc_contact_details();
    $site = home_url('/'); $org_id = $site . '#organization'; $person_id = $site . '#abdulqudus-tella';
    foreach ($graph as $index=>$node) {
        $types = (array) ($node['@type'] ?? array());
        if (in_array('Organization', $types, true) && (($node['@id'] ?? '') === $org_id || ($node['url'] ?? '') === $site)) { $organization = $index; $org_id = $node['@id'] ?? $org_id; }
        if (in_array('Person', $types, true) && ($node['name'] ?? '') === 'AbdulQudus Tella') { $person = $index; $person_id = $node['@id'] ?? $person_id; }
    }
    $org = array('@type'=>'Organization','@id'=>$org_id,'name'=>'Grant Publishing Co.','url'=>$site,'email'=>'hello@grantpublishingco.com','logo'=>array('@type'=>'ImageObject','url'=>gpc_asset_url('grant-footer-logo.png'),'width'=>1448,'height'=>1086),'founder'=>array('@id'=>$person_id));
    $existing = $organization !== null ? $graph[$organization] : array();
    $org['sameAs'] = array_values(array_unique(array_merge((array) ($existing['sameAs'] ?? array()), array($contact['linkedin'],$contact['upwork']))));
    if ($organization === null) { $graph[] = $org; } else { $graph[$organization] = array_merge($existing, $org); }
    $founder = array('@type'=>'Person','@id'=>$person_id,'name'=>'AbdulQudus Tella','url'=>gpc_url('about'),'jobTitle'=>'Book Publishing Specialist','worksFor'=>array('@id'=>$org_id),'sameAs'=>array($contact['linkedin'],$contact['upwork']));
    if ($person === null) { $graph[] = $founder; } else { $graph[$person] = array_merge($graph[$person], $founder); }
    return $graph;
}
add_filter('wpseo_schema_graph', 'gpc_review_schema_graph', 30);
add_action('wp_head', function() {
    if (!gpc_current_key() || defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION') || defined('SEOPRESS_VERSION')) { return; }
    echo '<script type="application/ld+json">' . wp_json_encode(array('@context'=>'https://schema.org','@graph'=>gpc_review_schema_graph(array())), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>' . "\n";
});
add_filter('robots_txt', function($text, $public) {
    if (!$public) { return $text; }
    $url = home_url(defined('WPSEO_VERSION') ? '/sitemap_index.xml' : '/wp-sitemap.xml');
    return stripos($text, 'Sitemap: ' . $url) !== false ? $text : rtrim($text) . "\n\nSitemap: " . $url . "\n";
}, 30, 2);
