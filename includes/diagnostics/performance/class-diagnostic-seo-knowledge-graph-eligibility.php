<?php
declare(strict_types=1);
/**
 * Knowledge Graph Eligibility Diagnostic
 *
 * Philosophy: Knowledge Graph establishes authority
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Knowledge_Graph_Eligibility extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'seo-knowledge-graph-eligibility',
			'title'         => 'Knowledge Graph Qualification',
			'description'   => 'Build entity recognition: consistent NAP, Wikipedia presence, Wikidata, social profiles.',
			'severity'      => 'low',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/knowledge-graph/',
			'training_link' => 'https://wpshadow.com/training/entity-seo/',
			'auto_fixable'  => false,
			'threat_level'  => 20,
		);
	}
}
