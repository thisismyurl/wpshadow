<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Post List Item Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-post-list-item-design
 * Training: https://wpshadow.com/training/design-post-list-item-design
 */
class Diagnostic_Design_POST_LIST_ITEM_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-post-list-item-design',
            'title' => __('Post List Item Design', 'wpshadow'),
            'description' => __('Checks post cards/list items styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-post-list-item-design',
            'training_link' => 'https://wpshadow.com/training/design-post-list-item-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
