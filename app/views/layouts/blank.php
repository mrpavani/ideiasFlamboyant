<?php /** @var string $content */ ?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Idéias Flamboyant') ?></title>
<link rel="icon" href="<?= asset('img/logo.png') ?>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=3">
</head>
<body class="admin-auth">
<?= $content ?>
</body>
</html>
