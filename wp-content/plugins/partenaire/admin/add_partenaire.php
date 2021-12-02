<?php

function add_partenaire() {
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

function insert_partenaire(){

    var_dump($_POST);exit;

    global $wpdb;
    $table_name = $wpdb->prefix . 'partenaire';

    $code_postal_table = $wpdb->prefix . 'code_postal';

    $code_postal = array();
    $code_postal = $_POST['code_postal'];

    $code_postal_comma = array();
    $code_postal_space = array();
    $code_postal_dash = array();
    $code_postal_on_value = array();
    $code_postal_two_characters = array();

    // verify if code postal is separated by a comma
    if (strpos($code_postal, ',') !== false) {
        $code_postal_comma = explode(',', $code_postal);
    }

    // verify if code postal is separated by a space
    if (strpos($code_postal, ' ') !== false) {
        $code_postal_space = explode(' ', $code_postal);
    }
    
    // verify if code postal is separated by a dash
    if (strpos($code_postal, '-') !== false) {
        
        // take the first part of the code postal
        $code_postal_dash = explode('-', $code_postal);
        $code_postal_dash_first = $code_postal_dash[0];

        // take the second part of the code postal
        $code_postal_dash_second = $code_postal_dash[1];

        // take all th numbers bettwen the first and the second part of the code postal
        $code_postal_dash_numbers = array();

        // take the difference between the first and the second part of the code postal
        $code_postal_dash_difference = $code_postal_dash_second - $code_postal_dash_first;

        // take all the numbers bettwen the first and the second part of the code postal
        for ($i=0; $i <= $code_postal_dash_difference; $i++) { 
            $code_postal_dash[] = $code_postal_dash_first + $i;
        }

    }
 

    // verify if code postal has only one value
    if (count($code_postal) == 1) {
        $code_postal_on_value = $code_postal[0];
    }
    // verify if code postal has oly two characters
    if (strlen($code_postal) == 2) {
        $code_postal_two_characters = $code_postal[0] . $code_postal[1];
    }
    
    // insert code postal in code_postal table according to the type of code postal

    if (isset($code_postal_comma)) {
        foreach ($code_postal_comma as $code_postal_comma) {
            $wpdb->insert($code_postal_table, array(
                'code_postal' => $code_postal_comma,
                'partenaire_id' => $_POST['partenaire_id']
            ));
        }
    }

    if (isset($code_postal_space)) {
        foreach ($code_postal_space as $code_postal_space) {
            $wpdb->insert($code_postal_table, array(
                'code_postal' => $code_postal_space,
                'partenaire_id' => $_POST['partenaire_id']
            ));
        }
    }
    
    if (isset($code_postal_dash)) {
        foreach ($code_postal_dash as $code_postal_dash) {
            $wpdb->insert($code_postal_table, array(
                'code_postal' => $code_postal_dash,
                'partenaire_id' => $_POST['partenaire_id']
            ));
        }
    }

    if (isset($code_postal_on_value)) {
        $wpdb->insert($code_postal_table, array(
            'code_postal' => $code_postal_on_value,
            'partenaire_id' => $_POST['partenaire_id']
        ));
    }

    if (isset($code_postal_two_characters)) {
        $wpdb->insert($code_postal_table, array(
            'code_postal' => $code_postal_two_characters,
            'partenaire_id' => $_POST['partenaire_id']
        ));
    }

    if (isset($_POST['submitPartenaire'])) {
        $nom = $_POST['partenaire_nom'];
        $numero_telephone = $_POST['numero_telephone'];
        $email = $_POST['email'];
        $siret = $_POST['siret'];
        // $code_postal = $_POST['code_postal'];
        $wpdb->insert(
            $table_name,
            array(
                'partenaire_nom' => $nom,
                'numero_telephone' => $numero_telephone,
                'email' => $email,
                'siret' => $siret,
                // 'code_postal' => $code_postal
            )
        );
    }
}