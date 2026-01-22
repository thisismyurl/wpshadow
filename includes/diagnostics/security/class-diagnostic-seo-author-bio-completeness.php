<?php
declare(strict_types=1);
/**
 * Author Bio Completeness Diagnostic
 *
 * Philosophy: Author credentials establish expertise
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Author_Bio_Completeness extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-author-bio-completeness',
            'title' => 'Author Biography Completeness',
            'description' => 'Add detailed author bios with credentials, expertise, and social links to establish E-E-A-T.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/author-bios/',
            'training_link' => 'https://wpshadow.com/training/eeat-optimization/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
