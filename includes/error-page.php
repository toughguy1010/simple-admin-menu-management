<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Render a friendly custom error page.
 */
function samh_render_error_page()
{
    if (! headers_sent()) {
        status_header(403);
        nocache_headers();
        header('Content-Type: text/html; charset=utf-8');
    }

    $admin_url = admin_url();
    $plugin_settings_url = admin_url('options-general.php?page=simple-admin-menu-hider');

    // Check capability
    $capability = defined('SAMH_CAPABILITY') ? SAMH_CAPABILITY : 'manage_options';
    $is_admin = current_user_can($capability);

?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php _e('Access Restricted', 'simple-admin-menu-hider'); ?></title>
        <link rel="stylesheet" href="<?php echo SAMH_PLUGIN_URL . 'assets/css/error-page.css?ver=' . time(); ?>">
    </head>

    <body>
        <div class="samh-error-card">
            <div class="samh-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
            </div>

            <h1><?php _e('Module Hidden', 'simple-admin-menu-hider'); ?></h1>
            <p>
                <?php _e('This menu item has been hidden by the <strong>Simple Admin Menu Hider</strong> plugin.', 'simple-admin-menu-hider'); ?>
            </p>

            <?php if (! $is_admin) : ?>
                <div class="samh-warning">
                    <?php _e('Please contact your administrator to request access.', 'simple-admin-menu-hider'); ?>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($admin_url); ?>" class="button">
                <?php _e('Go to Dashboard', 'simple-admin-menu-hider'); ?>
            </a>

            <?php if ($is_admin) : ?>
                <div class="footer-link">
                    <?php printf(
                        __('Configured via <a href="%s">Simple Admin Menu Hider</a>', 'simple-admin-menu-hider'),
                        esc_url($plugin_settings_url)
                    ); ?>
                </div>
            <?php endif; ?>
        </div>
    </body>

    </html>
<?php
    die();
}
