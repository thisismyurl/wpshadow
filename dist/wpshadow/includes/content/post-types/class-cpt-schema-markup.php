<?php
/**
 * Schema Markup for Custom Post Types
 *
 * Automatically generates and outputs Schema.org structured data
 * for better SEO and rich snippets in search results.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Schema_Markup Class
 *
 * Generates JSON-LD schema markup for all Custom Post Types.
 *
 * @since 0.6093.1200
 */
class CPT_Schema_Markup {

	/**
	 * Initialize schema markup system.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output_schema_markup' ), 1 );
	}

	/**
	 * Output schema markup for current post.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function output_schema_markup() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		$schema = self::get_schema_for_post( $post );

		if ( empty( $schema ) ) {
			return;
		}

		echo "\n" . '<!-- WPShadow Schema Markup -->' . "\n";
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "\n";
		echo '</script>' . "\n";
	}

	/**
	 * Get schema for a specific post.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data or empty array.
	 */
	public static function get_schema_for_post( $post ) {
		switch ( $post->post_type ) {
			case 'testimonial':
				return self::get_testimonial_schema( $post );
			case 'team_member':
				return self::get_team_member_schema( $post );
			case 'portfolio':
				return self::get_portfolio_schema( $post );
			case 'event':
				return self::get_event_schema( $post );
			case 'resource':
				return self::get_resource_schema( $post );
			case 'case_study':
				return self::get_case_study_schema( $post );
			case 'service':
				return self::get_service_schema( $post );
			case 'location':
				return self::get_location_schema( $post );
			case 'documentation':
				return self::get_documentation_schema( $post );
			case 'product':
				return self::get_product_schema( $post );
			default:
				return array();
		}
	}

	/**
	 * Get Review schema for testimonials.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_testimonial_schema( $post ) {
		$rating  = get_post_meta( $post->ID, '_wpshadow_rating', true );
		$company = get_post_meta( $post->ID, '_wpshadow_company', true );

		$schema = array(
			'@context'       => 'https://schema.org',
			'@type'          => 'Review',
			'@id'            => get_permalink( $post ),
			'reviewBody'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'datePublished'  => get_the_date( 'c', $post ),
			'author'         => array(
				'@type' => 'Person',
				'name'  => get_the_title( $post ),
			),
		);

		if ( $rating ) {
			$schema['reviewRating'] = array(
				'@type'       => 'Rating',
				'ratingValue' => $rating,
				'bestRating'  => '5',
				'worstRating' => '1',
			);
		}

		if ( $company ) {
			$schema['itemReviewed'] = array(
				'@type' => 'Organization',
				'name'  => $company,
			);
		}

		return $schema;
	}

	/**
	 * Get Person schema for team members.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_team_member_schema( $post ) {
		$job_title = get_post_meta( $post->ID, '_wpshadow_job_title', true );
		$email     = get_post_meta( $post->ID, '_wpshadow_email', true );
		$phone     = get_post_meta( $post->ID, '_wpshadow_phone', true );
		$linkedin  = get_post_meta( $post->ID, '_wpshadow_linkedin', true );
		$twitter   = get_post_meta( $post->ID, '_wpshadow_twitter', true );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Person',
			'@id'         => get_permalink( $post ),
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'         => get_permalink( $post ),
		);

		if ( $job_title ) {
			$schema['jobTitle'] = $job_title;
			$schema['worksFor'] = array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			);
		}

		if ( $email ) {
			$schema['email'] = $email;
		}

		if ( $phone ) {
			$schema['telephone'] = $phone;
		}

		// Add image if featured image exists.
		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = array(
				'@type' => 'ImageObject',
				'url'   => get_the_post_thumbnail_url( $post, 'full' ),
			);
		}

		// Add social media profiles.
		$same_as = array();
		if ( $linkedin ) {
			$same_as[] = $linkedin;
		}
		if ( $twitter ) {
			$same_as[] = $twitter;
		}
		if ( ! empty( $same_as ) ) {
			$schema['sameAs'] = $same_as;
		}

		return $schema;
	}

	/**
	 * Get CreativeWork schema for portfolio items.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_portfolio_schema( $post ) {
		$client      = get_post_meta( $post->ID, '_wpshadow_client', true );
		$project_url = get_post_meta( $post->ID, '_wpshadow_project_url', true );
		$year        = get_post_meta( $post->ID, '_wpshadow_year', true );

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'CreativeWork',
			'@id'           => get_permalink( $post ),
			'name'          => get_the_title( $post ),
			'description'   => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'           => get_permalink( $post ),
			'datePublished' => get_the_date( 'c', $post ),
			'creator'       => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		if ( $client ) {
			$schema['sponsor'] = array(
				'@type' => 'Organization',
				'name'  => $client,
			);
		}

		if ( $project_url ) {
			$schema['workExample'] = $project_url;
		}

		if ( $year ) {
			$schema['copyrightYear'] = $year;
		}

		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = array(
				'@type' => 'ImageObject',
				'url'   => get_the_post_thumbnail_url( $post, 'full' ),
			);
		}

		return $schema;
	}

	/**
	 * Get Event schema for events.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_event_schema( $post ) {
		$start_date  = get_post_meta( $post->ID, '_wpshadow_event_start_date', true );
		$end_date    = get_post_meta( $post->ID, '_wpshadow_event_end_date', true );
		$location    = get_post_meta( $post->ID, '_wpshadow_event_location', true );
		$registration = get_post_meta( $post->ID, '_wpshadow_event_registration_url', true );
		$is_virtual  = get_post_meta( $post->ID, '_wpshadow_event_is_virtual', true );
		$virtual_url = get_post_meta( $post->ID, '_wpshadow_event_virtual_url', true );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Event',
			'@id'         => get_permalink( $post ),
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'         => get_permalink( $post ),
			'organizer'   => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url(),
			),
		);

		// Start date (required).
		if ( $start_date ) {
			$schema['startDate'] = gmdate( 'c', strtotime( $start_date ) );
		}

		// End date (optional).
		if ( $end_date ) {
			$schema['endDate'] = gmdate( 'c', strtotime( $end_date ) );
		}

		// Event status.
		$schema['eventStatus'] = 'https://schema.org/EventScheduled';

		// Event attendance mode.
		if ( $is_virtual ) {
			$schema['eventAttendanceMode'] = 'https://schema.org/OnlineEventAttendanceMode';
			if ( $virtual_url ) {
				$schema['location'] = array(
					'@type' => 'VirtualLocation',
					'url'   => $virtual_url,
				);
			}
		} else {
			$schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
			if ( $location ) {
				$schema['location'] = array(
					'@type'   => 'Place',
					'name'    => $location,
					'address' => array(
						'@type'           => 'PostalAddress',
						'addressLocality' => $location,
					),
				);
			}
		}

		// Registration/tickets.
		if ( $registration ) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'url'   => $registration,
				'availability' => 'https://schema.org/InStock',
			);
		}

		// Image.
		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = array(
				'@type' => 'ImageObject',
				'url'   => get_the_post_thumbnail_url( $post, 'full' ),
			);
		}

		return $schema;
	}

	/**
	 * Get DigitalDocument schema for resources.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_resource_schema( $post ) {
		$file_url    = get_post_meta( $post->ID, '_wpshadow_resource_file_url', true );
		$file_format = get_post_meta( $post->ID, '_wpshadow_resource_file_format', true );
		$file_size   = get_post_meta( $post->ID, '_wpshadow_resource_file_size', true );

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'DigitalDocument',
			'@id'           => get_permalink( $post ),
			'name'          => get_the_title( $post ),
			'description'   => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'           => get_permalink( $post ),
			'datePublished' => get_the_date( 'c', $post ),
			'author'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		if ( $file_url ) {
			$schema['contentUrl'] = $file_url;
		}

		if ( $file_format ) {
			$schema['encodingFormat'] = 'application/' . strtolower( $file_format );
		}

		return $schema;
	}

	/**
	 * Get Article schema for case studies.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_case_study_schema( $post ) {
		$client = get_post_meta( $post->ID, '_wpshadow_case_study_client', true );

		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Article',
			'@id'              => get_permalink( $post ),
			'headline'         => get_the_title( $post ),
			'description'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'articleSection'   => 'Case Study',
			'author'           => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => get_site_icon_url(),
				),
			),
		);

		if ( $client ) {
			$schema['about'] = array(
				'@type' => 'Organization',
				'name'  => $client,
			);
		}

		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = array(
				'@type' => 'ImageObject',
				'url'   => get_the_post_thumbnail_url( $post, 'full' ),
			);
		}

		return $schema;
	}

	/**
	 * Get Service schema for services.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_service_schema( $post ) {
		$price    = get_post_meta( $post->ID, '_wpshadow_service_price', true );
		$duration = get_post_meta( $post->ID, '_wpshadow_service_duration', true );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Service',
			'@id'         => get_permalink( $post ),
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'         => get_permalink( $post ),
			'provider'    => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url(),
			),
		);

		// Service type from category.
		$categories = get_the_terms( $post, 'service_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$schema['serviceType'] = $categories[0]->name;
		}

		// Offers (pricing).
		if ( $price ) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'price' => preg_replace( '/[^0-9.]/', '', $price ),
				'priceCurrency' => 'USD',
			);
		}

		// Area served.
		$schema['areaServed'] = array(
			'@type' => 'Country',
			'name'  => 'Worldwide',
		);

		return $schema;
	}

	/**
	 * Get LocalBusiness schema for locations.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_location_schema( $post ) {
		$address   = get_post_meta( $post->ID, '_wpshadow_location_address', true );
		$city      = get_post_meta( $post->ID, '_wpshadow_location_city', true );
		$state     = get_post_meta( $post->ID, '_wpshadow_location_state', true );
		$zip       = get_post_meta( $post->ID, '_wpshadow_location_zip', true );
		$country   = get_post_meta( $post->ID, '_wpshadow_location_country', true );
		$phone     = get_post_meta( $post->ID, '_wpshadow_location_phone', true );
		$email     = get_post_meta( $post->ID, '_wpshadow_location_email', true );
		$hours     = get_post_meta( $post->ID, '_wpshadow_location_hours', true );
		$latitude  = get_post_meta( $post->ID, '_wpshadow_location_latitude', true );
		$longitude = get_post_meta( $post->ID, '_wpshadow_location_longitude', true );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'LocalBusiness',
			'@id'         => get_permalink( $post ),
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'         => get_permalink( $post ),
		);

		// Address.
		if ( $address || $city || $state || $zip || $country ) {
			$schema['address'] = array(
				'@type' => 'PostalAddress',
			);
			if ( $address ) {
				$schema['address']['streetAddress'] = $address;
			}
			if ( $city ) {
				$schema['address']['addressLocality'] = $city;
			}
			if ( $state ) {
				$schema['address']['addressRegion'] = $state;
			}
			if ( $zip ) {
				$schema['address']['postalCode'] = $zip;
			}
			if ( $country ) {
				$schema['address']['addressCountry'] = $country;
			}
		}

		// Contact.
		if ( $phone ) {
			$schema['telephone'] = $phone;
		}
		if ( $email ) {
			$schema['email'] = $email;
		}

		// Coordinates.
		if ( $latitude && $longitude ) {
			$schema['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => $latitude,
				'longitude' => $longitude,
			);
		}

		// Opening hours.
		if ( $hours ) {
			$schema['openingHours'] = explode( "\n", $hours );
		}

		// Image.
		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = array(
				'@type' => 'ImageObject',
				'url'   => get_the_post_thumbnail_url( $post, 'full' ),
			);
		}

		return $schema;
	}

	/**
	 * Get TechArticle schema for documentation.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_documentation_schema( $post ) {
		$version    = get_post_meta( $post->ID, '_wpshadow_doc_version', true );
		$difficulty = get_post_meta( $post->ID, '_wpshadow_doc_difficulty', true );

		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'TechArticle',
			'@id'              => get_permalink( $post ),
			'headline'         => get_the_title( $post ),
			'description'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => get_site_icon_url(),
				),
			),
		);

		if ( $difficulty ) {
			$schema['proficiencyLevel'] = ucfirst( $difficulty );
		}

		// Dependencies/prerequisites from categories.
		$categories = get_the_terms( $post, 'documentation_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$schema['articleSection'] = $categories[0]->name;
		}

		return $schema;
	}

	/**
	 * Get Product schema for products.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Post object.
	 * @return array Schema data.
	 */
	private static function get_product_schema( $post ) {
		$sku        = get_post_meta( $post->ID, '_wpshadow_product_sku', true );
		$price      = get_post_meta( $post->ID, '_wpshadow_product_price', true );
		$sale_price = get_post_meta( $post->ID, '_wpshadow_product_sale_price', true );
		$in_stock   = get_post_meta( $post->ID, '_wpshadow_product_in_stock', true );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'@id'         => get_permalink( $post ),
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'         => get_permalink( $post ),
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		if ( $sku ) {
			$schema['sku'] = $sku;
		}

		// Image.
		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = array(
				'@type' => 'ImageObject',
				'url'   => get_the_post_thumbnail_url( $post, 'full' ),
			);
		}

		// Offers.
		if ( $price ) {
			$offer_price = $sale_price ? $sale_price : $price;
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $offer_price,
				'priceCurrency' => 'USD',
				'availability'  => $in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'url'           => get_permalink( $post ),
			);

			if ( $sale_price ) {
				$schema['offers']['priceValidUntil'] = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
			}
		}

		// Category.
		$categories = get_the_terms( $post, 'product_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$schema['category'] = $categories[0]->name;
		}

		return $schema;
	}

	/**
	 * Get schema for a list of posts (for archive pages).
	 *
	 * @since 0.6093.1200
	 * @param  array  $posts Array of WP_Post objects.
	 * @param  string $type  Post type.
	 * @return array Schema data.
	 */
	public static function get_list_schema( $posts, $type ) {
		if ( empty( $posts ) ) {
			return array();
		}

		$items = array();
		foreach ( $posts as $post ) {
			$item_schema = self::get_schema_for_post( $post );
			if ( ! empty( $item_schema ) ) {
				$items[] = $item_schema;
			}
		}

		if ( empty( $items ) ) {
			return array();
		}

		return array(
			'@context' => 'https://schema.org',
			'@type'    => 'ItemList',
			'itemListElement' => array_map(
				function( $item, $index ) {
					return array(
						'@type'    => 'ListItem',
						'position' => $index + 1,
						'item'     => $item,
					);
				},
				$items,
				array_keys( $items )
			),
		);
	}
}
