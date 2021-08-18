(function ($) {
    $(function () {

        /**
         * Extend wpdatatable_config object with new properties and methods
         */
        $.extend(wpdatatable_config, {
            masterDetail: 0,
            masterDetailLogic: '',
            masterDetailRender: '',
            masterDetailRenderPage: '',
            masterDetailRenderPost: '',
            masterDetailPopupTitle: '',
            setMasterDetail: function (masterDetail) {
                let state = false;
                let masterColumn;
                wpdatatable_config.masterDetail = masterDetail;
                $('#wdt-md-toggle-master-detail').prop('checked', masterDetail);
                if (masterDetail == 1) {
                    jQuery('.wdt-md-column-block').removeClass('hidden');
                    jQuery('.wdt-md-click-event-logic-block').animateFadeIn();
                    jQuery('.wdt-md-render-data-in-block').animateFadeIn();
                    jQuery('.wdt-md-popup-title-block').animateFadeIn();
                    jQuery('.wdt-md-click-event-logic-block').show();
                    jQuery('.wdt-md-render-data-in-block').show();
                    jQuery('.wdt-md-popup-title-block').show();
                    jQuery('#wdt-md-click-event-logic').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-render-data-in').selectpicker('refresh').trigger('change');

                } else {
                    jQuery('.wdt-md-click-event-logic-block').hide();
                    jQuery('.wdt-md-render-data-in-block').hide();
                    jQuery('.wdt-md-popup-title-block').hide();
                    jQuery('.wdt-md-render-post-block').hide();
                    jQuery('.wdt-md-render-page-block').hide();
                    jQuery('.wdt-md-column-block').addClass('hidden');
                    wpdatatable_config.setMasterDetailPopupTitle('');
                    wpdatatable_config.setMasterDetailLogic('row');
                    wpdatatable_config.setMasterDetailRender('popup');

                    for (let column of wpdatatable_config.columns) {
                        if (column.orig_header === 'masterdetail') {
                            state = true;
                            masterColumn = column;
                        }
                    }
                    if (state) {
                        //fix column positions after deleting masterdetail column
                        for (var i = masterColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                            wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                        }
                        //remove masterdetaisl object from columns_by_headers
                        wpdatatable_config.columns_by_headers = _.omit(
                            wpdatatable_config.columns_by_headers, masterColumn.orig_header);

                        //remove masterdetail column from columns
                        wpdatatable_config.columns = _.reject(
                            wpdatatable_config.columns,
                            function (el) {
                                return el.orig_header == masterColumn.orig_header;
                            });
                    }

                }
            },
            setMasterDetailLogic: function (masterDetailLogic) {
                wpdatatable_config.masterDetailLogic = masterDetailLogic;
                let state = false;
                let masterColumn;
                for (let column of wpdatatable_config.columns) {
                    if (column.orig_header === 'masterdetail') {
                        state = true;
                        masterColumn = column;
                    }
                }
                if (wpdatatable_config.currentOpenColumn == null && wpdatatable_config.masterDetailLogic === 'row') {

                    if (state) {
                        //fix column positions after deleting masterdetail column
                        for (var i = masterColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                            wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                        }
                        //remove masterdetaisl object from columns_by_headers
                        wpdatatable_config.columns_by_headers = _.omit(
                            wpdatatable_config.columns_by_headers, masterColumn.orig_header);

                        //remove masterdetaisl column from columns
                        wpdatatable_config.columns = _.reject(
                            wpdatatable_config.columns,
                            function (el) {
                                return el.orig_header == masterColumn.orig_header;
                            });
                    }

                } else if (wpdatatable_config.currentOpenColumn == null && wpdatatable_config.masterDetailLogic === 'button') {

                    if (!state) {
                        //Adding a new Master-detail column
                        wpdatatable_config.addColumn(
                            new WDTColumn(
                                {
                                    type: 'masterdetail',
                                    orig_header: 'masterdetail',
                                    display_header: 'Details',
                                    pos: wpdatatable_config.columns.length,
                                    details: 'masterdetail',
                                    parent_table: wpdatatable_config
                                }
                            )
                        );
                    }
                }
                $('#wdt-md-click-event-logic')
                    .val( masterDetailLogic )
                    .selectpicker('refresh');
            },
            setMasterDetailRender: function (masterDetailRender) {
                wpdatatable_config.masterDetailRender = masterDetailRender;
                $('#wdt-md-render-data-in').selectpicker('val', masterDetailRender);
                if ( wpdatatable_config.masterDetailRender == 'wdtNewPage'){
                    jQuery('.wdt-md-render-post-block').hide();
                    jQuery('.wdt-md-popup-title-block').hide();
                    jQuery('.wdt-md-render-page-block').animateFadeIn();
                    jQuery('#wdt-md-render-page-block').selectpicker('refresh').trigger('change');
                }else if ( wpdatatable_config.masterDetailRender == 'wdtNewPost'){
                    jQuery('.wdt-md-render-page-block').hide();
                    jQuery('.wdt-md-popup-title-block').hide();
                    jQuery('.wdt-md-render-post-block').animateFadeIn();
                    jQuery('#wdt-md-render-post-block').selectpicker('refresh').trigger('change');
                } else if ( wpdatatable_config.masterDetailRender == 'popup' && wpdatatable_config.masterDetail){
                    jQuery('.wdt-md-render-post-block').hide();
                    jQuery('.wdt-md-render-page-block').hide();
                    jQuery('.wdt-md-popup-title-block').animateFadeIn();
                }
            },
            setMasterDetailRenderPage: function (masterDetailRenderPage) {
                wpdatatable_config.masterDetailRenderPage = masterDetailRenderPage;
                $('#wdt-md-render-page').selectpicker('val', masterDetailRenderPage);
            },
            setMasterDetailRenderPost: function (masterDetailRenderPost) {
                wpdatatable_config.masterDetailRenderPost = masterDetailRenderPost;
                $('#wdt-md-render-post').selectpicker('val', masterDetailRenderPost);
            },
            setMasterDetailPopupTitle: function (masterDetailPopupTitle) {
                wpdatatable_config.masterDetailPopupTitle = masterDetailPopupTitle;
                jQuery( '#wdt-md-popup-title' ).val( masterDetailPopupTitle );
            },

        });


        /**
         * Load the table for editing
         */
        if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.advanced_settings !== '') {

            var advancedSettings = JSON.parse(wpdatatable_init_config.advanced_settings);

            if (advancedSettings !== null) {

                var masterDetail = advancedSettings.masterDetail;
                var masterDetailLogic = advancedSettings.masterDetailLogic;
                var masterDetailRender = advancedSettings.masterDetailRender;
                var masterDetailRenderPage = advancedSettings.masterDetailRenderPage;
                var masterDetailRenderPost = advancedSettings.masterDetailRenderPost;
                var masterDetailPopupTitle = advancedSettings.masterDetailPopupTitle;

                if (typeof masterDetail !== 'undefined') {
                    wpdatatable_config.setMasterDetail(masterDetail);
                }

                if (typeof masterDetailLogic !== 'undefined') {
                    wpdatatable_config.setMasterDetailLogic(masterDetailLogic);
                }

                if (typeof masterDetailRender !== 'undefined') {
                    wpdatatable_config.setMasterDetailRender(masterDetailRender);
                }

                if (typeof masterDetailRenderPage !== 'undefined') {
                    wpdatatable_config.setMasterDetailRenderPage(masterDetailRenderPage);
                }

                if (typeof masterDetailRenderPost !== 'undefined') {
                    wpdatatable_config.setMasterDetailRenderPost(masterDetailRenderPost);
                }

                if (typeof masterDetailPopupTitle !== 'undefined') {
                    wpdatatable_config.setMasterDetailPopupTitle(masterDetailPopupTitle);
                }

            }

        }

        /**
         * Toggle "Master-detail" option
         */
        $('#wdt-md-toggle-master-detail').change(function () {
            wpdatatable_config.setMasterDetail($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Select "Master-detail" logic
         */
        $('#wdt-md-click-event-logic').change(function () {
            wpdatatable_config.setMasterDetailLogic($(this).val());
        });

        /**
         * Select "Master-detail" render option
         */
        $('#wdt-md-render-data-in').change(function () {
            wpdatatable_config.setMasterDetailRender($(this).val());
        });

        /**
         * Select "Master-detail" render page
         */
        $('#wdt-md-render-page').change(function () {
            wpdatatable_config.setMasterDetailRenderPage($(this).val());
        });

        /**
         * Select "Master-detail" render post
         */
        $('#wdt-md-render-post').change(function () {
            wpdatatable_config.setMasterDetailRenderPost($(this).val());
        });

        /**
         * Set "Master-detail" Popup Title
         */
        $('#wdt-md-popup-title').change(function (e) {
            wpdatatable_config.setMasterDetailPopupTitle($(this).val());
        });

        /**
         * Show Master-detail settings tab
         */
        if (!jQuery('.master-detail-settings-tab').is(':visible')) {
            jQuery('.master-detail-settings-tab').animateFadeIn();
        }

    });

})(jQuery);

/**
 * Initialize new property in object
 */
function callbackExtendColumnObject(column,obj) {
    var newOptionName = 'masterDetailColumnOption';
    if (typeof obj.masterDetailColumnOption == 'undefined'){
        obj.setAdditionalParam(newOptionName, column.masterDetailColumnOption);
    } else {
        obj.setAdditionalParam(newOptionName, 1);
    }
}

/**
 * Extend column settings and return it in an object format
 */
function callbackExtendOptionInObjectFormat(allColumnSettings, obj) {
    if (wpdatatable_config.masterDetail == 1){
        allColumnSettings.masterDetailColumnOption = obj.masterDetailColumnOption;
        return allColumnSettings;
    }
}

/**
 * Extend a small block with new column option in the list
 */
function callbackExtendSmallBlock($columnBlock, column) {
    $columnBlock.find('i.wdt-toggle-show-details').click(function (e) {
        e.preventDefault();
        if (!column.masterDetailColumnOption) {
            column.masterDetailColumnOption = 1;
            jQuery(this)
              .removeClass('inactive')
        } else {
            column.masterDetailColumnOption = 0;
            jQuery(this)
              .addClass('inactive')
        }
    });

    if (!column.masterDetailColumnOption) {
        $columnBlock.find('i.wdt-toggle-show-details')
          .addClass('inactive')
    }
}

/**
 * Fill in the visible inputs with data
 */
function callbackFillAdditinalOptionWithData(obj) {
    jQuery('#wdt-md-column').prop('checked',obj.masterDetailColumnOption).change();
}

/**
 * Hide tabs and options from Master-detail column
 */
function callbackHideColumnOptions(obj) {
    if (obj.type == 'masterdetail') {
        jQuery('li.column-filtering-settings-tab').hide();
        jQuery('li.column-editing-settings-tab').hide();
        jQuery('li.column-sorting-settings-tab').hide();
        jQuery('li.column-conditional-formatting-settings-tab').hide();
        jQuery('#wdt-column-type option[value="masterdetail"]').prop('disabled', '');
        jQuery('#wdt-column-type').prop('disabled', 'disabled').hide();
        jQuery('#column-data-settings .row:first-child').hide();
        jQuery('div.wdt-possible-values-type-block').hide();
        jQuery('div.wdt-possible-values-options-block').hide();
        jQuery('div.wdt-formula-column-block').hide();
        jQuery('div.wdt-skip-thousands-separator-block').hide();
        jQuery('div.wdt-numeric-column-block').hide();
        jQuery('div.wdt-float-column-block').hide();
        jQuery('div.wdt-date-input-format-block').hide();
        jQuery('div.wdt-group-column-block').hide();
        jQuery('div.wdt-link-target-attribute-block').hide();
        if (jQuery('#wdt-link-button-attribute').is(':checked')) {
            jQuery('div.wdt-link-button-label-block').show();
            jQuery('div.wdt-link-button-class-block').show();
        }
        jQuery('div.wdt-link-button-attribute-block').show();
        jQuery('div.wdt-md-column-block').hide();
    } else {
        jQuery('li.column-conditional-formatting-settings-tab').show();
        jQuery('#wdt-column-type option[value="masterdetail"]').prop('disabled', 'disabled');
        jQuery('#wdt-column-type').prop('disabled', '');
        jQuery('#column-data-settings .row:first-child').show();
    }

}

/**
 * Apply changes from UI to the object for new column option
 */
function callbackApplyUIChangesForNewColumnOption(obj) {
    obj.masterDetailColumnOption = jQuery('#wdt-md-column').is(':checked') ? 1 : 0;
}