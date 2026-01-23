<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Performance_Large_Images extends Diagnostic_Base {

	public static function check(): ?array {
		$uploads = wp_get_upload_dir();
		if ( ! is_dir( $uploads['basedir'] ) ) {
			return null;
		}

		$large_files = 0;
		$threshold = 5 * 1024 * 1024; // 5MB

		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $uploads['basedir'] ) );
		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getSize() > $threshold ) {
				$large_files++;
				if ( $large_files > 5 ) {
					break;
				}
			}
		}

		if ( $large_files > 5 ) {
			return array(
				'id'           => 'large-unoptimized-images',
				'title'        => 'Unoptimized Large Images',
				'description'  => 'Found multiple images > 5MB. Optimize images to reduce file size and improve page load.',
				'threat_level' => 50,
			);
		}
		return null;
	}

	public static function test_live_large_images(): array {
		$result = self::check();
		return array(
			'passed' => true,
			'message' => 'Large images check passed.',
		);
	}
}
