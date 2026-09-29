<?php
require_once __DIR__ . '/../init.php';

$titrePage = 'Mentions légales';
require_once __DIR__ . '/../header.php';
?>

<h1>Mentions légales</h1>

<div class="texte-legal">

    <p class="avertissement">
        Clubodogo est un site fictif, réalisé dans le cadre d'un projet de formation.
        Le club, les coordonnées et les personnes mentionnées sur ce site n'existent pas.
        Aucune inscription réelle ne peut être effectuée.
    </p>

    <h2>Éditeur du site</h2>
    <p>
        Site édité par BALTES Quentin, dans le cadre du projet de fin de formation
        au titre professionnel Développeur Web et Web Mobile.
    </p>

    <h2>Hébergement</h2>
    <p>
        Ce site n'est pas hébergé. Il s'agit d'une application fonctionnant en environnement
        local, développée à des fins pédagogiques et présentée devant un jury de certification.
    </p>

    <h2>Coordonnées du club</h2>
    <p>
        Clubodogo<br>
        12 rue des Peupliers<br>
        57000 Metz<br>
        contact@clubodogo.fr<br>
        06 12 34 56 78
    </p>

    <h2>Données personnelles collectées</h2>
    <p>
        La création d'un compte sur le site nécessite la saisie des informations suivantes :
        nom, prénom, adresse électronique et mot de passe. L'enregistrement d'un chien
        nécessite son nom, sa date de naissance, son sexe, sa race et son numéro
        d'identification.
    </p>
    <p>
        Ces données sont utilisées uniquement pour la gestion des comptes et des inscriptions
        aux cours. Elles ne font l'objet d'aucune transmission à des tiers, d'aucune revente
        et d'aucun traitement publicitaire.
    </p>

    <h2>Sécurité des données</h2>
    <p>
        Les mots de passe ne sont jamais conservés en clair : ils sont transformés par une
        fonction de hachage avant leur enregistrement. Il est donc impossible de les retrouver,
        y compris pour l'administrateur du site.
    </p>
    <p>
        Les accès aux différentes parties de l'application sont contrôlés selon le rôle de
        chaque utilisateur, et toutes les requêtes vers la base de données sont préparées afin
        de prévenir les injections SQL.
    </p>

    <h2>Durée de conservation</h2>
    <p>
        Les données sont conservées tant que le compte de l'utilisateur est actif. La
        suppression d'un chien entraîne la suppression des inscriptions qui lui sont
        rattachées.
    </p>

    <h2>Vos droits</h2>
    <p>
        Conformément au Règlement général sur la protection des données, vous disposez d'un
        droit d'accès, de rectification, d'effacement et d'opposition concernant les données
        vous concernant. Ces droits s'exercent par courrier électronique à l'adresse de
        contact indiquée ci-dessus.
    </p>

    <h2>Cookies</h2>
    <p>
        Le site utilise uniquement un cookie de session, strictement nécessaire à son
        fonctionnement : il permet de maintenir la connexion d'un utilisateur pendant sa
        visite. Aucun cookie de mesure d'audience ni de publicité n'est déposé.
    </p>

</div>

<?php require_once __DIR__ . '/../footer.php'; ?>