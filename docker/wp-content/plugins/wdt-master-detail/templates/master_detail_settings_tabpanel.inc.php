<!-- Master-Detail settings -->
<div role="tabpanel" class="tab-pane" id="master-detail-settings">
    <!-- .row -->
    <div class="row">
        <!-- Master-detail checkbox-->
        <div class="col-sm-4 m-b-16 wdt-md-toggle-master-detail-block">
            <h4 class="c-title-color m-b-4">
                <?php _e('Master-detail', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enable this to turn the master-detail functionality on for this table.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-md-toggle-master-detail" type="checkbox" hidden="hidden">
                <label for="wdt-md-toggle-master-detail"
                       class="ts-label"><?php _e('Enable master-detail functionality', 'wpdatatables'); ?></label>
            </div>
        </div>
        <!-- /Master-Detail checkbox-->

        <!-- Master-Detail Click Event Logic-->
        <div class="col-sm-4 wdt-md-click-event-logic-block hidden">

            <h4 class="c-title-color m-b-4">
                <?php _e('Open details on:', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('If the “Row click” is selected, users will be able to access details for a row by clicking it. If the “Button click” is selected, a new column will be added to the table, where each row would get a button opening the details for it.', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="form-control selectpicker" id="wdt-md-click-event-logic">
                            <option value="row"><?php _e('Row click', 'wpdatatables'); ?></option>
                            <option value="button"><?php _e('Button click', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Click Event Logic-->

        <!-- Master-Detail Render data in-->
        <div class="col-sm-4 wdt-md-render-data-in-block hidden">

            <h4 class="c-title-color m-b-4">
                <?php _e('Show details in:', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('If the “popup” option is selected, the details for the selected row will appear in a popup dialog on the same page. If you choose on of the the “Post” or “Page” options, users will be redirected to the chosen post or page (picked in a separate setting), which will be used as a template to render the details. Please note that you need to create the template post or page and fill it in with placeholders first, so that you could select it here', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="form-control selectpicker" id="wdt-md-render-data-in">
                            <option value="popup"><?php _e('Popup', 'wpdatatables'); ?></option>
                            <option value="wdtNewPage"><?php _e('Page', 'wpdatatables'); ?></option>
                            <option value="wdtNewPost"><?php _e('Post', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Render data in-->
    </div>
    <!-- /.row -->

    <!-- .row -->
    <div class="row">
        <!-- Master-Detail Render page-->
        <div class="col-sm-4 wdt-md-render-page-block hidden">

            <h4 class="c-title-color m-b-4">
                <?php _e('Template page', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Choose which page will be used to showing the row details', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="form-control selectpicker" id="wdt-md-render-page">
                            <?php foreach (WDTMasterDetail\Plugin::getAllPages() as $page) { ?>
                                <option value="<?php echo get_permalink($page['ID']); ?>"><?php echo $page['post_title']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Render page -->
    </div>
    <!-- /.row -->

    <!-- .row -->
    <div class="row">
        <!-- Master-Detail Render post-->
        <div class="col-sm-4 wdt-md-render-post-block hidden">

            <h4 class="c-title-color m-b-4">
                <?php _e('Template post', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Choose which post will be used to showing the row details', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="form-control selectpicker" id="wdt-md-render-post">
                            <?php foreach (WDTMasterDetail\Plugin::getAllPosts() as $post) { ?>
                                <option value="<?php echo get_permalink($post['ID']); ?>"><?php echo $post['post_title']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Render post -->

        <!-- Master-Detail Popup Title -->
        <div class="col-sm-4 wdt-md-popup-title-block hidden">
            <h4 class="c-title-color m-b-4">
                <?php _e('Popup Title', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter a title for the popup with row details. If you leave the field blank, the default title is “Row details”', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="wdt-md-popup-title" id="wdt-md-popup-title"
                                   class="form-control input-sm" placeholder="Enter a title for Popup modal"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Master-Detail Popup Title -->
    </div>
    <!-- /.row -->
</div>
<!-- /Master-Detail settings -->