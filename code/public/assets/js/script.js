const burger = document.getElementById('burger');
const navigation = document.getElementById('navigation');

if (burger && navigation) {
    burger.addEventListener('click', function () {
        navigation.classList.toggle('ouverte');
        burger.classList.toggle('actif');
    });
}