<?php
/**
 * Plugin Name: Custom Labels for WordPress Posts
 * Plugin Uri: https://github.com/narendr11/custom_labels_for_wp_posts
 * Author: Narendra Sishodiya(narenin), Studio Kadoosje
 * Author Uri: https://profiles.wordpress.org/narenin/
 * Version: 1.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: Adds custom labels to Posts, Pages, and Projects with a colored column in admin screens and Quick Edit support.
 * Tags: Custom Tag for Projects, Custom Tag for Pages, Custom Tag for Posts, Label to Admin Screen, Label
 */

defined('ABSPATH') || die('You are trying to enter restricted file!');

// Add Meta Box to Posts, Pages, and Projects
add_action('admin_init', 'plugin_custom_label_meta_box');
function plugin_custom_label_meta_box() {
    add_meta_box(
        'custom_label_field',
        'Label',
        'plugin_custom_label_meta_box_fun',
        ['post', 'page', 'project'],
        'side',
        'high'
    );
}

function plugin_custom_label_meta_box_fun($post) {
    $meta_value = get_post_meta($post->ID, '_input_meta_box', true) ?: '';
    ?>
    <h2 style="padding-left:0px">Add a Custom Label here:</h2>
    <input type="text" id="_input_meta_box" name="_input_meta_box" value="<?php echo esc_attr($meta_value); ?>" />
    <?php
}

// Save Meta Box Data
add_action('save_post', 'save_meta_box', 10, 3);
function save_meta_box($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    // Verify nonce (for both full edit and Quick Edit)
    if (!isset($_POST['custom_label_nonce']) || !wp_verify_nonce($_POST['custom_label_nonce'], 'save_custom_label')) {
        return;
    }
    if (isset($_POST['_input_meta_box'])) {
        $label = sanitize_text_field($_POST['_input_meta_box']);
        update_post_meta($post_id, '_input_meta_box', $label);
        error_log('Custom Label Saved for Post ID ' . $post_id . ': ' . $label);
    }
}

// Add Nonce Field to Meta Box
add_action('edit_form_after_title', 'add_custom_label_nonce');
function add_custom_label_nonce() {
    wp_nonce_field('save_custom_label', 'custom_label_nonce');
}

// Add Custom Label Column to Posts, Pages, and Projects Admin Screens
function custom_label_add_id_column($columns) {
    $columns['custom_label'] = 'Label';
    return $columns;
}

// Display Custom Label in the Column
function custom_label_id_column_content($column, $post_id) {
    if ('custom_label' == $column) {
        $label = get_post_meta($post_id, '_input_meta_box', true);
        error_log('Custom Label Retrieved for Post ID ' . $post_id . ': ' . ($label ?: 'No label found'));
        if ($label) {
            $label_class = 'label-' . sanitize_html_class(strtolower(str_replace(' ', '-', $label)));
            echo '<span class="' . esc_attr($label_class) . '" data-label="' . esc_attr($label) . '">' . esc_html($label) . '</span>';
        } else {
            echo '<span class="label-none" data-label="">No Label</span>';
        }
    }
}

// Dynamically Add Columns to All Specified Post Types
add_action('admin_init', 'register_custom_label_columns');
function register_custom_label_columns() {
    $post_types = ['post', 'page', 'project'];
    foreach ($post_types as $post_type) {
        if (post_type_exists($post_type)) {
            add_filter("manage_{$post_type}_posts_columns", 'custom_label_add_id_column', 5);
            add_action("manage_{$post_type}_posts_custom_column", 'custom_label_id_column_content', 5, 2);
            // Add Quick Edit support
            add_action("quick_edit_custom_box", 'custom_label_quick_edit_box', 10, 2);
        }
    }
}

// Add Custom Label Field to Quick Edit
function custom_label_quick_edit_box($column_name, $post_type) {
    if ($column_name !== 'custom_label' || !in_array($post_type, ['post', 'page', 'project'])) {
        return;
    }
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label>
                <span class="title">Label</span>
                <input type="text" name="_input_meta_box" class="inline-edit-custom-label" value="" />
            </label>
            <?php wp_nonce_field('save_custom_label', 'custom_label_nonce'); ?>
        </div>
    </fieldset>
    <?php
}

// Enqueue JavaScript for Quick Edit
add_action('admin_enqueue_scripts', 'custom_label_quick_edit_js');
function custom_label_quick_edit_js($hook) {
    if ($hook !== 'edit.php') {
        return;
    }
    wp_enqueue_script('custom-label-quick-edit', plugin_dir_url(__FILE__) . 'quick-edit.js', ['jquery', 'inline-edit-post'], '1.0.3', true);
}

// Add CSS for Colored Labels
add_action('admin_head', 'custom_label_admin_styles');
function custom_label_admin_styles() {
    ?>
    <style>
        .column-custom_label {
            width: 120px;
        }
        .column-custom_label span {
            padding: 3px 8px;
            border-radius: 3px;
            display: inline-block;
        }
        .label-to-do {
            background-color: #ffcc00; /* Yellow */
            color: #000;
        }
        .label-needs-review {
            background-color: #ff6666; /* Red */
            color: #fff;
        }
        .label-in-progress {
            background-color: #3399ff; /* Blue */
            color: #fff;
        }
        .label-done {
            background-color: #33cc33; /* Green */
            color: #fff;
        }
        .label-none {
            color: #999;
            opacity: 0.2;
            background-color: transparent;
        }
    </style>
    <?php
}
?>
