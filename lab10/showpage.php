<?php

/**
 * Funkcja wyświetlająca treść podstrony na podstawie numeru strony.
 * 
 * @param int $page Numer strony.
 * @return string Treść podstrony.
 */
function PokazPodstrone($page = 1) {
    // Zabezpieczenie wartości $page przed atakiem typu CODE INJECTION.
    $page_clear = htmlspecialchars($page);

    // Zapytanie SQL w celu pobrania treści podstrony o określonym ID.
    $query = "SELECT * FROM page_list WHERE id = '$page_clear' LIMIT 1";
    
    // Wykonanie zapytania SQL.
    $result = mysqli_query($GLOBALS['conn'], $query);
    
    // Pobranie wyniku zapytania.
    $row = mysqli_fetch_array($result);

    // Sprawdzenie, czy podstrona istnieje.
    if (empty($row['id'])) {
        $web = '<h1>Podstrona nie istnieje</h1>';
    } else {
        // Przypisanie treści podstrony do zmiennej.
        $web = $row['page_content'];
    }

    // Zwrócenie treści podstrony.
    return $web;
}

?>
