<?php
declare(strict_types=1);
/**
 * Breadcrumb Schema Coverage Diagnostic
 *
 * Philosophy: Aid SERP breadcrumbs and navigation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Breadcrumb_Schema_Coverage extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-breadcrumb-schema-coverage',
            'title' => 'Breadcrumb Schema Coverage',
            'description' => 'Ensure BreadcrumbList structured data is present on major templates (posts, products, categories).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/breadcrumb-schema/',
            'training_link' => 'https://wpshadow.com/training/structured-data/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}