<?php
include('cfg.php');

/**
 * Strona główna projektu.
 * Zawiera nagłówki HTML, importuje style i skrypty, oraz wyświetla nawigację, treść strony i stopkę.
 */
?>
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
    // Sprawdza, czy zmienna 'page' została przekazana za pomocą metody GET, jeśli nie, ustawia domyślną wartość na '1'.
    $page = isset($_GET['page']) ? $_GET['page'] : '1';

    // Importuje plik zawierający funkcję PokazPodstrone i wywołuje ją, przekazując numer strony.
    include('showpage.php');
    echo PokazPodstrone($page);
    ?>
</div>

<?php include('pages/footer.php'); ?>

<script src="js/clock.js"></script>
</body>
</html>