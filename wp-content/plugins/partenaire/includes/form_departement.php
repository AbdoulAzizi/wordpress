<?php

function departement_page_handler()
{
    global $wpdb;
    $table = new Departement_Custom_List_Table();
    $table->prepare_items();

    $table_name = $wpdb->prefix . 'departement'; 
    // var_dump($_REQUEST);
    $success_create_message = 'Département ajouté avec succès';
    $success_update_message = 'Département mis à jour avec succès';
    if(isset($_REQUEST['create_departement'])){
        $message = $success_create_message;
    }elseif (isset($_REQUEST['update_departement'])) {
        $message = $success_update_message;
    }
    else {
        $message = '';
    }
    
    $notice = '';
    $partenaire_id = $_REQUEST['id_partenaire'];
    // var_dump($partenaire_id);

    // var_dump( $_REQUEST);exit;

    $partenaire = $wpdb->get_results( "SELECT name FROM wp_partenaire WHERE id_partenaire = '".$_REQUEST['id_partenaire']."'");
    // var_dump($partenaire[0]->name);
    $partenaire_name = $partenaire[0]->name;
    if ('delete' === $table->current_action()) {
        $message = ( count($_REQUEST['id_departement'])).' '.__('Département (s) supprimé (s) avec succès.', 'wpbc');
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Liste des départements assignés au partenaire: '.$partenaire_name, 'wpbc')?> 
    </h2>
    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>"><?php _e('Voir la liste des partenaires', 'wpbc')?></a>    
    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_departement&id_partenaire='.$_REQUEST['id_partenaire'])?>"><?php _e('Assigner un départemnt', 'wpbc')?></a>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="id_partenaire" value="<?php if(isset($_REQUEST['id_partenaire'])){echo $_REQUEST['id_partenaire'];}?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php

}


function departement_form_page_handler()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'departement'; 

    $message = '';
    $notice = '';
    $partenaire_id = $_REQUEST['id_partenaire'];

    $default = array(
        'id_departement' => 0,
        'departement'     => '',
        'partenaire_id' => $partenaire_id,
    );

    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     
        // var_dump($item);exit;

        $item_valid = validate_form_departement($item);


        if ($item_valid === true) {
            if ($item['id_departement'] == 0) {
                // $result = $wpdb->insert($table_name, $item);
                // $item['id'] = $wpdb->insert_id;

                // var_dump($_REQUEST); exit;
                $ok = true;

                $departement =  $_REQUEST['departement'];
                  // vérifier si le code postal contient une virgule
                if (strpos($item['departement'], ',') !== false) {
                    $notice_code_departement = __('Veuillez ne pas utiliser de virgule dans le code Département', 'wpbc');
                }elseif (strpos($item['departement'], '-') !== false) {
                    $notice_code_departement = __('Veuillez ne pas utiliser de tiret dans le code Département', 'wpbc');
                }elseif (strpos($item['departement'], ' ') !== false) {
                    $notice_code_departement = __('Veuillez ne pas utiliser d\'espace dans le code Département', 'wpbc');
                }elseif (strlen($departement) > 6){
                    $notice_code_departement = __('le Département doit contenir au maximum 6 caractères', 'wpbc');
                } elseif (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $item['departement'])) {
                    $notice_code_departement = __('Veuillez ne pas utiliser de caractères spéciaux dans le code du Département', 'wpbc');
                }else{
                    // vérifier la taille du code postal
                    if (strlen($departement) == 5){
                        $code_departement = substr($departement, 0, 2);
                          // insertion des données
                        $item['departement'] = $code_departement;
                        $result = $wpdb->insert($table_name, $item); 
                    }elseif (strlen($departement) == 6){

                        $code_departement = substr($departement, 0, 3);
                          // insertion des données
                        $item['departement'] = $code_departement;
                        $result = $wpdb->insert($table_name, $item); 
                    
                    }
                    else {
                        $code_departement = substr($departement, 0, 2);

                        $item['departement'] = $code_departement;
                        $result = $wpdb->insert($table_name, $item);
                        
                    }
                }
                   
                
               
               

            
            if ($result) {
                $message = __('success', 'wpbc');
               ?>
                <script>
                     let message = '<?php echo $message; ?>';
                      window.location.href = '<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_departement&id_partenaire='.$partenaire_id);?>' + '&create_departement=' + message;
                    </script>
                <?php
            } else {
                if($notice_type_assignation){
                    $notice = $notice_type_assignation;
                }elseif($notice_code_departement){
                    $notice = $notice_code_departement;
                }else{
                $notice = __('Erreur lors de l\'ajout du code postal.', 'wpbc');
                }
            }
            } else {
                if (strlen($item['departement']) > 6){
                    $notice = __('le Département doit contenir au maximum 6 caractères', 'wpbc');
                } elseif (preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬.]/', $item['departement'])) {
                    $notice = __('Veuillez ne pas utiliser de caractères spéciaux dans le code du Département', 'wpbc');
                }else{

                    if (strlen($item['departement']) == 5){
                        $code_departement = substr($item['departement'], 0, 2);
                        // update
                        $item['departement'] = $code_departement;
                        $result = $wpdb->update($table_name, $item, array('id_departement' => $item['id_departement']));
                    }elseif (strlen($item['departement']) == 6){

                        $code_departement = substr($item['departement'], 0, 3);
                        // update
                        $item['departement'] = $code_departement;
                        $result = $wpdb->update($table_name, $item, array('id_departement' => $item['id_departement']));
                    
                    }
                    else {
                        $code_departement = substr($item['departement'], 0, 2);
                        // update
                        $item['departement'] = $code_departement;
                        $result = $wpdb->update($table_name, $item, array('id_departement' => $item['id_departement']));
                        
                    }
                
                    if ($result) {
                        $message = __('success', 'wpbc');
                        ?>
                        <script>
                        let message = '<?php echo $message; ?>';
                        window.location.href = '<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_departement&id_partenaire='.$partenaire_id);?>' + '&update_departement=' + message;
                        </script>
                        <?php
                    } else {
                        $notice = __('Aucune modification n\'a été effectuée.', 'wpbc');
                    }
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id_departement'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_departement = %d", $_REQUEST['id_departement']), ARRAY_A);
            if (!$item) {
                $item = $default;
                // $notice = __('Aucun code postal trouvé.', 'wpbc');
            }
        }
    }

    add_meta_box('contacts_form_meta_box', __('Assignation du Département', 'wpbc'), 'departement_form_meta_box_handler', 'contact', 'normal', 'default');
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Départements', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_departement&id_partenaire='.$_REQUEST['id_partenaire']);?>"><?php _e('Retour à la liste', 'wpbc')?></a>
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
        
        <input type="hidden" name="id_departement" value="<?php echo $item['id_departement'] ?>"/>
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

function departement_form_meta_box_handler($item)
{
    
    ?>
<tbody >
		
	<div class="formdatabc">		

        <form>

        <?php if(empty($item['id_departement'])){ ?>

            <h3><?php _e('Assignation du Département', 'wpbc')?></h3>
            <p> <?php _e('Vous pouvez soit saisir le code postal ou le code département', 'wpbc')?></p>
            <strong> <?php _e('Cas 1 : Saisir le code postal', 'wpbc')?></strong>
            <p> Si le code postal est composé de 5 chiffres, le code département sera composé de 2 chiffres. <br>
                Exemple: Si le code postal est le <b>75001</b>, le code département sera <b>75</b>
            <p> Si le code postal est composé de 6 chiffres, le code département sera composé de 3 chiffres. <br>
                Exemple: Si le code postal est le <b>750001</b>, le code département sera <b>750</b>
                <br> </p>
            <strong> <?php _e('Cas 2 : Saisir le code département', 'wpbc')?></strong>
            <p> Par défaut le code département sera composé de 2 chiffres. <br>
                Exemple: <b>75</b>  
            
        <?php } ?>
            
            <div class="form2bc">
                <p>			
                    <label for="departement"><?php _e('Département', 'wpbc')?></label>
                <br>	
                    <input id="departement" name="departement" type="text" value="<?php echo esc_attr($item['departement'])?>"
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


