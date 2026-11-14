<?php
if (!defined('ABSPATH')) { exit; }

/** Actual supplied work, with client projects and independent studies identified. */
function gpc_design_portfolio() {
    static $items;
    if ($items === null) { $items = json_decode(file_get_contents(dirname(__DIR__) . '/design-portfolio.json'), true); }
    return is_array($items) ? $items : array();
}
function gpc_portfolio_image($image, $alt, $sizes = '(max-width: 720px) calc(100vw - 40px), (max-width: 1120px) 44vw, 560px') {
    $set = !empty($image['preview']) ? ' srcset="' . esc_url(gpc_asset_url($image['preview'])) . ' 520w, ' . esc_url(gpc_asset_url($image['file'])) . ' ' . (int)$image['width'] . 'w" sizes="' . esc_attr($sizes) . '"' : '';
    return '<img src="' . esc_url(gpc_asset_url($image['file'])) . '"' . $set . ' width="' . (int)$image['width'] . '" height="' . (int)$image['height'] . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async">';
}
function gpc_design_card($item, $featured = false, $mode = 'work') {
    $label = $item['kind'] === 'client' ? 'Client project' : ($item['kind'] === 'study' ? 'Independent design study' : 'Interior formatting sample');
    $image = $featured && !empty($item['presentation']) ? $item['presentation'] : $item['image'];
    $categories = array($item['category']);
    if ($item['category'] === 'formatting' && (stripos($item['scope'],'cover') !== false || $item['id'] === 'slumber-club')) { $categories[] = 'covers'; }
    $alt = $item['title'] . ': ' . $item['scope'] . ($item['kind'] === 'study' ? ', independent portfolio study' : '');
    $visual = '<a class="gp-portfolio-visual' . ($item['category'] === 'aplus' ? ' gp-portfolio-detail' : '') . '" href="' . esc_url(gpc_asset_url($image['file'])) . '" target="_blank" rel="noopener noreferrer" aria-label="Open ' . esc_attr($item['title']) . ' project image at full size (opens in a new tab)" data-elementor-open-lightbox="no">' . gpc_portfolio_image($image, $alt) . '</a>';
    $has_reader = false;
    if (!$featured && $mode !== 'cover' && $item['category'] === 'formatting' && function_exists('gpc_interior_projects')) {
        foreach (gpc_interior_projects() as $interior) { if ($interior['id'] === $item['id']) {
            $cover = in_array('covers',$categories,true) ? '<div class="gp-portfolio-cover-preview" hidden>' . preg_replace('~(?<=\s)(src|srcset)=~','data-$1=',$visual) . '</div>' : '';
            $visual = gpc_interior_reader($interior,'work') . $cover; $has_reader = true; break;
        } }
    }
    $html = '<article class="gp-portfolio-card gp-portfolio-category-' . esc_attr($item['category']) . '" data-portfolio-kind="' . esc_attr($item['kind']) . '" data-portfolio-services="' . esc_attr(implode(' ',$categories)) . '" id="portfolio-' . esc_attr($item['id']) . '">' . $visual . '<div class="gp-portfolio-copy"><p class="gp-label">' . esc_html($label) . '</p><h3>' . esc_html($item['title']) . '</h3><p class="gp-portfolio-credit">' . esc_html($item['credit']) . '</p><p class="gp-portfolio-scope">' . esc_html($item['scope']) . '</p><p>' . esc_html($item['context']) . '</p>';
    if (!empty($item['samples']) || (!empty($item['presentation']) && !$featured)) {
        $html .= '<details class="gp-portfolio-pages"><summary>Explore the project images</summary><div class="gp-portfolio-page-grid">';
        if ($has_reader && in_array('covers',$categories,true)) { $html .= '<figure><a href="' . esc_url(gpc_asset_url($item['image']['file'])) . '" target="_blank" rel="noopener noreferrer" data-elementor-open-lightbox="no">' . gpc_portfolio_image($item['image'],$item['title'] . ', supplied cover presentation') . '</a><figcaption>Cover presentation</figcaption></figure>'; }
        if (!$featured && !empty($item['presentation'])) { $html .= '<figure><a href="' . esc_url(gpc_asset_url($item['presentation']['file'])) . '" target="_blank" rel="noopener noreferrer" data-elementor-open-lightbox="no">' . gpc_portfolio_image($item['presentation'], $item['title'] . ', book presentation') . '<span class="gpc-sr-only">Open presentation at full size (opens in a new tab)</span></a><figcaption>Book presentation</figcaption></figure>'; }
        foreach ($item['samples'] as $sample) { $html .= '<figure><a href="' . esc_url(gpc_asset_url($sample['file'])) . '" target="_blank" rel="noopener noreferrer" data-elementor-open-lightbox="no">' . gpc_portfolio_image($sample, $item['title'] . ', ' . $sample['caption']) . '<span class="gpc-sr-only">Open project image at full size (opens in a new tab)</span></a><figcaption>' . esc_html($sample['caption']) . '</figcaption></figure>'; }
        $html .= '</div></details>';
    }
    if (!empty($item['download'])) { $html .= '<a class="gp-text-link" href="' . esc_url(gpc_asset_url($item['download'])) . '" target="_blank" rel="noopener noreferrer">Open the fillable workbook sample<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a>'; }
    if (!empty($item['printable'])) { $html .= '<a class="gp-text-link" href="' . esc_url(gpc_asset_url($item['printable'])) . '" target="_blank" rel="noopener noreferrer">Open the printable workbook sample<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a>'; }
    if ($item['category'] === 'aplus') { $html .= '<a class="gp-text-link" href="' . esc_url(gpc_asset_url($image['file'])) . '" target="_blank" rel="noopener noreferrer">Open the complete A+ Content example<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a>'; }
    if ($featured) { $html .= '<a class="gp-text-link" href="' . esc_url(gpc_url('case-studies') . '#client-design-projects') . '">Explore the client projects ' . gpc_direction_icon() . '</a>'; }
    if (!empty($item['url'])) { $html .= '<a class="gp-text-link" href="' . esc_url($item['url']) . '" target="_blank" rel="noopener noreferrer">Explore the author’s books<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a>'; }
    return $html . '</div></article>';
}
function gpc_portfolio_collection($kind, $context = 'work') {
    $featured = $context === 'home'; $cards = '';
    foreach (gpc_design_portfolio() as $item) {
        if ($featured && !in_array($item['id'], array('cognate-shortcut','christmas-road-trip'), true)) { continue; }
        if (!$featured && $context === 'formatting' && !in_array($item['id'], array('christmas-road-trip','zoe','elfland','twenty-four-hours','next-chapter','slumber-club'), true)) { continue; }
        if (!$featured && $context !== 'formatting' && $item['kind'] !== $kind) { continue; }
        $cards .= gpc_design_card($item, $featured);
    }
    if (!$cards) { return ''; }
    $id = $context === 'formatting' ? 'interior-portfolio' : ($kind === 'client' ? 'client-design-projects' : ($kind === 'sample' ? 'interior-examples' : 'design-studies'));
    $title = $context === 'formatting' ? 'Inside the book.' : ($featured ? 'Book design,<br>in detail.' : ($kind === 'client' ? 'More client work.' : ($kind === 'sample' ? 'Interior examples.' : 'Independent design studies.')));
    $intro = $context === 'formatting' ? 'Chapter openings, reading pages, workbooks and activity layouts, with original project and author credits.' : ($kind === 'client' ? 'Cover design, book interiors and A+ Content from client projects. Open an image to explore the details.' : ($kind === 'sample' ? 'Selected interior layouts, with author attribution.' : 'Self-directed portfolio studies in cover presentation, typography and interior layout. Original authors and edition credits are retained.'));
    $more = $context === 'formatting' ? '<p class="gp-recommendations-next"><a class="gp-text-link" href="' . esc_url(gpc_url('case-studies')) . '">Explore the complete design portfolio ' . gpc_direction_icon() . '</a></p>' : '';
    return '<section class="gp-section gp-portfolio-collection gp-theme-day' . ($featured ? ' gp-portfolio-featured' : '') . '" id="' . esc_attr($id) . '" data-gpc-portfolio="' . esc_attr($context . '-' . $kind) . '"><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">' . ($kind === 'client' ? 'CLIENT DESIGN & PRESENTATION' : 'THE DESIGN PORTFOLIO') . '</p><div class="gp-heading"><h2>' . $title . '</h2></div></div><div class="gp-copy"><p>' . esc_html($intro) . '</p></div></div><div class="gp-portfolio-grid">' . $cards . '</div>' . $more . '</div></section>';
}
function gpc_exhibition_content($content, $key) {
    if (!is_string($content) || !in_array($key, array('home','feedback','case-studies','book-formatting','cover-design'), true) || strpos($content, 'data-gpc-exhibition') !== false) { return $content; }
    if ($key === 'home') {
        $hero = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-home-hero'); });
        if ($hero) {
            $inner = substr($content, $hero['inner_start'], $hero['close'] - $hero['inner_start']);
            $actions = gpc_content_block_bounds($inner, function($tag) { return gpc_tag_has_class($tag, 'gp-actions'); });
            if ($actions) {
                $quick = '<nav class="gp-hero-paths" aria-label="Client video"><a href="#hilda-video-review"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor"/><path d="M10 7.5v9l7-4.5z" fill="currentColor"/></svg>Watch Hilda’s publishing experience ' . gpc_direction_icon() . '</a></nav>';
                $inner = substr_replace($inner, $quick, $actions['end'], 0);
                $content = substr_replace($content, $inner, $hero['inner_start'], $hero['close'] - $hero['inner_start']);
            }
        }
        $selected = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-selected-work'); });
        if ($selected) { $content = substr_replace($content, gpc_portfolio_collection('client', 'home'), $selected['end'], 0); }
        $proof = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-proof'); });
        if ($proof) { $content = substr_replace($content, gpc_client_video_feature('home'), $proof['start'], 0); }
    }
    if ($key === 'case-studies') {
        $content = str_replace('Two books. Two different needs. A clear account of the work completed, the evidence available and the limits of what it shows.', 'Client projects and independent design studies. Explore the work, the deliverables and the context behind each project.', $content);
        $hero = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-page-hero'); });
        if ($hero) {
            $nav = '<nav class="gp-portfolio-nav gp-wrap" aria-label="Work collections"><a href="#client-design-projects">Client projects</a><a href="#design-studies">Independent studies</a><a href="#interior-examples">Interior examples</a><a href="' . esc_url(gpc_url('feedback') . '#hilda-video-review') . '">Client video</a></nav>';
            $content = substr_replace($content, $nav, $hero['end'], 0);
        }
        $first = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-section'); });
        if ($first && strpos(substr($content, $first['start'], $first['end'] - $first['start']), 'gp-work-grid') !== false) {
            $content = substr_replace($content, gpc_portfolio_collection('client'), $first['end'], 0);
        } else { $content = gpc_insert_journey($content, gpc_portfolio_collection('client')); }
        $content = gpc_insert_journey($content, gpc_portfolio_collection('study') . gpc_portfolio_collection('sample'));
    }
    if ($key === 'feedback') {
        $hero = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-page-hero'); });
        if ($hero) { $nav = '<nav class="gp-feedback-nav gp-wrap" aria-label="Client feedback sections"><a href="#hilda-video-review">Watch Hilda’s video</a><a href="#client-recommendations">Written recommendations</a><a href="#john-capon-review">John’s book project</a></nav>'; $content = substr_replace($content, $nav, $hero['end'], 0); }
    }
    if ($key === 'book-formatting') { $content = gpc_insert_journey($content, gpc_portfolio_collection('', 'formatting')); }
    if ($key === 'cover-design') {
        $cards = ''; foreach (gpc_design_portfolio() as $item) { if (in_array($item['id'], array('hazlo-si-te-quieres','cognate-shortcut','christmas-road-trip'), true)) { $cards .= gpc_design_card($item, false, 'cover'); } }
        $content = gpc_insert_journey($content, '<section class="gp-section gp-portfolio-collection gp-theme-day" id="cover-design-examples"><div class="gp-wrap"><div class="gp-heading"><h2>Cover design, in practice.</h2></div><div class="gp-portfolio-grid">' . $cards . '</div></div></section>');
    }
    return '<!-- data-gpc-exhibition -->' . gpc_field_notes_markup($content, $key);
}
add_filter('the_content', function($content) { return gpc_exhibition_content($content, gpc_journey_context()); }, 49);
