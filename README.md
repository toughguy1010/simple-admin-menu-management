# Simple Admin Menu Hider

A lightweight, robust WordPress plugin to easily hide admin menu and submenu items from the sidebar based on user roles.

## Features

- **Simple Interface**: Toggle switches to hide/show menus instantly.
- **Submenu Support**: Granular control over nested submenu items with a modern accordion UI.
- **Role Based**: Configuration is restricted to Super Admins (`update_core` capability).
- **Secure**: Strictly blocks access to hidden pages (including `edit.php` vs `edit.php?post_type=page` ambiguity handling).
- **Friendly Error Page**: Displays a custom, styled "Access Restricted" page withrole-aware messages.
- **Zero Bloat**: Lightweight code, runs only in admin.

## Installation

1.  Upload the `simple-admin-menu-hider` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to **Settings > Menu Hider** to configure.

## Usage

1.  Go to **Settings > Menu Hider**.
2.  You will see a list of all available admin menu items.
3.  **Toggle** the switch next to any item to hide it (Red = Hidden).
4.  Click the **Arrow** icon (or header) to reveal submenus and hide specific children.
5.  Click **Save Changes**.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## License

GPLv2 or later
