<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Apple News Format
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-apple-news-format
 * Training: https://wpshadow.com/training/design-apple-news-format
 */
class Diagnostic_Design_APPLE_NEWS_FORMAT {
    public static function check() {
        return [
            'id' => 'design-apple-news-format',
            'title' => __('Apple News Format', 'wpshadow'),
            'description' => __('If using Apple News, validates formatting.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-apple-news-format',
            'training_link' => 'https://wpshadow.com/training/design-apple-news-format',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
