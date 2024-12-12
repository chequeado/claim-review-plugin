<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options
$options_to_delete = array(
    'cra_post_type',
    'cra_taxonomy_fact_check',
    'cra_taxonomy_debunk',
    'cra_fact_check_tag',
    'cra_debunk_tag',
    'cra_rating_taxonomy',
    'cra_organization_name',
    'cra_organization_logo',
    'cra_debunk_author',
    'cra_ratings',
    'cra_convert_titles',
    'cra_disable_claim_author'
);

// Delete each option
foreach ($options_to_delete as $option) {
    delete_option($option);
}
