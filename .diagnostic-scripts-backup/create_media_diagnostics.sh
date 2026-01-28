#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 60 WordPress Media Library Diagnostics ==="
echo ""

# CATEGORY 1: File Upload Issues (12 diagnostics)
echo "Creating File Upload Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Upload File Size Limit" --body "Checks if upload_max_filesize and post_max_size are properly configured. Detects limits that are too restrictive. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload Timeout Errors" --body "Monitors for upload timeouts during large file uploads. Tests max_execution_time settings. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload Directory Permissions" --body "Verifies wp-content/uploads directory has correct write permissions. Tests directory creation. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload Memory Exhaustion" --body "Detects memory limit issues during file uploads. Tests memory_limit vs file sizes. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Duplicate Filename Handling" --body "Tests how WordPress handles duplicate filenames during upload. Verifies filename sanitization. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload File Type Restrictions" --body "Validates allowed file types configuration. Checks for overly restrictive or insecure settings. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] HTTP Upload Errors" --body "Detects HTTP errors during upload process. Monitors for 413, 502, 504 errors. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Multipart Upload Failures" --body "Tests chunked/multipart upload functionality for large files. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload Progress Tracking" --body "Verifies upload progress bar works correctly. Tests JavaScript upload handlers. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Mobile Upload Compatibility" --body "Tests file uploads from mobile devices. Verifies camera/gallery access. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Drag-and-Drop Upload" --body "Validates drag-and-drop upload functionality in media library. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload Queue Management" --body "Tests multiple simultaneous uploads. Detects queue failures and race conditions. Threat: 50" && sleep 2

echo "✅ File Upload: 12 diagnostics"
sleep 5

# CATEGORY 2: Image Processing (10 diagnostics)
echo "Creating Image Processing Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Thumbnail Generation Failures" --body "Detects failed thumbnail generation for uploaded images. Tests GD/ImageMagick availability. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Size Registration" --body "Validates all registered image sizes are generated correctly. Tests add_image_size functionality. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Quality Settings" --body "Checks JPEG quality settings and compression levels. Balances quality vs file size. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Library Detection" --body "Detects which image library is active (GD vs ImageMagick). Tests library capabilities. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] WebP Support Detection" --body "Tests if server supports WebP image format. Checks conversion and display capability. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] AVIF Support Detection" --body "Tests if server supports AVIF image format. Checks modern format compatibility. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] SVG Upload Security" --body "Validates SVG upload security measures. Tests for malicious code in SVG files. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Rotation Issues" --body "Tests EXIF orientation handling. Detects images displaying sideways/upside-down. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Large Image Handling" --body "Tests handling of very large images (dimensions and file size). Detects memory issues. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Optimization Integration" --body "Checks if image optimization plugins are working correctly. Tests compression and format conversion. Threat: 45" && sleep 2

echo "✅ Image Processing: 10 diagnostics"
sleep 5

# CATEGORY 3: Media Library Performance (10 diagnostics)
echo "Creating Media Library Performance Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Media Library Load Time" --body "Measures time to load media library grid/list view. Detects performance issues with large libraries. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Query Performance" --body "Tests performance of media attachment queries. Detects slow database queries. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Library Pagination" --body "Validates pagination works correctly in media library. Tests with large media counts. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Search Performance" --body "Measures media library search speed. Tests search query optimization. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Filter Functionality" --body "Tests media library filters (date, type, uploaded by). Validates filter accuracy. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Thumbnail Loading Speed" --body "Measures thumbnail load time in media library. Detects lazy loading issues. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Modal Performance" --body "Tests performance of media picker modal in post editor. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Library Memory Usage" --body "Monitors memory consumption when browsing large media libraries. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Count Accuracy" --body "Verifies media count displays match actual database counts. Tests count queries. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Library Grid vs List" --body "Tests both grid and list views for performance differences. Threat: 40" && sleep 2

echo "✅ Media Library Performance: 10 diagnostics"
sleep 5

# CATEGORY 4: File Management & Storage (10 diagnostics)
echo "Creating File Management Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] File URL Accessibility" --body "Tests if uploaded media files are accessible via their URLs. Detects 404 errors. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] File Permission Security" --body "Validates file permissions are secure (not 777). Tests for permission vulnerabilities. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Storage Space Availability" --body "Checks available disk space in uploads directory. Warns before reaching limits. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Orphaned Media Detection" --body "Identifies media files not attached to any posts. Detects unused media bloat. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Missing Physical Files" --body "Detects media database entries with missing physical files. Tests file existence. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Backup Status" --body "Checks if media files are included in backup routines. Validates backup plugin coverage. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CDN Integration Status" --body "Tests if CDN is properly serving media files. Validates URL rewriting. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Year/Month Folder Organization" --body "Verifies uploads organized into year/month folders correctly. Tests folder structure integrity. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Migration Issues" --body "Detects broken media links after site migration. Tests for hardcoded URLs. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] External Media URL Handling" --body "Tests handling of external/remote media URLs. Validates hotlink handling. Threat: 45" && sleep 2

echo "✅ File Management: 10 diagnostics"
sleep 5

# CATEGORY 5: Media Security (8 diagnostics)
echo "Creating Media Security Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Malicious File Upload Detection" --body "Tests for malicious file upload attempts. Validates file type verification beyond extension. Threat: 85" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Direct File Access Security" --body "Tests if direct access to PHP files in uploads is blocked. Validates .htaccess rules. Threat: 80" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Hotlinking Protection" --body "Checks if hotlinking protection is configured. Tests referrer checks. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] File Type MIME Validation" --body "Validates MIME type checking for uploaded files. Tests for MIME spoofing vulnerabilities. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Private Media Access Control" --body "Tests access control for private/restricted media files. Validates permission checks. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media SSL/HTTPS Enforcement" --body "Checks if media files are served over HTTPS. Detects mixed content issues. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Executable File Prevention" --body "Validates prevention of executable file uploads (.php, .exe, .sh). Tests security filters. Threat: 85" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media CORS Configuration" --body "Tests Cross-Origin Resource Sharing settings for media. Validates security headers. Threat: 55" && sleep 2

echo "✅ Media Security: 8 diagnostics"
sleep 5

# CATEGORY 6: Metadata & SEO (10 diagnostics)
echo "Creating Metadata & SEO Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Alt Text Coverage" --body "Measures percentage of images with alt text. Detects accessibility issues. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] EXIF Data Preservation" --body "Tests if EXIF data is preserved or stripped during upload. Validates privacy settings. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] GPS Data Removal" --body "Verifies GPS/location data is removed from images for privacy. Tests EXIF stripping. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Title Generation" --body "Tests automatic title generation from filenames. Validates title sanitization. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Caption Functionality" --body "Validates caption display on images. Tests caption filters and output. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Description Usage" --body "Checks if media descriptions are utilized properly. Tests description field functionality. Threat: 25" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Schema Markup" --body "Tests if proper schema markup is added to images. Validates structured data for SEO. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Filename Sanitization" --body "Validates filename sanitization removes special characters. Tests for URL-safe filenames. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Attachment Page SEO" --body "Tests SEO optimization of media attachment pages. Validates titles, descriptions, indexing. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Sitemap Generation" --body "Verifies media files are included in XML sitemap. Tests image sitemap functionality. Threat: 40" && sleep 2

echo "✅ Metadata & SEO: 10 diagnostics"
sleep 5

echo ""
echo "=== Media Library Diagnostics Creation Complete ==="
echo "Total Created: 60 diagnostics"
echo ""
echo "Categories:"
echo "  • File Upload Issues: 12"
echo "  • Image Processing: 10"
echo "  • Media Library Performance: 10"
echo "  • File Management & Storage: 10"
echo "  • Media Security: 8"
echo "  • Metadata & SEO: 10"
echo ""
echo "Comprehensive coverage of WordPress Media Library!"
