<!DOCTYPE html>
<html lang="<?= htmlentities($this->lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= $this->getRoot() . htmlentities($this->favicon)?>">
    <meta name="description" content="<?= htmlentities($this->meta)?>"/>
    <title><?= htmlentities($title) ?></title>
    <?= $styles ?>
</head>
<body>
    <?php include $this->header ?>
    <main class="overflow-hidden">
        <?= $this->main ?>
    </main>
    <?php include $this->footer ?>
    <?= $scripts ?>
</body>
</html>