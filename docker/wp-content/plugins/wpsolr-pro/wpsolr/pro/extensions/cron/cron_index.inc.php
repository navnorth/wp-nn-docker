<?php

use wpsolr\core\classes\models\WPSOLR_Model_Type_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;

$batch_size = ! empty( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_BATCH_SIZE ] ) ? $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_BATCH_SIZE ] : 100;
?>

<li class="wpsolr-sorted">
    <div class="wdm_row" data-wpsolr-index-label="<?php echo $index['index_name']; ?>">
        <input type='checkbox'
               class="wpsolr-cron-index-selected wpsolr_collapser wpsolr-remove-if-empty"
               name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][indexes][<?php echo $index_uuid ?>][is_in_cron]'
               value='1'
			<?php
			checked( isset( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ]['is_in_cron'] ) );
			?>
        >
        <span><?php echo $index['index_name'] ?> </span>
        <div class="wdm_row wpsolr_collapsed wpsolr-remove-if-hidden">
            <div class="wdm_row">
                <div class='col_left'>
                    Number of documents sent to the index as a single commit<br>
                    You can change this number to control indexing's performance
                </div>
                <div class='col_right'>
                    <input type='text'
                           class="wpsolr-cron-index-batch-size"
                           name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][indexes][<?php echo $index_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_BATCH_SIZE; ?>]'
                           placeholder="Enter a Number"
                           value="<?php echo $batch_size; ?>">
                    <span class='res_err'></span><br>
                </div>
                <div class="clear"></div>
            </div>
            <div class="wdm_row">
                <div class='col_left'>
                    Delete first
                </div>
                <div class='col_right'>
                    <input type='checkbox'
                           class="wpsolr-cron-index-delete-first wpsolr_collapser"
                           name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][indexes][<?php echo $index_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_IS_DELETE_FIRST; ?>]'
                           value='y'
						<?php
						checked( isset( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_IS_DELETE_FIRST ] ) );
						?>
                    >

                    <span class="wpsolr_collapsed">
                                            Delete the selected post types prior to indexing.
                                        </span>
                    <span class='res_err'></span><br>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Index mode
                </div>
                <div class='col_right'>
					<?php
					$index_type = isset( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_INDEX_TYPE ] )
						? $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_INDEX_TYPE ]
						: '';
					?>
                    <select
                            class="wpsolr-cron-index-mode"
                            name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][indexes][<?php echo $index_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_INDEX_TYPE; ?>]'
                    >
                        <option value="" <?php echo selected( $index_type, '' ); ?> >
                            Do not index
                        </option>
                        <option value="<?php echo WPSOLR_Option::OPTION_CRON_INDEX_TYPE_FULL; ?>" <?php echo selected( $index_type, WPSOLR_Option::OPTION_CRON_INDEX_TYPE_FULL ); ?> >
                            Index all the data
                        </option>
                        <option value="<?php echo WPSOLR_Option::OPTION_CRON_INDEX_TYPE_INCREMENTAL; ?>" <?php echo selected( $index_type, WPSOLR_Option::OPTION_CRON_INDEX_TYPE_INCREMENTAL ); ?> >
                            Index incrementally
                        </option>

                    </select>


                    <span class='res_err'></span><br>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Post types to index
                    <div style="float: right">
                        <a href="javascript:void();" class="wpsolr_checker">All</a> |
                        <a href="javascript:void();" class="wpsolr_unchecker">None</a>
                    </div>
                </div>
                <div class='col_right'>
					<?php
					$index_post_types = isset( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_INDEX_POST_TYPES ] )
						? $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_INDEX_POST_TYPES ]
						: [];
					?>

					<?php
					/** @var WPSOLR_Model_Type_Abstract[] $models */
					if ( ! empty( $models ) ) { ?>
						<?php foreach ( $models as $model ) {
							$post_type  = $model->get_type();
							$post_label = $model->get_label();
							?>
                            <div style="float:left;width:33%;">
                                <input type='checkbox'
                                       data-wpsolr-index-post-type="<?php echo $post_type; ?>"
                                       class="wpsolr_index_post_types wpsolr_checked"
                                       name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][indexes][<?php echo $index_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_INDEX_POST_TYPES; ?>][<?php echo $post_type; ?>]'
                                       value='y'
									<?php
									checked( isset( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_INDEX_POST_TYPES ][ $post_type ] ) );
									?>
                                >
								<?php echo $post_label; ?>
                            </div>
						<?php } ?>
					<?php } else { ?>
                        <span>First <a href="/wp-admin/admin.php?page=solr_settings&tab=solr_option&subtab=index_opt">select some post types to index</a>. Then configure them here.</span>
					<?php } ?>

                    <span class='res_err'></span><br>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Logs
                </div>
                <div class='col_right'>
					<?php
					$log = isset( $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_LOG ] )
						? $options['indexing'][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_LOG ]
						: '';
					?>
                    <input type="button" class="button-secondary wpsolr_collapser" value="Show last execution logs"/>
                    <div class="wpsolr_collapsed" style="margin:10px;">
                        <textarea
                                name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][indexes][<?php echo $index_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_LOG; ?>]'
                                rows="20"><?php echo empty( $log ) ? 'No log available.' : $log; ?></textarea>
                    </div>
                </div>
                <div class="clear"></div>
            </div>

        </div>
    </div>
</li>
									