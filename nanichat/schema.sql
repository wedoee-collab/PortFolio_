-- Schema base Nanichat
-- On cree la base si besoin.
CREATE DATABASE IF NOT EXISTS chat_project;
USE chat_project;

-- Table des utilisateurs (auth + roles).
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'utilisateur'
);

-- Index pour eviter les doublons.
CREATE UNIQUE INDEX users_username_unique ON users (username);
CREATE UNIQUE INDEX users_email_unique ON users (email);

-- Table des salons (public/prive + autorises en CSV).
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  owner VARCHAR(50) NOT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  allowed TEXT NOT NULL,
  created_at VARCHAR(20) NOT NULL
);

-- Table des messages lies a un salon.
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  username VARCHAR(50) NOT NULL,
  content TEXT NOT NULL,
  created_at VARCHAR(20) NOT NULL
);
