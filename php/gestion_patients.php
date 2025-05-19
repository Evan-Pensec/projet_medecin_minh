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
    <link rel="stylesheet" >
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des Patients</h1>
        
        <?php include 'menu.php'; ?>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show mt-3">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo ($patient) ? 'Modifier un patient' : 'Ajouter un patient'; ?></h5>
                        
                        <form method="post">
                            <?php if ($patient): ?>
                                <input type="hidden" name="id" value="<?php echo $patient['Numero_patient']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" required value="<?php echo ($patient) ? $patient['nom'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required value="<?php echo ($patient) ? $patient['prenom'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo ($patient) ? $patient['adresse'] : ''; ?>">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="code_postal" class="form-label">Code Postal</label>
                                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo ($patient) ? $patient['code_postal'] : ''; ?>">
                                </div>
                                <div class="col-md-8">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="ville" name="ville" value="<?php echo ($patient) ? $patient['ville'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="pays" name="pays" value="<?php echo ($patient) ? $patient['pays'] : 'France'; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="numero_ss" class="form-label">Numéro de Sécurité Sociale</label>
                                <input type="text" class="form-control" id="numero_ss" name="numero_ss" value="<?php echo ($patient) ? $patient['numero_securite_sociale'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo ($patient) ? $patient['telephone'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo ($patient) ? $patient['adresse_mail'] : ''; ?>">
                            </div>
                            
                            <button type="submit" name="submit" class="btn btn-primary"><?php echo ($patient) ? 'Modifier' : 'Ajouter'; ?></button>
                            
                            <?php if ($patient): ?>
                                <a href="gestion_patients.php" class="btn btn-secondary">Annuler</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des patients</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Téléphone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $p): ?>
                                        <tr>
                                            <td><?php echo $p['Numero_patient']; ?></td>
                                            <td><?php echo $p['nom']; ?></td>
                                            <td><?php echo $p['prenom']; ?></td>
                                            <td><?php echo $p['telephone']; ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $p['Numero_patient']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                                                <a href="gestion_ordonnances.php?patient=<?php echo $p['Numero_patient']; ?>" class="btn btn-sm btn-success">Ordonnance</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($patients)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Aucun patient enregistré</td>
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