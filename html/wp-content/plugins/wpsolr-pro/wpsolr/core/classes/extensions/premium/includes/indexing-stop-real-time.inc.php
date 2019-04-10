<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;

?>

<div class="wdm_row">
    <div class='col_left'>
        Stop real-time indexing
    </div>
    <div class='col_right'>
        <input type='checkbox' name='wdm_solr_form_data[is_real_time]' class="wpsolr_collapser"
               value='1'
			<?php checked( '1', isset( $solr_options['is_real_time'] ) ? $solr_options['is_real_time'] : '' ); ?>
			<?php echo $license_manager->get_license_enable_html_code( OptionLicenses::LICENSE_PACKAGE_PREMIUM ); ?>
        >
        <span class="wpsolr_collapsed">The search engine index will no more be updated as soon as a post/comment/attachment
        is
        added/saved/deleted, but only when you launch the indexing bach. Useful to load a large number of posts, for instance coupons/products from affiliate datafeeds.</span>

    </div>
    <div class="clear"></div>
</div>