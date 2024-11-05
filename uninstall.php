<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options
$options_to_delete = array(
    'crg_post_type',
    'crg_taxonomy_fact_check',
    'crg_taxonomy_debunk',
    'crg_fact_check_tag',
    'crg_debunk_tag',
    'crg_rating_taxonomy',
    'crg_organization_name',
    'crg_organization_logo',
    'crg_debunk_author',
    'crg_ratings'
);

// Delete each option
foreach ($options_to_delete as $option) {
    delete_option($option);
}

// Remove post meta for all posts
global $wpdb;
$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => 'manual_claim_review')
);
