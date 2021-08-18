(function ($) {
  $(function () {

    // Handle Activation Settings
    handleActivationSettings();


    // Add event on "Activate"/"Deactivate" button
    $('#wdt-activate-plugin-master-detail').on('click', function () {
      if (typeof wdt_current_config.wdtActivatedMasterDetail === 'undefined' || wdt_current_config.wdtActivatedMasterDetail == 0 || wdt_current_config.wdtActivatedMasterDetail == '') {
        activatePlugin()
      } else {
        deactivatePlugin()
      }
    });

    // Activate plugin
    function activatePlugin() {
      $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-spinner9"></i>Loading...');

      let domain    = location.hostname;
      let subdomain = location.hostname;

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wpdatatables_activate_plugin',
          purchaseCodeStore: $('#wdt-purchase-code-store-master-detail').val(),
          wdtNonce: $('#wdtNonce').val(),
          slug: 'wdt-master-detail',
          domain: domain,
          subdomain: subdomain
        },
        success: function (response) {
          let valid = JSON.parse(response).valid;
          let domainRegistered = JSON.parse(response).domainRegistered;

          if (valid === true && domainRegistered === true) {
            wdt_current_config.wdtActivatedMasterDetail = 1;
            wdt_current_config.wdtPurchaseCodeStoreMasterDetail = $('#wdt-purchase-code-store-master-detail').val();
            wdtNotify('Success!', 'Plugin has been activated', 'success');
            $('#wdt-purchase-code-store-master-detail').prop('disabled', 'disabled');
            $('#wdt-activate-plugin-master-detail').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
          } else if (valid === false) {
            wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.purchaseCodeInvalid, 'danger');
            $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
          } else {
            wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_domains_limit, 'danger');
            jQuery('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
          }
        },
        error: function () {
          wdt_current_config.wdtActivatedMasterDetail = 0;
          wdtNotify('Error!', 'Unable to activate the plugin. Please try again.', 'danger');
          $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
        }
      });
    }

    // Deactivate plugin
    function deactivatePlugin() {
      $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-spinner9"></i>Loading...');

      let domain    = location.hostname;
      let subdomain = location.hostname;
      let params = {
        action: 'wpdatatables_deactivate_plugin',
        wdtNonce: $('#wdtNonce').val(),
        domain: domain,
        subdomain: subdomain,
        slug: 'wdt-master-detail',
      };

      if (wdt_current_config.wdtPurchaseCodeStoreMasterDetail) {
        params.type = 'code';
        params.purchaseCodeStore = wdt_current_config.wdtPurchaseCodeStoreMasterDetail;
      }

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: params,
        success: function (response) {
          var parsedResponse = JSON.parse(response);
          if (parsedResponse.deactivated === true) {
            wdt_current_config.wdtPurchaseCodeStoreMasterDetail = '';
            wdt_current_config.wdtActivatedMasterDetail = 0;
            $('#wdt-purchase-code-store-master-detail').prop('disabled', '').val('');
            $('#wdt-activate-plugin-master-detail').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
            $('.wdt-preload-layer').animateFadeOut();
            $('.wdt-purchase-code-master-detail').show();
          } else {
            wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.unable_to_deactivate_plugin, 'danger');
            $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
          }
        }
      });
    }


    function handleActivationSettings() {
      if (wdt_current_config.wdtActivatedMasterDetail == 1) {

        // Fill the purchase code input on settings load
        $('#wdt-purchase-code-store-master-detail').val(wdt_current_config.wdtPurchaseCodeStoreMasterDetail);

        // Change the "Activate"/"Deactivate" button if plugin is activated/deactivated
        $('#wdt-purchase-code-store-master-detail').prop('disabled', 'disabled');
        $('#wdt-activate-plugin-master-detail').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');

      } else {
        $('#wdt-purchase-code-store-master-detail').prop('disabled', '');
        $('#wdt-activate-plugin-master-detail').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
      }
    }
  });
})(jQuery);
