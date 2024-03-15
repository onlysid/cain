<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $route->title;?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="css/output.css" rel="stylesheet">
</head>
<body class="bg-blue-100">
    <?php if($route->showMenu) {
        include 'templates/menu.php';
    }?>

    <div class="content">
        <?php include $route->view; ?>
    </div>

    <?php if($route->showMenu) {
        include 'templates/footer.php';
    }?>
</body>
</html>
