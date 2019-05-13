<?php
  /*
  Plugin name: External Content Csv importer
  Plugin URI: http://PLUGIN_URI.com/
  Description: Automatically import HTML content from external web pages using csv
  Author: Navigation North
  Author URI: https://www.navigationnorth.com
  Version: 1.0
  */

if(!defined('ABSPATH')){
    die;
}

class OeseExternalImporter  
{
   function __construct(){
      
   }


    public function register_hooks(){
        add_action('admin_enqueue_scripts',array($this,'enqueue'));
        add_action('admin_menu', array( $this, 'create_menu_option') , 30);
        add_action('wp_ajax_my_action', array( $this, 'my_ajax_action_function'));
   }

    public function create_menu_option(){
        add_menu_page('External Importer', 'WP External Importer', 'manage_options', 'theme-options', array( $this, 'csv_import_form'));
    }
 
    public function csv_import_form(){
            echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
            <h2>WP External Importer</h2>
              <form name="wp_importer" class="importer"  method="post" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="button" id="csv_upload" value="Upload Csv" name="submit">
              </form>
            </div>';
    }

    public function enqueue(){
      //plugins_url('/assets/myscript.js', dirname(__FILE__) );
        wp_enqueue_script( 'my_custom_script', plugins_url('/assets/myscript.js', dirname(__FILE__)));
    }

    public function removeEverythingBefore($in, $before) {
      $pos = strpos($in, $before);
      return $pos !== FALSE
          ? substr($in, $pos + strlen($before), strlen($in))
          : "";
    }

    public function removeEverythingAfter($in, $after){
        $pos = strpos($in, $after);
        return $pos !== FALSE
        ? substr($in, 0, strpos($in, $after))
        :"";
    }  


    public function getFilteredContentHtml($pageUrl,$startCode,$endCode){
      $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
      );  
      $htmlPageContent = file_get_contents($pageUrl, false, stream_context_create($arrContextOptions));

      $stringRight = $this->removeEverythingBefore($htmlPageContent,$startCode);
      $strLeft = $this->removeEverythingAfter($stringRight,$endCode);

      return $strLeft;
    }


    public function createNewPage($pageName,$pageContent,$templateName){
        $post      = get_page_by_title($pageName, 'OBJECT', 'page');
        $post_id   = $post->ID;

        if(!$post_id){
          $template = "page-templates/".$templateName."-template.php";
          $post_data = array(
              'post_title'    => wp_strip_all_tags($pageName),
              'post_content'  => $pageContent,
              'post_status'   => 'publish',
              'post_type'     => 'page',
              'post_author'   => '1',
              'page_template' => ''
          );
          $pageId = wp_insert_post( $post_data, $error_obj );

          update_post_meta( $pageId, '_wp_page_template', $template);
        }
        else{
          echo "page with the name exists";
        }  
    }


    public function my_ajax_action_function(){
      $csvImportFile = $_FILES['file']['tmp_name'];
      $csvAsArray = array_map('str_getcsv', file($csvImportFile));
      array_shift($csvAsArray);
      //print_r($csvAsArray);
      foreach ($csvAsArray as $key => $csvVal) {
          $pageUrl = $csvVal[0];
          $pageStartCode = $csvVal[1];  
          $pageEndCode = $csvVal[2];  
          $pageTitle = $csvVal[3];
          $pageTemplate = $csvVal[4];  
        
          $filteredHtml = $this->getFilteredContentHtml($pageUrl,$pageStartCode,$pageEndCode);
          
          if($filteredHtml){
            $this->createNewPage($pageTitle,$filteredHtml,$pageTemplate);
          }
      }
    }

}

if(class_exists('OeseExternalImporter')){
    $OeseExternalImporter = new OeseExternalImporter();
    $OeseExternalImporter->register_hooks();
} 

?>