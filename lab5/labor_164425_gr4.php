<head>
    <meta charset="UTF-8">
    <meta title="Laboratorium">
    <meta author="Krzysztof Rozmysłowicz">
</head>
<body>
<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>">
  Liczba1: <input type="text" name="l1">
  Liczba2: <input type="text" name="l2">
  <input type="submit">
</form>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
  Imie: <input type="text" name="imie">
  Nazwisko: <input type="text" name="nazwisko">
  <input type="submit">
</form>
</body>
<?php
 $nr_indeksu = '164425';
 $nrGrupy = '4';
 echo 'Krzysztof Rozmyslowicz '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
 echo 'Zastosowanie metody include()<br />';
 echo "A $color $fruit<br />";
include 'vars.php';
echo "A $color $fruit<br />";
echo "Zastosowanie warunków if, else, elseif, switch<br />";
if($nr_indeksu == '164425')
{
    echo 'Krzysztof Rozmyslowicz<br />';
}
else
{
    echo 'Nie jestem Krzysztofem Rozmyslowiczem<br />';
}
if($nrGrupy == '4')
{
    echo 'Grupa 4<br />';
}
elseif($nrGrupy == '3')
{
    echo 'Grupa 3<br />';
}
else
{
    echo 'Nie jestem w grupie 3 ani 4<br />';
}
switch($nr_indeksu)
{
    case '164425':
        echo 'Krzysztof Rozmyslowicz<br />';
        break;
    case '164426':
        echo 'Jan Nowak<br />';
        break;
    default:
        echo 'Nie jestem Krzysztofem Rozmyslowiczem ani Janem Nowakiem<br />';
        break;
}
echo 'Zastosowanie pętli for, while<br />';

for($i = 0; $i < 10; $i++)
{
    echo "$i<br />";
}
$i = 0;
while($i < 10)
{
    echo "$i<br />";
    $i++;
}
echo 'Zastosowanie typów zmiennych $_GET, $_POST, $_SESSION <br />';
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $l1 = $_GET['l1'];
    $l2 = $_GET['l2'];
    if (empty($l1) || empty($l2)) {
      echo "Liczby nie zostały podane";
    } else {
      echo "$l1 + $l2 = ".$l1+$l2."<br />";
    }
  }
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    if (empty($imie) || empty($nazwisko)) {
      echo "Dane użytkownika nie zostały podane<br />";
    }
    else
    {
        echo "Imie: $imie<br />";
        echo "Nazwisko: $nazwisko<br />";
    }
  }
  session_start();
  
  if(!isset($_SESSION['licznik']))
  {
      $_SESSION['licznik'] = 0;
  }
  
  $_SESSION['licznik']++;
  
  echo 'Odwiedziłeś już '.$_SESSION['licznik'].' podstron!<br />';

?>
