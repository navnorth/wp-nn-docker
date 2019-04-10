<?php
include plugin_dir_path( __FILE__ ) . 'includes/form_thickbox_drop_permalinks_table.inc.php';
?>

<script>
    jQuery(document).ready(function () {

        jQuery(document).on('click', '#btn_delete_permalinks_table', function (e) {
            var response = prompt("Please enter 'DELETE' in the box to validate your deletion", "");

            if ('DELETE' === response) {
                alert('OK');
            } else {
                alert('KO');
            }

        });

        // Uploading files
        var file_frame;

        jQuery('.wpsolr_attachment_button').on('click', function (event) {

            event.preventDefault();

            var me = jQuery(this);
            var image_preview = me.parent().find('.wpsolr_attachment_image_preview');
            var attachment_image_selected = me.parent().find('.wpsolr_attachment_image_selected');
            var attachment_image_selected_id = attachment_image_selected.val();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                // Set the image ID to what we want
                file_frame.uploader.uploader.param('post_id', attachment_image_selected_id);
                // Open frame
                file_frame.open();
                return;
            } else {
                // Set the wp.media post id so the uploader grabs the ID we want when initialised
                wp.media.model.settings.post.id = attachment_image_selected_id;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select an image',
                button: {
                    text: 'Use this image',
                },
                multiple: false	// Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            file_frame.on('select', function () {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();

                // Do something with attachment.id and/or attachment.url here
                image_preview.attr('src', attachment.url).css('width', 'auto');
                attachment_image_selected.val(attachment.id);
            });

            // Finally, open the modal
            file_frame.open();
        });


    });

</script>

<div class="wdm_row">
    <div class='col_left'>
        Remove the test mode
		<?php use wpsolr\core\classes\extensions\indexes\WPSOLR_Option_Indexes;
		use wpsolr\core\classes\services\WPSOLR_Service_Container;
		use wpsolr\core\classes\utilities\WPSOLR_Help;
		use wpsolr\core\classes\utilities\WPSOLR_Option;
		use wpsolr\pro\extensions\seo\WPSOLR_Option_Seo;

		echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_STEALTH_MODE ); ?>
    </div>
    <div class='col_right'>
        <input type='checkbox'
               name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE; ?>]'
               value='1'
			<?php checked( '1', isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE ] ) ? $extension_options[ WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE ] : '?' ); ?>>

        <p>
            Anyone, including bots, will see the optimized SEO metas and crawl the search permalinks. By default, in
            test mode, only logged in
            users
            can.
        </p>
    </div>
    <div class="clear"></div>
</div>

<div class="wdm_row">
    <div class='col_left'>
        Search page content
		<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_STEALTH_MODE ); ?>
    </div>
    <div class='col_right'>

        <h3>Robots</h3>
		<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_TAG_NOFOLLOW ); ?>
        <input class="wpsolr_collapser"
               type='checkbox'
               name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOFOLLOW; ?>]'
               value='1'
			<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOFOLLOW ] ) ); ?>>

        Tag all search page results content with "nofollow"
        <div class="wpsolr_collapsed">
            <p>
                This option will tell robots not to crawl your search pages
            </p>
        </div>
        <div style="margin-top: 10px">
			<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_TAG_NOINDEX ); ?>
            <input class="wpsolr_collapser"
                   type='checkbox'
                   name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOINDEX; ?>]'
                   value='1'
				<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOINDEX ] ) ); ?>>

            Tag all search page results content with "noindex"
            <div class="wpsolr_collapsed"
            ">
            <p>
                This option will tell robots not to index your search results
            </p>
        </div>

        <h3>Metas</h3>

		<?php
		wp_enqueue_media();
		?>
        <div class="wpsolr_attachment">
            <img class="wpsolr_attachment_image_preview"
                 src='<?php echo WPSOLR_Service_Container::getOption()->get_option_seo_open_graph_image_url( $extension_options_name ); ?>'
                 width='50' height='50'>
            <input type="button" class="button wpsolr_attachment_button"
                   value="Select an open graph image for all search pages"/>
            <input type='hidden'
                   class="wpsolr_attachment_image_selected"
                   name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_OPEN_GRAPH_IMAGE; ?>]'
                   value="<?php echo WPSOLR_Service_Container::getOption()->get_option_seo_open_graph_image_id( $extension_options_name ); ?>">
        </div>
        <br/>

        <input type='text'
               name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE; ?>]'
               placeholder="<?php echo WPSOLR_Option::OPTION_SEO_META_VAR_VALUE; ?> | myblog"
               value="<?php echo WPSOLR_Service_Container::getOption()->get_option_seo_template_meta_title( $extension_options_name ); ?>">

        <p>
            Set a title template for all your search pages. <?php echo WPSOLR_Option::OPTION_SEO_META_VAR_VALUE; ?>
            willl be replaced by the search page keywords
            and filters.
        </p>

        <textarea
                name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION; ?>]'
                rows="10"
                placeholder="Description of current search containing parameter <?php echo WPSOLR_Option::OPTION_SEO_META_VAR_VALUE; ?> anywhere."
                style="width: 100%"><?php echo WPSOLR_Service_Container::getOption()->get_option_seo_template_meta_description( $extension_options_name ); ?></textarea>

        <p>
            Set a description template for all your search
            pages. <?php echo WPSOLR_Option::OPTION_SEO_META_VAR_VALUE; ?>
            willl be replaced by the search page keywords and filters.
        </p>

    </div>
    <div class="clear"></div>
</div>

<div class="wdm_row">
    <div class='col_left'>
        Search page permalinks
    </div>
    <div class='col_right'>
        <div style="margin-bottom: 30px">
			<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_PERMALINKS_REDIRECT ); ?>
            <input type='text'
                   name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME; ?>]'
                   placeholder=""
                   value="<?php echo WPSOLR_Service_Container::getOption()->get_option_seo_common_permalinks_home( $extension_options_name ); ?>">
            <p>
                Enter a home for your permalinks search urls. No '/' at the beginning, nor at the end.<br/>
                Examples:
            <ol>
                <li>shop</li>
                <li>search</li>
                <li>search/seo</li>
            </ol>
            </p>
        </div>

        <h3>Bots</h3>
        <div style="margin-top: 10px">
			<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_TAG_NOFOLLOW ); ?>
            <input class="wpsolr_collapser"
                   type='checkbox'
                   name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_PERMALINKS_NOFOLLOW; ?>]'
                   value='1'
				<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_PERMALINKS_NOFOLLOW ] ) ); ?>>

            Tag all permalinks links with "nofollow"
            <div class="wpsolr_collapsed">
                <p>
                    This option will tell robots not to crawl your permalinks
                </p>
            </div>
            <div style="margin-top: 10px">
				<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_TAG_NOINDEX ); ?>
                <input class="wpsolr_collapser"
                       type='checkbox'
                       name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_PERMALINKS_NOINDEX; ?>]'
                       value='1'
					<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_PERMALINKS_NOINDEX ] ) ); ?>>

                Tag all permalinks links with "noindex"
                <div class="wpsolr_collapsed"
                ">
                <p>
                    This option will tell robots not to index your permalinks
                </p>
            </div>

            <h3>Redirection</h3>
            <div>
                <div style="margin-bottom: 50px">
                    <input class="wpsolr_collapser"
                           type='radio'
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE; ?>]'
                           value=''
						<?php checked( empty( $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE ] ) ); ?>>

                    Ignore permalinks

                    <div class="wpsolr_collapsed">
                        <p>Redirect users and bots accessing permalinks to the theme's 404 page</p>
                    </div
                </div>

                <div style="margin-top: 10px">
                    <input class="wpsolr_collapser"
                           type='radio'
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE; ?>]'
                           value='<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL; ?>'
						<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE ] ) && ( WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL === $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE ] ) ); ?>>

                    Use permalinks

                    <div class="wpsolr_collapsed">
                        <p>
                            Use permalink urls to show the theme's search template results
                        </p>

                        <div style="margin-left: 30px;">
							<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_PERMALINKS_REDIRECT ); ?>
                            <input class="wpsolr_collapser"
                                   type='checkbox'
                                   name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_IS_REDIRECT_FROM_SEARCH; ?>]'
                                   value='1'
								<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_IS_REDIRECT_FROM_SEARCH ] ) ); ?>>

                            Redirect search
                            <div class="wpsolr_collapsed">
                                <p>
                                    Redirect standard search urls (containing ?s=, or starting with /search/) to the
                                    permalinks home.<br/>
                                    Example 1: /?s=red will be redirected to /home_permalink/red<br/>
                                    Example 2: /search/red will be redirected to /home_permalink/red<br/>
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <div style="margin-top: 10px">
                    <input class="wpsolr_collapser"
                           type='radio'
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE; ?>]'
                           value='<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_REDIRECT_TO_SEARCH; ?>'
						<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE ] ) && ( WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_REDIRECT_TO_SEARCH === $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE ] ) ); ?>>

                    Revert permalinks

                    <div class="wpsolr_collapsed">
                        <p>
                            Users and bots accessing permalinks are redirected to the theme search page, with a HTTP
                            302
                            code.<br/><br/>
                            If you do not want to use permalinks anymore, but want to keep your users and bots
                            accessing
                            the
                            right content.<br/><br/>
                            This option should be used with all permalinks generation options deactivated, to
                            prevent
                            generating other permalinks.
                        </p>
                    </div>
                </div>

                <div class="clear"></div>
            </div>

            <h3>Generation</h3>
            <div style="margin-bottom: 10px">
				<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_FACET_PERMALINKS ); ?>
                <input class="wpsolr_collapser"
                       type='checkbox'
                       name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_GENERATE_FACETS_PERMALINKS; ?>]'
                       value='1'
					<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_GENERATE_FACETS_PERMALINKS ] ) ); ?>>

                Generate search facets permalinks
                <div class="wpsolr_collapsed">
                    <p>
                        This option will generate beautiful SEO urls for your
                        facets. <?php echo wpsolr_get_menu_html( WPSOLR_ADMIN_MENU_FACETS, 'Configure each facet individually', true ); ?>
                        to
                        define
                        how the permalinks will be generated.
                    </p>

                    <div style="margin-left: 30px;">
						<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_FACET_PERMALINKS ); ?>
                        <input class="wpsolr_collapser"
                               type='checkbox'
                               name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_REDIRECT_FACETS_PERMALINKS_HOME; ?>]'
                               value='1'
							<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_REDIRECT_FACETS_PERMALINKS_HOME ] ) ); ?>>

                        Redirect facets to permalinks home
                        <div class="wpsolr_collapsed">
                            <p>Clicking on a facet will display results on the permalinks homepage. By default,
                                results
                                would be displayed on the same page.</p>
                        </div>
                    </div>

                </div>
            </div>

            <div style="margin-bottom: 10px">
				<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_SEARCH_KEYWORDS_PERMALINKS ); ?>
                <input class="wpsolr_collapser"
                       type='checkbox'
                       name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_GENERATE_KEYWORDS_PERMALINKS; ?>]'
                       value='1'
                       disabled
					<?php checked( '1', isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_GENERATE_KEYWORDS_PERMALINKS ] ) ? $extension_options[ WPSOLR_Option::OPTION_SEO_IS_GENERATE_KEYWORDS_PERMALINKS ] : '?' ); ?>>

                Generate search keywords permalinks
                <div class="wpsolr_collapsed">
                    <p>
                        This option will generate beautiful SEO urls for your keywords.
                    </p>
                </div>
            </div>
            <div class="clear"></div>

            <div style="margin-bottom: 30px">
				<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEO_SORT_PERMALINKS ); ?>
                <input class="wpsolr_collapser"
                       type='checkbox'
                       name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_IS_GENERATE_SORTS_PERMALINKS; ?>]'
                       value='1'
                       disabled
					<?php checked( '1', isset( $extension_options[ WPSOLR_Option::OPTION_SEO_IS_GENERATE_SORTS_PERMALINKS ] ) ? $extension_options[ WPSOLR_Option::OPTION_SEO_IS_GENERATE_SORTS_PERMALINKS ] : '?' ); ?>>

                Generate search sort permalinks
                <div class="wpsolr_collapsed">
                    <p>
                        This option will generate beautiful SEO urls for your sort.
                    </p>
                </div>
            </div>
            <div class="clear"></div>

            <h3>Storage</h3>
            <div style="margin-bottom: 10px">
                <input type='radio'
                       class="wpsolr_collapser"
                       name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE; ?>]'
                       value='<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE_IS_DATABASE; ?>'
					<?php checked( ! isset( $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE ] ) || ( WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE_IS_DATABASE === $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE ] ) ); ?>>

                Store permalinks in a custom database table named
                '<?php echo WPSOLR_Option_Seo::CONST_TABLE_NAME_SEO_PERMALINKS; ?>'

                <div class="wpsolr_collapsed" style="margin:10px;float:right">
					<?php
					global $wpsolr_extensions;
					$nb_stored_permalinks = apply_filters( WPSOLR_Option_Seo::WPSOLR_FILTER_SEO_GET_NB_STORED_PERMALINKS, 0 );
					echo sprintf(
						'<a href="#TB_inline?width=800&height=500&inlineId=%s" class="thickbox wpsolr_premium_class" ><img src="%s" class="wpsolr_premium_text_class" style="display:inline"><span>%s</span></a>',
						'form_delete_permalinks_table',
						'',
						sprintf( "Delete the table with it's %s permalinks", $nb_stored_permalinks )
					);
					?>
                </div>
            </div>
            <div class="clear"></div>

            <div style="margin-bottom: 30px">
                <input type='radio'
                       class="wpsolr_collapser"
                       name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE; ?>]'
                       value='<?php echo WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE_IS_INDEX; ?>'
                       disabled
					<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE ] ) && ( WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE_IS_INDEX === $extension_options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_STORAGE ] ) ); ?>>

                Store permalinks in an index<br/>

                <div class="wpsolr_collapsed" style="margin:10px;float:left">
                    <select name='wdm_solr_res_data[default_solr_index_for_search]'>
						<?php
						// Empty option
						echo sprintf( "<option value='%s' %s>%s</option>",
							'',
							'',
							'Select an index to store your permalinks. It must be not be shared !'
						);

						$option_indexes = new WPSOLR_Option_Indexes();
						$solr_indexes   = $option_indexes->get_indexes();
						foreach (
							$solr_indexes as $solr_index_indice => $solr_index
						) {

							echo sprintf( "
											<option value='%s' %s>%s</option>
											",
								$solr_index_indice,
								selected( $solr_index_indice, isset( $solr_res_options['default_solr_index_for_search'] ) ?
									$solr_res_options['default_solr_index_for_search'] : '' ),
								isset( $solr_index['index_name'] ) ? $solr_index['index_name'] : 'Unnamed
											Solr index' );

						}
						?>
                    </select>
                </div>
                <div class="wpsolr_collapsed" style="margin:10px;float:right">
                    <input disabled type="button" class="button-primary" value="Copy the table in the index"/>
                    <input disabled type="button" class="button-primary" value="Delete the index content"/>
                </div>
            </div>

        </div>
        <div class="clear"></div>
    </div>

