<?php

defined( 'ABSPATH' );

require plugin_dir_path( __FILE__ ) . 'includes/form_partenaire.php';

function partenaires_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'partenaires_custom_admin_styles');


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Partenaire_Custom_List_Table extends WP_List_Table
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
            'list_code_postal' =>sprintf('<a href="?page=liste_code_postal&id_partenaire=%s">%s</a>',  $item['id_partenaire'],__('Code postal', 'wpbc')),
            'liste_departement' =>sprintf('<a href="?page=liste_departement&id_partenaire=%s">%s</a>',  $item['id_partenaire'],__('Département', 'wpbc')),
            'edit' => sprintf('<a href="?page=contacts_form&id_partenaire=%s">%s</a>', $item['id_partenaire'], __('Éditer', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id_partenaire=%s">%s</a>', $_REQUEST['page'], $item['id_partenaire'], __('Supprimer', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    function column_code_postal($item)
    {
        return '<em>' . $item['code_postal'] . '</em>';
    }

   

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id_partenaire[]" value="%s" />',
            $item['id_partenaire']
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
            'delete' => 'Supprimer'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'partenaire'; 
        $code_postal_table = $wpdb->prefix . 'code_postal';
        $departement_table = $wpdb->prefix . 'departement';


        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id_partenaire']) ? $_REQUEST['id_partenaire'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id_partenaire IN($ids)");
                // Supprimer les codes postaux associés
                $wpdb->query("DELETE FROM $code_postal_table WHERE partenaire_id IN($ids)");
                // Supprimer les départements associés
                $wpdb->query("DELETE FROM $departement_table WHERE partenaire_id IN($ids)");
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

        $total_items = $wpdb->get_var("SELECT COUNT(id_partenaire) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page ): 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
    }
}


function partenaires_validate_data($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Le nom est obligatoire', 'wpbc');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}