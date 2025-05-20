<?php
require_once 'connexion.php';

$patient = null;
if (isset($_GET['patient']) && is_numeric($_GET['patient'])) {
    $patientId = intval($_GET['patient']);
    $result = $conn->query("SELECT * FROM Patient WHERE Numero_patient = $patientId");
    if ($result && $result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    }
}

if (isset($_POST['creer_ordonnance']) && $patient) {
    $patientId = $patient['Numero_patient'];
    $date = date('Y-m-d');
    
    $stmt = $conn->prepare("INSERT INTO Ordonnance (Date, Numero_patient) VALUES (?, ?)");
    $stmt->bind_param("si", $date, $patientId);
    
    if ($stmt->execute()) {
        $ordonnanceId = $conn->insert_id;
        header("Location: gestion_ordonnances.php?patient=$patientId&ordonnance=$ordonnanceId");
        exit;
    }
}

$ordonnance = null;
$details = [];
if (isset($_GET['ordonnance']) && is_numeric($_GET['ordonnance'])) {
    $ordonnanceId = intval($_GET['ordonnance']);
    $result = $conn->query("SELECT o.*, p.nom, p.prenom FROM Ordonnance o JOIN Patient p ON o.Numero_patient = p.Numero_patient WHERE o.Numero_ordonnance = $ordonnanceId");
    if ($result && $result->num_rows > 0) {
        $ordonnance = $result->fetch_assoc();
        
        $detailsResult = $conn->query("SELECT d.*, m.Designation FROM Detail d JOIN Medicament m ON d.Code_medicament = m.Code_medicament WHERE d.Numero_ordonnance = $ordonnanceId");
        if ($detailsResult) {
            while ($row = $detailsResult->fetch_assoc()) {
                $details[] = $row;
            }
        }
    }
}

if (isset($_POST['ajouter_medicament']) && $ordonnance) {
    $medicamentId = $_POST['medicament'];
    $posologie = $_POST['posologie'];
    $ordonnanceId = $ordonnance['Numero_ordonnance'];
    
    $stmt = $conn->prepare("INSERT INTO Detail (Numero_ordonnance, Code_medicament, Posologie) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $ordonnanceId, $medicamentId, $posologie);
    
    if ($stmt->execute()) {
        header("Location: gestion_ordonnances.php?patient={$ordonnance['Numero_patient']}&ordonnance=$ordonnanceId");
        exit;
    }
}

if (isset($_GET['supprimer_detail']) && is_numeric($_GET['supprimer_detail']) && $ordonnance) {
    $detailId = intval($_GET['supprimer_detail']);
    
    if ($conn->query("DELETE FROM Detail WHERE Numero_detail = $detailId")) {
        header("Location: gestion_ordonnances.php?patient={$ordonnance['Numero_patient']}&ordonnance={$ordonnance['Numero_ordonnance']}");
        exit;
    }
}

$searchMed = isset($_GET['search_med']) ? $_GET['search_med'] : '';
$medicaments = [];

if (!empty($searchMed)) {
    $searchTerm = '%' . $searchMed . '%';
    $stmt = $conn->prepare("SELECT * FROM Medicament WHERE Designation LIKE ? OR Code_medicament LIKE ? LIMIT 20");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $medicaments[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Ordonnances</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div>
        
        
        <?php include 'menu.php'; ?>
        <h1 class="h1">Gestion des Ordonnances</h1>
        
        <?php if (!$patient): ?>
            <div>
                Veuillez sélectionner un patient dans la 
                <a class="aordo" href="gestion_patients.php">liste des patients</a> 
                pour créer ou consulter une ordonnance.
            </div>
        <?php else: ?>
            <div>
                <div>
                    <h5>Patient : <?php echo $patient['nom'] . ' ' . $patient['prenom']; ?></h5>
                </div>
                <div>
                    <div>
                        <div>
                            <p><strong>Adresse :</strong> <?php echo $patient['adresse']; ?>, <?php echo $patient['code_postal']; ?> <?php echo $patient['ville']; ?></p>
                            <p><strong>N° Sécurité Sociale :</strong> <?php echo $patient['numero_securite_sociale']; ?></p>
                        </div>
                        <div>
                            <p><strong>Téléphone :</strong> <?php echo $patient['telephone']; ?></p>
                            <p><strong>Email :</strong> <?php echo $patient['adresse_mail']; ?></p>
                        </div>
                    </div>
                    
                    <?php if (!$ordonnance): ?>
                        <form method="post">
                            <button type="submit" name="creer_ordonnance">
                                Créer une nouvelle ordonnance
                            </button>
                        </form>
                        
                        <?php
                        $ordonnancesResult = $conn->query("SELECT * FROM Ordonnance WHERE Numero_patient = {$patient['Numero_patient']} ORDER BY Date DESC");
                        if ($ordonnancesResult && $ordonnancesResult->num_rows > 0):
                        ?>
                            <h5>Ordonnances précédentes</h5>
                            <table>
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ord = $ordonnancesResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $ord['Numero_ordonnance']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($ord['Date'])); ?></td>
                                            <td>
                                                <a href="?patient=<?php echo $patient['Numero_patient']; ?>&ordonnance=<?php echo $ord['Numero_ordonnance']; ?>">Consulter</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div>
                            <h5>Ordonnance N° <?php echo $ordonnance['Numero_ordonnance']; ?> du <?php echo date('d/m/Y', strtotime($ordonnance['Date'])); ?></h5>
                            
                            <div>
                                <h6>Médicaments prescrits</h6>
                                
                                <?php if (empty($details)): ?>
                                    <div>Aucun médicament ajouté à cette ordonnance.</div>
                                <?php else: ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Médicament</th>
                                                <th>Posologie</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($details as $detail): ?>
                                                <tr>
                                                    <td><?php echo $detail['Designation']; ?></td>
                                                    <td><?php echo $detail['Posologie']; ?></td>
                                                    <td>
                                                        <a href="?patient=<?php echo $patient['Numero_patient']; ?>&ordonnance=<?php echo $ordonnance['Numero_ordonnance']; ?>&supprimer_detail=<?php echo $detail['Numero_detail']; ?>">Supprimer</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                                
                                <div>
                                    <div>
                                        <h6>Ajouter un médicament</h6>
                                    </div>
                                    <div>
                                        <form method="get">
                                            <input type="hidden" name="patient" value="<?php echo $patient['Numero_patient']; ?>">
                                            <input type="hidden" name="ordonnance" value="<?php echo $ordonnance['Numero_ordonnance']; ?>">
                                            
                                            <div>
                                                <input type="text" name="search_med" placeholder="Rechercher un médicament" value="<?php echo htmlspecialchars($searchMed); ?>">
                                                <button type="submit">Rechercher</button>
                                            </div>
                                        </form>
                                        
                                        <?php if (!empty($searchMed) && !empty($medicaments)): ?>
                                            <form method="post">
                                                <div>
                                                    <label for="medicament">Sélectionner un médicament</label>
                                                    <select id="medicament" name="medicament" required>
                                                        <option value="">-- Sélectionner --</option>
                                                        <?php foreach ($medicaments as $med): ?>
                                                            <option value="<?php echo $med['Code_medicament']; ?>"><?php echo $med['Designation']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div>
                                                    <label for="posologie">Posologie</label>
                                                    <textarea id="posologie" name="posologie" rows="2" required></textarea>
                                                </div>
                                                
                                                <button type="submit" name="ajouter_medicament">Ajouter à l'ordonnance</button>
                                            </form>
                                        <?php elseif (!empty($searchMed)): ?>
                                            <div>Aucun médicament trouvé pour cette recherche.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <a href="gestion_ordonnances.php?patient=<?php echo $patient['Numero_patient']; ?>">
                                        Terminer
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>