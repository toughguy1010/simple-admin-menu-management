<?php
// Expects: $scan_menu, $scan_submenu, $saved_hidden_menus, $saved_hidden_submenus, $saved_menu_order
?>
<?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'): ?>
    <div class="notice notice-success is-dismissible samh-notice">
        <p>
            <span class="dashicons dashicons-yes-alt"></span>
            <?php _e('Settings saved successfully.', 'simple-admin-menu-management'); ?>
        </p>
    </div>
<?php endif; ?>
<div class="wrap samh-wrapper">
    <div class="samh-header">
        <h1 class="samh-title">Menu Manager</h1>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('samh_save_settings', 'samh_nonce'); ?>

        <div class="samh-card">
            <p class="description" style="margin-bottom: 20px; font-size: 15px;">
                <?php _e('Drag and drop to reorder. Toggle switches to hide. Red indicates hidden.', 'simple-admin-menu-management'); ?>
            </p>

            <input type="hidden" name="samh_menu_order" id="samh_menu_order" value="<?php echo esc_attr(implode(',', $saved_menu_order)); ?>">
            <input type="hidden" name="samh_submenu_order" id="samh_submenu_order" value="">

            <?php if (! empty($scan_menu)) : ?>
                <div class="samh-grid">
                    <?php foreach ($scan_menu as $item) : ?>
                        <?php
                        if (empty($item[0])) continue;
                        if (isset($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) continue;

                        if (! empty($item[1]) && ! current_user_can($item[1])) continue;

                        $slug = $item[2];

                        $name_parts = explode('<', $item[0]);
                        $name = trim($name_parts[0]);
                        if (empty($name)) $name = strip_tags($item[0]);

                        if (empty($slug)) continue;

                        // Icon Parsing
                        $icon_class = 'dashicons-admin-generic';
                        $icon_style = '';
                        $icon_html  = '';
                        if (! empty($item[6])) {
                            $icon_data = $item[6];
                            if ('div' === $icon_data) {
                                $icon_html = '<div class="dashicons dashicons-admin-generic"></div>';
                            } elseif ('none' === $icon_data) {
                                $icon_html = '<div class="dashicons dashicons-admin-generic"></div>';
                            } elseif (strpos($icon_data, 'data:image/svg+xml;base64') === 0) {
                                $clean_icon_data = str_replace(array("\r", "\n", " "), "", $icon_data);
                                $icon_style = 'background-image:url(\'' . $clean_icon_data . '\');';
                                $icon_html  = '<div class="samh-icon-svg" style="' . $icon_style . '"></div>';
                            } elseif (filter_var($icon_data, FILTER_VALIDATE_URL)) {
                                $icon_html = '<img src="' . esc_url($icon_data) . '" class="samh-icon-img" />';
                            } else {
                                $icon_class = esc_attr($icon_data);
                                $icon_html = '<div class="dashicons ' . $icon_class . '"></div>';
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
                                        if (empty($sub_item[0])) continue;
                                        if (! current_user_can($sub_item[1])) continue;

                                        $sub_name_parts = explode('<', $sub_item[0]);
                                        $sub_name = trim($sub_name_parts[0]);
                                        if (empty($sub_name)) $sub_name = strip_tags($sub_item[0]);
                                        $sub_slug = $sub_item[2];

                                        $sub_checked = '';
                                        if (isset($saved_hidden_submenus[$slug]) && in_array($sub_slug, $saved_hidden_submenus[$slug])) {
                                            $sub_checked = 'checked';
                                        }
                                        ?>
                                        <div class="samh-submenu-item" data-slug="<?php echo esc_attr($sub_slug); ?>">
                                            <div class="samh-label-text" style=" display: flex; align-items: flex-start; flex-direction: row;">
                                                <span class="samh-submenu-drag-handle dashicons dashicons-menu" style="font-size: 20px;" title="<?php _e('Drag to reorder', 'simple-admin-menu-management'); ?>"></span>
                                                <div style="display: flex; align-items: flex-start; flex-direction: column;">
                                                    <span><?php echo esc_html($sub_name); ?></span>
                                                    <span class="samh-slug"><?php echo esc_html($sub_slug); ?></span>
                                                </div>
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
</div>
</form>