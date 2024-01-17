
<?php
session_start();

include("cfg.php");

$conn = $GLOBALS['conn'];

// Funkcja dodająca produkt do koszyka
function addToCart($conn, $productId, $quantity) {
    $sql = "SELECT * FROM produkty WHERE id = $productId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Sprawdź dostępność produktu w magazynie
        if ($row['ilosc_w_magazynie'] >= $quantity) {
            // Oblicz cenę brutto
            $cena_brutto = $row['cena_netto'] + ($row['cena_netto'] * $row['podatek_vat']);

            $productData = [
                'id' => $row['id'],
                'tytuł' => $row['tytul'],
                'quantity' => $quantity,
                'cena_netto' => $row['cena_netto'],
                'podatek_vat' => $row['podatek_vat'],
                'cena_brutto' => $cena_brutto,
            ];

            // Dodaj produkty do koszyka w sesji
            $_SESSION['cart'][$productId] = $productData;
        } else {
            echo "Nie wystarczająca ilość produktu w magazynie.";
        }
    }
}

// Funkcja usuwająca produkt z koszyka
function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

// Wyświetl formularz usuwania produktów z koszyka
function showRemoveForm() {
    echo "<h2>Usuń z koszyka:</h2>";
    echo "<form method='post'>";
    echo "<table border='1'>";
    echo "<tr><th>ID Produktu</th><th>Tytuł</th><th>Usuń</th></tr>";

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $productData) {
            echo "<tr>";
            echo "<td>" . $productData['id'] . "</td>";
            echo "<td>" . $productData['tytul'] . "</td>";
            echo "<td><button type='submit' name='removeId' value='$productId'>Usuń</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='3'>Koszyk jest pusty</td></tr>";
    }

    echo "</table>";
    echo "</form>";
}

// Funkcja wyświetlająca koszyk
function showCartDetails($conn) {
    echo "<h2>Koszyk:</h2>";
    echo "<form method='post'>";
    echo "<table>";
    echo "<tr><th>Tytuł</th><th>Opis</th><th>Cena netto</th><th>Podatek Vat</th><th>Cena brutto</th><th>Gabaryt</th><th>Zdjęcie</th><th>Kategoria</th><th>Ilość</th><th>Należność</th><th>Akcja</th></tr>";

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $productData) {
            $productId = $productData['id'];

            $sql = "SELECT p.*, k.nazwa AS nazwa_kategorii FROM produkty p INNER JOIN kategorie k ON p.kategoria = k.id  WHERE p.id = $productId ORDER BY p.id ASC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['tytul'] . "</td>";
                    echo "<td>" . $row['opis'] . "</td>";
                    echo "<td>" . $row['cena_netto'] . "zł</td>";
                    echo "<td>" . $row['podatek_vat'] . "</td>";
                    echo "<td>" . $productData['cena_brutto'] . "zł</td>";
                    echo "<td>" . $row['gabaryt'] . "</td>";
                    echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['zdjecie']) . "' width='100' height='100'></td>";
                    echo "<td>" . $row['nazwa_kategorii'] . "</td>";
                    echo "<td>" . $productData['quantity'] . "szt.</td>";
                    echo "<td>" . ($productData['cena_brutto'] * $productData['quantity']) . "zł</td>";
                    echo "<td><button type='submit' name='removeId' value='$productId'>Usuń</button></td>";
                    echo "</tr>";
                }
            }
        }
    } else {
        echo "<tr><td colspan='12'>Koszyk jest pusty</td></tr>";
    }

    echo "</table>";
    echo "</form>";
}

// Obsługa dodawania produktu do koszyka
if (isset($_POST['addId']) && isset($_POST['quantity'])) {
    $productId = $_POST['addId'];
    $quantity = $_POST['quantity'];
    addToCart($conn, $productId, $quantity);
}

if (isset($_POST['removeId'])) {
    removeFromCart($_POST['removeId']);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

function resetCart() {
    $_SESSION['cart'] = [];
}

// Obsługa złożenia zamówienia
if (isset($_POST['placeOrder'])) {
    // Zmniejsz ilość produktu w magazynie po złożeniu zamówienia
    foreach ($_SESSION['cart'] as $productId => $productData) {
        $productId = $productData['id'];
        $quantityOrdered = $productData['quantity'];

        // Aktualizacja ilości w magazynie
        $sqlUpdateStock = "UPDATE produkty SET ilosc_w_magazynie = ilosc_w_magazynie - $quantityOrdered WHERE id = $productId";
        $conn->query($sqlUpdateStock);
    }

    // Wyczyszczenie koszyka po złożeniu zamówienia
    resetCart();
    echo "<script>showPopup();</script>";
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Koszyk</title>
    <link rel="stylesheet" href="style/stylekoszyk.css">
</head>

<body>
    <h1>Sklep spożywczy</h1>
    <!-- Wyświetlanie koszyka -->
<div class="container">
    <div class="cart">
    <?php
    showCartDetails($conn);
    ?>
    <div class="total-amount">
    <?php
        $totalAmount = 0;

        // Obliczanie łącznej kwoty do zapłaty za wszystkie produkty w koszyku
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $productData) {
                $totalAmount += $productData['cena_brutto'] * $productData['quantity'];
            }
        }

        echo "<h2>Suma: $totalAmount zł</h2>";
    ?>
    </div>
    <div>
    <!-- Przycisk "Złóż zamówienie" -->
    <form method="post" action="" class="place-order-form">
        <input type="submit" name="placeOrder" value="Wyślij zamówienie">
    </form>
    </div>

    </div>
<div class="products">
<?php
function pokazProdukty($conn) {
    $query = "SELECT p.*, k.nazwa AS nazwa_kategorii FROM produkty p INNER JOIN kategorie k ON p.kategoria = k.id WHERE p.status_dostepnosci = 1 ORDER BY p.id ASC";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Tytuł</th><th>Opis</th><th>Cena netto</th><th>Podatek VAT</th><th>Ilość w magazynie</th><th>Zdjęcie</th><th>Kategoria</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['tytul'] . "</td>";
            echo "<td>" . $row['opis'] . "</td>";
            echo "<td>" . $row['cena_netto'] . "zł</td>";
            echo "<td>" . $row['podatek_vat'] . "</td>";
            echo "<td>" . $row['ilosc_w_magazynie'] . "szt.</td>";
            echo "<td><img src='data:image/*;base64," . base64_encode($row['zdjecie']) . "' alt='Obraz'></td>";
            echo "<td>" . $row['nazwa_kategorii'] . "</td>";
            echo "</tr>";
        
        }
        echo "</table>";
    } else {
        echo "Brak produktów w bazie danych.";
    }
}
pokazProdukty($conn);
?>
</div>
<form method="post" action="", class="add-to-cart-form">
        <label for="addId">Wybierz produkt:</label>
        <select name="addId" id="addId">
            <?php
            // Zapytanie SQL pobierające produkty
            $sql = "SELECT id, tytul FROM produkty";

            // Wykonanie zapytania
            $result = $conn->query($sql);

            // Wyświetlenie opcji wyboru produktów
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['tytul'] . "</option>";
                }
            }
            ?>
        </select>
        <label for="quantity">Ilość:</label>
        <input type="number" id="quantity" name="quantity" min="1">
        <input type="submit" value="Dodaj do koszyka">
    </form>
</div>

</body>

</html>
