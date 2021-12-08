<?php

function wpbc_contacts_page_handler_villes()
{
    global $wpdb;

    $table = new Custom_Table_Example_List_Table_Villes();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Nombre d\'enregistrement (s) supprimé (s): %d', 'wpbc'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_villes');?>"><?php _e('Nouvelle ville', 'wpbc')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

function form_page_handler_villes()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'villes_france'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
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
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'wpbc');
                } else {
                    $notice = __('There was an error while saving item', 'wpbc');
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

    
    add_meta_box('contacts_form_meta_box', __('Informations de la ville', 'wpbc'), 'wpbc_contacts_form_meta_box_handler_villes', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_villes');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
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

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'wpbc')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function wpbc_contacts_form_meta_box_handler_villes($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
            <form>
                <div class="form2bc">
                    <p>			
                        <label for="Code_commune_INSEE"><?php _e('Code commune INSEE', 'wpbc')?></label>
                    <br>	
                        <input id="Code_commune_INSEE" name="Code_commune_INSEE" type="text" value="<?php echo esc_attr($item['Code_commune_INSEE'])?>"
                                required>
                    </p>
                    <p>			
                        <label for="Nom_commune"><?php _e('Nom commune', 'wpbc')?></label>
                    <br>	
                        <input id="Nom_commune" name="Nom_commune" type="text" value="<?php echo esc_attr($item['Nom_commune'])?>"
                                required>
                    </p>
                </div>
                <div class="form2bc">
                    <p>			
                        <label for="Code_postal"><?php _e('Code postal', 'wpbc')?></label>
                    <br>	
                        <input id="Code_postal" name="Code_postal" type="text" value="<?php echo esc_attr($item['Code_postal'])?>"
                                required>
                    </p>
                
                    <p>			
                        <label for="Ligne_5"><?php _e('Ligne 5', 'wpbc')?></label>
                    <br>
                        <input id="Ligne_5" name="Ligne_5" type="text" value="<?php echo esc_attr($item['Ligne_5'])?>"
                                required>
                    </p>
                </div>
                <div class="form2bc">
                    <p>			
                        <label for="Libelle_d_acheminement"><?php _e('Libelle d\'acheminement', 'wpbc')?></label>
                    <br>
                        <input id="Libelle_d_acheminement" name="Libelle_d_acheminement" type="text" value="<?php echo esc_attr($item['Libelle_d_acheminement'])?>"
                                required>
                    </p>
                </div>
                               
            </form>
		</div>
</tbody>
<?php
}
