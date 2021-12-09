<?php

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );
// require plugin_dir_path( __FILE__ ) . 'includes/code_postal.php';
require plugin_dir_path( __FILE__ ) . 'includes/form_code_postal.php';

function wpbc_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'wpbc_admin_styles');


function wpbc_plugin_load_textdomain_cp() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain_cp' );


global $wpbc_db_version;
$wpbc_db_version = '1.1.0'; 



function wpbc_update_db_check_cp()
{
    global $wpbc_db_version;
    if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
        wpbc_install();
    }
}

add_action('plugins_loaded', 'wpbc_update_db_check_cp');


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table_CP extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'contact',
            'plural'   => 'contacts',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            // 'cp' => sprintf('<a href="?page=form_cp&id=%s">%s</a>', $item['id'], __('cp', 'wpbc')),
            'edit' => sprintf('<a href="?page=form_cp&id=%s&id_partenaire=%s">%s</a>', $item['id'],$_REQUEST['id_partenaire'], __('Éditer', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s&id_partenaire=%s">%s</a>', $_REQUEST['page'], $item['id'], $_REQUEST['id_partenaire'], __('Supprimer', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['code_postal'],
            $this->row_actions($actions)
        );
    }
   

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Code postal', 'wpbc'),            
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('Code postal', true),            
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'code_postal'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'code_postal'; 

        // var_dump($_REQUEST['id_partenaire']);
        $partenaire_id = $_REQUEST['id_partenaire'];

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'code_postal';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE partenaire_id = %d ORDER BY $orderby $order LIMIT %d OFFSET %d", $partenaire_id, $per_page, $paged), ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function form_validate_code_postal($item)
{
    $messages = array();

    if (empty($item['code_postal'])) $messages[] = __('Le champ code postal est obligatoire.', 'wpbc');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages_cp()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages_cp');