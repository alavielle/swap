$(function(){
    const faqs = 
    [
        {
            question: 'Informations légales', 
            answer: 'Le site Internet swap, ci-après dénommé « swap.alcedi.fr » propose un service de dépôt et de consultation de petites annonces sur Internet plus spécifiquement destiné aux particuliers. L\'accès au site, sa consultation et son utilisation sont subordonnés à l\'acceptation sans réserve des présentes Conditions Générales d\'Utilisation'
        },

        {
            question: 'Objet', 
            answer: 'Les présentes Conditions Générales d\'Utilisation (CGU) ont pour objet de déterminer les conditions d\'utilisation du Service SWAP mis à disposition des Utilisateurs et des Annonceurs '
        },
        {
            question: 'Acceptation', 
            answer: 'Tout Utilisateur - tout Annonceur, tout Acheteur, tout Vendeur, tout Hôte et tout Voyageur déclare en accédant et utilisant le service LEBONCOIN et/ou le Service de Paiement Sécurisé, depuis le Site et/ou les Applications, avoir pris connaissance des présentes Conditions Générales d’Utilisation et les accepter expressément sans réserve et/ou modification de quelque nature que ce soit. Les présentes CGU sont donc pleinement opposables aux Utilisateurs, aux Annonceurs, aux Acheteurs, aux Vendeurs, aux Hôtes et aux Voyageurs.'
        },
        {
            question: 'Données à caractère personnel',
            answer: 'Tout traitement de données personnelles dans le cadre des présentes est soumis aux dispositions de notre politique de confidentialité, qui fait partie intégrante des présentes CGU.'
        },
        {
            question: 'Utilisation du service', 
            answer: "Tout Utilisateur – tout Annonceur déclare être informé qu’il devra, pour accéder au Service SWAP, disposer d’un accès à l’Internet souscrit auprès du fournisseur de son choix, dont le coût est à sa charge.            L’Annonceur s’engage, le cas échéant, à respecter et à maintenir la confidentialité des Identifiants de connexion à son compte et reconnaît expressément que toute connexion à son compte, ainsi que toute transmission de données depuis son compte sera réputée avoir été effectuée par l’Annonceur. Toute perte, détournement ou utilisation des Identifiants de connexion et leurs éventuelles conséquences relèvent de la seule et entière responsabilité de l’Annonceur. L'Annonceur est informé et accepte que pour des raisons d'ordre technique, son Annonce ne sera pas diffusée instantanément après son dépôt sur le Site et les Applications. L'Annonceur est informé qu'en publiant son Annonce sur le Site, celle-ci est susceptible d'être partagée par tout Utilisateur et/ou tout Annonceur. Toute Annonce publiée sera diffusée sur le Site et les Applications. "},
  {
    question: 'Modération des annonces', 
    answer: ' SWAP se réserve le droit de supprimer, sans préavis ni indemnité ni droit à remboursement, toute Annonce qui ne serait pas conforme aux règles de diffusion du Service et/ou qui serait susceptible de porter atteinte aux droits d\'un tiers.'
  },

    ];

// On génére les questions  dans la section accordion-list
for(let faq of faqs){
    $('#accordion-list').append(`
    <div class="accordion-item" >
     <a href="#" class="question btn">
      ${faq.question}<i class="fa fa-angle-right"></i>
    </a>
    <p class="answer">${faq.answer}</p>
    </div>
`);
}

// Selection des questions
let items = $('a.question');


// fonction
function showQuestion(){
    // J'ajoute la classe active qd je clique sur la question
    $(this).toggleClass('active');
    // J'affiche l'elt suivant (frere) en slide avec animation
    $(this).next().slideToggle(300);
    // Je retire la class active à toutes les autres questions
    items.not($(this)).removeClass('active');
    // Je replie ts les réponses des autres questions
    $('p.answer').not($(this).next()).slideUp(300);
}

items.on('click',showQuestion);

// Bonus : faire apparaitre en fadeIn chaque lien de la liste avec un interval de 150ms

let inter = 0;
$('.accordion-item').each(function(){
    // A chaque aparition , rajoute 150ms à inter
    inter += 150;
    $(this).delay(inter).fadeIn();
})


});/////////////////////////////////////// 
