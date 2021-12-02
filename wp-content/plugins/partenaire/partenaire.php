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


 
// function to create the DB / Options / Defaults					
function partenaire_options_install() {

    global $wpdb;
    $table_name = $wpdb->prefix . "villes_france";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        Code_commune_INSEE int(11) NULL,
        Nom_commune varchar(255) NOT NULL,
        Code_postal varchar(255)  NULL,
        Ligne_5 varchar(255) NULL,
        Libelle_d_acheminement varchar(255) NULL,
        -- coordonnees_gps varchar(255) NOT NULL,
        -- code_commune_etrangere varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // insert data from CSV file
    
    $csv_file = plugin_dir_path( __FILE__ ) . 'partenaire.csv';
    $csv_file = fopen( $csv_file, 'r' );
    $csv_data = fgetcsv( $csv_file, 0, ';' );
    while ( ( $csv_data = fgetcsv( $csv_file, 0, ';' ) ) !== FALSE ) {

          // skip the first line
          if ( $line[0] == 'Code_commune_INSEE' ) {
            continue;
        }

        $wpdb->insert( $table_name, array(
            'Code_commune_INSEE' => $csv_data[0],
            'Nom_commune' => $csv_data[1],
            'Code_postal' => $csv_data[2],
            'Ligne_5' => $csv_data[3],
            'Libelle_d_acheminement' => $csv_data[4],
            // 'coordonnees_gps' => $csv_data[5],
            // 'code_commune_etrangere' => $csv_data[6]
        ) );
    }
    fclose( $csv_file );
    
 
}
// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'partenaire_options_install');


/**
 * Deactivation hook.
 */
function partenaire_options_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    // unregister_post_type( 'book' );

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

wp_enqueue_script( 'bootstrap-js', plugins_url( '/bootstrap/js/bootstrap.min.js', __FILE__ ), array( 'jquery' ), null, true );
wp_enqueue_style( 'bootstrap-css',plugins_url( '/bootstrap/css/bootstrap.min.css', __FILE__ ) );

add_action( 'wp_enqueue_scripts', 'partenaire_public_css' );
add_action('admin_menu', 'partenaire_plugin_setup_menu');
 
function partenaire_plugin_setup_menu(){
    add_menu_page( 'Page des partenaires', 'Nos Partenaires', 'manage_options', 'aprtenaire-plugin', 'partenaire_admin_init' );
}
 
function partenaire_admin_init(){

    partenaire_import_csv();
    
    echo "<h1> Importer le file CSV </h1>";
    
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
    // delete the post type
    // unregister_post_type( 'book' );
    // drop a custom database table
    global $wpdb;
    $table_name = $wpdb->prefix . "villes_france";
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}

register_uninstall_hook(__FILE__, 'partenaire_options_uninstall');
