<?php
/*
Plugin Name: ClaimReview Generator
Plugin URI: https://example.com/plugins/claim-review-generator
Description: Generates ClaimReview schema for fact-checking articles and debunks
Version: 2.0
Author: Your Name
Author URI: https://example.com
*/

// Include the negacion_a_afirmacion_simple function
include_once(plugin_dir_path(__FILE__) . 'negacion_a_afirmacion_simple.php');

// Add settings page
function crg_add_settings_page() {
    add_options_page('ClaimReview Generator Settings', 'ClaimReview Generator', 'manage_options', 'crg-settings', 'crg_render_settings_page');
}
add_action('admin_menu', 'crg_add_settings_page');

// Render settings page
function crg_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>ClaimReview Generator Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('crg_settings');
            do_settings_sections('crg-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function crg_register_settings() {
    register_setting('crg_settings', 'crg_fact_check_tag');
    register_setting('crg_settings', 'crg_debunk_tag');
    register_setting('crg_settings', 'crg_organization_name');
    register_setting('crg_settings', 'crg_organization_logo');
    register_setting('crg_settings', 'crg_debunk_author');
    register_setting('crg_settings', 'crg_best_rating');
    register_setting('crg_settings', 'crg_worst_rating');
    register_setting('crg_settings', 'crg_true_alternate_name');
    register_setting('crg_settings', 'crg_false_alternate_name');
    register_setting('crg_settings', 'crg_rating_method');
    register_setting('crg_settings', 'crg_rating_custom_function');
    register_setting('crg_settings', 'crg_rating_url_patterns');
    register_setting('crg_settings', 'crg_rating_category_patterns');
    register_setting('crg_settings', 'crg_rating_content_patterns');

    add_settings_section('crg_main_section', 'Main Settings', null, 'crg-settings');

    add_settings_field('crg_fact_check_tag', 'Fact-check Tag', 'crg_fact_check_tag_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_debunk_tag', 'Debunk Tag', 'crg_debunk_tag_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_organization_name', 'Organization Name', 'crg_organization_name_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_organization_logo', 'Organization Logo URL', 'crg_organization_logo_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_debunk_author', 'Debunk Author', 'crg_debunk_author_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_best_rating', 'Best Rating', 'crg_best_rating_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_worst_rating', 'Worst Rating', 'crg_worst_rating_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_true_alternate_name', 'True Alternate Name', 'crg_true_alternate_name_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_false_alternate_name', 'False Alternate Name', 'crg_false_alternate_name_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_method', 'Rating Extraction Method', 'crg_rating_method_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_custom_function', 'Custom Rating Function', 'crg_rating_custom_function_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_url_patterns', 'URL Rating Patterns', 'crg_rating_url_patterns_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_category_patterns', 'Category Rating Patterns', 'crg_rating_category_patterns_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_content_patterns', 'Content Rating Patterns', 'crg_rating_content_patterns_callback', 'crg-settings', 'crg_main_section');
}
add_action('admin_init', 'crg_register_settings');

// Settings callbacks
function crg_fact_check_tag_callback() {
    $tag = get_option('crg_fact_check_tag');
    echo "<input type='text' name='crg_fact_check_tag' value='$tag' />";
}

function crg_debunk_tag_callback() {
    $tag = get_option('crg_debunk_tag');
    echo "<input type='text' name='crg_debunk_tag' value='$tag' />";
}

function crg_organization_name_callback() {
    $name = get_option('crg_organization_name');
    echo "<input type='text' name='crg_organization_name' value='$name' />";
}

function crg_organization_logo_callback() {
    $logo = get_option('crg_organization_logo');
    echo "<input type='text' name='crg_organization_logo' value='$logo' />";
}

function crg_debunk_author_callback() {
    $author = get_option('crg_debunk_author', 'Social media');
    echo "<input type='text' name='crg_debunk_author' value='$author' />";
}

function crg_best_rating_callback() {
    $best = get_option('crg_best_rating', 5);
    echo "<input type='number' name='crg_best_rating' value='$best' />";
}

function crg_worst_rating_callback() {
    $worst = get_option('crg_worst_rating', 1);
    echo "<input type='number' name='crg_worst_rating' value='$worst' />";
}

function crg_true_alternate_name_callback() {
    $true_name = get_option('crg_true_alternate_name', 'True');
    echo "<input type='text' name='crg_true_alternate_name' value='$true_name' />";
}

function crg_false_alternate_name_callback() {
    $false_name = get_option('crg_false_alternate_name', 'False');
    echo "<input type='text' name='crg_false_alternate_name' value='$false_name' />";
}


// Add the management page to the admin menu
function crm_add_menu_page() {
    add_menu_page(
        'ClaimReview Manager',
        'ClaimReview Manager',
        'edit_posts',
        'claim-review-manager',
        'crm_render_manager_page',
        'dashicons-analytics',
        6
    );
}
add_action('admin_menu', 'crm_add_menu_page');

// Render the management page
function crm_render_manager_page() {
    ?>
    <div class="wrap">
        <h1>ClaimReview Manager</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#debunks" class="nav-tab nav-tab-active">Debunks</a>
            <a href="#verifications" class="nav-tab">Verifications</a>
        </h2>
        <div id="debunks" class="tab-content">
            <?php crm_render_posts_table('debunk'); ?>
        </div>
        <div id="verifications" class="tab-content" style="display:none;">
            <?php crm_render_posts_table('verification'); ?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.nav-tab-wrapper a').click(function(e) {
                e.preventDefault();
                $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });

            // AJAX save
            $('.save-claim-review').click(function() {
                var postId = $(this).data('post-id');
                var claimReview = $('#claim-review-' + postId).val();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_manual_claim_review',
                        post_id: postId,
                        claim_review: claimReview,
                        nonce: '<?php echo wp_create_nonce("save_manual_claim_review"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Claim review saved successfully!');
                        } else {
                            alert('Error saving claim review.');
                        }
                    }
                });
            });
        });
    </script>
    <?php
}

// Render the table of posts
function crm_render_posts_table($type) {
    $tag = $type === 'debunk' ? get_option('crg_debunk_tag') : get_option('crg_fact_check_tag');
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'post',
        'tag' => $tag
    ));

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Post Title</th><th>Calculated Claim Review</th><th>Manual Claim Review</th><th>Action</th></tr></thead>';
    echo '<tbody>';
    foreach ($posts as $post) {
        $calculated_claim_review = crg_generate_claim_review_text($post);
        $manual_claim_review = get_post_meta($post->ID, 'manual_claim_review', true);
        echo '<tr>';
        echo '<td>' . esc_html($post->post_title) . '</td>';
        echo '<td>' . esc_html($calculated_claim_review) . '</td>';
        echo '<td><textarea id="claim-review-' . $post->ID . '" rows="3" cols="50">' . esc_textarea($manual_claim_review) . '</textarea></td>';
        echo '<td><button class="button save-claim-review" data-post-id="' . $post->ID . '">Save</button></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

// AJAX handler for saving manual claim review
function crm_save_manual_claim_review() {
    check_ajax_referer('save_manual_claim_review', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    $claim_review = sanitize_textarea_field($_POST['claim_review']);
    
    update_post_meta($post_id, 'manual_claim_review', $claim_review);
    
    wp_send_json_success();
}
add_action('wp_ajax_save_manual_claim_review', 'crm_save_manual_claim_review');

// Callbacks for new settings
function crg_rating_method_callback() {
    $method = get_option('crg_rating_method', 'url');
    echo "<select name='crg_rating_method'>
        <option value='url' " . selected($method, 'url', false) . ">URL</option>
        <option value='category' " . selected($method, 'category', false) . ">Category</option>
        <option value='content' " . selected($method, 'content', false) . ">Content</option>
        <option value='custom' " . selected($method, 'custom', false) . ">Custom Function</option>
    </select>";
}

function crg_rating_custom_function_callback() {
    $function = get_option('crg_rating_custom_function', '');
    echo "<textarea name='crg_rating_custom_function' rows='10' cols='50'>" . esc_textarea($function) . "</textarea>";
    echo "<p>Enter a custom PHP function to extract the rating. The function should accept a WP_Post object and return a rating value.</p>";
}

function crg_rating_url_patterns_callback() {
    $patterns = get_option('crg_rating_url_patterns', '');
    echo "<textarea name='crg_rating_url_patterns' rows='5' cols='50'>" . esc_textarea($patterns) . "</textarea>";
    echo "<p>Enter URL patterns and corresponding ratings, one per line. Format: pattern|rating</p>";
}

function crg_rating_category_patterns_callback() {
    $patterns = get_option('crg_rating_category_patterns', '');
    echo "<textarea name='crg_rating_category_patterns' rows='5' cols='50'>" . esc_textarea($patterns) . "</textarea>";
    echo "<p>Enter category names and corresponding ratings, one per line. Format: category|rating</p>";
}

function crg_rating_content_patterns_callback() {
    $patterns = get_option('crg_rating_content_patterns', '');
    echo "<textarea name='crg_rating_content_patterns' rows='5' cols='50'>" . esc_textarea($patterns) . "</textarea>";
    echo "<p>Enter content patterns and corresponding ratings, one per line. Format: pattern|rating</p>";
}

// Function to extract rating based on the chosen method
function crg_extract_rating($post) {
    $method = get_option('crg_rating_method', 'url');
    
    switch ($method) {
        case 'url':
            return crg_extract_rating_from_url($post->post_name);
        case 'category':
            return crg_extract_rating_from_category($post->ID);
        case 'content':
            return crg_extract_rating_from_content($post->post_content);
        case 'custom':
            return crg_extract_rating_custom($post);
        default:
            return get_option('crg_worst_rating', 1);
    }
}

function crg_extract_rating_from_url($post_name) {
    $patterns = explode("\n", get_option('crg_rating_url_patterns', ''));
    foreach ($patterns as $pattern) {
        list($url_pattern, $rating) = explode('|', trim($pattern));
        if (strpos($post_name, trim($url_pattern)) !== false) {
            return intval(trim($rating));
        }
    }
    return get_option('crg_worst_rating', 1);
}

function crg_extract_rating_from_category($post_id) {
    $patterns = explode("\n", get_option('crg_rating_category_patterns', ''));
    $post_categories = wp_get_post_categories($post_id, array('fields' => 'names'));
    foreach ($patterns as $pattern) {
        list($category, $rating) = explode('|', trim($pattern));
        if (in_array(trim($category), $post_categories)) {
            return intval(trim($rating));
        }
    }
    return get_option('crg_worst_rating', 1);
}

function crg_extract_rating_from_content($content) {
    $patterns = explode("\n", get_option('crg_rating_content_patterns', ''));
    foreach ($patterns as $pattern) {
        list($content_pattern, $rating) = explode('|', trim($pattern));
        if (strpos($content, trim($content_pattern)) !== false) {
            return intval(trim($rating));
        }
    }
    return get_option('crg_worst_rating', 1);
}

function crg_extract_rating_custom($post) {
    $custom_function = get_option('crg_rating_custom_function', '');
    if (!empty($custom_function)) {
        $func = function($post) use ($custom_function) {
            return eval('return ' . $custom_function . ';');
        };
        return $func($post);
    }
    return get_option('crg_worst_rating', 1);
}


// Helper function to generate claim review text for the admin table
function crg_generate_claim_review_text($post) {
    $fact_check_tag = get_option('crg_fact_check_tag');
    $debunk_tag = get_option('crg_debunk_tag');

    $is_fact_check = has_tag($fact_check_tag, $post->ID);
    $is_debunk = has_tag($debunk_tag, $post->ID);

    if (!$is_fact_check && !$is_debunk) {
        return 'Not a fact-check or debunk';
    }

    $post_title = get_the_title($post->ID);
    $post_content = $post->post_content;

    if ($is_fact_check) {
        // For fact-checks, use the part after ":", "|", or ","
        $separators = array(':', '|', ',');
        $parts = str_replace($separators, $separators[0], $post_title, $count);
        $parts = explode($separators[0], $parts, 2);
        $claim_reviewed = isset($parts[1]) ? trim($parts[1]) : $post_title;
        // Remove quote marks
        $claim_reviewed = str_replace('"', '', $claim_reviewed);
    } else {
        // For debunks, use negacion_a_afirmacion_simple
        $claim_reviewed = negacion_a_afirmacion_simple($post_title);
        if ($claim_reviewed === null) {
            $claim_reviewed = $post_title; // Use original title if no transformation
        }
    }

    return $claim_reviewed;
}

// Modify the existing claim review generation function
function crg_generate_claim_review($content) {
    global $post;

    $fact_check_tag = get_option('crg_fact_check_tag');
    $debunk_tag = get_option('crg_debunk_tag');

    // Check if the post has either the fact-check or debunk tag
    if (!has_tag($fact_check_tag, $post) && !has_tag($debunk_tag, $post)) {
        return $content;
    }

    // Check for manual claim review
    $manual_claim_review = get_post_meta($post->ID, 'manual_claim_review', true);
    if (!empty($manual_claim_review)) {
        $claim_reviewed = $manual_claim_review;
    } else {
        $claim_reviewed = crg_generate_claim_review_text($post);
    }

    // Get post data
    $post_url = get_permalink($post->ID);

    // Determine the claim author
    if (has_tag($fact_check_tag, $post)) {
        // Extract the first words before ":", "|", or "," from the post content
        preg_match('/^([^:|,]+)/', $post->post_content, $matches);
        $claim_author = isset($matches[1]) ? trim($matches[1]) : 'Unknown';
    } else {
        $claim_author = get_option('crg_debunk_author', 'Social media');
    }

    // Extract rating
    $rating_value = crg_extract_rating($post);
    $alternate_name = ($rating_value == get_option('crg_best_rating', 5)) ? get_option('crg_true_alternate_name', 'True') : get_option('crg_false_alternate_name', 'False');

    // Prepare the ClaimReview schema
    $claim_review = array(
        '@context' => 'https://schema.org',
        '@type' => 'ClaimReview',
        'url' => $post_url,
        'claimReviewed' => $claim_reviewed,
        'itemReviewed' => array(
            '@type' => 'Claim',
            'author' => array(
                '@type' => 'Person',
                'name' => $claim_author
            )
        ),
        'author' => array(
            '@type' => 'Organization',
            'name' => get_option('crg_organization_name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_option('crg_organization_logo')
            )
        ),
        'reviewRating' => array(
            '@type' => 'Rating',
            'ratingValue' => $rating_value,
            'bestRating' => get_option('crg_best_rating', 5),
            'worstRating' => get_option('crg_worst_rating', 1),
            'alternateName' => $alternate_name
        )
    );

    // Generate the script tag
    $script = '<script type="application/ld+json">' . json_encode($claim_review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';

    // Add the script to the content
    return $script . $content;
}

add_filter('the_content', 'crg_generate_claim_review');
?>