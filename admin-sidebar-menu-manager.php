<?php

/**
 * Plugin Name: Admin Sidebar Menu Manager
 * Plugin URI:  https://example.com
 * Description: Manage your admin sidebar menu - reorder items with drag & drop and hide/show menus and submenus.
 * Version:     1.4.1
 * Author:      Duc Pham
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 * Text Domain: simple-admin-menu-management
 */

if (! defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('SAMH_VERSION', '1.4.1');
define('SAMH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SAMH_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include settings controller
require_once SAMH_PLUGIN_DIR . 'includes/settings-controller.php';
// Include error page
require_once SAMH_PLUGIN_DIR . 'includes/error-page.php';



// Activation hook - migrate settings if plugin was renamed
register_activation_hook(__FILE__, 'samh_activation_migrate_settings');

function samh_activation_migrate_settings()
{
	// Check if settings already exist (fresh install)
	$existing_menus = get_option('samh_hidden_menus', null);
	$existing_submenus = get_option('samh_hidden_submenus', null);
	$existing_order = get_option('samh_menu_order', null);

	// If no settings exist yet, this is likely a fresh activation
	// Settings are already preserved in the database with the same option names
	// No migration needed since we're keeping the same option keys

	// Optional: Set a flag to indicate plugin has been activated
	update_option('samh_plugin_activated', true);
}

// Global to store all menus before they are hidden
global $samh_all_menus, $samh_all_submenus;
$samh_all_menus = array();
$samh_all_submenus = array();

/**
 * Enqueue scripts and styles.
 */
function samh_enqueue_scripts($hook)
{
	if ('settings_page_simple-admin-menu-management' !== $hook) {
		return;
	}
	wp_enqueue_style('samh-admin-style', SAMH_PLUGIN_URL . 'assets/css/admin-style.css', array(), SAMH_VERSION);
	wp_enqueue_script('samh-admin-script', SAMH_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery', 'jquery-ui-sortable'), SAMH_VERSION, true);
}
add_action('admin_enqueue_scripts', 'samh_enqueue_scripts');

// Hook to remove menus
add_action('admin_menu', 'samh_remove_menus', PHP_INT_MAX);

/**
 * Capture all menus and remove selected ones.
 */
function samh_remove_menus()
{
	global $menu, $submenu, $samh_all_menus, $samh_all_submenus;

	if (isset($menu) && is_array($menu)) {
		$samh_all_menus = $menu;
	}
	if (isset($submenu) && is_array($submenu)) {
		$samh_all_submenus = $submenu;
	}

	// Hide Menus
	$hidden_menus = get_option('samh_hidden_menus', array());
	if (! empty($hidden_menus) && is_array($hidden_menus)) {
		foreach ($hidden_menus as $menu_slug) {
			remove_menu_page($menu_slug);
		}
	}

	// Apply Menu Order
	$menu_order = get_option('samh_menu_order', array());
	if (! empty($menu_order) && is_array($menu_order) && ! empty($menu) && is_array($menu)) {
		$new_menu = array();
		$menu_items_map = array();

		foreach ($menu as $priority => $item) {
			if (empty($item[2])) continue;
			$menu_items_map[$item[2]] = $item;
		}

		$priority_counter = 1;
		foreach ($menu_order as $slug) {
			if (isset($menu_items_map[$slug])) {
				$new_menu[$priority_counter++] = $menu_items_map[$slug];
				unset($menu_items_map[$slug]);
			}
		}

		foreach ($menu_items_map as $slug => $item) {
			$new_menu[$priority_counter++] = $item;
		}

		$menu = $new_menu;
	}

	// Apply Submenu Order
	$submenu_order = get_option('samh_submenu_order', array());
	if (! empty($submenu_order) && is_array($submenu_order) && ! empty($submenu) && is_array($submenu)) {
		foreach ($submenu_order as $parent_slug => $ordered_subs) {
			if (isset($submenu[$parent_slug]) && is_array($submenu[$parent_slug])) {
				$current_subs = $submenu[$parent_slug];
				$new_subs = array();
				$sub_map = array();

				foreach ($current_subs as $index => $item) {
					if (isset($item[2])) {
						$sub_map[$item[2]] = $item;
					}
				}

				foreach ($ordered_subs as $sub_slug) {
					if (isset($sub_map[$sub_slug])) {
						$new_subs[] = $sub_map[$sub_slug];
						unset($sub_map[$sub_slug]);
					}
				}

				foreach ($sub_map as $item) {
					$new_subs[] = $item;
				}

				$submenu[$parent_slug] = $new_subs;
			}
		}
	}

	// Remove Submenus
	$hidden_submenus = get_option('samh_hidden_submenus', array());
	if (! empty($hidden_submenus) && is_array($hidden_submenus)) {
		foreach ($hidden_submenus as $parent_slug => $sub_slugs) {
			if (! is_array($sub_slugs)) continue;
			foreach ($sub_slugs as $sub_slug) {
				remove_submenu_page($parent_slug, $sub_slug);
			}
		}
	}
}

// Hook to restrict access to hidden pages
add_action('admin_init', 'samh_restrict_access');

/**
 * Restrict access to hidden pages.
 */
function samh_restrict_access()
{
	// Do not restrict AJAX requests
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return;
	}

	// Do not restrict our own settings page just in case
	if (isset($_GET['page']) && 'simple-admin-menu-management' === $_GET['page']) {
		return;
	}

	$hidden_menus = get_option('samh_hidden_menus', array());
	$hidden_submenus = get_option('samh_hidden_submenus', array());

	$all_hidden_slugs = array();

	if (! empty($hidden_menus) && is_array($hidden_menus)) {
		$all_hidden_slugs = array_merge($all_hidden_slugs, $hidden_menus);
	}

	if (! empty($hidden_submenus) && is_array($hidden_submenus)) {
		foreach ($hidden_submenus as $parent => $subs) {
			if (is_array($subs)) {
				$all_hidden_slugs = array_merge($all_hidden_slugs, $subs);
			}
		}
	}

	// Remove duplicates
	$all_hidden_slugs = array_unique($all_hidden_slugs);

	if (empty($all_hidden_slugs)) {
		return;
	}

	global $pagenow;

	// Check against all hidden slugs
	foreach ($all_hidden_slugs as $menu_slug) {
		// Exact match
		if ($menu_slug === $pagenow) {
			if ('edit.php' === $pagenow) {
				$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
				if ('post' === $post_type) {
					samh_render_error_page();
				}
			} else {
				samh_render_error_page();
			}
		}

		// Plugin page match
		if (isset($_GET['page']) && $menu_slug === $_GET['page']) {
			samh_render_error_page();
		}

		// Query parameter match
		if (strpos($menu_slug, '?') !== false) {
			$parts = parse_url($menu_slug);
			$path = isset($parts['path']) ? $parts['path'] : '';
			$query = isset($parts['query']) ? $parts['query'] : '';

			if ($path === $pagenow && ! empty($query)) {
				parse_str($query, $query_vars);
				$match = true;
				foreach ($query_vars as $key => $value) {
					if (! isset($_GET[$key]) || $_GET[$key] !== $value) {
						$match = false;
						break;
					}
				}
				if ($match) {
					samh_render_error_page();
				}
			}
		}
	}
}
