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

    global $wpdb;
    $table_name = $wpdb->prefix . 'partenaire';

    

    // if (isset($_POST['submitPartenaire'])) {
    //     $nom = $_POST['partenaire_nom'];
    //     $numero_telephone = $_POST['numero_telephone'];
    //     $email = $_POST['email'];
    //     $siret = $_POST['siret'];
    //     // $code_postal = $_POST['code_postal'];
    //     $wpdb->insert(
    //         $table_name,
    //         array(
    //             'partenaire_nom' => $nom,
    //             'numero_telephone' => $numero_telephone,
    //             'email' => $email,
    //             'siret' => $siret,
    //             // 'code_postal' => $code_postal
    //         )
    //     );
    // }
}