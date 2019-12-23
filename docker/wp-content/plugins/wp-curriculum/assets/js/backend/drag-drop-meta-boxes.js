// JavaScript Document
jQuery(document).ready(function ($) {

    // Sort the parents
    $(".sortable").sortable({
        containment: "document",
        items: "> div",
        handle: ".move",
        tolerance: "pointer",
        cursor: "move",
        opacity: 0.7,
        revert: 300,
        delay: 150,
        placeholder: "movable-placeholder",
        start: function(e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        }
    });

    $('.sortable').on("sortstop", function (event, ui) {
        $(".sortable .order").each(function (index) {
            //console.log(index + 1);
            var count = index + 1;
            console.log("reorder" + count);

            var position = $(this).val();
            console.log("position value is"  + position);
            var newvalue = $(this).val(count);
            // reassign all of the numbers once it's loaded.

        })
    });
});