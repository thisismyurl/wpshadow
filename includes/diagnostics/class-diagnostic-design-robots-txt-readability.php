<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Robots.txt Readability
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-robots-txt-readability
 * Training: https://wpshadow.com/training/design-robots-txt-readability
 */
class Diagnostic_Design_ROBOTS_TXT_READABILITY {
    public static function check() {
        return [
            'id' => 'design-robots-txt-readability',
            'title' => __('Robots.txt Readability', 'wpshadow'),
            'description' => __('Validates robots.txt serves properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-robots-txt-readability',
            'training_link' => 'https://wpshadow.com/training/design-robots-txt-readability',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
