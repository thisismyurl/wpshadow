# Cross-Origin Isolation Headers

## Overview

WPShadow includes Cross-Origin Isolation headers (COOP and COEP) as part of the Security Hardening feature to protect your WordPress site from Spectre-like attacks and isolate your site's browsing context from untrusted third-party content.

## What are Cross-Origin Isolation Headers?

Cross-Origin Isolation is a security mechanism that uses two HTTP headers to protect your site:

### Cross-Origin-Opener-Policy (COOP)
- **Header Value**: `same-origin`
- **Purpose**: Isolates the browsing context exclusively to same-origin documents
- **Protection**: Prevents cross-origin documents from being able to access the window object

### Cross-Origin-Embedder-Policy (COEP)
- **Header Value**: `require-corp`
- **Purpose**: Requires resources to explicitly opt-in to being loaded
- **Protection**: Ensures that cross-origin resources have either CORS or CORP headers

## Benefits

1. **Security**: Protects against Spectre-like attacks and side-channel attacks
2. **Isolation**: Isolates your site's browsing context from untrusted third-party content
3. **Advanced Features**: Enables use of powerful web platform features like SharedArrayBuffer
4. **Best Practices**: Aligns with modern web security best practices

## How to Enable

The Cross-Origin Isolation headers are included as part of the Security Hardening feature:

1. Navigate to **WPShadow → Settings**
2. Enable the **One-Click Security Hardening** feature
3. The headers will be automatically sent with all HTTP responses

## Implementation Details

- Headers are sent via WordPress `send_headers` action hook
- Only sent when Security Hardening feature is enabled
- Includes check to prevent sending if headers were already sent
- No configuration required - works out of the box

## Important Considerations

### Potential Impact on Third-Party Content

When these headers are enabled, third-party resources must explicitly opt-in to being loaded. This means:

1. **Cross-origin images, scripts, and stylesheets** must be served with appropriate CORS headers
2. **Embedded content** (iframes, videos, etc.) may require updates to work properly
3. **Third-party integrations** might need to be configured to support COEP

### Testing Before Production

If you use third-party services or embed external content, we recommend:

1. Test the feature on a staging site first
2. Check that all external resources load correctly
3. Verify that embedded content and integrations work as expected
4. Monitor for any browser console errors

### Compatibility

- Works with all modern browsers that support COOP and COEP headers
- Gracefully degrades in older browsers (headers are ignored)
- Compatible with WordPress 6.4+ and PHP 8.1.29+

## Troubleshooting

### External Resources Not Loading

If external resources stop loading after enabling the feature:

1. Check browser console for COEP/CORP errors
2. Ensure external resources are served with proper CORS headers
3. Contact third-party service providers for COEP-compatible versions
4. Temporarily disable the feature if critical functionality is affected

### Embedded Content Issues

If embedded content (iframes, videos) stops working:

1. Verify the embed source supports COEP
2. Check if the embed provider offers COEP-compatible embed codes
3. Consider using WordPress oEmbed when possible (WordPress handles COEP automatically)

## Technical References

- [Cross-Origin-Opener-Policy (MDN)](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Opener-Policy)
- [Cross-Origin-Embedder-Policy (MDN)](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Embedder-Policy)
- [COOP and COEP explained (web.dev)](https://web.dev/coop-coep/)
- [Making your website "cross-origin isolated" (web.dev)](https://web.dev/cross-origin-isolation-guide/)

## Support

If you encounter issues with Cross-Origin Isolation headers:

1. Check the troubleshooting section above
2. Visit our [support forum](https://wpshadow.com/support)
3. [Report an issue](https://github.com/thisismyurl/wpshadow/issues) on GitHub

## Disabling the Feature

If you need to disable Cross-Origin Isolation headers:

1. Navigate to **WPShadow → Settings**
2. Disable the **One-Click Security Hardening** feature
3. The headers will no longer be sent

Note: Disabling the entire Security Hardening feature will also disable other security features. If you only want to disable the headers temporarily, consider using a custom filter (contact support for assistance).
