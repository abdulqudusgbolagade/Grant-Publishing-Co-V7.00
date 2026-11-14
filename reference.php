<?php
if (!defined('ABSPATH')) { exit; }

/** Faithful PDF excerpts. Covers are deliberately not formatting thumbnails. */
function gpc_interior_projects() {
    static $items;
    if ($items !== null) { return $items; }
    $items = json_decode(file_get_contents(dirname(__DIR__) . '/interior-portfolio.json'), true);
    if (!is_array($items)) { $items = array(); }
    $seventh = array('id'=>'seventh-binding','title'=>'The Seventh Binding','credit'=>'Supplied interior portfolio sample','kind'=>'sample','scope'=>'Interior formatting','context'=>'Illustrated chapter openings, running heads and a balanced reading layout.','frames'=>array());
    foreach (array('seventh-chapter-two','seventh-text-spread','seventh-title-pages','seventh-chapter-one') as $id) {
        foreach (gpc_formatting_samples() as $sample) {
            if ($sample['id'] !== $id) { continue; }
            $seventh['frames'][] = array('label'=>$sample['view'],'images'=>array(array('file'=>gpc_formatting_sample_file($sample),'width'=>$sample['width'],'height'=>$sample['height'])));
        }
    }
    $luma = array('id'=>'luma','title'=>'Luma the Sleepy Star','credit'=>'John Capon','kind'=>'client','scope'=>'Cover redesign and interior formatting','context'=>'A 35-page, 8.5 × 8.5-inch paperback interior. The documented project ended at delivery of the print PDFs.','url'=>gpc_url('case-luma'),'frames'=>array(array('label'=>'Final formatted interior spread','images'=>array(array('file'=>'luma-interior-spread-1400.webp','preview'=>'luma-interior-spread-720.webp','preview_width'=>720,'width'=>1400,'height'=>800)))));
    $luma_types = json_decode(file_get_contents(dirname(__DIR__) . '/luma-interior-excerpts.json'), true);
    if (is_array($luma_types)) { $luma['frames'] = array_merge($luma_types, $luma['frames']); }
    array_unshift($items, $seventh, $luma);
    $order = array_flip(array('seventh-binding','luma','elfland','christmas-road-trip','twenty-four-hours','zoe','turning-pain','wood','undine','lulu-bett','enchanted-april','next-chapter','slumber-club'));
    usort($items, function($a,$b) use($order) { return $order[$a['id']] <=> $order[$b['id']]; });
    return $items;
}
function gpc_interior_image($image, $alt, $first = true) {
    $url = esc_url(gpc_asset_url($image['file']));
    $sets = !empty($image['preview']) ? gpc_asset_url($image['preview']) . ' ' . (int)($image['preview_width'] ?? 440) . 'w, ' . gpc_asset_url($image['file']) . ' ' . (int)$image['width'] . 'w' : '';
    return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" data-elementor-open-lightbox="no" aria-label="Open ' . esc_attr($alt) . ' at full size (opens in a new tab)"><img ' . ($first ? 'src' : 'data-src') . '="' . $url . '"' . ($sets ? ' ' . ($first ? 'srcset' : 'data-srcset') . '="' . esc_attr($sets) . '" sizes="(max-width:720px) 45vw, (max-width:1120px) 42vw, 560px"' : '') . ' width="' . (int)$image['width'] . '" height="' . (int)$image['height'] . '" alt="' . esc_attr($alt) . '" decoding="async"' . ($first ? ' loading="lazy"' : '') . '></a>';
}
function gpc_interior_reader($item, $prefix = 'formatting') {
    $id = $prefix . '-reader-' . $item['id'];
    $html = '<div class="gp-book-reader" id="' . esc_attr($id) . '" data-book-reader role="region" aria-label="' . esc_attr($item['title'] . ' interior samples') . '"><div class="gp-reader-stage">';
    foreach ($item['frames'] as $i=>$frame) {
        $html .= '<figure class="gp-reader-frame' . ($i === 0 ? ' is-current' : '') . '" data-reader-frame' . ($i ? ' hidden inert aria-hidden="true"' : '') . '><div class="gp-reader-spread' . (count($frame['images']) === 1 ? ' gp-reader-single' : '') . '">';
        foreach ($frame['images'] as $j=>$image) { $html .= gpc_interior_image($image, $item['title'] . ', ' . $frame['label'] . (count($frame['images']) > 1 ? ', ' . ($j ? 'right page' : 'left page') : ''), $i === 0); }
        $html .= '</div><figcaption>' . esc_html($frame['label']) . '</figcaption></figure>';
    }
    $html .= '</div><div class="gp-reader-controls" hidden><button type="button" data-reader-prev aria-label="Previous sample in ' . esc_attr($item['title']) . '">←</button><span class="gp-reader-count"><span data-reader-current>1</span> / ' . count($item['frames']) . '</span><button type="button" data-reader-next aria-label="Next sample in ' . esc_attr($item['title']) . '">→</button><button type="button" class="gp-reader-expand" data-reader-expand>Enlarge<span class="gpc-sr-only"> ' . esc_html($item['title']) . ' interior</span> ' . gpc_direction_icon() . '</button></div><p class="gpc-sr-only" data-reader-status role="status" aria-live="polite" aria-atomic="true"></p><noscript><p>Open the visible spread for a full-size view. Additional selected pages:</p><ul>';
    foreach (array_slice($item['frames'], 1) as $frame) { foreach ($frame['images'] as $j=>$image) { $html .= '<li><a href="' . esc_url(gpc_asset_url($image['file'])) . '" target="_blank" rel="noopener noreferrer">' . esc_html($frame['label'] . (count($frame['images']) > 1 ? ($j ? ', right page' : ', left page') : '')) . '<span class="gpc-sr-only"> (opens in a new tab)</span></a></li>'; } }
    return $html . '</ul></noscript></div>';
}
function gpc_interior_portfolio($context = 'formatting', $earlier_samples = '') {
    $html = '<section class="gp-section gp-interior-exhibition gp-theme-day" id="interior-portfolio" data-gpc-interiors><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">THE INTERIOR PORTFOLIO</p><div class="gp-heading"><h2>Open the book.<br><em>See the craft.</em></h2></div></div><div class="gp-copy"><p>Chapter openings, reading spreads and activity pages. Explore the typography, spacing and detail inside each book.</p><p>Selected excerpts are shown. Use the arrows to turn the pages or enlarge a spread for a closer look.</p></div></div><div class="gp-interior-grid">';
    foreach (gpc_interior_projects() as $i=>$item) {
        if ($i === 7) { $html .= '</div><details class="gp-interior-more" id="more-interior-examples"><summary>Explore more interiors and title-page samples</summary><div class="gp-interior-grid">'; }
        $label = $item['kind'] === 'client' ? 'Client project' : ($item['kind'] === 'study' ? 'Independent design study' : 'Interior portfolio sample');
        $html .= '<article class="gp-interior-project' . ($i === 0 ? ' gp-interior-leading' : '') . '" id="interior-' . esc_attr($item['id']) . '"><div class="gp-interior-project-head"><div><p class="gp-label">' . esc_html($label) . '</p><h3>' . esc_html($item['title']) . '</h3><p class="gp-interior-credit">' . esc_html($item['credit']) . '</p></div><span class="gp-interior-index" aria-hidden="true">' . str_pad((string)($i + 1),2,'0',STR_PAD_LEFT) . '</span></div>' . gpc_interior_reader($item, $context) . '<div class="gp-interior-project-caption"><p>' . esc_html($item['context']) . '</p>' . (!empty($item['url']) ? '<a class="gp-text-link" href="' . esc_url($item['url']) . '">Explore the documented project ' . gpc_direction_icon() . '</a>' : '') . '</div></article>';
    }
    return $html . '</div>' . $earlier_samples . '</details></div></section>';
}

function gpc_cinematic_hero() {
    $slides = array(
        array('title'=>'Luma the Sleepy Star','credit'=>'John Capon','scope'=>'Cover redesign & interior formatting','file'=>'luma-final-front-734.webp','width'=>734,'height'=>734,'url'=>gpc_url('case-luma'),'scene'=>true),
        array('title'=>'Christmas Road Trip Activity Book','credit'=>'Rachael Funmilayo','scope'=>'Client project · cover and interior layout','file'=>'portfolio-christmas-road-trip-presentation.webp','width'=>1254,'height'=>1254,'url'=>gpc_url('case-studies') . '#portfolio-christmas-road-trip'),
        array('title'=>'The Seventh Binding','credit'=>'Interior portfolio sample','scope'=>'Chapter opening and reading spread','file'=>'formatting-seventh-chapter-two.webp','width'=>723,'height'=>539,'url'=>gpc_url('book-formatting') . '#interior-seventh-binding'),
        array('title'=>'The King of Elfland’s Daughter','credit'=>'Lord Dunsany · independent design study','scope'=>'Cover presentation & interior typesetting','file'=>'portfolio-elfland-presentation.webp','width'=>1600,'height'=>900,'url'=>gpc_url('case-studies') . '#portfolio-elfland'),
        array('title'=>'The Cognate Shortcut','credit'=>'VocabEasy.org · client project','scope'=>'Book cover presentation','file'=>'portfolio-cognate-shortcut-presentation.webp','width'=>1200,'height'=>857,'url'=>gpc_url('case-studies') . '#portfolio-cognate-shortcut'),
    );
    $html = '<section class="gp-home-hero gp-cinematic-hero gp-theme-night" id="home" data-gpc-cinematic><div class="gp-wrap"><div class="gp-hero-grid"><div class="gp-hero-copy"><p class="gp-label">BOOK DESIGN, PUBLISHING &amp; MARKETING</p><div class="gp-hero-title"><h1>Books built to<br> <span class="gp-hero-second-line">be <em>discovered.</em></span></h1></div><div class="gp-lead"><p>Book design, interior formatting, publishing support and Amazon visibility for independent authors and publishers.</p></div><div class="gp-actions"><div class="gp-action"><a class="elementor-button" href="' . esc_url(gpc_url('case-studies')) . '">Explore our work ' . gpc_direction_icon() . '</a></div><div class="gp-action gp-secondary"><a class="elementor-button" href="' . esc_url(gpc_url('contact') . '?request=project#gpc-enquiry') . '">Discuss your book ' . gpc_direction_icon() . '</a></div></div><p class="gp-hero-assessment"><a href="' . esc_url(gpc_url('enquiry') . '?request=assessment#gpc-enquiry') . '">Already published? Request a free book assessment.</a></p><div class="gp-reputation"><span class="gp-stars" aria-label="5 out of 5 stars">★★★★★</span><p><strong>5.0 / 5 client feedback</strong><br><a class="gp-review-source-link" href="' . esc_url(gpc_url('feedback')) . '">Nadine Laman &amp; John Capon</a></p></div><a class="gp-hero-video-link" href="#hilda-video-review"><span aria-hidden="true">▷</span> Watch Hilda’s publishing experience</a></div><div class="gp-hero-art"><section class="gp-book-showcase gp-cinematic-showcase gp-showcase-home" data-book-showcase data-stop-on-browse role="region" aria-roledescription="carousel" aria-label="Selected book projects and interiors"><div class="gp-showcase-stage">';
    foreach ($slides as $i=>$slide) {
        $html .= '<div class="gp-showcase-slide' . ($i === 0 ? ' is-active' : '') . (!empty($slide['scene']) ? ' gp-book-scene' : '') . '" data-slide role="group" aria-roledescription="slide" aria-label="' . esc_attr(($i+1) . ' of ' . count($slides) . ': ' . $slide['title']) . '"' . ($i ? ' hidden inert aria-hidden="true"' : '') . '><a class="gp-showcase-book-link" href="' . esc_url($slide['url']) . '"' . ($i ? ' tabindex="-1"' : '') . ' aria-label="Explore ' . esc_attr($slide['title']) . '"><img ' . ($i ? 'data-src' : 'src') . '="' . esc_url(gpc_asset_url($slide['file'])) . '" width="' . $slide['width'] . '" height="' . $slide['height'] . '" alt="' . esc_attr($slide['title'] . ': ' . $slide['scope']) . '" decoding="async"' . ($i ? '' : ' fetchpriority="high"') . '>' . (!empty($slide['scene']) ? '<img class="gp-scene-interior" src="' . esc_url(gpc_asset_url('luma-interior-spread-720.webp')) . '" width="720" height="411" alt="Luma the Sleepy Star: final formatted interior spread" decoding="async">' : '') . '</a><div class="gp-showcase-caption"><p class="gp-showcase-title">' . esc_html($slide['title']) . '</p><p class="gp-showcase-author">' . esc_html($slide['credit']) . '</p><p class="gp-showcase-scope">' . esc_html($slide['scope']) . '</p></div></div>';
    }
    return $html . '</div><div class="gp-showcase-controls" hidden><button type="button" data-previous aria-label="Previous project">←</button><span class="gp-showcase-position" aria-hidden="true"><span data-current>1</span> / ' . count($slides) . '</span><button type="button" data-next aria-label="Next project">→</button><p class="gp-showcase-help">Use an arrow to stop and explore.</p></div><p class="gp-showcase-status gpc-sr-only" role="status" aria-live="polite" aria-atomic="true"></p></section></div></div></div></section><div class="gp-ref-trust"><div class="gp-wrap"><span>Book design &amp; formatting</span><span>Publishing &amp; KDP support</span><span>Amazon visibility</span><span>Author-led. Evidence-led.</span></div></div>';
}
function gpc_reference_services() {
    $groups = array(
        array('Book design & production','A book that feels considered, inside and out.','Readable interiors, genre-aware covers and appropriately prepared ebook and print files.',array('book-formatting'=>'Book formatting','cover-design'=>'Book cover design')),
        array('Publishing & launch','Bring the files, editions and next steps together.','Edition preparation, agreed KDP setup and a clear plan for bringing your book to market.',array('publishing-support'=>'Publishing & KDP setup','launch-promotion'=>'Book launch & promotion')),
        array('Amazon visibility','Give the right reader a clearer picture.','Considered metadata, book descriptions and A+ Content that communicate the book’s reading proposition.',array('amazon-visibility'=>'Amazon Book Visibility','book-presentation'=>'Book Descriptions & A+ Content')),
        array('Marketing & strategy','Choose the work that matters next.','Research, priorities and suitable routes to readers. Advertising setup and optional recurring management follow an agreed scope, with ad spend separate.',array('book-strategy'=>'Book Marketing Strategy','amazon-ads'=>'Amazon Ads Setup & Management','author-platform'=>'Author Platform','catalog-strategy'=>'Series & Catalog Strategy')),
    );
    $html = '<section class="gp-section gp-reference-services gp-theme-night" id="services" data-service-explorer><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">CONNECTED EXPERTISE</p><div class="gp-heading"><h2>From manuscript<br>to <em>market.</em></h2></div></div><div class="gp-copy"><p>Focused support for one stage, or a connected scope built around your book.</p><a class="gp-text-link" href="' . esc_url(gpc_url('services')) . '">Explore all ten services ' . gpc_direction_icon() . '</a></div></div><div class="gp-service-explorer"><div class="gp-service-tabs" aria-label="Service groups" hidden>';
    foreach ($groups as $i=>$g) { $html .= '<button type="button" id="gpc-service-tab-' . $i . '" data-service-tab="' . $i . '"><span class="gp-service-tab-number">0' . ($i+1) . '</span><span>' . esc_html($g[0]) . '</span>' . gpc_direction_icon() . '</button>'; }
    $html .= '</div><div class="gp-service-panels">';
    foreach ($groups as $i=>$g) {
        $html .= '<section class="gp-service-panel" id="gpc-service-panel-' . $i . '" data-service-panel="' . $i . '"><p class="gp-label">' . esc_html($g[0]) . '</p><h3>' . esc_html($g[1]) . '</h3><p>' . esc_html($g[2]) . '</p><ul>';
        foreach ($g[3] as $key=>$label) { $html .= '<li><a href="' . esc_url(gpc_url($key)) . '">' . esc_html($label) . ' ' . gpc_direction_icon() . '</a></li>'; }
        $html .= '</ul></section>';
    }
    return $html . '</div></div></div></section>';
}
function gpc_reference_process() {
    $html = '<section class="gp-section gp-reference-process gp-theme-day" id="process"><div class="gp-wrap"><div class="gp-process-intro"><p class="gp-label">THE GRANT FRAMEWORK</p><div class="gp-heading"><h2>Understand first.<br><em>Recommend second.</em></h2></div><p>Our working method for finding your book’s next priority, from market fit and presentation to suitable visibility channels and agreed review.</p><p>Each project focuses on the agreed stages. Ongoing monitoring is included only when it forms part of the scope.</p><a class="gp-text-link" href="' . esc_url(gpc_url('about') . '#grant-framework') . '">Explore our approach ' . gpc_direction_icon() . '</a></div><ol class="gp-framework-list" data-gpc-framework="home">';
    foreach (gpc_framework_stages() as $letter=>$stage) { $html .= '<li><span class="gp-framework-letter" aria-hidden="true">' . $letter . '</span><div><h3>' . esc_html($stage['name']) . '</h3><p>' . esc_html($stage['brief']) . '</p></div></li>'; }
    return $html . '</ol></div></section>';
}
function gpc_reference_replace($content, $predicate, $replacement) {
    $block = gpc_content_block_bounds($content, $predicate);
    return $block ? substr_replace($content, $replacement, $block['start'], $block['end'] - $block['start']) : $content;
}
/** Changes known reusable presentation blocks; never writes saved page/meta data. */
function gpc_reference_content($content, $key) {
    if (!is_string($content) || strpos($content,'data-gpc-reference-design') !== false) { return $content; }
    if ($key === 'home') {
        $hero = gpc_content_block_bounds($content,function($t) { return gpc_tag_has_class($t,'gp-home-hero'); });
        if ($hero) {
            $inside = substr($content,$hero['inner_start'],$hero['close']-$hero['inner_start']);
            $title = preg_match('~<h1\b[^>]*>(.*?)</h1>~is',$inside,$heading) ? preg_replace('/\s+/u','',wp_strip_all_tags($heading[1])) : '';
            // Upgrade the shipped hero while protecting later owner-authored copy.
            if (in_array($title,array('Yourmanuscript.Professionallyproduced.','Bringyourbooktolife.Helpitreachreaders.'),true)) { $content = substr_replace($content,gpc_cinematic_hero(),$hero['start'],$hero['end']-$hero['start']); }
        }
        $content = gpc_reference_replace($content, function($t) { return gpc_tag_has_class($t,'gp-selected-work'); }, gpc_reference_selected());
        $content = gpc_reference_replace($content, function($t) { return strpos($t,'data-gpc-portfolio="home-client"') !== false; }, '');
        $content = gpc_reference_replace($content, function($t) { return strpos($t,'data-gpc-service-overview') !== false; }, gpc_reference_services());
        $content = gpc_reference_replace($content, function($t) { return preg_match('~\bid=["\x27]process["\x27]~',$t); }, gpc_reference_process());
        // Retain the intake/audience copy as useful disclosures, rather than two
        // large bands competing with the named five-stage working method.
        $intake = '';
        foreach (array('who-i-help'=>'Authors and publishers we support','how-it-works'=>'What happens after you get in touch?') as $id=>$label) {
            $block = gpc_content_block_bounds($content,function($t) use($id) { return preg_match('~\bid=["\x27]' . $id . '["\x27]~',$t); });
            if (!$block) { continue; }
            $intake .= '<details class="gp-reference-intake" id="' . $id . '"><summary>' . esc_html($label) . '</summary>' . substr($content,$block['inner_start'],$block['close']-$block['inner_start']) . '</details>';
            $content = substr_replace($content,'',$block['start'],$block['end']-$block['start']);
        }
        if ($intake) {
            $services = gpc_content_block_bounds($content,function($t) { return strpos($t,'data-service-explorer') !== false; });
            if ($services) { $content = substr_replace($content,'<div class="gp-reference-intake-row gp-wrap">' . $intake . '</div>',$services['close'],0); }
        }
        $content = gpc_reference_replace($content,function($t) { return gpc_tag_has_class($t,'gp-recommendations-home'); },gpc_reference_quote());
        // These decorative diagrams repeat article copy and are not project evidence.
        while (gpc_content_block_bounds($content,function($t) { return gpc_tag_has_class($t,'gp-editorial-art'); })) { $content = gpc_reference_replace($content,function($t) { return gpc_tag_has_class($t,'gp-editorial-art'); },''); }
    }
    if ($key === 'book-formatting') {
        $slot = gpc_content_block_bounds($content,function($t) { return strpos($t,'data-gpc-portfolio="formatting-"') !== false; });
        if ($slot) {
            $earlier = ''; $old = gpc_content_block_bounds($content,function($t) { return gpc_tag_has_class($t,'gp-formatting-portfolio'); });
            if ($old) { $earlier = substr($content,$old['start'],$old['end']-$old['start']); $content = substr_replace($content,'',$old['start'],$old['end']-$old['start']); }
            $content = gpc_reference_replace($content, function($t) { return strpos($t,'data-gpc-portfolio="formatting-"') !== false; }, gpc_interior_portfolio('formatting',$earlier));
        }
    }
    if ($key === 'case-studies') {
        $block = gpc_content_block_bounds($content,function($t) { return preg_match('~\bid=["\x27]client-design-projects["\x27]~',$t); });
        if ($block) {
            $filters = '<div class="gp-work-filter-surface" data-work-filters hidden><div class="gp-work-filter-bar gp-wrap"><p>Explore the design portfolio</p><div role="group" aria-label="Filter design portfolio examples">';
            foreach (array('all'=>'All examples','covers'=>'Cover design','formatting'=>'Interiors','aplus'=>'A+ Content','study'=>'Independent studies') as $value=>$label) { $filters .= '<button type="button" data-work-filter="' . $value . '" aria-pressed="' . ($value === 'all' ? 'true' : 'false') . '">' . $label . '</button>'; }
            $filters .= '</div><p class="gpc-sr-only" data-work-filter-status role="status" aria-live="polite"></p></div></div>';
            $content = substr_replace($content,$filters,$block['start'],0);
        }
    }
    if (!in_array($key,array('home','book-formatting','case-studies'),true)) { return $content; }
    return '<!-- data-gpc-reference-design -->' . $content;
}
function gpc_reference_selected() {
    $items = array(
        array('Luma the Sleepy Star','John Capon','COVER REDESIGN & INTERIOR FORMATTING','luma-final-front-734.webp',734,734,'case-luma'),
        array('My Dear Grandfather','Barsha Rai · Cactus Rain Publishing','AMAZON LISTING OPTIMIZATION','my-dear-grandfather-302.webp',302,466,'case-grandfather'),
        array('The Cognate Shortcut','VocabEasy.org · client project','BOOK COVER PRESENTATION','portfolio-cognate-shortcut-presentation.webp',1200,857,'case-studies'),
    );
    $html = '<section class="gp-section gp-reference-selected gp-theme-day" id="selected-work"><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">SELECTED WORK</p><div class="gp-heading"><h2>Real books.<br><em>Considered work.</em></h2></div></div><a class="gp-text-link" href="' . esc_url(gpc_url('case-studies')) . '">Explore the complete portfolio ' . gpc_direction_icon() . '</a></div><div class="gp-reference-work-grid">';
    foreach ($items as $i=>$p) { $url = gpc_url($p[6]) . ($i === 2 ? '#portfolio-cognate-shortcut' : ''); $html .= '<article class="gp-reference-work-card"><a class="gp-reference-work-image gp-reference-work-image-' . $i . '" href="' . esc_url($url) . '" aria-label="Explore ' . esc_attr($p[0]) . '"><img src="' . esc_url(gpc_asset_url($p[3])) . '" width="' . $p[4] . '" height="' . $p[5] . '" alt="' . esc_attr($p[0] . ($i === 1 ? ', book cover shown for the listing project' : ', project presentation')) . '" loading="lazy" decoding="async"></a><p class="gp-label">' . esc_html($p[2]) . '</p><h3><a href="' . esc_url($url) . '">' . esc_html($p[0]) . ' ' . gpc_direction_icon() . '</a></h3><p>' . esc_html($p[1]) . '</p></article>'; }
    return $html . '</div></div></section>';
}
function gpc_reference_quote() {
    foreach (gpc_recommendations() as $item) {
        if ($item['id'] !== 'cactus-rain-publishing') { continue; }
        return '<section class="gp-section gp-reference-quote gp-theme-day" id="client-recommendations"><div class="gp-wrap"><p class="gp-label">A CONTINUING WORKING RELATIONSHIP</p><blockquote>' . esc_html($item['quote']) . '</blockquote><p><strong>' . esc_html($item['reviewer']) . '</strong><br>' . esc_html($item['role']) . '</p><a class="gp-text-link" href="' . esc_url(gpc_url('feedback') . '#client-recommendations') . '">Read all the client recommendations ' . gpc_direction_icon() . '</a></div></section>';
    }
    return '';
}
add_filter('the_content', function($content) { return gpc_reference_content($content,gpc_journey_context()); },50);
add_filter('body_class', function($classes) { if (in_array('gpc-field-notes',$classes,true)) { $classes[] = 'gpc-reference-design'; } return $classes; },20);
