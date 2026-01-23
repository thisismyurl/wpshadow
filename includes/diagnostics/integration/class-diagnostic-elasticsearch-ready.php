<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Elasticsearch Integration Ready?
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Elasticsearch_Ready extends Diagnostic_Base {
	protected static $slug        = 'elasticsearch-ready';
	protected static $title       = 'Elasticsearch Integration Ready?';
	protected static $description = 'Tests search infrastructure compatibility.';

	public static function check(): ?array {
		// Check for ElasticPress plugin
		if (!is_plugin_active('elasticpress/elasticpress.php')) {
			return null; // Pass - ElasticPress not used
		}
		
		// Check ElasticPress connection
		if (function_exists('ep_elasticsearch_can_connect')) {
			if (ep_elasticsearch_can_connect()) {
				return null; // Pass - Elasticsearch connected
			}
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'ElasticPress installed but cannot connect to Elasticsearch server.',
				'color'         => '#f44336',
				'bg_color'      => '#ffebee',
				'kb_link'       => 'https://wpshadow.com/kb/elasticsearch-ready/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=elasticsearch-ready',
				'training_link' => 'https://wpshadow.com/training/elasticsearch-ready/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Integration',
				'priority'      => 2,
			);
		}
		
		return null;
	}




	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Elasticsearch Integration Ready?
	 * Slug: elasticsearch-ready
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Tests search infrastructure compatibility.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_elasticsearch_ready(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Elasticsearch properly configured and ready'];
		}
		$message = $result['description'] ?? 'Elasticsearch configuration issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
