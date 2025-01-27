/**
 * Admin JavaScript for Automatic structured data for ClaimReview
 * 
 * Dependencies:
 * - jQuery (provided by WordPress)
 * - wp.escapeHtml (provided by WordPress)
 * - asdcrAdmin.ajaxurl (localized via wp_localize_script)
 * - asdcrAdmin.nonce (localized via wp_localize_script)
 */

(function($) {
    'use strict';

    /**
     * Updates taxonomy terms in a select element based on chosen taxonomy
     * @param {string} taxonomy - The taxonomy slug
     * @param {jQuery} selectElement - jQuery object for the select element to update
     */
    function updateTaxonomyTerms(taxonomy, selectElement) {
        // Show loading state
        selectElement.prop('disabled', true);
        
        $.ajax({
            url: asdcrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_taxonomy_terms',
                taxonomy: taxonomy,
                nonce: asdcrAdmin.nonce
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    // Clear existing options
                    selectElement.empty();
                    
                    // Add new options
                    response.data.forEach(function(term) {
                        var option = new Option(
                            wp.escapeHtml(term.name),
                            wp.escapeHtml(term.slug)
                        );
                        selectElement.append(option);
                    });
                } else {
                    console.warn('Invalid response format from server');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching taxonomy terms:', error);
            },
            complete: function() {
                // Re-enable select
                selectElement.prop('disabled', false);
            }
        });
    }

    // Document ready handler
    $(function() {
        // Cache selectors
        const $factCheckTaxonomy = $('#asdcr_taxonomy_fact_check');
        const $debunkTaxonomy = $('#asdcr_taxonomy_debunk');
        const $ratingTaxonomy = $('#asdcr_rating_taxonomy');
        
        // Handle fact check taxonomy changes
        $factCheckTaxonomy.on('change', function() {
            const $targetSelect = $('select[name="asdcr_fact_check_tag[]"]');
            if ($targetSelect.length) {
                updateTaxonomyTerms($(this).val().trim(), $targetSelect);
            }
        });

        // Handle debunk taxonomy changes
        $debunkTaxonomy.on('change', function() {
            const $targetSelect = $('select[name="asdcr_debunk_tag[]"]');
            if ($targetSelect.length) {
                updateTaxonomyTerms($(this).val().trim(), $targetSelect);
            }
        });

        // Handle rating taxonomy changes
        $ratingTaxonomy.on('change', function() {
            const $targetSelect = $('select[name="asdcr_ratings[]"]');
            if ($targetSelect.length) {
                updateTaxonomyTerms($(this).val().trim(), $targetSelect);
            }
        });
    });

})(jQuery);