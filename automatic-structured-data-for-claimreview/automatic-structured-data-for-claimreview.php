<?php
/*
Plugin Name: Automatic structured data for ClaimReview
Plugin URI: https://github.com/chequeado/claim-review-plugin
Description: Genera automáticamente el esquema ClaimReview para artículos de verificación y fact-checking.
Version: 1.0
Requires at least: 4.7
Requires PHP: 5.4
Author: Chequeado
Author URI: https://chequeado.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

// Ensure no whitespace or output before the opening PHP tag
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the asdcr_negacion_a_afirmacion_simple function
include_once(plugin_dir_path(__FILE__) . 'includes/claim-converter.php');

// Add settings page
function asdcr_add_settings_page() {
    add_options_page('Automatic structured data for ClaimReview', 'Automatic structured data for ClaimReview', 'manage_options', 'asdcr-settings', 'asdcr_render_settings_page');
}
add_action('admin_menu', 'asdcr_add_settings_page');

// Render settings page
function asdcr_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Automatic structured data for ClaimReview</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('asdcr_settings');
            do_settings_sections('asdcr-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function asdcr_register_settings() {
    function asdcr_register_settings() {
    register_setting('asdcr_settings', 'asdcr_post_type', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'post'
    ));

    register_setting('asdcr_settings', 'asdcr_taxonomy_fact_check', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ));

    register_setting('asdcr_settings', 'asdcr_taxonomy_debunk', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ));

    register_setting('asdcr_settings', 'asdcr_fact_check_tag', array(
        'type' => 'array',
        'sanitize_callback' => 'asdcr_sanitize_tags',
        'default' => array()
    ));
    register_setting('asdcr_settings', 'asdcr_debunk_tag', array(
        'type' => 'array',
        'sanitize_callback' => 'asdcr_sanitize_tags',
        'default' => array()
    ));
    register_setting('asdcr_settings', 'asdcr_organization_name', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('asdcr_settings', 'asdcr_organization_logo', array(
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw'
    ));
    register_setting('asdcr_settings', 'asdcr_debunk_author', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Social media'
    ));
    register_setting('asdcr_settings', 'asdcr_rating_taxonomy', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('asdcr_settings', 'asdcr_ratings', array(
        'type' => 'array',
        'sanitize_callback' => 'asdcr_sanitize_ratings',
        'default' => array()
    ));
    register_setting('asdcr_settings', 'asdcr_convert_titles', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => true
    ));
    register_setting('asdcr_settings', 'asdcr_disable_claim_author', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false
    ));

    add_settings_section('asdcr_main_section', 'Main Settings', null, 'asdcr-settings');

    add_settings_field('asdcr_post_type', 'Tipo de entrada para artículos', 'asdcr_post_type_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_taxonomy_fact_check', 'Taxonomía para identificar chequeos', 'asdcr_taxonomy_fact_check_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_fact_check_tag', 'Slugs para identificar chequeos ', 'asdcr_fact_check_tag_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_taxonomy_debunk', 'Taxonomía para identificar verificaciones', 'asdcr_taxonomy_debunk_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_debunk_tag', 'Slugs para identificar verificaciones', 'asdcr_debunk_tag_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_organization_name', 'Nombre de la Organización', 'asdcr_organization_name_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_organization_logo', 'URL del Logo de la Organización', 'asdcr_organization_logo_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field('asdcr_rating_taxonomy', 'Taxonomía de Calificaciones', 'asdcr_rating_taxonomy_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field(
        'asdcr_ratings', 
        'Calificaciones', 
        'asdcr_ratings_callback', 
        'asdcr-settings', 
        'asdcr_main_section'
    );  
    add_settings_field(
        'asdcr_convert_titles', 
        'Convertir titulares a descripciones', 
        'asdcr_convert_titles_callback', 
        'asdcr-settings', 
        'asdcr_main_section'
    ); 
    add_settings_field('asdcr_debunk_author', 'Autor de desinformación predeterminado', 'asdcr_debunk_author_callback', 'asdcr-settings', 'asdcr_main_section');
    add_settings_field(
        'asdcr_disable_claim_author',
        'Deshabilitar autor de la afirmación',
        'asdcr_disable_claim_author_callback',
        'asdcr-settings',
        'asdcr_main_section'
    );
}
add_action('admin_init', 'asdcr_register_settings');
// Update the settings callbacks
function asdcr_fact_check_tag_callback() {
    $selected_tags = get_option('asdcr_fact_check_tag', []);
    $taxonomy = get_option('asdcr_taxonomy_fact_check');
    
    if (!is_array($selected_tags)) {
        $selected_tags = explode("\n", $selected_tags);
    }
    
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms)) {
        echo '<p>Error: Please select a taxonomy first</p>';
        return;
    }
    
    ?>
    <div class="taxonomy-select">
        <select name="asdcr_fact_check_tag[]" multiple size="8" style="width: 300px;">
            <?php foreach ($terms as $term): ?>
                <option value="<?php echo esc_attr($term->slug); ?>" 
                    <?php echo in_array($term->slug, $selected_tags) ? 'selected' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Hold Ctrl/Cmd to select multiple items</p>
    </div>
    <?php
}

function asdcr_debunk_tag_callback() {
    $selected_tags = get_option('asdcr_debunk_tag', []);
    $taxonomy = get_option('asdcr_taxonomy_debunk');
    
    if (!is_array($selected_tags)) {
        $selected_tags = explode("\n", $selected_tags);
    }
    
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms)) {
        echo '<p>Error: Please select a taxonomy first</p>';
        return;
    }
    
    ?>
    <div class="taxonomy-select">
        <select name="asdcr_debunk_tag[]" multiple size="8" style="width: 300px;">
            <?php foreach ($terms as $term): ?>
                <option value="<?php echo esc_attr($term->slug); ?>" 
                    <?php echo in_array($term->slug, $selected_tags) ? 'selected' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Hold Ctrl/Cmd to select multiple items</p>
    </div>
    <?php
}

// Update sanitize callback
function asdcr_sanitize_tags($input) {
    return is_array($input) ? array_map('sanitize_text_field', $input) : [];
}


function asdcr_organization_name_callback() {
    $name = get_option('asdcr_organization_name');

    ?>
        <input type='text' name='asdcr_organization_name' value='<?php echo esc_attr($name); ?>' />
    <?php
}

function asdcr_organization_logo_callback() {
    $logo = get_option('asdcr_organization_logo');

    ?>
        <input type='text' name='asdcr_organization_logo' value='<?php echo esc_attr($logo); ?>' />
    <?php
}

function asdcr_debunk_author_callback() {
    $author = get_option('asdcr_debunk_author', 'Social media');

    ?>
        <input type='text' name='asdcr_debunk_author' value='<?php echo esc_attr($author); ?>' />
    <?php
}

function asdcr_post_type_callback() {
    $selected_post_type = get_option('asdcr_post_type');
    $post_types = get_post_types(array('public' => true), 'objects');

    echo '<select id="asdcr_post_type" name="asdcr_post_type">';
    foreach ($post_types as $post_type) {
        $selected = ($selected_post_type === $post_type->name) ? 'selected="selected"' : '';

        ?>
            <option value="<?php echo esc_attr($post_type->name); ?>"  <?php echo  esc_attr($selected); ?> > <?php echo esc_html($post_type->label); ?> </option>
        <?php
    }
    echo '</select>';
}


function asdcr_taxonomy_fact_check_callback() {
    $selected_taxonomy = get_option('asdcr_taxonomy_fact_check');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="asdcr_taxonomy_fact_check" name="asdcr_taxonomy_fact_check">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';

        ?>
            <option value=" <?php echo esc_attr($taxonomy->name); ?> "  <?php echo esc_attr($selected); ?>  > <?php echo  esc_html($taxonomy->label); ?> </option>
        <?php
    }
    echo '</select>';
}
    
function asdcr_taxonomy_debunk_callback() {
    $selected_taxonomy = get_option('asdcr_taxonomy_debunk');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="asdcr_taxonomy_debunk" name="asdcr_taxonomy_debunk">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';

        ?>
            <option value=" <?php echo esc_attr($taxonomy->name) ?> "  <?php echo esc_attr( $selected ); ?> > <?php echo esc_html($taxonomy->label) ?> </option>
        <?php
    }
    echo '</select>';
}

function asdcr_sanitize_ratings($input) {
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

function asdcr_ratings_callback() {
    $selected_ratings = get_option('asdcr_ratings', []);
    $selected_taxonomy = get_option('asdcr_rating_taxonomy');
    
    $terms = get_terms([
        'taxonomy' => $selected_taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms)) {
        echo '<p>Error: Please select a taxonomy first</p>';
        return;
    }
    ?>
    <div class="taxonomy-select">
        <select name="asdcr_ratings[]" multiple size="8" style="width: 300px;">
            <?php foreach ($terms as $term): ?>
                <option value="<?php echo esc_attr($term->name); ?>" 
                    <?php echo in_array($term->name, $selected_ratings) ? 'selected' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Hold Ctrl/Cmd to select multiple items.<br/>
        ¡IMPORTANTE! Orden: arriba va la peor calificación (ej: Falso) y sigue en orden hasta la mejor calificación abajo (ej: Verdadero).</p>
    </div>
    <?php
}


function asdcr_rating_taxonomy_callback() {

    $selected_taxonomy = get_option('asdcr_rating_taxonomy');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="asdcr_rating_taxonomy" name="asdcr_rating_taxonomy">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . esc_attr($selected) . '>' . esc_html($taxonomy->label) . '</option>';
    }
    echo '</select>';

}

function asdcr_convert_titles_callback() {
    $convert_titles = get_option('asdcr_convert_titles', true);
    echo '<input type="checkbox" name="asdcr_convert_titles" value="1" ' . checked(1, $convert_titles, false) . '/>';
    echo '<p class="description">Si está activado, convierte automáticamente las negaciones en afirmaciones.</p>';
}

function asdcr_disable_claim_author_callback() {
    $disable_claim_author = get_option('asdcr_disable_claim_author', false);
    echo '<input type="checkbox" name="asdcr_disable_claim_author" value="1" ' . checked(1, $disable_claim_author, false) . '/>';
    echo '<p class="description">Si está activado, se omite el autor de la afirmación del schema ClaimReview.</p>';
}

// Function to extract rating based on the chosen method
function asdcr_extract_rating($post) {
    $rating_taxonomy = get_option('asdcr_rating_taxonomy');
    $rating_array = get_option('asdcr_ratings');
    
    if (empty($rating_array)) {
        return null;
    }

    // Check each rating in order
    foreach ($rating_array as $index => $rating) {
        if (asdcr_has_any_term($rating, $rating_taxonomy, $post->ID)) {
            return [
                'tag_name' => $rating,
                'rating_value' => $index + 1
            ];
        }
    }
    
    return null;
}

// Function to check if post has any tag from a list of tags in a taxonomy
function asdcr_has_any_term($tags, $taxonomy, $post_id) {
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

function asdcr_extract_claim_from_title($post_title) {
    // Definir separadores en orden de prioridad
    $separators = array(':', '|', ',');
    
    // Probar cada separador en orden
    foreach ($separators as $separator) {
        $parts = explode($separator, $post_title, 2);
        if (count($parts) > 1 && !empty(trim($parts[1]))) {
            // Si encontramos un separador válido con contenido después,
            // retornamos la parte derecha limpia
            return str_replace('"', '', trim($parts[1]));
        }
    }
    
    // Si no encontramos ningún separador válido, retornamos el título original
    return str_replace('"', '', trim($post_title));
}

// Helper function to generate claim review text for the admin table
function asdcr_generate_claim_review_text($post) {
    $fact_check_tags = get_option('asdcr_fact_check_tag', array());
    $debunk_tags = get_option('asdcr_debunk_tag', array());
    
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    $is_fact_check = asdcr_has_any_term($fact_check_tags, get_option('asdcr_taxonomy_fact_check'), $post->ID);
    $is_debunk = asdcr_has_any_term($debunk_tags, get_option('asdcr_taxonomy_debunk'), $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return 'No es chequeo ni verificación.';
    }
    
    $post_title = get_the_title($post->ID);
    
    if ($is_fact_check) {
        $claim_reviewed = asdcr_extract_claim_from_title($post_title);
    } else {
        // Verificar si está habilitada la conversión
        $convert_titles = get_option('asdcr_convert_titles', true);
        if ($convert_titles) {
            $claim_reviewed = asdcr_negacion_a_afirmacion_simple($post_title);
            if ($claim_reviewed === null) {
                $claim_reviewed = $post_title;
            }
        } else {
            $claim_reviewed = $post_title;
        }
    }
    
    return $claim_reviewed;
}

// First, remove the content filter
remove_filter('the_content', 'asdcr_generate_claim_review');

// Add the wp_head action
add_action('wp_head', 'asdcr_output_claim_review_schema', 99);

// Create new function to output schema in head
function asdcr_output_claim_review_schema() {
    // Only run on single posts
    if (!is_singular()) {
        return;
    }
    
    global $post;
    
    // Get the tags as arrays
    $fact_check_tags = get_option('asdcr_fact_check_tag', array());
    $debunk_tags = get_option('asdcr_debunk_tag', array());
    
    // Ensure we have arrays
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    // Check for fact check or debunk tags
    $fact_check_taxonomy = get_option('asdcr_taxonomy_fact_check');
    $debunk_taxonomy = get_option('asdcr_taxonomy_debunk');
    
    $is_fact_check = asdcr_has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = asdcr_has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return;
    }

    // Extract rating
    $rating_value = asdcr_extract_rating($post);
    
    if (!$rating_value) {
        return;
    }
    
    // Get claim reviewed text
    $claim_reviewed = asdcr_generate_claim_review_text($post);

    // Check for manual claim review
    $manual_claim_review = get_post_meta($post->ID, 'asdcr_manual_claim_review', true);
    if (!empty($manual_claim_review)) {
        $claim_reviewed = $manual_claim_review;
    }
    
    // Get post data
    $post_url = get_permalink($post->ID);
    
    // Determine the claim author
    if ($is_fact_check) {
        preg_match('/^([^:|,]+)/', $post->post_title, $matches);
        $claim_author = isset($matches[1]) ? trim($matches[1]) : 'Desconocido';
    } else {
        $claim_author = get_option('asdcr_debunk_author', 'Social media');
    }
    
    // Build itemReviewed object
    $item_reviewed = array(
        '@type' => 'Claim',
        'datePublished' => get_the_date('c')
    );

    // Only add author if not disabled
    if (!get_option('asdcr_disable_claim_author', false)) {
        $item_reviewed['author'] = array(
            '@type' => 'Person',
            'name' => $claim_author
        );
    }

    // Prepare the ClaimReview schema
    $claim_review = array(
        '@context' => 'https://schema.org',
        '@type' => 'ClaimReview',
        'url' => $post_url,
        'claimReviewed' => $claim_reviewed,
        'itemReviewed' => $item_reviewed,
        'author' => array(
            '@type' => 'Organization',
            'name' => get_option('asdcr_organization_name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_option('asdcr_organization_logo')
            )
        ),
        'reviewRating' => array(
            '@type' => 'Rating',
            'ratingValue' => $rating_value['rating_value'],
            'bestRating' => count(get_option('asdcr_ratings')),
            'worstRating' => 1,
            'alternateName' => $rating_value['tag_name']
        )
    );
    
    // Output the schema in the head
    echo "\n<!-- ClaimReview Schema by Automatic structured data for ClaimReview -->\n";
    echo '<script type="application/ld+json">';
    echo wp_json_encode($claim_review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</script>\n";
}

// Add Meta Box to post editor
function asdcr_add_meta_box() {
    $post_type = get_option('asdcr_post_type');
    if (empty($post_type) || !post_type_exists($post_type)) {
        return;
    }
    
    // Simplemente agregar el meta box al tipo de post correcto
    // Las verificaciones de título y taxonomías se harán en el render
    add_meta_box(
        'asdcr_manual_claim_review',
        'ClaimReview - Descripción de lo que estás chequeando ',
        'asdcr_render_meta_box',
        $post_type,
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'asdcr_add_meta_box');

// Render Meta Box content
function asdcr_render_meta_box($post) {
    // Verificar si el post tiene título aquí
    if (empty($post->post_title)) {
        echo '<p class="description">La opción para corregir la frase para ClaimReview estará disponible después de guardar el título del post y actualizar la página.</p>';
        return;
    }
    
    // Verificar las taxonomías aquí donde ya están cargadas
    $fact_check_tags = get_option('asdcr_fact_check_tag', array());
    $debunk_tags = get_option('asdcr_debunk_tag', array());
    
    $fact_check_taxonomy = get_option('asdcr_taxonomy_fact_check');
    $debunk_taxonomy = get_option('asdcr_taxonomy_debunk');
    
    $is_fact_check = asdcr_has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = asdcr_has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        echo '<p class="description">Este post no está marcado como chequeo ni como verificación.</p>';
        return;
    }
    
    // Add nonce for security
    wp_nonce_field('asdcr_manual_claim_review_nonce', 'asdcr_manual_claim_review_nonce');
    
    // Get saved value if exists
    $manual_claim = get_post_meta($post->ID, 'asdcr_manual_claim_review', true);
    
    // If no manual claim exists, calculate it
    if (empty($manual_claim)) {
        $manual_claim = asdcr_generate_claim_review_text($post);
    }
    
    ?>
    <div class="asdcr-meta-box-container">
        <textarea 
            id="asdcr_manual_claim_review" 
            name="asdcr_manual_claim_review" 
            class="widefat" 
            rows="3"
            style="width: 100%"
        ><?php echo esc_textarea($manual_claim); ?></textarea>
        <p class="description">
            Esta sección permite corregir el texto que se carga en ClaimReview en caso de que contenga algún error. 
            <br/>
            El texto refiere a la frase que se está chequeando o una descripción del contenido qué se está verificando.
        </p>
        <?php 
        // Show automatically calculated claim for reference
        $auto_claim = asdcr_generate_claim_review_text($post);
        if ($auto_claim !== $manual_claim) {
            echo '<p class="description">';
            echo '<strong>Descripción generada automáticamente:</strong><br>';
            echo esc_html($auto_claim);
            echo '</p>';
        }
        ?>
    </div>
    <?php
}

// Save Meta Box data
function asdcr_save_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['asdcr_manual_claim_review_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['asdcr_manual_claim_review_nonce'])), 'asdcr_manual_claim_review_nonce')){
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
    $manual_claim = isset($_POST['asdcr_manual_claim_review']) ? 
        sanitize_textarea_field(wp_unslash($_POST['asdcr_manual_claim_review'])) : '';

    // Update or delete the meta field
    if (!empty($manual_claim)) {
        update_post_meta($post_id, 'asdcr_manual_claim_review', $manual_claim);
    } else {
        delete_post_meta($post_id, 'asdcr_manual_claim_review');
    }
}
add_action('save_post', 'asdcr_save_meta_box');

function asdcr_enqueue_admin_scripts($hook) {
    // Only load on our plugin's settings page
    if ('settings_page_asdcr-settings' !== $hook) {
        return;
    }
    
    // Register and enqueue our admin JavaScript
    wp_register_script(
        'asdcr-admin-script',
        plugins_url('js/admin.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize the script with our data
    wp_localize_script('asdcr-admin-script', 'asdcrAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('get_taxonomy_terms')
    ));

    wp_enqueue_script('asdcr-admin-script');
}
add_action('admin_enqueue_scripts', 'asdcr_enqueue_admin_scripts');


function asdcr_get_taxonomy_terms_ajax() {
    check_ajax_referer('get_taxonomy_terms', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    if (!isset($_POST['taxonomy'])) {
        return;
    }

    $taxonomy = sanitize_text_field(wp_unslash($_POST['taxonomy']));
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms)) {
        wp_send_json_error('Invalid taxonomy');
    }

    $terms_array = array_map(function($term) {
        return [
            'slug' => $term->slug,
            'name' => $term->name
        ];
    }, $terms);

    wp_send_json_success($terms_array);
}
add_action('wp_ajax_get_taxonomy_terms', 'asdcr_get_taxonomy_terms_ajax');

?>