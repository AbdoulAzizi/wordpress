<?php

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/form_departement.php';

function wpbc_custom_admin_styles_departments() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'wpbc_custom_admin_styles_departments');


function wpbc_plugin_load_textdomain_departments() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain_departments' );


global $wpbc_db_version;
$wpbc_db_version = '1.1.0'; 



function wpbc_install_data_departments()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'department_france'; 

}

register_activation_hook(__FILE__, 'wpbc_install_data_departments');


function wpbc_update_db_check_departments()
{
    global $wpbc_db_version;
    if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
        wpbc_install();
    }
}

add_action('plugins_loaded', 'wpbc_update_db_check_departments');


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Table_List_Table_departements extends WP_List_Table
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


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=form_departements&id=%s">%s</a>', $item['id'], __('Éditer', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Supprimer', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['nom_departement'],
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
            
            'nom_department'     => __('départment', 'wpbc'),
            'code_department'     => __('Numéro', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            // 'name'      => array('Code commune INSEE', true),
            // 'Code_commune_INSEE'  => array('Code communeINSEE', true),
            'nom_department'     => array('départment', true),
            'code_department'     => array('Numéro', true),
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
        $table_name = $wpdb->prefix . 'department_france'; 

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
        $table_name = $wpdb->prefix . 'department_france'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $search = isset($_REQUEST['s']) ? $_REQUEST['s'] : false;		 if($search===false){        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");		}else{		$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name  WHERE name  like '%".str_replace("'","\\'",str_replace("\\","\\\\",$search))."%'");			}


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

if($search===false){			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d, %d",  $paged*$per_page, $per_page), ARRAY_A);		}else{			$this->items = $wpdb->get_results("SELECT * FROM $table_name WHERE nom_department  like '%".str_replace("'","\\'",str_replace("\\","\\\\",$search))."%' ORDER BY $orderby $order LIMIT ".($paged*$per_page).", ".$per_page, ARRAY_A);		}

        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function validate_form_departements($item)
{
    $messages = array();

    if (empty($item['code_department'])) $messages[] = __('Code départements manquant', 'wpbc');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages_departments()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages_departments');