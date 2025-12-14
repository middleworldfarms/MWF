/**
 * AJAX Product Category Search for WooCommerce
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Cache DOM elements
        var $searchForm = $('.mwf-product-search');
        var $searchInput = $searchForm.find('input[name="s"]');
        var $categorySelect = $searchForm.find('select[name="product_cat"]');
        var $resultsContainer = $('<div class="mwf-search-results"></div>');
        
        // Add results container after search form
        $searchForm.after($resultsContainer);
        
        // Variables for search delay
        var searchTimer;
        var searchDelay = 500; // ms
        
        // Function to perform AJAX search
        function performSearch() {
            var searchTerm = $searchInput.val();
            var category = $categorySelect.val();
            
            // Don't search if term is too short
            if (searchTerm.length < 3) {
                $resultsContainer.empty().hide();
                return;
            }
            
            $.ajax({
                url: mwf_search.ajax_url,
                type: 'POST',
                data: {
                    action: 'product_category_search',
                    security: mwf_search.nonce,
                    search: searchTerm,
                    category: category
                },
                beforeSend: function() {
                    $resultsContainer.html('<div class="mwf-searching">Searching...</div>').show();
                },
                success: function(response) {
                    if (response.success && response.data) {
                        displayResults(response.data);
                    } else {
                        $resultsContainer.html('<div class="mwf-no-results">' + (mwf_search.no_results || 'No products found') + '</div>');
                    }
                },
                error: function() {
                    $resultsContainer.html('<div class="mwf-error">Error searching products</div>');
                }
            });
        }
        
        // Display search results
        function displayResults(products) {
            $resultsContainer.empty();
            
            if (products.length === 0) {
                $resultsContainer.html('<div class="mwf-no-results">' + (mwf_search.no_results || 'No products found') + '</div>');
                return;
            }
            
            var $resultsList = $('<ul class="mwf-products-list"></ul>');
            
            $.each(products, function(index, product) {
                var $item = $('<li class="mwf-product-item"></li>');
                var $link = $('<a href="' + product.permalink + '"></a>');
                
                $link.append('<div class="mwf-product-image"><img src="' + product.image + '" alt="' + product.title + '"></div>');
                $link.append('<div class="mwf-product-details"><span class="mwf-product-title">' + product.title + '</span><span class="mwf-product-price">' + product.price + '</span></div>');
                
                $item.append($link);
                $resultsList.append($item);
            });
            
            $resultsContainer.append($resultsList);
            
            // Add "View All Results" link
            var searchTerm = $searchInput.val();
            var category = $categorySelect.val();
            var viewAllUrl = '?s=' + encodeURIComponent(searchTerm) + '&post_type=product';
            
            if (category) {
                viewAllUrl += '&product_cat=' + encodeURIComponent(category);
            }
            
            $resultsContainer.append('<div class="mwf-view-all"><a href="' + viewAllUrl + '">View all results</a></div>');
        }
        
        // Event handlers
        $searchInput.on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(performSearch, searchDelay);
        });
        
        $categorySelect.on('change', function() {
            if ($searchInput.val().length >= 3) {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(performSearch, searchDelay);
            }
        });
        
        // Close results when clicking outside
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.mwf-product-search, .mwf-search-results').length) {
                $resultsContainer.hide();
            }
        });
        
        // Submit form normally when clicking search button
        $searchForm.on('submit', function() {
            $resultsContainer.empty().hide();
        });
    });
    
})(jQuery);