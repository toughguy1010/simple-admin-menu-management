<?php
if (! defined('ABSPATH')) {
    exit;
}

// Define the required capability
define('SAMH_CAPABILITY', 'update_core');

/**
 * Register the settings menu.
 */
function samh_register_settings_page()
{
    add_options_page(
        __('Admin Sidebar Menu Manager', 'simple-admin-menu-management'),
        __('Menu Manager', 'simple-admin-menu-management'),
        SAMH_CAPABILITY,
        'simple-admin-menu-management',
        'samh_render_sidebar_page'
    );
}
add_action('admin_menu', 'samh_register_settings_page');

add_action('admin_init', 'samh_handle_save');

/**
 * Handle settings save.
 */
function samh_handle_save()
{
    if (isset($_POST['samh_submit']) && check_admin_referer('samh_save_settings', 'samh_nonce')) {
        if (! current_user_can(SAMH_CAPABILITY)) {
            wp_die(__('You do not have permission to manage these settings.', 'simple-admin-menu-management'));
        }

        $hidden = isset($_POST['samh_hidden_menus']) ? (array) $_POST['samh_hidden_menus'] : array();
        $hidden = array_map('sanitize_text_field', $hidden);
        update_option('samh_hidden_menus', $hidden);

        $hidden_submenus = isset($_POST['samh_hidden_submenus']) ? (array) $_POST['samh_hidden_submenus'] : array();
        $sanitized_submenus = array();
        foreach ($hidden_submenus as $parent => $subs) {
            if (is_array($subs)) {
                $sanitized_submenus[sanitize_text_field($parent)] = array_map('sanitize_text_field', $subs);
            }
        }
        update_option('samh_hidden_submenus', $sanitized_submenus);

        $menu_order_str = isset($_POST['samh_menu_order']) ? sanitize_text_field($_POST['samh_menu_order']) : '';
        if (! empty($menu_order_str)) {
            $menu_order = explode(',', $menu_order_str);
            $menu_order = array_map('sanitize_text_field', $menu_order);
            update_option('samh_menu_order', $menu_order);
        }

        $submenu_order_json = isset($_POST['samh_submenu_order']) ? wp_unslash($_POST['samh_submenu_order']) : '';
        if (! empty($submenu_order_json)) {
            $submenu_order = json_decode($submenu_order_json, true);
            if (is_array($submenu_order)) {
                $clean_submenu_order = array();
                foreach ($submenu_order as $p => $subs) {
                    if (is_array($subs)) {
                        $clean_submenu_order[sanitize_text_field($p)] = array_map('sanitize_text_field', $subs);
                    }
                }
                update_option('samh_submenu_order', $clean_submenu_order);
            }
        }

        wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }
}

/**
 * Render the sidebar settings page (Controller Logic).
 */
function samh_render_sidebar_page()
{
    if (! current_user_can(SAMH_CAPABILITY)) {
        wp_die(__('You do not have permission to access this page.', 'simple-admin-menu-management'));
    }

    // Prepare data for the view
    $saved_hidden_menus = get_option('samh_hidden_menus', array());
    $saved_hidden_submenus = get_option('samh_hidden_submenus', array());
    $saved_menu_order = get_option('samh_menu_order', array());
    $saved_submenu_order = get_option('samh_submenu_order', array());

    global $samh_all_menus, $samh_all_submenus;

    if (empty($samh_all_menus)) {
        global $menu;
        $scan_menu = $menu;
    } else {
        $scan_menu = $samh_all_menus;
    }

    $scan_submenu = ! empty($samh_all_submenus) ? $samh_all_submenus : (isset($GLOBALS['submenu']) ? $GLOBALS['submenu'] : array());

    // Sort Submenus
    if (! empty($saved_submenu_order) && is_array($saved_submenu_order)) {
        foreach ($saved_submenu_order as $parent_slug => $ordered_subs) {
            if (isset($scan_submenu[$parent_slug]) && is_array($scan_submenu[$parent_slug])) {
                $current_subs = $scan_submenu[$parent_slug];
                $reordered = array();
                $sub_map = array();
                foreach ($current_subs as $index => $item) {
                    if (isset($item[2])) {
                        $sub_map[$item[2]] = $item;
                    }
                }
                foreach ($ordered_subs as $sub_slug) {
                    if (isset($sub_map[$sub_slug])) {
                        $reordered[] = $sub_map[$sub_slug];
                        unset($sub_map[$sub_slug]);
                    }
                }
                foreach ($sub_map as $item) {
                    $reordered[] = $item;
                }
                $scan_submenu[$parent_slug] = $reordered;
            }
        }
    }

    // Sort Menus
    if (! empty($scan_menu)) {
        ksort($scan_menu);
    }

    if (! empty($saved_menu_order) && is_array($saved_menu_order) && ! empty($scan_menu)) {
        $ordered_menu = array();
        $menu_map = array();
        foreach ($scan_menu as $item) {
            if (! empty($item[2])) {
                $menu_map[$item[2]] = $item;
            }
        }
        foreach ($saved_menu_order as $slug) {
            if (isset($menu_map[$slug])) {
                $ordered_menu[] = $menu_map[$slug];
                unset($menu_map[$slug]);
            }
        }
        foreach ($menu_map as $item) {
            $ordered_menu[] = $item;
        }
        $scan_menu = $ordered_menu;
    }

    require_once SAMH_PLUGIN_DIR . 'includes/views/settings-view.php';
}
