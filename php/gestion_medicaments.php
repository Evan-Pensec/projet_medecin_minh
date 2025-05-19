<?php
require_once 'connexion.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$medicaments = [];

if (!empty($search)) {
    $searchTerm = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT * FROM Medicament WHERE Designation LIKE ? OR Code_medicament LIKE ? OR Laboratoire LIKE ? ORDER BY Designation LIMIT 100");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM Medicament ORDER BY Designation LIMIT 100");
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $medicaments[] = $row;
    }
}

$countResult = $conn->query("SELECT COUNT(*) as total FROM Medicament");
$totalMedicaments = $countResult->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Médicaments</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des Médicaments</h1>
        
        <?php include 'menu.php'; ?>
        
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Recherche de médicaments</h5>
                
                <form method="get" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, code ou laboratoire" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                        <?php if (!empty($search)): ?>
                            <a href="gestion_medicaments.php" class="btn btn-secondary">Réinitialiser</a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <p>Nombre total de médicaments dans la base : <?php echo $totalMedicaments; ?></p>
                
                <?php if (!empty($search)): ?>
                    <p>Résultats pour la recherche "<?php echo htmlspecialchars($search); ?>" : <?php echo count($medicaments); ?> médicament(s) trouvé(s)</p>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Désignation</th>
                                <th>Laboratoire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicaments as $medicament): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($medicament['Code_medicament']); ?></td>
                                    <td><?php echo htmlspecialchars($medicament['Designation']); ?></td>
                                    <td><?php echo htmlspecialchars($medicament['Laboratoire']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($medicaments)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucun médicament trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($medicaments) == 100 && empty($search)): ?>
                    <div class="alert alert-info">
                        Affichage limité aux 100 premiers médicaments. Utilisez la recherche pour affiner les résultats.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>