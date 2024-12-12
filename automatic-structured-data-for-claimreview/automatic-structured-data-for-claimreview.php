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

// Include the cra_negacion_a_afirmacion_simple function
include_once(plugin_dir_path(__FILE__) . 'includes/claim-converter.php');

// Add settings page
function cra_add_settings_page() {
    add_options_page('Automatic structured data for ClaimReview', 'Automatic structured data for ClaimReview', 'manage_options', 'cra-settings', 'cra_render_settings_page');
}
add_action('admin_menu', 'cra_add_settings_page');

// Render settings page
function cra_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Automatic structured data for ClaimReview</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cra_settings');
            do_settings_sections('cra-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function cra_register_settings() {
    register_setting('cra_settings', 'cra_post_type');
    register_setting('cra_settings', 'cra_taxonomy_fact_check');
    register_setting('cra_settings', 'cra_taxonomy_debunk');
    register_setting('cra_settings', 'cra_fact_check_tag', [
        'sanitize_callback' => 'cra_sanitize_tags'
    ]);
    register_setting('cra_settings', 'cra_debunk_tag', [
        'sanitize_callback' => 'cra_sanitize_tags'
    ]);
    register_setting('cra_settings', 'cra_organization_name', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('cra_settings', 'cra_organization_logo', [
        'sanitize_callback' => 'esc_url_raw'
    ]);
    register_setting('cra_settings', 'cra_debunk_author');
    register_setting('cra_settings', 'cra_rating_taxonomy');
    register_setting('cra_settings', 'cra_ratings', [
        'sanitize_callback' => 'cra_sanitize_ratings'
    ]);
    register_setting('cra_settings', 'cra_convert_titles', [
        'default' => true
    ]);
    register_setting('cra_settings', 'cra_disable_claim_author', [
        'type' => 'boolean',
        'default' => false
    ]);

    add_settings_section('cra_main_section', 'Main Settings', null, 'cra-settings');

    add_settings_field('cra_post_type', 'Tipo de entrada para artículos', 'cra_post_type_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_taxonomy_fact_check', 'Taxonomía para identificar chequeos', 'cra_taxonomy_fact_check_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_fact_check_tag', 'Slugs para identificar chequeos ', 'cra_fact_check_tag_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_taxonomy_debunk', 'Taxonomía para identificar verificaciones', 'cra_taxonomy_debunk_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_debunk_tag', 'Slugs para identificar verificaciones', 'cra_debunk_tag_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_organization_name', 'Nombre de la Organización', 'cra_organization_name_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_organization_logo', 'URL del Logo de la Organización', 'cra_organization_logo_callback', 'cra-settings', 'cra_main_section');
    add_settings_field('cra_rating_taxonomy', 'Taxonomía de Calificaciones', 'cra_rating_taxonomy_callback', 'cra-settings', 'cra_main_section');
    add_settings_field(
        'cra_ratings', 
        'Calificaciones', 
        'cra_ratings_callback', 
        'cra-settings', 
        'cra_main_section'
    );  
    add_settings_field(
        'cra_convert_titles', 
        'Convertir titulares a descripciones', 
        'cra_convert_titles_callback', 
        'cra-settings', 
        'cra_main_section'
    ); 
    add_settings_field('cra_debunk_author', 'Autor de desinformación predeterminado', 'cra_debunk_author_callback', 'cra-settings', 'cra_main_section');
    add_settings_field(
        'cra_disable_claim_author',
        'Deshabilitar autor de la afirmación',
        'cra_disable_claim_author_callback',
        'cra-settings',
        'cra_main_section'
    );
}
add_action('admin_init', 'cra_register_settings');
// Update the settings callbacks
function cra_fact_check_tag_callback() {
    $selected_tags = get_option('cra_fact_check_tag', []);
    $taxonomy = get_option('cra_taxonomy_fact_check');
    
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
        <select name="cra_fact_check_tag[]" multiple size="8" style="width: 300px;">
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

function cra_debunk_tag_callback() {
    $selected_tags = get_option('cra_debunk_tag', []);
    $taxonomy = get_option('cra_taxonomy_debunk');
    
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
        <select name="cra_debunk_tag[]" multiple size="8" style="width: 300px;">
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
function cra_sanitize_tags($input) {
    return is_array($input) ? array_map('sanitize_text_field', $input) : [];
}


function cra_organization_name_callback() {
    $name = get_option('cra_organization_name');

    ?>
        <input type='text' name='cra_organization_name' value='<?php echo esc_attr($name); ?>' />
    <?php
}

function cra_organization_logo_callback() {
    $logo = get_option('cra_organization_logo');

    ?>
        <input type='text' name='cra_organization_logo' value='<?php echo esc_attr($logo); ?>' />
    <?php
}

function cra_debunk_author_callback() {
    $author = get_option('cra_debunk_author', 'Social media');

    ?>
        <input type='text' name='cra_debunk_author' value='<?php echo esc_attr($author); ?>' />
    <?php
}

function cra_post_type_callback() {
    $selected_post_type = get_option('cra_post_type');
    $post_types = get_post_types(array('public' => true), 'objects');

    echo '<select id="cra_post_type" name="cra_post_type">';
    foreach ($post_types as $post_type) {
        $selected = ($selected_post_type === $post_type->name) ? 'selected="selected"' : '';

        ?>
            <option value="<?php echo esc_attr($post_type->name); ?>"  <?php echo  esc_attr($selected); ?> > <?php echo esc_html($post_type->label); ?> </option>
        <?php
    }
    echo '</select>';
}


function cra_taxonomy_fact_check_callback() {
    $selected_taxonomy = get_option('cra_taxonomy_fact_check');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="cra_taxonomy_fact_check" name="cra_taxonomy_fact_check">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';

        ?>
            <option value=" <?php echo esc_attr($taxonomy->name); ?> "  <?php echo esc_attr($selected); ?>  > <?php echo  esc_html($taxonomy->label); ?> </option>
        <?php
    }
    echo '</select>';
}
    
function cra_taxonomy_debunk_callback() {
    $selected_taxonomy = get_option('cra_taxonomy_debunk');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="cra_taxonomy_debunk" name="cra_taxonomy_debunk">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';

        ?>
            <option value=" <?php echo esc_attr($taxonomy->name) ?> "  <?php echo esc_attr( $selected ); ?> > <?php echo esc_html($taxonomy->label) ?> </option>
        <?php
    }
    echo '</select>';
}

function cra_sanitize_ratings($input) {
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

function cra_ratings_callback() {
    $selected_ratings = get_option('cra_ratings', []);
    $selected_taxonomy = get_option('cra_rating_taxonomy');
    
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
        <select name="cra_ratings[]" multiple size="8" style="width: 300px;">
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


function cra_rating_taxonomy_callback() {

    $selected_taxonomy = get_option('cra_rating_taxonomy');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="cra_rating_taxonomy" name="cra_rating_taxonomy">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . esc_attr($selected) . '>' . esc_html($taxonomy->label) . '</option>';
    }
    echo '</select>';

}

function cra_convert_titles_callback() {
    $convert_titles = get_option('cra_convert_titles', true);
    echo '<input type="checkbox" name="cra_convert_titles" value="1" ' . checked(1, $convert_titles, false) . '/>';
    echo '<p class="description">Si está activado, convierte automáticamente las negaciones en afirmaciones.</p>';
}

function cra_disable_claim_author_callback() {
    $disable_claim_author = get_option('cra_disable_claim_author', false);
    echo '<input type="checkbox" name="cra_disable_claim_author" value="1" ' . checked(1, $disable_claim_author, false) . '/>';
    echo '<p class="description">Si está activado, se omite el autor de la afirmación del schema ClaimReview.</p>';
}

// Function to extract rating based on the chosen method
function cra_extract_rating($post) {
    $rating_taxonomy = get_option('cra_rating_taxonomy');
    $rating_array = get_option('cra_ratings');
    
    if (empty($rating_array)) {
        return null;
    }

    // Check each rating in order
    foreach ($rating_array as $index => $rating) {
        if (cra_has_any_term($rating, $rating_taxonomy, $post->ID)) {
            return [
                'tag_name' => $rating,
                'rating_value' => $index + 1
            ];
        }
    }
    
    return null;
}

// Function to check if post has any tag from a list of tags in a taxonomy
function cra_has_any_term($tags, $taxonomy, $post_id) {
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

function cra_extract_claim_from_title($post_title) {
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
function cra_generate_claim_review_text($post) {
    $fact_check_tags = get_option('cra_fact_check_tag', array());
    $debunk_tags = get_option('cra_debunk_tag', array());
    
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    $is_fact_check = cra_has_any_term($fact_check_tags, get_option('cra_taxonomy_fact_check'), $post->ID);
    $is_debunk = cra_has_any_term($debunk_tags, get_option('cra_taxonomy_debunk'), $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return 'No es chequeo ni verificación.';
    }
    
    $post_title = get_the_title($post->ID);
    
    if ($is_fact_check) {
        $claim_reviewed = cra_extract_claim_from_title($post_title);
    } else {
        // Verificar si está habilitada la conversión
        $convert_titles = get_option('cra_convert_titles', true);
        if ($convert_titles) {
            $claim_reviewed = cra_negacion_a_afirmacion_simple($post_title);
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
remove_filter('the_content', 'cra_generate_claim_review');

// Add the wp_head action
add_action('wp_head', 'cra_output_claim_review_schema', 99);

// Create new function to output schema in head
function cra_output_claim_review_schema() {
    // Only run on single posts
    if (!is_singular()) {
        return;
    }
    
    global $post;
    
    // Get the tags as arrays
    $fact_check_tags = get_option('cra_fact_check_tag', array());
    $debunk_tags = get_option('cra_debunk_tag', array());
    
    // Ensure we have arrays
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    // Check for fact check or debunk tags
    $fact_check_taxonomy = get_option('cra_taxonomy_fact_check');
    $debunk_taxonomy = get_option('cra_taxonomy_debunk');
    
    $is_fact_check = cra_has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = cra_has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return;
    }

    // Extract rating
    $rating_value = cra_extract_rating($post);
    
    if (!$rating_value) {
        return;
    }
    
    // Get claim reviewed text
    $claim_reviewed = cra_generate_claim_review_text($post);

    // Check for manual claim review
    $manual_claim_review = get_post_meta($post->ID, 'cra_manual_claim_review', true);
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
        $claim_author = get_option('cra_debunk_author', 'Social media');
    }
    
    // Build itemReviewed object
    $item_reviewed = array(
        '@type' => 'Claim',
        'datePublished' => get_the_date('c')
    );

    // Only add author if not disabled
    if (!get_option('cra_disable_claim_author', false)) {
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
            'name' => get_option('cra_organization_name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_option('cra_organization_logo')
            )
        ),
        'reviewRating' => array(
            '@type' => 'Rating',
            'ratingValue' => $rating_value['rating_value'],
            'bestRating' => count(get_option('cra_ratings')),
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
function cra_add_meta_box() {
    $post_type = get_option('cra_post_type');
    if (empty($post_type) || !post_type_exists($post_type)) {
        return;
    }
    
    // Simplemente agregar el meta box al tipo de post correcto
    // Las verificaciones de título y taxonomías se harán en el render
    add_meta_box(
        'cra_manual_claim_review',
        'ClaimReview - Descripción de lo que estás chequeando ',
        'cra_render_meta_box',
        $post_type,
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cra_add_meta_box');

// Render Meta Box content
function cra_render_meta_box($post) {
    // Verificar si el post tiene título aquí
    if (empty($post->post_title)) {
        echo '<p class="description">La opción para corregir la frase para ClaimReview estará disponible después de guardar el título del post y actualizar la página.</p>';
        return;
    }
    
    // Verificar las taxonomías aquí donde ya están cargadas
    $fact_check_tags = get_option('cra_fact_check_tag', array());
    $debunk_tags = get_option('cra_debunk_tag', array());
    
    $fact_check_taxonomy = get_option('cra_taxonomy_fact_check');
    $debunk_taxonomy = get_option('cra_taxonomy_debunk');
    
    $is_fact_check = cra_has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = cra_has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        echo '<p class="description">Este post no está marcado como chequeo ni como verificación.</p>';
        return;
    }
    
    // Add nonce for security
    wp_nonce_field('cra_manual_claim_review_nonce', 'cra_manual_claim_review_nonce');
    
    // Get saved value if exists
    $manual_claim = get_post_meta($post->ID, 'cra_manual_claim_review', true);
    
    // If no manual claim exists, calculate it
    if (empty($manual_claim)) {
        $manual_claim = cra_generate_claim_review_text($post);
    }
    
    ?>
    <div class="cra-meta-box-container">
        <textarea 
            id="cra_manual_claim_review" 
            name="cra_manual_claim_review" 
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
        $auto_claim = cra_generate_claim_review_text($post);
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
function cra_save_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['cra_manual_claim_review_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cra_manual_claim_review_nonce'])), 'cra_manual_claim_review_nonce')){
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
    $manual_claim = isset($_POST['cra_manual_claim_review']) ? 
        sanitize_textarea_field(wp_unslash($_POST['cra_manual_claim_review'])) : '';

    // Update or delete the meta field
    if (!empty($manual_claim)) {
        update_post_meta($post_id, 'cra_manual_claim_review', $manual_claim);
    } else {
        delete_post_meta($post_id, 'cra_manual_claim_review');
    }
}
add_action('save_post', 'cra_save_meta_box');

// Add this to your existing PHP file
function cra_admin_footer_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        function updateTaxonomyTerms(taxonomy, selectElement) {
            $.post(ajaxurl, {
                action: 'get_taxonomy_terms',
                taxonomy: taxonomy,
                nonce: <?php echo wp_json_encode(wp_create_nonce('get_taxonomy_terms')); ?>
            }, function(response) {
                if (response.success) {
                    selectElement.empty();
                    response.data.forEach(function(term) {
                        var option = new Option(
                            wp.escapeHtml(term.name), 
                            wp.escapeHtml(term.slug)
                        );
                        selectElement.append(option);
                    });
                }
            });
        }

        $('#cra_taxonomy_fact_check').on('change', function() {
            updateTaxonomyTerms($(this).val(), $('select[name="cra_fact_check_tag[]"]'));
        });

        $('#cra_taxonomy_debunk').on('change', function() {
            updateTaxonomyTerms($(this).val(), $('select[name="cra_debunk_tag[]"]'));
        });

        $('#cra_rating_taxonomy').on('change', function() {
            updateTaxonomyTerms($(this).val(), $('select[name="cra_ratings[]"]'));
        });
    });
    </script>
    <?php
}
add_action('admin_footer-settings_page_cra-settings', 'cra_admin_footer_scripts');

function cra_get_taxonomy_terms_ajax() {
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
add_action('wp_ajax_get_taxonomy_terms', 'cra_get_taxonomy_terms_ajax');

?>