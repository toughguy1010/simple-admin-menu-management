# Admin Sidebar Menu Manager

A powerful yet lightweight WordPress plugin to fully manage your admin sidebar menu - reorder items with drag & drop and selectively hide/show menus and submenus.

## Features

- **Drag & Drop Reordering**: Easily rearrange admin menu items to match your workflow.
- **Simple Interface**: Toggle switches to hide/show menus instantly.
- **Submenu Support**: Granular control over nested submenu items with a modern accordion UI.
- **Role Based**: Configuration is restricted to Super Admins (`update_core` capability).
- **Secure**: Strictly blocks access to hidden pages (including `edit.php` vs `edit.php?post_type=page` ambiguity handling).
- **Friendly Error Page**: Displays a custom, styled "Access Restricted" page with role-aware messages.
- **Zero Bloat**: Lightweight code, runs only in admin.

## Installation

1.  Upload the `simple-admin-menu-management` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to **Settings > Menu Manager** to configure.

## Usage

1.  Go to **Settings > Menu Manager**.
2.  **Drag and drop** menu items to rearrange them.
3.  **Toggle** the switch next to any item to hide it (Red = Hidden, Gray = Visible).
4.  Click the **menu item** to reveal submenus and manage specific children.
5.  Click **Save Changes** to apply.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Changelog

### 1.2.0

- Added drag and drop menu reordering
- Rebranded to "Admin Sidebar Menu Manager"
- Improved UI with vertical list layout
- Enhanced drag handle with hover effects

### 1.0.0

- Initial release
- Hide/show menu and submenu items
- Role-based access control
- Custom error page

## License

GPLv2 or later
