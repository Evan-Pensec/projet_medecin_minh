CREATE DATABASE IF NOT EXISTS cabinet_medical;
USE cabinet_medical;

CREATE TABLE Patient (
    Numero_patient INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    adresse VARCHAR(255),
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    pays VARCHAR(100),
    numero_securite_sociale VARCHAR(20),
    telephone VARCHAR(20),
    adresse_mail VARCHAR(100)
);

CREATE TABLE Medicament (
    Code_medicament VARCHAR(20) PRIMARY KEY,
    Designation VARCHAR(255) NOT NULL,
    Laboratoire VARCHAR(255)
);

CREATE TABLE Ordonnance (
    Numero_ordonnance INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE NOT NULL,
    Numero_patient INT NOT NULL,
    CONSTRAINT fk_ordonnance_patient FOREIGN KEY (Numero_patient) 
        REFERENCES Patient(Numero_patient)
);

CREATE TABLE Detail (
    Numero_detail INT AUTO_INCREMENT PRIMARY KEY,
    Numero_ordonnance INT NOT NULL,
    Code_medicament VARCHAR(20) NOT NULL,
    Posologie TEXT,
    CONSTRAINT fk_detail_ordonnance FOREIGN KEY (Numero_ordonnance) 
        REFERENCES Ordonnance(Numero_ordonnance),
    CONSTRAINT fk_detail_medicament FOREIGN KEY (Code_medicament) 
        REFERENCES Medicament(Code_medicament)
);

CREATE INDEX idx_patient_nom ON Patient(nom, prenom);
CREATE INDEX idx_medicament_designation ON Medicament(Designation);
CREATE INDEX idx_ordonnance_date ON Ordonnance(Date);
CREATE INDEX idx_ordonnance_patient ON Ordonnance(Numero_patient);
CREATE INDEX idx_detail_ordonnance ON Detail(Numero_ordonnance);
CREATE INDEX idx_detail_medicament ON Detail(Code_medicament);