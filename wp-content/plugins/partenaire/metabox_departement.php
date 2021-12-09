<?php

add_action( 'add_meta_boxes', 'meta_box_add_departement' );
function meta_box_add_departement() {
    add_meta_box( 'meta_box_departement', 'Département', 'meta_box_departement', 'page', 'side', 'high' );
}
function meta_box_departement( $post ) {
    $values = get_post_custom( $post->ID );
    $text = isset( $values['meta_box_text_departement'] ) ? esc_attr( $values['meta_box_text_departement'][0] ) : '';
    wp_nonce_field( 'meta_box_nonce_department', 'meta_box_nonce_departement' );
    ?>
    <p>
        <label for="meta_box_text_departement">Département</label>
        <input type="text" name="meta_box_text_departement" id="meta_box_text_departement" value="<?php echo $text; ?>" />
    </p>
    <?php   
}
add_action( 'save_post', 'departemnt_meta_box_save' );
function departemnt_meta_box_save( $post_id ) {
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    // if our nonce isn't there, or we can't verify it, bail
    if( !isset( $_POST['meta_box_nonce_departement'] ) || !wp_verify_nonce( $_POST['meta_box_nonce_departement'], 'meta_box_nonce_department' ) ) return;
    // if our current user can't edit this post, bail
    if( !current_user_can( 'edit_post', $post_id ) ) return;
    // now we can actually save the data
    $allowed = array( 
        'a' => array( // on allow a tags
            'href' => array() // and those anchords can only have href attribute
        )
    );
    // Probably a good idea to make sure your data is set
    if( isset( $_POST['meta_box_text_departement'] ) )
        update_post_meta( $post_id, 'meta_box_text_departement', wp_kses( $_POST['meta_box_text_departement'], $allowed ) );
}