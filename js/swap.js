document.addEventListener("DOMContentLoaded", function () {
  const euro = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 2,
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
  /* Affichage temporaire des messages d'alerte*/
  function messageOff() {
    if (document.querySelector(".disparition")) {
      setTimeout(function () {
        document.querySelector(".disparition").style.display = "none";
      }, 8000);
    }
  }

  /* Appel de la fonction */
  messageOff();

  // Prévisu de l'image à la sélection du fichier
  for (i = 1; i < 6; i++) {
    if (document.getElementById("preview" + i)) {
      upLoadPhoto(i);
      affichePhotoVignette(i);
    }
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
              document
                .getElementById(nom_original)
                .setAttribute("value", fichier.name);
              document
                .getElementById(data_img)
                .setAttribute("value", e.target.result);
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

  // Clic sur la croix de suppression des photos à la modification de l'annonce
  $(".suppr").click(function (e) {
    e.stopPropagation();
    e.preventDefault();
    numToDel = idNum($(this).attr("id")); // id numérique de la photo survolée
    $("#toDel" + numToDel).val("toDelete");
    remettreVignetteDefaut(numToDel);
  });

  function remettreVignetteDefaut(numPhoto) {
    document
      .querySelector("#preview" + numPhoto + " img")
      .setAttribute(
        "src",
        "https://via.placeholder.com/100?text=Photo" + numPhoto
      );
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

  // Si click sur le nom d'un membre ds une annonce, renvoi sur page d'accueil filtrée
  // if (document.getElementById("id_membre")) {
  //   if ($("#id_membre").val() > 0) {
  //     window.onload = filtre_et_tri();
  //   }
  // }

  // Filtres et tris de la page d'accueil en revenant page 1

  $("#tri").change(filtre_et_tri);

  $("#id_categorie").change(filtre_et_tri);

  $("#id_membre").change(filtre_et_tri);

  $("#region").change(filtre_et_tri);

  $("#rangePrix").change(function () {
    $("#prixMax").html("maximum : " + euro.format(this.value));
    filtre_et_tri();
  });
  $(".numeros").click(function (e) {
    e.preventDefault();
    let page = idNum($(this).attr("id"));
    $(".pageCourante").attr("id", "page_" + parseInt(page));
    filtre_et_tri();
  });
  $("#previous").click(function (e) {
    e.preventDefault();
    if (!$(this).hasClass('disabled')){
      let page = idNum($(".pageCourante").attr("id")); 
      $(".pageCourante").attr("id", "page_" + (parseInt(page) - 1));
      filtre_et_tri();
    }
  });
  $("#next").click(function (e) {
      e.preventDefault();
      if (!$(this).hasClass('disabled')) {
      let page = idNum($(".pageCourante").attr("id"));
      $(".pageCourante").attr("id", "page_" + (parseInt(page) + 1));
      filtre_et_tri();
    }
  });
  function filtre_et_tri() {
    let id_categorie = $("#id_categorie").val();
    let id_membre = $("#id_membre").val();
    let region = $("#region").val();
    let prix = $("#rangePrix").val();
    let id_tri = $("#tri").val();
    let selectPage = idNum($(".pageCourante").attr("id"));
    let nbDePages = parseInt($("#nbDePages").attr("data-index"));
    let nbEltsParPage = $("#nbAnnoncesParPage").val();
    let premierEltDeLaPage = (selectPage - 1) * nbEltsParPage;
    $.ajax({
      url: "ajax/filtre_et_tri.php",
      type: "POST",
      dataType: "json",
      data: {
        id_categorie: id_categorie,
        id_membre: id_membre,
        region: region,
        prix: prix,
        id_tri: id_tri,
      },
    })
      .done(function (datas) {
        // On vide le contenu de la table et on le re-rempli ds l'ordre du tri
        $("#tbody").html("");
        $("#modalTemp").html("");
        $("#previous").html("");
        for (let i = 1; i <= nbDePages; i++) {
          $(`#numero_${i}`).html("");
        }
        $("#next").html("");
        let nb = datas.length;
        let nbPages = Math.ceil(nb / nbEltsParPage);
        let dernier = nbEltsParPage * selectPage;
        if (nbEltsParPage * selectPage > nb) {
          dernier = nb;
        }
        for (let i = premierEltDeLaPage; i < dernier; i++) {
          let id_annonce = datas[i]["id_annonce"];
          let photo = datas[i]["photo"];
          let titre = datas[i]["titre"];
          let description_courte = datas[i]["description_courte"];
          let description = datas[i]["description_longue"].substr(0, 200);
          if (datas[i]["description_longue"].length > 200) {
            description += " ...";
          }
          let pseudo = datas[i]["pseudo"];
          let id_membre = datas[i]["id_membre"];
          let prix = euro.format(datas[i]["prix"]);

          remplissageBody(
            id_annonce,
            photo,
            titre,
            description_courte,
            description,
            pseudo,
            id_membre,
            prix
          );
        }

        let pluriel = "";
        if (nb > 1) pluriel = "s";
        $("#nb").html(nb + " résultat" + pluriel);
        majPagination(selectPage, nbPages);
      })
      .fail(function (error) {
        console.log(error);
      });
  }

  // fonction de remplissage de la div tbody
  function remplissageBody(
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
      url: "ajax/moyenne.php",
      type: "POST",
      dataType: "json",
      data: {
        id_membre: id_membre,
      },
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
        for (i = Math.ceil(moyenne); i < 5; i++) {
          etoiles += '<i class="far fa-star"></i>';
        }
        let tbody = `
          <tr>
            <td class="col-md-3">
                <div class="hauteur"><img src="images/${photo}" alt="${titre}" class="mx-auto d-block" height="100%"></div>
            </td>
            <td class="col-md-9">
                <div class="d-flex justify-content-between pb-2">
                    <span class="d-inline"><a href="annonce.php?id=${id_annonce}"" id="tbodyTitre" class="fw-bold text-decoration-none text-lien">${titre}</a></span>
                    <span class="fw-bold col-md-2 d-inline text-end">${prix}</span>
                </div>
                <div class="d-flex">
                    <p>${description_courte}<br>
                    ${description}
                    </p>
                </div>
                <div class="d-flex align-items-end" data-attr="${id_membre}" >
                    <p class="mt-3 text-lien">${pseudo}
                    ${etoiles}
                    <span class="hidden" id="moyenne">${moyenne}</span></p>
                </div>
            </td> `;
        $("#tbody").append(tbody);
      })
      .fail(function (error) {
        console.log(error);
      });
  }

  // fonction de mise à jour de la pagination
  function majPagination(selectPage, nbPages) {
    let premiere = "";
    if (selectPage == 1) {
      premiere = "disabled";
      $("#previous").addClass("disabled");
    } else {
      $("#previous").removeClass("disabled");
    }
    let derniere = "";
    if (selectPage == nbPages) {
      derniere = "disabled";
      $("#next").addClass("disabled");
    } else {
      $("#next").removeClass("disabled");
    }
    $("#previous").append(`
      <li class="page-item ${premiere}">
      <a class="page-link " href="" aria-label="Previous">
          <span aria-hidden="true">&laquo;</span>
      </a>
      </li> `);
    for (i = 1; i <= nbPages; i++) {
      let active = "";
      let courante = "";
      if (selectPage == i) {
        active = "active";
        courante = "pageCourante";
      }
      $(`#numero_${i}`).append(`
            <li class="page-item ${active} ${courante}" id="page_${i}"><a class="page-link" href="">${i}</a></li>
          `);
    }
    $("#next").append(`
      <li class="page-item ${derniere} ?>">
      <a class="page-link" href="" aria-label="Next">
          <span aria-hidden="true">&raquo;</span>
      </a>
      </li>
    </ul>
    `);
  }

  // Ouverure d'une modale sur clic sur le vendeur pour afficher notes et avis
  //  $(".listeAvis").on("click", function (e){
  // document.querySelectorAll('.listeAvis').forEach((elt) => {
  //   console.log(elt);
  //   elt.addEventListener('click', (e) => {
  //     console.log(e);
  //   })
  // })

  //     elt.addEventListener('click', function (e) {

  //   // e.preventDefault();
  //    // id numérique du membre survolé
  //   id = $(this).attr("data-attr");

  //   if($("#modalMembre").show){
  //     $('#tousLesAvis').html("");

  //     $.ajax({
  //       url: "ajax/avis.php",
  //       type: "POST",
  //       dataType: "json",
  //       data: {
  //         id_membre: id,
  //       },
  //       })
  //       .done(function (lesAvis) {
  //         console.log(lesAvis);
  //         if(lesAvis.length > 0){
  //           $.each(lesAvis, function (value) {

  //             var listAvis = "";
  //             listAvis += `<div class="row mt-2">
  //             <div class="col-6 couleur-perso fst-italic fs-6">Par ${lesAvis[value]['pseudo']} le ${lesAvis[value]['dateFr']}
  //             </div>
  //             <div class="col-6 text-end couleur-perso">`;

  //                 for (i = 0; i < lesAvis[value]['note']; i++) {
  //                   listAvis += `<i class="fas fa-star"></i> `;
  //                 }
  //                 for (i = lesAvis[value]['note']; i < 5; i++) {
  //                   listAvis += `<i class="far fa-star"></i>`;
  //                 }
  //                 listAvis += `</div>
  //             </div>
  //             <div>
  //                 ${lesAvis[value]['avis']}
  //             </div>`;

  //             $('#tousLesAvis').append(listAvis);
  //           });
  //         } else {
  //           $('#tousLesAvis').append("Pas encore d'avis déposé...");
  //         }
  //       });
  //     }
  //   });
  // });



  // Statistiques
  //
  // Afficher les meileures notes au chargement de la page
  if (document.getElementById("meilleuresNotes")) {
    window.onload = afficheTopNotes();
  }

  // sur click, affiche les membres les mieux notés
  $("#meilleuresNotes").click(afficheTopNotes);

  function afficheTopNotes(e) {
    let currentValue = $("#topNotes").attr("id");
    $.ajax({
      url: "../ajax/top_membre.php",
      type: "POST",
      dataType: "json",
      data: { myInputValue: currentValue },
    }).done(function (datas) {
      let nb = datas.length;
      if (nb > 0) {
        $("#topNotes").html("");
        let num = 0;
        datas.forEach(function (value) {
          num++;
          let nb_etoiles = Math.round(value["moyenne"] * 100) / 100;
          let pluriel = "";
          if (nb_etoiles > 1) {
            pluriel = "s";
          }
          let statPhrase =
            nb_etoiles +
            " étoile" +
            pluriel +
            " basé sur " +
            value["nb_avis"] +
            " avis";
          remplissageTop(
            currentValue,
            num,
            value["nom"],
            value["prenom"],
            statPhrase
          );
        });
      }
    });
  }
  // sur click, affiche les membres les plus actifs
  $("#plusActifs").click(function (e) {
    let currentValue = $("#topActifs").attr("id");
    $.ajax({
      url: "../ajax/top_actif.php",
      type: "POST",
      dataType: "json",
      data: { myInputValue: currentValue },
    }).done(function (datas) {
      let nb = datas.length;
      if (nb > 0) {
        $("#topActifs").html("");
        let num = 0;
        datas.forEach(function (value) {
          num++;
          let pluriel = "";
          if (value["nb_annonces"] > 1) {
            pluriel = "s";
          }
          let statPhrase =
            value["nb_annonces"] +
            " annonce" +
            pluriel +
            " postée" +
            pluriel +
            " depuis le " +
            value["date_origine"];
          remplissageTop(
            currentValue,
            num,
            value["nom"],
            value["prenom"],
            statPhrase
          );
        });
      }
    });
  });
  // sur click, affiche les plus anciennes annonces
  $("#plusAnciens").click(function (e) {
    let currentValue = $("#topAnciens").attr("id");
    $.ajax({
      url: "../ajax/top_ancien.php",
      type: "POST",
      dataType: "json",
      data: { myInputValue: currentValue },
    }).done(function (datas) {
      let nb = datas.length;
      if (nb > 0) {
        $("#topAnciens").html("");
        let num = 0;
        datas.forEach(function (value) {
          num++;
          let statPhrase =
            " déposée le " +
            value["date_origine"] +
            " par " +
            value["prenom"] +
            " " +
            value["nom"];
          remplissageTop(currentValue, num, value["titre"], "", statPhrase);
        });
      }
    });
  });
  // sur click, affiche les catégories les plus représentées
  $("#meilleuresCategories").click(function (e) {
    let currentValue = $("#topCategories").attr("id");
    $.ajax({
      url: "../ajax/top_categorie.php",
      type: "POST",
      dataType: "json",
      data: { myInputValue: currentValue },
    }).done(function (datas) {
      let nb = datas.length;
      if (nb > 0) {
        $("#topCategories").html("");
        let num = 0;
        datas.forEach(function (value) {
          num++;
          let pluriel = "";
          if (value["nb_annonces"] > 1) {
            pluriel = "s";
          }
          let statPhrase =
            " avec " +
            value["nb_annonces"] +
            " annonce" +
            pluriel +
            " en ligne";
          remplissageTop(currentValue, num, value["titre"], "", statPhrase);
        });
      }
    });
  });

  // On remplis la div "top" en fonction du choix utilisateur
  function remplissageTop(currentValue, num, nom, prenom, statPhrase) {
    $("#" + currentValue).append(`
      <div class="row mb-3">
      <div class="col-6">
          ${num} - ${prenom} ${nom}
      </div>
      <div class="col-6 bg-secondary rounded-pill text-light text-center">
        ${statPhrase}
      </div>
  </div>
    `);
  }

  // fonction de recherche
  $("#recherche").click(function (e) {
    let currentValue = $("#myDataList").val();
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
            $("#zonePagination").html("");
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

              remplissageBody(
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
  $("#myDataList").keyup(function () {
    let currentValue = $(this).val();
    if (currentValue.length == 0) {
      $("#datalistOptions").html("");
      return false;
    }

    $.ajax({
      url: "ajax/autocomplete.php",
      type: "GET",
      dataType: "json",
      data: { myInputValue: currentValue },
    }).done(function (data) {
      let listOptions = "";
      $.each(data, function (index, value) {
        listOptions += "<option> " + value.titre + " </option>";
      });
      $("#datalistOptions").html(listOptions);
    });
  });

  // Data Tables
  $("#tableAnnonce").DataTable({
    scrollX: true,

    language: {
      url: "../media/datatablefrench.json",
    },
    order: [[0, "desc"]],
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
      { type: "text" },
      { type: "text" },
      { orderable: false },
      { orderable: false },
      { type: "date" },
      { orderable: false },
    ],
  });

  $("#tableAnnonceProfil").DataTable({
    language: {
      url: "media/datatablefrench.json",
    },
    scrollX: false,
    searching: false,
    dom: "tip",
    columns: [
      { type: "date" },
      { orderable: false },
      { orderable: false },
      { orderable: false },
    ],
  });

  $("#tableNoteProfil").DataTable({
    language: {
      url: "media/datatablefrench.json",
    },
    searching: false,
    dom: "tip",
    columns: [
      { type: "text" },
      { type: "text" },
      { orderable: false },
      { type: "date" },
      { orderable: false },
    ],
  });

  // Modal pour contacter l'auteur de l'annonce
  if (document.getElementById("modalContact")) {
    let modalContact = document.getElementById("modalContact");
    modalContact.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      let button = event.relatedTarget;
      // Extract info from data-bs-* attributes
      let recipient = button.getAttribute("data-bs-whatever");
      // Update the modal's content.
      let modalTitle = modalContact.querySelector(".modal-title");
      modalTitle.textContent = "Contacter " + recipient;
    });
  }

  //lightBox pour les photos supplémentaires
  $(".lightbox img").click(function () {
    let $body = $("body");
    let $imgHref = $(this).attr("src");
    let $lightbox = $('<div id="lightbox">');
    let $lightboxImage = $("<img>").attr("src", $imgHref); // Nous nous créons une nouvelle balise <img> à laquelle nous associons l'attribut "src" que nous avons récupéré
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

  // Obtenir id numérique des étoiles au format star_numero ou des croix pour la suppression au format suppr_numero
  function idNum(numero) {
    numero = numero.split("_");
    numero = numero[1];
    return numero;
  }

  if (document.getElementById("notation")) {
    // Ouverture de la modal uniquement si connecté
    $("#ouvertureModal").click(function (e) {
      if (!$("#ouvertureModal").attr("data-index")) {
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
    $(".noter .fa-star").hover(function () {
      id = idNum($(this).attr("id")); // id numérique de l'étoile survolée
      let nbStars = $(".fa-star").length; // Nombre d'étoiles de la classe .fa-star
      let i;
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

  // Enregistrement de la réponse au commentaire
  // Ouverture de la modal
  $(".ouvertureModalReponse").click(function (e) {
    e.preventDefault();
    let id_annonce = $(this).attr("data-index");
    $("#modalReponse" + id_annonce).modal("show");
  });

  $(".reponse").click(function (e) {
    let id_annonce = $(this).attr("data-index");
    let commentaire = $("#commentaire-text" + id_annonce).val();

    if (!(commentaire == "")) {
      $.ajax({
        url: "ajax/notation.php",
        type: "POST",
        dataType: "json",
        data: {
          commentaire: commentaire,
          id_annonce: id_annonce,
        },
      })
        .done(function (datas) {
          $("#modalReponse" + id_annonce).modal("hide");
        })
        .fail(function (error) {
          console.log(error);
        });
    } else {
      return window.alert("Vous n'avez pas saisi de réponse...");
    }
  });

  $(".avis").click(function (e) {
    e.preventDefault();
    id = idNum($(this).attr("id")); // id numérique du membre survolé
    $("#modalMembre" + id).modal("show");
  });

  // Selection et Affichage des miniatures en grand sur l'annonce
  $(".miniature:first").css("border", "3px solid #ffaf00");

  $(".miniature").on("click", function () {
    $(".miniature").css("border", "3px solid white");
    $(this).css("border", "3px solid #ffaf00");
    let nom = $(this).attr("id");
    $("#grand").attr("src", "images/" + nom);
  });

  // API VIa Michelin pour récupérer les coordonnées GSP de l'adresse
  if (document.getElementById("geolocalisation")) {
    let cp_ville = document.getElementById("geolocalisation").value;
    let output = document.getElementById("output");
    let conf = {
      singleFieldSearch: cp_ville
    };
    output.innerHTML = "";
    let displayError = function () {
      output.innerHTML = "<div>Une erreur s'est produite.</div>";
    };

    let callbacks = {
      onSuccess: function (results) {
        let coords = results[0].coords;
        let conf2 = {
          container: $_id("mapContainer"),
          scrollwheel: false,
          markerControl: true,
          center: {
            coords: {
              lon: coords.lon,
              lat: coords.lat,
            },
          },
          zoom: 15,
        };
        VMLaunch("ViaMichelin.Api.Map", conf2);
      },
      onError: displayError,
      onInitError: displayError,
    };
    VMLaunch("ViaMichelin.Api.Geocoding", conf, callbacks);
  }
});
