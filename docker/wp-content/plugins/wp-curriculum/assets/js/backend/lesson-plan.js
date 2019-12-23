/**
 * All backend custom JavaScript code are here
 *
 */
jQuery(document).ready(function ($) {

    var LessonPlan = {
        updateActivityTitle: function () {
            $(document).on('keyup', '.lp-ac-item input[type=text]', function () {
                var InputValue = $(this).val();
                var ContainerId = $(this).closest('.lp-ac-item').attr('id');
                $('a[href=#' + ContainerId +']').text(InputValue);
            });
        },

        // Add more time elements
        addMoreTimeElements: function () {
            $(document).on('click', '.lp-add-time-element', function () {
                var ClonedDiv = $('.lp-time-element-row:first').clone();
                ClonedDiv.insertAfter('div.lp-time-element-row:last');
                ClonedDiv.find('input[type=text]').val('');
                ClonedDiv.find(':selected').removeAttr('selected');
                $('.remove-time-element').removeClass('disabled').prop('disabled', false);
            });
        },

        // Remove time elements
        removeTimeElements: function () {
            $(document).on('click', '.remove-time-element', function () {
                $(this).closest('.lp-time-element-row').remove();
                if($('.lp-time-element-row').length == 1) {
                    $('.lp-time-element-row .remove-time-element ').addClass('disabled').prop('disabled', true);
                }
            });
        },

        // Add More Related Instructional Objectives
        addMoreObjectives: function () {
            $(document).on('click', '.lp-add-related-objective', function () {
                var ClonedDiv = $('.lp-related-objective-row:first').clone();
                ClonedDiv.insertAfter('div.lp-related-objective-row:last');
                ClonedDiv.find('input[type=text]').val('');
                $('.lp-remove-related-objective').prop('disabled', false);
            });
        },

        // Remove time elements
        removeObjectives: function () {
            $(document).on('click', '.lp-remove-related-objective', function () {
                $(this).closest('.lp-related-objective-row').remove();
                if($('.lp-related-objective-row').length == 1) {
                    $('.lp-related-objective-row .lp-remove-related-objective').prop('disabled', true);
                }
            });
        },

        // Add Activity in Lesson
        addActivityInLesson: function () {
            $(document).on('click', '.lp-add-ac-item', function () {
                var total_form_box = parseInt($('.lp-ac-item').length, 10);
                $.post(ajaxurl, {action:'lp_add_more_activity_callback', row_id: total_form_box}).done(function (response) {
                    if($('div.lp-ac-item').length) {
                        $(response).insertAfter('div.lp-ac-item:last');
                    } else {
                       $('.lp-ac-inner-panel').html(response);
                    }

                    tinymce.execCommand( 'mceRemoveEditor', false, 'oer-lp-activity-detail-' + total_form_box );
                    tinymce.execCommand( 'mceAddEditor', false, 'oer-lp-activity-detail-' + total_form_box );

                    // Create dynamic elements on sidebar
                    var cloned = $('.sidebar-lesson-activities-title li:last').clone();
                    cloned.find('a').attr('href', '#lp-ac-item-' + total_form_box);
                    cloned.find('a').text('Unnamed Activity');
                    cloned.insertAfter('.sidebar-lesson-activities-title li:last');
                    // Toggle reorder button
                    LessonPlan.toggleUpDownButton();
                });
            });
        },

        // Delete module
        deleteModule: function () {
            $(document).on('click', '.lp-remove-module',function(e) {
                var moduleId = $(this).closest('.panel-default').attr('id');
                e.preventDefault();
                $('#lp-confirm').modal({
                    backdrop: 'static',
                    keyboard: false
                })
                .on('click', '#lp-delete-confirm', function(e) {
                    $('#' + moduleId).remove();
                    $('a[href=#' + moduleId +']').parent('li').remove();
                    $('#lp-confirm').modal('hide');
                });
            });
        },

        // Drag and drop elements
        lessonElementSortable: function () {

            $(document).on('click', '.reorder-up', function(){
                var $current = $(this).closest('.lp-element-wrapper');
                var $previous = $current.prev('.lp-element-wrapper');
                if($previous.length !== 0){
                    $current.insertBefore($previous);
                    LessonPlan.changeElementOrder();
                }
                return false;
            });

            $(document).on('click', '.reorder-down', function(){
                var $current = $(this).closest('.lp-element-wrapper');
                var $next = $current.next('.lp-element-wrapper');
                if($next.length !== 0){
                    $current.insertAfter($next);
                    LessonPlan.changeElementOrder();
                }
                return false;
            });

            // Author element reorder
            $(document).on('click', '.author-reorder-up', function(){
                var $current = $(this).closest('.lp-author-element-wrapper');
                var $previous = $current.prev('.lp-author-element-wrapper');
                if($previous.length !== 0){
                    $current.insertBefore($previous);
                    LessonPlan.toggleUpDownButton();
                }
                return false;
            });

            $(document).on('click', '.author-reorder-down', function(){
                var $current = $(this).closest('.lp-author-element-wrapper');
                var $next = $current.next('.lp-author-element-wrapper');
                if($next.length !== 0){
                    $current.insertAfter($next);
                    LessonPlan.toggleUpDownButton();
                }
                return false;
            });


            // For move inner module activity
            $(document).on('click', '.activity-reorder-up', function(){
                var $current = $(this).closest('.lp-ac-item');
                var $previous = $current.prev('.lp-ac-item');
                if($previous.length !== 0){
                    $current.insertBefore($previous);

                    $(".lp-ac-item").each(function (index) {
                        var textAreaId = $(this).find('textarea').attr('id');

                        if (typeof textAreaId !== 'undefined') {
                            tinymce.execCommand( 'mceRemoveEditor', false, textAreaId );
                            tinymce.execCommand( 'mceAddEditor', false, textAreaId );
                        }
                    })
                }
                return false;
            });

            $(document).on('click', '.activity-reorder-down', function(){
                var $current = $(this).closest('.lp-ac-item');
                var $next = $current.next('.lp-ac-item');
                if($next.length !== 0){
                    $current.insertAfter($next);

                    $(".lp-ac-item").each(function (index) {
                        var textAreaId = $(this).find('textarea').attr('id');

                        if (typeof textAreaId !== 'undefined') {
                            tinymce.execCommand( 'mceRemoveEditor', false, textAreaId );
                            tinymce.execCommand( 'mceAddEditor', false, textAreaId );
                        }
                    })
                }
                return false;
            });

            // Material element reorder
            $(document).on('click', '.material-reorder-up', function(){
                var $current = $(this).closest('.lp-material-element-wrapper');
                var $previous = $current.prev('.lp-material-element-wrapper');
                if($previous.length !== 0){
                    $current.insertBefore($previous);
                    LessonPlan.toggleUpDownButton();
                }
                return false;
            });

            $(document).on('click', '.material-reorder-down', function(){
                var $current = $(this).closest('.lp-material-element-wrapper');
                var $next = $current.next('.lp-material-element-wrapper');
                if($next.length !== 0){
                    $current.insertAfter($next);
                    LessonPlan.toggleUpDownButton();
                }
                return false;
            });
            
            // Primary Source element reorder
            $(document).on('click', '.resource-reorder-up', function(){
                var $current = $(this).closest('.lp-primary-resource-element-wrapper');
                var $previous = $current.prev('.lp-primary-resource-element-wrapper');
                if($previous.length !== 0){
                    $current.insertBefore($previous);
                    LessonPlan.toggleUpDownButton();
                }
                return false;
            });

            $(document).on('click', '.resource-reorder-down', function(){
                var $current = $(this).closest('.lp-primary-resource-element-wrapper');
                var $next = $current.next('.lp-primary-resource-element-wrapper');
                if($next.length !== 0){
                    $current.insertAfter($next);
                    LessonPlan.toggleUpDownButton();
                }
                return false;
            });
        },

        // Change order value in hidden field and reinitialize the text editor
        changeElementOrder: function() {
            $("#oer-lp-sortable .lp-element-wrapper").each(function (index) {
                var count = index + 1;

                var position = $(this).find('.element-order').val();
                var newvalue = $(this).find('.element-order').val(count);
                // reassign all of the numbers once it's loaded.

                var textAreaId = $(this).find('textarea').attr('id');

                if (typeof textAreaId !== 'undefined') {
                    tinymce.execCommand( 'mceRemoveEditor', false, textAreaId );
                    tinymce.execCommand( 'mceAddEditor', false, textAreaId );
                }
            });

            LessonPlan.toggleUpDownButton();
        },

        // Show/Hide up/down button
        toggleUpDownButton: function() {
            // Hide the up button in the first child
            $('.reorder-up').removeClass('hide');
            $('.reorder-down').removeClass('hide');
            $('.reorder-up').first().addClass('hide');
            $('.reorder-down').last().addClass('hide');

            // Toggle Activity button order
            $('.activity-reorder-up').removeClass('hide');
            $('.activity-reorder-down').removeClass('hide');
            $('.activity-reorder-up').first().addClass('hide');
            $('.activity-reorder-down').last().addClass('hide');

            // Toggle button from author module
            // Hide up button from first element
            // hide down button from last element
            $('.author-reorder-up').removeClass('hide');
            $('.author-reorder-down').removeClass('hide');
            $('.author-reorder-up').first().addClass('hide');
            $('.author-reorder-down').last().addClass('hide');

            // Show / Hide button on Materials module
            $('.material-reorder-up').removeClass('hide');
            $('.material-reorder-down').removeClass('hide');
            $('.material-reorder-up').first().addClass('hide');
            $('.material-reorder-down').last().addClass('hide');

            // Show / Hide button on Resource module
            $('.resource-reorder-up').removeClass('hide');
            $('.resource-reorder-down').removeClass('hide');
            $('.resource-reorder-up').first().addClass('hide');
            $('.resource-reorder-down').last().addClass('hide');
        },

        // Create dynamic module
        createDynamicModule: function () {

            // Open modal when click one add module button
            $(document).on('click', '#lp-create-dynamic-module', function (e) {
                e.preventDefault();
                $('#lp-dynamic-module-modal').modal({
                    backdrop: 'static',
                    keyboard: false
                })
            });

            $(document).on('click','#lp-create-module-btn', function () {
                var total_form_box = parseInt($('.lp-element-wrapper').length, 10);
                var module_type = $('#module-type').val();

                $.post(ajaxurl, {action:'lp_create_module_callback', module_type: module_type, row_id: total_form_box}).done(function (response) {
                    $(response).insertAfter('div.lp-element-wrapper:last');

                    if (module_type == 'editor') {
                        tinymce.execCommand( 'mceRemoveEditor', false, 'oer-lp-custom-editor-' + total_form_box );
                        tinymce.execCommand( 'mceAddEditor', false, 'oer-lp-custom-editor-' + total_form_box );
                    }

                    $('#lp-dynamic-module-modal').modal('hide');
                    // Toggle reorder button
                    LessonPlan.toggleUpDownButton();
                });
            });
        },

        // Dismiss the plugin installation message
        dismissInstallNotice: function () {
            $(document).on('click', '#oep-lp-dismissible', function () {
                $.post(ajaxurl, {action:'lp_dismiss_notice_callback'}).done(function (response) {

                });
            });
        },
        // Add more author
        addMoreAuthor: function () {
            $(document).on('click', '#lp-add-more-author', function () {
                var ClonedDiv = $('.lp-author-element-wrapper:last').clone();
                ClonedDiv.insertAfter('div.lp-author-element-wrapper:last');
                ClonedDiv.find('input[type=text]').val('');
                ClonedDiv.find('img.lp-oer-person-placeholder').attr('src',lpScript.image_placeholder_url);
                $('.lp-remove-author').removeAttr('disabled');
                LessonPlan.toggleUpDownButton();
            });
        },

        // Delete author
        deleteAuthor: function () {
            $(document).on('click', '.lp-remove-author',function(e) {
                console.log("Clocked");
                var author = $(this).closest('.panel-default');
                var elementId = author.attr('id');
                e.preventDefault();
                $('#lp-delete-author').modal({
                    backdrop: 'static',
                    keyboard: false
                })
                    .on('click', '#lp-author-delete-confirm', function(e) {
                        author.remove();
                        $('a[href=#' + elementId +']').parent('li').remove();
                        $('#lp-delete-author').modal('hide');

                        // Disable delete button for author
                        if($('.lp-author-element-wrapper').length === 1) {
                            $('.lp-remove-author').attr('disabled', 'disabled');
                        }
                    });
            });
        },
        
        // Upload author image
        lpUploadAuthorImage: function () {
            $(document).on('click', '.lp-oer-person-placeholder', function (e) {
                var frame;
                e.preventDefault();
                var dis = $(this);
                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Select Author Picture',
                    button: { text: 'Use Picture' },
                    library: { type: [ 'image' ] },
                    multiple:false
                });

                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    var attachment_url = attachment.url;
                    dis.prev('input').val(attachment_url);
                    dis.attr('src', attachment_url);
                });

                frame.open();
            });
        },

        // Select standards
        lpSelectStandards: function () {
            jQuery(document).on('click', '#lp-select-standard', function (e) {
                e.preventDefault();
                // Open modal
                jQuery('#lpOerStandardModal').modal({
                    backdrop: 'static',
                    keyboard: false
                })
                .on('click', '#lpSelectStandardSaveBtn', function (e) {
                    e.preventDefault();
                    var selectedStandards = [];
                    var selectedHtml = "";
                    jQuery.each(jQuery('#lpOerStandardModal input[type=checkbox]:checked'), function(){
                        var standardId = jQuery(this).val();
                        selectedStandards.push(standardId);
                        //var standardTitle = jQuery(this).next().next('div.lp-notation-description').text();
                        var standardTitle = jQuery(this).next('.oer_stndrd_desc').text();

                        selectedHtml += '<span class="selected-standard-pill">';
                        selectedHtml += standardTitle;
                        selectedHtml += '<a href="javascript:void(0)" class="remove-ss-pill" data-id="'+standardId+'"><i class="fa fa-times"></i>';
                        selectedHtml += '</a></span>';
                    });
                    jQuery('#selected-standard-wrapper').html(selectedHtml);
                    var selectedStandardsIds = selectedStandards.join();
                    jQuery("input[name='oer_lp_standards']").val(selectedStandardsIds);
                    jQuery('#lpOerStandardModal').modal('hide');
                });
            });
        },

        // Remove selected standards from the list
        lpRemoveStandardsFromList: function () {
            jQuery(document).on('click', 'a.remove-ss-pill', function (e) {
                e.preventDefault();
                var dis = jQuery(this);
                var pillId = dis.attr('data-id');
                dis.parent().remove();

                // Update the selected ids in input fields
                var standardsIds =  jQuery("input[name='oer_lp_standards']").val();
                var standardsArr = standardsIds.split(",");
                standardsArr = jQuery.grep(standardsArr, function(value) {
                    return value != pillId;
                });

                standardsIds = standardsArr.join();
                jQuery("input[name='oer_lp_standards']").val(standardsIds);
                // Unchecked the checkbox from popup
                jQuery('#lpOerStandardModal input[value='+pillId+']').attr('checked', false);
            });
        },
        
        // Add materials to the module
        lpAddMaterials: function () {
            $(document).on('click', '.lp-add-materials', function (e) {
                e.preventDefault();
                var moduleName = $(this).attr('data-name');
                var materialsContainer = $(this).prev('.lp-materials-container');

                // Prepare input field name for the filed
                // Called this code on main materials module file selection
                // And When select file form the custom material module
                if (typeof moduleName !== 'undefined') {
                    var lp_oer_materials_input = moduleName;
                } else {
                    var lp_oer_materials_input = 'lp_oer_materials';
                }

                var materialFrame;
                if (materialFrame) {
                    materialFrame.open();
                    return;
                }
                materialFrame = wp.media({
                    title: 'Select Materials',
                    button: { text: 'Use Materials' },
                    //library: { type: [ 'image' ] },
                    multiple:'add'
                });

                // Get selected files
                materialFrame.on('select', function(){
                    var materialHTML = "";
                    var selected = materialFrame.state().get('selection');
                    selected.map(function (attachment) {
                        attachment = attachment.toJSON();
                        // Get the file type and pic the icon according to that
                        var title = "";
                        var icon = "";
                        if ($.inArray(attachment.subtype, ['zip', 'x-7z-compressed']) !== -1) {
                            title = 'Archived';
                            icon = '<i class="fa fa-file-archive-o fa-2x"></i>';
                        } else if($.inArray(attachment.subtype, ['plain']) !== -1) {
                            title = 'Plain text';
                            icon = '<i class="fa fa-file-text-o fa-2x"></i>';
                        } else if($.inArray(attachment.subtype, ['pdf']) !== -1) {
                            title = 'PDF';
                            icon = '<i class="fa fa-file-pdf-o fa-2x"></i>';
                        } else if($.inArray(attachment.type, ['image']) !== -1) {
                            title = 'Image';
                            icon = '<i class="fa fa-file-image-o fa-2x"></i>';
                        } else if($.inArray(attachment.subtype, ['msword', 'vnd.openxmlformats-officedocument.wordprocessingml.document']) !== -1) {
                            title = 'Microsoft Document';
                            icon = '<i class="fa fa-file-word-o fa-2x"></i>';
                        } else if($.inArray(attachment.subtype,['vnd.ms-excel'])) {
                            title = 'Microsoft Excel';
                            icon = '<i class="fa fa-file-excel-o fa-2x"></i>';
                        } else if($.inArray(attachment.subtype,['vnd.ms-powerpoint'])) {
                            title = 'Microsoft Powerpoint';
                            icon = '<i class="fa fa-file-powerpoint-o fa-2x"></i>';
                        }

                        materialHTML += '<div class="panel panel-default lp-material-element-wrapper">' +
                                            '<div class="panel-heading">' +
                                                '<h3 class="panel-title lp-module-title">' +
                                                    '<span class="lp-sortable-handle">' +
                                                        '<i class="fa fa-arrow-down material-reorder-down" aria-hidden="true"></i>' +
                                                        '<i class="fa fa-arrow-up material-reorder-up" aria-hidden="true"></i>' +
                                                    '</span>' +
                                                    '<span class="btn btn-danger btn-sm lp-remove-material" title="Delete"><i class="fa fa-trash"></i></span>' +
                                                '</h3>' +
                                            '</div>' +
                                            '<div class="panel-body">' +
                                                '<div class="form-group">' +
                                                    '<div class="input-group">' +
                                                        '<input type="text" class="form-control" name="' + lp_oer_materials_input + '[url][]" placeholder="URL" value="' + attachment.url + '">' +
                                                        '<div class="input-group-addon oer-lp-material-icon" title="'+ title +'">' + icon + '</div>' +
                                                    '</div>' +
                                                '</div>' +
                                                '<div class="form-group">' +
                                                   '<input type="text" class="form-control" name="' + lp_oer_materials_input + '[title][]" placeholder="Title" value="' + attachment.name + '">' +
                                                '</div>' +
                                                '<div class="form-group">' +
                                                    '<textarea class="form-control" name="' + lp_oer_materials_input + '[description][]" rows="6" placeholder="Description">' + attachment.description + '</textarea>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>';

                    });
                    if (materialsContainer.has('.lp-material-element-wrapper').length) {
                        $(materialHTML).appendTo(materialsContainer);
                    } else {
                        materialsContainer.html(materialHTML);
                    }

                    /*if($('.lp-material-element-wrapper').length) {
                        $(materialHTML).insertAfter('.lp-material-element-wrapper:last');
                    } else {
                        $('#lp-materials-container').html(materialHTML);
                    }*/
                    LessonPlan.toggleUpDownButton();
                });

                materialFrame.open();
            })
        },

        // Prepare the material icon based on the type of selected file
        lpPrepareMaterialIcon: function(attachment) {
            // Get the file type and pic the icon according to that
            var title = "";
            var icon = "";
            if ($.inArray(attachment.subtype, ['zip', 'x-7z-compressed']) !== -1) {
                title = 'Archived';
                icon = '<i class="fa fa-file-archive-o fa-2x"></i>';
            } else if($.inArray(attachment.subtype, ['plain']) !== -1) {
                title = 'Plain text';
                icon = '<i class="fa fa-file-text-o fa-2x"></i>';
            } else if($.inArray(attachment.subtype, ['pdf']) !== -1) {
                title = 'PDF';
                icon = '<i class="fa fa-file-pdf-o fa-2x"></i>';
            } else if($.inArray(attachment.type, ['image']) !== -1) {
                title = 'Image';
                icon = '<i class="fa fa-file-image-o fa-2x"></i>';
            } else if($.inArray(attachment.subtype, ['msword', 'vnd.openxmlformats-officedocument.wordprocessingml.document']) !== -1) {
                title = 'Microsoft Document';
                icon = '<i class="fa fa-file-word-o fa-2x"></i>';
            } else if($.inArray(attachment.subtype,['vnd.ms-excel'])) {
                title = 'Microsoft Excel';
                icon = '<i class="fa fa-file-excel-o fa-2x"></i>';
            } else if($.inArray(attachment.subtype,['vnd.ms-powerpoint'])) {
                title = 'Microsoft Powerpoint';
                icon = '<i class="fa fa-file-powerpoint-o fa-2x"></i>';
            }

            return {title, icon};
        },

        // Update material
        lpUpdateMaterial: function() {
            $(document).on('click', '.oer-lp-material-icon', function (e) {
                e.preventDefault();
                var dis = $(this);
                var elementWrapper = dis.closest('.lp-material-element-wrapper');
                var wraperElementId = dis.closest('.lp-element-wrapper').attr('id');
                    wraperElementId = wraperElementId.split("-");
                var elementNumber = wraperElementId[wraperElementId.length-1];

                var inputUrl = "input[name='lp_oer_materials[url][]']";
                var inputTitle = "input[name='lp_oer_materials[title][]']";
                var inputDescription = "textarea[name='lp_oer_materials[description][]']";

                if ($.isNumeric(elementNumber)) {
                    inputUrl = "input[name='lp_oer_materials_list_"+elementNumber+"[url][]']";
                    inputTitle = "input[name='lp_oer_materials_list_"+elementNumber+"[title][]']";
                    inputDescription = "textarea[name='lp_oer_materials_list_"+elementNumber+"[description][]']";
                }

                var materialFrame;
                if (materialFrame) {
                    materialFrame.open();
                    return;
                }
                materialFrame = wp.media({
                    title: 'Select Material',
                    button: { text: 'Use Material' },
                    multiple: false
                });

                materialFrame.on('select', function(){
                    var attachment = materialFrame.state().get('selection').first().toJSON();
                    var response = LessonPlan.lpPrepareMaterialIcon(attachment);

                    dis.html(response.icon);
                    $(elementWrapper).find(inputUrl).val(attachment.url);
                    $(elementWrapper).find(inputTitle).val(attachment.name);
                    $(elementWrapper).find(inputDescription).val(attachment.description);
                });

                materialFrame.open();
            });
        },

        // Delete Material module
        lpDeleteMaterials: function () {
            $(document).on('click', '.lp-remove-material',function(e) {
                var material = $(this).closest('.panel-default');
                var elementId = material.attr('id');
                e.preventDefault();
                $('#lp-delete-confirm-popup').modal({
                    backdrop: 'static',
                    keyboard: false
                })
                    .on('click', '#lp-delete-confirm-popup-btn', function(e) {
                        material.remove();
                        $('a[href=#' + elementId +']').parent('li').remove();
                        $('#lp-delete-confirm-popup').modal('hide');

                        // Disable delete button for author
                        if($('.lp-material-element-wrapper').length === 1) {
                            $('.lp-remove-material').attr('disabled', 'disabled');
                        }
                    });
            });
        },

        // Search standards on modal
        lpSearchStandards: function () {

            //setup before functions
            var typingTimer;                //timer identifier
            var doneTypingInterval = 1000;  //time in ms, 5 second for example
            var $input = $('#oerLpSearchStandardInput');

            //on keyup, start the countdown
            $input.on('keyup', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(LessonPlan.lpProcessingStandardSearch, doneTypingInterval);
            });

            //on keydown, clear the countdown
            $input.on('keydown', function () {
                clearTimeout(typingTimer);
            });

            $input.on('keypress', function (e) {
                if (e.which == 13) {
                    if ($(this).val() !== '') {
                        LessonPlan.lpProcessingStandardSearch();
                    } else {
                        $('.oer-lp-standard-search-result').addClass('hide');
                        $('.oer-lp-standard-default-result').removeClass('hide');
                    }
                }
            });
        },
        
        // Process the standard search
        lpProcessingStandardSearch: function () {
            var $input = $('#oerLpSearchStandardInput');

            if($input.val() == '') {
                $('.oer-lp-standard-search-result').addClass('hide');
                $('.oer-lp-standard-default-result').removeClass('hide');
                return false;
            }

            var data = {
                action: 'lp_searched_standards_callback',
                post_id: $input.attr('data-post'),
                keyword: $input.val()
            };
            $.post(
                ajaxurl,
                data
            ).done(function (response) {
                var $resultContainer = $(".oer-lp-standard-search-result");
                $resultContainer.html(response);
                $resultContainer.removeClass('hide');
                $('.oer-lp-standard-default-result').addClass('hide');
            });
        },

        // Select lesson document for download copy
        lpDownloadCopyLesson: function() {
            $(document).on('click', 'input[name="oer_lp_download_copy_document"], .oer-lp-download-copy-icon', function (e) {
                e.preventDefault();
                var dis = $(this);

                var materialFrame;
                if (materialFrame) {
                    materialFrame.open();
                    return;
                }
                materialFrame = wp.media({
                    title: 'Select Material',
                    library: { type: [ 'application/msword', 'application/pdf' ] },
                    button: { text: 'Use Material' },
                    multiple: false
                });

                materialFrame.on('select', function(){
                    var attachment = materialFrame.state().get('selection').first().toJSON();
                    var response = LessonPlan.lpPrepareMaterialIcon(attachment);

                    var icon = response.icon;
                        icon = icon.replace('fa-2x', '');

                    $('.oer-lp-download-copy-icon').html(icon);
                    
                    $('input[name="oer_lp_download_copy_document"]').val(attachment.url);
                    if (dis.parent().find('.lp-selected-section')) {
                        dis.parent().find('.lp-selected-section a').attr('href',attachment.url);
                        dis.parent().find('.lp-selected-section a').text(attachment.url);
                        dis.parent().find('.lp-selected-section').removeClass('lp-hidden');
                        dis.parent().find('.oer-lp-select-label').addClass('lp-hidden');
                        dis.parent().find('.oer-lp-download-copy-icon').addClass('lp-hidden');
                    }
                });

                materialFrame.open();
            });
        },

        // Add More Primary resources
        addMorePrimaryResource: function () {
            $(document).on('click', '.lp-add-more-resource', function () {
                var total_form_box = parseInt($('.lp-primary-resource-element-panel').find('.lp-primary-resource-element-wrapper').length, 10);
                total_form_box += 1;
                $.post(ajaxurl, {action:'lp_add_more_pr_callback', row_id: total_form_box}).done(function (response) {
                    if($('div.lp-primary-resource-element-wrapper').length) {
                        $(response).insertAfter('div.lp-primary-resource-element-wrapper:last').tinymce_textareas();
                    } else {
                        $('.lp-primary-resource-element-panel').html(response).tinymce_textareas();
                    }
                    
                    if (typeof( tinymce ) == "object" && typeof( tinymce.execCommand ) == "function" ) {
                        tinymce.execCommand( 'mceRemoveEditor', false, 'oer-lp-resource-teacher-' + total_form_box );
                        tinymce.execCommand( 'mceAddEditor', false, 'oer-lp-resource-teacher-' + total_form_box );
                        quicktags({ id: 'oer-lp-resource-teacher-' + total_form_box });
                        
                        tinymce.execCommand( 'mceRemoveEditor', false, 'oer-lp-resource-student-' + total_form_box );
                        tinymce.execCommand( 'mceAddEditor', false, 'oer-lp-resource-student-' + total_form_box );
                        quicktags({ id: 'oer-lp-resource-student-' + total_form_box });
                    }

                    // Toggle reorder button
                   LessonPlan.toggleUpDownButton();
                });
            });
            $.fn.tinymce_textareas = function(){
                tinyMCE.init({
                    skin: 'wp_theme',
                    mode: 'exact',
                    menubar: false,
                    toolbar: 'bold italic underscore blockquote strikethrough bullist numlist alignleft aligncenter alignright undo redo'
                });
            }
        },

        // Delete source
        deletePrimarySource: function () {
            $(document).on('click', '.lp-remove-source',function(e) {
                var source = $(this).closest('.panel-default');
                var elementId = source.attr('id');
                e.preventDefault();
                $('#lp-delete-source').modal({
                    backdrop: 'static',
                    keyboard: false
                })
                    .on('click', '#lp-source-delete-confirm', function(e) {
                        source.remove();
                        $('a[href=#' + elementId +']').parent('li').remove();
                        $('#lp-delete-source').modal('hide');

                        // Disable delete button for author
                        if($('.lp-primary-source-element-wrapper').length === 1) {
                            $('.lp-remove-source').attr('disabled', 'disabled');
                        }
                    });
            });
        },
        
        requireModuleTitle: function(){
            var validated = false;
            $(document).on('click', '#publishing-action #publish',function(e) {
                if (validated==true) {
                    validated = false;
                    return;
                }
                e.preventDefault();
                var custom_editor = $(".oer-lp-introduction-group[id^=oer-lp-custom-editor-group");
                if (custom_editor.length>0) {
                    $.each(custom_editor, function(index, value){
                        var id = $(this).attr('id');
                        if ($(this).is(":visible")){
                            title = $(this).find("input[name$='[title]']");
                            if (title.val()!=="") {
                                validated = true;
                            } else {
                                $(document).scrollTop = title.scrollTop;
                                title.after("<span class='error' style='color:#ff0000;'>Please enter a title</span>")
                                title.focus();
                                validated = false;
                                return false;
                            }
                        }
                    });
                    if (validated==true) {
                        $(this).trigger("click");
                    }
                }
            });
        },
        // Select lesson document for download copy
        lpRemoveCopyLesson: function() {
            $(document).on('click', '.lp-remove-download-copy', function (e) {
                e.preventDefault();
                var dis = $('input[name="oer_lp_download_copy_document"]');

                $('.lp-selected-section').addClass('lp-hidden');
                $('input[name="oer_lp_download_copy_document"]').val("");
                $('.oer-lp-select-label').removeClass('lp-hidden');
                $('.oer-lp-download-copy-icon').removeClass('lp-hidden').html('<i class="fa fa-upload"></i>');
                
                /*var materialFrame;
                if (materialFrame) {
                    materialFrame.open();
                    return;
                }
                materialFrame = wp.media({
                    title: 'Select Material',
                    library: { type: [ 'application/msword', 'application/pdf' ] },
                    button: { text: 'Use Material' },
                    multiple: false
                });

                materialFrame.on('select', function(){
                    var attachment = materialFrame.state().get('selection').first().toJSON();
                    var response = LessonPlan.lpPrepareMaterialIcon(attachment);

                    var icon = response.icon;
                        icon = icon.replace('fa-2x', '');

                    $('.oer-lp-download-copy-icon').html(icon);
                    
                    $('input[name="oer_lp_download_copy_document"]').val(attachment.url);
                    if (dis.parent().find('.lp-selected-section')) {
                        dis.parent().find('.lp-selected-section a').attr('href',attachment.url);
                        dis.parent().find('.lp-selected-section a').text(attachment.url);
                        dis.parent().find('.lp-selected-section').removeClass('lp-hidden');
                        dis.parent().find('.oer-lp-select-label').addClass('lp-hidden');
                        dis.parent().find('.oer-lp-download-copy-icon').addClass('lp-hidden');
                    }
                });

                materialFrame.open();*/
            });
        },
        
        // Select lesson document for download copy
        lpPrimarySourceSensitiveMaterial: function() {
            $(document).on('change', 'input[name="oer_lp_primary_resources[sensitive_material][]"]', function (e) {
                e.preventDefault();
                var dis = $(this);
                var val = "no";
                if (dis.is(":checked")) {
                    val = "yes"
                } 
                dis.parent().find('input[name="oer_lp_primary_resources[sensitive_material_value][]"]').val(val);
            });
        },
        
        // Select Type
        lpOtherCurriculumType: function() {
            $(document).on('change', 'select[name="oer_lp_type"]', function (e) {
                var dis = $(this);
                if (dis.val()=="Other")
                    $('.other-type-group').removeClass('hidden').show();
                else
                    $('.other-type-group').hide();
            });
        }
    };

    // Initialize all function on ready state
    LessonPlan.updateActivityTitle();
    LessonPlan.addMoreTimeElements();
    LessonPlan.removeTimeElements();
    LessonPlan.addMoreObjectives();
    LessonPlan.removeObjectives();
    LessonPlan.addActivityInLesson();
    LessonPlan.deleteModule();
    LessonPlan.lessonElementSortable();
    LessonPlan.createDynamicModule();
    LessonPlan.toggleUpDownButton();
    LessonPlan.dismissInstallNotice();
    LessonPlan.addMoreAuthor();
    LessonPlan.deleteAuthor();
    LessonPlan.lpUploadAuthorImage();
    LessonPlan.lpSelectStandards();
    LessonPlan.lpRemoveStandardsFromList();
    LessonPlan.lpAddMaterials();
    LessonPlan.lpUpdateMaterial();
    LessonPlan.lpDeleteMaterials();
    LessonPlan.lpSearchStandards();
    LessonPlan.lpDownloadCopyLesson();
    LessonPlan.addMorePrimaryResource();
    LessonPlan.deletePrimarySource();
    LessonPlan.requireModuleTitle();
    LessonPlan.lpRemoveCopyLesson();
    LessonPlan.lpPrimarySourceSensitiveMaterial();
    LessonPlan.lpOtherCurriculumType();
});

//Process Initial Setup
function lpInitialSettings(form) {
    setTimeout(function() {
        var Top = document.documentElement.scrollTop || document.body.scrollTop;
        jQuery('.loader .loader-img').css({'padding-top':Top + 'px'});
        jQuery('.loader').show();
    } ,1000);
    return true;
}