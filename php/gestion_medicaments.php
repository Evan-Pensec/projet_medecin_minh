<?php
require_once 'connexion.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 100; 
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$medicaments = [];

if (!empty($search)) {
    $searchTerm = '%' . $search . '%';
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM Medicament WHERE Designation LIKE ? OR Code_medicament LIKE ? OR Laboratoire LIKE ?");
    $countStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalMedicaments = $countResult->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT * FROM Medicament WHERE Designation LIKE ? OR Code_medicament LIKE ? OR Laboratoire LIKE ? ORDER BY Designation LIMIT ? OFFSET ?");
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $countResult = $conn->query("SELECT COUNT(*) as total FROM Medicament");
    $totalMedicaments = $countResult->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT * FROM Medicament ORDER BY Designation LIMIT $limit OFFSET $offset");
}

$totalPages = ceil($totalMedicaments / $limit);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $medicaments[] = $row;
    }
}
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
        <h1 class="h1">Gestion des Médicaments</h1>
        
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
                <?php else: ?>
                    
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
                
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navigation des pages">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                            <ssn class="page-current"><?php echo $i; ?></ssn>
                            <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="page-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                            <?php if ($i < $totalPages): ?>
                            <span class="page-separator">, </span>
                            <?php endif; ?>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>