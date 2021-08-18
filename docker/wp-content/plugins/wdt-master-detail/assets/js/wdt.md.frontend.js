if (Object.keys(wpDataTablesHooks).length > 0) {
    wpDataTablesHooks['onRenderDetails'] = []
} else {
    var wpDataTablesHooks = {
        onRenderDetails: []
    }
}

/**
 * Show popup Detail modal
 */
wpDataTablesHooks.onRenderDetails.push(function showDetailModal(tableDescription) {
    (function ($) {

        if (tableDescription.masterDetail) {
            // Disable Details button on load
            $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');

            // Check is it enabled Master-detail functionality, Click event and Editing
            if (tableDescription.masterDetailLogic === 'row' &&
                tableDescription.masterDetailRender === 'popup' &&
                tableDescription.editable) {

                // Event for showing Detail modal after clicking on Detail button
                $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').on('click', function () {

                    showDetailsModal(this, tableDescription);
                });

                helpFunctions(tableDescription);

            } else if (tableDescription.masterDetailLogic === 'row' &&
                tableDescription.masterDetailRender === 'popup' &&
                !tableDescription.editable) {

                // Event for showing Detail modal after clicking on table row
                $(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' tbody').on('mouseenter', 'tr', function () {

                    $(this).css("cursor", "pointer");

                }).on('click', 'tr', function (e) {
                    if (!$(e.target).closest('a').length){
                        showDetailsModal(this, tableDescription);
                    }
                });

                helpFunctions(tableDescription);

            } else if (tableDescription.masterDetailLogic === 'button' && tableDescription.masterDetailRender === 'popup') {

                // Event for removing Details button
                $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').remove();

                // Master-detail ClickEvent for links or buttons in Master-detail column
                var mdClickEvent = function (e) {
                    e.stopPropagation();
                    showDetailsModal(this, tableDescription);
                };
                var ua = navigator.userAgent,
                    event = (ua.match(/iPad/i)) ? "touchstart" : "click";

                $(document).off(event, tableDescription.selector + ' tbody tr td .master_detail_column_btn').on(event, tableDescription.selector + ' tbody tr td .master_detail_column_btn', mdClickEvent);

                helpFunctions(tableDescription);
            }

            function helpFunctions(tableDescription) {

                // Render Detail dialog in DOM and hide it
                $('#wdt-md-modal').on('hidden.bs.modal', function (e) {
                    $(tableDescription.selector + '_wrapper').append($(tableDescription.selector + '_md_dialog').hide());
                });

                // If is turn on Popover Tools, add Details button in popover
                if (tableDescription.popoverTools) {
                    $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').prependTo(tableDescription.selector + '_wrapper .wpDataTablesPopover.editTools').css('float', 'right');
                }
            }

            // Show details modal for all tables
            function showDetailsModal(obj, tableDescription) {
                var modal = $('#wdt-md-modal');
                var modalTitle = tableDescription.masterDetailPopupTitle !== '' ? tableDescription.masterDetailPopupTitle : wdtMdTranslationStrings.modalTitle;

                if ($(obj).hasClass('disabled'))
                    return false;

                if (tableDescription.editable && tableDescription.popoverTools) {
                    $('.wpDataTablesPopover.editTools').hide();
                }

                modal.find('.modal-title').html(modalTitle);
                modal.find('.modal-body').html('');
                modal.find('.modal-footer').html('');
                var rowData;

                if ((tableDescription.masterDetailLogic === 'button' || tableDescription.masterDetailLogic === 'row') && $(obj).parents('.columnValue').length) {
                    rowData = $(obj).closest('tr').prevAll('.detail-show');
                } else if (tableDescription.editable && tableDescription.masterDetailLogic === 'row') {
                    rowData = $(tableDescription.selector + ' tr.selected');
                } else if (tableDescription.masterDetailLogic === 'button') {
                    rowData = $(obj).closest('tr');
                } else {
                    rowData = $(obj);
                }

                var row = rowData.get(0);

                var data = wpDataTables[tableDescription.tableId].fnGetData(row);

                $(data).each(function (index, el) {
                    var $columnValue = $('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');
                    if (el) {
                        var val = el.toString();
                    } else {
                        var val = '';
                    }

                    $columnValue.html(val);

                });
                modal.find('.modal-body').append($(tableDescription.selector + '_md_dialog').show());
                modal.modal('show');
            }
        }

    })(jQuery);
});

/**
 * Show details on new page or post
 */
wpDataTablesHooks.onRenderDetails.push(function sendDetails(tableDescription) {
    (function ($) {
        if (tableDescription.masterDetail) {
            if (tableDescription.masterDetailLogic === 'row' && !tableDescription.editable &&
            (tableDescription.masterDetailRender === 'wdtNewPage' || tableDescription.masterDetailRender === 'wdtNewPost')) {
                $(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' tbody').on('mouseenter', 'tr', function () {

                    $(this).css("cursor", "pointer");

                }).on('click', 'tr', function (e) {
                    if ($(this).hasClass('row-detail')) {
                        rowData = $(this).closest('tr').prevAll('.detail-show');
                    } else {
                        rowData = $(this);
                    }

                    var row = rowData.get(0);

                    var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                    var detailObject = {};
                    $(data).each(function (index, el) {
                        var $columnValue = $('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');

                        $columnValue = $columnValue[0].id.replace(tableDescription.tableId + "_", "");
                        if (el) {
                            var val = el.toString();
                        } else {
                            var val = '';
                        }
                        if ($columnValue != 'masterdetail_detials') {
                            $columnValue = $columnValue.replace('_detials', '');
                            detailObject[$columnValue] = val;
                        }

                    });
                    detailObject['wdt_md_id_table'] = tableDescription.dataTableParams.wpdatatable_id;
                    $inputValue = $('#' + tableDescription.tableId + '_md_dialog .wdt_md_hidden_data');
                    $submitButton = $('#' + tableDescription.tableId + '_md_dialog .master_detail_column_btn');
                    $inputValue[0].value = JSON.stringify(detailObject);
                    $submitButton.click();
                });
            } else if (tableDescription.masterDetailLogic === 'row' && tableDescription.editable &&
                (tableDescription.masterDetailRender === 'wdtNewPage' || tableDescription.masterDetailRender === 'wdtNewPost')) {

                $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').on('click', function () {
                    if ($(tableDescription.selector + ' tbody tr.selected').length > 0) {
                        rowData = $(tableDescription.selector + ' tr.selected');

                        var row = rowData.get(0);

                        var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                        var detailObject = {};
                        $(data).each(function (index, el) {
                            var $columnValue = $('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');

                            $columnValue = $columnValue[0].id.replace(tableDescription.tableId + "_", "");
                            if (el) {
                                var val = el.toString();
                            } else {
                                var val = '';
                            }
                            if ($columnValue != 'masterdetail_detials') {
                                $columnValue = $columnValue.replace('_detials', '');
                                detailObject[$columnValue] = val;
                            }

                        });
                        detailObject['wdt_md_id_table'] = tableDescription.dataTableParams.wpdatatable_id;
                        $inputValue = $('#' + tableDescription.tableId + '_md_dialog .wdt_md_hidden_data');
                        $submitButton = $('#' + tableDescription.tableId + '_md_dialog .master_detail_column_btn');
                        $inputValue[0].value = JSON.stringify(detailObject);
                        $submitButton.click();
                    }

                });

                if (tableDescription.popoverTools) {
                    $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').prependTo(tableDescription.selector + '_wrapper .wpDataTablesPopover.editTools').css('float', 'right');
                }

            } else if (tableDescription.masterDetailLogic === 'button' &&
                (tableDescription.masterDetailRender === 'wdtNewPage' || tableDescription.masterDetailRender === 'wdtNewPost')) {

                $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').remove();

                var mdClickEvent = function (e) {
                    e.stopPropagation();
                        if ($(this).closest('tr').hasClass('row-detail')) {
                            rowData = $(this).closest('tr').prevAll('.detail-show');
                        } else {
                            rowData = $(this).closest('tr');
                        }

                        var row = rowData.get(0);

                        var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                        var detailObject = {};
                        $(data).each(function (index, el) {
                            var $columnValue = $('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');

                            $columnValue = $columnValue[0].id.replace(tableDescription.tableId + "_", "");
                            if (el) {
                                var val = el.toString();
                            } else {
                                var val = '';
                            }
                            if ($columnValue != 'masterdetail_detials') {
                                $columnValue = $columnValue.replace('_detials', '');
                                detailObject[$columnValue] = val;
                            }

                        });
                        detailObject['wdt_md_id_table'] = tableDescription.dataTableParams.wpdatatable_id;
                        $inputValue = $(this).closest('form.wdt_md_form').find('input.wdt_md_hidden_data');
                        $inputValue[0].value = JSON.stringify(detailObject);
                };
                var ua = navigator.userAgent,
                    event = (ua.match(/iPad/i)) ? "touchstart" : "click";

                $(document).off(event, tableDescription.selector + ' tbody tr td .master_detail_column_btn').on(event, tableDescription.selector + ' tbody tr td .master_detail_column_btn', mdClickEvent);

            }
        }
    })(jQuery);
});
