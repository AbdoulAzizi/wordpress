<?php

function wpbc_contacts_page_handler_cp()
{
    global $wpdb;
    $table = new Custom_Table_Example_List_Table_CP();
    $table->prepare_items();

    // var_dump( $_REQUEST);exit;

    $partenaire = $wpdb->get_results( "SELECT name FROM wp_partenaire WHERE id = '".$_REQUEST['id_partenaire']."'");
    // var_dump($partenaire[0]->name);
    $partenaire_name = $partenaire[0]->name;
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Nombre d\'enregistrement (s) supprimé (s): %d', 'wpbc'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Liste des codes postaux assignés au partenaire: '.$partenaire_name, 'wpbc')?> 
    </h2>
    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>"><?php _e('Voir la liste des partenaires', 'wpbc')?></a>
    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_cp&id_partenaire='.$_REQUEST['id_partenaire'])?>"><?php _e('Assigner Code postal', 'wpbc')?></a>
    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="partenaire_id" value="<?php echo $_REQUEST['id'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php

}


function wpbc_contacts_form_page_handler_cp()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'code_postal'; 

    $message = '';
    $notice = '';
    $partenaire_id = $_REQUEST['id_partenaiire'];
    // var_dump($partenaire_id);

    $default = array(
        'id' => 0,
        'code_postal'     => '',
        'partenaire_id' => $partenaire_id,
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = wpbc_validate_contact_cp($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                // $result = $wpdb->insert($table_name, $item);
                // $item['id'] = $wpdb->insert_id;

                // var_dump($_REQUEST); exit;

                // vérifier si l'un des button radio est coché
                if(! isset($_REQUEST['type_assignation'])){
                    $notice_type_assignation = __('Veuillez choisir le type d\'assignation', 'wpbc');
                }else{
                    $type_assignation = $_REQUEST['type_assignation'];

                    // veriifer le type d'assignation du code postal selon le button radio choisi
                    if ($type_assignation == 'default') {
                        // echo '<script>alert("Vous avez choisi l\'assignation par défaut");</script>';
                        $result = $wpdb->insert($table_name, $item);
                        $item['id'] = $wpdb->insert_id;
                    }elseif ($type_assignation == 'cp_virgule') {
                        $cp_virgule = explode(',', $_REQUEST['code_postal']);
                        $rows = array();
                        foreach ($cp_virgule as $key => $value) {
                            $item['code_postal'] = $value;
                            $rows[] = $item;
                        }
                        $result = wp_insert_rows($rows, $table_name);
                    }elseif ($type_assignation == 'cp_tranche') {
                        $cp_tranche = explode('-', $_REQUEST['code_postal']);
                        $cp_tranche_debut = $cp_tranche[0];
                        $cp_tranche_fin = $cp_tranche[1];
                        $rows = array();

                        for ($i=$cp_tranche_debut; $i <= $cp_tranche_fin; $i++) { 
                            $item['code_postal'] = $i;
                            $rows[] = $item;
                        }
                        $result = wp_insert_rows($rows, $table_name);
                    }elseif ($type_assignation == 'cp_departement') {
                        $cp_departement =  $_REQUEST['code_postal'];
                        $numero_departement = substr($cp_departement, 0, 2);                   
                        $item['code_postal'] = $numero_departement;
                        $result = $wpdb->insert($table_name, $item);
                        $item['id'] = $wpdb->insert_id;
                    }
                }
                
               
               

                
                if ($result) {
                    $message = __('Item was successfully saved', 'wpbc');
                } else {
                    if($notice_type_assignation){
                        $notice = $notice_type_assignation;
                    }else{
                    $notice = __('There was an error while saving item', 'wpbc');
                    }
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'wpbc');
                } else {
                    $notice = __('There was an error while updating item', 'wpbc');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpbc');
            }
        }
    }

    
    add_meta_box('contacts_form_meta_box', __('Assignation du Code postal', 'wpbc'), 'wpbc_contacts_form_meta_box_handler_cp', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts_form_cp&id_partenaire='.$_REQUEST['id_partenaire']);?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
        <input type="hidden" name="partenaire_id" value="<?php echo $_REQUEST['id'] ?>"/>


        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Assigner', 'wpbc')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function wpbc_contacts_form_meta_box_handler_cp($item)
{
    
    ?>
<tbody >
		
	<div class="formdatabc">		
		
        <form>
            <h3 style="color:#135e96"> Veullez choisir le le type d'assignation du code postal :</h3>
           <?php // liste des choix de type d'assignation avec un checkbox par type d'assignation ?>
            <div class="form-group">
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="default" value="default">
                    <label class="form-check-label" for="default">
                           Assignation par défaut. Par exemple : <strong>75001</strong>
                    </label>
                    </p>
                </div>
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="cp_virgule" value="cp_virgule">
                    <label class="form-check-label" for="cp_virgule">
                        Assignation par séparation avec des virgules. Par exemple : <strong>75001,75002,75003</strong>
                    </label>
                    </p>
                </div>
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="cp_tranche" value="cp_tranche">
                    <label class="form-check-label" for="cp_tranche">
                        Assignation par tranche. Par exemple : <strong>75001-75002</strong>
                    </label>
                    </p>
                </div>
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="cp_departement" value="cp_departement">
                    <label class="form-check-label" for="cp_departement">
                        Assignation par département. Par exemple : <strong>75</strong>
                    </label>
                    </p>
                </div>
                    
            </div>
            
            <div class="form2bc">
                <p>			
                    <label for="code_postal"><?php _e('Code postal', 'wpbc')?></label>
                <br>	
                    <input id="code_postal" name="code_postal" type="text" value="<?php echo esc_attr($item['code_postal'])?>"
                            required>
                </p> 	
            </div>
            <div class="form2bc">
                <p>				
                    <input id="partenaire_id" name="partenaire_id" type="hidden" value="<?php echo $_REQUEST['id_partenaire']? $_REQUEST['id_partenaire'] : esc_attr($item['partenaire_id'])?>"
                            required>
                </p>
            </div>

        </form>
    </div>
</tbody>
<?php
}

function wp_insert_rows($row_arrays = array(), $wp_table_name, $update = false, $primary_key = null) {
	global $wpdb;
	$wp_table_name = esc_sql($wp_table_name);
	// Setup arrays for Actual Values, and Placeholders
	$values        = array();
	$place_holders = array();
	$query         = "";
	$query_columns = "";
	
	$query .= "INSERT INTO `{$wp_table_name}` (";
	foreach ($row_arrays as $count => $row_array) {
		foreach ($row_array as $key => $value) {
			if ($count == 0) {
				if ($query_columns) {
					$query_columns .= ", " . $key . "";
				} else {
					$query_columns .= "" . $key . "";
				}
			}
			
			$values[] = $value;
			
			$symbol = "%s";
			if (is_numeric($value)) {
				if (is_float($value)) {
					$symbol = "%f";
				} else {
					$symbol = "%d";
				}
			}
			if (isset($place_holders[$count])) {
				$place_holders[$count] .= ", '$symbol'";
			} else {
				$place_holders[$count] = "( '$symbol'";
			}
		}
		// mind closing the GAP
		$place_holders[$count] .= ")";
	}
	
	$query .= " $query_columns ) VALUES ";
	
	$query .= implode(', ', $place_holders);
	
	if ($update) {
		$update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
		$cnt    = 0;
		foreach ($row_arrays[0] as $key => $value) {
			if ($cnt == 0) {
				$update .= "$key=VALUES($key)";
				$cnt = 1;
			} else {
				$update .= ", $key=VALUES($key)";
			}
		}
		$query .= $update;
	}
	
	$sql = $wpdb->prepare($query, $values);
	if ($wpdb->query($sql)) {
		return true;
	} else {
		return false;
	}
}



