<?php

function wpbc_contacts_page_handler_cp()
{
    global $wpdb;
    $table = new Table_List_Table_CP();
    $table->prepare_items();

    $table_name = $wpdb->prefix . 'code_postal'; 


    $message = $_REQUEST['success_create_message'] ?? '';
    
    $notice = $_REQUEST['error_create_message'] ?? '';
    $partenaire_id = $_REQUEST['id_partenaire'];
    // var_dump($partenaire_id);

    // var_dump( $_REQUEST);exit;

    $partenaire = $wpdb->get_results( "SELECT name FROM ".$wpdb->prefix."partenaire WHERE id = '".$_REQUEST['id_partenaire']."'");

    $partenaire_name = $partenaire[0]->name;
    if ('delete' === $table->current_action()) {
			if(!is_array($_REQUEST['id'])) $_REQUEST['id']=[$_REQUEST['id']];
        $message = ( count($_REQUEST['id'])).' '.__('Code (s) postal (aux) supprimé (s) avec succès.', 'wpbc');
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Liste des codes postaux assignés au partenaire: '.$partenaire_name, 'wpbc')?> 
    </h2>
    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>"><?php _e('Voir la liste des partenaires', 'wpbc')?></a>
    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_cp&id_partenaire='.$_REQUEST['id_partenaire'])?>"><?php _e('Assigner Code postal', 'wpbc')?></a>
    
    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ echo $_REQUEST['page']; } ?>"/>
        <input type="hidden" name="partenaire_id" value="<?php if(isset($_REQUEST['id'])){ echo $_REQUEST['id']; } ?>"/>
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
    $partenaire_id = $_REQUEST['id_partenaire'];

    $default = array(
        'id' => 0,
        'code_postal'     => '',
        'partenaire_id' => $partenaire_id,
    );

    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = form_validate_code_postal($item);


        if ($item_valid === true) {
			
            if ($item['id'] == 0) {
                // $result = $wpdb->insert($table_name, $item);
                // $item['id'] = $wpdb->insert_id;
				unset($item['id']);
                // var_dump($_REQUEST); exit;

              // vérifier si l'un des button radio est coché
                if(! isset($_REQUEST['type_assignation'])){
                    $notice_type_assignation = __('Veuillez choisir le type d\'assignation', 'wpbc');
                }else{
                    $type_assignation = $_REQUEST['type_assignation'];
					
					$_REQUEST['code_postal']=str_replace(' ','',$_REQUEST['code_postal']);
                    // gestion des erreurs


                    // veriifer le type d'assignation du code postal selon le button radio choisi
                    if ($type_assignation == 'default') {
                        // vérifier si le code postal ne contient pas de virgule, dash ou espace
                        if (strpos($item['code_postal'], ',') !== false || strpos($item['code_postal'], '-') !== false || strpos($item['code_postal'], ' ') !== false) {
                            $notice_code_postal = __('Vous avez choisi le type d\'assignation par défaut, veuillez ne pas utiliser de virgule, de tiret ou d\'espace dans le code postal', 'wpbc');
                        }elseif(strlen($item['code_postal']) > 6){
                            $notice_code_postal = __('le code postal doit contenir au maximum 6 caractères', 'wpbc');
                        }
                      
                        // vérifier le code postal ne contient pas de caractères spéciaux
                        elseif (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $item['code_postal'])) {
                            $notice_code_postal = __('Veuillez ne pas utiliser de caractères spéciaux dans le code postal', 'wpbc');
                        }else {
							$item['code_postal']=trim($item['code_postal']);
                            $result = $wpdb->insert($table_name, $item);
                            $item['id'] = $wpdb->insert_id;
                            
                        }
                       
                        
                    
                    }elseif ($type_assignation == 'cp_virgule') {
                        // vérifier si le code postal contient une virgule
                        if (strpos($item['code_postal'], ',') !== false) {
                           $ok = true;
                            $cp_virgule = explode(',', $_REQUEST['code_postal']);
                            $rows = array();
                            foreach ($cp_virgule as $key => $value) {
                                if (strlen($value) > 6) {
                                    $notice_code_postal = __('chaque code postal doit contenir au maximum 6 caractères', 'wpbc');
                                    $ok = false;
                                }else{
									$item['code_postal'] = trim($value);
									$rows[] = $item;
                                }
                            }
                            if ($ok) {
                                $result = wp_insert_rows($rows, $table_name);
                            }else{
                                $item['code_postal'] = $_REQUEST['code_postal'];
                            }
                        }elseif (strpos($item['code_postal'], '-') !== false) {
                                $notice_code_postal = __('Vous avez choisi le type d\'assignation par séparateur de virgule, veuillez ne pas utiliser de tiret dans le code postal', 'wpbc');
                            }elseif (strpos($item['code_postal'], ' ') !== false) {
                                $notice_code_postal = __('Vous avez choisi le type d\'assignation par séparateur de virgule, veuillez ne pas utiliser d\'espace dans le code postal', 'wpbc');
                            }else{
                                $notice_code_postal = __('Vous avez choisi le type d\'assignation par séparateur de virgule, veuillez utiliser une virgule pour séparer les codes postaux', 'wpbc');
                            }
                    }elseif ($type_assignation == 'cp_tranche') {

                        
                        if (strpos($item['code_postal'], '-') !== false) {
        
                            $cp_tranche = explode('-', $_REQUEST['code_postal']);
                            $cp_tranche_debut = $cp_tranche[0];
                            $cp_tranche_fin = $cp_tranche[1];
                            $rows = array();
                            if (strlen($cp_tranche_debut) > 6 || strlen($cp_tranche_fin) > 6) {
                                $notice_code_postal = __('chaque code postal doit contenir au maximum 6 caractères', 'wpbc');
                            } elseif (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $item['code_postal'])) {
                                $notice_code_postal = __('Veuillez ne pas utiliser de caractères spéciaux dans le code postal', 'wpbc');
                            }
                            else{

                            for ($i=$cp_tranche_debut; $i <= $cp_tranche_fin; $i++) { 
								$row=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'villes_france WHERE code_insee="'.trim($i).'"');
								
								
								if(is_object($row)){
									$item['code_postal'] = trim($i);
									$rows[] = $item;
								}
                            }
							// var_dump($rows);exit;
                            $result = wp_insert_rows($rows, $table_name);
                            }
                        }elseif (strpos($item['code_postal'], ',') !== false) {
                            $notice_code_postal = __('Vous avez choisi le type d\'assignation par tranche, veuillez ne pas utiliser de virgule dans le code postal', 'wpbc');
                    
                        }elseif (strpos($item['code_postal'], ' ') !== false) {
                            $notice_code_postal = __('Vous avez choisi le type d\'assignation par tranche, veuillez ne pas utiliser d\'espace dans le code postal', 'wpbc');
                        }else{
                                $notice_code_postal = __('Vous avez choisi le type d\'assignation par tranche, veuillez utiliser un tiret pour séparer les codes postaux', 'wpbc');
                            }
                        
                    }elseif ($type_assignation == 'cp_departement') {

                        $cp_departement ='';
                        $code_postal =  $_REQUEST['code_postal'];
                        // vérifier la taille du code postal
                        if (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $item['code_postal'])) {
                            $notice_code_postal = __('Veuillez ne pas utiliser de caractères spéciaux dans le département', 'wpbc');
                        }elseif (strlen($cp_departement) <4 && strlen($cp_departement)>1){

                        
                        }else{
                            $notice_code_postal = __('le département doit contenir au maximum 3 caractères', 'wpbc');
                        } 
                       
                        // vérifier si le code postal contient une virgule
                        if (strpos($item['code_postal'], ',') !== false) {
                            $notice_code_postal = __('Vous avez choisi le type d\'assignation par département, veuillez ne pas utiliser de virgule dans le code postal', 'wpbc');
                        }elseif (strpos($item['code_postal'], '-') !== false) {
                            $notice_code_postal = __('Vous avez choisi le type d\'assignation par département, veuillez ne pas utiliser de tiret dans le code postal', 'wpbc');
                        }elseif (strpos($item['code_postal'], ' ') !== false) {
                            $notice_code_postal = __('Vous avez choisi le type d\'assignation par département, veuillez ne pas utiliser d\'espace dans le code postal', 'wpbc');
                        }

                        // insertion des données
                        $item['code_postal'] = $code_postal;
                        $result = $wpdb->insert($table_name, $item);                        

                    }elseif ($type_assignation == 'cp_departementtranche') {                        
							if (strpos($item['code_postal'], '-') !== false) {                            
								$cp_tranche = explode('-', $_REQUEST['code_postal']);                            
								$cp_tranche_debut = $cp_tranche[0];							
								if (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $cp_tranche_debut)) {
									$notice_code_postal = __('Veuillez ne pas utiliser de caractères spéciaux dans le département', 'wpbc');
								}elseif (strlen($cp_tranche_debut) >=4 || strlen($cp_tranche_debut)<=1){
									$notice_code_postal = __('le département doit contenir au maximum 3 caractères', 'wpbc');							
								}                             
								$cp_tranche_fin = $cp_tranche[1];							
								if (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $cp_tranche_fin)) {
									$notice_code_postal = __('Veuillez ne pas utiliser de caractères spéciaux dans le département', 'wpbc');
								}elseif (strlen($cp_tranche_debut) >=4 || strlen($cp_tranche_fin)<=1){
									$notice_code_postal = __('le département doit contenir au maximum 3 caractères', 'wpbc');							
								}							                            $rows = array();                           							
								for ($i=$cp_tranche_debut; $i <= $cp_tranche_fin; $i++) {    
									if($i==2){
										$item['code_postal'] ="2a"; 
										$rows[] = $item;
										$item['code_postal'] ="2b"; 
										$rows[] = $item;
									}else{
										$item['code_postal'] = $i; 
										$rows[] = $item;
									}
								}	
								$result = wp_insert_rows($rows, $table_name);
							}else{
								if (strpos($item['code_postal'], ',') !== false) {
									$notice_code_postal = __('Vous avez choisi le type d\'assignation par tranche, veuillez ne pas utiliser de virgule dans le  département', 'wpbc');
								}elseif (strpos($item['code_postal'], ' ') !== false) {
									$notice_code_postal = __('Vous avez choisi le type d\'assignation par tranche, veuillez ne pas utiliser d\'espace dans le  département', 'wpbc');
								}else{
									$notice_code_postal = __('Vous avez choisi le type d\'assignation par tranche, veuillez utiliser un tiret pour séparer les  départements', 'wpbc');
								}
							}
                }elseif ($type_assignation == 'cp_departementvirgule') {  
					
					$result=false;
					if (strpos($item['code_postal'], ',') !== false) {                           
						$ok = true;                            
						$cp_virgule = explode(',', $_REQUEST['code_postal']);                            
						$rows = array();                            
						foreach ($cp_virgule as $key => $value) {                                
							if (strlen($value) > 3) {                                    
								$notice_code_postal = __('le département doit contenir au maximum 3 caractères', 'wpbc');                                    
								$ok = false;                                
							}else{
								$item['code_postal'] = $value;									
								$rows[] = $item;                                
							}
                        }
						if ($ok) {
							$result = wp_insert_rows($rows, $table_name);
                        }else{                                
							$item['code_postal'] = $_REQUEST['code_postal'];                            
						}
                    }elseif (strpos($item['code_postal'], '-') !== false) {
						$notice_code_postal = __('Vous avez choisi le type d\'assignation par séparateur de virgule, veuillez ne pas utiliser de tiret dans le code postal', 'wpbc'); 
					}elseif (strpos($item['code_postal'], ' ') !== false) {
						$notice_code_postal = __('Vous avez choisi le type d\'assignation par séparateur de virgule, veuillez ne pas utiliser d\'espace dans le code postal', 'wpbc');
                    }else{
						$notice_code_postal = __('Vous avez choisi le type d\'assignation par séparateur de virgule, veuillez utiliser une virgule pour séparer les codes postaux', 'wpbc');                        
					}
							
			}

if ($result) {
					$message = __('Code postal ajouté avec succès.', 'wpbc');
				   ?>
					<script>
						 let message = '<?php echo $message; ?>';
						  window.location.href = '<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_code_insee&id_partenaire='.$partenaire_id);?>' + '&success_create_message=' + message;
						</script>
					<?php
				} else {
					if($notice_type_assignation){
						$notice = $notice_type_assignation;
					}elseif($notice_code_postal){
						$notice = $notice_code_postal;
					}else{
						$notice = __('Erreur lors de l\'ajout du code postal, peut être un doublon ?', 'wpbc');
					}
				
				}				
			}				
				
				
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Code postal mis à jour avec succès.', 'wpbc');
					?>
                <script>
                     let message = '<?php echo $message; ?>';
                      window.location.href = '<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_code_insee&id_partenaire='.$partenaire_id);?>' + '&success_create_message=' + message;
                    </script>
                <?php
                } else {
                    $notice = __('Aucune modification n\'a été effectuée. peut être un doublon?', 'wpbc');
					?>
                <script>
                     let message = '<?php echo str_replace("'","\\'",$notice); ?>';
                      window.location.href = '<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_code_insee&id_partenaire='.$partenaire_id);?>' + '&error_create_message=' + message;
                    </script>
                <?php
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
                // $notice = __('Aucun code postal trouvé.', 'wpbc');
            }
        }
    }

    add_meta_box('contacts_form_meta_box', __('Assignation du Code postal', 'wpbc'), 'wpbc_contacts_form_meta_box_handler_cp', 'contact', 'normal', 'default');
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_code_insee&id_partenaire='.$_REQUEST['id_partenaire']);?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST" 
          enctype="multipart/form-data">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
        <input type="hidden" name="id_partenaire" value="<?php echo $_REQUEST['id_partenaire'] ?>"/>


        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Enregistrer', 'wpbc')?>" id="submit" class="button-primary" name="submit" OnClick="redirect_function()">
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
            <?php if(empty($item['id'])){?>
            
            <h3 style="color:#135e96"> Veuillez choisir le le type d'assignation du code insee/departement :</h3>
           <?php // liste des choix de type d'assignation avec un checkbox par type d'assignation ?>
            <div class="form-group">
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="default" value="default" <?php if(isset($_POST['type_assignation']) && $_POST['type_assignation'] == 'default'){echo 'checked';}?>>
                    <label class="form-check-label" for="default">
                           Assignation par défaut. Par exemple : <strong>75001</strong>
                    </label>
                    </p>
                </div>
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="cp_virgule" value="cp_virgule" <?php if(isset($_POST['type_assignation']) && $_POST['type_assignation'] == 'cp_virgule'){echo 'checked';}?>>
                    <label class="form-check-label" for="cp_virgule">
                        Assignation par séparation avec des virgules. Par exemple : <strong>75001,75002,75003</strong>
                    </label>
                    </p>
                </div>
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="cp_tranche" value="cp_tranche" <?php if(isset($_POST['type_assignation']) && $_POST['type_assignation'] == 'cp_tranche'){echo 'checked';}?>>
                    <label class="form-check-label" for="cp_tranche">
                        Assignation par tranche. Par exemple : <strong>75001-75002</strong>
                    </label>
                    </p>
                </div>				<div class="form-check">                    <p>                     <input class="form-check-input" type="radio" name="type_assignation" id="cp_departement" value="cp_departementvirgule" <?php if(isset($_POST['type_assignation']) && $_POST['type_assignation'] == 'cp_departementvirgule'){echo 'checked';}?>>                    <label class="form-check-label" for="cp_departement">                        Assignation des départements  par séparation avec des virgules.  Par exemple : <strong>75,77,78</strong>                    </label>                    </p>                </div>
                <div class="form-check">
                    <p> 
                    <input class="form-check-input" type="radio" name="type_assignation" id="cp_departement" value="cp_departement" <?php if(isset($_POST['type_assignation']) && $_POST['type_assignation'] == 'cp_departement'){echo 'checked';}?>>
                    <label class="form-check-label" for="cp_departement">
                        Assignation par département. Par exemple : <strong>75</strong>
                    </label>
                    </p>
                </div>				<div class="form-check">                    <p>                     <input class="form-check-input" type="radio" name="type_assignation" id="cp_departementtranche" value="cp_departementtranche" <?php if(isset($_POST['type_assignation']) && $_POST['type_assignation'] == 'cp_departementtranche'){echo 'checked';}?>>                    <label class="form-check-label" for="cp_departement">                        Assignation par département par tranche. Par exemple : <strong>91-95</strong>                    </label>                    </p>                </div>
                    
            </div>
            <?php } ?>
            
            <div class="form2bc">
                <p>			
                    <label for="code_postal"><?php _e('Code insee/departement', 'wpbc')?></label>
                <br>	
                    <input id="code_postal" name="code_postal" type="text" value="<?php echo esc_attr($item['code_postal'])?>"
                            required>
                </p> 	
            </div>
            <div class="form2bc">
                <p>				
                    <input id="partenaire_id" name="partenaire_id" type="hidden" value="<?php echo $_REQUEST['id_partenaire'] ? $_REQUEST['id_partenaire'] : esc_attr($item['partenaire_id'])?>"
                            required>
                </p>
            </div>

        </form>
    </div>
</tbody>
<?php
}

function wp_insert_rows($row_arrays = array(), $wp_table_name='', $update = false, $primary_key = null) {
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



