<?php
/**
 * Service JSON-LD schema markup output.
 *
 * Generates structured data for Google Rich Results and other search engines.
 *
 * @package WPShadow
 * @subpackage Content\Post_Types
 * @since 0.6096
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output Service schema markup for SEO and rich results.
 */
class Service_Schema_Output {

	/**
	 * Ensure hooks are only added once.
	 *
	 * @var bool
	 */
	private static $bootstrapped = false;

	/**
	 * Wire schema output hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$bootstrapped ) {
			return;
		}

		self::$bootstrapped = true;

		// Output schema on service singular pages.
		add_action( 'wp_head', array( __CLASS__, 'output_service_schema' ), 11 );
	}

	/**
	 * Output Service schema markup for current post.
	 *
	 * @return void
	 */
	public static function output_service_schema(): void {
		// Only output on service singular pages.
		if ( ! is_singular( 'service' ) ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$schema = self::build_service_schema( $post );

		if ( empty( $schema ) ) {
			return;
		}

		// Output with proper escaping and formatting.
		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		echo '</script>' . "\n";
	}

	/**
	 * Build Service schema structure for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array<string,mixed>
	 */
	private static function build_service_schema( \WP_Post $post ): array {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Service',
			'@id'      => get_permalink( $post ),
			'name'     => $post->post_title,
		);

		// Add description from post content or excerpt.
		$description = ! empty( $post->post_excerpt ) ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
		if ( ! empty( $description ) ) {
			$schema['description'] = substr( $description, 0, 160 ); // Keep it reasonable length.
		}

		// Add URL.
		$schema['url'] = get_permalink( $post );

		// Add image if featured image exists.
		$image_id = get_post_thumbnail_id( $post );
		if ( ! empty( $image_id ) ) {
			$image_url = wp_get_attachment_url( $image_id );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);

				// Add image dimensions if available.
				$image_meta = wp_get_attachment_metadata( $image_id );
				if ( ! empty( $image_meta['width'] ) && ! empty( $image_meta['height'] ) ) {
					$schema['image']['width']  = $image_meta['width'];
					$schema['image']['height'] = $image_meta['height'];
				}
			}
		}

		// Add service provider (organization).
		$provider = self::get_provider_schema();
		if ( ! empty( $provider ) ) {
			$schema['provider'] = $provider;
		}

		// Add service area from location taxonomy.
		$areas = self::get_service_areas( $post );
		if ( ! empty( $areas ) ) {
			if ( count( $areas ) === 1 ) {
				$schema['areaServed'] = $areas[0];
			} else {
				$schema['areaServed'] = $areas;
			}
		}

		// Add service category from service_category taxonomy.
		$categories = self::get_service_categories( $post );
		if ( ! empty( $categories ) ) {
			if ( count( $categories ) === 1 ) {
				$schema['serviceType'] = $categories[0];
			} else {
				$schema['serviceType'] = $categories;
			}
		}

		// Add pricing information.
		$pricing = self::get_pricing_schema( $post );
		if ( ! empty( $pricing ) ) {
			$schema['offers'] = $pricing;
		}

		// Add duration if available.
		$duration = self::get_duration_schema( $post );
		if ( ! empty( $duration ) ) {
			$schema['processingTime'] = $duration;
		}

		/**
		 * Filter Service schema data.
		 *
		 * @param array<string,mixed> $schema The schema array.
		 * @param \WP_Post            $post   Post object.
		 */
		return apply_filters( 'wpshadow_service_schema', $schema, $post );
	}

	/**
	 * Get provider (organization) schema.
	 *
	 * @return array<string,mixed>|null
	 */
	private static function get_provider_schema(): ?array {
		$blog_name = get_bloginfo( 'name' );
		$blog_url  = get_home_url();
		$blog_desc = get_bloginfo( 'description' );

		if ( empty( $blog_name ) ) {
			return null;
		}

		$provider = array(
			'@type' => 'Organization',
			'name'  => $blog_name,
			'url'   => $blog_url,
		);

		// Add organization description if available.
		if ( ! empty( $blog_desc ) ) {
			$provider['description'] = $blog_desc;
		}

		// Add logo if site icon exists.
		$logo_id = get_option( 'site_icon' );
		if ( ! empty( $logo_id ) ) {
			$logo_url = wp_get_attachment_url( $logo_id );
			if ( $logo_url ) {
				$provider['logo'] = array(
					'@type' => 'ImageObject',
					'url'   => $logo_url,
				);
			}
		}

		return $provider;
	}

	/**
	 * Get service area locations from location taxonomy.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array<int,string>
	 */
	private static function get_service_areas( \WP_Post $post ): array {
		$areas  = array();
		$terms  = get_the_terms( $post, 'location' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return $areas;
		}

		foreach ( $terms as $term ) {
			if ( ! empty( $term->name ) ) {
				$areas[] = $term->name;
			}
		}

		return array_values( array_unique( $areas ) );
	}

	/**
	 * Get service categories from service_category taxonomy.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array<int,string>
	 */
	private static function get_service_categories( \WP_Post $post ): array {
		$categories = array();
		$terms      = get_the_terms( $post, 'service_category' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return $categories;
		}

		foreach ( $terms as $term ) {
			if ( ! empty( $term->name ) ) {
				$categories[] = $term->name;
			}
		}

		return array_values( array_unique( $categories ) );
	}

	/**
	 * Get pricing schema for Service.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array<string,mixed>|null
	 */
	private static function get_pricing_schema( \WP_Post $post ): ?array {
		// Check if Service_Meta_Fields exists and call it.
		if ( ! class_exists( __NAMESPACE__ . '\\Service_Meta_Fields', false ) ) {
			return null;
		}

		$pricing = Service_Meta_Fields::get_pricing( $post->ID );
		if ( empty( $pricing ) ) {
			return null;
		}

		$currency = $pricing['currency'] ?? 'USD';
		$offer    = array(
			'@type'    => 'Offer',
			'priceCurrency' => $currency,
		);

		// Add pricing information based on available data.
		if ( ! empty( $pricing['base_price'] ) ) {
			$offer['price'] = (string) $pricing['base_price'];
		} elseif ( ! empty( $pricing['min_price'] ) && ! empty( $pricing['max_price'] ) ) {
			$offer['priceCurrency'] = $currency;
			$offer['lowPrice']       = (string) $pricing['min_price'];
			$offer['highPrice']      = (string) $pricing['max_price'];
		}

		// Add tier information if available.
		if ( ! empty( $pricing['tiers'] ) && is_array( $pricing['tiers'] ) ) {
			$offers = array();
			foreach ( $pricing['tiers'] as $tier ) {
				if ( ! empty( $tier['price'] ) ) {
					$offers[] = array(
						'@type'           => 'Offer',
						'name'            => $tier['name'] ?? 'Service Package',
						'price'           => (string) $tier['price'],
						'priceCurrency'   => $currency,
						'description'     => $tier['description'] ?? '',
					);
				}
			}
			return ! empty( $offers ) ? $offers : $offer;
		}

		return ! empty( $offer['price'] ) || ! empty( $offer['lowPrice'] ) ? $offer : null;
	}

	/**
	 * Get duration schema for Service.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string|null
	 */
	private static function get_duration_schema( \WP_Post $post ): ?string {
		// Check if Service_Meta_Fields exists and call it.
		if ( ! class_exists( __NAMESPACE__ . '\\Service_Meta_Fields', false ) ) {
			return null;
		}

		$duration = Service_Meta_Fields::get_duration( $post->ID );
		if ( empty( $duration ) || empty( $duration['value'] ) ) {
			return null;
		}

		$value = (int) $duration['value'];
		$unit  = strtoupper( $duration['unit'] ?? 'DAYS' );

		// Convert to ISO 8601 duration format: P[n]D, P[n]W, P[n]M, P[n]Y.
		$unit_map = array(
			'DAYS'   => 'D',
			'WEEKS'  => 'W',
			'MONTHS' => 'M',
			'YEARS'  => 'Y',
			'HOURS'  => 'H',
		);

		$iso_unit = $unit_map[ $unit ] ?? 'D';
		return sprintf( 'P%d%s', $value, $iso_unit );
	}
}
