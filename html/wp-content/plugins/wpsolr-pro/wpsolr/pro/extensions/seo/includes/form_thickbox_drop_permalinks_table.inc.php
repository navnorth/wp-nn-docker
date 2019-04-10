<script>

    jQuery(document).ready(function () {

        jQuery(document).on("click", "#button_confirm_drop_permalinks_table", function (e) {

            // Remember this for ajax
            var current = this;

            // Show progress
            var button_clicked = jQuery(this);
            var button_form = button_clicked.parents('.wpsolr_form_license');
            var buttonText = button_clicked.val(); // Remember button text
            var error_message_element = jQuery(this).siblings(".error-message");
            error_message_element.css("display", "none");
            error_message_element.html("");


            if ('DELETE' !== jQuery('#input_confirm_drop_permalinks_table').val()) {
                error_message_element.css("display", "inline");
                error_message_element.html("Please enter 'DELETE' first.");
                return false;
            }

            button_clicked.val('Operation in progress ... Please wait.');
            button_clicked.prop('disabled', true);

            var data = {
                action: 'wpsolr_ajax_drop_permalinks_table',
                security: jQuery('#<?php echo esc_attr( WPSOLR_DASHBOARD_NONCE_SELECTOR )?>').val(),
            }

            // Pass parameters to Ajax
            jQuery.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: "post",
                data: data,
                success: function (data1) {

                    data1 = JSON.parse(data1);

                    // Error message
                    if ("OK" != data1.status.state) {

                        // End progress
                        button_clicked.val(buttonText);
                        button_clicked.prop('disabled', false);

                        error_message_element.css("display", "inline-block");
                        error_message_element.html(data1.status.message);

                    } else {

                        // End progress
                        jQuery(current).val(buttonText);
                        jQuery(current).prop('disabled', false);
                        jQuery('#input_confirm_drop_permalinks_table').val('');
                        tb_remove(); // Close the thickbox
                    }

                },
                error: function () {

                    // End progress
                    jQuery(current).val(buttonText);
                    jQuery(current).prop('disabled', false);

                    /*
                     // Post Ajax UI display
                     jQuery('.loading_res').css('display', 'none');
                     jQuery('.results-by-facets').css('display', 'block');
                     */

                },
                always: function () {
                    // Not called.
                }
            });

            return false;
        });
    });
</script>


<div id="form_delete_permalinks_table" style="display:none;" class="wdm-vertical-tabs-content">

    <form method="POST" class="wpsolr_form_license">

        <div class='wrapper wpsolr_license_popup'><h4 class='head_div'>Delete the permalinks table and it's content</h4>
            <div class="wdm_note">
                You are about to delete your search permalinks.<br/><br/>
                This will have consequences on your traffic, and on your SEO:
                <ol>
                    <li>
                        Your permalinks will be gone forever
                    </li>
                    <li>
                        Any access to your permalink pages will be denied
                    </li>
                    <li>
                        Bots will deindex all your permalink urls
                    </li>
                </ol>
            </div>

            <hr/>
            <div class="wdm_row">
                <div class='col_left'>
                    Are you sure you want to do that ?<br/>There will be no way to recover your data.
                </div>
                <div class='col_right'>

                    <input id="input_confirm_drop_permalinks_table"
                           type="input" value=""
                           placeholder="Confirm by entering 'DELETE' in capslock."
                           style="width: 100%"/>
                    <span class="error-message"></span>
                    <hr/>
                    <input id="button_confirm_drop_permalinks_table" type="button" class="button-primary"
                           value="Click to forever delete all your search permalinks"/>

                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>


    </form>

</div>
