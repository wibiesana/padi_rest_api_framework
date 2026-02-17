# CHANGE LOG

## v1.0.1 (2026-02-17)

### Core Framework Updates

- **PHP Compatibility**:
  - Fixed "Implicitly nullable parameter" deprecation warnings for PHP 8.1+.
  - Updated `core/Cache.php`, `core/Controller.php`, and `core/ActiveRecord.php` with explicit nullable type hints.
- **Generator Improvements**:
  - Added support for sorting in generated `searchPaginate` methods.
  - Set default pagination size to 25 items.
  - Fixed `primaryKey` type hint to support composite keys (`string|array`).
- **ActiveRecord enhancements**:
  - Refined `searchPaginate` with improved SQL join logic and table aliasing.
  - Enhanced relationship eager loading (`loadRelations`).
- **Database & Routing**:
  - Improved multi-database connection management in `DatabaseManager`.
  - Added URI normalization to filter redundant slashes in request paths.
- **Audit System**:
  - Integrated semi-automatic audit fields (`created_at`, `updated_at`, etc.) directly into `ActiveRecord`.

## v1.0.0

- Initial release of Padi REST API Framework.
- Core features: ActiveRecord, Fluent Query Builder, Autoloading, JWT Auth.
