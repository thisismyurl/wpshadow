<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Image Optimization
 *
 * Analyzes image sizes and identifies optimization opportunities.
 * Unoptimized images significantly increase page load times and bandwidth usage.
 *
 * @since 1.2.0
 */
class Test_Image_Optimization extends Diagnostic_Base {


	/**
	 * Check image optimization status
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$optimization = self::analyze_image_optimization();

		if ( $optimization['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $optimization['threat_level'],
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => $optimization['issue'],
			'metadata'      => $optimization,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-image-optimization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-image-performance/',
		);
	}

	/**
	 * Guardian Sub-Test: Image optimization plugin
	 *
	 * @return array Test result
	 */
	public static function test_image_optimization_plugin(): array {
		$active_plugins = get_plugins();

		$optimization_plugins = array(
			'imagify/imagify.php'                          => 'Imagify',
			'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel',
			'smush/wp-smush.php'                           => 'Smush',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW',
		);

		$has_plugin = false;
		foreach ( $optimization_plugins as $plugin_file => $plugin_name ) {
			if ( isset( $active_plugins[ $plugin_file ] ) ) {
				$has_plugin = true;
				break;
			}
		}

		return array(
			'test_name'   => 'Image Optimization Plugin',
			'has_plugin'  => $has_plugin,
			'passed'      => $has_plugin,
			'description' => $has_plugin ? 'Image optimization plugin active' : 'No image optimization plugin installed',
		);
	}

	/**
	 * Guardian Sub-Test: Attachment count
	 *
	 * @return array Test result
	 */
	public static function test_attachment_count(): array {
		$attachment_count = wp_count_posts( 'attachment' )->publish ?? 0;

		$status = 'normal';
		if ( $attachment_count > 1000 ) {
			$status = 'high';
		} elseif ( $attachment_count > 500 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'        => 'Attachment Count',
			'attachment_count' => $attachment_count,
			'status'           => $status,
			'passed'           => $status === 'normal',
			'description'      => sprintf( '%d media files uploaded', $attachment_count ),
		);
	}

	/**
	 * Guardian Sub-Test: Responsive image sizes
	 *
	 * @return array Test result
	 */
	public static function test_responsive_images(): array {
		global $_wp_additional_image_sizes;

		$image_sizes = array_merge( get_intermediate_image_sizes(), array_keys( $_wp_additional_image_sizes ?? array() ) );

		$has_responsive = count( $image_sizes ) > 4; // More than default sizes

		return array(
			'test_name'   => 'Responsive Image Sizes',
			'image_sizes' => $image_sizes,
			'size_count'  => count( $image_sizes ),
			'passed'      => $has_responsive,
			'description' => sprintf( '%d image sizes configured', count( $image_sizes ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Uploads folder size
	 *
	 * @return array Test result
	 */
	public static function test_uploads_folder_size(): array {
		$upload_dir   = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];

		$size_bytes = self::get_directory_size( $uploads_path );
		$size_mb    = round( $size_bytes / ( 1024 * 1024 ), 2 );

		$status = 'normal';
		if ( $size_mb > 1000 ) {
			$status = 'large';
		} elseif ( $size_mb > 500 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'   => 'Uploads Folder Size',
			'size_bytes'  => $size_bytes,
			'size_mb'     => $size_mb,
			'status'      => $status,
			'passed'      => $status === 'normal',
			'description' => sprintf( 'Uploads folder: %.2f MB', $size_mb ),
		);
	}

	/**
	 * Get directory size recursively
	 *
	 * @param string $directory Directory path
	 * @return int Total size in bytes
	 */
	private static function get_directory_size( string $directory ): int {
		$size = 0;

		if ( is_dir( $directory ) ) {
			$files = scandir( $directory );

			foreach ( $files as $file ) {
				if ( $file === '.' || $file === '..' ) {
					continue;
				}

				$path = $directory . '/' . $file;

				if ( is_dir( $path ) ) {
					$size += self::get_directory_size( $path );
				} else {
					$size += filesize( $path );
				}
			}
		}

		return $size;
	}

	/**
	 * Analyze image optimization
	 *
	 * @return array Optimization analysis
	 */
	private static function analyze_image_optimization(): array {
		$active_plugins = get_plugins();

		$threat_level = 0;
		$issues       = array();

		// Check for optimization plugin
		$optimization_plugins = array(
			'imagify/imagify.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'smush/wp-smush.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
		);

		$has_plugin = false;
		foreach ( $optimization_plugins as $plugin_file ) {
			if ( isset( $active_plugins[ $plugin_file ] ) ) {
				$has_plugin = true;
				break;
			}
		}

		if ( ! $has_plugin ) {
			$issues[]     = 'No image optimization plugin installed';
			$threat_level = 25;
		}

		// Check uploads folder size
		$upload_dir = wp_upload_dir();
		$size_bytes = self::get_directory_size( $upload_dir['basedir'] );
		$size_mb    = round( $size_bytes / ( 1024 * 1024 ), 2 );

		if ( $size_mb > 1000 ) {
			$issues[]     = sprintf( 'Large uploads folder (%.2f MB)', $size_mb );
			$threat_level = max( $threat_level, 40 );
		}

		$issue = ! empty( $issues ) ? implode( '; ', $issues ) : 'Image optimization is configured';

		return array(
			'threat_level'    => $threat_level,
			'issue'           => $issue,
			'has_plugin'      => $has_plugin,
			'uploads_size_mb' => $size_mb,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Image Optimization';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Analyzes image sizes and identifies optimization opportunities';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Performance';
	}
}
