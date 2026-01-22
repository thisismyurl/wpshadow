<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Author_Consistency_Analysis {
    public static function check() {
        return ['id' => 'seo-author-consistency', 'title' => __('Author Voice Consistency', 'wpshadow'), 'description' => __('Analyzes writing style consistency across multiple articles by same author. AI-generated content from different prompts shows zero consistency. Real authors have distinct voice.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/author-authority/', 'training_link' => 'https://wpshadow.com/training/author-brand/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
