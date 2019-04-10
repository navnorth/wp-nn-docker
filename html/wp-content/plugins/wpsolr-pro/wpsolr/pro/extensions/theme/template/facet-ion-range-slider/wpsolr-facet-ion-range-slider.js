jQuery(document).ready(function ($) {

    function updateInputs(data) {

        var from = data.from, to = data.to;

        var facet = data.input.closest(".select_opt");
        var facet_data = facet.data('wpsolr-facet-data');
        facet_data.from = from;
        facet_data.to = to;
        facet_data.item_value = facet_data.from + "-" + facet_data.to;
        $(this).data('wpsolr-facet-data', facet_data);

        window.wpsolr_facet_change(facet, null);
    }


    function refresh() {

        $(".wpsolr_facet_slider_ion").each(function (index) {

            var regex = /(wpsolr_facet_class_\S*)/g;
            var match = regex.exec($(this).attr("class"));
            var facet_class_uuid = (null !== match) ? match[1] : '';
            var localized_data = window["wpsolr_localize_script_layout_" + facet_class_uuid];

            var custom_options = (typeof wpsolr_ion_range_slider_options === 'undefined') ? {} : (wpsolr_ion_range_slider_options[facet_class_uuid] || {});

            var default_options = {
                type: "double",
                onFinish: updateInputs,
                onUpdate: updateInputs
            };

            // Override default parameters with custom parameters
            var parameters = $.extend(true, {},
                default_options,
                custom_options
            );

            $(this).find("." + localized_data.data.js_layout_class).ionRangeSlider(parameters);

        });
    }

    // Custom event to redraw the component after Ajax
    $(document).on('wpsolr_on_ajax_success', function (event) {

        refresh();
    });

    // Initialize
    refresh();
});