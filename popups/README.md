# Popup System Documentation

## Overview

This is a lightweight popup engine built into the WordPress theme. It follows strict architectural rules:

- **PHP evaluates conditions** (audience, location) before rendering
- **JavaScript handles triggers and frequency** after rendering
- **Only ONE popup displays per page** (priority-based conflict resolution)
- **Extensible design** for adding new conditions/triggers

## Architecture

### Evaluation Order (Mandatory)

1. **Audience Check** (PHP) - Login state, customer state
2. **Location Check** (PHP) - Where popup should appear
3. **Priority Resolution** (PHP) - If multiple qualify, highest priority wins
4. **Render Popup HTML** - Inject into DOM (wp_footer)
5. **Frequency Check** (JS) - Once ever, once per session, every time
6. **Trigger Execution** (JS) - Page load, scroll percentage

## Creating a Popup

1. Go to **WordPress Admin → Popups → Add New**
2. Enter a title for the popup
3. Configure all settings in the "Popup Settings" meta box:

### Required Settings

- **Template**: Select a template from the registry
- **Priority**: 1-100 (higher = displays first when multiple qualify)
- **Login State**: All Users, Logged In Only, or Logged Out Only
- **Location Mode**: Entire Site, Front Page, Blog Pages, Shop Pages, or Specific Pages
- **Trigger Type**: Page Load or Scroll Percentage
- **Frequency Type**: Every Time, Once Ever, or Once Per Session

### Optional Settings

- **Customer State** (only for logged-in users): Any Customer, Has Orders, or No Orders
- **Scroll Percentage** (only for scroll trigger): 1-100
- **Specific Pages** (only for specific pages mode): Select pages from list

## Creating Templates

Templates are PHP files located in `/popups/templates/`.

### Template Structure

```php
<?php
/**
 * Template Name
 * 
 * Available variables:
 * - $popup_id: Popup post ID
 */

if (!defined('ABSPATH')) exit;
?>

<button class="lfa-popup-close" aria-label="Close">
	<!-- Close icon SVG -->
</button>

<div class="lfa-popup-inner">
	<h2 class="lfa-popup-title">Your Title</h2>
	<p class="lfa-popup-text">Your content</p>
	<div class="lfa-popup-actions">
		<a href="#" class="lfa-popup-button lfa-popup-button-primary">Action</a>
		<button class="lfa-popup-button lfa-popup-button-secondary lfa-popup-close">Close</button>
	</div>
</div>
```

### Registering Templates

Templates must be registered in `inc/popups.php`:

```php
function lfa_popup_get_templates() {
	return apply_filters('lfa_popup_templates', [
		'your-template-slug' => __('Display Name', 'livingfitapparel'),
	]);
}
```

## Condition System

### Audience Conditions (WHO)

**Login State** (required, mutually exclusive):
- `all` - All users
- `logged_in` - Only logged-in users
- `logged_out` - Only logged-out users

**Customer State** (optional, only for logged-in users):
- `any` - Any customer
- `has_orders` - Has placed orders
- `no_orders` - Has not placed orders

**Logic**: All audience conditions must pass (AND logic).

### Location Conditions (WHERE)

**Location Modes**:
- `entire_site` - All pages
- `front_page` - Front page only
- `blog_pages` - Blog, category, tag, archive, single posts
- `shop_pages` - WooCommerce shop pages (requires WooCommerce)
- `specific_pages` - Manually selected pages

**Rules**: No exclusions, no mixed modes.

### Frequency Rules (HOW OFTEN)

**Frequency Types**:
- `every_time` - Show every time conditions are met
- `once_ever` - Show once per user (localStorage + cookie fallback)
- `once_per_session` - Show once per browser session (sessionStorage)

**Storage Keys**: Unique per popup ID (`lfa_popup_{popup_id}`)

### Trigger Rules (WHEN)

**Trigger Types**:
- `page_load` - Show immediately on page load
- `scroll_percent` - Show when user scrolls X% down the page

**Configuration**: Scroll trigger requires scroll percentage (1-100).

## Conflict Resolution

If multiple popups qualify:
1. Popups are sorted by priority (highest first)
2. Only the highest priority popup renders
3. If priorities match, first match wins

## Extensibility

### Adding New Templates

1. Create template file in `/popups/templates/`
2. Register in `lfa_popup_get_templates()` filter
3. Use in popup settings

### Adding New Conditions

Modify condition evaluators in `inc/popups.php`:
- `lfa_popup_evaluate_audience()` - Add new audience rules
- `lfa_popup_evaluate_location()` - Add new location providers

### Adding New Triggers

1. Add trigger type to admin meta box
2. Add trigger handling in `assets/js/popups.js` → `setupTrigger()`
3. Add data attribute in `lfa_popup_render()`

## Files Structure

```
theme/
├── inc/
│   └── popups.php          # Core popup system
├── assets/
│   ├── css/
│   │   └── popups.css      # Popup styles
│   └── js/
│       └── popups.js       # Popup JavaScript engine
└── popups/
    ├── templates/
    │   ├── first-time-visitor.php
    │   └── logged-in-offer.php
    └── README.md           # This file
```

## Example Use Cases

### First-Time Visitor Popup
- **Login State**: Logged Out Only
- **Location**: Entire Site
- **Trigger**: Page Load
- **Frequency**: Once Ever

### Member Exclusive Offer
- **Login State**: Logged In Only
- **Customer State**: Has Orders
- **Location**: Shop Pages
- **Trigger**: Scroll 50%
- **Frequency**: Once Per Session

### Newsletter Signup
- **Login State**: All Users
- **Location**: Front Page
- **Trigger**: Scroll 75%
- **Frequency**: Once Ever

## Notes

- Popups are **never rendered in DOM** if conditions fail
- Frequency and triggers are **only evaluated after PHP allows rendering**
- System is **designed for extensibility** - avoid hard-coding condition logic
- All popup HTML is injected in `wp_footer` hook (priority 999)
