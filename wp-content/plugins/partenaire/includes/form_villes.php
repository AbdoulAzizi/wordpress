<?php


function wpbc_contacts_page_handler_villes()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'villes_france'; 
    $message = '';
    $notice = '';



    $default = array(
        'id' => 0,
       
        'Nom_commune'  => '',
        'Code_postal'     => '',
        'code_insee'     => '',
    );

    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = validate_form_villes($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Ville créée avec succès.', 'wpbc');
                   
                } else {
                    $notice = __('Erreur lors de la création de la ville.', 'wpbc');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
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
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                // $notice = __('Aucune ville trouvée.', 'wpbc');
            }
        }
    }

    $table = new Table_List_Table_Villes();

   
    $table->prepare_items();

    // $message = '';
    if ('delete' === $table->current_action()) {
        $message = ( count($_REQUEST['id'])).' '.__('Ville (s) supprimée (s) avec succès', 'wpbc');
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>
            <?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_villes');?>"><?php _e('Nouvelle ville', 'wpbc')?></a>
        </h2>
    
    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>


    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="id" value="<?php echo (isset($_REQUEST['id'])) ? $_REQUEST['id'] : 0 ;?>"/>
        <?php 
		 $table->search_box('Search', 'search');
		$table->display() ?>
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
        'Nom_commune'  => '',
        'Code_postal'     => '',
        'code_insee'     => '',
    );

   
    $item = $default;
    if (isset($_REQUEST['id'])) {
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
        if (!$item) {
            $item = $default;
            $notice = __('Item not found', 'wpbc');
        }
    }
    
    add_meta_box('contacts_form_meta_box', __('Informations de la ville', 'wpbc'), 'wpbc_contacts_form_meta_box_handler_villes', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_villes');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

   
    <form id="form" method="POST" enctype="multipart/form-data" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_villes');?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

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

function wpbc_contacts_form_meta_box_handler_villes($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
            <form>
               
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
                <div class="form2bc">
                    <p>			
                        <label for="code_insee"><?php _e('Code insee', 'wpbc')?></label>
                    <br>	
                        <input id="code_insee" name="code_insee" type="text" value="<?php echo esc_attr($item['code_insee'])?>"
                                required>
                    </p>
                
                </div>
                               
            </form>
		</div>
</tbody>
<?php
}
