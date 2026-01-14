<?php

/**
 * Plugin Name: Simple Admin Menu Hider
 * Plugin URI:  https://example.com
 * Description: A simple plugin to hide admin menu items.
 * Version:     1.0.0
 * Author:      Duc Pham
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 * Text Domain: simple-admin-menu-hider
 */

if (! defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('SAMH_VERSION', '1.0.0');
define('SAMH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SAMH_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include settings page
require_once SAMH_PLUGIN_DIR . 'includes/settings-page.php';
// Include error page
require_once SAMH_PLUGIN_DIR . 'includes/error-page.php';

// Global to store all menus before they are hidden
global $samh_all_menus, $samh_all_submenus;
$samh_all_menus = array();
$samh_all_submenus = array();

/**
 * Enqueue scripts and styles.
 */
function samh_enqueue_scripts($hook)
{
	if ('settings_page_simple-admin-menu-hider' !== $hook) {
		return;
	}
	wp_enqueue_style('samh-admin-style', SAMH_PLUGIN_URL . 'assets/css/admin-style.css', array(), SAMH_VERSION);
	wp_enqueue_script('samh-admin-script', SAMH_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), SAMH_VERSION, true);
}
add_action('admin_enqueue_scripts', 'samh_enqueue_scripts');

// Hook to remove menus (Priority PHP_INT_MAX ensures we run last)
add_action('admin_menu', 'samh_remove_menus', PHP_INT_MAX);

/**
 * Capture all menus and then remove selected ones.
 */
function samh_remove_menus()
{
	global $menu, $submenu, $samh_all_menus, $samh_all_submenus;

	// Capture menus exactly as they are right before we start removing things
	if (isset($menu) && is_array($menu)) {
		$samh_all_menus = $menu;
	}
	if (isset($submenu) && is_array($submenu)) {
		$samh_all_submenus = $submenu;
	}

	// Remove Top Level Menus
	$hidden_menus = get_option('samh_hidden_menus', array());
	if (! empty($hidden_menus) && is_array($hidden_menus)) {
		foreach ($hidden_menus as $menu_slug) {
			remove_menu_page($menu_slug);
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
	if (isset($_GET['page']) && 'simple-admin-menu-hider' === $_GET['page']) {
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
		// Case 1: Standard PHP file match (e.g. tools.php)
		if ($menu_slug === $pagenow) {
			// Special handling for edit.php (Posts) to avoid blocking other post types (Pages, etc.)
			if ('edit.php' === $pagenow) {
				$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
				if ('post' === $post_type) {
					samh_render_error_page();
				}
			} else {
				// For all other pages, strict pagenow match is usually sufficient
				samh_render_error_page();
			}
		}

		// Case 2: Plugin page match (e.g. page=my-plugin)
		if (isset($_GET['page']) && $menu_slug === $_GET['page']) {
			samh_render_error_page();
		}

		// Case 3: Menu slug with query parameters (e.g. edit.php?post_type=page)
		if (strpos($menu_slug, '?') !== false) {
			$parts = parse_url($menu_slug);
			$path = isset($parts['path']) ? $parts['path'] : '';
			$query = isset($parts['query']) ? $parts['query'] : '';

			// If the base file matches the current page
			if ($path === $pagenow && ! empty($query)) {
				parse_str($query, $query_vars);
				$match = true;
				// Check if all params in the menu slug are present in current $_GET
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
