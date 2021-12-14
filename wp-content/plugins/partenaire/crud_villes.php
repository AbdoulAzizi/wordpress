<?php

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/form_villes.php';

function admin_styles_villes() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'admin_styles_villes');


function wpbc_plugin_load_textdomain_villes() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain_villes' );



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Villes_Custom_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=form_villes&id_ville=%s">%s</a>', $item['id_ville'], __('Éditer', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id_ville=%s">%s</a>', $_REQUEST['page'], $item['id_ville'], __('Supprimer', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['Code_commune_INSEE'],
            $this->row_actions($actions)
        );
    }
   

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id_ville[]" value="%s" />',
            $item['id_ville']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Code commune INSEE', 'wpbc'),
            'Nom_commune'     => __('Nom commune', 'wpbc'),
            'Code_postal'     => __('Code postal', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('Code commune INSEE', true),
            'Nom_commune'     => array('Nom commune', true),
            'Code_postal'     => array('Code postal', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Supprimer'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'villes_france'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id_ville']) ? $_REQUEST['id_ville'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id_ville IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'villes_france'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id_ville) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']- 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id_ville';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function validate_form_villes($item)
{
    $messages = array();

    if (empty($item['Code_postal'])) $messages[] = __('Code postal manquant', 'wpbc');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages_villes()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages_villes');