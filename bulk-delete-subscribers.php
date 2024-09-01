<?php
/*
Plugin Name: Bulk Delete Subscribers
Plugin URI: https://example.com/bulk-delete-subscribers
Description: **Bulk Delete Subscribers** is a simple yet powerful plugin that allows administrators to delete all users with the 'subscriber' role in bulk while excluding administrators and authors. Ideal for cleaning up spam accounts or managing user roles effectively. ⚠️ **Important:** Ensure that you have a backup of your site before using this plugin to avoid accidental data loss. This plugin permanently deletes users, and deleted users cannot be recovered. 🔍 **Features:** - Bulk delete subscribers while preserving admins and authors. - Provides a simple interface in the WordPress admin panel. - Displays the current logged-in user as the reassignment target for orphaned content.
Version: 1.0
Author: Rakibul Hashan Rabbi
Author URI: https://example.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bulk-delete-subscribers
Domain Path: /languages
*/

// Hook to add admin menu
add_action('admin_menu', 'bulk_delete_subscribers_menu');

function bulk_delete_subscribers_menu() {
    add_menu_page(
        'Bulk Delete Subscribers', // Page title
        'Bulk Delete Subscribers', // Menu title
        'manage_options',          // Capability
        'bulk-delete-subscribers', // Menu slug
        'bulk_delete_subscribers_page' // Callback function
    );
}

function bulk_delete_subscribers_page() {
    global $wpdb;

    // Check if form is submitted
    if (isset($_POST['delete_subscribers'])) {
        // Sanitize and validate nonce
        $nonce = isset($_POST['bulk_delete_subscribers_nonce']) ? sanitize_text_field(wp_unslash($_POST['bulk_delete_subscribers_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'bulk_delete_subscribers_action')) {
            wp_die('Nonce verification failed');
        }

        // Sanitize the delete_subscribers input
        $delete_subscribers = isset($_POST['delete_subscribers']) ? sanitize_text_field(wp_unslash($_POST['delete_subscribers'])) : '';

        // Retrieve the user IDs using the get_users() function
        $args = array(
            'role'    => 'subscriber',
            'exclude' => array('administrator', 'author'),
        );
        $users = get_users($args);
        $user_ids = array();
        foreach ($users as $user) {
            $user_ids[] = $user->ID;
        }

        // Cache the user IDs
        wp_cache_set('bulk_delete_subscribers_user_ids', $user_ids);

        if (!empty($user_ids)) {
            foreach ($user_ids as $user_id) {
                wp_delete_user($user_id);
            }
            echo '<div class="updated notice"><p>Subscribers deleted successfully!</p></div>';
        } else {
            echo '<div class="error notice"><p>No subscribers found to delete.</p></div>';
        }
    }

    ?>

    <div class="wrap">
        <h1>Bulk Delete Subscribers</h1>
        
        <div class="notice notice-warning">
            <p><strong>⚠️ Important:</strong> Before proceeding, please ensure you have a complete backup of your site. This plugin will permanently delete all users with the 'subscriber' role, excluding administrators and authors. Once deleted, users cannot be recovered.</p>
            <p><strong>🔍 Features:</strong></p>
            <ul>
                <li>Bulk delete subscribers while preserving admins and authors.</li>
                <li>Provides a simple interface in the WordPress admin panel.</li>
                <li>Displays the current logged-in user as the reassignment target for orphaned content.</li>
            </ul>
        </div>

        <form method="post" onsubmit="return confirmDelete();">
            <?php wp_nonce_field('bulk_delete_subscribers_action', 'bulk_delete_subscribers_nonce'); ?>
            <p>
                <input type="submit" name="delete_subscribers" class="button button-primary" value="Delete Subscribers">
            </p>
        </form>
    </div>

    <script type="text/javascript">
        function confirmDelete() {
            return confirm('⚠️ Are you sure you want to delete all subscribers? This action cannot be undone. Please ensure you have a backup of your site.');
        }
    </script>

    <?php
}
?>
