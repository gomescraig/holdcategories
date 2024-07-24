<?php
/*
Plugin Name: Hold Categories
Description: Ensures that posts retain their categories and enforces that categories remain intact when set.
Version: 1.0
Author: Craig Gomes
*/

// Hook into the save_post action
add_action('save_post', 'ensure_categories_intact');

function ensure_categories_intact($post_id) {
    // Verify this is not an auto-save routine.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Verify user permissions
    if (!current_user_can('edit_post', $post_id)) return;

    // Get the post categories
    $categories = wp_get_post_categories($post_id);

    // Check if the post has categories assigned
    if (empty($categories)) {
        // Assign a default category if none is assigned
        $default_category = get_option('default_category');
        wp_set_post_categories($post_id, array($default_category));
    }
}

// Hook into the pre_delete_term action
add_action('pre_delete_term', 'prevent_category_deletion', 10, 2);

function prevent_category_deletion($term, $taxonomy) {
    if ($taxonomy == 'category') {
        // Prevent category deletion if it is assigned to any post
        $posts = get_posts(array(
            'category' => $term,
            'numberposts' => 1,
            'post_type' => 'any',
            'post_status' => 'any'
        ));

        if (!empty($posts)) {
            wp_die('This category is assigned to one or more posts and cannot be deleted.');
        }
    }
}
