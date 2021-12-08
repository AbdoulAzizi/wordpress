<?php

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/meta_villes.php';

function wpbc_custom_admin_styles_villes() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'wpbc_custom_admin_styles_villes');


function wpbc_plugin_load_textdomain_villes() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain_villes' );


global $wpbc_db_version;
$wpbc_db_version = '1.1.0'; 



function wpbc_install_data_villes()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'villes_france'; 

}

register_activation_hook(__FILE__, 'wpbc_install_data_villes');


function wpbc_update_db_check_villes()
{
    global $wpbc_db_version;
    if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
        wpbc_install();
    }
}

add_action('plugins_loaded', 'wpbc_update_db_check_villes');


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table_Villes extends WP_List_Table
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
            'edit' => sprintf('<a href="?page=form_villes&id=%s">%s</a>', $item['id'], __('Éditer', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Supprimer', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['Code_commune_INSEE'],
            $this->row_actions($actions)
        );
    }

    function column_code_postal($item)
    {
        return '<em>' . $item['Code_postal'] . '</em>';
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
            'name'      => __('Code commune INSEE', 'wpbc'),
            // 'Code_commune_INSEE'  => __('Code communeINSEE', 'wpbc'),
            'Nom_commune'     => __('Nom commune', 'wpbc'),
            'Code_postal'     => __('Code postal', 'wpbc'),
            'Ligne_5'   => __('Ligne 5', 'wpbc'),
            'Libelle_d_acheminement'   => __('Libellé d\'acheminement', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('Code commune INSEE', true),
            // 'Code_commune_INSEE'  => array('Code communeINSEE', true),
            'Nom_commune'     => array('Nom commune', true),
            'Code_postal'     => array('Code postal', true),
            'Ligne_5'   => array('Ligne 5', true),
            'Libelle_d_acheminement'   => array('Libellé d\'acheminement', true),
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
        $table_name = $wpdb->prefix . 'villes_france'; 

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
        $table_name = $wpdb->prefix . 'villes_france'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function wpbc_validate_contact_villes($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Le nom est obligatoire', 'wpbc');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages_villes()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages_villes');