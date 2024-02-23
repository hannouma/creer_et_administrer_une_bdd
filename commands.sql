-- cd  C:\xampp\mysql\bin
-- mysql -u root -p;
-- Création de la Base de Données
create database cinemaBDD;

Use cinemaBDD;

-- Create cinemas table with UUID as primary key
CREATE TABLE cinemas (
    cinema_id CHAR(36) PRIMARY KEY NOT NULL,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    halls_number INT(11) DEFAULT NULL,
    CHECK (halls_number >= 0)
);


-- Create movies table 
CREATE TABLE movies (
    movie_id INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
    movie_title VARCHAR(255) DEFAULT NULL,
    movie_description text DEFAULT NULL,
    movie_duration INT(11) DEFAULT NULL,
    movie_genre VARCHAR(255) DEFAULT NULL
);

CREATE TABLE movie_cinema_relationship (
    movie_id INT(11) NOT NULL,
    cinema_id CHAR(36) NOT NULL,
    PRIMARY KEY (movie_id, cinema_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (cinema_id) REFERENCES cinemas(cinema_id) ON DELETE CASCADE
);

-- Create halls table
CREATE TABLE halls (
    hall_id INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
    cinema_id CHAR(36) DEFAULT NULL,
    hall_name VARCHAR(255) NOT NULL,
    hall_capacity INT(11) DEFAULT NULL,
    CHECK (hall_capacity >= 0),
    FOREIGN KEY (cinema_id) REFERENCES cinemas(cinema_id) ON DELETE CASCADE
);

-- Create users table
CREATE TABLE users (
    user_id CHAR(36) PRIMARY KEY NOT NULL,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    cinema_id CHAR(36) DEFAULT NULL,
    FOREIGN KEY (cinema_id) REFERENCES cinemas(cinema_id)
);

-- Create sessions table
CREATE TABLE sessions (
    session_id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    cinema_id CHAR(36) NOT NULL,
    date DATE DEFAULT NULL,
    user_id CHAR(36),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (cinema_id) REFERENCES cinemas(cinema_id)
);

CREATE TABLE session_halls (
    session_id INT(11) NOT NULL,
    hall_id INT(11) NOT NULL,
    PRIMARY KEY (session_id, hall_id),
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (hall_id) REFERENCES halls(hall_id) ON DELETE CASCADE
);

CREATE TABLE session_movies (
    session_id INT(11) NOT NULL,
    movie_id INT(11) NOT NULL,
    PRIMARY KEY (session_id, movie_id),
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
);

CREATE TABLE roles
(
    role_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE userRoles
(
    userId CHAR(36) NOT NULL,
    roleId INT(11) NOT NULL,
    PRIMARY KEY (userId, roleId),
    FOREIGN KEY (userId) REFERENCES users(user_id),
    FOREIGN KEY (roleId) REFERENCES roles(role_id)
);

-- Create the status table
CREATE TABLE status (
    id INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
    status VARCHAR(255) NOT NULL
);

-- Create Booking table
CREATE TABLE bookings (
    booking_id CHAR(36) PRIMARY KEY NOT NULL,
    date DATETIME NOT NULL,
    status_id INT(11) DEFAULT NULL,
    payment_amount DECIMAL(10, 2) NOT NULL,
    seat_number INT(11) DEFAULT NULL,
    session_id INT(11) DEFAULT NULL,
    user_id CHAR(36) DEFAULT NULL,
    hall_id INT(11) DEFAULT NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (hall_id) REFERENCES halls(hall_id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES status(id)
);

CREATE TABLE paymentTypes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
    type_name VARCHAR(255) NOT NULL UNIQUE
);

-- Create Payment table
CREATE TABLE payments (
    id CHAR(36) PRIMARY KEY NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    date DATETIME NOT NULL,
    booking_id CHAR(36) DEFAULT NULL,
    payment_type_id int(11) DEFAULT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (payment_type_id) REFERENCES paymentTypes(id)
);

show tables;
-- Insertion de Données Factices

-- Inserting fictive data into the cinemas table
INSERT INTO cinemas (cinema_id, name, location, halls_number)
    VALUES
      ('487ef5c9-a0d2-11ee-8705-34415d418993', 'Cineplex Odeon', 'New York City', 10),
      ('4880cec4-a0d2-11ee-8705-34415d418993', 'AMC Theatres', 'Los Angeles', 8),
      ('e69e48a5-ae09-11ee-ab73-34415d418993', 'City Cinema', 'City Center', 5);

-- Inserting fictive data into the movies table
INSERT INTO movies (movie_title, movie_description, movie_duration, movie_genre) VALUES
    ('The Matrix', 'A computer hacker learns about the true nature of his reality', 136, 'Sci-Fi'),
    ('Inception', 'A thief who enters the dreams of others to steal their secrets', 148, 'Sci-Fi'),
    ('The Shawshank Redemption', 'Two imprisoned men bond over several years, finding solace and eventual redemption through acts of common decency.', 142, 'Drama'),
    ('The Godfather', 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.', 175, 'Crime');

-- Example data for movie_cinema_relationship table
INSERT INTO movie_cinema_relationship (movie_id, cinema_id) VALUES
(1, '487ef5c9-a0d2-11ee-8705-34415d418993'),
(1, 'e69e48a5-ae09-11ee-ab73-34415d418993'),
(1, '4880cec4-a0d2-11ee-8705-34415d418993'),
(2, '4880cec4-a0d2-11ee-8705-34415d418993'),
(2, 'e69e48a5-ae09-11ee-ab73-34415d418993'),
(2, '487ef5c9-a0d2-11ee-8705-34415d418993'),
(3, '487ef5c9-a0d2-11ee-8705-34415d418993'),
(3, 'e69e48a5-ae09-11ee-ab73-34415d418993'),
(3, '4880cec4-a0d2-11ee-8705-34415d418993'),
(4, '487ef5c9-a0d2-11ee-8705-34415d418993'),
(4, 'e69e48a5-ae09-11ee-ab73-34415d418993'),
(4, '4880cec4-a0d2-11ee-8705-34415d418993');

-- Add fictive halls to the first cinema (Cineplex Odeon)
INSERT INTO halls (cinema_id, hall_name, hall_capacity)
VALUES
  ('487ef5c9-a0d2-11ee-8705-34415d418993', 'Hall 1', 80),
  ('487ef5c9-a0d2-11ee-8705-34415d418993', 'Hall 2', 120),
  ('487ef5c9-a0d2-11ee-8705-34415d418993', 'Hall 3', 90),
  ('487ef5c9-a0d2-11ee-8705-34415d418993', 'Hall 4', 100);
  
-- Add fictive halls to the second cinema (AMC Theatres)
INSERT INTO halls (cinema_id, hall_name, hall_capacity)
VALUES
  ('4880cec4-a0d2-11ee-8705-34415d418993', 'Hall A', 110),
  ('4880cec4-a0d2-11ee-8705-34415d418993', 'Hall B', 95),
  ('4880cec4-a0d2-11ee-8705-34415d418993', 'Hall C', 80),
  ('4880cec4-a0d2-11ee-8705-34415d418993', 'Hall D', 120);

-- Add fictive halls to the third cinema (City Cinema)
INSERT INTO halls (cinema_id, hall_name, hall_capacity)
VALUES
  ('e69e48a5-ae09-11ee-ab73-34415d418993', 'Small Hall', 50),
  ('e69e48a5-ae09-11ee-ab73-34415d418993', 'Medium Hall', 70),
  ('e69e48a5-ae09-11ee-ab73-34415d418993', 'Large Hall', 100),
  ('e69e48a5-ae09-11ee-ab73-34415d418993', 'VIP Hall', 30);

-- Inserting fictive data into the users table (admin_password : A6minP@$$w0r6)
INSERT INTO users (user_id, username, email, password_hash, cinema_id) VALUES 
('66c20506-a0d4-11ee-8705-34415d418993', 'john_doe', 'john.doe@example.com', '$2y$10$NUgkWY5ITHem8PNlUf6xOOCeJ73mjWGgUF85BwhzpnZcTs17.l7pW', '487ef5c9-a0d2-11ee-8705-34415d418993');
-- COMPLEX_USER password : C0mple*U$erP@$$w0r6
INSERT INTO users (user_id, username, email, password_hash, cinema_id) VALUES 
('b04633b9-a0d4-11ee-8705-34415d418993','jane_smith', 'jane.smith@example.com', '$2y$10$tw1OSgut/alnJEIEP.R.dOG/dyXAxgHqszO4DyjzwHfAJupWQtfqW', '487ef5c9-a0d2-11ee-8705-34415d418993'),
('53c580f6-aa5c-11ee-ae5c-34415d418993','sara_smith', 'sara.smith@example.com', '$2y$10$tw1OSgut/alnJEIEP.R.dOG/dyXAxgHqszO4DyjzwHfAJupWQtfqW', '4880cec4-a0d2-11ee-8705-34415d418993'),
('a4b25bc0-b92f-11ee-a9cd-34415d418993','Callie_Hawkins', 'callie.hawkins@example.com', '$2y$10$tw1OSgut/alnJEIEP.R.dOG/dyXAxgHqszO4DyjzwHfAJupWQtfqW', 'e69e48a5-ae09-11ee-ab73-34415d418993');
-- The user password : password123
INSERT INTO users (user_id, username, email, password_hash, cinema_id) VALUES 
(UUID(), 'test_user', 'test@example.com', '$2y$10$hv2m6oFnpMs6sZmpyNK1r.iWEJO/CU96h7b95VjYCC5Msw.lGdn8G', '487ef5c9-a0d2-11ee-8705-34415d418993');

-- Inserting fictive data into the roles table
INSERT INTO roles (name) VALUES ('ROLE_USER'), ('ROLE_ADMIN'), ('COMPLEX_USER');

-- Inserting fictive data into the userRoles table
INSERT INTO userRoles (userId, roleId) VALUES
('66c20506-a0d4-11ee-8705-34415d418993', '2'),  -- User with ROLE_ADMIN (john_doe)
('b04633b9-a0d4-11ee-8705-34415d418993', '3'),  -- User with COMPLEX_USER (jane_smith)
('53c580f6-aa5c-11ee-ae5c-34415d418993', '3'),  -- User with COMPLEX_USER (sara_smith)
('a4b25bc0-b92f-11ee-a9cd-34415d418993', '3');  -- User with COMPLEX_USER (Callie_Hawkins)

-- Inserting fictive data into the sessions table
INSERT INTO sessions (start_time, end_time,cinema_id, date, user_id) VALUES
('18:00:00', '20:00:00', '487ef5c9-a0d2-11ee-8705-34415d418993', '2024-02-09', '66c20506-a0d4-11ee-8705-34415d418993'),
('19:30:00', '21:30:00', '487ef5c9-a0d2-11ee-8705-34415d418993', '2024-02-10', '66c20506-a0d4-11ee-8705-34415d418993'),
('15:00:00', '17:30:00', '487ef5c9-a0d2-11ee-8705-34415d418993', '2024-02-11', '66c20506-a0d4-11ee-8705-34415d418993'),
('20:00:00', '23:00:00', 'e69e48a5-ae09-11ee-ab73-34415d418993', '2024-02-13', '66c20506-a0d4-11ee-8705-34415d418993'),
('19:30:00', '21:30:00', 'e69e48a5-ae09-11ee-ab73-34415d418993', '2024-02-14', '66c20506-a0d4-11ee-8705-34415d418993'),
('21:00:00', '23:30:00', 'e69e48a5-ae09-11ee-ab73-34415d418993', '2024-02-15', 'b04633b9-a0d4-11ee-8705-34415d418993');
INSERT INTO sessions (start_time, end_time,cinema_id, date, user_id) VALUES
('21:00:00', '23:00:00', '4880cec4-a0d2-11ee-8705-34415d418993', '2024-02-15', 'b04633b9-a0d4-11ee-8705-34415d418993');
INSERT INTO sessions (start_time, end_time,cinema_id, date, user_id) VALUES
('17:00:00', '19:00:00', '4880cec4-a0d2-11ee-8705-34415d418993', '2024-02-14', 'b04633b9-a0d4-11ee-8705-34415d418993');

-- Insert into session_halls
INSERT INTO session_halls (session_id, hall_id) VALUES (1, 1);
INSERT INTO session_halls (session_id, hall_id) VALUES (2, 2), (3, 3), (4, 9), (5, 10), (6, 11);
INSERT INTO session_halls (session_id, hall_id) VALUES (7, 5), (8, 8);

-- Insert into session_movies
INSERT INTO session_movies (session_id, movie_id) VALUES (1, 1);
INSERT INTO session_movies (session_id, movie_id) VALUES (2, 2), (3, 3), (4, 4), (5, 1), (6, 4);
INSERT INTO session_movies (session_id, movie_id) VALUES (7, 1), (8, 1);

-- Insert initial status options
INSERT INTO status (status) VALUES
('pending'),
('confirmed'),
('cancelled');

--Insert into paymentTypes
INSERT INTO paymentTypes (type_name) VALUES ('Cash'), ('Credit');

create USER 'user.php'@'localhost' IDENTIFIED BY 'Cinem@d4t4B@$e';

GRANT SELECT, INSERT, UPDATE, DELETE ON cinemaBDD.users TO 'user.php'@'localhost';
GRANT SELECT, INSERT ON cinemaBDD.userRoles TO 'user.php'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON cinemaBDD.roles TO 'user.php'@'localhost';
GRANT SELECT, UPDATE ON cinemaBDD.halls TO 'user.php'@'localhost';
GRANT SELECT ON cinemaBDD.bookings TO 'user.php'@'localhost';
GRANT SELECT ON cinemaBDD.cinemas TO 'user.php'@'localhost';
GRANT SELECT, INSERT ON cinemaBDD.movies TO 'user.php'@'localhost';
GRANT SELECT, INSERT ON cinemaBDD.movie_cinema_relationship TO 'user.php'@'localhost';
GRANT SELECT, INSERT ON cinemaBDD.session_halls TO 'user.php'@'localhost';
GRANT INSERT, DELETE, UPDATE ON cinemaBDD.sessions TO 'user.php'@'localhost';
GRANT SELECT, INSERT ON cinemaBDD.session_movies TO 'user.php'@'localhost';
GRANT SELECT, INSERT, UPDATE ON cinemaBDD.bookings TO 'user.php'@'localhost';
GRANT SELECT ON cinemaBDD.paymentTypes TO 'user.php'@'localhost';
GRANT INSERT, SELECT ON cinemaBDD.payments TO 'user.php'@'localhost';




