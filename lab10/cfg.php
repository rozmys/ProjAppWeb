<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moja_strona";
$port = "3307";
$login = "admin";
$pass = "admin";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Połączenie nieudane. Błąd: " . $conn->connect_error);
}
class ZarzadzajKategoriami {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function DodajKategorie($matka, $nazwa) {
        $nazwa = mysqli_real_escape_string($this->conn, $nazwa);
        $query = "INSERT INTO kategorie (matka, nazwa) VALUES ('$matka', '$nazwa')";
        mysqli_query($this->conn, $query);
    }

    public function UsunKategorie($id) {
        $query = "DELETE FROM kategorie WHERE id = '$id' OR matka = '$id'";
        mysqli_query($this->conn, $query);
    }

    public function EdytujKategorie($id, $nazwa) {
        $nazwa = mysqli_real_escape_string($this->conn, $nazwa);
        $query = "UPDATE kategorie SET nazwa = '$nazwa' WHERE id = '$id'";
        mysqli_query($this->conn, $query);
    }

    public function PokazKategorie() {
        $query = "SELECT * FROM kategorie WHERE matka = 0";
        $result = mysqli_query($this->conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<strong>{$row['nazwa']}</strong><br>";

            $this->PokazPodkategorie($row['id'], 1);
        }
    }

    public function PokazOpcjeKategorii($matka, $indent) {
        $query = "SELECT * FROM kategorie WHERE matka = '$matka'";
        $result = mysqli_query($this->conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $nazwa = str_repeat("&nbsp;", $indent * 4) . $row['nazwa'];
            echo "<option value='{$row['id']}'>$nazwa</option>";

            $this->PokazOpcjeKategorii($row['id'], $indent + 1);
        }
    }

    private function PokazPodkategorie($matka, $indent) {
        $query = "SELECT * FROM kategorie WHERE matka = '$matka'";
        $result = mysqli_query($this->conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $nazwa = str_repeat("&nbsp;", $indent * 4) . $row['nazwa'];
            echo "&nbsp;&nbsp;&nbsp;&nbsp;$nazwa";
            echo " <a href='?action=edytujkategorie&id={$row['id']}'>Edytuj</a>";
            echo " <a href='?action=usunkategorie&id={$row['id']}'>Usuń</a><br>";

            $this->PokazPodkategorie($row['id'], $indent + 1);
        }
    }
}
?>
