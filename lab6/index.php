<?php include('cfg.php'); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona PHP</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<?php include('pages/navigation.php'); ?>

<div class="content-container">
<?php
    $page = isset($_GET['page']) ? $_GET['page'] : '1';
    include('showpage.php');
    echo PokazPodstrone($page);
?>
</div>

<?php include('pages/footer.php'); ?>

<script src="js/clock.js"></script>
</body>
</html>
