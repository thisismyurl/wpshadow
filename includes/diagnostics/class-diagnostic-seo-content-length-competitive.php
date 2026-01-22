<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Content_Length_Competitive {
    public static function check() {
        return ['id' => 'seo-content-length-gap', 'title' => __('Content Length vs Competitors', 'wpshadow'), 'description' => __('Your content length vs top 10. If competitors all 3000+ words and you\'re 500, you\'re at disadvantage. Gap analysis shows undershooting vs overshooting.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/content-depth/', 'training_link' => 'https://wpshadow.com/training/long-form-content/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
