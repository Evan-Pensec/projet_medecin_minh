<?php
require_once 'connexion.php';

if (isset($_POST['submit'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $ville = $_POST['ville'];
    $pays = $_POST['pays'];
    $numero_ss = $_POST['numero_ss'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE Patient SET nom=?, prenom=?, adresse=?, code_postal=?, ville=?, pays=?, numero_securite_sociale=?, telephone=?, adresse_mail=? WHERE Numero_patient=?");
        $stmt->bind_param("sssssssssi", $nom, $prenom, $adresse, $code_postal, $ville, $pays, $numero_ss, $telephone, $email, $id);
        
        if ($stmt->execute()) {
            $message = "Patient modifié avec succès.";
            $alertType = "success";
        } else {
            $message = "Erreur lors de la modification : " . $conn->error;
            $alertType = "danger";
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO Patient (nom, prenom, adresse, code_postal, ville, pays, numero_securite_sociale, telephone, adresse_mail) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $nom, $prenom, $adresse, $code_postal, $ville, $pays, $numero_ss, $telephone, $email);
        
        if ($stmt->execute()) {
            $message = "Patient ajouté avec succès.";
            $alertType = "success";
        } else {
            $message = "Erreur lors de l'ajout : " . $conn->error;
            $alertType = "danger";
        }
    }
}

$patient = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM Patient WHERE Numero_patient = $id");
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    }
}

$patients = [];
$result = $conn->query("SELECT * FROM Patient ORDER BY nom, prenom");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Patients</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div>
        <h1 class="h1">Gestion des Patients</h1>
        
        <?php include 'menu.php'; ?>
        
        <?php if (isset($message)): ?>
            <div>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div>
            <div>
                <div>
                    <div>
                        <h5><?php echo ($patient) ? 'Modifier un patient' : 'Ajouter un patient'; ?></h5>
                        
                        <form method="post">
                            <?php if ($patient): ?>
                                <input type="hidden" name="id" value="<?php echo $patient['Numero_patient']; ?>">
                            <?php endif; ?>
                            
                            <div>
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" required value="<?php echo ($patient) ? $patient['nom'] : ''; ?>">
                            </div>
                            
                            <div>
                                <label for="prenom">Prénom</label>
                                <input type="text" id="prenom" name="prenom" required value="<?php echo ($patient) ? $patient['prenom'] : ''; ?>">
                            </div>
                            
                            <div>
                                <label for="adresse">Adresse</label>
                                <input type="text" id="adresse" name="adresse" value="<?php echo ($patient) ? $patient['adresse'] : ''; ?>">
                            </div>
                            
                            <div>
                                <div>
                                    <label for="code_postal">Code Postal</label>
                                    <input type="text" id="code_postal" name="code_postal" value="<?php echo ($patient) ? $patient['code_postal'] : ''; ?>">
                                </div>
                                <div>
                                    <label for="ville">Ville</label>
                                    <input type="text" id="ville" name="ville" value="<?php echo ($patient) ? $patient['ville'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div>
                                <label for="pays">Pays</label>
                                <input type="text" id="pays" name="pays" value="<?php echo ($patient) ? $patient['pays'] : 'France'; ?>">
                            </div>
                            
                            <div>
                                <label for="numero_ss">Numéro de Sécurité Sociale</label>
                                <input type="text" id="numero_ss" name="numero_ss" value="<?php echo ($patient) ? $patient['numero_securite_sociale'] : ''; ?>">
                            </div>
                            
                            <div>
                                <label for="telephone">Téléphone</label>
                                <input type="tel" id="telephone" name="telephone" value="<?php echo ($patient) ? $patient['telephone'] : ''; ?>">
                            </div>
                            
                            <div>
                                <label for="email">Adresse Email</label>
                                <input type="email" id="email" name="email" value="<?php echo ($patient) ? $patient['adresse_mail'] : ''; ?>">
                            </div>
                            
                            <button type="submit" name="submit"><?php echo ($patient) ? 'Modifier' : 'Ajouter'; ?></button>
                            
                            <?php if ($patient): ?>
                                <a href="gestion_patients.php">Annuler</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div>
                <div>
                    <div>
                        <h5>Liste des patients</h5>
                        
                        <div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Adresse</th>
                                        <th>Code postal</th>
                                        <th>Ville</th>
                                        <th>Pays</th>
                                        <th>Numéro de Sécu</th>
                                        <th>Téléphone</th>
                                        <th>Adresse mail</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $p): ?>
                                        <tr>
                                            <td><?php echo $p['Numero_patient']; ?></td>
                                            <td><?php echo $p['nom']; ?></td>
                                            <td><?php echo $p['prenom']; ?></td>
                                            <td><?php echo $p['adresse']; ?></td>
                                            <td><?php echo $p['code_postal']; ?></td>
                                            <td><?php echo $p['ville']; ?></td>
                                            <td><?php echo $p['pays']; ?></td>
                                            <td><?php echo $p['numero_securite_sociale']; ?></td>
                                            <td><?php echo $p['telephone']; ?></td>
                                            <td><?php echo $p['adresse_mail']; ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $p['Numero_patient']; ?>">Modifier</a>
                                                <a href="gestion_ordonnances.php?patient=<?php echo $p['Numero_patient']; ?>">Ordonnance</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($patients)): ?>
                                        <tr>
                                            <td colspan="5">Aucun patient enregistré</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>