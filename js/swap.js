document.addEventListener("DOMContentLoaded", function () {

  const euro = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 0,
  });

  // Personalisation des messages de confirmation
  if (document.querySelectorAll("a.confirm")) {
    let confirmations = document.querySelectorAll("a.confirm");
    for (let i = 0; i < confirmations.length; i++) {
      let message = confirmations[i].dataset.message
        ? confirmations[i].dataset.message
        : "Etes vous sûr(e) de vouloir supprimer cet élément ?";
      confirmations[i].onclick = () => {
        return window.confirm(message);
      };
    }
  }

  // Prévisu de l'image à la sélection du fichier
  if (document.getElementById("preview1")) {
    upLoadPhoto(1);
    affichePhotoVignette(1);
  }
  if (document.getElementById("preview2")) {
    upLoadPhoto(2);
    affichePhotoVignette(2);
  }
  if (document.getElementById("preview3")) {
    upLoadPhoto(3);
    affichePhotoVignette(3);
  }
  if (document.getElementById("preview4")) {
    upLoadPhoto(4);
    affichePhotoVignette(4);
  }
  if (document.getElementById("preview5")) {
    upLoadPhoto(5);
    affichePhotoVignette(5);
  }

  function affichePhotoVignette(numPhoto) {
    let header = "header" + numPhoto;
    let id = "#preview" + numPhoto + " img";
    let nom_original = "nom_original" + numPhoto;
    let data_img = "data_img" + numPhoto;
    
    document
      .getElementById(header)
      .addEventListener("change", function (event) {
        let fichier = event.target.files[0];
        let ext = ["image/jpeg", "image/png"];
        if (ext.includes(fichier.type)) {
          let reader = new FileReader();
          reader.readAsDataURL(fichier);
          reader.onload = (e) => {
            document.querySelector(id).setAttribute("src", e.target.result);
            if (document.getElementById(nom_original)) {
              // memoriser les infos du fichier image
              document.getElementById(nom_original).setAttribute("value", fichier.name);
              document.getElementById(data_img).setAttribute("value", e.target.result);
            }
          };
        }
      });
  }

  function upLoadPhoto(numPhoto) {
    let header = "header" + numPhoto;
    let preview = "preview" + numPhoto;
    document.addEventListener("dragover", (e) => {
      e.stopPropagation();
      e.preventDefault();
      document.getElementById(preview).style.border = "4px dashed blue";
    });
    document.addEventListener("dragleave", (e) => {
      e.stopPropagation();
      e.preventDefault();
      document.getElementById(preview).style.border = "";
    });
    document.addEventListener("drop", (e) => {
      e.stopPropagation();
      e.preventDefault();
      document.getElementById(preview).style.border = "";
    });
    // On dépose le fichier uniquement qd on est en survol sur la zone
    document.getElementById(preview).addEventListener("drop", (e) => {
      document.getElementById(preview).style.border = "";
      let fichier = e.dataTransfer.files;
      // Alimentation de l'input de type file avec cette info
      document.getElementById(header).files = fichier;
      let event = new Event("change"); // On simule un evenement avec un trigger
      document.getElementById(header).dispatchEvent(event);
    });
  }

  // On récupère la ville à l'aide du code postal via l'API gouv.fr
  if (document.getElementById("cp")) {
    document.getElementById("cp").addEventListener("input", function () {
      if (this.value.length == 5) {
        let url = `https://geo.api.gouv.fr/communes?codePostal=${this.value}&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre`;

        fetch(url)
          .then((response) => response.json())
          .then((data) => {
            let affichage = "";
            for (let ville of data) {
              affichage += `<option value=${ville.nom}>${ville.nom}</option>`;
            }
            document.getElementById("ville").innerHTML = affichage;

            // On va récupérer la région en focntion du code région indiqué avec le code postal
            let url_region = `https://geo.api.gouv.fr/regions?code=${data[0].codeRegion}&fields=nom,code`;
            fetch(url_region)
              .then((response_region) => response_region.json())
              .then((data_region) => {
                document.getElementById(
                  "pays"
                ).innerHTML = `<option value=${data_region[0].nom}>${data_region[0].nom}</option>`;
              })
              .catch((err_region) => console.log(err_region));
          })
          .catch((err) => console.log(err));
      }
    });
  }

  // On récupère toutes les régions  via l'API gouv.fr
  if (document.getElementById("region")) {
    window.onload = initListRegions;
  }

  function initListRegions() {
    let url_region = "https://geo.api.gouv.fr/regions";
    fetch(url_region)
      .then((response_region) => response_region.json())
      .then((data_region) => {
        let affichage =
          '<option value="Toutes" selected>Toutes les régions</option>';
        for (let region of data_region) {
          affichage += `<option value=${region.nom}>${region.nom}</option>`;
        }
        document.getElementById("region").innerHTML = affichage;
      })
      .catch((err_region) => console.log(err_region));
  }

  if (document.getElementById("id_membre")) {
    if ($("#id_membre").val() > 0) {
      window.onload = filtre_et_tri(e);
    }
  }

  // Filtres et de la page d'accueil
  $("#tri").change(filtre_et_tri);
  $("#id_categorie").change(filtre_et_tri);
  $("#id_membre").change(filtre_et_tri);
  $("#region").change(filtre_et_tri);
  $("#rangePrix").change(function (e) {
    $("#prixMax").html("maximum : " + euro.format(this.value));
    filtre_et_tri(e);
  });

  function filtre_et_tri(e) {
    e.preventDefault(); // on empêche le bouton d'envoyer le formulaire

    var id_categorie = $("#id_categorie").val();
    var id_membre = $("#id_membre").val();
    var region = $("#region").val();
    var prix = $("#rangePrix").val();
    var id_tri = $("#tri").val();

    if (id_categorie >= 0 || id_membre >= 0 || prix > 0) {
      // on vérifie que les variables ne sont pas vides
      $.ajax({
        url: "ajax/filtre_et_tri.php", // on donne l'URL du fichier de traitement
        type: "POST", // la requête est de type POST
        dataType: "json",
        data: {
          id_categorie: id_categorie,
          id_membre: id_membre,
          region: region,
          prix: prix,
          id_tri: id_tri,
        }, // et on envoie nos données
      })
        .done(function (datas) {
          // done permet de récupérer la réponse positive du serveur et dans l'argument data, on récupère les données renvoyées par le serveur
          // On vide le contenu de la table et on le re-rempli ds l'ordre du tri
          $("#tbody").html("");
          let nb = datas.length;
          datas.forEach(function (value) {
            let id_annonce = value["id_annonce"];
            let photo = value["photo"];
            let titre = value["titre"];
            let description_courte = value["description_courte"];
            let description = value["description_longue"].substr(0, 200);
            if (value["description_longue"].length > 200) {
              description += " ...";
            }
            let pseudo = value["pseudo"];
            let id_membre = value["id_membre"];
            let prix = euro.format(value["prix"]);
            remplissage(
              id_annonce,
              photo,
              titre,
              description_courte,
              description,
              pseudo,
              id_membre,
              prix
            );
          });
          let pluriel = "";
          if (nb > 1) pluriel = "s";
          $("#nb").html(nb + " résultat" + pluriel);
        })
        .fail(function (error) {
          console.log(error);
          // fail permet de récupérer la réponse negative du serveur
        });
    }
  }

  // fonction de remplissage de la div tbody
  function remplissage(
    id_annonce,
    photo,
    titre,
    description_courte,
    description,
    pseudo,
    id_membre,
    prix
  ) {
    $.ajax({
      url: "ajax/moyenne.php", // on donne l'URL du fichier de traitement
      type: "POST", // la requête est de type POST
      dataType: "json",
      data: {
        id_membre: id_membre,
      }, // et on envoie nos données
    })
      .done(function (datas) {
        let moyenne = datas[0]["moyenne"];

        let i = 1;
        let etoiles = "";
        while (i <= moyenne) {
          etoiles += '<i class="fas fa-star"></i>';
          i++;
        }
        if (moyenne > i - 1) {
          etoiles += '<i class="fas fa-star-half-alt"></i>';
        }

        $("#tbody").append(`

    <tr>
      <td class="col-md" id="tbodyPhoto">
          <div id="hauteur"><img src="images/${photo}" alt="${titre}" class="mx-auto d-block" height="100%"></div>
      </td>
      <td class="col-md">
          <div class="d-flex justify-content-between pb-2">
              <span class="d-inline"><a href="annonce.php?id=${id_annonce}"" id="tbodyTitre" class="fw-bold text-decoration-none text-lien">${titre}</a></span>
              <span class="fw-bold col-md-2 d-inline text-end">${prix}</span>
          </div>
          <div class="d-flex">
              <p id="tbodyDesc">${description_courte}<br>
              ${description}
              </p>
          </div>
          <div class="d-flex align-items-end">
              <p id="tbodyPseudo" class="mt-3 "><a href="#" class="text-decoration-none text-lien">${pseudo}
               ${etoiles}
              </a><span class="hidden" id="moyenne">${moyenne}</span></p>
          </div>
      </td>

  </tr>
  
  `);
      })
      .fail(function (error) {
        console.log(error);
      });
  }

  // fonction de recherche
  $("#recherche").click(function (e) {
    let currentValue = $("#critere").val();
    if (currentValue.length == 0) {
      return false;
    } else {
      $.ajax({
        url: "ajax/suggestions.php",
        type: "GET",
        dataType: "json",
        data: { myInputValue: currentValue },
      })
        .done(function (datas) {
          // On vide le contenu de la table et on le re-rempli avec les resultats de la recherche
          let nb = datas.length;
          if (nb > 0) {
            $("#filtre").html("");
            $("#tbody").html("");
            datas.forEach(function (value) {
              let id_annonce = value["id_annonce"];
              let photo = value["photo"];
              let titre = value["titre"];
              let description_courte = value["description_courte"];
              let description = value["description_longue"].substr(0, 200);
              if (value["description_longue"].length > 200) {
                description += " ...";
              }
              let pseudo = value["pseudo"];
              let id_membre = value["id_membre"];
              let prix = euro.format(value["prix"]);

              remplissage(
                id_annonce,
                photo,
                titre,
                description_courte,
                description,
                pseudo,
                id_membre,
                prix
              );
            });
            let pluriel = "";
            if (nb > 1) pluriel = "s";
            $("#nb").html(nb + " résultat" + pluriel);
          } else {
            return window.alert("Aucune annonce trouvée");
          }
        })
        .fail(function (error) {
          // console.log(error);
        });
    }
  });

  // Autocompletion pour la recherche
  // $("#myDataList").keyup(function () {

  //   let currentValue = $(this).val();

  //   if(currentValue.length == 0) {
  //       $("#datalistOptions").html("");
  //       return false;
  //   }

  //   $.ajax({
  //       url: "ajax/autocomplete.php",
  //       type: "GET",
  //       dataType: "json",
  //       data: { "myInputValue": currentValue }
  //   }).done(function (data) {
  //     console.log(data);
  //       let listOptions = "";
  //       $.each(data, function (index, value) {
  //           listOptions += "<option> " + value + " </option>";
  //       });

  //       $("#datalistOptions").html(listOptions);
  //   });

  // });

  // Data Tables
  $("#tableAnnonce").DataTable({
    "scrollX": true,
  
    language: {
      url: "../media/datatablefrench.json",
    },
    columns: [
      { type: "num" },
      { type: "text" },
      { orderable: false },
      { orderable: false },
      { type: "num" },
      { orderable: false },
      { type: "text" },
      { type: "text" },
      { type: "text" },
      { type: "text" },
      { type: "text" },
      { type: "text" },
      { type: "date" },
      { orderable: false },
    ],
  });

  $("#tableCommentaire").DataTable({
    language: {
      url: "../media/datatablefrench.json",
    },
    columns: [
      { type: "num" },
      { type: "text" },
      { type: "text" },
      { orderable: false },
      { type: "date" },
      { orderable: false },
    ],
  });

  $("#tableNote").DataTable({
    language: {
      url: "../media/datatablefrench.json",
    },
    columns: [
      { type: "num" },
      { type: "text" },
      { type: "text" },
      { type: "num" },
      { orderable: false },
      { type: "date" },
      { orderable: false },
    ],
  });

  // Modal pour contacter l'auteur de l'annonce
  if (document.getElementById("modalContact")) {
    var modalContact = document.getElementById("modalContact");
    modalContact.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      var button = event.relatedTarget;
      // Extract info from data-bs-* attributes
      var recipient = button.getAttribute("data-bs-whatever");
      // Update the modal's content.
      var modalTitle = modalContact.querySelector(".modal-title");
      modalTitle.textContent = "Contacter " + recipient;
      var modalObjetInput = modalContact.querySelector(".modal-body input");
      var modalBodyInput = modalContact.querySelector(".modal-body textarea");
      document.getElementById("envoyer").addEventListener('click', function(){
        if(!modalObjetInput.value){
          window.alert('Vous devez saisir un objet au message');
        }
        else if(!modalBodyInput.value){
          window.alert('Vous devez saisir un message');
        }
        else {
          console.log('fr');
          $("#modalContact").modal('hide');
        }
      });
    });

  }

  //lightBox pour les photos supplémentaires
  $(".lightbox img").click(function () {
    var $body = $("body");
    var $imgHref = $(this).attr("src");
    var $lightbox = $('<div id="lightbox">');
    var $lightboxImage = $("<img>").attr("src", $imgHref); // Nous nous créons une nouvelle balise <img> à laquelle nous associons l'attribut "src" que nous avons récupéré
    $lightbox.append($lightboxImage);
    $lightbox.fadeIn(200);
    $body.append($lightbox); // Le contenu généré sera automatiquement ajouté avant la fermeture de la balise <div>
    $("#lightbox").on("click", function (remove) {
      // Lorsque l'utilisateur clique en dehors de l'image, la lightbox se ferme et est supprimée
      if (remove.target == this) {
        //La fermeture au clique ne fonctionne qu'en dehors de l'image
        $lightbox.fadeOut(200, function () {
          $("#lightbox").remove(); // Après avoir fait disparaître l'image on supprime la lightbox
        });
      }
    });
  });

 // Obtenir id numérique des étoiles au format star_numero
  function idNum(id) {
    var id = id.split("_");
    var id = id[1];
    return id;
  }

  
  if (document.getElementById("notation")) {

    // Ouverture de la modal uniquement si connecté
     $("#ouvertureModal").click(function(e){
      if(!$("#ouvertureModal").attr('data-index')){
        window.alert(
          "Vous devez être connecté pour déposer un commentaire ou une note."
        );
      } else {
        e.preventDefault();
        $("#modalNote").modal("show");
      }
    });

    // Attribuer une note au survol des étoiles
    let note = 0;
    $(".fa-star").hover(function () {
      id = idNum($(this).attr("id")); // id numérique de l'étoile survolée
      var nbStars = $(".fa-star").length; // Nombre d'étoiles de la classe .fa-star
      var i;
      console.log(note);
      for (i = 0; i <= nbStars; i++) {
        if (i <= id) $("#star_" + i).attr({ class: "fas fa-star" });
        else if (i > id) $("#star_" + i).attr({ class: "far fa-star" });
        if (i == id) note = i; // affectation de la note
      }
    });
    // Enregistrement de la note
    $("#depot").click(function (e) {
      let vendeur = $("#vendeur").attr("data-index");
      let id_annonce = $("#id_annonce").attr("data-index");
      let commentaire = $("#commentaire-text").val();
      let avis = $("#avis").val();
      console.log(vendeur);
      console.log(id_annonce);
      console.log(commentaire);
      console.log(avis);
      if (!(commentaire == "" && note == 0 && avis == "")) {
        $.ajax({
          url: "ajax/notation.php",
          type: "POST",
          dataType: "json",
          data: {
            commentaire: commentaire,
            note: note,
            avis: avis,
            id_annonce: id_annonce,
            vendeur: vendeur,
          },
        })
          .done(function (datas) {
            console.log(datas);
            $("#modalNote").modal("hide");
          })
          .fail(function (error) {
            console.log(error);
          });
      } else {
        return window.alert(
          "Vous n'avez saisi aucun commentaire, ni aucune note..."
        );
      }
    });
  }

  // Selection et Affichage des miniatures en grand sur l'annonce
  $(".miniature:first").css("border", "3px solid #ffaf00");

  $(".miniature").on("click", function () {
    $(".miniature").css("border", "3px solid white");
    $(this).css("border", "3px solid #ffaf00");
    let nom = $(this).attr("id");
    $("#grand").attr("src", "images/" + nom);
  });
});
