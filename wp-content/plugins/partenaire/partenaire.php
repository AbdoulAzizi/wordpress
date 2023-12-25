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

require plugin_dir_path(__FILE__) . 'crud_partenaire.php';

require plugin_dir_path(__FILE__) . 'crud_code_postal.php';

require plugin_dir_path(__FILE__) . 'crud_villes.php';

require plugin_dir_path(__FILE__) . 'crud_departements.php';

require plugin_dir_path(__FILE__) . 'metabox_code_postal.php';

require plugin_dir_path(__FILE__) . 'metabox_departement.php';

function partenaire_options_install()
{

    global $wpdb;

    $depts_table_name = $wpdb->prefix . "department_france";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $depts_table_name (

        id int(11) NOT NULL AUTO_INCREMENT,

        nom_department varchar(255) NOT NULL,

        code_department varchar(3) NOT NULL,

        PRIMARY KEY  (id)

    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta($sql);

    $villes_table_name = $wpdb->prefix . "villes_france";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $villes_table_name (

        id int(11) NOT NULL AUTO_INCREMENT,

        Nom_commune varchar(255) NOT NULL,

        Code_postal varchar(255)  NULL,

        code_insee varchar(120)  NULL,

        PRIMARY KEY  (id)

    ) $charset_collate;";

    // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta($sql);

    // create partenaire table

    $partenaire_table_name = $wpdb->prefix . "partenaire";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $partenaire_table_name (

        id int(11) NOT NULL AUTO_INCREMENT,

        name  varchar (50) NOT NULL,

        phone varchar(255) NOT NULL,

		phone_second varchar(255) NOT NULL,

        email varchar(255) NOT NULL,

        siret varchar(255) NOT NULL,

        PRIMARY KEY  (id)

    ) $charset_collate;";

    // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta($sql);

    // create code_postal table with partenaire_id as foreign key

    $code_postal_table_name = $wpdb->prefix . "code_postal";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $code_postal_table_name (

        id int(11) NOT NULL AUTO_INCREMENT,

        code_postal varchar(6) NOT NULL,

        partenaire_id int(11) NOT NULL,

        PRIMARY KEY  (id),

	   UNIQUE(`code_postal`,`partenaire_id`)

    ) $charset_collate;";

    // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta($sql);

    // import data from CSV file when plugin is activated

    // vérifier si la table est vide

    $count = $wpdb->get_var("SELECT COUNT(*) FROM $villes_table_name");

    if ($count == 0) {

        $csv_file = plugin_dir_path(__FILE__) . 'public/files/villes_france.csv';

        // vérifier si le fichier existe

        if (file_exists($csv_file)) {

            $csv_file = fopen($csv_file, 'r');

            $lineNumber = 0;

            while (($csv_data = fgetcsv($csv_file, 0, ';', '"')) !== false) {

                // skip the first line

                if ($lineNumber > 0) {

                    $csv_data[1] = str_replace(' ', '', $csv_data[1]);

                    if (strpos($csv_data[1], '-') !== false) {
                        $csv_data[1] = substr($csv_data[1], 0, strpos($csv_data[1], '-'));
                    }

                    $wpdb->insert(

                        $villes_table_name,

                        array(

                            'Nom_commune' => $csv_data[0],

                            'Code_postal' => str_pad(str_replace(' ', '', $csv_data[1]), 5, '0', STR_PAD_LEFT),

                            'code_insee' => str_replace(' ', '', $csv_data[2]),

                        )

                    );

                }

                $lineNumber++;

            }

            fclose($csv_file);

        }

    }

    $count = $wpdb->get_var("SELECT COUNT(*) FROM $depts_table_name");

    if ($count == 0) {

        $csv_file = plugin_dir_path(__FILE__) . 'public/files/departements_france.csv';

        // vérifier si le fichier existe

        if (file_exists($csv_file)) {

            $csv_file = fopen($csv_file, 'r');

            $lineNumber = 0;

            while (($csv_data = fgetcsv($csv_file, 0, ';', '"')) !== false) {

                // skip the first line

                if ($lineNumber > 0) {

                    $wpdb->insert(

                        $depts_table_name,

                        array(

                            'nom_department' => $csv_data[1],

                            'code_department' => str_replace(' ', '', $csv_data[0]),

                        )

                    );

                }

                $lineNumber++;

            }

            fclose($csv_file);

        }

    }

}

// run the install scripts upon plugin activation

register_activation_hook(__FILE__, 'partenaire_options_install');

function wpbc_install()
{

    global $wpbc_db_version;

    //update

    update_option('wpbc_db_version', $wpbc_db_version);

}

/**

 * Deactivation hook.

 */

function partenaire_options_deactivate()
{

    // Clear the permalinks to remove our post type's rules from the database.

    flush_rewrite_rules();

}

function partenaire_admin_liste_des_partenaires()
{

    include_once plugin_dir_path(__FILE__) . '/admin/list_partenaire.php';

    // call list partenaire function

    list_partenaire();

}

function main_partenaire()
{

    include_once plugin_dir_path(__FILE__) . '/admin/add_partenaire.php';

    // call add partenaire function

    add_partenaire();

    // call insert partenaire function

    insert_partenaire();

}

function partenaire_export_csv()
{

    global $wpdb;

    if (isset($_POST['downloadCSV'])) {

        ob_start();

        echo "\"Nom du partenaire\";\"tel\";\"tel2\";\"email\";\"siret\";\"departement\";\"code postal\"\n";

        // exit;

        $res = $wpdb->get_results("SELECT p.*,group_concat(c.code_postal) as cp FROM  " . $wpdb->prefix . "partenaire p LEFT JOIN " . $wpdb->prefix . "code_postal c ON (p.id=c.partenaire_id) WHERE 1 GROUP BY p.id", ARRAY_A);

        foreach ($res as $p) {

            echo '"' . str_replace('"', '""', $p['name']) . '"';

            echo ';"' . str_replace('"', '""', $p['phone']) . '"';

            echo ';"' . str_replace('"', '""', $p['phone_second']) . '"';

            echo ';"' . str_replace('"', '""', $p['email']) . '"';

            echo ';"' . str_replace('"', '""', $p['siret']) . '"';

            echo ';"' . $p['cp'] . '"';

            echo "\n";

        }

        $rs = ob_get_clean();

        // var_dump($rs);

        ?>

		<script language="javascript">

			var encodedUri = encodeURI(decodeURIComponent(escape(window.atob("<?php echo base64_encode("data:text/csv;charset=utf-8," . $rs); ?>"))));

	var link = document.createElement("a");

	link.setAttribute("href", encodedUri);

	link.setAttribute("download", "partenaires.csv");

	document.body.appendChild(link); // Required for FF



	link.click();

</script>

<?php

        // die();

    }

}

function partenaire_import_csv()
{

    global $wpdb;

    // var_dump("1");

    $table_name = $wpdb->prefix . "partenaire";

    $charset_collate = $wpdb->get_charset_collate();

    // check if the form has been submitted

    if (isset($_FILES['csv_file_partenaire'])) {

        // var_dump("2");

        // upload the file and store it in the media library

        $csv_file_partenaire = wp_upload_bits($_FILES['csv_file_partenaire']['name'], null, file_get_contents($_FILES['csv_file_partenaire']['tmp_name']));

        if (!$csv_file_partenaire['error']) {

            // var_dump($_FILES['csv_file_partenaire']);exit;

            // create the media library attachment

            $id = media_handle_upload('csv_file_partenaire', 0);

            if (!is_wp_error($id)) {

                // get the file path

                $file_path = get_attached_file($id);

                // var_dump($id);exit;

                // open the file

                $file = fopen($file_path, 'r');

                // read the file

                $lineNumber = 0;

                while (($line = fgetcsv($file, 0, ';', '"')) !== false) {

                    // var_dump($line);

                    // skip the first line

                    if ($lineNumber > 0) {

                        // insert the data into the database

                        $P = $wpdb->insert($table_name, array(

                            'name' => $line[0],

                            'phone' => $line[1],

                            'phone_second' => $line[2],

                            'email' => $line[3],

                            'siret' => $line[4],

                        ));

                        $P = $wpdb->insert_id;

                        $cp = explode(',', $line[5]);

                        foreach ($cp as $c) {

                            $wpdb->insert($wpdb->prefix . "code_postal", array(

                                'code_postal' => trim($c),

                                'partenaire_id' => $P,

                            ));

                        }

                    }

                    $lineNumber++;

                }

                // close the file

                fclose($file);

                // delete the file

                unlink($file_path);

                // display a message

                echo '<div class="updated"><p>' . __('Le fichier CSV a été importé avec succès.', 'textdomain') . '</p></div>';

            }

        }

    }

}

function partenaire_admin_importexport()
{

    partenaire_import_csv();

    partenaire_export_csv();

    echo "<h1> Exporter des partenaires CSV </h1>";

    echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';

    echo '<input type="submit" name="downloadCSV" value="Exporter" />';

    // echo submit_button('Importer');

    echo '</form>';

    echo "<h1> Importer des partenaires CSV </h1>";

    echo "<p> La première ligne doit contenir les en-tetes, et le fichier doit etre au format \"Nom du partenaire\";\"tel\";\"tel2\";\"email\";\"siret\";\"departement\";\"code postal\" </p>";

    // form to import CSV file

    echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';

    echo '<input type="file" name="csv_file_partenaire" id="csv_file_partenaire"" placeholder="Choissisez le fichier CSV" />';

    // echo '<input type="submit" name="submitCSV" value="Import" />';

    echo submit_button('Importer');

    echo '</form>';

}

function partenaire_admin_liste_des_villes()
{

    partenaire_import_csv_villes();

    echo "<h1> Importer le fichier CSV </h1>";

    echo "<p> La première ligne doit contenir les en-tetes, et le fichier doit etre au format \"Nom de la ville\";\"code postal\" </p>";

    // form to import CSV file

    echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';

    echo '<input type="file" name="csv_file" id="csv_file" placeholder="Choissisez le fichier CSV" />';

    // echo '<input type="submit" name="submitCSV" value="Import" />';

    echo submit_button('Importer');

    echo '</form>';

}

// function to import CSV file

function partenaire_import_csv_villes()
{

    global $wpdb;

    $table_name = $wpdb->prefix . "villes_france";

    $charset_collate = $wpdb->get_charset_collate();

    // check if the form has been submitted

    if (isset($_FILES['csv_file'])) {

        // upload the file and store it in the media library

        $csv_file = wp_upload_bits($_FILES['csv_file']['name'], null, file_get_contents($_FILES['csv_file']['tmp_name']));

        if (!$csv_file['error']) {

            // var_dump($_FILES['csv_file']);exit;

            // create the media library attachment

            $id = media_handle_upload('csv_file', 0);

            if (!is_wp_error($id)) {

                // get the file path

                $file_path = get_attached_file($id);

                // var_dump($id);exit;

                // open the file

                $file = fopen($file_path, 'r');

                // read the file

                $lineNumber = 0;

                while (($line = fgetcsv($file, 0, ';', '"')) !== false) {

                    // skip the first line

                    if ($lineNumber > 0) {

                        // insert the data into the database

                        $wpdb->insert($table_name, array(

                            'Nom_commune' => $line[0],

                            'Code_postal' => str_pad(str_replace(' ', '', $line[1]), 5, '0', STR_PAD_LEFT),

                            // 'coordonnees_gps' => $line[5],

                            // 'code_commune_etrangere' => $line[6]

                        ));

                    }

                    $lineNumber++;

                }

                // close the file

                fclose($file);

                // delete the file

                unlink($file_path);

                // display a message

                echo '<div class="updated"><p>' . __('Le fichier CSV a été importé avec succès.', 'textdomain') . '</p></div>';

            }

        }

    }

}

//DISPLAY SETTINGS

function wpbc_contacts_page_handler_settings()
{

    //DETECT SENT

    $default = array(
        'page_modele_departement[]' => array(0),
        'page_modele_ville[]' => array(0),
    );
    $pageModeleDepartement = get_option('_partenaires_model_page_departement', $default['page_modele_departement[]']);
    $pageModeleVille = get_option('_partenaires_model_page_ville', $default['page_modele_ville[]']);

    // Récupérer les états des boutons checkboxes
    $selectedModels = get_option('_partenaires_selected_models', array());

  
    // Vérifier si $pageModeleDepartement n'est pas déjà un tableau
    if (!is_array($pageModeleDepartement)) {
        // Transformer la valeur en un tableau
        $pageModeleDepartement = array($pageModeleDepartement);
    }

    // Vérifier si $pageModeleVille n'est pas déjà un tableau
    if (!is_array($pageModeleVille)) {
        // Transformer la valeur en un tableau
        $pageModeleVille = array($pageModeleVille);
    }

    
    // if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
    //     $item = shortcode_atts($default, $_REQUEST);
    
    //     // Sanitize and update options
    //     $pageModeleDepartement = array_map('intval', $item['page_modele_departement[]']);
    //     $pageModeleVille = array_map('intval', $item['page_modele_ville[]']);

    //     // var_dump($item);
    //     var_dump($item);exit;
    
    //     update_option('_partenaires_model_page_departement', $pageModeleDepartement);
    //     update_option('_partenaires_model_page_ville', $pageModeleVille);
    
    //     $message = "Vos paramètres ont été mis à jour";
    // }

    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
    // Récupérer les valeurs du formulaire
    $pageModeleDepartement = isset($_POST['page_modele_departement']) ? array_map('intval', $_POST['page_modele_departement']) : array();
    $pageModeleVille = isset($_POST['page_modele_ville']) ? array_map('intval', $_POST['page_modele_ville']) : array();
    $selectedModels = isset($_POST['selected_models']) ? $_POST['selected_models'] : array();

    // Mettre à jour les options
    update_option('_partenaires_model_page_departement', $pageModeleDepartement);
    update_option('_partenaires_model_page_ville', $pageModeleVille);
    update_option('_partenaires_selected_models', $selectedModels);

    $message = "Vos paramètres ont été mis à jour";
}

    
    //BUTTON GENERATEvar_dump('ok');exit;

    ?>

	<div class="wrap">





    <h2><?php _e('Settings', 'wpbc')?></h2>

	<?php

    $lst = (get_pages(array('post_status' => 'private,draft', 'meta_query' => array('template_clause' => array('key' => 'meta_box_code_postal_text', 'value' => '', 'compare' => '='), 'relation' => 'OR', array('key' => 'meta_box_code_postal_text', 'compare' => 'NOT EXISTS')))));

    if (!empty($notice)): ?>

    <div id="notice" class="error"><p><?php echo $notice ?></p></div>

    <?php endif;?>

    <?php if (!empty($message)): ?>

    <div id="message" class="updated"><p><?php echo $message ?></p></div>

    <?php endif;?>



    <form id="form" method="POST" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=settingspartenaires'); ?>" enctype="multipart/form-data">
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>

    <div id="page-models-container">
        <div id="page-models" style="padding: 12px;">
            <input type="checkbox" id="select-all-models" class="select-all-models">
            <label for="select-all-models">Sélectionner tous les modèles</label>
        </div>
        <?php foreach ($pageModeleDepartement as $index => $defaultValue): ?>
            <div class="metabox-holder" data-index="<?php echo $index + 1; ?>">
                <div class="postbox">
                    <h2 class="hndle">
                        <span>
                            <input type="checkbox" name="selected_models[]" class="select-model" value="<?php echo $index; ?>" <?php echo in_array($index, $selectedModels) ? 'checked' : ''; ?>>
                            Page Modèle <?php echo $index + 1; ?>
                        </span>
                    </h2>
                    <div class="inside">
                        <p>
                            <label for="page_modele_departement_<?php echo $index + 1; ?>">Page modèle département</label>
                            <select name="page_modele_departement[]" class="page-model-select">
                                <option value="0" <?php selected($defaultValue, 0); ?>>Ne pas générer de page département</option>
                                <?php foreach ($lst as $page) : ?>
                                    <option value="<?php echo $page->ID; ?>" <?php selected($defaultValue, $page->ID); ?>><?php echo $page->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <label for="page_modele_ville_<?php echo $index + 1; ?>">Page modèle ville</label>
                            <select name="page_modele_ville[]" class="page-model-select">
                                <option value="0" <?php selected($pageModeleVille[$index], 0); ?>>Ne pas générer de page ville</option>
                                <?php foreach ($lst as $page) : ?>
                                    <option value="<?php echo $page->ID; ?>" <?php selected($pageModeleVille[$index], $page->ID); ?>><?php echo $page->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <?php if ($index > 0): ?>
                            <button type="button" class="button remove-model-button" style="float: right;">-</button>
                        <?php endif; ?>
                        &nbsp;&nbsp;
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button add-model-button" style="float: right; margin-right: 12px;">+</button>

    <input type="submit" value="<?php _e('Enregistrer', 'wpbc')?>" id="submit" class="button-primary" name="submit">
</form>






	<div>

	<p><strong>Pour les pages <u>villes</u> voici les parametres :</strong>

	<ul>

		<li>%zip_code%</li>

		<li>%code_departement%</li>

		<li>%city%</li>

		<li>%partenaire_email%</li>

		<li>%partenaire_tel%</li>

		<li>%partenaire_tel_second%</li>

		<li>%partenaire_nom%</li>

		<li>%partenaire_siret%</li>
        
        <li>%numero_model%</li>

	</ul>

	<br />

	<strong>Pour les pages <u>départements</u> voici les parametres :</strong>

	<ul>

		<li>%code_departement%</li>

		<li>%nom_departement%</li>

		<li>%partenaire_email%</li>

		<li>%partenaire_tel%</li>

		<li>%partenaire_tel_second%</li>

		<li>%partenaire_nom%</li>

		<li>%partenaire_siret%</li>

        <li>%numero_model%</li>

        

	</ul>

	</p>

	<div>

		<button class="button-secondary" onclick="generateCitiesPages()">Générer les pages villes</button>

		<div  id="infoVilles">

			<p style="display:none;">Batch <span class="number-bacth"></span> / <span class="total-bacth"></span></p>

			<input type="text"  id="resumtxt"  value="" placeholder="6" style="width:50px;text-align:right;" />

			<input type="button" value="Reprendre" id="resumbtn" onclick="generateCitiesPages(jQuery('#resumtxt').val())" />

		</div>

	</div>

	<button class="button-secondary" onclick="generateDepartmentsPages()">Générer les pages départements</button>

	<button class="button-secondary" onclick="generateDataBase()">Mettre à jour la base de données</button>

	<script>

	var actualBatch=1;

	function generateDataBase(){

		 dt={action: "generate_database"};

		 jQuery.ajax({

            type : "POST",

            dataType : "json",

            url : "<?php echo admin_url('admin-ajax.php'); ?>",

            data : dt,

			error: function(){

				alert('Error!');

			},

            success: function(response) {

				alert('done');

            }

        });

	}

	var JqoCity=false;

	var IntervalJqoCity=false;







	function generateCitiesPages(batch){

        if ($('.select-model:checked').length === 0) {
            // Aucun modele n'est sélectionné,, affichez un message d'alerte
            alert('Veuillez cocher au moins un modele pour générer les pages');
            return;
           
        }else {
                // Au moins un modèle est sélectionné, vérifiez les valeurs des sélecteurs
                var isValid = true;

                $('.select-model:checked').each(function() {
                    var index = $(this).val();

                    // Vérifiez les valeurs des sélecteurs en fonction du type de génération (ville ou département)
                    if ($('select[name="page_modele_ville[]"]:eq(' + index + ')').val() == 0) {
                        index++;
                        // Affichez un message d'alerte avec le numéro de modele
                        alert('Veuillez sélectionner une page modèle ville pour le modèle ' + (index) + '.');
                        isValid = false;
                    }
                });

                if (!isValid) {
                    return;
                }

            }

		 dt={action: "generate_cities_pages"};

		 if(batch){

			 dt['batch']=batch;

		 	jQuery('#resumtxt').val(batch);

		 }

		 jQuery('#infoVilles > p').show();

		 jQuery('#resumbtn').prop('disabled',true);

		 JqoCity=jQuery.ajax({

            type : "POST",

            dataType : "json",

            url : "<?php echo admin_url('admin-ajax.php'); ?>",

            data : dt,

			error: function(){
				console.log('Erreur AJAX on reprends :'+jQuery('#resumtxt').val());

				jQuery('#resumbtn').removeProp('disabled');

				generateCitiesPages(jQuery('#resumtxt').val());

			},



            success: function(response) {

				console.log('Valide AJAX '+response.batch);

				actualBatch=response.batch;

				jQuery('.number-bacth').html(response.batch);

				jQuery('.total-bacth').html(response.totalBatch);

				if(response.batch && response.totalBatch){

					if(response.batch<response.totalBatch){

						generateCitiesPages((response.batch+1));

					}else{

						jQuery('#infoVilles > p').hide();

						clearInterval(IntervalJqoCity);

						JqoCity=false;

						IntervalJqoCity=false;

						 alert("La génération est terminée");



					}

				}

            }

        });





		if(IntervalJqoCity===false){

			IntervalJqoCity=setInterval(function(){

				if(IntervalJqoCity===false){

					clearInterval(IntervalJqoCity);

				}else{

					console.log('checking XHR Internval');

					if(JqoCity.readyState>1){

						console.log('Error on ajax :'+JqoCity.readyState);

						console.log('Resuming at '+jQuery('#resumtxt').val());

						generateCitiesPages(jQuery('#resumtxt').val());

					}else{

						console.log('==>still ongoing');

					}

				}

			},60000);

		}



		 if(!batch) alert("La génération est en cours et peu prendre plusieurs minutes, merci de bien vouloir patienter");

	}

	function generateDepartmentsPages(){

        if ($('.select-model:checked').length === 0) {
            // Aucun modele n'est sélectionné,, affichez un message d'alerte
            alert('Veuillez cocher au moins un modele pour générer les pages');
            return;
           
        }else {
                // Au moins un modèle est sélectionné, vérifiez les valeurs des sélecteurs
                var isValid = true;

                $('.select-model:checked').each(function() {
                    var index = $(this).val();

                    // Vérifiez les valeurs des sélecteurs en fonction du type de génération (ville ou département)
                    if ($('select[name="page_modele_departement[]"]:eq(' + index + ')').val() == 0) {
                        index++;
                        // Affichez un message d'alerte avec le numéro de modele
                        alert('Veuillez sélectionner une page modèle département pour le modèle ' + (index) + '.');
                        isValid = false;
                    }
                });

                if (!isValid) {
                    return;
                }

            }

		alert("La génération est en cours et peu prendre plusieurs minutes, merci de bien vouloir patienter");

		 jQuery.ajax({

            type : "POST",

            dataType : "json",

            url : "<?php echo admin_url('admin-ajax.php'); ?>",

            data : {action: "generate_departments_pages"},

            success: function(response) {

                // alert("Your vote could not be added");

            }

        });

	}

	</script>

	</div>

	<?php

}

register_deactivation_hook(__FILE__, 'partenaire_options_deactivate');

function partenaire_options_uninstall()
{

    global $wpdb;

    // drop villes table if it exists

    $villes_table_name = $wpdb->prefix . "villes_france";

    $wpdb->query("DROP TABLE IF EXISTS {$villes_table_name}");

    // drop partenaire table if exists

    $partenaire_table_name = $wpdb->prefix . "partenaire";

    $wpdb->query("DROP TABLE IF EXISTS {$partenaire_table_name}");

}

register_uninstall_hook(__FILE__, 'partenaire_options_uninstall');

add_action('wp_ajax_generate_database', '_generate_database');

add_action('wp_ajax_generate_cities_pages', '_generate_cities_pages');

add_action('wp_ajax_generate_departments_pages', '_generate_departments_pages');

function _generate_database()
{

    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $wpdb->query("UPDATE `" . $wpdb->prefix . "_department_france` SET nom_department='Territoire de Belfort' WHERE TRIM(nom_department)='Territoire'");

    $wpdb->query("UPDATE `" . $wpdb->prefix . "_department_france` SET nom_department='La Réunion' WHERE TRIM(nom_department)='La'");

    $villes_table_name = $wpdb->prefix . "villes_france";

    $wpdb->query("DROP TABLE IF EXISTS {$villes_table_name}");

    $sql = "CREATE TABLE $villes_table_name (

        id int(11) NOT NULL AUTO_INCREMENT,

        Nom_commune varchar(255) NOT NULL,

        Code_postal varchar(255)  NULL,

        code_insee varchar(120)  NULL,

        PRIMARY KEY  (id)

    ) $charset_collate;";

    // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta($sql);

    $csv_file = plugin_dir_path(__FILE__) . 'public/files/villes_france.csv';

    // vérifier si le fichier existe

    if (file_exists($csv_file)) {

        $csv_file = fopen($csv_file, 'r');

        $lineNumber = 0;

        while (($csv_data = fgetcsv($csv_file, 0, ';', '"')) !== false) {

            // skip the first line

            if ($lineNumber > 0) {

                $csv_data[1] = str_replace(' ', '', $csv_data[1]);

                if (strpos($csv_data[1], '-') !== false) {
                    $csv_data[1] = substr($csv_data[1], 0, strpos($csv_data[1], '-'));
                }

                $wpdb->insert(

                    $villes_table_name,

                    array(

                        'Nom_commune' => $csv_data[0],

                        'Code_postal' => str_pad(str_replace(' ', '', $csv_data[1]), 5, '0', STR_PAD_LEFT),

                        'code_insee' => str_replace(' ', '', $csv_data[2]),

                    )

                );

            }

            $lineNumber++;

        }

        fclose($csv_file);

    }

}
function enqueue_custom_scripts() {
    // Enregistrez le fichier JavaScript
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . '/js/script.js', array('jquery'), '1.0', true);
    
}
add_action('admin_enqueue_scripts', 'enqueue_custom_scripts');
function _generate_cities_pages() {

    global $wpdb;

    set_time_limit(0);

    _pp_deleteAllCache();

    // Récupérer les valeurs de _partenaires_model_page_ville
    $defaultValues = get_option('_partenaires_model_page_ville', array());
  
    if (empty($defaultValues)) {
        echo "Les pages par défaut ne sont pas définies. La génération ne peut pas avoir lieu";
        wp_die();
    }

    $perBatch = 1;
    $batch = (isset($_POST['batch'])) ? (int) $_POST['batch'] : 1;

    // Récupérer les modèles sélectionnés depuis les options
    $selectedModels = get_option('_partenaires_selected_models', array());

    // Vérifier si des modèles sont sélectionnés
    if (!empty($selectedModels)) {

        // Générer des pages pour chaque modèle sélectionné
        foreach ($selectedModels as $selectedModelIndex) {

            // Vérifier si l'index du modèle est valide
            if (!isset($defaultValues[$selectedModelIndex])) {
                continue;
            }

            $defaultValueID = $defaultValues[$selectedModelIndex];

            // Récupérer le modèle de page par défaut correspondant
            $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "posts WHERE ID = '$defaultValueID'", ARRAY_A);

            // Vérifier si le modèle de page par défaut existe
            if (empty($row)) {
                continue;
            }

            $cities = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS v.code_insee,v.Code_postal, v.Nom_commune,c.partenaire_id FROM " . $wpdb->prefix . "villes_france v INNER JOIN " . $wpdb->prefix . "code_postal c ON(c.code_postal=v.code_insee) WHERE 1 group by code_insee ORDER BY code_insee DESC LIMIT " . (($batch - 1) * $perBatch) . "," . $perBatch, ARRAY_A);

            $nb = $wpdb->get_results("SELECT FOUND_ROWS() AS nombre_article;", ARRAY_A);

            $totalBatch = ceil($nb[0]['nombre_article'] / $perBatch);

            $cityNb = 0;

            if (!empty($cities)) {
                foreach ($cities as $city) {
                    $city['code_insee'] = explode('-', $city['code_insee']);

                    foreach ($city['code_insee'] as $cp) {
                        $partner = $wpdb->get_row("SELECT p.* FROM " . $wpdb->prefix . "partenaire p WHERE p.id='" . $city['partenaire_id'] . "'", ARRAY_A);

                        if (is_null($partner) || (is_array($partner) && sizeof($partner) == 0)) {
                            $partner = false;
                        }

                        if ($partner !== false) {
                            $params = array(
                                'zip_code' => $city['Code_postal'],
                                'code_insee' => $cp,
                                'code_departement' => substr($cp, 0, strlen($cp) - 3),
                                'city' => $city['Nom_commune'],
                                'partenaire_email' => $partner['email'],
                                'partenaire_tel' => $partner['phone'],
                                'partenaire_tel_second' => $partner['phone_second'],
                                'partenaire_nom' => $partner['name'],
                                'partenaire_siret' => $partner['siret'],
                                'numero_model' => $defaultValueID,
                            );

                            $my_post = array(
                                'post_title' => _parseWithParams($row['post_title'], $params),
                                'post_content' => _parseWithParams($row['post_content'], $params),
                                'post_status' => 'publish',
                                'post_author' => $row['post_author'],
                                'post_category' => wp_get_post_categories($defaultValueID, array('fields' => 'ids')),
                                'post_type' => 'page',
                            );

                            $meta_key = 'meta_box_code_postal_text_' . $defaultValueID;
                            $post = $wpdb->get_row($wpdb->prepare(
                                'SELECT post_id, meta_key FROM ' . $wpdb->prefix . 'postmeta WHERE meta_value = %s AND meta_key LIKE %s',
                                $cp,
                                '%meta_box_code_postal_text%'
                            ));

                            if (is_null($post)) {
                                $idP = wp_insert_post($my_post);
                            } else {
                                // Vérifiez si le meta_key a déjà le préfixe
                                if (strpos($post->meta_key, $meta_key) === false) {
                                    // Mettez à jour le meta_key avec le préfixe
                                    $wpdb->update(
                                        $wpdb->prefix . 'postmeta',
                                        array('meta_key' => $meta_key),
                                        array('post_id' => $post->post_id, 'meta_key' => $post->meta_key)
                                    );
                                }

                                $idP = $my_post['ID'] = $post->post_id;
                                wp_update_post($my_post);
                            }

                            $lstMeta = get_post_meta($defaultValueID);

                            if (is_array($lstMeta) && sizeof($lstMeta) > 0) {
                                foreach ($lstMeta as $metaKey => $metaVal) {
                                    update_post_meta($idP, $metaKey, _parseWithParams($metaVal[0], $params));
                                }
                            }

                            $lstMeta = get_post_meta($defaultValueID);

                            if (is_array($lstMeta) && sizeof($lstMeta) > 0) {
                                foreach ($lstMeta as $metaKey => $metaVal) {
                                    // Ajoutez le préfixe du $defaultValueID au meta_key
                                    $meta_key = $metaKey . '_' . $defaultValueID;
                                    update_post_meta($idP, $meta_key, _parseWithParams($metaVal[0], $params));
                                }
                            }

                            // Ajoutez le préfixe du $defaultValueID au meta_key 'meta_box_code_postal_text'
                            $meta_key_code_postal = 'meta_box_code_postal_text_' . $defaultValueID;
                            update_post_meta($idP, $meta_key_code_postal, $cp);
                        }
                    }

                    $cityNb++;
                }
            }
        }

        $t = new \stdClass();
        $t->batch = $batch;
        $t->totalBatch = $totalBatch;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($t);
        wp_die();
    } else {
        // Aucun modèle sélectionné, affichez un message d'erreur ou redirigez l'utilisateur
        echo "Aucun modèle sélectionné pour la génération.";
        wp_die();
    }
}

function _parseWithParams($r, $params)
{

    if (is_string($r)) {

        foreach ($params as $key => $val) {

            $r = str_replace('%' . $key . '%', $val, $r);

        }

    }

    return $r;

}

function _generate_departments_pages()
{
    global $wpdb;
    set_time_limit(0);
    _pp_deleteAllCache();

    // Récupérer les valeurs de _partenaires_model_page_departement
    $defaultValues = get_option('_partenaires_model_page_departement', array());

    if (empty($defaultValues)) {
        echo "Les pages par défaut ne sont pas définies. La génération ne peut pas avoir lieu";
        wp_die();
    }

    $perBatch = 1;
    $batch = (isset($_POST['batch'])) ? (int)$_POST['batch'] : 1;

    // Récupérer les modèles sélectionnés depuis les options
    $selectedModels = get_option('_partenaires_selected_models', array());

    // Vérifier si des modèles sont sélectionnés
    if (!empty($selectedModels)) {

        // Générer des pages pour chaque modèle sélectionné
        foreach ($selectedModels as $selectedModelIndex) {

            // Vérifier si l'index du modèle est valide
            if (!isset($defaultValues[$selectedModelIndex])) {
                continue;
            }

            $defaultValueID = $defaultValues[$selectedModelIndex];

            // Récupérer le modèle de page par défaut correspondant
            $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "posts WHERE ID = '$defaultValueID'", ARRAY_A);

            // Vérifier si le modèle de page par défaut existe
            if (empty($row)) {
                continue;
            }

            $departments = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "department_france WHERE 1", ARRAY_A);

            $totalBatch = count($departments);

            $departmentNb = 0;

            // Générer des pages pour chaque département
            foreach ($departments as $department) {
                $partner = $wpdb->get_row("SELECT p.* FROM " . $wpdb->prefix . "code_postal c INNER JOIN " . $wpdb->prefix . "partenaire p ON (c.partenaire_id=p.id) WHERE c.code_postal='" . str_pad($department['code_department'], 2, '0', STR_PAD_LEFT) . "' AND LENGTH(c.code_postal)<4", ARRAY_A);

                if (is_null($partner) || (is_array($partner) && sizeof($partner) == 0)) {
                    $partner = false;
                }

                if ($partner !== false) {
                    $params = array(
                        'code_departement' => str_pad(str_replace(' ', '', $department['code_department']), 2, '0', STR_PAD_LEFT),
                        'nom_departement' => $department['nom_department'],
                        'partenaire_email' => $partner['email'],
                        'partenaire_tel' => $partner['phone'],
                        'partenaire_tel_second' => $partner['phone_second'],
                        'partenaire_nom' => $partner['name'],
                        'partenaire_siret' => $partner['siret'],
                        'numero_model' => $defaultValueID,
                    );

                    $my_post = array(
                        'post_title' => _parseWithParams($row['post_title'], $params),
                        'post_content' => _parseWithParams($row['post_content'], $params),
                        'post_status' => 'publish',
                        'post_author' => $row['post_author'],
                        'post_category' => wp_get_post_categories($defaultValueID, array('fields' => 'ids')),
                        'post_type' => 'page',
                    );

                    $meta_key = 'meta_box_code_departement_text_' . $defaultValueID;
                    $post = $wpdb->get_row($wpdb->prepare(
                        'SELECT post_id, meta_key FROM ' . $wpdb->prefix . 'postmeta WHERE meta_value = %s AND meta_key LIKE %s',
                        $department['code_department'],
                        '%meta_box_code_departement_text%'
                    ));

                    if (is_null($post)) {
                        $idP = wp_insert_post($my_post);
                    } else {
                        // Vérifiez si le meta_key a déjà le préfixe
                        if (strpos($post->meta_key, $meta_key) === false) {
                            // Mettez à jour le meta_key avec le préfixe
                            $wpdb->update(
                                $wpdb->prefix . 'postmeta',
                                array('meta_key' => $meta_key),
                                array('post_id' => $post->post_id, 'meta_key' => $post->meta_key)
                            );
                        }

                        $idP = $my_post['ID'] = $post->post_id;
                        wp_update_post($my_post);
                    }

                    $lstMeta = get_post_meta($defaultValueID);

                    foreach ($lstMeta as $metaKey => $metaVal) {
                        update_post_meta($idP, $metaKey, _parseWithParams($metaVal[0], $params));
                    }

                    update_post_meta($idP, $meta_key, $department['code_department']);
                }

                $departmentNb++;
            }
        }

        $t = new \stdClass();
        $t->batch = $batch;
        $t->totalBatch = $totalBatch;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($t);
        wp_die();
    } else {
        // Aucun modèle sélectionné, affichez un message d'erreur ou redirigez l'utilisateur
        echo "Aucun modèle sélectionné pour la génération.";
        wp_die();
    }
}



add_shortcode('liste_villes', '_shortcode_liste_villes');

add_shortcode('liste_departements', '_shortcode_liste_departements');

function _shortcode_liste_villes($args, $content, $name)
{

    global $wpdb;

    $res = '';

    $departement = (isset($args['departement'])) ? $args['departement'] : ((isset($args['dep'])) ? $args['dep'] : ((isset($args['dept'])) ? $args['dept'] : false));

    $turnIntoList = (isset($args['makelist']));

    if (!is_array($args)) {
        $args = [];
    }

    $fName = 'cities_' . md5(implode(',', array_keys($args)) . implode(',', array_values($args)));

    if ($turnIntoList) {
        addPluginPartenaireStaticRessources();
    }

    // var_dump($fName);exit;

    if (_pp_isCacheExists($fName)) {

        return _pp_getCache($fName);

    } else {

        $listExtraClass = (isset($args['listclass'])) ? $args['listclass'] : '';

        $content = (trim($content) == '') ? '<p><a href="%pageville_lien%" alt="Voir %ville_nom%"><strong>%ville_nom%</strong></a></p>' : $content;

        //liste les villes

        if ($departement === false) {

            $postList = $wpdb->get_results('SELECT p.post_id,v.* FROM ' . $wpdb->prefix . 'postmeta p,' . $wpdb->prefix . 'villes_france v where p.meta_value=v.code_insee and p.meta_key=\'meta_box_code_postal_text\' group by code_insee ');

        } else {

            $postList = $wpdb->get_results('SELECT p.post_id,v.* FROM ' . $wpdb->prefix . 'postmeta p,' . $wpdb->prefix . 'villes_france v where v.Code_postal LIKE "' . $departement . '%" AND p.meta_value=v.code_insee  and p.meta_key=\'meta_box_code_postal_text\' group by code_insee ');

        }

        foreach ($postList as $row) {

            $page = get_post($row->post_id);

            $partner = $wpdb->get_row("SELECT p.* FROM " . $wpdb->prefix . "code_postal c INNER JOIN " . $wpdb->prefix . "partenaire p ON (c.partenaire_id=p.id) WHERE c.Code_postal='" . $row->code_insee . "'", ARRAY_A);

            if (is_null($partner) || (is_array($partner) && sizeof($partner) == 0)) {
                $partner = false;
            }

            // if($partner===false){

            // $partner= $wpdb->get_results("SELECT p.* FROM ".$wpdb->prefix . "code_postal c INNER JOIN ".$wpdb->prefix . "partenaire p ON (c.partenaire_id=p.id) WHERE c.code_postal=LEFT('".$row->Code_postal."',LENGTH(c.code_postal)) AND LENGTH(c.code_postal)<4", ARRAY_A);

            // if(is_null($partner) || (is_array($partner) && sizeof($partner)==0)) $partner=false;

            // }

            if ($partner !== false) {

                $params = array(

                    'ville_codepostal' => $row->Code_postal,

                    'ville_nom' => $row->Nom_commune,

                    'pageville_titre' => $page->post_title,

                    'pageville_lien' => get_page_link($page),

                    'partenaireville_email' => $partner['email'],

                    'partenaireville_tel' => $partner['phone'],

                    'partenaireville_tel_second' => $partner['phone_second'],

                    'partenaireville_nom' => $partner['name'],

                    'partenaireville_siret' => $partner['siret'],

                );

                if ($res == '' && $turnIntoList) {

                    $res = '<ul class="partenaires-list partenaires-list-villes ' . $listExtraClass . '">';

                }

                $res .= ($turnIntoList ? '<li>' : '') . _parseWithParams($content, $params) . ($turnIntoList ? '</li>' : '');

            }

        }

        if ($res != '' && $turnIntoList) {
            $res .= '</ul>';
        }

        _pp_setCache($fName, $res);

    }

    return $res;

}

function _pp_getCacheDirName()
{

    $dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

    if (!is_dir($dir)) {
        mkdir($dir);
    }

    return $dir;

}

function _pp_getCacheFileName($name)
{

    return _pp_getCacheDirName() . md5($name) . '.php';

}

function _pp_setCache($name, $value)
{

    // var_dump(_pp_getCacheFileName($name));

    // var_dump($value);

    return file_put_contents(_pp_getCacheFileName($name), $value);

}

function _pp_getCache($name)
{

    return file_get_contents(_pp_getCacheFileName($name));

}

function _pp_deleteAllCache()
{

    $files = glob(_pp_getCacheDirName() . '*.php'); // get all file names

    foreach ($files as $file) { // iterate files

        if (is_file($file)) {

            unlink($file); // delete file

        }

    }

}

function _pp_isCacheExists($name)
{

    return file_exists(_pp_getCacheFileName($name));

}

function _shortcode_liste_departements($args, $content, $name)
{

    global $wpdb;

    $turnIntoList = (isset($args['makelist']));

    if ($turnIntoList) {
        addPluginPartenaireStaticRessources();
    }

    $listExtraClass = (isset($args['listclass'])) ? $args['listclass'] : '';

    $content = (trim($content) == '') ? '<p><a href="%pagedepartement_lien%" alt="Voir %departement_nom%"><strong>%departement_nom%</strong></a></p>' : $content;

    //liste les villes

    if (!is_array($args)) {
        $args = [];
    }

    $fName = 'department_' . md5(implode(',', array_keys($args)) . implode(',', array_values($args)));

    if (_pp_isCacheExists($fName)) {

        return _pp_getCache($fName);

    } else {

        $postList = $wpdb->get_results('SELECT p.post_id,v.* FROM ' . $wpdb->prefix . 'postmeta p,' . $wpdb->prefix . 'department_france v where v.code_department=p.meta_value and p.meta_key=\'meta_box_text_departement\'');

        $res = '';

        foreach ($postList as $row) {

            $page = get_post($row->post_id);

            $partner = $wpdb->get_row("SELECT p.* FROM " . $wpdb->prefix . "code_postal c INNER JOIN " . $wpdb->prefix . "partenaire p ON (c.partenaire_id=p.id) WHERE c.code_postal='" . $row->code_department . "' AND LENGTH(c.code_postal)<4", ARRAY_A);

            if (is_null($partner) || (is_array($partner) && sizeof($partner) == 0)) {
                $partner = false;
            }

            if ($partner !== false) {

                $params = array(

                    'departement_code' => $row->code_department,

                    'departement_nom' => $row->nom_department,

                    'pagedepartement_titre' => $page->post_title,

                    'pagedepartement_lien' => get_page_link($page),

                    'partenairedepartement_email' => $partner['email'],

                    'partenairedepartement_tel' => $partner['phone'],

                    'partenairedepartement_tel_second' => $partner['phone_second'],

                    'partenairedepartement_nom' => $partner['name'],

                    'partenairedepartement_siret' => $partner['siret'],

                );

                if ($res == '' && $turnIntoList) {

                    $res = '<ul class="partenaires-list partenaires-list-departements ' . $listExtraClass . '">';

                }

                $res .= ($turnIntoList ? '<li>' : '') . do_shortcode(_parseWithParams($content, $params)) . ($turnIntoList ? '</li>' : '');

            }

        }

        if ($res != '' && $turnIntoList) {
            $res .= '</ul>';
        }

        _pp_setCache($fName, $res);

    }

    return $res;

}

function addPluginPartenaireStaticRessources()
{

    static $added = false;

    if (!$added) {

        wp_enqueue_style('pluginpartenaire', '/wp-content/plugins/partenaire/css/styles.css', false, '1.1', 'all');

        wp_enqueue_script('pluginpartenaire', '/wp-content/plugins/partenaire/js/script.js', array('jquery'), 1.1, true);

        $added = true;

    }

}