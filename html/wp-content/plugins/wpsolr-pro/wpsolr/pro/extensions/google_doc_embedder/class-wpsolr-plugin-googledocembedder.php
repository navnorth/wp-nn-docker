<?php

namespace wpsolr\pro\extensions\google_doc_embedder;

use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\pro\extensions\embed_any_document\WPSOLR_Plugin_EmbedAnyDocument;

/**
 * Class WPSOLR_Plugin_GoogleDocEmbedder
 * @package wpsolr\pro\extensions\google_doc_embedder
 *
 * Manage Google Doc Embedder plugin
 * @link https://wordpress.org/plugins/google-document-embedder/
 */
class WPSOLR_Plugin_GoogleDocEmbedder extends WPSOLR_Plugin_EmbedAnyDocument {

	const EMBEDDOC_SHORTCODE = 'gview';
	const EMBEDDOC_SHORTCODE_ATTRIBUTE_URL = 'file';

	protected function set_is_do_embed_documents() {
		$this->is_do_embed_documents = WPSOLR_Service_Container::getOption()->get_google_doc_embedder_is_do_embed_documents();
	}


}