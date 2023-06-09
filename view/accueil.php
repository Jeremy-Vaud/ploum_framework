<div class="my-32 text-center">
    <h4>Template de la page d'acceuil</h4>
    <p>Modifier le template de la page d'accueil dans le fichier view/acceuil.php</p>
    <br>
    <h6><?= htmlentities($example) ?></h6>
    <?php foreach($articles as $art): ?>
        <p> <?= htmlentities($art->get("titre") ?? "") ?>
    <?php endforeach; ?>
</div>