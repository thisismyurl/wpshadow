# Security Policy

## Reporting a Security Vulnerability

If you discover a security vulnerability in this plugin, please email security@thisismyurl.com instead of using the issue tracker.

Please include:
- A description of the vulnerability
- Steps to reproduce or proof of concept
- Affected versions
- Any known workarounds

I take security seriously and will respond promptly to responsible disclosures.

## Security Practices

This plugin follows WordPress security best practices:

- **Input validation:** All user input is validated and sanitized.
- **Escaping:** Output is properly escaped for context (HTML, JavaScript, URL, CSS).
- **Capability checks:** All admin actions check user capabilities.
- **Nonce verification:** Forms include WordPress nonces.
- **Database queries:** Prepared statements with placeholders to prevent SQL injection.
- **No external phone-homes:** This plugin does not send data to external services without explicit user consent.
- **Regular updates:** Security patches are released promptly.

## Supported Versions

Security updates are provided for the current version and one previous major version.

| Version | Support | Status |
|---------|---------|--------|
| Latest | ✅ | Security updates |
| Previous | ✅ | Critical security updates only |
| Older | ❌ | No longer supported |

## Changelog and Updates

Check [CHANGELOG.md](CHANGELOG.md) and [GitHub Releases](../../releases) for security-related updates and fixes.

## Questions?

If you have security-related questions (that aren't vulnerability reports), feel free to open a discussion or contact me directly through my website: [thisismyurl.com](https://thisismyurl.com/)
