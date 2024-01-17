<?php
session_start();

include('../cfg.php');

class AdminPanel {
    public function FormularzLogowania() {
        if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
            $this->ListaPodstron();
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->SprawdzLogowanie();
            } else {
                $this->WyswietlFormularzLogowania();
            }
        }
    }

    private function SprawdzLogowanie() {
        $login_input = isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '';
        $pass_input = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';

        if ($login_input === $GLOBALS['login'] && $pass_input === $GLOBALS['pass']) {
            $_SESSION['logged'] = true;
            $this->ListaPodstron();
        } else {
            echo "<p>Błąd logowania. Spróbuj ponownie.</p>";
            $this->WyswietlFormularzLogowania();
        }
    }

    private function WyswietlFormularzLogowania() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Logowanie</title>
            <link rel="stylesheet" href="../style/styleadmin.css"> <!-- Dodaj swoje style CSS -->
        </head>
        <body>
            <div class="login-form">
                <h2>Formularz Logowania</h2>
                <form method="post" action="">
                    <label for="login">Login:</label>
                    <input type="text" name="login" required>
                    <label for="password">Hasło:</label>
                    <input type="password" name="password" required>
                    <button type="submit">Zaloguj</button>
                </form>
            </div>
        </body>
        </html>
        <?php
    }

    public function ListaPodstron() {
        // Pobierz listę podstron z bazy danych
        $query = "SELECT id, page_title FROM page_list";
        $result = mysqli_query($GLOBALS['conn'], $query);
    
        // Sprawdź, czy istnieją podstrony
        if (mysqli_num_rows($result) > 0) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Admin Panel</title>
                <link rel="stylesheet" href="../style/styleadmin.css"> 
            </head>
            <body>
                <h2>Lista Podstron</h2>
                <ul>
                    <?php
                    // Wyświetl listę podstron
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<li>';
                        echo '<a href="?action=edit&id=' . $row['id'] . '">' . $row['page_title'] . '</a>';
                        echo ' | <a href="?action=delete&id=' . $row['id'] . '">Usuń</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
                <p><a href="?action=add">Dodaj Nową Podstronę</a></p>
            <?php
        } else {
            echo "<p>Brak dostępnych podstron.</p>";
        }
    }

    public function EdytujPodstrone($id) {
        if (!$this->CzyZalogowany()) {
            return;
        }
    
        // Jeśli identyfikator jest pusty, oznacza to, że chcemy dodać nową podstronę
        $isAddingNew = empty($id);
    
        // Pobierz dane podstrony z bazy danych na podstawie ID (jeśli nie dodajemy nowej)
        if (!$isAddingNew) {
            $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
            $stmt = mysqli_prepare($GLOBALS['conn'], $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $page = mysqli_fetch_assoc($result);
    
            // Sprawdź, czy znaleziono podstronę (jeśli nie dodajemy nowej)
            if (!$page) {
                echo "<p>Błąd: Podstrona o podanym ID nie istnieje.</p>";
                return;
            }
        } else {
            // Ustaw domyślne wartości dla nowej podstrony
            $page = [
                'id' => '',
                'page_title' => '',
                'page_content' => '',
                'status' => 1, // Domyślnie ustawiona jako aktywna
            ];
        }
    
        // Wyświetl formularz edycji/dodawania
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>AdminPanel></title>
            <link rel="stylesheet" href="../style/styleadmin.css">
        </head>
        <body>
            <h2><?php echo $isAddingNew ? 'Dodaj Nową Podstronę' : 'Edytuj Podstronę'; ?></h2>
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo ($page['id']); ?>">
                <label for="title">Tytuł:</label>
                <input type="text" name="title" value="<?php echo ($page['page_title']); ?>" required>
                <label for="content">Treść:</label>
                <textarea name="content" required><?php echo ($page['page_content']); ?></textarea>
                <label for="active">Aktywna:</label>
                <input type="checkbox" name="active" <?php echo $page['status'] ? 'checked' : ''; ?>>
                <button type="submit">Zapisz</button>
            </form>
        </body>
        </html>
        <?php
    
        // Obsługa zapisu zmian po przesłaniu formularza
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isAddingNew) {
                // Jeśli dodajemy nową podstronę, użyj funkcji DodajNowaPodstrone
                $this->DodajNowaPodstrone();
            } else {
                // Jeśli edytujemy istniejącą podstronę, użyj funkcji ZapiszEdycje
                $this->ZapiszEdycje($id);
            }
        }
    }

    private function ZapiszEdycje($id) {
        // Sprawdź, czy został przekazany identyfikator podstrony
        if (empty($id)) {
            echo "<p>Błąd: Brak identyfikatora podstrony.</p>";
            return;
        }

        // Pobierz dane z formularza
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $content = isset($_POST['content']) ? ($_POST['content']) : '';
        $status = isset($_POST['active']) ? 1 : 0; // 1 oznacza aktywną, 0 nieaktywną

        // Zapisz zmiany w bazie danych
        $query = "UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($GLOBALS['conn'], $query);

        if (!$stmt) {
            die('Error in mysqli_prepare: ' . mysqli_error($GLOBALS['conn']));
        }

        mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<p>Zmiany zostały zapisane.</p>";
        } else {
            echo "<p>Błąd: Nie udało się zapisać zmian.</p>";
        }

        // Zamknij przygotowane zapytanie
        mysqli_stmt_close($stmt);
    }

    public function DodajNowaPodstrone() {
        if (!$this->CzyZalogowany()) {
            return;
        }
    
        // Sprawdź, czy formularz został przesłany
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPageId = $this->ZapiszNowaPodstrone();
            if ($newPageId !== false) {
                // Przekieruj do edycji nowo dodanej podstrony
                header("Location: ?action=edit&id={$newPageId}");
                exit();
            }
        }
    
        // Wyświetl formularz dodawania nowej podstrony
        $this->EdytujPodstrone(''); // Przekazujemy pusty string jako identyfikator
    }
    
    private function ZapiszNowaPodstrone() {
        // Pobierz dane z formularza
        $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
        $content = isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '';
        $status = isset($_POST['active']) ? 1 : 0; // 1 oznacza aktywną, 0 nieaktywną
    
        // Zapisz nową podstronę w bazie danych
        $query = "INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($GLOBALS['conn'], $query);
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $status);
    
        if (mysqli_stmt_execute($stmt)) {
            $newPageId = mysqli_insert_id($GLOBALS['conn']); // Pobierz identyfikator nowo dodanej podstrony
            echo "<p>Nowa podstrona została dodana.</p>";
            return $newPageId;
        } else {
            echo "<p>Błąd: Nie udało się dodać nowej podstrony.</p>";
        }
    
        // Zamknij przygotowane zapytanie
        mysqli_stmt_close($stmt);
    
        return false;
    }

    public function UsunPodstrone($id) {
        if (!$this->CzyZalogowany()) {
            return;
        }
    
        // Sprawdź, czy został przekazany identyfikator podstrony
        if (empty($id)) {
            echo "<p>Błąd: Brak identyfikatora podstrony.</p>";
            return;
        }
    
        // Usuń podstronę z bazy danych
        $query = "DELETE FROM page_list WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($GLOBALS['conn'], $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
    
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>Podstrona została usunięta.</p>";
        } else {
            echo "<p>Błąd: Nie udało się usunąć podstrony.</p>";
        }
    
        // Zamknij przygotowane zapytanie
        mysqli_stmt_close($stmt);
    }

    // ... inne metody ...

    private function CzyZalogowany() {
        if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
            echo "<p>Błąd: Brak autoryzacji. Zaloguj się.</p>";
            return false;
        }

        return true;
    }

}
class ZarzadzanieKategoriami {

        private $conn;
    
        public function __construct($conn) {
            $this->conn = $conn;
        }
    
        public function dodajKategorie($nazwa, $matka = 0) {
            $sql = "INSERT INTO kategorie (matka, nazwa) VALUES (?, ?) LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $matka, $nazwa);
            $stmt->execute();
            $stmt->close();
        }
    
        public function usunKategorie($kategoriaId) {
            $sql = "DELETE FROM kategorie WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $kategoriaId);
            $stmt->execute();
            $stmt->close();
        }
    
        public function edytujKategorie($kategoriaId, $nowaNazwa) {
            $sql = "UPDATE kategorie SET nazwa = ? WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $nowaNazwa, $kategoriaId);
            $stmt->execute();
            $stmt->close();
        }
    
        public function pokazKategorie() {
            $sql = "SELECT * FROM kategorie";
            $result = $this->conn->query($sql);
    
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($row['matka'] == 0) {
                        echo "Kategoria główna: " . $row['nazwa'] . " (ID: " . $row['id'] . ")<br>";
                        $this->_pokazPodkategorie($row['id'], 1);
                    }
                }
            } else {
                echo "Brak kategorii w bazie danych.";
            }
        }
    
        private function _pokazPodkategorie($matkaId, $indent) {
            $sql = "SELECT * FROM kategorie WHERE matka = $matkaId";
            $result = $this->conn->query($sql);
    
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo str_repeat("&nbsp;", $indent * 4) . "Podkategoria: " . $row['nazwa'] . " (ID: " . $row['id'] . ")<br>";
                    $this->_pokazPodkategorie($row['id'], $indent + 1);
                }
            }
        }
    }

    class ZarzadzanieProduktami {
        private $conn;
        
        public function __construct($conn) {
            $this->conn = $conn;
        }
    
        public function dodajProdukt($tytul, $opis, $dataUtworzenia, $dataModyfikacji, $dataWyg, $cenaNetto, $podatekVAT, $iloscWMagazynie, $statusDostepnosci, $gabaryt, $zdjecie, $kategoria) {
            $sql = "INSERT INTO produkty (tytul, opis, data_utworzenia, data_modyfikacji, data_wygasniecia, cena_netto, podatek_vat, ilosc_w_magazynie, status_dostepnosci, gabaryt, zdjecie, kategoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
        
            if (!$stmt) {
                die('Error: ' . $this->conn->error);  // Add this line to check for errors
            }
        
            $stmt->bind_param("sssssssssssi", $tytul, $opis, $dataUtworzenia, $dataModyfikacji, $dataWyg, $cenaNetto, $podatekVAT, $iloscWMagazynie, $statusDostepnosci, $gabaryt, $zdjecie, $kategoria);
            
            if (!$stmt->execute()) {
                die('Error: ' . $stmt->error);  // Add this line to check for errors
            }
        
            $stmt->close();
        }
    
        public function usunProdukt($produktId) {
            $sql = "DELETE FROM produkty WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $produktId);
            $stmt->execute();
            $stmt->close();
        }
    
        public function edytujProdukt($produktId, $nowyTytul, $nowyOpis, $dataUtworzenia, $dataModyfikacji, $dataWyg,$cenaNetto,$podatekVAT,$iloscWMagazynie,$statusDostepnosci,$gabaryt,$noweZdjecie,$kategoria) {
            $sql = "UPDATE produkty SET tytul = ?, opis = ?, data_utworzenia = ?, data_modyfikacji = ?, data_wygasniecia = ?, cena_netto = ?, podatek_vat = ?, ilosc_w_magazynie = ?, status_dostepnosci = ?, gabaryt = ?, zdjecie = ?, kategoria = ? WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                die('Error: ' . $this->conn->error);  // Add this line to check for errors
            }
            $stmt->bind_param("sssssssssssis", $nowyTytul, $nowyOpis, $dataUtworzenia, $dataModyfikacji, $dataWyg, $cenaNetto, $podatekVAT, $iloscWMagazynie, $statusDostepnosci, $gabaryt, $noweZdjecie, $kategoria, $produktId);
            if (!$stmt->execute()) {
                die('Error: ' . $stmt->error);  // Add this line to check for errors
            }
            $stmt->close();
        }
    
        public function pokazProdukty() {
            $sql = "SELECT * FROM produkty";
            $result = $this->conn->query($sql);
    
            if ($result->num_rows > 0) {
                echo "<table border='1'>";
                
                // Nagłówki kolumn
                echo "<tr>";
                $headerPrinted = false;
                while ($row = $result->fetch_assoc()) {
                    if (!$headerPrinted) {
                        foreach ($row as $key => $value) {
                            echo "<th>" . ucfirst($key) . "</th>";
                        }
                        echo "</tr><tr>";
                        $headerPrinted = true;
                    }
                    // Drukowanie danych wiersza
                    foreach ($row as $key => $value) {
                        echo "<td>";
                        if ($key === 'zdjecie') {
                            echo "<img src='data:image/*;base64," . base64_encode($value) . "' alt='Obraz' width='100' height='100'>";
                        } else {
                            echo $value;
                        }
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Brak produktów w bazie danych.";
            }
        }
    }
   
$adminPanel = new AdminPanel();

// Przekazywanie parametrów z URL (np. ?action=edit&id=1)
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Wywołaj odpowiednią metodę w zależności od akcji
switch ($action) {
    case 'edit':
        $adminPanel->EdytujPodstrone($id);
        break;
    case 'add':
        $adminPanel->DodajNowaPodstrone();
        break;
    case 'delete':
        $adminPanel->UsunPodstrone($id);
        break;
    default:
        $adminPanel->FormularzLogowania();
}
if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
$zarzadzanie = new ZarzadzanieKategoriami($GLOBALS['conn']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['dodajKat'])) {
        $nazwa = $_POST['nazwa'];
        $matka = $_POST['matka'];
        $zarzadzanie->dodajKategorie($nazwa, $matka);
    } elseif (isset($_POST['usunKat'])) {
        $kategoriaId = $_POST['usun_id'];
        $zarzadzanie->usunKategorie($kategoriaId);
    } elseif (isset($_POST['edytujKat'])) {
        $kategoriaId = $_POST['edytuj_id'];
        $nowaNazwa = $_POST['nowa_nazwa'];
        $zarzadzanie->edytujKategorie($kategoriaId, $nowaNazwa);
    }
}

// Wyświetlamy formularz dodawania kategorii
echo '<h2>Dodaj nową kategorię:</h2>';
echo '<form method="post" action="">';
echo 'Nazwa kategorii: <input type="text" name="nazwa" required><br>';
echo 'ID kategorii nadrzędnej (opcjonalne): <input type="number" name="matka"><br>';
echo '<input type="submit" name="dodajKat" value="Dodaj kategorię">';
echo '</form>';

// Wyświetlamy formularz usuwania kategorii
echo '<h2>Usuń kategorię:</h2>';
echo '<form method="post" action="">';
echo 'ID kategorii do usunięcia: <input type="number" name="usun_id" required><br>';
echo '<input type="submit" name="usunKat" value="Usuń kategorię">';
echo '</form>';

// Wyświetlamy formularz edycji kategorii
echo '<h2>Edytuj kategorię:</h2>';
echo '<form method="post" action="">';
echo 'ID kategorii do edycji: <input type="number" name="edytuj_id" required><br>';
echo 'Nowa nazwa kategorii: <input type="text" name="nowa_nazwa" required><br>';
echo '<input type="submit" name="edytujKat" value="Edytuj kategorię">';
echo '</form>';

// Wyświetlamy listę kategorii
echo '<h2>Lista kategorii:</h2>';
$zarzadzanie->pokazKategorie();

$zarzadzanieProduktami = new ZarzadzanieProduktami($GLOBALS['conn']);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['dodaj'])) {
            // Pobranie danych z formularza dodawania produktu
            $tytul = $_POST['tytul'];
            $opis = $_POST['opis'];
            $dataUtworzenia = $_POST['data_utworzenia'];
            $dataModyfikacji = $_POST['data_modyfikacji'];
            $dataWyg = $_POST['data_wygasniecia'];
            $cenaNetto = $_POST['cena_netto'];
            $podatekVAT = $_POST['podatek_vat'];
            $iloscWMagazynie = $_POST['ilosc_w_magazynie'];
            $statusDostepnosci = $_POST['status_dostepnosci'];
            $gabaryt = $_POST['gabaryt'];
            $kategoria = $_POST['kategoria'];
        
            $zdjecie = file_get_contents($_FILES['zdjecie']['tmp_name']);

            // Wywołanie funkcji dodającej produkt
            $zarzadzanieProduktami->dodajProdukt($tytul, $opis, $dataUtworzenia, $dataModyfikacji, $dataWyg, $cenaNetto, $podatekVAT, $iloscWMagazynie, $statusDostepnosci, $gabaryt, $zdjecie, $kategoria);
        } elseif (isset($_POST['usun'])) {
            // Usuwanie produktu
            $produktId = $_POST['usun_id'];
            $zarzadzanieProduktami->usunProdukt($produktId);
        } elseif (isset($_POST['edytuj'])) {
            $produktId = $_POST['edytuj_id'];
            $nowyTytul = $_POST['nowy_tytul'];
            $nowyOpis = $_POST['nowy_opis'];
            $nowaDataUtworzenia = $_POST['nowa_data_utworzenia'];
            $nowaDataModyfikacji = $_POST['nowa_data_modyfikacji'];
            $nowaDataWygasniecia = $_POST['nowa_data_wygasniecia'];
            $nowaCenaNetto = $_POST['nowa_cena_netto'];
            $nowyPodatekVAT = $_POST['nowy_podatek_vat'];
            $nowaIloscWMagazynie = $_POST['nowa_ilosc_w_magazynie'];
            $nowyStatusDostepnosci = $_POST['nowy_status_dostepnosci'];
            $nowyGabaryt = $_POST['nowy_gabaryt'];
            $noweZdjecie = file_get_contents($_FILES['nowe_zdjecie']['tmp_name']);
            $nowaKategoria = $_POST['nowa_kategoria'];

            $zarzadzanieProduktami->edytujProdukt($produktId,$nowyTytul,$nowyOpis,$nowaDataUtworzenia,
            $nowaDataModyfikacji,$nowaDataWygasniecia,$nowaCenaNetto,$nowyPodatekVAT,
            $nowaIloscWMagazynie,$nowyStatusDostepnosci,$nowyGabaryt,$noweZdjecie,$nowaKategoria);
        }
    }

    echo '<h1>Zarządzanie produktami</h1>
    <h2>Dodaj nowy produkt:</h2>
    <form method="post" action="" enctype="multipart/form-data">
    Tytuł: <input type="text" name="tytul" required><br>
    Opis: <input type="text" name="opis" required><br>
    Data utworzenia: <input type="date" name="data_utworzenia" required><br>
    Data modyfikacji: <input type="date" name="data_modyfikacji" required><br>
    Data wygaśnięcia: <input type="date" name="data_wygasniecia" required><br>
    Cena netto: <input type="number" step="0.01" name="cena_netto" required><br>
    Podatek VAT: <input type="number" step="0.01" name="podatek_vat" required><br>
    Ilość w magazynie: <input type="number" name="ilosc_w_magazynie" required><br>
    Status dostępności: <input type="number" name="status_dostepnosci" min="0" max="1" required><br>
    Gabaryt: <input type="text" name="gabaryt" required><br>
    Zdjęcie: <input type="file" name="zdjecie" accept="image/*" required><br>
    Kategoria: <input type="number" name="kategoria" required><br>
    <input type="submit" name="dodaj" value="Dodaj produkt">
    </form>
    <h2>Usuń produkt:</h2>
    <form method="post" action="">
        ID produktu do usunięcia: <input type="number" name="usun_id" required><br>
        <input type="submit" name="usun" value="Usuń produkt">
    </form>
    <h2>Edytuj produkt:</h2>
    <form method="post" action="", enctype="multipart/form-data">
    ID produktu do edycji: <input type="number" name="edytuj_id" required><br>
    Nowy tytuł: <input type="text" name="nowy_tytul" required><br>
    Nowy opis: <input type="text" name="nowy_opis" required><br>
    Data utworzenia: <input type="date" name="nowa_data_utworzenia" required><br>
    Data modyfikacji: <input type="date" name="nowa_data_modyfikacji" required><br>
    Data wygaśnięcia: <input type="date" name="nowa_data_wygasniecia" required><br>
    Cena netto: <input type="number" step="0.01" name="nowa_cena_netto" required><br>
    Podatek VAT: <input type="number" step="0.01" name="nowy_podatek_vat" required><br>
    Ilość w magazynie: <input type="number" name="nowa_ilosc_w_magazynie" required><br>
    Status dostępności: <input type="number" name="nowy_status_dostepnosci" min="0" max="1" required><br>
    Gabaryt: <input type="text" name="nowy_gabaryt" required><br>
    Zdjęcie: <input type="file" name="nowe_zdjecie" accept="image/*" ><br>
    Nowa kategoria: <input type="number" name="nowa_kategoria" required><br>
    <input type="submit" name="edytuj" value="Edytuj produkt">
</form>
    <h2>Lista produktów:</h2>';

    $zarzadzanieProduktami->pokazProdukty();
}
?>
</body>
</html>
