#!/bin/bash
#
# WPShadow Repository Metadata Updater
#
# Updates descriptions and topics for all WPShadow repositories.
# Uses GitHub CLI (gh) for updates.
#
# Usage: bash dev-tools/update-repo-metadata.sh
#
# Date: February 4, 2026
# Version: 1.6035.0948

set -e

OWNER="thisismyurl"

# Repository metadata: repo_name|description|topics
REPOS=(
    # Core + Cloud + Theme
    "wpshadow|The Helpful Neighbor - WordPress health, security & performance diagnostics with auto-fixes. Free, accessible, privacy-first.|wordpress,plugin,diagnostics,security,performance,health-check,helpful-neighbor"

    "wpshadow-cloud|WPShadow Cloud platform - SaaS backend for cloud diagnostics, external scanning, and multi-site management.|wordpress,saas,cloud,diagnostics,security-scanning,multi-site"

    "theme-wpshadow|Official companion theme for WPShadow - accessible, responsive, integrated dashboard widgets.|wordpress,theme,accessible,wcag-aa,wpshadow-companion"

    # Pro Platform + Licensing
    "wpshadow-pro|WPShadow Pro platform - module manager and licensing system for pro add-ons.|wordpress,plugin,licensing,module-manager,wpshadow-pro"

    "wpshadow-pro-license|WPShadow licensing system - activation, renewal, and entitlement management.|wordpress,plugin,licensing,activation,wpshadow-ecosystem"

    # WPAdmin Suite
    "wpshadow-pro-wpadmin|WPAdmin Suite core - enhanced WordPress admin interface and unified experience across modules.|wordpress,admin,ui-enhancement,wpshadow-pro"

    "wpshadow-pro-wpadmin-login|WPAdmin Login - advanced login flow controls, security options, and branding.|wordpress,login,authentication,security,admin-interface"

    "wpshadow-pro-wpadmin-setting|WPAdmin Settings - settings management UI and configuration controls.|wordpress,settings,admin,configuration,wpshadow-pro"

    "wpshadow-pro-wpadmin-content|WPAdmin Content - content management and editorial tools.|wordpress,content-management,editorial,admin,wpshadow-pro"

    "wpshadow-pro-wpadmin-tool|WPAdmin Tools - admin tool collection and utilities.|wordpress,admin-tools,utilities,wpshadow-pro"

    "wpshadow-pro-wpadmin-theme|WPAdmin Theme - theme configuration and style controls.|wordpress,theme-management,customization,wpshadow-pro"

    "wpshadow-pro-multisite|WPShadow Pro Multisite - network-wide management and site policies for WordPress Multisite.|wordpress,multisite,network-management,wpshadow-pro"

    "wpshadow-pro-agency|WPShadow Pro Agency - white-label, client management, and agency workflows.|wordpress,agency,white-label,client-management,wpshadow-pro"

    # Media Suite
    "wpshadow-pro-wpadmin-media|Media Hub - universal media library, optimization, and transcoding infrastructure.|wordpress,media,media-management,optimization,wpshadow-pro"

    "wpshadow-pro-wpadmin-media-image|Image Enhancement - advanced image filters, watermarking, and social optimization.|wordpress,image-optimization,watermark,social-media,content-creation"

    "wpshadow-pro-wpadmin-media-video|Video Management - video editing, streaming optimization, and engagement features.|wordpress,video,video-editing,streaming,content-management"

    "wpshadow-pro-wpadmin-media-document|Document Management - document preview, search, versioning, and collaboration.|wordpress,document-management,preview,versioning,collaboration"

    # Integration Suite
    "wpshadow-pro-integration|Design Integrations - Canva, Adobe Express, and Figma integration for seamless design workflow.|wordpress,design,integration,canva,adobe,figma"

    # Vault
    "wpshadow-pro-vault|WPShadow Vault - secure backup system with encryption, immutability, geo-redundancy, and disaster recovery.|wordpress,backup,disaster-recovery,security,encryption,cloud-storage"
)

echo "=========================================="
echo "WPShadow Repository Metadata Updater"
echo "=========================================="
echo ""

UPDATED=0
FAILED=0

for repo_entry in "${REPOS[@]}"; do
    IFS='|' read -r repo_name description topics <<< "$repo_entry"

    echo "Updating: $repo_name"

    # Update repository description
    if gh repo edit "$OWNER/$repo_name" \
        --description "$description" \
        2>/dev/null; then
        echo "  ✓ Description updated"
    else
        echo "  ✗ Failed to update description"
        FAILED=$((FAILED + 1))
        continue
    fi

    # Add topics (tags)
    # Convert pipe-separated topics to space-separated for gh command
    topic_array=(${topics//,/ })
    if gh repo edit "$OWNER/$repo_name" \
        --add-topic "${topic_array[@]}" \
        2>/dev/null; then
        echo "  ✓ Topics added: ${topics}"
    else
        echo "  ✗ Failed to add topics"
        FAILED=$((FAILED + 1))
        continue
    fi

    echo ""
    UPDATED=$((UPDATED + 1))
done

echo "=========================================="
echo "Summary"
echo "=========================================="
echo "Total repositories: ${#REPOS[@]}"
echo "Updated: $UPDATED"
echo "Failed: $FAILED"
echo ""

if [ $FAILED -eq 0 ]; then
    echo "✅ All repositories updated successfully!"
else
    echo "⚠️  $FAILED repositories failed to update"
fi
