<?php
declare(strict_types=1);
/**
 * JobPosting Schema Diagnostic
 *
 * Philosophy: Job schema improves job search visibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_JobPosting_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-jobposting-schema',
            'title' => 'JobPosting Schema Markup',
            'description' => 'Add JobPosting schema for job listings: salary, location, employment type, qualifications.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/job-schema/',
            'training_link' => 'https://wpshadow.com/training/job-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }

}