<?php
include('../cfg.php');

/**
 * Klasa Contact - obsługuje formularz kontaktowy.
 */
class Contact {
    /**
     * Metoda PokazKontakt - generuje formularz kontaktowy.
     */
    function PokazKontakt() {
        echo '<form action="contact.php" method="post">
            Imię: <input type="text" name="name"><br>
            E-mail: <input type="text" name="email"><br>
            Wiadomość: <textarea name="message"></textarea><br>
            <input type="submit" name="submit" value="Wyślij">
            <input type="submit" name="reset" value="Przypomnij hasło">
        </form>';
    }

    /**
     * Metoda WyslijMailKontakt - obsługuje wysyłanie wiadomości e-mail z formularza.
     */
    function WyslijMailKontakt() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
            $name = $_POST["name"];
            $email = $_POST["email"];
            $message = $_POST["message"];

            $to = "admin@example.com";
            $subject = "Nowa wiadomość od $name";
            $headers = "From: $email";

            mail($to, $subject, $message, $headers);
        }
    }

    /**
     * Metoda PrzypomnijHaslo - obsługuje przypomnienie hasła.
     */
    function PrzypomnijHaslo() {
        global $pass;
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset"])) {
            $to = "admin@example.com";
            $subject = "Przypomnienie hasła";
            $message = "Twoje hasło to: $pass";
            $headers = "From: system@example.com";

            mail($to, $subject, $message, $headers);
        }
    }
}

$contact = new Contact();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submit"])) {
        $contact->WyslijMailKontakt();
    } elseif (isset($_POST["reset"])) {
        $contact->PrzypomnijHaslo();
    }
} else {
    $contact->PokazKontakt();
}
?>




