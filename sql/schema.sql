CREATE DATABASE IF NOT EXISTS makeup_db;
USE makeup_db;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price INT NOT NULL,
    image_path VARCHAR(255),
    is_cruelty_free TINYINT(1) DEFAULT 1,
    available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Datos de prueba
INSERT INTO categories (name) VALUES ('Rostro'), ('Ojos'), ('Labios');
INSERT INTO products (category_id, name, description, price, image_path, is_cruelty_free) 
VALUES (1, 'Base Luminosa Soft Glow', 'Base de cobertura media.', 24990, 'img/base.jpg', 1);