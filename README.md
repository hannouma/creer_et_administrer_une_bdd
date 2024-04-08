README
# Cinema Booking System

This project is an exercise developed for my online course at STUDI. It's a cinema booking system that allows users to browse available cinemas, movies, and book tickets for 
movie sessions.

## Initialization

To initialize the app, follow these steps:

1. Open your command line interface.
2. Navigate to your MySQL bin directory. For example:
    ```
    cd C:\xampp\mysql\bin
    ```
3. Log in to MySQL:
    ```
    mysql -u root -p
    ```
4. Run the `command.sql` file included in the project. This script creates the necessary database structure and inserts some fictive data.

## Navigating the App

### Index Page (index.php)

The `index.php` page displays a list of cinema complexes retrieved from the database.

### Movies Page (movies.php)

The `movies.php` page displays available movies within a selected cinema.

### Sessions Page (sessions.php)

The `sessions.php` page displays all available sessions for a specific cinema and movie, allowing users to book tickets for each session.

### User Authentication

User authentication is implemented via the following pages:

- `login.php`: Handles user login.
- `register.php`: Handles user registration.

### Booking Process

After selecting the number of seats and ticket type and clicking "Book Tickets," the process is managed in `process_bookings.php`:

1. Validate the submitted data.
2. Create a new booking object.
3. Save the booking in the database within a PDO transaction.
4. Prompt the user to select a payment method (cash or credit).
5. Upon submission, associate the booking with the chosen payment method and forward to `process_payment.php` for further processing.

### Administrator and Complex User Logic

- The system comprises two main user types: Administrator and Complex User.
- Administrators control all cinemas, while Complex Users are restricted to managing their own cinema.
- Upon successful login, both types of users are redirected to `moviesSessionsManaging.php`.
- Administrators and Complex Users can add, edit, and delete movies and sessions.
- However, Administrators can perform these actions for all cinemas, and he can run the backup and restore process, while Complex Users can only do so for their own cinema.
- The `processMoviesSessions.php` file handles movie and session manipulation logic.

### Searching for Movies

The `processSearchMovie.php` file allows administrators and complex users to search for movies before adding them to a cinema. It follows these steps:

1. Establish a connection to the database.
2. Retrieve the search query and associated cinema ID.
3. Use the `searchMovies` static method to search for movies based on the query.
4. Display search results or provide an option to add a new movie if none are found.

## Test Credentials

- **Administrator:**
    - Email: john.doe@example.com
    - Password: A6minP@$$w0r6

- **Complex Users:**
    - Email: jane.smith@example.com
    - Password: C0mple*U$erP@$$w0r6

    - Email: sara.smith@example.com
    - Password: C0mple*U$erP@$$w0r6

    - Email: callie.hawkins@example.com
    - Password: C0mple*U$erP@$$w0r6
