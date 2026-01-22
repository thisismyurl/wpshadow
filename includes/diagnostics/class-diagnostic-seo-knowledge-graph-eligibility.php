<?php declare(strict_types=1);
/**
 * Knowledge Graph Eligibility Diagnostic
 *
 * Philosophy: Knowledge Graph establishes authority
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Knowledge_Graph_Eligibility {
    public static function check() {
        return [
            'id' => 'seo-knowledge-graph-eligibility',
            'title' => 'Knowledge Graph Qualification',
            'description' => 'Build entity recognition: consistent NAP, Wikipedia presence, Wikidata, social profiles.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/knowledge-graph/',
            'training_link' => 'https://wpshadow.com/training/entity-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
