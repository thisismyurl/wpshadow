<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Edit_History_Analysis {
    public static function check() {
        return ['id' => 'seo-edit-history-analysis', 'title' => __('Edit History Analysis', 'wpshadow'), 'description' => __('Analyzes WordPress revision history. AI content shows zero revisions (dumped as-is). Genuine articles show iterative refinement, editor notes, multiple drafts.', 'wpshadow'), 'severity' => 'low', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/content-process/', 'training_link' => 'https://wpshadow.com/training/editorial-process/', 'auto_fixable' => false, 'threat_level' => 3];
    }
}
