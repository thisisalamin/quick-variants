# WooCommerce Variant Product Table

A WordPress plugin that displays WooCommerce products in a table format with expandable variants.

## Features

- Responsive product table display
- Show/hide product variants
- Quick add to cart functionality
- Sliding cart panel
- Real-time cart updates
- Supports variable products
- Category filtering
- Mobile-friendly design

## Installation

1. Download the plugin zip file
2. Go to WordPress Dashboard > Plugins > Add New
3. Click "Upload Plugin" and select the downloaded zip file
4. Click "Install Now" and then "Activate"

## Usage

### Basic Shortcode

Add the product table to any page or post using the shortcode:

```
[wc_product_table]
```

### Filter by Category

Display products from specific categories:

```
[wc_product_table category="category-slug"]
```

Multiple categories can be separated by commas:

```
[wc_product_table category="category-1,category-2"]
```

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.2 or higher

## Styling

The plugin uses Tailwind CSS for styling. Custom styles can be added through your theme's CSS file or the WordPress customizer.

## Support

For support questions or bug reports, please open an issue on the plugin's GitHub repository.

## License

This plugin is licensed under the GPL v2 or later.