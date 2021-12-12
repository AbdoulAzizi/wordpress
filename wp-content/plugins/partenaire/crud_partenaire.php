<?php

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/form_partenaire.php';

function wpbc_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'wpbc_custom_admin_styles');


function wpbc_plugin_load_textdomain() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain' );


global $wpbc_db_version;
$wpbc_db_version = '1.1.0'; 



function wpbc_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'partenaire'; 

}

register_activation_hook(__FILE__, 'wpbc_install_data');


function wpbc_update_db_check()
{
    global $wpbc_db_version;
    if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
        wpbc_install();
    }
}

add_action('plugins_loaded', 'wpbc_update_db_check');


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table extends WP_List_Table
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
            'list_code_postal' =>sprintf('<a href="?page=liste_code_postal&id_partenaire=%s">%s</a>',  $item['id'],__('Code postal', 'wpbc')),
            'liste_departement' =>sprintf('<a href="?page=liste_departement&id_partenaire=%s">%s</a>',  $item['id'],__('Département', 'wpbc')),
            // 'add_code_postal' => sprintf('<a href="?page=form_departement&id_partenaire=%s">%s</a>',$item['id'],__('Département','wpbc')),
            'edit' => sprintf('<a href="?page=contacts_form&id=%s">%s</a>', $item['id'], __('Éditer', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Supprimer', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    function add_cp($item)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'partenaire'; 
        $id = $item['id'];
        $code_postal = $_POST['code_postal'];
        $wpdb->insert($table_name, array(
            'id' => $id,
            'code_postal' => $code_postal,
        ));
        wp_redirect(admin_url('admin.php?page=wpbc_list_table_example'));
    }

    function column_code_postal($item)
    {
        return '<em>' . $item['code_postal'] . '</em>';
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
            'name'      => __('Nom', 'wpbc'),
            'phone'  => __('Numéro de téléphone', 'wpbc'),
            'email'     => __('E-Mail', 'wpbc'),
            'siret'     => __('Siret', 'wpbc'),
            // 'code_postal'   => __('Code postal', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'phone'  => array('Numéro de téléphone', true),
            'email'     => array('email', true),
            'siret'     => array('siret', true),
            // 'code_postal'   => array('code_postal', true),
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
        $table_name = $wpdb->prefix . 'partenaire'; 

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
        $table_name = $wpdb->prefix . 'partenaire'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function wpbc_admin_menu()
{
    add_menu_page(__('Partenaire', 'wpbc'), __('Partenaire', 'wpbc'), 'activate_plugins', 'partenaires', 'wpbc_contacts_page_handler');
    add_submenu_page('Partenaire', __('Partenaire', 'wpbc'), __('Partenaires', 'wpbc'), 'activate_plugins', 'partenaires', 'wpbc_contacts_page_handler');
   
    add_submenu_page('partenaires', __('Ajouter un partenaire', 'wpbc'), __('Ajouter un partenaire', 'wpbc'), 'activate_plugins', 'contacts_form', 'wpbc_contacts_form_page_handler');
    add_submenu_page('null', __('CP', 'wpbc'), __('CP', 'wpbc'), 'activate_plugins', 'form_cp', 'wpbc_contacts_form_page_handler_cp');
    add_submenu_page('null', __('CP', 'wpbc'), __('CP', 'wpbc'), 'activate_plugins', 'form_departement', 'departement_form_page_handler');
    add_submenu_page('null', __('Assigner code postal', 'wpbc'), __('Assigner code postal', 'wpbc'), 'activate_plugins', 'assign_code_postal', 'add_code_postal_page');
    add_submenu_page('null', __('Associer code postal', 'wpbc'), __('Associer code postal', 'wpbc'), 'activate_plugins', 'liste_code_postal', 'wpbc_contacts_page_handler_cp');
    add_submenu_page('null', __('Département', 'wpbc'), __('Département', 'wpbc'), 'activate_plugins', 'liste_departement', 'departement_page_handler');
    add_submenu_page('partenaires', 'Importer des villes', 'Importer des villes', 8, 'importation_des_villes', 'partenaire_admin_liste_des_villes');
    add_submenu_page('partenaires', 'Liste des villes', 'Liste des villes', 8, 'liste_des_villes', 'wpbc_contacts_page_handler_villes');
    add_submenu_page('null', __('CP', 'wpbc'), __('CP', 'wpbc'), 'activate_plugins', 'form_villes', 'form_page_handler_villes');


}

add_action('admin_menu', 'wpbc_admin_menu');

function wpbc_validate_contact($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Le nom est obligatoire', 'wpbc');
    // if (empty($item['lastname'])) $messages[] = __('Last Name is required', 'wpbc');
    // if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format', 'wpbc');
    // if(!empty($item['phone']) && !absint(intval($item['phone'])))  $messages[] = __('Phone can not be less than zero');
    // if(!empty($item['phone']) && !preg_match('/[0-9]+/', $item['phone'])) $messages[] = __('Phone must be number');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages');