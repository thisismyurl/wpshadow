<?php
declare(strict_types=1);
/**
 * RSS/Atom Feed Availability Diagnostic
 *
 * Philosophy: Feeds enable content syndication
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_RSS_Atom_Feed_Availability extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-rss-atom-feed-availability',
            'title' => 'RSS/Atom Feed Configuration',
            'description' => 'Ensure RSS/Atom feeds are enabled and linked in HTML head for content discovery and syndication.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/rss-feeds/',
            'training_link' => 'https://wpshadow.com/training/content-syndication/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}