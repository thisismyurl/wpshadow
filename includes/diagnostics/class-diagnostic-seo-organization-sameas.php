<?php declare(strict_types=1);
/**
 * Organization sameAs Profiles Diagnostic
 *
 * Philosophy: Strengthen entity signals via sameAs links
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Organization_SameAs {
    /**
     * Advisory: ensure Organization/Person schema includes sameAs profile links.
     *
     * @return array|null
     */
    public static function check() {
        return [
            'id' => 'seo-organization-sameas',
            'title' => 'Add sameAs Social Profiles to Organization Schema',
            'description' => 'Ensure Organization (or Person) schema includes sameAs URLs for official social profiles to reinforce entity understanding.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/schema-sameas/',
            'training_link' => 'https://wpshadow.com/training/entity-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
