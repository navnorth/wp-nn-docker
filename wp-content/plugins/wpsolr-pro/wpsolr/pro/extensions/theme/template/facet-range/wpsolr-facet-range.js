jQuery(document).ready(function () {

    jQuery("#res_facets").on('click', ".select_opt", function (event) {

        if ('facet_type_range' === jQuery(this).data('wpsolr-facet-data').type) {

            window.wpsolr_facet_change(jQuery(this), event);
        }

    });

});