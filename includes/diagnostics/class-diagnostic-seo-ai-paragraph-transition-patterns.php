<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_AI_Paragraph_Transition_Patterns {
    public static function check() {
        return ['id' => 'seo-ai-paragraph-transitions', 'title' => __('AI Paragraph Transition Patterns', 'wpshadow'), 'description' => __('AI models repeat transition phrases ("Furthermore", "In conclusion", "Moving on"). Human writers vary connectors naturally.', 'wpshadow'), 'severity' => 'low', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/natural-writing-flow/', 'training_link' => 'https://wpshadow.com/training/content-voice/', 'auto_fixable' => false, 'threat_level' => 3];
    }
}
