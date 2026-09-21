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