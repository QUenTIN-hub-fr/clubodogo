const burger = document.getElementById('burger');
const navigation = document.getElementById('navigation');

if (burger && navigation) {
    burger.addEventListener('click', function () {
        navigation.classList.toggle('ouverte');
        burger.classList.toggle('actif');
    });
}

const formulairesAConfirmer = document.querySelectorAll('form[data-confirmation]');

formulairesAConfirmer.forEach(function (formulaire) {
    formulaire.addEventListener('submit', function (evenement) {
        if (!confirm(formulaire.dataset.confirmation)) {
            evenement.preventDefault();
        }
    });
});

const boutonsMotDePasse = document.querySelectorAll('.afficher-mdp');

boutonsMotDePasse.forEach(function (bouton) {
    bouton.addEventListener('click', function () {
        const champ = document.getElementById(bouton.dataset.cible);

        if (champ.type === 'password') {
            champ.type = 'text';
            bouton.textContent = 'Masquer';
        } else {
            champ.type = 'password';
            bouton.textContent = 'Afficher';
        }
    });
});