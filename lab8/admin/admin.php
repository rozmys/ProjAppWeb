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
            <link rel="stylesheet" href="../style.css"> <!-- Dodaj swoje style CSS -->
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
                <title>Lista Podstron</title>
                <link rel="stylesheet" href="style.css"> <!-- Dodaj swoje style CSS -->
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
            </body>
            </html>
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
            <title><?php echo $isAddingNew ? 'Dodaj Nową Podstronę' : 'Edytuj Podstronę'; ?></title>
            <link rel="stylesheet" href="style.css"> <!-- Dodaj swoje style CSS -->
        </head>
        <body>
            <h2><?php echo $isAddingNew ? 'Dodaj Nową Podstronę' : 'Edytuj Podstronę'; ?></h2>
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($page['id']); ?>">
                <label for="title">Tytuł:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($page['page_title']); ?>" required>
                <label for="content">Treść:</label>
                <textarea name="content" required><?php echo htmlspecialchars($page['page_content']); ?></textarea>
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
        $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
        $content = isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '';
        $status = isset($_POST['active']) ? 1 : 0; // 1 oznacza aktywną, 0 nieaktywną

        // Zapisz zmiany w bazie danych
        $query = "UPDATE page_list SET title = ?, page_content = ?, status = ? WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($GLOBALS['conn'], $query);
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
?>
