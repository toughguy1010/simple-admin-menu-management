<?php
if (! defined('ABSPATH')) {
    exit;
}

// Define the required capability to access and manage this plugin
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
        'samh_render_settings_page'
    );
}
add_action('admin_menu', 'samh_register_settings_page');

add_action('admin_init', 'samh_handle_save');

/**
 * Handle settings save.
 */
function samh_handle_save()
{
    // Handle settings save
    if (isset($_POST['samh_submit']) && check_admin_referer('samh_save_settings', 'samh_nonce')) {
        // Capability check
        if (! current_user_can(SAMH_CAPABILITY)) {
            wp_die(__('You do not have permission to manage these settings.', 'simple-admin-menu-management'));
        }

        // Save Top Menus
        $hidden = isset($_POST['samh_hidden_menus']) ? (array) $_POST['samh_hidden_menus'] : array();
        $hidden = array_map('sanitize_text_field', $hidden);
        update_option('samh_hidden_menus', $hidden);

        // Save Submenus
        // Expected format: samh_hidden_submenus[parent_slug][] = sub_slug
        $hidden_submenus = isset($_POST['samh_hidden_submenus']) ? (array) $_POST['samh_hidden_submenus'] : array();

        // Sanitize nested array
        $sanitized_submenus = array();
        foreach ($hidden_submenus as $parent => $subs) {
            if (is_array($subs)) {
                $sanitized_submenus[sanitize_text_field($parent)] = array_map('sanitize_text_field', $subs);
            }
        }
        update_option('samh_hidden_submenus', $sanitized_submenus);

        // Save Menu Order
        $menu_order_str = isset($_POST['samh_menu_order']) ? sanitize_text_field($_POST['samh_menu_order']) : '';
        if (! empty($menu_order_str)) {
            $menu_order = explode(',', $menu_order_str);
            $menu_order = array_map('sanitize_text_field', $menu_order);
            update_option('samh_menu_order', $menu_order);
        }

        // Redirect to same page with updated status
        wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }
}

/**
 * Render the settings page.
 */
function samh_render_settings_page()
{
    // Capability check barrier
    if (! current_user_can(SAMH_CAPABILITY)) {
        wp_die(__('You do not have permission to access this page.', 'simple-admin-menu-management'));
    }

    // Show notice if settings updated
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        echo '<div class="notice notice-success is-dismissible samh-notice">
				<p>
					<span class="dashicons dashicons-yes-alt"></span> 
					' . __('Settings saved successfully.', 'simple-admin-menu-management') . '
				</p>
			  </div>';
    }

    // Get current hidden menus
    $saved_hidden_menus = get_option('samh_hidden_menus', array());
    $saved_hidden_submenus = get_option('samh_hidden_submenus', array());
    $saved_menu_order = get_option('samh_menu_order', array());

    // Get all available menus
    // $samh_all_menus is populated in samh_remove_menus at PHP_INT_MAX
    global $samh_all_menus, $samh_all_submenus;

    if (empty($samh_all_menus)) {
        global $menu;
        $scan_menu = $menu;
    } else {
        $scan_menu = $samh_all_menus;
    }

    $scan_submenu = ! empty($samh_all_submenus) ? $samh_all_submenus : (isset($GLOBALS['submenu']) ? $GLOBALS['submenu'] : array());

    // Sort by key (position) to match sidebar order
    if (! empty($scan_menu)) {
        ksort($scan_menu);
    }

    // Apply saved menu order to match the actual sidebar order
    if (! empty($saved_menu_order) && is_array($saved_menu_order) && ! empty($scan_menu)) {
        $ordered_menu = array();
        $menu_map = array();

        // Index by slug
        foreach ($scan_menu as $item) {
            if (! empty($item[2])) {
                $menu_map[$item[2]] = $item;
            }
        }

        // Reorder based on saved order
        foreach ($saved_menu_order as $slug) {
            if (isset($menu_map[$slug])) {
                $ordered_menu[] = $menu_map[$slug];
                unset($menu_map[$slug]);
            }
        }

        // Append remaining items
        foreach ($menu_map as $item) {
            $ordered_menu[] = $item;
        }

        $scan_menu = $ordered_menu;
    }

?>
    <div class="wrap samh-wrapper">
        <div class="samh-header">
            <h1 class="samh-title"><?php _e('Menu Manager', 'simple-admin-menu-management'); ?></h1>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('samh_save_settings', 'samh_nonce'); ?>

            <div class="samh-card">
                <p class="description" style="margin-bottom: 20px; font-size: 15px;">
                    <?php _e('Drag and drop to reorder. Toggle switches to hide. Red indicates hidden.', 'simple-admin-menu-management'); ?>
                </p>

                <input type="hidden" name="samh_menu_order" id="samh_menu_order" value="<?php echo esc_attr(implode(',', $saved_menu_order)); ?>">

                <?php if (! empty($scan_menu)) : ?>
                    <div class="samh-grid">
                        <?php foreach ($scan_menu as $item) : ?>
                            <?php
                            // $item format: [0] => Name, [1] => Capability, [2] => Slug, ...
                            // Filter out separators
                            if (empty($item[0])) continue;
                            if (isset($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) continue;

                            // Check Capability
                            if (! empty($item[1]) && ! current_user_can($item[1])) continue;

                            $slug = $item[2];

                            // Clean Name
                            $name_parts = explode('<', $item[0]);
                            $name = trim($name_parts[0]);
                            if (empty($name)) $name = strip_tags($item[0]); // Fallback

                            if (empty($slug)) continue;

                            // Icon Handling
                            $icon_class = 'dashicons-admin-generic'; // Default
                            $icon_style = '';
                            $icon_html  = '';

                            if (! empty($item[6])) {
                                $icon_data = $item[6];
                                if ('div' === $icon_data) {
                                    $icon_html = '<div class="dashicons dashicons-admin-generic"></div>';
                                } elseif ('none' === $icon_data) {
                                    $icon_html = '<div class="dashicons dashicons-admin-generic"></div>';
                                } elseif (strpos($icon_data, 'data:image/svg+xml;base64') === 0) {
                                    // SVG Base64
                                    $icon_style = 'background-image:url(\'' . esc_url($icon_data) . '\');';
                                    $icon_html  = '<div class="samh-icon-svg" style="' . $icon_style . '"></div>';
                                } elseif (strpos($icon_data, 'dashicons-') === 0) {
                                    // Dashicon Class
                                    $icon_html = '<div class="dashicons ' . esc_attr($icon_data) . '"></div>';
                                } elseif (filter_var($icon_data, FILTER_VALIDATE_URL)) {
                                    // Image URL
                                    $icon_html = '<img src="' . esc_url($icon_data) . '" class="samh-icon-img" />';
                                } else {
                                    // Default/Fallback
                                    $icon_html = '<div class="dashicons dashicons-admin-generic"></div>';
                                }
                            } else {
                                $icon_html = '<div class="dashicons dashicons-admin-generic"></div>';
                            }

                            $checked = in_array($slug, $saved_hidden_menus) ? 'checked' : '';

                            // Check for Submenus
                            $has_submenus = isset($scan_submenu[$slug]) && ! empty($scan_submenu[$slug]);
                            ?>
                            <div class="samh-menu-item <?php echo $has_submenus ? 'has-submenus' : ''; ?>" data-slug="<?php echo esc_attr($slug); ?>">
                                <div class="samh-menu-header">
                                    <div class="samh-menu-header-left">
                                        <div class="samh-drag-handle" title="<?php _e('Drag to reorder', 'simple-admin-menu-management'); ?>">
                                            <span class="dashicons dashicons-menu"></span>
                                        </div>
                                        <div class="samh-label">
                                            <div class="samh-label-icon">
                                                <?php echo $icon_html; ?>
                                            </div>
                                            <div class="samh-label-text">
                                                <span><?php echo esc_html($name); ?></span>
                                                <span class="samh-slug"><?php echo esc_html($slug); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="samh-menu-header-right">
                                        <label class="samh-toggle">
                                            <input type="checkbox" name="samh_hidden_menus[]" value="<?php echo esc_attr($slug); ?>" <?php echo $checked; ?>>
                                            <span class="samh-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <?php if ($has_submenus) : ?>
                                    <div class="samh-submenu-list">
                                        <?php foreach ($scan_submenu[$slug] as $sub_item) : ?>
                                            <?php
                                            // Submenu format: [0] => Name, [1] => Capability, [2] => Slug
                                            if (empty($sub_item[0])) continue;
                                            if (! current_user_can($sub_item[1])) continue;

                                            $sub_name = strip_tags($sub_item[0]);
                                            $sub_slug = $sub_item[2];

                                            $sub_checked = '';
                                            if (isset($saved_hidden_submenus[$slug]) && in_array($sub_slug, $saved_hidden_submenus[$slug])) {
                                                $sub_checked = 'checked';
                                            }
                                            ?>
                                            <div class="samh-submenu-item">
                                                <div class="samh-label-text">
                                                    <span><?php echo esc_html($sub_name); ?></span>
                                                    <span class="samh-slug"><?php echo esc_html($sub_slug); ?></span>
                                                </div>
                                                <label class="samh-toggle samh-toggle-sm">
                                                    <input type="checkbox" name="samh_hidden_submenus[<?php echo esc_attr($slug); ?>][]" value="<?php echo esc_attr($sub_slug); ?>" <?php echo $sub_checked; ?>>
                                                    <span class="samh-slider"></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p><?php _e('No menus found.', 'simple-admin-menu-management'); ?></p>
                <?php endif; ?>

                <div class="samh-actions">
                    <input type="submit" name="samh_submit" id="submit" class="button button-primary button-hero" value="<?php _e('Save Changes', 'simple-admin-menu-management'); ?>">
                </div>
            </div>
        </form>
    </div>
<?php
}
