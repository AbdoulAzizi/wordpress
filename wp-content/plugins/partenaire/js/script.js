$ = jQuery;

$(document).ready(function () {
  $("ul.partenaires-list a.broken_link").removeClass("broken_link");

  //OPEN CLOSE LIST

  $("ul.partenaires-list.openclose-list li").click(function () {
    //console.log(this);

    var subelem = $(this).find("ul.partenaires-list");

    if (subelem.is(":visible")) {
      subelem.hide();

      $(this).removeClass("collapsed");
    } else {
      subelem.show();

      $(this).addClass("collapsed");
    }
  });
});

$(document).on('click', '.add-model-button', function() {
    var container = $('#page-models-container');
    var index = container.find('.metabox-holder').length + 1;

    // Cloner le dernier modèle de page
    var newModel = container.find('.metabox-holder:last').clone(true, true);

    // Mettre à jour l'index dans les nouveaux éléments
    newModel.attr('data-index', index);
    newModel.find('[name^="page_modele_departement"]').attr('name', 'page_modele_departement[' + index + ']');
    newModel.find('[name^="page_modele_ville"]').attr('name', 'page_modele_ville[' + index + ']');

    // Réinitialiser les valeurs sélectionnées
    newModel.find('select').val(0);

    // Supprimer le bouton "moins" du modèle cloné
    newModel.find('.remove-model-button').remove();

    // Ajouter le nouveau modèle à la page
    container.append(newModel);

    // Ajouter le bouton "moins" uniquement si plus d'un modèle est présent
    if (index > 1) {
        var removeButton = $('<button type="button" class="button remove-model-button" style="float: right;">-</button>');
        removeButton.click(function() {
            $(this).closest('.metabox-holder').remove();
        });
        newModel.find('.inside').append(removeButton);
    }
});

// Fonction pour gérer la suppression de modèles de page existants
$(document).on('click', '.remove-model-button', function() {
    $(this).closest('.metabox-holder').remove();
});

