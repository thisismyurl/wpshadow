<?php
declare(strict_types=1);
/**
 * Citations and References Diagnostic
 *
 * Philosophy: Citations establish authority
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Citations_References extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-citations-references',
            'title' => 'Citations and External References',
            'description' => 'Link to authoritative sources and cite references to support claims and build trust.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/citations/',
            'training_link' => 'https://wpshadow.com/training/authoritative-content/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
