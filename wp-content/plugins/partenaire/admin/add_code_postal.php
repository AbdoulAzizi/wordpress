<?php

function add_code_postal() {
    global $wpdb;
  
    $code_postal_table = $wpdb->prefix . 'code_postal';

    $ville_table = $wpdb->prefix . 'villes_france';

    // $code_postal = array();
    $code_postal = $_POST['code_postal'];
    // var_dump(count($code_postal));exit;

    $partenaire_id = $_POST['partenaire_id'];
    // var_dump($partenaire_id);exit;


    if (isset($_POST['submitCodePostal'])) {

            $code_postal_comma = array();
            $code_postal_space = array();
            $code_postal_dash = array();
            $code_postal_one_value = '';
            $code_postal_two_characters = '';

            if(strlen($code_postal) == 2) {
                // verify if code postal has oly two characters
                
                    $code_postal_two_characters = $code_postal;
					
            }
            // verify if code postal is separated by a comma
            elseif (strpos($code_postal, ',') !== false) {
                $code_postal_comma = explode(',', $code_postal);
            }
            elseif(strpos($code_postal, ' ') !== false) {

            // verify if code postal is separated by a space
                $code_postal_space = explode(' ', $code_postal);
            }
            elseif(strpos($code_postal, '-') !== false) {

            // verify if code postal is separated by a dash
            
                
                // take the first part of the code postal
                $code_postal_separate_dash = explode('-', $code_postal);
                $code_postal_dash_first = $code_postal_separate_dash[0];

                // take the second part of the code postal
                $code_postal_dash_second = $code_postal_separate_dash[1];

                // take the difference between the first and the second part of the code postal
                $code_postal_dash_difference = $code_postal_dash_second - $code_postal_dash_first;

                // take all the numbers bettwen the first and the second part of the code postal
                for ($i=0; $i <= $code_postal_dash_difference; $i++) { 
                    $code_postal_dash[] = $code_postal_dash_first + $i;
                }

            }
            elseif(count($code_postal) == 1) {

                // verify if code postal has only one value
                
                    $code_postal_one_value = $code_postal;
                    
            }
           
            // var_dump($code_postal_two_characters);exit;
            // insert code postal in code_postal table according to the type of code postal

            if (isset($code_postal_two_characters) && !empty($code_postal_two_characters)) {
                // retreive all the code postal witch have the same first and second characters
                $all_code_postal = $wpdb->get_results("SELECT Code_postal FROM $ville_table WHERE Code_postal LIKE '$code_postal_two_characters%'");
                // var_dump($all_code_postal);exit;
                // insert all the code postal in the table
                foreach ($all_code_postal as $key => $value) {
                    $wpdb->insert($code_postal_table, array(
                        'code_postal' => $value->Code_postal,
                        'partenaire_id' => $partenaire_id
                    ));
                }
                

            }
            if (isset($code_postal_comma)) {
                foreach ($code_postal_comma as $code_postal_comma) {
                    $wpdb->insert($code_postal_table, array(
                        'code_postal' => $code_postal_comma,
                        'partenaire_id' => $partenaire_id
                    ));
                }
            }
            if (isset($code_postal_space)) {
                foreach ($code_postal_space as $code_postal_space) {
                    $wpdb->insert($code_postal_table, array(
                        'code_postal' => $code_postal_space,
                        'partenaire_id' => $partenaire_id
                    ));
                }
            }
            if (isset($code_postal_dash)) {
                foreach ($code_postal_dash as $code_postal_dash) {
                    $wpdb->insert($code_postal_table, array(
                        'code_postal' => $code_postal_dash,
                        'partenaire_id' => $partenaire_id
                    ));
                }
            }
            if (isset($code_postal_one_value) && !empty($code_postal_one_value)) {
                $wpdb->insert($code_postal_table, array(
                    'code_postal' => $code_postal_one_value,
                    'partenaire_id' => $partenaire_id
                ));
            }

           
    
    }
}