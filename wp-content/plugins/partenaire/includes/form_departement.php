<?php


function wpbc_contacts_page_handler_departements()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'department_france'; 
    $message = '';
    $notice = '';



    $default = array(
        'id' => 0,
       
        'nom_department'  => '',
        'code_department'     => '',
    );
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = validate_form_departements($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Département créé avec succès.', 'wpbc');
                   
                } else {
                    $notice = __('Erreur lors de la création du département.', 'wpbc');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Département mise à jour avec succès.', 'wpbc');
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
                // $notice = __('Aucune departement trouvée.', 'wpbc');
            }
        }
    }

    $table = new Table_List_Table_departements();
    $table->prepare_items();

    // $message = '';
    if ('delete' === $table->current_action()) {
        $message = ( count($_REQUEST['id'])).' '.__('departement (s) supprimée (s) avec succès', 'wpbc');
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>
            <?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=form_departements');?>"><?php _e('Nouveau département', 'wpbc')?></a>
        </h2>
    
    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>


    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ echo $_REQUEST['page']; } ?>"/>
        <input type="hidden" name="id" value="<?php if(isset($_REQUEST['id'])){ echo $_REQUEST['id']; } ?>"/>
                <?php		 $table->search_box('Search', 'search');		 $table->display() ?>
    </form>

</div>
<?php
}

function form_page_handler_departements()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'department_france'; 
    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'nom_department'  => '',
        'code_department'     => '',
    );

   
    $item = $default;
    if (isset($_REQUEST['id'])) {
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
        if (!$item) {
            $item = $default;
            $notice = __('Item not found', 'wpbc');
        }
    }
    
    add_meta_box('contacts_form_meta_box', __('Informations de la departement', 'wpbc'), 'wpbc_contacts_form_meta_box_handler_departements', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_departements');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

   
    <form id="form" method="POST" enctype="multipart/form-data" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=liste_des_departements');?>">
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

function wpbc_contacts_form_meta_box_handler_departements($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
            <form>
               
                <div class="form2bc">
                    <p>			
                        <label for="nom_department"><?php _e('Nom département', 'wpbc')?></label>
                    <br>	
                        <input id="nom_department" name="nom_department" type="text" value="<?php echo esc_attr($item['nom_department'])?>"
                                required>
                    </p>
                </div>
                <div class="form2bc">
                    <p>			
                        <label for="code_department"><?php _e('Numéro', 'wpbc')?></label>
                    <br>	
                        <input id="code_department" name="code_department" type="text" value="<?php echo esc_attr($item['code_department'])?>"
                                required>
                    </p>
                
                </div>
                               
            </form>
		</div>
</tbody>
<?php
}
