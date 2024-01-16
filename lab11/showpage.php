<?php 

function PokazPodstrone($page = 1) {
    $page_clear = htmlspecialchars($page);

    $query = "SELECT * FROM page_list WHERE id = '$page_clear' LIMIT 1";
    $result = mysqli_query($GLOBALS['conn'], $query);
    $row = mysqli_fetch_array($result);

    if(empty($row['id'])) {
        $web = '<h1>Podstrona nie istnieje</h1>';
    } else {
        $web = $row['page_content'];
    }

    return $web;
}

?>