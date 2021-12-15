<?php

defined( 'ABSPATH' );

require plugin_dir_path( __FILE__ ) . 'includes/form_departement.php';


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Departement_Custom_List_Table extends WP_List_Table
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
            'edit' => sprintf('<a href="?page=form_departement&id_departement=%s&id_partenaire=%s">%s</a>', $item['id_departement'], $_REQUEST['id_partenaire'], __('Éditer', 'partenaire')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id_departement=%s&id_partenaire=%s">%s</a>', $_REQUEST['page'], $item['id_departement'], $_REQUEST['id_partenaire'], __('Supprimer', 'partenaire')),
        );

        return sprintf('%s %s',
            $item['departement'],
            $this->row_actions($actions)
        );
    }

    function column_departement($item)
    {
        return '<em>' . $item['departement'] . '</em>';
    }

   

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id_departement[]" value="%s" />',
            $item['id_departement']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Département', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('Département', true),
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
        $table_name = $wpdb->prefix . 'departement'; 
       
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id_departement']) ? $_REQUEST['id_departement'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id_departement IN($ids)");
                
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'departement'; 

        $partenaire_id = $_REQUEST['id_partenaire'];

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id_departement) FROM $table_name WHERE partenaire_id = $partenaire_id");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'departement';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE partenaire_id = %d ORDER BY $orderby $order LIMIT %d OFFSET %d", $partenaire_id, $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function validate_form_departement($item)
{
    $messages = array();

    if (empty($item['departement'])) $messages[] = __('Département manquant', 'wpbc');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}
