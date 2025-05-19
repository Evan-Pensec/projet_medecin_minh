<body>
    <?php
    $x=0;
require("bdd.php");
// include("PHARMA_DEVELOPPEMENT.txt");
$nom=file_get_contents("PHARMA_DEVELOPPEMENT.txt", 'r');
$id=file_get_contents("id.txt", 'r');
$text=file_get_contents("texte.txt", 'r');

// echo $nom;
$zard=explode("/", $nom);
$ids=explode("#", $id);
$texts=explode("#", $text);
foreach($zard as $zlek){
    $sql='INSERT INTO `medicament`(`Code_medicament`, `Designation`, `Laboratoire`) VALUES ('.$ids[$x].',"'.$texts[$x].'","'.$zlek.'")';
    $pdo->exec($sql) ?? null;
    $x=$x+1;
}
?>
</body>