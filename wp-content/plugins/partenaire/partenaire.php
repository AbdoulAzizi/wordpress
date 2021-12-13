<?php
/**
 * @package Partenaire
 * @version 1.7.2
 */
/*
Plugin Name: Partenaire
Description: Affiche les partenaires
Author: Woby Web
Version: 1.7.2
*/


require plugin_dir_path( __FILE__ ) . 'crud_partenaire.php';
require plugin_dir_path( __FILE__ ) . 'crud_code_postal.php';
require plugin_dir_path( __FILE__ ) . 'crud_villes.php';
require plugin_dir_path( __FILE__ ) . 'crud_departement.php';
require plugin_dir_path( __FILE__ ) . 'metabox_code_postal.php';
require plugin_dir_path( __FILE__ ) . 'metabox_departement.php';


function partenaire_options_install()
{
    global $wpdb;
    $villes_table_name = $wpdb->prefix . "villes_france";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $villes_table_name (
        id_ville int(11) NOT NULL AUTO_INCREMENT,
        Code_commune_INSEE int(11) NULL,
        Nom_commune varchar(255) NOT NULL,
        Code_postal varchar(255)  NULL,
        PRIMARY KEY  (id_ville)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // create partenaire table
    $partenaire_table_name = $wpdb->prefix . "partenaire";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $partenaire_table_name (
        id_partenaire int(11) NOT NULL AUTO_INCREMENT,
        name  varchar (50) NOT NULL,
        phone varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        siret varchar(255) NOT NULL,
        PRIMARY KEY  (id_partenaire)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // create code_postal table with partenaire_id as foreign key
    $code_postal_table_name = $wpdb->prefix . "code_postal";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $code_postal_table_name (
        id_code_postal int(11) NOT NULL AUTO_INCREMENT,
        code_postal varchar(255) NOT NULL,
        partenaire_id int(11) NOT NULL,
        PRIMARY KEY  (id_code_postal)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    $departement_table_name = $wpdb->prefix . "departement";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $departement_table_name (
        id_departement int(11) NOT NULL AUTO_INCREMENT,
        departement varchar(255) NOT NULL,
        partenaire_id int(11) NOT NULL,
        PRIMARY KEY  (id_departement)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // import data from CSV file when plugin is activated
    // vérifier si la table est vide
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM $villes_table_name" );
    if ( $count == 0 ) {
        $csv_file = plugin_dir_path( __FILE__ ) . 'public/files/villes_france.csv';
        // vérifier si le fichier existe
        if ( file_exists( $csv_file ) ) {
         
            $csv_file = fopen( $csv_file, 'r' );
            while ( ( $csv_data = fgetcsv( $csv_file, 0, ';' ) ) !== FALSE ) {
                // skip the first line  
                if ( $csv_data[0] == 'Code_commune_INSEE' ) {
                    continue;
                }
                // vérifier si la ville existe déjà
                // $count = $wpdb->get_var( "SELECT COUNT(*) FROM $villes_table_name WHERE Code_commune_INSEE = '$csv_data[0]'" );
                // if ( $count == 0 ) {
                    $wpdb->insert(
                        $villes_table_name,
                        array(
                            'Code_commune_INSEE' => $csv_data[0],
                            'Nom_commune' => $csv_data[1],
                            'Code_postal' => $csv_data[2],
                        )
                    );
                // }

            }
            fclose( $csv_file );
        }
    }

   
}
// run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'partenaire_options_install');


/**
 * Deactivation hook.
 */
function partenaire_options_deactivate() {

    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}

// add bootstrap css
function partenaire_bootstrap_css() {
    wp_enqueue_style( 'partenaire-bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css' );
}
add_action( 'wp_enqueue_scripts', 'partenaire_bootstrap_css' );

// add public css
function partenaire_public_css() {
    wp_enqueue_style( 'partenaire-public-css', plugin_dir_url( __FILE__ ) . 'public/css/partenaire.css' );
}

function partenaire_admin_liste_des_partenaires() {
    
    include_once(plugin_dir_path( __FILE__ ) . '/admin/list_partenaire.php');

    // call list partenaire function
    list_partenaire();
}

function main_partenaire(){

    
    include_once(plugin_dir_path( __FILE__ ) . '/admin/add_partenaire.php');

    // call add partenaire function
    add_partenaire();

    // call insert partenaire function
    insert_partenaire();
 
}

   

 
function partenaire_admin_liste_des_villes(){

    partenaire_import_csv();
    
    echo "<h1> Importer le fichier CSV </h1>";
    
    // form to import CSV file
    echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="csv_file" id="csv_file" placeholder="Choissisez le fichier CSV" />';
    // echo '<input type="submit" name="submitCSV" value="Import" />';
     echo submit_button('Importer');
    echo '</form>';

}

// function to import CSV file
function partenaire_import_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . "villes_france";
    $charset_collate = $wpdb->get_charset_collate();
    // check if the form has been submitted
    if ( isset($_FILES['csv_file']) ) {
        // upload the file and store it in the media library
        $csv_file = wp_upload_bits( $_FILES['csv_file']['name'], null, file_get_contents( $_FILES['csv_file']['tmp_name'] ) );
        if ( ! $csv_file['error'] ) {
                    // var_dump($_FILES['csv_file']);exit;

            // create the media library attachment
            $id = media_handle_upload( 'csv_file', 0 );
            if ( ! is_wp_error( $id ) ) {
                // get the file path
                $file_path = get_attached_file( $id );
                // var_dump($id);exit;
                // open the file    
                $file = fopen( $file_path, 'r' );
                // read the file
                    while ( ( $line = fgetcsv( $file, 0, ';' ) ) !== FALSE ) {

                        // skip the first line
                        if ( $line[0] == 'Code_commune_INSEE' ) {
                            continue;
                        }

                    // insert the data into the database
                    $wpdb->insert( $table_name, array(
                        'Code_commune_INSEE' => $line[0],
                        'Nom_commune' => $line[1],
                        'Code_postal' => $line[2],
                        'Ligne_5' => $line[3],
                        'Libelle_d_acheminement' => $line[4],
                        // 'coordonnees_gps' => $line[5],
                        // 'code_commune_etrangere' => $line[6]
                    ) );
                }
                // close the file
                fclose( $file );
                // delete the file
                unlink( $file_path );
                // display a message
                echo '<div class="updated"><p>' . __( 'Le fichier CSV a été importé avec succès.', 'textdomain' ) . '</p></div>';
            }
        }
    }
}


register_deactivation_hook( __FILE__, 'partenaire_options_deactivate' );

function partenaire_options_uninstall() {
    
    global $wpdb;

    // drop villes table if it exists
    $villes_table_name = $wpdb->prefix . "villes_france";
    $wpdb->query("DROP TABLE IF EXISTS {$villes_table_name}");

    // drop partenaire table if exists
    $partenaire_table_name = $wpdb->prefix . "partenaire";
    $wpdb->query("DROP TABLE IF EXISTS {$partenaire_table_name}");

    // drop code_postal table if exits
    $code_postal_table = $wpdb->prefix . "code_postal";
    $wpdb->query("DROP TABLE IF EXISTS {$code_postal_table}");

    // drop departement table if exists
    $departement_postal_table = $wpdb->prefix . "departement";
    $wpdb->query("DROP TABLE IF EXISTS {$departement_postal_table}");


}

register_uninstall_hook(__FILE__, 'partenaire_options_uninstall');
