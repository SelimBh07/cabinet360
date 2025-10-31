-- =============================================
-- Cabinet360 Database Schema
-- Cabinet d'Avocat Management System
-- =============================================

CREATE DATABASE IF NOT EXISTS lexmanage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lexmanage;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'lawyer') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: clients
-- =============================================
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    cin VARCHAR(20) UNIQUE,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: cases
-- =============================================
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    case_number VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('civil', 'penal', 'commercial', 'administratif', 'familial', 'autre') NOT NULL,
    status ENUM('ouvert', 'en_cours', 'clos', 'suspendu') DEFAULT 'ouvert',
    lawyer VARCHAR(100),
    date_opened DATE NOT NULL,
    notes TEXT,
    document_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: appointments
-- =============================================
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    purpose VARCHAR(200),
    location VARCHAR(150),
    status ENUM('planifie', 'termine', 'annule') DEFAULT 'planifie',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: payments
-- =============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    case_id INT,
    date DATE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    method ENUM('espèces', 'chèque', 'virement', 'carte') DEFAULT 'espèces',
    status ENUM('payé', 'impayé', 'partiel') DEFAULT 'impayé',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
-- =============================================
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'admin');

-- =============================================
-- Sample data for testing (optional)
-- =============================================
INSERT INTO clients (full_name, cin, phone, email, address, notes) VALUES 
('Ahmed Bennani', 'AB123456', '+212 6 12 34 56 78', 'ahmed.bennani@email.ma', 'Casablanca, Maroc', 'Client VIP'),
('Fatima Zahra El Mansouri', 'FZ789012', '+212 6 98 76 54 32', 'fatima.mansouri@email.ma', 'Rabat, Maroc', 'Nouveau client'),
('Youssef Alami', 'YA345678', '+212 6 55 44 33 22', 'youssef.alami@email.ma', 'Marrakech, Maroc', 'Affaire urgente');

INSERT INTO cases (client_id, case_number, type, status, lawyer, date_opened, notes) VALUES 
(1, 'DOS-2025-001', 'commercial', 'en_cours', 'Me. Hassan Tazi', '2025-01-15', 'Litige commercial avec société X'),
(2, 'DOS-2025-002', 'familial', 'ouvert', 'Me. Amina Bennis', '2025-02-10', 'Dossier de divorce'),
(3, 'DOS-2025-003', 'civil', 'en_cours', 'Me. Hassan Tazi', '2025-03-05', 'Contentieux immobilier');

INSERT INTO appointments (client_id, date, time, purpose, location, status) VALUES 
(1, '2025-10-20', '10:00:00', 'Consultation initiale', 'Bureau principal', 'planifie'),
(2, '2025-10-22', '14:30:00', 'Signature des documents', 'Bureau principal', 'planifie'),
(3, '2025-10-18', '09:00:00', 'Suivi du dossier', 'Bureau principal', 'planifie');

INSERT INTO payments (client_id, case_id, date, amount, method, status, notes) VALUES 
(1, 1, '2025-01-20', 5000.00, 'virement', 'payé', 'Acompte initial'),
(2, 2, '2025-02-15', 3000.00, 'chèque', 'payé', 'Consultation et honoraires'),
(3, 3, '2025-03-10', 4500.00, 'espèces', 'impayé', 'Honoraires à régler');

-- =============================================
-- End of database schema
-- =============================================

