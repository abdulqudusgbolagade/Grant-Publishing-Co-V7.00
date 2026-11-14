<?php
if (!defined('ABSPATH')) { exit; }

/** Owner-supplied recommendations. Missing ratings and project scopes stay absent. */
function gpc_recommendations() {
    static $items;
    if ($items === null) { $items = json_decode(file_get_contents(dirname(__DIR__) . '/client-recommendations.json'), true); }
    return is_array($items) ? $items : array();
}
function gpc_client_video() {
    static $video;
    if ($video === null) { $video = json_decode(file_get_contents(dirname(__DIR__) . '/client-video.json'), true); }
    return is_array($video) ? $video : array();
}
function gpc_client_video_feature($context = 'feedback') {
    $video = gpc_client_video();
    if (empty($video['url']) || empty($video['name'])) { return ''; }
    $html = '<section class="gp-section gp-video-review gp-tone-indigo" id="hilda-video-review" data-gpc-video-review><div class="gp-wrap gp-video-review-grid"><div class="gp-video-frame"><video controls playsinline preload="none" width="720" height="1280" poster="' . esc_url(gpc_asset_url($video['poster'])) . '" aria-label="Video testimonial from ' . esc_attr($video['name']) . '"><source src="' . esc_url($video['url']) . '" type="video/mp4"><track kind="captions" srclang="en" label="English (auto-transcribed)" src="' . esc_url(gpc_asset_url($video['captions'])) . '" default><p><a href="' . esc_url($video['url']) . '">Watch the original video from ' . esc_html($video['name']) . '</a>.</p></video></div><div class="gp-video-copy"><p class="gp-label">AUTHOR VIDEO FEEDBACK</p><div class="gp-heading"><h2>' . esc_html($video['name']) . '</h2></div><div class="gp-copy"><p>' . esc_html($video['context']) . '</p></div><p><a class="gp-text-link" href="' . esc_url($video['author_url']) . '" target="_blank" rel="noopener noreferrer">Explore Hilda’s books<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a></p><p><a class="gp-text-link" href="' . esc_url(gpc_url('publishing-support')) . '">Explore book publishing support ' . gpc_direction_icon() . '</a></p><p class="gp-small gp-video-caption-note">English captions are auto-transcribed. The original recording is unchanged.</p><details class="gp-video-transcript"><summary>Read the transcript (auto-transcribed)</summary><div class="gp-copy"><blockquote class="gp-video-transcript-quote">';
    foreach ($video['transcript'] ?? array() as $paragraph) { $html .= '<p>' . esc_html($paragraph) . '</p>'; }
    return $html . '</blockquote></div></details></div></div></section>';
}
function gpc_recommendation_card($item, $compact = false) {
    $html = '<article class="gp-recommendation-card" id="recommendation-' . esc_attr($item['id']) . '"><div class="gp-recommendation-top"><p class="gp-label">' . esc_html($item['label']) . '</p>';
    if (isset($item['rating']) && is_numeric($item['rating']) && $item['rating'] >= 0 && $item['rating'] <= 5) { $html .= '<p class="gp-recommendation-rating"><span aria-hidden="true">' . ((float) $item['rating'] === 5.0 ? '★★★★★' : '★') . '</span><span>' . esc_html(number_format((float) $item['rating'], 1)) . ' / 5</span></p>'; }
    $html .= '</div>';
    if (!empty($item['book'])) {
        $html .= '<div class="gp-recommendation-book"><a href="' . esc_url($item['book_url']) . '" target="_blank" rel="noopener noreferrer" aria-label="View ' . esc_attr($item['book']) . ' by ' . esc_attr($item['author']) . ' on Amazon (opens in a new tab)"><img src="' . esc_url(gpc_asset_url($item['image'])) . '" width="' . (int) $item['width'] . '" height="' . (int) $item['height'] . '" alt="' . esc_attr($item['book'] . ' by ' . $item['author'] . ', the book assessed for this project') . '" loading="lazy" decoding="async"></a><div><h3>' . esc_html($item['book']) . '</h3><p>By ' . esc_html($item['author']) . '</p><p class="gp-small">Amazon assessment and improvement recommendations</p></div></div>';
    }
    if (!empty($item['scope'])) { $html .= '<p class="gp-recommendation-context">' . esc_html($item['scope']) . '</p>'; }
    $html .= '<blockquote>' . esc_html($item['quote']) . '</blockquote><div class="gp-recommendation-credit"><p><strong>';
    $html .= !empty($item['reviewer_url']) ? '<a class="gp-review-source-link" href="' . esc_url($item['reviewer_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($item['reviewer']) . '</a>' : esc_html($item['reviewer']);
    $html .= '</strong><span>' . esc_html($item['role']) . '</span></p>';
    if (!empty($item['source_url'])) { $html .= '<a class="gp-recommendation-source gp-review-source-link" href="' . esc_url($item['source_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($item['source']) . '<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a>'; }
    if (!empty($item['author_books_url'])) { $html .= '<a class="gp-recommendation-source gp-review-source-link" href="' . esc_url($item['author_books_url']) . '" target="_blank" rel="noopener noreferrer">Explore the author’s books<span class="gpc-sr-only"> (opens in a new tab)</span> ' . gpc_direction_icon() . '</a>'; }
    return $html . '</div></article>';
}
function gpc_recommendation_section($context) {
    $items = gpc_recommendations(); $cards = '';
    foreach ($items as $item) {
        if ($context === 'home' && empty($item['home'])) { continue; }
        if ($context === 'work' && $item['id'] !== 'under-african-sun') { continue; }
        $cards .= gpc_recommendation_card($item, $context === 'home');
    }
    $heading = $context === 'work' ? 'Assessment work,<br>in the client’s words.' : ($context === 'home' ? 'Trusted with<br>the work.' : 'Recommendations,<br>with context.');
    $intro = $context === 'work' ? 'An Amazon assessment and improvement-recommendations project. This is distinct from cover design or publishing work.' : 'Client perspectives on the work and the working relationship. Several comments relate to continuing work with Cactus Rain Publishing and Nadine Laman.';
    $html = '<section class="gp-section gp-recommendations gp-recommendations-' . esc_attr($context) . ' gp-tone-lilac" id="client-recommendations" data-gpc-recommendations><div class="gp-wrap"><div class="gp-editorial-heading"><div><p class="gp-label">CLIENT PERSPECTIVES</p><div class="gp-heading"><h2>' . $heading . '</h2></div></div><div class="gp-copy"><p>' . esc_html($intro) . '</p></div></div><div class="gp-recommendation-grid">' . $cards . '</div>';
    if ($context !== 'feedback') { $html .= '<div class="gp-recommendations-next"><a class="gp-text-link" href="' . esc_url(gpc_url('feedback') . '#client-recommendations') . '">Read the client recommendations ' . gpc_direction_icon() . '</a>' . ($context === 'home' ? '<a class="gp-text-link" href="' . esc_url(gpc_url('feedback') . '#hilda-video-review') . '">Watch Hilda’s publishing experience ' . gpc_direction_icon() . '</a>' : '') . '</div>'; }
    return $html . '</div></section>';
}
/** Add evidence through the same connected-page architecture as existing proof. */
function gpc_recommendations_markup($content, $key) {
    if (!is_string($content) || !in_array($key, array('home','feedback','case-studies'), true) || strpos($content, 'data-gpc-recommendations') !== false) { return $content; }
    if ($key === 'feedback') {
        $hero = gpc_content_block_bounds($content, function($tag) { return gpc_tag_has_class($tag, 'gp-page-hero'); });
        if ($hero) { $content = substr_replace($content, gpc_client_video_feature() . gpc_recommendation_section('feedback'), $hero['end'], 0); }
        $content = str_replace('Five-star client feedback on book design, publishing support and marketing. Words from Nadine Laman and John Capon, presented with the project context.', 'Client feedback and recommendations on book design, publishing support and marketing, presented with the project context.', $content);
        return $content;
    }
    return gpc_insert_journey($content, gpc_recommendation_section($key === 'home' ? 'home' : 'work'));
}
add_filter('the_content', function($content) { return gpc_recommendations_markup($content, gpc_journey_context()); }, 46);
