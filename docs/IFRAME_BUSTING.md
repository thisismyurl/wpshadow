# Iframe Busting (Clickjacking Protection)

## Overview

The Iframe Busting feature protects your WordPress site against clickjacking attacks by preventing malicious sites from embedding your pages in hidden iframes. This security feature implements multiple layers of protection following modern web security standards.

## What is Clickjacking?

Clickjacking is a malicious technique where attackers trick users into clicking something different from what they perceive. This is typically done by embedding your site in an invisible iframe on a malicious page, overlaying hidden elements that users click on while thinking they're interacting with legitimate content.

## Protection Layers

The feature implements three complementary protection mechanisms:

### 1. Content-Security-Policy (CSP) - Modern Standard

The `frame-ancestors` directive is the modern, recommended approach for controlling who can embed your site:

- **Most secure and flexible**
- Supported by all modern browsers
- Allows granular control over allowed embedding origins

### 2. X-Frame-Options Header - Legacy Support

For older browsers that don't support CSP, the `X-Frame-Options` header provides fallback protection:

- **Widely supported** across all browsers
- Simple configuration (DENY or SAMEORIGIN)
- Industry standard for years

### 3. JavaScript Frame-Buster - Ultimate Fallback

A JavaScript-based protection that runs when the page loads:

```javascript
if (top !== self) {
    top.location.replace(location);
}
```

- **Works even in ancient browsers**
- Can be disabled if not needed
- Minimal performance impact

## Configuration Options

### Frame Policy Settings

Choose the protection level that fits your needs:

#### DENY (Most Secure)
- **Blocks ALL framing** of your site
- Use when: Your site should never be embedded anywhere
- Headers sent:
  - `Content-Security-Policy: frame-ancestors 'none'`
  - `X-Frame-Options: DENY`

#### SAMEORIGIN (Recommended)
- **Allows framing only by your own domain**
- Use when: You need to embed pages within your own site
- Headers sent:
  - `Content-Security-Policy: frame-ancestors 'self'`
  - `X-Frame-Options: SAMEORIGIN`

#### CUSTOM (Advanced)
- **Allow specific trusted domains** to embed your site
- Use when: You have trusted partners who need to embed your content
- Requires configuration of allowed origins
- Example: `frame-ancestors 'self' https://trusted-site.com https://partner.org`

### JavaScript Frame-Buster

Option to enable/disable the JavaScript fallback:
- **Enabled by default** for maximum compatibility
- Can be disabled if you only need modern browser support
- No negative impact when enabled

## How to Enable

1. Navigate to **WPShadow → Dashboard** in your WordPress admin
2. Find the **Security** widget section
3. Locate **Iframe Busting (Clickjacking Protection)**
4. Toggle the feature **ON**
5. Configure your preferred frame policy in settings

## When to Use Each Policy

### Use DENY when:
- Your site is purely administrative (WordPress backend only)
- You never need to embed your pages in iframes
- Maximum security is required
- You're protecting sensitive applications

### Use SAMEORIGIN when:
- You use WordPress features that embed pages (like the Customizer)
- You have internal dashboards that frame your content
- You want protection but need flexibility within your own domain
- **This is the recommended default for most WordPress sites**

### Use CUSTOM when:
- You have legitimate business partners who need to embed your content
- You're building widgets or embeddable tools
- You run a multi-site network with trusted external sites
- You need fine-grained control over embedding permissions

## Testing Your Configuration

### Method 1: Browser DevTools
1. Open your site in Chrome/Firefox
2. Press F12 to open Developer Tools
3. Go to the **Network** tab
4. Reload the page
5. Click on the main document request
6. Look for these headers in the **Response Headers**:
   - `Content-Security-Policy: frame-ancestors ...`
   - `X-Frame-Options: ...`

### Method 2: Online Tools
Use tools like:
- [Security Headers](https://securityheaders.com/)
- [Mozilla Observatory](https://observatory.mozilla.org/)

### Method 3: Manual Test
Create a test HTML file:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Iframe Test</title>
</head>
<body>
    <h1>Iframe Test</h1>
    <iframe src="https://your-site.com" width="800" height="600"></iframe>
</body>
</html>
```

Open this file in a browser. If protection is working:
- With DENY: The iframe will show an error
- With SAMEORIGIN: The iframe will show an error (since you're opening a local file)
- JavaScript frame-buster: The page will redirect to escape the frame

## Browser Compatibility

| Browser | CSP frame-ancestors | X-Frame-Options | JS Frame-Buster |
|---------|-------------------|----------------|-----------------|
| Chrome 40+ | ✓ | ✓ | ✓ |
| Firefox 70+ | ✓ | ✓ | ✓ |
| Safari 10+ | ✓ | ✓ | ✓ |
| Edge (Chromium) | ✓ | ✓ | ✓ |
| IE 11 | ✗ | ✓ | ✓ |
| IE 8-10 | ✗ | ✓ | ✓ |

## Performance Impact

The feature has **minimal to zero performance impact**:
- Headers add ~50-100 bytes to each response
- JavaScript frame-buster adds ~150 bytes and runs once on page load
- No database queries required
- No external API calls

## Common Issues & Solutions

### Issue: WordPress Customizer not loading
**Solution:** Use SAMEORIGIN instead of DENY. The Customizer embeds preview frames.

### Issue: Page builders not working
**Solution:** Some page builders use iframes. Switch to SAMEORIGIN policy.

### Issue: Headers not appearing
**Solution:** Check if another plugin or server configuration is already sending these headers.

### Issue: Custom origins not working
**Solution:** Ensure URLs are properly formatted with protocol and don't have trailing slashes.

## Security Considerations

### Limitations
- Frame-busting can be bypassed by sophisticated attackers using browser vulnerabilities
- JavaScript can be disabled by users
- Some proxy servers may strip headers

### Best Practices
1. **Always enable all three protection layers** for defense in depth
2. **Use DENY or SAMEORIGIN** unless you have specific requirements
3. **Audit custom origins regularly** to ensure they're still trusted
4. **Test thoroughly** after enabling, especially if you use iframes internally
5. **Combine with other security features** (CSP, HTTPS, etc.)

## Related Features

Consider enabling these complementary security features:
- **One-Click Security Hardening** - Additional security measures
- **Web Application Firewall** - Request filtering and attack detection
- **Content Security Policy** - Comprehensive content security headers

## Further Reading

- [OWASP Clickjacking Defense](https://cheatsheetseries.owasp.org/cheatsheets/Clickjacking_Defense_Cheat_Sheet.html)
- [MDN: CSP frame-ancestors](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors)
- [MDN: X-Frame-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options)

## Support

If you encounter issues with this feature:
1. Check the browser console for CSP violations
2. Verify headers using browser DevTools
3. Test with JavaScript frame-buster disabled to isolate issues
4. Contact support with specific error messages and browser information
