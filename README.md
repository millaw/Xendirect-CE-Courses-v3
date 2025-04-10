# XD CE Courses WordPress Plugin

This WordPress plugin integrates with the Xenegrade/Xendirect API to display continuing education course offerings, specifically tailored for CUNY colleges and other educational institutions utilizing their platform.

## ✨ Features

- **API Integration**: Connects seamlessly with Xenegrade/Xendirect services
- **Customizable Display**: Show courses using simple shortcodes
- **Admin Dashboard**: Easy configuration of API credentials
- **Post Subtitle Support**: Use subtitles as API query parameters
- **Clean Uninstallation**: Removes all plugin data when deleted

## 🛠 Installation

1. Download the latest release from GitHub
2. Upload the `xd-ce-courses` folder to `/wp-content/plugins/`
3. Activate the plugin through WordPress admin
4. Configure your API settings at: **Settings → XD CE Courses**

## ⚙️ Configuration

### API Setup
```php
API Key: [Your unique key from Xenegrade/Xendirect]
Organization ID: [Your institution identifier]
Base URL: https://api.xendirect.com/v2/
```

### Shortcode Usage
Add this to any post/page to display courses:

```html
[ce_courses]
```

### Advanced Usage
Set a custom subtitle in the post editor to use as an API filter key.

## 🧩 File Structure

```
xd-ce-courses/
├── api/
│   ├── xd-connect.php       # API connection handler
│   └── xd-settings.php      # Admin settings panel
├── pages/
│   └── ce-courses.php       # Display template
├── uninstall.php            # Cleanup script
└── xd-ce-courses.php        # Main plugin file
```

## 🚀 For Developers

### Hooks Available

```php
do_action('xd_ce_before_courses_display'); // Before courses render
do_action('xd_ce_after_courses_display');  // After courses render
```

### Constants

```php
define('XDCECOURSE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('XDCECOURSE_VERSION', '3.0.0');
// ... and more
```

## 📦 Requirements

- WordPress 5.0+
- PHP 7.2+ (fully compatible with PHP 8.0+)
- cURL enabled
- Valid Xenegrade/Xendirect API credentials


## 📜 License

GPL-2.0+. This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License.

❓ Support

Please **open an issue** for support requests or feature suggestions.
