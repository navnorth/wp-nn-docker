<?php


namespace WDTMasterDetail;

/**
 * @package Master-Detail Tables for wpDataTables
 * @version 1.3.1
 */
/*
Plugin Name: Master-Detail Tables for wpDataTables
Plugin URI: https://wpdatatables.com/documentation/addons/master-detail-tables/
Description: A wpDataTables addon which allows showing additional details for a specific row in a popup or a separate page or post. Handy when you would like to keep fewer columns in the table, while allowing user to access full details of particular entries.
Version: 1.3.1
Author: TMS-Plugins
Author URI: http://tms-plugins.com
Text Domain: wpdatatables
*/

use Exception;
use MasterDetailWDTColumn;
use WDTConfigController;
use WDTColumn;
use WDTTools;
use WP_Error;
use WPDataTable;

defined('ABSPATH') or die('Access denied');
// Full path to the WDT Master-detail root directory
define('WDT_MD_ROOT_PATH', plugin_dir_path(__FILE__));
// URL of WDT Master-detail plugin
define('WDT_MD_ROOT_URL', plugin_dir_url(__FILE__));
// Current version of WDT Master-detail plugin
define('WDT_MD_VERSION', '1.3.1');
// Required wpDataTables version
define('WDT_MD_VERSION_TO_CHECK', '3.3');
// Path to Master-detail templates
define('WDT_MD_TEMPLATE_PATH', WDT_MD_ROOT_PATH . 'templates/');

// Init Master-detail for wpDataTables add-on
add_action('plugins_loaded', array('WDTMasterDetail\Plugin', 'init'), 10);

/**
 * Class Plugin
 * Main entry point of the wpDataTables Master-detail add-on
 * @package WDTMasterDetail
 */
class Plugin
{

    public static $initialized = false;

    /**
     * Instantiates the class
     * @return bool
     */
    public static function init()
    {
        // Check if wpDataTables is installed
        if (!defined('WDT_ROOT_PATH')) {
            add_action('admin_notices', array('WDTMasterDetail\Plugin', 'wdtNotInstalled'));
            return false;
        }
        // Add JS and CSS for editable tables on backend
        add_action('wdt_enqueue_on_edit_page', array('WDTMasterDetail\Plugin', 'wdtMasterDetailEnqueueBackend'));

        // Add JS and CSS for editable tables on frontend
        add_action('wdt_enqueue_on_frontend', array('wdtMasterDetail\Plugin', 'wdtMasterDetailEnqueueFrontend'));

        // Add "Master-Detail" tab on table configuration page
        add_action('wdt_add_table_configuration_tab', array('WDTMasterDetail\Plugin', 'addMasterDetailSettingsTab'));

        // Add tab panel for "Master-detail" tab on table configuration page
        add_action('wdt_add_table_configuration_tabpanel', array('WDTMasterDetail\Plugin', 'addMasterDetailSettingsTabPanel'));

        // Add element in Display column settings for "Master-detail" table
        add_action('wdt_add_column_display_settings_element', array('WDTMasterDetail\Plugin', 'addMasterDetailColumnSettingsElement'));

        // Add new column type option
        add_action('wpdatatables_add_custom_column_type_option', array('WDTMasterDetail\Plugin', 'addMasterDetailColumnTypeOption'));

        // Extend table config before saving table to DB
        add_filter('wpdatatables_filter_insert_table_array', array('wdtMasterDetail\Plugin', 'extendTableConfig'), 10, 1);

        // Extend WPDataTable Object with new properties
        add_action('wdt_extend_wpdatatable_object', array('wdtMasterDetail\Plugin', 'extendTableObject'), 10, 2);

        // Extend table description before returning it to the front-end
        add_filter('wpdatatables_filter_table_description', array('wdtMasterDetail\Plugin', 'extendJSONDescription'), 10, 3);

        // Add custom modal in DOM
        add_action('wpdatatables_add_custom_modal', array('wdtMasterDetail\Plugin', 'insertModal'), 10, 1);

        // Add custom modal in DOM
        add_action('wpdatatables_add_custom_template_modal', array('wdtMasterDetail\Plugin', 'insertTemplateModal'), 10, 1);

        // Prepare column data
        add_filter('wpdatatables_prepare_column_data', array('wdtMasterDetail\Plugin', 'prepareColumnData'), 10, 2);

        // Custom populate cells
        add_action('wpdatatables_custom_populate_cells', array('wdtMasterDetail\Plugin', 'fillCellsMasterDetail'), 10, 2);

        // Custom prepare output data
        add_filter('wpdatatables_custom_prepare_output_data', array('wdtMasterDetail\Plugin', 'prepareOutputDataMasterDetails'), 10, 5);

        // Disable wpdatatables features fro new column(sorting,searching and filtering)
        add_action('wpdatatables_columns_from_arr', array('wdtMasterDetail\Plugin', 'setColumnDetails'), 10, 4);

        // Include file that contaions MasterDetailWDTColumn class from MD in wpdt
        add_filter('wpdatatables_column_formatter_file_name', array('wdtMasterDetail\Plugin', 'columnFormatterFileName'), 10, 2);

        // Filtering column types array
        add_filter('wpdatatables_columns_types_array', array('wdtMasterDetail\Plugin', 'columnsTypesArray'), 10, 3);

        // Add and save custom column
        add_action('wpdatatables_add_and_save_custom_column', array('wdtMasterDetail\Plugin', 'saveColumns'), 10, 4);

        // Removing columns that that are not in source
        add_filter('wpdatatables_columns_not_in_source', array('wdtMasterDetail\Plugin', 'removeColumnsNotInSource'), 10, 4);

        // Filter the content with detail placehodlers
        add_filter( 'the_content', array('wdtMasterDetail\Plugin', 'filterTheContent'), has_filter('tcb_remove_deprecated_strings') ? 999 : 10);

        // Filter the content with detail placeholders for Thrive Architect
        add_filter( 'tcb_remove_deprecated_strings', array('wdtMasterDetail\Plugin', 'filterTheContent'));

        // Filter column JSON definition
        add_filter('wpdatatables_extend_column_js_definition', array('wdtMasterDetail\Plugin', 'extendColumnJSONDefinition'), 10, 2);

        // Filter data column properties
        add_filter('wpdt_filter_data_column_properties', array('wdtMasterDetail\Plugin', 'extendDataColumnProperties'), 10, 3);

        // Filter column params
        add_filter('wpdt_filter_column_params', array('wdtMasterDetail\Plugin', 'extendColumnParams'), 10, 2);

        // Filter column options
        add_filter('wpdt_filter_column_options', array('wdtMasterDetail\Plugin', 'extendColumnOptions'), 10, 2);

        // Filter supplementary array column object
        add_filter('wpdt_filter_supplementary_array_column_object', array('wdtMasterDetail\Plugin', 'extendSupplementaryArrayColumnObject'), 10, 3 );

        // Extend column config object
        add_filter('wpdt_filter_column_config_object', array('wdtMasterDetail\Plugin', 'extendColumnConfigObject'), 10, 2 );

        // Extend column description object
        add_filter('wpdt_filter_column_description_object', array('wdtMasterDetail\Plugin', 'extendColumnDescriptionObject'), 10, 3 );

        // Extend datacolumn object
        add_filter('wpdatatables_extend_datacolumn_object', array('wdtMasterDetail\Plugin', 'extendDataColumnObject'), 10, 2);

        // Extend small column block
        add_action('wpdt_add_small_column_block', array('WDTMasterDetail\Plugin', 'addMasterDetailSmallBlack'));

        // Add Master-Detail activation setting
        add_action('wdt_add_activation', array('WDTMasterDetail\Plugin', 'addMasterDetailActivation'));

        // Enqueue Master-Detail add-on files on back-end settings page
        add_action('wdt_enqueue_on_settings_page', array('WDTMasterDetail\Plugin', 'wdtMasterDetailEnqueueBackendSettings'));

        // Check auto update
        add_filter('pre_set_site_transient_update_plugins', array('WDTMasterDetail\Plugin', 'wdtCheckUpdateMasterDetail'));

        // Check plugin info
        add_filter('plugins_api', array('WDTMasterDetail\Plugin', 'wdtCheckInfoMasterDetail'), 10, 3);

        // Add a message for unavailable auto update if plugin is not activated
        add_action('in_plugin_update_message-' . plugin_basename(__FILE__), array('WDTMasterDetail\Plugin', 'addMessageOnPluginsPageMasterDetail'));

        // Add error message on plugin update if plugin is not activated
        add_filter('upgrader_pre_download', array('WDTMasterDetail\Plugin', 'addMessageOnUpdateMasterDetail'), 10, 4);

        // Filter Columns CSS
        add_filter('wpdt_filter_columns_css', array('WDTMasterDetail\Plugin', 'wpdtFilterColumnsCss'), 10, 4);

        // Check if wpDataTables required version is installed
        if (version_compare(WDT_CURRENT_VERSION, WDT_MD_VERSION_TO_CHECK) < 0) {
            // Show message if required wpDataTables version is not installed
            add_action('admin_notices', array('WDTMasterDetail\Plugin', 'wdtRequiredVersionMissing'));
            return false;
        }

        return self::$initialized = true;
    }

    /**
     *  Filter Columns CSS
     * @param $columnsCSS
     * @param $columnObj
     * @param $tableID
     * @param $cssColumnHeader
     * @return string
     */
    public static function wpdtFilterColumnsCss( $columnsCSS, $columnObj, $tableID, $cssColumnHeader )
    {
        if ($columnObj->text_before != '') {
            $columnsCSS .= "\n#wdt-md-modal div.{$cssColumnHeader}:not(:empty):before{ content: '{$columnObj->text_before}' }";
        }
        if ($columnObj->text_after != '') {
            $columnsCSS .= "\n#wdt-md-modal div.{$cssColumnHeader}:not(:empty):after { content: '{$columnObj->text_after}' }";
        }
        if ($columnObj->color != '') {
            $columnsCSS .= "#wdt-md-modal div.{$cssColumnHeader}{ background-color: {$columnObj->color} !important; }";
        }

        return $columnsCSS;
    }
    /**
     *  Extend small column block
     * @param $tableData
     */
    public static function addMasterDetailSmallBlack( $tableData )
    {
        $advancedSettingsTable = json_decode($tableData->table->advanced_settings);
        if (isset($advancedSettingsTable->masterDetail) && $advancedSettingsTable->masterDetail != 0) {
            ob_start();
            include WDT_MD_ROOT_PATH . 'templates/master_detail_small_block.inc.php';
            $masterDetailSmallBlock = ob_get_contents();
            ob_end_clean();

            echo $masterDetailSmallBlock;
        }
    }


    /**
     *  Extend datacolumn object
     * @param $dataColumn
     * @param $dataColumnProperties
     * @return mixed
     */
    public static function extendDataColumnObject($dataColumn,$dataColumnProperties){
        if (isset($dataColumnProperties['masterDetailColumnOption'])){
            $dataColumn->masterDetailColumnOption = $dataColumnProperties['masterDetailColumnOption'];
        }else {
            $dataColumn->masterDetailColumnOption = 1;
        }
        return $dataColumn;
    }

    /**
     *  Extend column description object
     * @param $feColumn
     * @param $dbColumn
     * @param $advancedSettings
     * @return mixed
     */
    public static function extendColumnDescriptionObject( $feColumn, $dbColumn, $advancedSettings)
    {
        if (isset($advancedSettings->masterDetailColumnOption)){
            $feColumn->masterDetailColumnOption = $advancedSettings->masterDetailColumnOption;
        } else {
            $feColumn->masterDetailColumnOption = 1;
        }
        return $feColumn;
    }

    /**
     *  Extend column config object
     * @param $columnConfig
     * @param $feColumn
     * @return mixed
     */
    public static function extendColumnConfigObject($columnConfig, $feColumn)
    {
        $columnAdvancedSettings = json_decode($columnConfig['advanced_settings']);

        if (isset($feColumn->masterDetailColumnOption)) {
            $columnAdvancedSettings->masterDetailColumnOption = $feColumn->masterDetailColumnOption;
        } else {
            $columnAdvancedSettings->masterDetailColumnOption = 1;
        }

        $columnConfig['advanced_settings'] = json_encode($columnAdvancedSettings);

        return $columnConfig;
    }

    /**
     *  Extend supplementary array column object
     * @param $colObjOptions
     * @param $wdtParameters
     * @param $dataColumn_key
     * @return mixed
     */
    public static function extendSupplementaryArrayColumnObject($colObjOptions, $wdtParameters, $dataColumn_key)
    {
        if (isset($wdtParameters['masterDetailColumnOption'])) {
            $colObjOptions['masterDetailColumnOption'] = $wdtParameters['masterDetailColumnOption'][$dataColumn_key];
        } else {
            $colObjOptions['masterDetailColumnOption'] = true;
        }

        return $colObjOptions;
    }

    /**
     *  Extend column options
     * @param $columnOptions
     * @param $columnData
     * @return mixed
     */
    public static function extendColumnOptions($columnOptions, $columnData)
    {
        foreach ($columnData as $column) {
            $advancedSettings = json_decode($column->advanced_settings);

            if (isset($advancedSettings->masterDetailColumnOption) && $advancedSettings->masterDetailColumnOption == 1 ) {
                $masterDetailColumnOption[$column->orig_header] = $advancedSettings->masterDetailColumnOption;
            } else {
                $masterDetailColumnOption[$column->orig_header] = null;
            }
            $columnOptions['masterDetailColumnOption'] = $masterDetailColumnOption;
        }

        return $columnOptions;
    }

    /**
     *  Extend column params
     * @param $params
     * @param $columnData
     * @return mixed
     */
    public static function extendColumnParams($params, $columnData)
    {
        if (isset($columnData['masterDetailColumnOption'])) {
            $params['masterDetailColumnOption'] = $columnData['masterDetailColumnOption'];
        } else {
            $params['masterDetailColumnOption'] = 1;
        }
        return $params;
    }

    /**
     *  Extend data column properties
     * @param $dataColumnProperties
     * @param $wdtParameters
     * @param $key
     * @return mixed
     */
    public static function extendDataColumnProperties($dataColumnProperties, $wdtParameters, $key)
    {
        if (isset($wdtParameters['masterDetailColumnOption']) && isset($wdtParameters['masterDetailColumnOption'][$key]) && is_array($wdtParameters['masterDetailColumnOption'])) {
            $dataColumnProperties['masterDetailColumnOption'] =  $wdtParameters['masterDetailColumnOption'][$key];
        } else {
            $dataColumnProperties['masterDetailColumnOption'] = 1;
        }
        return $dataColumnProperties;
    }

    /**
     *  Extend column JSON definition
     * @param $colJsDefinition
     * @param $title
     * @return mixed
     */
    public static function extendColumnJSONDefinition($colJsDefinition, $wpdatatable)
    {
        if (isset($wpdatatable->masterDetailColumnOption)) {
            $colJsDefinition->masterDetailColumnOption = $wpdatatable->masterDetailColumnOption;
        } else {
            $colJsDefinition->masterDetailColumnOption = 1;
        }
        return $colJsDefinition;
    }

    /**
     *  Removing columns that that are not in source
     * @param $columnsNotInSource
     * @param $table
     * @param $tableId
     * @param $frontendColumns
     * @return array
     */
    public static function removeColumnsNotInSource($columnsNotInSource, $table, $tableId, $frontendColumns)
    {
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in masterdetail columns in this loop
                if ($feColumn->type != 'masterdetail') {
                    continue;
                }
                // Removing this column from the array of marked for deletiong
                $columnsNotInSource = array_diff($columnsNotInSource, array($feColumn->orig_header));

            }
            return $columnsNotInSource;
        }
        return array();
    }

    /**
     * Add and save custom column
     * @param $table \WPDataTable
     * @param $tableId
     * @param $frontendColumns
     * @throws Exception
     */
    public static function saveColumns($table, $tableId, $frontendColumns)
    {
        global $wpdb;
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in masterdetail column in this loop
                if ($feColumn->type != 'masterdetail') {
                    continue;
                }
                $wdtColumn = WDTColumn::generateColumn(
                    'masterdetail',
                    array(
                        'orig_header' => $feColumn->orig_header,
                        'display_header' => $feColumn->display_header,
                        'decimalPlaces' => $feColumn->decimalPlaces
                    )
                );
                $existingPositionQuery = $wpdb->prepare(
                    "SELECT pos
                FROM " . $wpdb->prefix . "wpdatatables_columns
                WHERE table_id = %d",
                    $tableId
                );

                $columnsPositionInSource = $wpdb->get_col($existingPositionQuery);
                $columnsPositionInSourceCounts = array_count_values($columnsPositionInSource);

                $tempMasterDetailPosition = $feColumn->pos;
                $keyExist = array_search($tempMasterDetailPosition,array_keys($columnsPositionInSourceCounts));
                $checkDuplicatePosition= $keyExist ? $columnsPositionInSourceCounts[$tempMasterDetailPosition] : 0;

                /** @var MasterDetailWDTColumn $wdtColumn */
                $columnConfig = WDTConfigController::prepareDBColumnConfig($wdtColumn, $frontendColumns, $tableId);
                $columnConfig['filter_type'] = 'none';

                if ((in_array($tempMasterDetailPosition, $columnsPositionInSource) && $checkDuplicatePosition > 1)
                    || in_array($tempMasterDetailPosition, $columnsPositionInSource) && $tempMasterDetailPosition >= count($columnsPositionInSource) ) {
                    $dataSourceColumns = $table->getColumns();
                    $columnConfig['pos'] = count($dataSourceColumns);
                } else {
                    $columnConfig['pos'] = $tempMasterDetailPosition;
                }

                WDTConfigController::saveSingleColumn($columnConfig);
            }
        }
    }

    /**
     * Filtering column types array
     * @param $columnsTypesArray
     * @param $columnsNotInSource
     * @param $columnsTypes
     * @return array
     */
    public static function columnsTypesArray($columnsTypesArray, $columnsNotInSource, $columnsTypes)
    {
        $columnsTypesArray = array_diff(array_combine($columnsNotInSource, $columnsTypes), ['masterdetail', 'formula']);
        return $columnsTypesArray;
    }

    /**
     * Format file that contain column class
     * @param $columnFormatterFileName
     * @param $wdtColumnType
     * @return string
     */
    public static function columnFormatterFileName($columnFormatterFileName, $wdtColumnType)
    {
        if ($wdtColumnType == 'masterdetail') {
            $columnFormatterFileName = WDT_MD_ROOT_PATH . $columnFormatterFileName;
        }
        return $columnFormatterFileName;
    }

    /**
     * Disable sorting and searching for Master-detail column
     * @param $obj \WPDataTable
     * @param $dataColumn
     * @param $wdtColumnTypes
     * @param $key
     * @throws \WDTException
     */
    public static function setColumnDetails($obj, $dataColumn, $wdtColumnTypes, $key)
    {
        if (isset($wdtColumnTypes[$key])) {
            if ($wdtColumnTypes[$key] === 'masterdetail') {
                /** @var MasterDetailWDTColumn $dataColumn */
                if ($obj->serverSide()) {
                    $dataColumn->setSorting(false);
                    $dataColumn->setSearchable(false);
                    $dataColumn->setFilterType('none');
                }
            }
        }
    }


    /**
     * Filter columns_from_arr
     * @param $obj \WPDataTable
     * @param $wdtColumnTypes
     */
    public static function fillCellsMasterDetail($obj, $wdtColumnTypes)
    {
        if (in_array('masterdetail', $wdtColumnTypes)) {
            self::populateDetailsCells($obj);
        }
    }

    /**
     * Fill cell with predefined values
     * @param $obj \WPDataTable
     */
    public static function populateDetailsCells($obj)
    {
        foreach (array_keys($obj->getWdtColumnTypes(), 'masterdetail') as $column_key) {

            $allDataRows = $obj->getDataRows();
            foreach ($allDataRows as &$row) {
                try {
                    $row[$column_key] = 'More details';
                } catch (Exception $e) {
                    $row[$column_key] = '';
                }
            }
            $obj->setDataRows($allDataRows);

        }
    }

    /**
     * Insert Modal templates
     * @param $output
     * @param $obj \WPDataTable
     * @param $main_res_dataRows
     * @param $wdtParameters
     * @param $colObjs
     * @return array
     * @throws \WDTException
     */
    public static function prepareOutputDataMasterDetails($output, $obj, $main_res_dataRows, $wdtParameters, $colObjs)
    {
        $output = [];
        if (!empty($main_res_dataRows)) {
            foreach ($wdtParameters['foreignKeyRule'] as $columnKey => $foreignKeyRule) {
                if ($foreignKeyRule != null) {
                    $foreignKeyData = $obj->joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $main_res_dataRows);
                    $main_res_dataRows = $foreignKeyData['dataRows'];
                }
            }

            foreach ($main_res_dataRows as $res_row) {
                $row = array();
                foreach ($wdtParameters['columnOrder'] as $dataColumn_key) {
                    if ($wdtParameters['data_types'][$dataColumn_key] == 'masterdetail') {
                        try {
                            $detailsValue = 'More Details';
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($detailsValue),
                                $obj->getWpId(),
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = '';
                        }
                    } else if ($wdtParameters['data_types'][$dataColumn_key] == 'formula') {
                        try {
                            $headers = array();
                            $headersInFormula = $obj->detectHeadersInFormula($wdtParameters['columnFormulas'][$dataColumn_key], array_keys($wdtParameters['data_types']));
                            $headers = WDTTools::sanitizeHeaders($headersInFormula);
                            $formulaVal =
                                $obj::solveFormula(
                                    $wdtParameters['columnFormulas'][$dataColumn_key],
                                    $headers,
                                    $res_row
                                );
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($formulaVal),
                                $obj->getWpId(),
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = 0;
                        }
                    } else {

                        $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue($res_row[$dataColumn_key]), $obj->getWpId(), $dataColumn_key);
                    }
                }
                $output[] = self::formatAjaxQueryResultRow($row, $obj);
            }
        }
        return $output;
    }

    /**
     * Formatting row data structure for ajax display table
     * @param $row - key => value pairs as column name and cell value of a row
     * @param $obj WPDataTable/WPExcelDataTable object
     * @return array
     */
    public static function formatAjaxQueryResultRow($row, $obj)
    {
        if (is_a($obj, 'WPExcelDataTable')) {
            return $row;
        } else{
            return array_values($row);
        }
    }

    /**
     * Prepare column data
     * @param $returnArray
     * @param $column
     * @return mixed
     */
    public static function prepareColumnData($returnArray, $column)
    {
        if ($column->type === 'masterdetail') {
            $returnArray['columnTypes'][$column->orig_header] = $column->type;
        }

        if (isset($column->masterDetailColumnOption)){
            $returnArray['masterDetailColumnOption'][$column->orig_header] = isset($column->masterDetailColumnOption) ? $column->masterDetailColumnOption : null;
        }

        return $returnArray;
    }

    /**
     * Insert Modal templates
     * @param $wpDataTable \WPDataTable
     */
    public static function insertModal($wpDataTable)
    {
        if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail && is_admin()) {
            include WDT_MD_TEMPLATE_PATH . 'modal.inc.php';
            include WDT_MD_TEMPLATE_PATH . 'md_modal.inc.php';
        } else if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail){
            include WDT_MD_TEMPLATE_PATH . 'md_modal.inc.php';
        }
    }

    /**
     * Insert Template Modal
     */
    public static function insertTemplateModal()
    {
        include WDT_MD_TEMPLATE_PATH . 'modal.inc.php';
    }


    /**
     * Show message if wpDataTables is not installed
     */
    public static function wdtNotInstalled()
    {
        $message = __('Master-detail Tables for wpDataTables is an add-on - please install and activate wpDataTables to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Show message if required wpDataTables version is not installed
     */
    public static function wdtRequiredVersionMissing()
    {
        $message = __('Master-Detail Tables add-on for wpDataTables requires wpDataTables version ' . WDT_MD_VERSION_TO_CHECK . '. Please update wpDataTables plugin to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Enqueue Master-detail add-on files on back-end
     */
    public static function wdtMasterDetailEnqueueBackend()
    {
        if (self::$initialized) {
            wp_enqueue_style(
                'wdt-md-stylesheet',
                WDT_MD_ROOT_URL . 'assets/css/wdt.md.css',
                array(),
                WDT_MD_VERSION
            );
            wp_enqueue_script(
                'wdt-md-backend',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.backend.js',
                array(),
                WDT_MD_VERSION,
                true
            );
            wp_enqueue_script(
                'wdt-md-frontend',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.frontend.js',
                array(),
                WDT_MD_VERSION,
                true
            );

            \WDTTools::exportJSVar('wdtMdDashboard', is_admin());
            \WDTTools::exportJSVar('wdtMdTranslationStrings', \WDTTools::getTranslationStrings());
        }
    }

    /**
     * Enqueue Master-Detail add-on files on front-end
     */
    public static function wdtMasterDetailEnqueueFrontend()
    {
        if (self::$initialized) {
            wp_enqueue_script(
                'wdt-md-frontend',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.frontend.js',
                array(),
                WDT_MD_VERSION,
                true
            );

            wp_enqueue_style(
                'wdt-md-stylesheet',
                WDT_MD_ROOT_URL . 'assets/css/wdt.md.css',
                array(),
                WDT_MD_VERSION
            );

            \WDTTools::exportJSVar('wdtMdDashboard', is_admin());
            \WDTTools::exportJSVar('wdtMdTranslationStrings', \WDTTools::getTranslationStrings());
        }
    }

    /**
     * Function that extend table config before saving table to the database
     * @param $tableConfig - array that contains table configuration
     * @return mixed
     */
    public static function extendTableConfig($tableConfig)
    {
        $table = apply_filters(
            'wpdatatables_before_save_table',
            json_decode(
                stripslashes_deep($_POST['table'])
            )
        );

        $advancedSettings = json_decode($tableConfig['advanced_settings']);
        if (isset($table->masterDetail)) $advancedSettings->masterDetail = $table->masterDetail;
        if (isset($table->masterDetailLogic)) $advancedSettings->masterDetailLogic = $table->masterDetailLogic;
        if (isset($table->masterDetailRender)) $advancedSettings->masterDetailRender = $table->masterDetailRender;
        if (isset($table->masterDetailRenderPage)) $advancedSettings->masterDetailRenderPage = $table->masterDetailRenderPage;
        if (isset($table->masterDetailRenderPost)) $advancedSettings->masterDetailRenderPost = $table->masterDetailRenderPost;
        if (isset($table->masterDetailPopupTitle)) $advancedSettings->masterDetailPopupTitle = $table->masterDetailPopupTitle;

        $tableConfig['advanced_settings'] = json_encode($advancedSettings);

        return $tableConfig;
    }

    /**
     * Function that extend $wpDataTable object with new properties
     * @param $wpDataTable \WPDataTable
     * @param $tableData \stdClass
     */
    public static function extendTableObject($wpDataTable, $tableData)
    {
        if (!empty($tableData->advanced_settings)) {
            $advancedSettings = json_decode($tableData->advanced_settings);

            if (isset($advancedSettings->masterDetail)) {
                $wpDataTable->masterDetail = $advancedSettings->masterDetail;
            }

            if (isset($advancedSettings->masterDetailLogic)) {
                $wpDataTable->masterDetailLogic = $advancedSettings->masterDetailLogic;
            }

            if (isset($advancedSettings->masterDetailRender)) {
                $wpDataTable->masterDetailRender = $advancedSettings->masterDetailRender;
            }

            if (isset($advancedSettings->masterDetailRenderPage)) {
                $wpDataTable->masterDetailRenderPage = $advancedSettings->masterDetailRenderPage;
            }

            if (isset($advancedSettings->masterDetailRenderPost)) {
                $wpDataTable->masterDetailRenderPost = $advancedSettings->masterDetailRenderPost;
            }

            if (isset($advancedSettings->masterDetailPopupTitle)) {
                $wpDataTable->masterDetailPopupTitle = $advancedSettings->masterDetailPopupTitle;
            }

        }

    }

    /**
     * Function that extend table description before returning it to the front-end
     *
     * @param $tableDescription \stdClass
     * @param $wpDataTable \WPDataTable
     * @return mixed
     */
    public static function extendJSONDescription($tableDescription, $tableId, $wpDataTable)
    {

        if (isset($wpDataTable->masterDetail)) {
            $tableDescription->masterDetail = $wpDataTable->masterDetail;
        }

        if (isset($wpDataTable->masterDetailLogic)) {
            $tableDescription->masterDetailLogic = $wpDataTable->masterDetailLogic;
        }

        if (isset($wpDataTable->masterDetailRender)) {
            $tableDescription->masterDetailRender = $wpDataTable->masterDetailRender;
        }

        if (isset($wpDataTable->masterDetailRenderPage)) {
            $tableDescription->masterDetailRenderPage = $wpDataTable->masterDetailRenderPage;
        }

        if (isset($wpDataTable->masterDetailRenderPost)) {
            $tableDescription->masterDetailRenderPost = $wpDataTable->masterDetailRenderPost;
        }

        if (isset($wpDataTable->masterDetailPopupTitle)) {
            $tableDescription->masterDetailPopupTitle = $wpDataTable->masterDetailPopupTitle;
        }

        if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail &&
            isset($wpDataTable->masterDetailLogic) && $wpDataTable->isEditable() && $wpDataTable->serverSide()) {
            (!isset($tableDescription->dataTableParams->buttons)) ? $tableDescription->dataTableParams->buttons = array() : '';
            array_push(
                $tableDescription->dataTableParams->buttons,

                array(
                    'text' => __('Details', 'wpdatatables'),
                    'className' => 'master_detail DTTT_button DTTT_button_md'
                )
            );
        }

        return $tableDescription;
    }


    /**
     * Add Master-Detail Settings tab on table configuration page
     */
    public static function addMasterDetailSettingsTab()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master_detail_settings_tab.inc.php';
        $masterDetailSettingsTab = ob_get_contents();
        ob_end_clean();

        echo $masterDetailSettingsTab;
    }

    /**
     * Add tablpanel for Master-Detail Settings tab on table configuration page
     */
    public static function addMasterDetailSettingsTabPanel()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master_detail_settings_tabpanel.inc.php';
        $masterDetailSettingsTabPanel = ob_get_contents();
        ob_end_clean();

        echo $masterDetailSettingsTabPanel;
    }

    /**
     * Add element in column settings for Master-Detail table
     */
    public static function addMasterDetailColumnSettingsElement()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master-detail-column-display-element.inc.php';
        $masterDetailColumnSettingsElement = ob_get_contents();
        ob_end_clean();

        echo $masterDetailColumnSettingsElement;
    }

    /**
     * Add new option for column type
     */
    public static function addMasterDetailColumnTypeOption()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master-detail-column-type-option.inc.php';
        $masterDetailColumnTypeOption = ob_get_contents();
        ob_end_clean();

        echo $masterDetailColumnTypeOption;
    }

    /**
     * Get all pages from database
     */
    public static function getAllPages() {
        global $wpdb;

        $query = "SELECT post_title, guid, ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'page' ORDER BY {$wpdb->prefix}posts.ID ASC ";

        $allPages = $wpdb->get_results($query, ARRAY_A);
        return $allPages;
    }

    /**
     * Get all posts from database
     */
    public static function getAllPosts() {
        global $wpdb;

        $query = "SELECT post_title, guid, ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'post' ORDER BY {$wpdb->prefix}posts.ID ASC ";

        $allPosts = $wpdb->get_results($query, ARRAY_A);
        return $allPosts;
    }

    /**
     * Get masterDetailRenderPage and masterDetailRenderPost values from database
     */
    public static function removePlaceholdersFromContent($currentPostLink, $content) {
        global $wpdb;
        $finalPageIDs = [];

        $query = "SELECT id, advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE {$wpdb->prefix}wpdatatables.id > 0";

        $advancedSettingsFromAllTables= $wpdb->get_results($query, ARRAY_A);
        foreach ($advancedSettingsFromAllTables as $advancedSetting) {
            $tempID = $advancedSetting['id'];
            $tempAdvancedSettings = json_decode($advancedSetting['advanced_settings']);
            if (isset($tempAdvancedSettings->masterDetailRenderPage) && $tempAdvancedSettings->masterDetailRenderPage != '' ){
                if ($tempAdvancedSettings->masterDetailRenderPage == $currentPostLink)
                    $finalPageIDs[] = $tempID;
            }
            if (empty($finalPageID)){
                if (isset($tempAdvancedSettings->masterDetailRenderPost) && $tempAdvancedSettings->masterDetailRenderPost != '' ){
                    if ($tempAdvancedSettings->masterDetailRenderPost == $currentPostLink)
                        $finalPageIDs[] =$tempID;
                }
            }
        }
        if (empty($finalPageID)){
            foreach ($finalPageIDs as $finalPageID){
                $columnsData = WDTConfigController::loadColumnsFromDB($finalPageID);
                $origHeaders= [];
                foreach ($columnsData as $columnData){
                    $origHeaders[] = $columnData->orig_header;
                }
                foreach ($origHeaders as $origHeader) {
                    $content = str_replace("%" . $origHeader . "%", "", $content);
                }
            }
        }

        return $content;
    }

    /**
     * Replace Master-detail placeholders in content(page or post)
     */
    public static function filterTheContent( $content ) {
        if (isset($_POST['wdt_details_data'])){
            $detailsData= json_decode(stripslashes($_POST['wdt_details_data']), true);
            $columnsData = WDTConfigController::loadColumnsFromDB($detailsData['wdt_md_id_table']);
            $origHeaders= [];
            $removeOrigHeaders= [];
            foreach ($columnsData as $columnData){
                $advancedColumnSettings = json_decode($columnData->advanced_settings);
                if(isset($advancedColumnSettings->masterDetailColumnOption) &&
                    $advancedColumnSettings->masterDetailColumnOption == 1 ){
                    $origHeaders[] = $columnData->orig_header;
                } else if (isset($advancedColumnSettings->masterDetailColumnOption) &&
                    $advancedColumnSettings->masterDetailColumnOption == 0){
                    $removeOrigHeaders[]= $columnData->orig_header;
                }
            }
            foreach ($origHeaders as $origHeader) {
                if (isset($detailsData[$origHeader])) {
                    $detailsData[$origHeader] = apply_filters('wpdatatables_md_filter_details_data', $detailsData[$origHeader], $origHeader, $detailsData['wdt_md_id_table']);
                    $content = str_replace("%" . $origHeader . "%", $detailsData[$origHeader], $content);
                }
            }
            if (isset($removeOrigHeaders)){
                foreach ($removeOrigHeaders as $removeOrigHeader) {
                    $content = str_replace("%" . $removeOrigHeader . "%", '', $content);
                }
            }
        } else {
            $currentPostLink = get_permalink(get_the_ID());
            $content = self::removePlaceholdersFromContent($currentPostLink, $content);
        }

        return $content;
    }

    /**
     * Add Master-Detail activation on wpDataTables settings page
     */
    public static function addMasterDetailActivation()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/activation.inc.php';
        $activation = ob_get_contents();
        ob_end_clean();

        echo $activation;
    }

    /**
     * Enqueue Master-Detail add-on files on back-end settings page
     */
    public static function wdtMasterDetailEnqueueBackendSettings()
    {
        if (self::$initialized) {
            wp_enqueue_script(
                'wdt-md-settings',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.admin.settings.js',
                array(),
                WDT_MD_VERSION,
                true
            );
        }
    }

    /**
     * @param $transient
     *
     * @return mixed
     */
    public static function wdtCheckUpdateMasterDetail($transient)
    {

        if (class_exists('WDTTools')) {
            $pluginSlug = plugin_basename(__FILE__);

            if (empty($transient->checked)) {
                return $transient;
            }

            $purchaseCode = get_option('wdtPurchaseCodeStoreMasterDetail');

            $envatoTokenEmail = '';

            // Get the remote info
            $remoteInformation = WDTTools::getRemoteInformation('wdt-master-detail', $purchaseCode, $envatoTokenEmail);

            // If a newer version is available, add the update
            if ($remoteInformation && version_compare(WDT_MD_VERSION, $remoteInformation->new_version, '<')) {
                $remoteInformation->package = $remoteInformation->download_link;
                $transient->response[$pluginSlug] = $remoteInformation;
            }
        }

        return $transient;
    }

    /**
     * @param $response
     * @param $action
     * @param $args
     *
     * @return bool|mixed
     */
    public static function wdtCheckInfoMasterDetail($response, $action, $args)
    {

        if (class_exists('WDTTools')) {

            $pluginSlug = plugin_basename(__FILE__);

            if ('plugin_information' !== $action) {
                return $response;
            }

            if (empty($args->slug)) {
                return $response;
            }

            $purchaseCode = get_option('wdtPurchaseCodeStoreMasterDetail');

            $envatoTokenEmail = '';

            if ($args->slug === $pluginSlug) {
                return WDTTools::getRemoteInformation('wdt-master-detail', $purchaseCode, $envatoTokenEmail);
            }
        }

        return $response;
    }


    public static function addMessageOnPluginsPageMasterDetail()
    {
        /** @var bool $activated */
        $activated = get_option('wdtActivatedMasterDetail');

        /** @var string $url */
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

        /** @var string $redirect */
        $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

        if (!$activated) {
            echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Master-Detail Tables for wpDataTables.', 'wpdatatables'), $redirect);
        }
    }

    public static function addMessageOnUpdateMasterDetail($reply, $package, $updater)
    {
        if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === get_plugin_data( __FILE__ )['Name']) {
            /** @var string $url */
            $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

            /** @var string $redirect */
            $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

            if (!$package) {
                return new WP_Error(
                    'wpdatatables_master_detail_not_activated',
                    sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Master-Detail Tables for wpDataTables.', 'wpdatatables'), $redirect)
                );
            }

            return $reply;
        }

        return $reply;
    }
}