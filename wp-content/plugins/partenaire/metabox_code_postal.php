<?php

// Add custom Slider ID field to 'Edit Page'
add_action( 'add_meta_boxes', 'cd_meta_box_add' );
function cd_meta_box_add() {
    add_meta_box( 'code_postal_meta_box_id', 'Code Postal', 'meta_box_code_postal', 'page', 'side', 'high' );
}
function meta_box_code_postal( $post ) {
    $values = get_post_custom( $post->ID );
    $text = isset( $values['meta_box_code_postal_text'] ) ? esc_attr( $values['meta_box_code_postal_text'][0] ) : '';
    wp_nonce_field( 'meta_box_nonce_code_postal', 'meta_box_nonce' );
    ?>
    <p>
        <label for="meta_box_code_postal_text">Code postal</label>
        <input type="text" name="meta_box_code_postal_text" id="meta_box_code_postal_text" value="<?php echo $text; ?>" />
    </p>
    <?php   
}
add_action( 'save_post', 'code_postal_meta_box_save' );
function code_postal_meta_box_save( $post_id ) {
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    // if our nonce isn't there, or we can't verify it, bail
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'meta_box_nonce_code_postal' ) ) return;
    // if our current user can't edit this post, bail
    if( !current_user_can( 'edit_post', $post_id ) ) return;
    // now we can actually save the data
    $allowed = array( 
        'a' => array( // on allow a tags
            'href' => array() // and those anchords can only have href attribute
        )
    );
    // Probably a good idea to make sure your data is set
    if( isset( $_POST['meta_box_code_postal_text'] ) )
        update_post_meta( $post_id, 'meta_box_code_postal_text', wp_kses( $_POST['meta_box_code_postal_text'], $allowed ) );
}