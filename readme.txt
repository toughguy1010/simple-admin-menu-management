=== Admin Sidebar Menu Manager ===
Contributors: ducpham
Tags: admin menu, sidebar, hide menu, reorder menu, menu manager
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful yet lightweight WordPress plugin to fully manage your admin sidebar menu - reorder items with drag & drop and selectively hide/show menus and submenus.

== Description ==

Admin Sidebar Menu Manager is a comprehensive solution for customizing your WordPress admin interface. Whether you want to simplify the dashboard for clients by hiding unnecessary menus or reorganize the workflow for yourself, this plugin makes it effortless.

**Key Features:**

*   **Drag & Drop Reordering:** Easily rearrange admin menu items to match your workflow. Works for local main menus and submenus.
*   **Simple Management:** Toggle switches to hide/show menus instantly.
*   **Submenu Control:** Granular control over nested submenu items with a modern, accordion-style UI.
*   **Role Based Access:** Configuration is restricted to Super Admins (users with `update_core` capability).
*   **Secure:** Strictly blocks access to hidden pages, ensuring users cannot access them even via direct URL.
*   **Modern UI:** Clean, native-feeling interface that blends perfectly with WordPress.

== Installation ==

1.  Upload the `simple-admin-menu-management` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to **Settings > Menu Manager** to configure.

== Frequently Asked Questions ==

= Who can manage the menus? =
Only users with the `update_core` capability (typically Administrators/Super Admins) can access the configuration page.

= Does this delete the menus? =
No, it only hides them from display and restricts access. You can easily unhide them at any time.

== Screenshots ==

1.  **Menu Manager Interface** - The main drag-and-drop interface for managing your sidebar.

== Changelog ==

= 1.4.1 =
*   Fix: Resolved issue where admin notices would jump inside the plugin wrapper.
*   Fix: Standardized version numbers.

= 1.4.0 =
*   Improvement: Enhanced icon compatibility (including support for Flatsome theme icons).
*   Improvement: Dark mode UI for icon containers.
*   Update: Removed Admin Bar Manager feature.
*   Update: Removed external update checker.
*   Refactor: Codebase refactored to MVC structure.

= 1.2.0 =
*   Feature: Added drag and drop menu reordering.
*   Update: Rebranded to "Admin Sidebar Menu Manager".
*   Improvement: Improved UI with vertical list layout.

= 1.0.0 =
*   Initial release.
