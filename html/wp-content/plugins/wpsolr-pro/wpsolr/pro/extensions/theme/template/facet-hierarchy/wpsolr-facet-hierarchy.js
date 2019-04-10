jQuery(document).ready(function () {

    // Custom event to redraw the hierachy when Ajax has rebuilt the hierarchy.
    jQuery(document).on('wpsolr_on_ajax_success', function (event) {
        jQuery(this).collapse({
            query: '.select_opt.wpsolr_facet_l',
            //clickQuery: ".wpsolr_facet_plus",
            persist: true,
            accordion: false
        });

        jQuery(this).find('.select_opt.wpsolr_facet_l').each(function () {
            jQuery(this).append('<i class="wpsolr_facet_plus fa ' + (jQuery(this).hasClass('open') ? 'fa-minus-circle' : 'fa-plus-circle') + '"></i>');
        });
    });

    // Notify facets update
    jQuery(document).trigger('wpsolr_on_ajax_success');

    // Unbind WPSOLR facets event, before replacing it.
    jQuery(document).off('click', 'div.select_opt');

    jQuery("#res_facets").on('click', "div.select_opt", function (event) {

        if ('facet_type_field' === jQuery(this).data('wpsolr-facet-data').type) {

            if (!jQuery(event.target).hasClass('wpsolr_facet_plus')) {
                // Collapse/uncollapse, and check the facet, when clicking text or image
                //alert('1: ' + jQuery(event.target).attr('class'));
                //alert('1: ' + jQuery(this).attr('class'));

                // Don't uncollapse if unselecting
                if (jQuery(event.target).hasClass('checked') || jQuery(this).hasClass('checked')
                    && !(jQuery(event.target).hasClass('open') || jQuery(this).hasClass('open'))) {
                    //alert('unchecking');
                    event.stopPropagation();
                }

                // Don't collapse if selecting
                if (!(jQuery(event.target).hasClass('checked') || jQuery(this).hasClass('checked'))
                    && (jQuery(event.target).hasClass('open') || jQuery(this).hasClass('open'))) {
                    //alert('checking');
                    event.stopPropagation();
                }

                window.wpsolr_facet_change(jQuery(this), event);
            } else {
                // Just collapse/uncollapse when clicking on up/down icon
                //alert('2: '+ jQuery(event.target).attr('class'));
                //event.stopPropagation();
            }
        }

    });

    jQuery("#res_facets").find(".select_opt").bind("opened", function (e, section) {
        jQuery(this).find('.wpsolr_facet_plus').first().removeClass("fa fa-plus-circle");
        jQuery(this).find('.wpsolr_facet_plus').first().addClass("fa fa-minus-circle");
    });

    jQuery("#res_facets").find(".select_opt").bind("closed", function (e, section) {
        jQuery(this).find('.wpsolr_facet_plus').first().removeClass("fa fa-minus-circle");
        jQuery(this).find('.wpsolr_facet_plus').first().addClass("fa fa-plus-circle");
    });

})
;