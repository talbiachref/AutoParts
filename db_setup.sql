
CREATE DATABASE IF NOT EXISTS autoparts_db
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE autoparts_db;


CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    prenom      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,         
    telephone   VARCHAR(20),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO users (nom, prenom, email, password, telephone) VALUES
('Ben Salem',  'Mohamed', 'med@autoparts.tn',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+216 20 000 001'),
('Trabelsi',  'Sarra',   'sarra@autoparts.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+216 20 000 002');
