<?php
require_once 'connexion.php';

function chargerMedicaments() {
    global $conn;
    
    $url = "https://base-donnees-publique.medicaments.gouv.fr/telechargement.php?fichier=CIS_bdpm.txt";
    
    $fichier = file_get_contents($url);
    
    if ($fichier === FALSE) {
        echo "Impossible de télécharger le fichier des médicaments.";
        return false;
    }
    
    $lignes = explode("\n", $fichier);
    
    $conn->query("TRUNCATE TABLE Medicament");
    
    $stmt = $conn->prepare("INSERT INTO Medicament (Code_medicament, Designation, Laboratoire) VALUES (?, ?, ?)");
    
    $compteur = 0;
    
    foreach ($lignes as $ligne) {
        if (empty(trim($ligne))) continue;
        
        $data = explode("\t", $ligne);
        
        if (count($data) >= 3) {
            $code = trim($data[0]);
            $nom = trim($data[1]);
            $labo = trim($data[2]);
            
            $stmt->bind_param("sss", $code, $nom, $labo);
            
            if ($stmt->execute()) {
                $compteur++;
            }
        }
    }
    
    $stmt->close();
    
    return $compteur;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Importation des médicaments</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" >
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h1>Importation des médicaments</h1>
        
        <?php include 'menu.php'; ?>
        
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Importer les médicaments</h5>
                <p>Cette opération va télécharger et importer les médicaments depuis la base de données publique des médicaments.</p>
                
                <?php
                if (isset($_POST['importer'])) {
                    $nbImportes = chargerMedicaments();
                    if ($nbImportes !== false) {
                        echo '<div class="alert alert-success">' . $nbImportes . ' médicaments ont été importés avec succès.</div>';
                    } else {
                        echo '<div class="alert alert-danger">Une erreur s\'est produite lors de l\'importation.</div>';
                    }
                }
                ?>
                
                <form method="post">
                    <button type="submit" name="importer" class="btn btn-primary">Lancer l'importation</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>