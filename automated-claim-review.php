<?php
/*
Plugin Name: ClaimReview Automático
Plugin URI: https://github.com/chequeado/claim-review-plugin
Description: Genera el schema ClaimReview para chequeos y verificaciones de manera automática
Version: 2.1
Author: Chequeado
Author URI: https://chequeado.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
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
    register_setting('crg_settings', 'crg_convert_titles', [
        'default' => true
    ]);
    register_setting('crg_settings', 'crg_disable_claim_author', [
        'type' => 'boolean',
        'default' => false
    ]);

    add_settings_section('crg_main_section', 'Main Settings', null, 'crg-settings');

    add_settings_field('crg_post_type', 'Tipo de entrada para artículos', 'crg_post_type_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_taxonomy_fact_check', 'Taxonomía para identificar chequeos', 'crg_taxonomy_fact_check_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_fact_check_tag', 'Slugs para identificar chequeos ', 'crg_fact_check_tag_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_taxonomy_debunk', 'Taxonomía para identificar verificaciones', 'crg_taxonomy_debunk_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_debunk_tag', 'Slugs para identificar verificaciones', 'crg_debunk_tag_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_organization_name', 'Nombre de la Organización', 'crg_organization_name_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_organization_logo', 'URL del Logo de la Organización', 'crg_organization_logo_callback', 'crg-settings', 'crg_main_section');
    add_settings_field('crg_rating_taxonomy', 'Taxonomía de Calificaciones', 'crg_rating_taxonomy_callback', 'crg-settings', 'crg_main_section');
    add_settings_field(
        'crg_ratings', 
        'Calificaciones', 
        'crg_ratings_callback', 
        'crg-settings', 
        'crg_main_section'
    );  
    add_settings_field(
        'crg_convert_titles', 
        'Convertir titulares a descripciones', 
        'crg_convert_titles_callback', 
        'crg-settings', 
        'crg_main_section'
    ); 
    add_settings_field('crg_debunk_author', 'Autor de desinformación predeterminado', 'crg_debunk_author_callback', 'crg-settings', 'crg_main_section');
    add_settings_field(
        'crg_disable_claim_author',
        'Deshabilitar autor de la afirmación',
        'crg_disable_claim_author_callback',
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

    ?>
        <textarea name='crg_fact_check_tag' rows='5' cols='50'><?= esc_html($tags_string); ?></textarea>
        <p>Ingresá un slug en cada nueva línea.</p>
    <?php
}

function crg_debunk_tag_callback() {
    $tags = get_option('crg_debunk_tag', []);
    if (!is_array($tags)) {
        $tags = explode("\n", $tags);
    }

    $tags_string = implode("\n", $tags);

    ?>
        <textarea name='crg_debunk_tag' rows='5' cols='50'><?= esc_html($tags_string); ?></textarea>
        <p>Ingresá un slug en cada nueva línea.</p>
    <?php
}


function crg_organization_name_callback() {
    $name = get_option('crg_organization_name');

    ?>
        <input type='text' name='crg_organization_name' value='<?= esc_attr($name); ?>' />
    <?php
}

function crg_organization_logo_callback() {
    $logo = get_option('crg_organization_logo');

    ?>
        <input type='text' name='crg_organization_logo' value='<?= esc_attr($logo); ?>' />
    <?php
}

function crg_debunk_author_callback() {
    $author = get_option('crg_debunk_author', 'Social media');

    ?>
        <input type='text' name='crg_debunk_author' value='<?= esc_attr($author); ?>' />
    <?php
}

function crg_post_type_callback() {
    $selected_post_type = get_option('crg_post_type');
    $post_types = get_post_types(array('public' => true), 'objects');

    echo '<select id="crg_post_type" name="crg_post_type">';
    foreach ($post_types as $post_type) {
        $selected = ($selected_post_type === $post_type->name) ? 'selected="selected"' : '';

        ?>
            <option value="<?= esc_attr($post_type->name); ?>"  <?=  esc_attr($selected); ?> > <?= esc_html($post_type->label); ?> </option>
        <?php
    }
    echo '</select>';
}


function crg_taxonomy_fact_check_callback() {
    $selected_taxonomy = get_option('crg_taxonomy_fact_check');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="crg_taxonomy_fact_check" name="crg_taxonomy_fact_check">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';

        ?>
            <option value=" <?= esc_attr($taxonomy->name); ?> "  <?= esc_attr($selected); ?>  > <?=  esc_html($taxonomy->label); ?> . </option>
        <?php
    }
    echo '</select>';
}
    
function crg_taxonomy_debunk_callback() {
    $selected_taxonomy = get_option('crg_taxonomy_debunk');
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select id="crg_taxonomy_debunk" name="crg_taxonomy_debunk">';
    foreach ($taxonomies as $taxonomy) {
        $selected = ($selected_taxonomy === $taxonomy->name) ? 'selected="selected"' : '';

        ?>
            <option value=" <?= esc_attr($taxonomy->name) ?> "  <?= esc_attr( $selected ); ?> > <?= esc_html($taxonomy->label) ?> </option>
        <?php
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
    <p class="description">¡IMPORTANTE!<br/>Orden: arriba va la peor calificación (ej: Falso) y sigue en orden hasta la mejor calificación abajo (ej: Verdadero).</p>

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
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . esc_attr($selected) . '>' . esc_html($taxonomy->label) . '</option>';
    }
    echo '</select>';

}

function crg_convert_titles_callback() {
    $convert_titles = get_option('crg_convert_titles', true);
    echo '<input type="checkbox" name="crg_convert_titles" value="1" ' . checked(1, $convert_titles, false) . '/>';
    echo '<p class="description">Si está activado, convierte automáticamente las negaciones en afirmaciones.</p>';
}

function crg_disable_claim_author_callback() {
    $disable_claim_author = get_option('crg_disable_claim_author', false);
    echo '<input type="checkbox" name="crg_disable_claim_author" value="1" ' . checked(1, $disable_claim_author, false) . '/>';
    echo '<p class="description">Si está activado, se omite el autor de la afirmación del schema ClaimReview.</p>';
}

// Function to extract rating based on the chosen method
function crg_extract_rating($post) {
    $rating_taxonomy = get_option('crg_rating_taxonomy');
    $rating_array = get_option('crg_ratings');
    
    if (empty($rating_array)) {
        return null;
    }

    // Check each rating in order
    foreach ($rating_array as $index => $rating) {
        if (has_any_term($rating, $rating_taxonomy, $post->ID)) {
            return [
                'tag_name' => $rating,
                'rating_value' => $index + 1
            ];
        }
    }
    
    return null;
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

function extract_claim_from_title($post_title) {
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
function crg_generate_claim_review_text($post) {
    $fact_check_tags = get_option('crg_fact_check_tag', array());
    $debunk_tags = get_option('crg_debunk_tag', array());
    
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    $is_fact_check = has_any_term($fact_check_tags, get_option('crg_taxonomy_fact_check'), $post->ID);
    $is_debunk = has_any_term($debunk_tags, get_option('crg_taxonomy_debunk'), $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return 'No es chequeo ni verificación.';
    }
    
    $post_title = get_the_title($post->ID);
    
    if ($is_fact_check) {
        $claim_reviewed = extract_claim_from_title($post_title);
    } else {
        // Verificar si está habilitada la conversión
        $convert_titles = get_option('crg_convert_titles', true);
        if ($convert_titles) {
            $claim_reviewed = negacion_a_afirmacion_simple($post_title);
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
remove_filter('the_content', 'crg_generate_claim_review');

// Add the wp_head action
add_action('wp_head', 'crg_output_claim_review_schema', 99);

// Create new function to output schema in head
function crg_output_claim_review_schema() {
    // Only run on single posts
    if (!is_singular()) {
        return;
    }
    
    global $post;
    
    // Get the tags as arrays
    $fact_check_tags = get_option('crg_fact_check_tag', array());
    $debunk_tags = get_option('crg_debunk_tag', array());
    
    // Ensure we have arrays
    $fact_check_tags = is_array($fact_check_tags) ? $fact_check_tags : array($fact_check_tags);
    $debunk_tags = is_array($debunk_tags) ? $debunk_tags : array($debunk_tags);
    
    // Check for fact check or debunk tags
    $fact_check_taxonomy = get_option('crg_taxonomy_fact_check');
    $debunk_taxonomy = get_option('crg_taxonomy_debunk');
    
    $is_fact_check = has_any_term($fact_check_tags, $fact_check_taxonomy, $post->ID);
    $is_debunk = has_any_term($debunk_tags, $debunk_taxonomy, $post->ID);
    
    if (!$is_fact_check && !$is_debunk) {
        return;
    }

    // Extract rating
    $rating_value = crg_extract_rating($post);
    
    if (!$rating_value) {
        return;
    }
    
    // Get claim reviewed text
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
        $claim_author = isset($matches[1]) ? trim($matches[1]) : 'Desconocido';
    } else {
        $claim_author = get_option('crg_debunk_author', 'Social media');
    }
    
    // Build itemReviewed object
    $item_reviewed = array(
        '@type' => 'Claim',
        'datePublished' => get_the_date('c')
    );

    // Only add author if not disabled
    if (!get_option('crg_disable_claim_author', false)) {
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
    
    // Output the schema in the head
    echo "\n<!-- ClaimReview Schema by ClaimReview Automático -->\n";
    echo '<script type="application/ld+json">';
    echo json_encode($claim_review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</script>\n";
}

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
        'ClaimReview - Descripción de lo que estás chequeando ',
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
        echo '<p class="description">La opción para corregir la frase para ClaimReview estará disponible después de guardar el título del post y actualizar la página.</p>';
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
        echo '<p class="description">Este post no está marcado como chequeo ni como verificación.</p>';
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
            Esta sección permite corregir el texto que se carga en ClaimReview en caso de que contenga algún error. 
            <br/>
            El texto refiere a la frase que se está chequeando o una descripción del contenido qué se está verificando.
        </p>
        <?php 
        // Show automatically calculated claim for reference
        $auto_claim = crg_generate_claim_review_text($post);
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
function crg_save_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['crg_manual_claim_review_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field($_POST['crg_manual_claim_review_nonce']), 'crg_manual_claim_review_nonce')) {
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
