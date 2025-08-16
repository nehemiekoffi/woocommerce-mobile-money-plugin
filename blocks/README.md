# Mobile Money Blocks Implementation

This folder contains the Blocks-compatible implementation for WooCommerce 8.3+ that supports the new Checkout Block and Cart Block.

## Overview

The Blocks implementation provides full compatibility with WooCommerce's new block-based checkout and cart system while maintaining backward compatibility with the legacy implementation.

## Files Structure

- `blocks-loader.php` - Main loader that handles Blocks initialization and compatibility checks
- `class-wc-mobile-money-blocks-gateway.php` - Blocks-compatible payment gateway class
- `class-wc-mobile-money-blocks-integration.php` - Blocks API integration class
- `mobilemoney-blocks.js` - Enhanced JavaScript for Blocks functionality
- `mobilemoney-blocks.css` - Blocks-specific styling

## Features

### Blocks Support
- **Checkout Block**: Full integration with WooCommerce Checkout Block
- **Cart Block**: Support for WooCommerce Cart Block
- **Payment Method Block**: Custom payment method integration

### Enhanced Functionality
- Modern form styling optimized for Blocks
- Responsive design with mobile-first approach
- Dark mode support
- Enhanced validation and error handling
- Blocks-specific event handling

### Backward Compatibility
- Automatically detects WooCommerce version
- Falls back to legacy implementation for older versions
- Maintains all existing functionality

## How It Works

1. **Version Detection**: The plugin automatically detects WooCommerce version
2. **Implementation Selection**: 
   - WooCommerce 8.3+ → Blocks implementation
   - WooCommerce 8.2.x and below → Legacy implementation
3. **Dynamic Loading**: Only loads the necessary implementation
4. **Seamless Integration**: Works with both classic and block-based themes

## Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+
- For Blocks support: WooCommerce 8.3+

## Installation

The Blocks implementation is automatically loaded when:
1. WooCommerce 8.3+ is detected
2. The Blocks package is available
3. The site is using block-based checkout/cart

## Configuration

The Blocks gateway appears as a separate payment method in WooCommerce settings:
- **Title**: "Mobile Money Payment (Blocks)"
- **Settings**: Same configuration options as legacy gateway
- **Compatibility**: Works alongside legacy gateway

## Customization

### CSS Customization
Modify `mobilemoney-blocks.css` to customize the appearance:
- Form styling
- Color schemes
- Responsive breakpoints
- Dark mode support

### JavaScript Customization
Extend `mobilemoney-blocks.js` for additional functionality:
- Custom validation rules
- Additional event handlers
- Blocks-specific integrations

### PHP Customization
Extend the gateway classes for custom business logic:
- Additional payment processing
- Custom validation
- Integration with external services

## Troubleshooting

### Common Issues

1. **Blocks not loading**: Check WooCommerce version and Blocks package
2. **Styling issues**: Verify CSS file is being loaded
3. **JavaScript errors**: Check browser console for errors
4. **Payment processing**: Verify gateway settings

### Debug Mode

Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For issues related to the Blocks implementation:
1. Check WooCommerce version compatibility
2. Verify Blocks package is active
3. Test with default theme
4. Check browser console for JavaScript errors

## Migration from Legacy

The plugin automatically handles migration:
- No manual configuration required
- Existing orders remain accessible
- Settings are preserved
- Seamless transition between implementations

## Future Updates

The Blocks implementation will be updated to support:
- New WooCommerce Blocks features
- Enhanced payment processing
- Additional customization options
- Performance improvements
