jQuery(document).ready(function ($) {

    $.wpsolr_cached_script = function (url, options) {

        // Allow user to set any option except for dataType, cache, and url
        options = $.extend(options || {}, {
            dataType: "script",
            cache: true,
            url: url
        });

        // Use $.ajax() since it is more flexible than $.getScript
        // Return the jqXHR object so we can chain callbacks
        return jQuery.ajax(options);
    };

    function refresh($create) {

        $(".wpsolr_facet_select2").each(function (index) {

            var regex = /(wpsolr_facet_class_\S*)/g;
            var match = regex.exec($(this).attr("class"));
            var facet_class_uuid = (null !== match) ? match[1] : '';
            var localized_data = window["wpsolr_localize_script_layout_" + facet_class_uuid];

            var custom_options = (typeof wpsolr_select2_options === 'undefined') ? {} : (wpsolr_select2_options[facet_class_uuid] || {});

            var default_options = {
                "theme": "default"
            };

            var mandatory_options = {
                "closeOnSelect": true
            };

            // Override default parameters with custom parameters
            var parameters = $.extend(true, {},
                default_options,
                custom_options,
                mandatory_options
            );

            var object = $(this).find("." + 'wpsolr-select2');

            if (parameters.language) {
                // Load i18n select2 language file before.

                $.wpsolr_cached_script(localized_data.data.js_layout_files['dir_i18n'] + parameters.language + ".js").done(function (script, textStatus) {
                    object.select2(parameters);
                });

            } else {
                object.select2(parameters);
            }


            object.on('select2:select', function (e) {
                wpsolr_facet_change($(document.getElementById(e.params.data.id)), e);
            });

            object.on('select2:unselect', function (e) {
                // Prevent opening the select on removing a tag
                e.params.originalEvent.stopPropagation();

                wpsolr_facet_change($(document.getElementById(e.params.data.id)), e);
            });

        });

    }

    // Custom event to redraw the component after Ajax
    $(document).on('wpsolr_on_ajax_success', function (event) {
        refresh(false);
    });

    // Initialize
    refresh(true);
});