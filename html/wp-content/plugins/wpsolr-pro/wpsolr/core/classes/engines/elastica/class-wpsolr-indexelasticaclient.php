<?php

namespace wpsolr\core\classes\engines\elastica;

use Elastica\Query;
use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_IndexElasticaClient
 *
 * @property \Elastica\Client $search_engine_client
 */
class WPSOLR_IndexElasticaClient extends WPSOLR_AbstractIndexClient {
	use WPSOLR_ElasticaClient;

	const PIPELINE_INGEST_ATTACHMENT_ID = 'wpsolr_attachment';
	const PIPELINE_INGEST_ATTACHMENT_DEFINITION =
		<<<'TAG'
{
  "description" : "WPSOLR - Ingest attachment pipeline",
  "processors" : [
    {
      "attachment" : {
        "field" : "data"
      }
    }
  ]
}
TAG;


	/**
	 * @inheritDoc
	 */
	public function search_engine_client_execute( $search_engine_client, $query ) {
		// Nothing here.
	}


	/**
	 * @param array $documents
	 */
	protected function search_engine_client_prepare_documents_for_update( array $documents ) {

		$formatted_document = [];

		$type = $this->get_elastica_type();

		foreach ( $documents as $document ) {
			$upsert_document = new \Elastica\Document( $document['id'], $document, $type );
			$upsert_document->setDocAsUpsert( true );

			$formatted_document[] = $upsert_document;
		}

		return $formatted_document;
	}

	/**
	 * Use Tika to extract a file content.
	 *
	 * @param $file
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function search_engine_client_extract_document_content( $file ) {

		// Decoded value
		$decoded_attached_value = '';

		// Workaround to the lack of ingest api in Elastica: https://github.com/ruflin/Elastica/issues/1248#issuecomment-321464511
		$document = new \Elastica\Document( $this->WPSOLR_DOC_ID_ATTACHMENT, [], $this->get_elastica_type() );
		$document->addFile( 'data', $file );
		$bulk = new \Elastica\Bulk( $this->search_engine_client );
		$bulk->setType( $this->get_elastica_type() );
		$bulk->setRequestParam( 'pipeline', self::PIPELINE_INGEST_ATTACHMENT_ID );
		$bulk->addDocument( $document );
		try {
			$result = $bulk->send();

		} catch ( \Exception $e ) {

			if ( false !== strpos( $e->getMessage(), sprintf( 'pipeline with id [%s] does not exist', self::PIPELINE_INGEST_ATTACHMENT_ID ) ) ) {

				// Create our attachment pipeline as it does not exist yet.
				$this->search_engine_client->request( sprintf( '_ingest/pipeline/%s', self::PIPELINE_INGEST_ATTACHMENT_ID ),
					\Elastica\Request::PUT,
					self::PIPELINE_INGEST_ATTACHMENT_DEFINITION
				);

				// then retry
				$result = $bulk->send();

			} else {
				// Not a missing ingest pipeline error. Don't catch it here.
				throw $e;
			}

		}

		if ( ! $result->hasError() ) {
			$attached_document = $this->get_elastica_type()->getDocument( $this->WPSOLR_DOC_ID_ATTACHMENT, [ '_source' => 'attachment.content' ] );

			$decoded_attached_array = $attached_document->getData();
			if ( ! empty( $decoded_attached_array ) && ! empty( $decoded_attached_array['attachment'] ) && ! empty( $decoded_attached_array['attachment']['content'] ) ) {
				$decoded_attached_value = $decoded_attached_array['attachment']['content'];
			}

		} else {

			throw new \Exception( $result->getErrorMessage() );
		}

		// Get rid of the file: from ES 6.0, one cannot use anymore an attachment type to hide attached files.
		//$this->get_elastica_type()->deleteById( $this->WPSOLR_DOC_ID_ATTACHMENT );

		return sprintf( '<body>%s</body>', $decoded_attached_value );
	}

	/**
	 * @param array[] $documents
	 *
	 * @return int|mixed
	 */
	public function send_posts_or_attachments_to_solr_index( $documents ) {

		$formatted_docs = $this->search_engine_client_prepare_documents_for_update( $documents );

		/**
		 * We use addDocuments(), because it replaces completely a document.
		 * updateDocuments() would leave the deleted fields in the document.
		 */
		//$results = $this->get_elastica_type()->updateDocuments( $formatted_docs );
		$results = $this->get_elastica_type()->addDocuments( $formatted_docs );

		return $results->hasError();
	}

	/**
	 * @inheritdoc
	 */
	protected function search_engine_client_delete_all_documents( $post_types = null, $site_id = '' ) {

		if ( ( is_null( $post_types ) || empty( $post_types ) ) && ( empty( $site_id ) ) ) {

			$this->get_elastica_type()->deleteByQuery( new \Elastica\Query\MatchAll() );

		} else {

			$bool_filter = new \Elastica\Query\BoolQuery();

			if ( ! ( is_null( $post_types ) || empty( $post_types ) ) ) {

				$terms = new \Elastica\Query\Terms();
				$terms->setTerms( WpSolrSchema::_FIELD_NAME_TYPE, $post_types );
				$bool_filter->addMust( $terms );
			}

			if ( ! empty( $site_id ) ) {

				$terms = new \Elastica\Query\Terms();
				$terms->setTerms( WpSolrSchema::_FIELD_NAME_BLOG_NAME_STR, [ $site_id ] );
				$bool_filter->addMust( $terms );
			}

			$this->get_elastica_type()->deleteByQuery( $bool_filter );
		}

	}

	/**
	 * @inheritdoc
	 */
	protected function search_engine_client_get_count_document( $site_id = '' ) {

		$bool_filter = new \Elastica\Query\BoolQuery();

		// Filter out the attachment document
		$terms_no_attachments = new \Elastica\Query\Terms();
		$terms_no_attachments->setTerms( WpSolrSchema::_FIELD_NAME_INTERNAL_ID, [ $this->WPSOLR_DOC_ID_ATTACHMENT ] );
		$bool_filter->addMustNot( $terms_no_attachments );

		if ( ! empty( $site_id ) ) {

			$terms_site_id = new \Elastica\Query\Terms();
			$terms_site_id->setTerms( WpSolrSchema::_FIELD_NAME_BLOG_NAME_STR, [ $site_id ] );
			$bool_filter->addMust( $terms_site_id );
		}


		$nb_documents = $this->get_elastica_type()->count( new Query( $bool_filter ) );

		return $nb_documents;
	}

	/**
	 * Transform a string in a date.
	 *
	 * @param $date_str String date to convert from.
	 *
	 * @return string
	 */
	public function search_engine_client_format_date( $date_str ) {
		return \Elastica\Util::convertDate( $date_str );
	}

	/**
	 * Delete a document.
	 *
	 * @param string $document_id
	 *
	 */
	protected function search_engine_client_delete_document( $document_id ) {

		$term = new \Elastica\Query\Term();
		$term->setTerm( 'id', $document_id );

		$this->get_elastica_type()->deleteByQuery( $term );
	}

}
