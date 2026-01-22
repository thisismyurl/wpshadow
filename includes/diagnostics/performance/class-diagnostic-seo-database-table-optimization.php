<?php
declare(strict_types=1);
/**
 * Database Table Optimization Diagnostic
 *
 * Philosophy: Optimized tables improve query speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Database_Table_Optimization extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'seo-database-table-optimization',
			'title'         => 'Database Table Optimization',
			'description'   => 'Regularly optimize database tables to reduce overhead and improve query performance.',
			'severity'      => 'low',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/database-optimization/',
			'training_link' => 'https://wpshadow.com/training/database-maintenance/',
			'auto_fixable'  => false,
			'threat_level'  => 15,
		);
	}
}
