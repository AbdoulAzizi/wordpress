<?php

function wpbc_contacts_page_handler()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'partenaire'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'phone'  => '',
        'phone_second'  => '',
        'email'     => '',
        'siret'     => '',
        // 'code_postal'     => '',
    );
	foreach($default as $key=>$v){
		if(isset($_REQUEST[$key])){
			$_REQUEST[$key]=str_replace("\\'","'",$_REQUEST[$key]);
		}
	}
	 $table = new Table_List_Table();
   
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
      
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = wpbc_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Partenaire ajouté avec succès.', 'wpbc');
                } else {
                    $notice = __('Erreur lors de l\'ajout du partenaire.', 'wpbc');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
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
        if (isset($_REQUEST['id']) && 'delete' != $table->current_action()) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Aucun partenaire trouvé.', 'wpbc');
            }
        }
    }


 $table->prepare_items();
   

    // $message = '';
    if ('delete' === $table->current_action()) {
		if(!is_array($_REQUEST['id'])) $_REQUEST['id']=[$_REQUEST['id']];
        $message = ( count($_REQUEST['id'])).' '.__('partenaire(s) supprimé(s) avec succès.', 'wpbc');
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
        <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ echo $_REQUEST['page']; } ?>"/>
        <input type="hidden" name="id" value="<?php if(isset($_REQUEST['id'])){ echo $_REQUEST['id']; } ?>"/>
        <?php		 $table->search_box('Search', 'search');		$table->display() ?>
    </form>

</div>
<?php
}

 // create partenaire page to assign code postal
 function add_code_insee_page()
 {

    // var_dump($_REQUEST);exit;
     global $wpdb;
     $table_name = $wpdb->prefix . 'partenaire'; 
     $id = $_REQUEST['id'];
     $partenaire = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
     
     ?>
     <div class="wrap">
     <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

         <h2>Assigner un ou plusieurs code postal (aux) au partenaire <?php echo $partenaire[0]->name; ?></h2>
         <!-- exemple des formats de code postal -->
            <p>Vous pouvez assigner un seul code postal. par exemple : <strong>75001</strong></p>
            <p>Vous pouvez assigner plusieurs code postaux en les séparant par une virgule. par exemple : <strong>75001,75002,75003</strong></p>
            <p>Vous pouvez assigner un intervalle de code postal. par exemple : <strong>75001-75003</strong></p>
        <!-- fin exemple -->

        <!-- Laisser une alert à l'utilisateur -->
        <div class="alert alert-danger" role="alert">
            <strong>Note!</strong> Si le partenaire a déjà un ou plusieurs code postal(aux) assigné(s), vous pouvez lui assigner d'autres en les séparant par une virgule.
        </div>
        
         <form method="post" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>" enctype="multipart/form-data" >
             <table class="form-table">
                 <tbody>
                     <tr>
                         <th scope="row">
                             <label for="code_postal">Code Insee/departement </label>
                         </th>
                         <td>
                             <input name="code_postal" type="text" id="code_postal" value="<?php echo $partenaire[0]->code_postal;?>" class="regular-text">
                         </td>
                     </tr>
                 </tbody>
             </table>
             <input type="hidden" name="partenaire_id" value="<?php echo $id; ?>">
             <input type="submit" name="submitCodePostal" id="submitCodePostal" class="button button-primary" value="Enregistrer">
         </form>
     </div>
     <?php

// var_dump(plugin_dir_path(__FILE__) . 'meta_cp.php');
    // require_once plugin_dir_url('').'partenaire/code_postal.php';
    // var_dump(plugin_dir_url('').'partenaire/code_postal.php');exit;
    //  include_once plugin_dir_path(__FILE__) . 'meta_cp.php';
    //  wpbc_contacts_page_handler1();
     
 }



function wpbc_contacts_form_page_handler()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'partenaire'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'phone'  => '',
        'phone_second'  => '',
        'email'     => '',
        'siret'     => '',
        // 'code_postal'     => '',
    );
    
    
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpbc');
            }
        }
    

    
    add_meta_box('contacts_form_meta_box', __('Coordonnées du partenaire', 'wpbc'), 'wpbc_contacts_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Partenaires', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>"><?php _e('Retour à la liste', 'wpbc')?></a>
    </h2>

    <form id="form" method="POST" action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partenaires');?>" enctype="multipart/form-data">
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

function wpbc_contacts_form_meta_box_handler($item)
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
            <label for="phone"><?php _e('Numéro de téléphone', 'wpbc')?></label>
		<br>
		    <input id="phone" name="phone" type="text" value="<?php echo esc_attr($item['phone'])?>"
                    >
        </p>
		<p>	
            <label for="phone_second"><?php _e('Numéro de téléphone2', 'wpbc')?></label>
		<br>
		    <input id="phone_second" name="phone_second" type="text" value="<?php echo esc_attr($item['phone_second'])?>"
                    >
        </p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="email"><?php _e('E-Mail:', 'wpbc')?></label> 
		<br>	
            <input id="email" name="email" type="email" value="<?php echo esc_attr($item['email'])?>"
                  >
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
