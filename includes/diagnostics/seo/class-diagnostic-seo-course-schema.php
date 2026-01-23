<?php
declare(strict_types=1);
/**
 * Course Schema Diagnostic
 *
 * Philosophy: Course schema for educational content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Course_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-course-schema',
            'title' => 'Course Schema Markup',
            'description' => 'Add Course schema for educational content: provider, duration, offers, reviews.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/course-schema/',
            'training_link' => 'https://wpshadow.com/training/education-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}