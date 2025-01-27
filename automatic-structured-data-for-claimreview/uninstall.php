<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options
$options_to_delete = array(
    'asdcr_post_type',
    'asdcr_taxonomy_fact_check',
    'asdcr_taxonomy_debunk',
    'asdcr_fact_check_tag',
    'asdcr_debunk_tag',
    'asdcr_rating_taxonomy',
    'asdcr_organization_name',
    'asdcr_organization_logo',
    'asdcr_debunk_author',
    'asdcr_ratings',
    'asdcr_convert_titles',
    'asdcr_disable_claim_author'
);

// Delete each option
foreach ($options_to_delete as $option) {
    delete_option($option);
}
