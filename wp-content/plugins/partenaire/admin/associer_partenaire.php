<?php


function associer_partenaire() {
    add_menu_page('Partenaire', 'Partenaire', 'manage_options', __FILE__, 'main_partenaire', plugins_url('MyPluginFolder/images/icon.png') );
}

add_action('admin_menu', 'associer_partenaire');



