$ = jQuery;

$(document).ready(function() {
    $('#show-excerpt').click(function() {
        toggleArrow('#framework-excerpt', '#show-excerpt');
        $('#framework-excerpt').slideToggle('slow', function() {
            if ($(this).is(':visible')) { 
                $(this).css({'display': 'flex', 'align-items': 'stretch'});
            } else {
            }
        })
    })
    
    $('#close-excerpt').click(function() {
        toggleArrow('#framework-excerpt', '#show-excerpt');
        $('#framework-excerpt').slideUp('slow');
    })
    
    $(".open-standards").click(function() {
        $("#standards-dialog").dialog({
            modal: true,
            width: 700,
            title: 'Standards',
            draggable: false,
            resizable: false,
            dialogClass: 'standards-dialog'
        });
    });

    $(".open-tags").click(function() {
        $("#tags-dialog").dialog({
            modal: true,
            width: 700,
            minHeight: 200,
            title: 'Tags',
            draggable: false,
            resizeable: false,
            dialogClass: 'tags-dialog',
            create: function(event, ui) { 
                var widget = $(this).dialog("widget");
                $(".ui-dialog-titlebar-close span.ui-button-icon-primary", widget)
                    .removeClass("ui-icon-closethick ui-icon")
                    .addClass("fal fa-times");
             }
        });
    })
    
    if ($('.tc-home-tabs-content .active .tc-tab-content').height()<340) {
        $('.tc-home-tabs-content .active .tc-read-more').addClass('tc-btn-hide');
    }
    $(".tc-read-more").on("click",function(e){
        var tabContent = $(this).parent().find('.tc-tab-content');
        var minHeight = 340;
        if (tabContent.height()<=minHeight) {
            tabContent.addClass("slidedown");
            $(this).text("Close");
        } else {
            tabContent.removeClass("slidedown");
            $(this).text("Read More");
        }
    });
    
    if ($('.excerpt-section-custom-width').is(":visible")) {
        excerpt_height = $('.excerpt-section-custom-width').innerHeight();
        border_width = Math.floor(excerpt_height/2);
        $('.tc-pink-triangle').css({
            'border-top': border_width.toString() + 'px solid transparent',
            'border-bottom': border_width.toString() + 'px solid transparent'
        });
    }

    splitTextIntoColumns('#initial-excerpt');
    splitTextIntoColumns('#tc-historical-background-tab-content .tc-tab-content span');
})

function toggleArrow(parentID, iconID) {
    if (!$(parentID) || $(!iconID)) { return; }

    if ($(parentID).is(':visible')) {
        $(`${iconID} i`).removeClass('fa-angle-up').addClass('fa-angle-down');
    } else {
        $(`${iconID} i`).removeClass('fa-angle-down').addClass('fa-angle-up');
    }
}

function splitTextIntoColumns(selector) {
    if (!$(selector)) { return 'Element not found'; }
    let text = $(selector).text(),
        textLength = text.length;
    if (textLength < 1000) { return; }

    let words = text.split(' '),
        wordCount = words.length,
        part1 = words.slice(0, (wordCount/ 2) + 5).join(' '),
        part2 = words.slice(((wordCount / 2) + 5), words.length).join(' ');

    $(selector).text(part1);
    $(`<span id="appended_text">${part2}</span>`).insertAfter(selector);
}