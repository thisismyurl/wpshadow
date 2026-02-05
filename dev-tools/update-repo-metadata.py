#!/usr/bin/env python3
"""
WPShadow Repository Metadata Updater via GitHub REST API

Updates descriptions and topics for all WPShadow repositories.

Usage:
    python3 dev-tools/update-repo-metadata.py [--token YOUR_TOKEN]

Date: February 4, 2026
"""

import json
import sys
import os
from urllib.request import Request, urlopen
from urllib.error import HTTPError

# Repository metadata
REPOS = {
    "wpshadow": {
        "description": "The Helpful Neighbor - WordPress health, security & performance diagnostics with auto-fixes. Free, accessible, privacy-first.",
        "topics": ["wordpress", "plugin", "diagnostics", "security", "performance", "health-check", "helpful-neighbor"]
    },
    "wpshadow-cloud": {
        "description": "WPShadow Cloud platform - SaaS backend for cloud diagnostics, external scanning, and multi-site management.",
        "topics": ["wordpress", "saas", "cloud", "diagnostics", "security-scanning", "multi-site"]
    },
    "theme-wpshadow": {
        "description": "Official companion theme for WPShadow - accessible, responsive, integrated dashboard widgets.",
        "topics": ["wordpress", "theme", "accessible", "wcag-aa", "wpshadow-companion"]
    },
    "wpshadow-pro": {
        "description": "WPShadow Pro platform - module manager and licensing system for pro add-ons.",
        "topics": ["wordpress", "plugin", "licensing", "module-manager", "wpshadow-pro"]
    },
    "wpshadow-pro-license": {
        "description": "WPShadow licensing system - activation, renewal, and entitlement management.",
        "topics": ["wordpress", "plugin", "licensing", "activation", "wpshadow-ecosystem"]
    },
    "wpshadow-pro-wpadmin": {
        "description": "WPAdmin Suite core - enhanced WordPress admin interface and unified experience across modules.",
        "topics": ["wordpress", "admin", "ui-enhancement", "wpshadow-pro"]
    },
    "wpshadow-pro-wpadmin-login": {
        "description": "WPAdmin Login - advanced login flow controls, security options, and branding.",
        "topics": ["wordpress", "login", "authentication", "security", "admin-interface"]
    },
    "wpshadow-pro-wpadmin-setting": {
        "description": "WPAdmin Settings - settings management UI and configuration controls.",
        "topics": ["wordpress", "settings", "admin", "configuration", "wpshadow-pro"]
    },
    "wpshadow-pro-wpadmin-content": {
        "description": "WPAdmin Content - content management and editorial tools.",
        "topics": ["wordpress", "content-management", "editorial", "admin", "wpshadow-pro"]
    },
    "wpshadow-pro-wpadmin-tool": {
        "description": "WPAdmin Tools - admin tool collection and utilities.",
        "topics": ["wordpress", "admin-tools", "utilities", "wpshadow-pro"]
    },
    "wpshadow-pro-wpadmin-theme": {
        "description": "WPAdmin Theme - theme configuration and style controls.",
        "topics": ["wordpress", "theme-management", "customization", "wpshadow-pro"]
    },
    "wpshadow-pro-multisite": {
        "description": "WPShadow Pro Multisite - network-wide management and site policies for WordPress Multisite.",
        "topics": ["wordpress", "multisite", "network-management", "wpshadow-pro"]
    },
    "wpshadow-pro-agency": {
        "description": "WPShadow Pro Agency - white-label, client management, and agency workflows.",
        "topics": ["wordpress", "agency", "white-label", "client-management", "wpshadow-pro"]
    },
    "wpshadow-pro-wpadmin-media": {
        "description": "Media Hub - universal media library, optimization, and transcoding infrastructure.",
        "topics": ["wordpress", "media", "media-management", "optimization", "wpshadow-pro"]
    },
    "wpshadow-pro-wpadmin-media-image": {
        "description": "Image Enhancement - advanced image filters, watermarking, and social optimization.",
        "topics": ["wordpress", "image-optimization", "watermark", "social-media", "content-creation"]
    },
    "wpshadow-pro-wpadmin-media-video": {
        "description": "Video Management - video editing, streaming optimization, and engagement features.",
        "topics": ["wordpress", "video", "video-editing", "streaming", "content-management"]
    },
    "wpshadow-pro-wpadmin-media-document": {
        "description": "Document Management - document preview, search, versioning, and collaboration.",
        "topics": ["wordpress", "document-management", "preview", "versioning", "collaboration"]
    },
    "wpshadow-pro-integration": {
        "description": "Design Integrations - Canva, Adobe Express, and Figma integration for seamless design workflow.",
        "topics": ["wordpress", "design", "integration", "canva", "adobe", "figma"]
    },
    "wpshadow-pro-vault": {
        "description": "WPShadow Vault - secure backup system with encryption, immutability, geo-redundancy, and disaster recovery.",
        "topics": ["wordpress", "backup", "disaster-recovery", "security", "encryption", "cloud-storage"]
    }
}

OWNER = "thisismyurl"


def get_token():
    """Get GitHub token from environment or argument."""
    token = os.environ.get("GH_TOKEN") or os.environ.get("GITHUB_TOKEN")
    
    if not token:
        print("Error: GitHub token not found. Set GH_TOKEN or GITHUB_TOKEN environment variable.")
        sys.exit(1)
    
    return token


def make_api_request(method, endpoint, token, data=None):
    """Make GitHub API request."""
    url = f"https://api.github.com{endpoint}"
    headers = {
        "Authorization": f"token {token}",
        "Accept": "application/vnd.github.v3+json",
        "X-GitHub-Api-Version": "2022-11-28",
    }
    
    if data:
        data = json.dumps(data).encode("utf-8")
        headers["Content-Type"] = "application/json"
    
    request = Request(url, data=data, headers=headers, method=method)
    
    try:
        with urlopen(request) as response:
            return response.status, json.loads(response.read().decode("utf-8"))
    except HTTPError as e:
        error_data = e.read().decode("utf-8")
        try:
            error_json = json.loads(error_data)
            return e.code, error_json
        except:
            return e.code, {"message": error_data}


def update_repository(repo_name, description, topics, token):
    """Update repository description and topics."""
    print(f"Updating: {repo_name}")
    
    endpoint = f"/repos/{OWNER}/{repo_name}"
    
    # Update description
    update_data = {"description": description}
    status, response = make_api_request("PATCH", endpoint, token, update_data)
    
    if status != 200:
        print(f"  ✗ Failed to update description")
        print(f"    Status: {status}")
        if "message" in response:
            print(f"    Error: {response['message']}")
        return False
    
    print(f"  ✓ Description updated")
    
    # Update topics
    topics_data = {"names": topics}
    status, response = make_api_request("PUT", f"{endpoint}/topics", token, topics_data)
    
    if status != 200:
        print(f"  ✗ Failed to update topics")
        print(f"    Status: {status}")
        if "message" in response:
            print(f"    Error: {response['message']}")
        return False
    
    print(f"  ✓ Topics added: {', '.join(topics)}")
    return True


def main():
    """Main function."""
    print("=" * 60)
    print("WPShadow Repository Metadata Updater")
    print("=" * 60)
    print()
    
    token = get_token()
    
    updated = 0
    failed = 0
    
    for repo_name, metadata in REPOS.items():
        if update_repository(repo_name, metadata["description"], metadata["topics"], token):
            updated += 1
        else:
            failed += 1
        print()
    
    print("=" * 60)
    print("Summary")
    print("=" * 60)
    print(f"Total repositories: {len(REPOS)}")
    print(f"Updated: {updated}")
    print(f"Failed: {failed}")
    print()
    
    if failed == 0:
        print("✅ All repositories updated successfully!")
        return 0
    else:
        print(f"⚠️  {failed} repositories failed to update")
        return 1


if __name__ == "__main__":
    sys.exit(main())
