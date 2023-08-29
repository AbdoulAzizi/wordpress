<?php

add_action( 'admin_menu', 'ville_options_page');

function ville_options_page() {

	add_options_page(
		'Configuration Pages modèles',
		'Configuration Pages modèles',
		'manage_options',
		'ville_options',
		'display_modele_options_page'
	);

}

function display_modele_options_page() {

	echo '<form method="post" action="options.php">';

	do_settings_sections( 'ville_options' );
	settings_fields( 'modeles_pages_settings' );

	submit_button('Sauvegarder');

	echo '</form>';

	// Button régénérer les pages
	echo '<form method="post" action="">';
	submit_button('Regérénerer les pages villes');
	echo '</form>';

}

add_action( 'admin_init', 'ville_settings_init' );
function ville_settings_init() {

	add_settings_section(
		'ville_settings_section',      
		'Génération des modèles pages ',         
		'',  
		'ville_options'               
	);

	add_settings_field(
		'page_ville_select',        
		'Choisir une page ville modèle',        
		'page_ville_select_callback',  
		'ville_options',        
		'ville_settings_section' 
	);

	register_setting(
		'modeles_pages_settings',    
		'page_ville_select'    
	);

}

function page_ville_select_callback(){

   ?>
   <p>
        <select name="page_ville_select">
          <option value="page1" <?php selected(get_option('page_ville_select'), "page1"); ?>>Page 1</option>
          <option value="page2" <?php selected(get_option('page_ville_select'), "page2"); ?>>Page 2</option>
        </select>
    </p>
   <?php

}

add_action( 'admin_init', 'departement_options_page');
function departement_options_page() {

	add_settings_section(
		'departement_settings_section',
		'Modèle de page département',
		'',
		'ville_options'
	);

	add_settings_field(
		'page_departement_select',
		'Choisir une page département modèle',
		'page_departement_select_callback',
		'ville_options',
		'departement_settings_section'
	);

	register_setting(
		'modeles_pages_settings',
		'page_departement_select'
	);

}


function page_departement_select_callback() {

	?>
	<p>
		<select name="page_departement_select">
			<option value="page1" <?php selected(get_option('page_departement_select'), "page1"); ?>>Département 1</option>
			<option value="page2" <?php selected(get_option('page_departement_select'), "page2"); ?>>Département 2</option>
		</select>
	</p>
	<?php

}