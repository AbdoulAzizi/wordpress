<?php

// function list_partenaire(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'partenaire';
    $partenaires = $wpdb->get_results("SELECT * FROM $table_name");
   
    if(!empty($partenaires)){

        echo '<div class="container">';
        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<h1>Liste des partenaires</h1>';
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Nom</th>';
        echo '<th scope="col">Numéro de téléphone</th>';
        echo '<th scope="col">Email</th>';
        echo '<th scope="col">Siret</th>';
        echo '<th scope="col">Code postal</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($partenaires as $partenaire) {
            echo '<tr>';
            echo '<td>' . $partenaire->partenaire_nom . '</td>';
            echo '<td>' . $partenaire->numero_telephone . '</td>';
            echo '<td>' . $partenaire->email . '</td>';
            echo '<td>' . $partenaire->siret . '</td>';
            echo '<td>' . $partenaire->code_postal . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    else{
        echo '<tr>';
        echo '<td colspan="5"><h3>Aucun partenaire n\'a été ajouté</h3></td>';
        echo '</tr>';
    }
// }
