<?php
if (!defined('ABSPATH')) { exit; }

/** Owner-approved copy corrections. URLs, field values and client quotations are protected. */
function gpc_us_english($text) {
    if (!is_string($text)) { return $text; }
    $words = array('enquiry'=>'inquiry','enquiries'=>'inquiries','enquire'=>'inquire','enquired'=>'inquired','enquiring'=>'inquiring','optimisation'=>'optimization','optimisations'=>'optimizations','optimise'=>'optimize','optimised'=>'optimized','optimises'=>'optimizes','optimising'=>'optimizing','recognise'=>'recognize','recognised'=>'recognized','recognises'=>'recognizes','recognising'=>'recognizing','organisation'=>'organization','organisations'=>'organizations','organise'=>'organize','organised'=>'organized','organising'=>'organizing','prioritise'=>'prioritize','prioritised'=>'prioritized','prioritising'=>'prioritizing','authorise'=>'authorize','authorised'=>'authorized','authorisation'=>'authorization','colour'=>'color','colours'=>'colors','behaviour'=>'behavior','behaviours'=>'behaviors','centre'=>'center','centres'=>'centers','centred'=>'centered','analyse'=>'analyze','analysed'=>'analyzed','analysing'=>'analyzing','personalise'=>'personalize','personalised'=>'personalized','specialise'=>'specialize','specialised'=>'specialized','specialising'=>'specializing','catalogue'=>'catalog','catalogues'=>'catalogs','programme'=>'program','programmes'=>'programs','licence'=>'license','licences'=>'licenses','labelled'=>'labeled','labelling'=>'labeling');
    return preg_replace_callback('/\b(' . implode('|', array_keys($words)) . ')\b/i', function($match) use($words) {
        $replacement = $words[strtolower($match[0])];
        if ($match[0] === strtoupper($match[0])) { return strtoupper($replacement); }
        return ctype_upper(substr($match[0], 0, 1)) ? ucfirst($replacement) : $replacement;
    }, $text);
}
function gpc_personal_copy($text) {
    $text = strtr($text, array(
        'A book from our client. Grant’s work included an Amazon assessment and recommendations.'=>'A book from my client. My work included an Amazon assessment and recommendations.',
        'a result we can attribute to our work'=>'a result I can attribute to my work',
        'We distinguish what we can observe from what still needs research.'=>'I distinguish what I can observe from what still needs research.',
        'We distinguish what we can observe'=>'I distinguish what I can observe',
        'We do not present'=>'I do not present',
        'We first identify'=>'I first identify',
        'We confirm the relevant marketplace, formats, communication arrangements and project scope together.'=>'I confirm the relevant marketplace, formats, communication arrangements and project scope with you.',
        'We can review'=>'I can review',
        'we may also need'=>'I may also need',
        'we may request'=>'I may request',
        'so we can respond'=>'so I can respond',
        'outside our control'=>'outside my control',
        'How we approach the work'=>'How I approach the work',
        'HOW WE WORK'=>'HOW I WORK',
        'What we can work on'=>'What I can work on',
        'START WITH WHAT WE CAN SEE'=>'START WITH WHAT I CAN SEE',
        'BEFORE WE BEGIN'=>'BEFORE I BEGIN',
        'how we agree the work'=>'how I plan the work with you',
        'Tell us'=>'Tell me', 'tell us'=>'tell me', 'help us'=>'help me', 'helps us'=>'helps me',
    ));
    foreach (array('review','confirm','help','work','plan','consider','clarify','connect','examine','develop','prepare','provide','start','set up') as $verb) {
        $text = str_replace(array('We ' . $verb, 'we ' . $verb), 'I ' . $verb, $text);
    }
    $text = str_replace(array('We will','we will'), 'I will', $text);
    return str_replace(array('We agree','we agree'), 'I confirm', $text);
}
/** Only text and human-readable attributes change; no DOM reserialization. */
function gpc_copy_review_text($html, $personal = false) {
    if (!is_string($html)) { return $html; }
    $protected = array();
    $html = preg_replace_callback('~<!--.*?-->|<(blockquote|q|script|style|code|pre|textarea)\b[^>]*>.*?</\1\s*>~is', function($match) use(&$protected) {
        $token = 'GPCPROTECTED' . count($protected) . 'END'; $protected[$token] = $match[0]; return $token;
    }, $html);
    $parts = preg_split('~(<(?:[^>"\x27]|"[^"]*"|\x27[^\x27]*\x27)*>)~s', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as &$part) {
        if ($part === '' || isset($protected[$part])) { continue; }
        if (substr($part, 0, 1) === '<') {
            $part = preg_replace_callback('~(?<!\S)(alt|title|aria-label)\s*=\s*(["\x27])(.*?)\2~is', function($match) {
                $value = gpc_us_english($match[3]);
                $value = preg_replace('/\bAbdulQudus(?:\s+Tella)?\b/i', 'AbdulQudus Tella', $value);
                return $match[1] . '=' . $match[2] . $value . $match[2];
            }, $part);
        } else {
            $part = gpc_us_english($part);
            if ($personal) { $part = gpc_personal_copy($part); }
            $part = preg_replace('/\bAbdulQudus(?:\s+Tella)?\b/i', 'AbdulQudus Tella', $part);
        }
    }
    unset($part);
    return strtr(implode('', $parts), $protected);
}
function gpc_home_author_path() {
    return gpc_resolve_string(file_get_contents(dirname(__DIR__) . '/partials/home-author-path.html'), true);
}
function gpc_review_home($html) {
    $hero = gpc_content_block_bounds($html, function($tag) { return gpc_tag_has_class($tag, 'gp-home-hero'); });
    if ($hero) {
        $inner = substr($html, $hero['inner_start'], $hero['close'] - $hero['inner_start']);
        $subline = 'I design your cover, format your book for print and Kindle, and set it up on Amazon so it looks professional and is easier for readers to find.';
        $inner = str_replace(array('From cover design and interior formatting to Amazon KDP publishing and discoverability, we help independent authors and publishers bring their books to market with confidence.', 'From cover design and interior formatting to Amazon KDP publishing and discoverability, I help independent authors and publishers bring their books to market with confidence.'), $subline, $inner);
        $inner = preg_replace_callback('~<a\b([^>]*)>(.*?)</a>~is', function($match) {
            if (gpc_stock_copy_text($match[2]) !== 'Discuss your project') { return $match[0]; }
            $attrs = preg_replace('~(?<!\S)href\s*=\s*(["\x27])[^"\x27]*\1~i', 'href="' . esc_url(add_query_arg('request', 'project', gpc_url('contact')) . '#gpc-enquiry') . '"', $match[1], 1);
            return '<a' . $attrs . '>' . str_replace('Discuss your project', 'Get a free quote', $match[2]) . '</a>';
        }, $inner);
        $caption = '5.0 / 5 from client reviews on <a class="gp-review-source-link" href="https://www.upwork.com/freelancers/abdulqudust" target="_blank" rel="noopener noreferrer">Upwork</a> and <a class="gp-review-source-link" href="https://www.linkedin.com/services/page/a29863343146852122/" target="_blank" rel="noopener noreferrer">LinkedIn</a>';
        $inner = str_replace('5.0 / 5 · Client feedback', $caption, $inner);
        if (strpos($inner, 'data-gpc-hero-assessment') === false && strpos(gpc_stock_copy_text($inner), 'Already published? Get a free book assessment') === false) {
            $actions = gpc_content_block_bounds($inner, function($tag) { return gpc_tag_has_class($tag, 'gp-actions'); });
            if ($actions) {
                $link = '<div class="gp-small gp-hero-assessment" data-gpc-hero-assessment><p><a href="' . esc_url(add_query_arg('request', 'assessment', gpc_url('enquiry')) . '#gpc-enquiry') . '">Already published? Get a free book assessment</a></p></div>';
                $inner = substr_replace($inner, $link, $actions['end'], 0);
            }
        }
        $html = substr_replace($html, $inner, $hero['inner_start'], $hero['close'] - $hero['inner_start']);
    }
    if (strpos($html, 'data-gpc-author-path') === false) {
        $services = gpc_content_block_bounds($html, function($tag) { return strpos($tag, 'data-gpc-service-overview') !== false; });
        if ($services) { $html = substr_replace($html, gpc_home_author_path(), $services['end'], 0); }
    }
    // Transplant the existing, attributed Kathryn's Beach quote without editing it.
    $proof = gpc_content_block_bounds($html, function($tag) { return gpc_tag_has_class($tag, 'gp-proof-grid'); });
    if ($proof) {
        $row = substr($html, $proof['start'], $proof['end'] - $proof['start']);
        if (strpos($row, 'Kathryn') !== false && strpos(gpc_review_match_text($row), gpc_review_match_text(gpc_publishing_review())) !== false) {
            $row = preg_replace_callback('~<blockquote\b[^>]*>.*?</blockquote>~is', function() { return gpc_kathryns_review_markup(); }, $row, 1);
            $html = substr_replace($html, $row, $proof['start'], $proof['end'] - $proof['start']);
        }
    }
    return $html;
}
function gpc_service_groups() {
    return array(
        'Book Design and Production'=>array('book-formatting','cover-design'),
        'Publishing and Launch Support'=>array('publishing-support','launch-promotion'),
        'Amazon Visibility and Optimization'=>array('amazon-visibility','book-presentation'),
        'Book Marketing and Growth'=>array('book-strategy','amazon-ads','author-platform','catalog-strategy'),
    );
}
function gpc_group_service_rows($html) {
    if (strpos($html, 'data-gpc-service-groups') !== false) { return $html; }
    $list = gpc_content_block_bounds($html, function($tag) { return gpc_tag_has_class($tag, 'gp-service-list-full'); });
    if (!$list) { return $html; }
    $inner = substr($html, $list['inner_start'], $list['close'] - $list['inner_start']); $rows = array();
    $remaining = $inner;
    while ($block = gpc_content_block_bounds($remaining, function($tag) { return gpc_tag_has_class($tag, 'gp-service-row'); })) {
        $row = substr($remaining, $block['start'], $block['end'] - $block['start']);
        if (!preg_match('~(?:^|\s)id\s*=\s*(["\x27])([^"\x27]+)\1~i', substr($row, 0, strpos($row, '>') + 1), $id) || isset($rows[$id[2]])) { return $html; }
        $rows[$id[2]] = $row; $remaining = substr_replace($remaining, '', $block['start'], $block['end'] - $block['start']);
    }
    $keys = array_merge(...array_values(gpc_service_groups()));
    // Preserve custom layouts or additional content instead of guessing.
    if (count($rows) !== 10 || array_diff($keys, array_keys($rows)) || trim(wp_strip_all_tags($remaining)) !== '') { return $html; }
    $grouped = ''; $number = 0;
    foreach (gpc_service_groups() as $heading=>$services) {
        $grouped .= '<div class="gp-service-group" data-gpc-service-groups><div class="gp-subheading"><h2>' . esc_html($heading) . '</h2></div>';
        foreach ($services as $key) {
            $row = preg_replace('~<h2\b([^>]*)>(.*?)</h2>~is', '<h3$1>$2</h3>', $rows[$key]);
            $number++; $row = preg_replace('~(<(?:p|span)\b[^>]*>)\s*\d{2}\s*(</(?:p|span)>)~i', '${1}' . sprintf('%02d', $number) . '$2', $row, 1);
            $grouped .= $row;
        }
        $grouped .= '</div>';
    }
    return substr_replace($html, $grouped, $list['inner_start'], $list['close'] - $list['inner_start']);
}
function gpc_copy_review_markup($html, $key) {
    if (!is_string($html) || !isset(gpc_pages()[$key]) || in_array($key, array('privacy-policy','terms-of-service'), true)) { return $html; }
    if ($key === 'home') { $html = gpc_review_home($html); }
    if ($key === 'services') { $html = gpc_group_service_rows($html); }
    if (in_array($key, array('home','about'), true)) {
        while ($scope = gpc_content_block_bounds($html, function($tag) { return gpc_tag_has_class($tag, 'gp-framework-scope'); })) { $html = substr_replace($html, '', $scope['start'], $scope['end'] - $scope['start']); }
    }
    $personal = in_array($key, array_merge(array('home','about','services','contact','enquiry','faq','case-studies','case-grandfather','case-luma'), array_keys(gpc_service_connections())), true);
    return gpc_copy_review_text($html, $personal);
}
add_filter('the_content', function($content) { return gpc_copy_review_markup($content, gpc_journey_context()); }, 47);
