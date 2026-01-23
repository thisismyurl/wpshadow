<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Css extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-css_file_optimization',
			'title'         => __( 'CSS File Optimization', 'wpshadow' ),
			'description'   => __( 'Verifies CSS minified, compressed. Unoptimized = slower rendering.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/',
			'training_link' => 'https://wpshadow.com/training/',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

}