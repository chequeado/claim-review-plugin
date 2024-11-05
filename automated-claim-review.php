<?php
/*
Plugin Name: ClaimReview Automático
Plugin URI: https://github.com/chequeado/claim-review-plugin
Description: Genera el schema ClaimReview para chequeos y verificaciones de manera automática
Version: 2.0
Author: Chequeado
Author URI: https://chequeado.com
*/

// Include the negacion_a_afirmacion_simple function
include_once(plugin_dir_path(__FILE__) . 'negacion_a_afirmacion_simple.php');


// Add settings page
function crg_add_settings_page() {
    add_options_page('Ajustes ClaimReview Automático', 'ClaimReview Automático', 'manage_options', 'crg-settings', 'crg_render_settings_page');
}
add_action('admin_menu', 'crg_add_settings_page');



// Render settings page
function crg_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ajustes ClaimReview</h1>
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
    register_setting('crg_settings', 'crg_post_type');
    register_setting('crg_settings', 'crg_taxonomy_fact_check');
    register_setting('crg_settings', 'crg_taxonomy_debunk');
    register_setting('crg_settings', 'crg_fact_check_tag', [
        'sanitize_callback' => 'crg_sanitize_tags'
    ]);
    register_setting('crg_settings', 'crg_debunk_tag', [
        'sanitize_callback' => 'crg_sanitize_tags'
    ]);
    register_setting('crg_settings', 'crg_organization_name', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('crg_settings', 'crg_organization_logo', [
        'sanitize_callback' => 'esc_url_raw'
    ]);
    register_setting('crg_settings', 'crg_debunk_author');
    register_setting('crg_settings', 'crg_rating_taxonomy');
    register_setting('crg_settings', 'crg_ratings', [
        'sanitize_callback' => 'crg_sanitize_ratings'
    ]);
   

    add_settings_section('crg_main_section', 'Main Settings', null, 'crg-settings');

    add_settings_field('crg_post_type', 'Tipo de entrada para artículos', 'crg_post_type_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_taxonomy_fact_check', 'Taxonomía para identificar chequeos', 'crg_taxonomy_fact_check_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_fact_check_tag', 'Slugs para identificar chequeos ', 'crg_fact_check_tag_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_taxonomy_debunk', 'Taxonomía para identificar verificaciones', 'crg_taxonomy_debunk_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_debunk_tag', 'Slugs para identificar verificaciones', 'crg_debunk_tag_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_organization_name', 'Nombre de la Organización', 'crg_organization_name_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_organization_logo', 'URL del Logo de la Organización', 'crg_organization_logo_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_debunk_author', 'Autor de desinformación predeterminado', 'crg_debunk_author_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_taxonomy', 'Taxonomía de Calificaciones', 'crg_rating_taxonomy_callback', 'crg-settings', 'crg_main_section');
    add_settings_field(
        'crg_ratings', 
        'Calificaciones', 
        'crg_ratings_callback', 
        'crg-settings', 
        'crg_main_section'
    );   
}
add_action('admin_init', 'crg_register_settings');

function crg_sanitize_tags($input) {
    // If input is already an array, just sanitize each element
    if (is_array($input)) {
        return array_filter(array_map('trim', $input));
    }
    
    // If input is a string (from textarea), split by newlines
    if (is_string($input)) {
        return array_filter(array_map('trim', explode("\n", $input)));
    }
    
    // If input is neither string nor array, return empty array
    return array();
}

// Settings callbacks

function crg_fact_check_tag_callback() {
    // Get the array of tags; if it's a string (from an old version), convert it to an array
    $tags = get_option('crg_fact_check_tag', []);
    if (!is_array($tags)) {
        $tags = explode("\n", $tags);
    }

    // Convert the array to a newline-separated string for display in the textarea
    $tags_string = implode("\n", $tags);

    echo "<textarea name='crg_fact_check_tag' rows='5' cols='50'>$tags_string</textarea>";
    echo "<p>Ingresá un slug en cada nueva línea.</p>";
}

function crg_debunk_tag_callback() {
    $tags = get_option('crg_debunk_tag', []);
    if (!is_array($tags)) {
        $tags = explode("\n", $tags);
    }

    $tags_string = implode("\n", $tags);

    echo "<textarea name='crg_debunk_tag' rows='5' cols='50'>$tags_string</textarea>";
    echo "<p>Ingresá un slug en cada nueva línea.</p>";
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

function crg_post_type_callback() {
    $selected_post_type = get_option('crg_post_type');
    $post_types = get_post_types(array('public' => true), 'objects');

    echo '<select id="crg_post_type" name="crg_post_type">';
    foreach ($post_types as $post_type) {
        $selected = ($selected_post_type === $post_type->name) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($post_type->name) . '" ' . $selected . '>' . esc_html($post_type->label) . '</option>';
    }
    echo '</select>';
}


function crg_taxonomy_fact_check_callback() {
    $selected_taxonomy = get_option('crg_taxonomy_fact_check');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="crg_taxonomy_fact_check" name="crg_taxonomy_fact_check">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . $selected . '>' . esc_html($taxonomy->label) . '</option>';
    }
    echo '</select>';
}
    
function crg_taxonomy_debunk_callback() {
    $selected_taxonomy = get_option('crg_taxonomy_debunk');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="crg_taxonomy_debunk" name="crg_taxonomy_debunk">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . $selected . '>' . esc_html($taxonomy->label) . '</option>';
    }
    echo '</select>';
}

function crg_sanitize_ratings($input) {
    if (!is_array($input)) {
        return [];
    }

    // Loop through the array and sanitize each entry
    $output = [];
    foreach ($input as $key => $value) {
        $output[$key] = sanitize_text_field($value);
    }

    return $output;
}

function crg_ratings_callback() {
    $ratings = get_option('crg_ratings', []);

    // Display a label and the button for adding new rows
    ?>
    <div id="crg_ratings_container">
        <?php
        if (!empty($ratings)) {
            foreach ($ratings as $index => $rating) {
                ?>
                <div class="crg_rating_row">
                    <input type="text" name="crg_ratings[]" value="<?php echo esc_attr($rating); ?>" />
                    <button class="button remove-rating">Borrar</button>
                </div>
                <?php
            }
        } else {
            // Display an empty row if no ratings are present
            ?>
            <div class="crg_rating_row">
                <input type="text" name="crg_ratings[]" value="" />
                <button class="button remove-rating">Borrar</button>
            </div>
            <?php
        }
        ?>
    </div>
    <button class="button add-rating">Agregar Calificación</button>

    <script>
    jQuery(document).ready(function($) {
        // Add new rating row
        $('.add-rating').click(function(e) {
            e.preventDefault();
            var newRow = '<div class="crg_rating_row"><input type="text" name="crg_ratings[]" value="" /><button class="button remove-rating">Borrar</button></div>';
            $('#crg_ratings_container').append(newRow);
        });

        // Remove rating row
        $(document).on('click', '.remove-rating', function(e) {
            e.preventDefault();
            $(this).parent().remove();
        });
    });
    </script>
    <style>
        .crg_rating_row {
            margin-bottom: 10px;
        }
        .crg_rating_row input {
            width: 300px;
        }
    </style>
    <?php
}

function crg_rating_taxonomy_callback() {

    $selected_taxonomy = get_option('crg_rating_taxonomy');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="crg_rating_taxonomy" name="crg_rating_taxonomy">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . $selected . '>' . esc_html($taxonomy->label) . '</option>';
    }
    echo '</select>';

}

// Function to extract rating based on the chosen method
function crg_extract_rating($post) {

    $rating_taxonomy = get_option('crg_rating_taxonomy');

    $rating_tag = [];

    foreach(wp_get_post_terms( $post->ID, $rating_taxonomy ) as $taxonomy)
        {
            array_push ($rating_tag, $taxonomy->name);
        }
    $rating_array = get_option('crg_ratings');

    $final_rating = array_intersect($rating_tag, $rating_array);

   

    if (empty($final_rating)){
        return null;
    } else {
        $rating_value = array_search(array_values($final_rating)[0], $rating_array);

        return [
            'tag_name' => array_values($final_rating)[0],
            'rating_value' => $rating_value + 1
        ];
    }
}

// Function to check if post has any tag from a list of tags in a taxonomy
function has_any_term($tags, $taxonomy, $post_id) {
    // Ensure tags is an array
    $tags = is_array($tags) ? $tags : array($tags);
    
    // Remove any empty values
    $tags = array_filter($tags, function($tag) {
        return !empty(trim($tag));
    });
    
    if (empty($tags)) {
        return false;
    }
    
    foreach ($tags as $tag) {
        if (has_term(trim($tag), $taxonomy, $post_id)) {
            return true;
        }
    }
    return false;
}

// Helper function to generate claim review text for the admin table
function crg_generate_claim_review_text($post) {
    $fact_check_tags = get_option('crg_fact_check_tag', array());
    $debunk_tags = get_option('crg_debunk_tag', array());
    
    // Ensure both are arrays
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    // Use the helper function to check for tags
    $is_fact_check = has_any_term($fact_check_tags, get_option('crg_taxonomy_fact_check'), $post->ID);
    $is_debunk = has_any_term($debunk_tags, get_option('crg_taxonomy_debunk'), $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return 'Not a fact-check or debunk';
    }
    
    $post_title = get_the_title($post->ID);
    
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

function crg_generate_claim_review($content) {
    // Evitar múltiples ejecuciones
    static $has_run = false;
    if ($has_run || !is_singular() || !in_the_loop()) {
        return $content;
    }
    $has_run = true;
    
    global $post;
    
    // Get the tags as arrays
    $fact_check_tags = get_option('crg_fact_check_tag', array());
    $debunk_tags = get_option('crg_debunk_tag', array());
    
    // Ensure we have arrays
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    // Check for fact check or debunk tags using the helper function
    $fact_check_taxonomy = get_option('crg_taxonomy_fact_check');
    $debunk_taxonomy = get_option('crg_taxonomy_debunk');
    
    $is_fact_check = has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return $content;
    }
    
    // Rest of the claim review generation logic...
    $claim_reviewed = crg_generate_claim_review_text($post);
    
    // Check for manual claim review
    $manual_claim_review = get_post_meta($post->ID, 'manual_claim_review', true);
    if (!empty($manual_claim_review)) {
        $claim_reviewed = $manual_claim_review;
    }
    
    // Get post data
    $post_url = get_permalink($post->ID);
    
    // Determine the claim author
    if ($is_fact_check) {
        preg_match('/^([^:|,]+)/', $post->post_title, $matches);
        $claim_author = isset($matches[1]) ? trim($matches[1]) : 'Unknown';
    } else {
        $claim_author = get_option('crg_debunk_author', 'Social media');
    }
    
    // Extract rating
    $rating_value = crg_extract_rating($post);
    
    if (!$rating_value) {
        return $content;
    }
    
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
            ),
            'datePublished' => get_the_date('c')
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
            'ratingValue' => $rating_value['rating_value'],
            'bestRating' => count(get_option('crg_ratings')),
            'worstRating' => 1,
            'alternateName' => $rating_value['tag_name']
        )
    );
    
    // Generate the script tag
    $script = '<script type="application/ld+json">' . json_encode($claim_review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    
    return $script . $content;
}

add_filter('the_content', 'crg_generate_claim_review');

// Add Meta Box to post editor
function crg_add_meta_box() {
    $post_type = get_option('crg_post_type');
    if (empty($post_type) || !post_type_exists($post_type)) {
        return;
    }
    
    // Simplemente agregar el meta box al tipo de post correcto
    // Las verificaciones de título y taxonomías se harán en el render
    add_meta_box(
        'crg_manual_claim_review',
        'ClaimReview Manual',
        'crg_render_meta_box',
        $post_type,
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'crg_add_meta_box');

// Render Meta Box content
function crg_render_meta_box($post) {
    // Verificar si el post tiene título aquí
    if (empty($post->post_title)) {
        echo '<p class="description">El claim review estará disponible después de guardar el título del post.</p>';
        return;
    }
    
    // Verificar las taxonomías aquí donde ya están cargadas
    $fact_check_tags = get_option('crg_fact_check_tag', array());
    $debunk_tags = get_option('crg_debunk_tag', array());
    
    $fact_check_taxonomy = get_option('crg_taxonomy_fact_check');
    $debunk_taxonomy = get_option('crg_taxonomy_debunk');
    
    $is_fact_check = has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        echo '<p class="description">Este post no está marcado como fact-check ni como debunk.</p>';
        return;
    }
    
    // Add nonce for security
    wp_nonce_field('crg_manual_claim_review_nonce', 'crg_manual_claim_review_nonce');
    
    // Get saved value if exists
    $manual_claim = get_post_meta($post->ID, 'manual_claim_review', true);
    
    // If no manual claim exists, calculate it
    if (empty($manual_claim)) {
        $manual_claim = crg_generate_claim_review_text($post);
    }
    
    ?>
    <div class="crg-meta-box-container">
        <textarea 
            id="crg_manual_claim_review" 
            name="crg_manual_claim_review" 
            class="widefat" 
            rows="3"
            style="width: 100%"
        ><?php echo esc_textarea($manual_claim); ?></textarea>
        <p class="description">
            Este texto se usa en el schema ClaimReview. Si lo dejás vacío, se usará el texto calculado automáticamente.
        </p>
        <?php 
        // Show automatically calculated claim for reference
        $auto_claim = crg_generate_claim_review_text($post);
        if ($auto_claim !== $manual_claim) {
            echo '<p class="description">';
            echo '<strong>Claim calculado automáticamente:</strong><br>';
            echo esc_html($auto_claim);
            echo '</p>';
        }
        ?>
    </div>
    <?php
}

// Save Meta Box data
function crg_save_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['crg_manual_claim_review_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['crg_manual_claim_review_nonce'], 'crg_manual_claim_review_nonce')) {
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Get the manual claim review value
    $manual_claim = isset($_POST['crg_manual_claim_review']) ? 
        sanitize_textarea_field($_POST['crg_manual_claim_review']) : '';

    // Update or delete the meta field
    if (!empty($manual_claim)) {
        update_post_meta($post_id, 'manual_claim_review', $manual_claim);
    } else {
        delete_post_meta($post_id, 'manual_claim_review');
    }
}
add_action('save_post', 'crg_save_meta_box');

?>
