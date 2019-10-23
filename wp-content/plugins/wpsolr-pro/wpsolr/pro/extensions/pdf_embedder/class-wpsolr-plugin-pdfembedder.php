<?php

namespace wpsolr\pro\extensions\pdf_embedder;

use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\pro\extensions\embed_any_document\WPSOLR_Plugin_EmbedAnyDocument;

/**
 * Class WPSOLR_Plugin_PdfEmbedder
 * @package wpsolr\pro\extensions\pdf_embedder
 *
 * Manage Pdf Embedder plugin
 * @link https://wordpress.org/plugins/pdf-embedder/
 */
class WPSOLR_Plugin_PdfEmbedder extends WPSOLR_Plugin_EmbedAnyDocument {


	const EMBEDDOC_SHORTCODE = 'pdf-embedder';

	protected function set_is_do_embed_documents() {
		$this->is_do_embed_documents = WPSOLR_Service_Container::getOption()->get_pdf_embedder_is_do_embed_documents();
	}


}