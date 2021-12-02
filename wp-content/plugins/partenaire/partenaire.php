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

    // create partenaire table
    $partenaire_table_name = $wpdb->prefix . "partenaire";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $partenaire_table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        partenaire_nom varchar(255) NOT NULL,
        numero_telephone varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        siret varchar(255) NOT NULL,
        code_postal varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // insert data from CSV file
    
    // $csv_file = plugin_dir_path( __FILE__ ) . 'partenaire.csv';
    // $csv_file = fopen( $csv_file, 'r' );
    // $csv_data = fgetcsv( $csv_file, 0, ';' );
    // while ( ( $csv_data = fgetcsv( $csv_file, 0, ';' ) ) !== FALSE ) {

    //       // skip the first line
    //       if ( $line[0] == 'Code_commune_INSEE' ) {
    //         continue;
    //     }

    //     $wpdb->insert( $table_name, array(
    //         'Code_commune_INSEE' => $csv_data[0],
    //         'Nom_commune' => $csv_data[1],
    //         'Code_postal' => $csv_data[2],
    //         'Ligne_5' => $csv_data[3],
    //         'Libelle_d_acheminement' => $csv_data[4],
    //         // 'coordonnees_gps' => $csv_data[5],
    //         // 'code_commune_etrangere' => $csv_data[6]
    //     ) );
    // }
    // fclose( $csv_file );
    
 
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
// add_action('admin_menu', 'partenaire_plugin_setup_menu');
// add_action('admin_menu', 'partenaire');
 

function partenaire_menu() {
    add_menu_page('Partenaire', 'Partenaire', 'manage_options', __FILE__, 'partenaire', plugins_url('MyPluginFolder/images/icon.png') );
    add_submenu_page(__FILE__, 'Liste des partenaires', 'Liste des partenaires', 'manage_options', 'partenaire', 'partenaire_admin_liste_des_partenaires');
    add_submenu_page(__FILE__, 'Importer des villes', 'Importer des villes', 8, 'myplugin-cars-page', 'partenaire_admin_liste_des_villes');
}
add_action('admin_menu', 'partenaire_menu');

// function partenaire_plugin_setup_menu(){
//     add_menu_page( 'Page des partenaires', 'Importer des villes', 'manage_options', 'aprtenaire-plugin', 'partenaire_admin_liste_des_villes' );
// }

function partenaire_admin_liste_des_partenaires() {
    // call list partenaire function
    // list_partenaire();
    // include plugin_dir_path( __FILE__ ) . 'partenaire.php';
    // var_dump(plugin_dir_path( __FILE__ ) . '/admin/partenaire.php');
    
    include(plugin_dir_path( __FILE__ ) . '/admin/list_partenaire.php');
}

function partenaire(){

    // call adding new partenaire function
    add_partenaire();
    
    // form to add new partenaire
    echo '<div class="container">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<h1>Ajouter un partenaire</h1>';
    echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '" enctype="multipart/form-data">';
    echo '<input type="hidden" name="partenaire_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
    echo '<table class="form-table">';
    echo '<tbody>';
    echo '<tr>';
    echo '<th scope="row"><label for="partenaire_nom">Nom</label></th>';
    echo '<td><input name="partenaire_nom" type="text" id="partenaire_nom" class="regular-text" value="" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="partenaire_adresse">Numéro de téléphone</label></th>';
    echo '<td><input name="numero_telephone" type="text" id="numero_telephone" class="regular-text" value="" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><la for="partenaire_adresse">Email</label></th>';
    echo '<td><input name="email" type="text" id="email" class="regular-text" value="" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="partenaire_adresse">Siret</label></th>';
    echo '<td><input name="siret" type="text" id="siret" class="regular-text" value="" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="partenaire_adresse">Code postal</label></th>';
    echo '<td><input name="code_postal" type="text" id="code_postal" class="regular-text" value="" /></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '<p class="submit"><input type="submit" name="submitPartenaire" id="submitPartenaire" class="button button-primary" value="Ajouter"></p>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

}

function add_partenaire(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'partenaire';

    if (isset($_POST['submitPartenaire'])) {
        $nom = $_POST['partenaire_nom'];
        $numero_telephone = $_POST['numero_telephone'];
        $email = $_POST['email'];
        $siret = $_POST['siret'];
        $code_postal = $_POST['code_postal'];
        $wpdb->insert(
            $table_name,
            array(
                'partenaire_nom' => $nom,
                'numero_telephone' => $numero_telephone,
                'email' => $email,
                'siret' => $siret,
                'code_postal' => $code_postal
            )
        );
    }
}

   

 
function partenaire_admin_liste_des_villes(){

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

// register_post_type( 'partenaire',
//     array(
//         'labels' => array(
//             'name' => __( 'Partenaires' ),
//             'singular_name' => __( 'Partenaire' )
//         ),
//         'public' => true,
//         'has_archive' => true,
//         'rewrite' => array('slug' => 'partenaire'),
//         'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'page-attributes', 'post-formats', 'thumbnail' ),
//         'taxonomies' => array( 'category', 'post_tag' ),
//         'menu_icon' => 'dashicons-groups',
//         'menu_position' => 5,
//         'show_in_rest' => true,
//     )
// );


// function to display the custom post type
function partenaire_display_post_type() {
    $args = array(
        'post_type' => 'partenaire',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title'
    );
    $partenaire_query = new WP_Query( $args );
    if ( $partenaire_query->have_posts() ) {
        while ( $partenaire_query->have_posts() ) {
            $partenaire_query->the_post();
            ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                        <p class="card-text"><?php the_excerpt(); ?></p>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">En savoir plus</a>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    wp_reset_postdata();
}



register_deactivation_hook( __FILE__, 'partenaire_options_deactivate' );

function partenaire_options_uninstall() {
    // delete the post type
    // unregister_post_type( 'book' );
    // drop a custom database table
    global $wpdb;
    $table_name = $wpdb->prefix . "villes_france";
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    // drop partenaire table if exists
    $table_name = $wpdb->prefix . "partenaire";
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}

register_uninstall_hook(__FILE__, 'partenaire_options_uninstall');
