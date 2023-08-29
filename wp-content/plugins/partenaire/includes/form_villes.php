<?php

function villes_page_handler_villes()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'villes_france'; 
    $message = '';
    $notice = '';



    $default = array(
        'id_ville' => 0,
        'Code_commune_INSEE'      => '',
        'Nom_commune'  => '',
        'Code_postal'     => '',
    );
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = validate_form_villes($item);
        if ($item_valid === true) {
            if ($item['id_ville'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id_ville'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Ville créée avec succès.', 'wpbc');
                   
                } else {
                    $notice = __('Erreur lors de la création de la ville.', 'wpbc');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id_ville' => $item['id_ville']));
                if ($result) {
                    $message = __('Ville mise à jour avec succès.', 'wpbc');
                } else {
                    $notice = __('Aucune modification n\'a été apportée.', 'wpbc');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id_ville'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_ville = %d", $_REQUEST['id_ville']), ARRAY_A);
            if (!$item) {
                $item = $default;
                // $notice = __('Aucune ville trouvée.', 'wpbc');
            }
        }
    }

    $table = new Villes_Custom_List_Table();
    $table->prepare_items();

    // $message = '';
    if ('delete' === $table->current_action()) {
        $message = ( count($_REQUEST['id_ville'])).' '.__('Ville (s) supprimée (s) avec succès', 'wpbc');
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>
            <?php _e('Liste des villes', 'wpbc')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_villes');?>"><?php _e('Nouvelle ville', 'wpbc')?></a>
        </h2>
    
    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>


    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="id_ville" value="<?php if(isset($_REQUEST['id_ville'])) echo $_REQUEST['id_ville']; ?>"/>
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
        'id_ville' => 0,
        'Code_commune_INSEE'      => '',
        'Nom_commune'  => '',
        'Code_postal'     => '',
    );

   
    $item = $default;
    if (isset($_REQUEST['id_ville'])) {
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_ville = %d", $_REQUEST['id_ville']), ARRAY_A);
        if (!$item) {
            $item = $default;
            $notice = __('Item not found', 'wpbc');
        }
    }
    
    add_meta_box('villes_form_meta_box', __('Informations de la ville', 'wpbc'), 'form_villes_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Ville', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_villes');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

   
    <form id="form" method="POST" enctype="multipart/form-data" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_villes');?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id_ville" value="<?php echo $item['id_ville'] ?>"/>

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

function form_villes_handler($item)
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
                 </div>
                <div class="form2bc">
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
                
                </div>
                               
            </form>
		</div>
</tbody>
<?php
}
