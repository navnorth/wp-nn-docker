<!-- Panel Group -->
<div class="col-sm-6 m-b-30">
    <div class="wdt-activation-section">

        <div class="wpdt-plugins-desc">
            <img class="img-responsive" src="<?php echo WDT_ASSETS_PATH; ?>img/addons/master-detail-logo.png" alt="">
            <h4> <?php _e('Master-Detail', 'wpdatatables'); ?></h4>
        </div>

        <!-- Panel Body -->
        <div class="panel-body">

            <!-- TMS Store Purchase Code -->
            <div class="col-sm-10 wdt-purchase-code-master-detail p-l-0">

                <!-- TMS Store Purchase Code Heading-->
                <h4 class="c-title-color m-b-4 m-t-0">
                    <?php _e('TMS Store Purchase Code', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('If your brought the plugin directly on our website, enter TMS Store purchase code to enable auto updates.', 'wpdatatables'); ?>"></i>
                </h4>
                <!-- /TMS Store Purchase Code Heading -->

                <!-- TMS Store Purchase Code Form -->
                <div class="form-group">
                    <div class="row">

                        <!-- TMS Store Purchase Code Input -->
                        <div class="col-sm-11 p-r-0">
                            <div class="fg-line">
                                <input type="text" name="wdt-purchase-code-store-master-detail"
                                       id="wdt-purchase-code-store-master-detail"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Please enter your Master-Detail TMS Store Purchase Code', 'wpdatatables'); ?>"
                                       value=""
                                />
                            </div>
                        </div>
                        <!-- TMS Store Purchase Code Input -->

                        <!-- TMS Store Purchase Code Activate Button -->
                        <div class="col-sm-1">
                            <button class="btn btn-primary waves-effect wdt-store-activate-plugin" id="wdt-activate-plugin-master-detail">
                                <i class="wpdt-icon-check-circle-full"></i><?php _e('Activate ', 'wpdatatables'); ?>
                            </button>
                        </div>
                        <!-- /TMS Store Purchase Code Activate Button -->

                    </div>
                </div>
                <!-- /TMS Store Purchase Code Form -->

            </div>
            <!-- /TMS Store Purchase Code -->

        </div>
        <!-- /Panel Body -->
    </div>
</div>
<!-- /Panel Group -->

