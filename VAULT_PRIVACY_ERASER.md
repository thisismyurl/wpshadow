# Vault Personal Data Erasure (Issue #35)

## Overview
Implements GDPR-compliant personal data erasure for the Vault system (Issue #35). When a user requests data erasure via WordPress's personal data export/erase tools, the system:

1. **Retains originals** in the Vault (needed for audit trail and recovery).
2. **Anonymizes personal data**:
   - Strips EXIF/metadata from derivatives (thumbnails, srcset).
   - Scrubs uploader user ID from metadata.
   - Removes user IDs from operation journals.
   - Logs anonymization events to global ledger.

## Compliance Model

### What Gets Anonymized
- **Attachment metadata**: `_timu_vault_uploader_user_id` → 0
- **Operation journals**: All `user_id` fields referencing the erased user → 0
- **Derivatives**: EXIF/metadata stripped from `wp_get_attachment_image_src()` thumbnails and srcset images
- **Global ledger**: Records anonymization operation with success status

### What Stays
- **Original vault files**: Retained for audit and potential recovery (unencrypted originals still contain no personal data beyond EXIF)
- **Vault structure**: Path mappings, hashes, encryption metadata intact
- **Public content**: Blog posts/pages unchanged; derivatives remain usable

### WordPress Integration
Registers with WordPress's Personal Data Export & Erase API:
- **Eraser name**: "TIMU Vault (anonymize originals & derivatives)"
- **Callback**: `timu_core_vault_eraser_callback()`
- **Location**: Tools → Export Personal Data → Select "TIMU Vault" eraser
- **Batching**: 50 items per request; multi-page support built-in

## Implementation Details

### 1. Uploader Tracking on Ingest
When a file is uploaded and ingested into the Vault (`TIMU_Vault::ingest()`):
```php
$uploader = get_current_user_id();
update_post_meta( $attachment_id, '_timu_vault_uploader_user_id', (int) $uploader );
```

### 2. EXIF Stripping (Multi-Engine)
Strips metadata from derivative images using **Imagick** (preferred) or **GD** (fallback):
- **JPEG**: Re-encode with 90% quality
- **PNG**: GD save drops ancillary chunks
- **WebP**: Re-encode with 80% quality
- **Unsupported**: Silently skip (no metadata loss risk)

Supported formats: JPG, JPEG, PNG, WebP. RAW, HEIC, AVIF handled gracefully (retained as-is).

### 3. Journal Scrubbing
For each attachment's journal (`_timu_vault_journal`):
```
operations[].user_id (if === target_user_id) → 0
```
Preserves operation history with anonymized user references.

### 4. Anonymization Entry
Records the erasure:
```php
update_post_meta( $attachment_id, '_timu_vault_anonymized', '2026-01-08T15:30:45Z' );
```

### 5. Ledger Entry
Global ledger captures:
```
{
  "ts": "2026-01-08T15:30:45Z",
  "site_id": 1,
  "attachment_id": 123,
  "user_id": 456,
  "op": "erase_personal_data",
  "success": true
}
```

## API Reference

### PHP Function: `TIMU_Vault::anonymize_attachment()`
```php
public static function anonymize_attachment( int $attachment_id, int $user_id ): bool
```
Anonymizes a single attachment. Returns `true` on success.

### PHP Function: `TIMU_Vault::erase_user_personal_data()`
```php
public static function erase_user_personal_data(
    int $user_id,
    int $page = 1,
    int $per_page = 50
): array {
    return array(
        'items_removed'  => int,   // anonymized attachments
        'items_retained' => int,   // failed attempts
        'messages'       => array, // operation log
        'done'           => bool   // pagination complete
    );
}
```

### WP-CLI Command
```bash
wp timu vault erase-user-data <user-id-or-email> [--batch=50] [--verbose]
```

**Examples:**
```bash
# Anonymize by user ID
wp timu vault erase-user-data 42

# Anonymize by email
wp timu vault erase-user-data jane@example.com

# Custom batch size with verbose logging
wp timu vault erase-user-data 42 --batch=100 --verbose
```

### WordPress Privacy Hook
Callback registered to `wp_privacy_personal_data_erasers`:
```php
function timu_core_vault_eraser_callback(
    string $email_address,
    int $page = 1
): array {
    // Resolves user by email, delegates to erase_user_personal_data()
}
```

Called by: **Tools → Export Personal Data** (admin interface).

## Testing Checklist

- [ ] **Manual GDPR Flow**:
  1. Admin → Tools → Export/Erase Personal Data
  2. Select user (by email)
  3. Select "TIMU Vault" eraser
  4. Run request
  5. Verify attachments are anonymized
  6. Check journals/ledger are scrubbed

- [ ] **WP-CLI**:
  ```bash
  wp timu vault erase-user-data 1 --verbose
  ```
  Verify all messages logged and attachments anonymized.

- [ ] **Multi-page Batching**:
  Create 150+ attachments with same uploader; verify pagination works and all items anonymized.

- [ ] **EXIF Stripping**:
  Upload JPEG with EXIF → Anonymize → Verify EXIF removed from derivative thumbnails.

- [ ] **Ledger Audit**:
  ```php
  $ledger = TIMU_Vault::get_global_ledger( array( 'op' => 'erase_personal_data' ) );
  ```
  Verify all erasure operations logged.

## Database Schema

### Post Meta Added
- `_timu_vault_uploader_user_id` (int): User ID who uploaded the attachment
- `_timu_vault_anonymized` (string ISO8601 timestamp): When anonymized

### Option Format (Global Ledger)
```
timu_vault_global_ledger => array[
    {
        ts: "2026-01-08T15:30:45Z",
        site_id: 1,
        attachment_id: 123,
        user_id: 456,  // -> 0 if erased
        op: "erase_personal_data",
        success: true
    }
]
```
Rotation: Keeps latest 10,000 entries; trims on overflow.

## Migration Notes

### Existing Attachments (Pre-Feature)
Attachments ingested before uploader tracking was added have no `_timu_vault_uploader_user_id` meta. Options:

1. **On-demand backfill** (recommended):
   - Annotate query to include `meta_compare: 'NOT EXISTS'` for META_UPLOADER
   - Add async migration task to populate from post author

2. **Accept gaps**: Erasure requests skip attachments without uploader metadata (logged as "Failed to anonymize").

### Forward Compatibility
All new uploads automatically tracked. No migration needed for future data.

## Security Considerations

1. **EXIF Stripping**: Ensures no GPS, camera info, or embedded personal data in public derivatives.
2. **Journal Anonymization**: Removes audit trail links to erased users without destroying history.
3. **Ledger Immutability**: Erasure events logged but user IDs zeroed; immutable record of compliance.
4. **Multisite Scope**: Leverages `get_current_blog_id()` in ledger; per-site erasure supported.
5. **No Hard Deletes**: Originals retained for audit; GDPR satisfied by anonymization.

## Future Enhancements

- [ ] **Exportable Anonymization Report**: Generate CSV of anonymized attachments with timestamps.
- [ ] **Deferrable Erasure**: Implement "trash" period (30 days) before permanent vault purge.
- [ ] **Privacy Policy Template**: Add example text for privacy policies documenting data retention.
- [ ] **Bulk User Erasure**: API for batch user deletions (network-wide).

## Files Modified

1. **core-support-thisismyurl.php**:
   - Added `timu_core_register_privacy_erasers()`: Registers eraser hook.
   - Added `timu_core_vault_eraser_callback()`: WordPress privacy API integration.

2. **includes/class-timu-vault.php**:
   - Added `META_UPLOADER`, `META_ANONYMIZED` constants.
   - Modified `ingest()`: Tracks uploader user ID.
   - Added `strip_exif_from_file()`: Multi-engine EXIF stripping.
   - Added `strip_exif_from_attachment()`: Batch derivative scrubbing.
   - Added `anonymize_attachment()`: Core anonymization logic.
   - Added `erase_user_personal_data()`: Batched user data erasure.
   - Added `cli_erase_user_data()`: WP-CLI command.
   - Updated `init()`: Registered WP-CLI command.

## References

- [WordPress Personal Data Erasure API](https://developer.wordpress.org/plugins/privacy/adding-privacy-policies/)
- [GDPR Compliance (Article 17 - Right to Erasure)](https://gdpr-info.eu/art-17-gdpr/)
- Issue #35: Personal Data Erasure: Purge & Anonymize Originals
- Issue #36: Privacy & User Data: Export & Erase Hooks
