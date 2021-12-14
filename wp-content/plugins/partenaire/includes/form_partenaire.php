<?php

function partenaires_page_handler()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'partenaire'; 

    $message = '';
    $notice = '';


    $default = array(
        'id_partenaire' => 0,
        'name'      => '',
        'phone'  => '',
        'email'     => '',
        'siret'     => '',
        // 'code_postal'     => '',
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);  

        $item_valid = wpbc_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id_partenaire'] == 0) {
                var_dump($item);
                // gérer la présence d'un apostrophe dans le nom
                $item['name'] = __( stripslashes($item['name']) );
                $result = $wpdb->insert($table_name, $item);
                if ($result) {
                    $message = __('Partenaire ajouté avec succès.', 'wpbc');
                } else {
                    $notice = __('Erreur lors de l\'ajout du partenaire.', 'wpbc');
                }
            } else {
                $item['name'] = __( stripslashes($item['name']) );
                $result = $wpdb->update($table_name, $item, array('id_partenaire' => $item['id_partenaire']));
                if ($result) {
                    $message = __('Partenaire mis à jour avec succès.', 'wpbc');
                } else {
                    $notice = __('Aucune modification n\'a été effectuée.', 'wpbc');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }else {
        
        $item = $default;
        if (isset($_REQUEST['id_partenaire'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_partenaire = %d", $_REQUEST['id_partenaire']), ARRAY_A);
            if (!$item) {
                $item = $default;
                // $notice = __('Aucun partenaire trouvé.', 'wpbc');
            }
        }
    }


    $table = new Partenaire_Custom_List_Table();
    $table->prepare_items();

    // $message = '';
    if ('delete' === $table->current_action()) {
        $message = ( count($_REQUEST['id_partenaire'])).' '.__('partenaire(s) supprimé(s) avec succès.', 'wpbc');
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts_form');?>"><?php _e('Nouveau partenaire', 'wpbc')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="id_partenaire" value="<?php echo $_REQUEST['id_partenaire'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

function wpbc_contacts_form_page_handler()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'partenaire'; 

    $message = '';
    $notice = '';


    $default = array(
        'id_partenaire' => 0,
        'name'      => '',
        'phone'  => '',
        'email'     => '',
        'siret'     => '',
        // 'code_postal'     => '',
    );
    
    
        
        $item = $default;
        if (isset($_REQUEST['id_partenaire'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_partenaire = %d", $_REQUEST['id_partenaire']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpbc');
            }
        }
    

    
    add_meta_box('partenaires_form_meta_box', __('Coordonnées du partenaire', 'wpbc'), 'partenaires_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

    <form id="form" method="POST" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>" enctype="multipart/form-data">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id_partenaire" value="<?php echo $item['id_partenaire'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Enregistrer', 'wpbc')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function partenaires_form_meta_box_handler($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Nom', 'wpbc')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>"
                    required>
		</p>
        </div>	
		<div class="form2bc">
        <p>	
            <label for="phone"><?php _e('Numero de telephone', 'wpbc')?></label>
		<br>
		    <input id="phone" name="phone" type="text" value="<?php echo esc_attr($item['phone'])?>"
                    required>
        </p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="email"><?php _e('E-Mail:', 'wpbc')?></label> 
		<br>	
            <input id="email" name="email" type="email" value="<?php echo esc_attr($item['email'])?>"
                   required>
        </p>
        </div>	
		<div class="form2bc">
        <p>	  
            <label for="siret"><?php _e('Siret', 'wpbc')?></label>
		<br>
			<input id="siret" name="siret" type="tel" value="<?php echo esc_attr($item['siret'])?>">
		</p>
		</div>
			
		</form>
		</div>
</tbody>
<?php
}
