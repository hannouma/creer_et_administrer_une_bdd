<?php
include_once 'Session.php';
class Movie {
    private $movie_id;
    private $movie_title;
    private $movie_description;
    private $movie_duration;
    private $movie_genre;

    public function __construct($movie_id, $movie_title, $movie_description, $movie_duration, $movie_genre) {
        $this->movie_id = $movie_id;
        $this->movie_title = $movie_title;
        $this->movie_description = $movie_description;
        $this->movie_duration = $movie_duration;
        $this->movie_genre = $movie_genre;

    }

    public function getMovieID(): int {
        return $this->movie_id;
    }

    public function getMovieTitle(): string {
        return $this->movie_title;
    }

    public function getMovieDescription(): string {
        return $this->movie_description;
    }

    public function getMovieDuration(): int {
        return $this->movie_duration;
    }

    public function getMovieGenre(): string {
        return $this->movie_genre;
    }

    public function setMovieTitle($newMovieTitle): void {
        $this->movie_title = $newMovieTitle;
    }

    public function setMovieGenre($newMovieGenre): void {
        $this->movie_genre = $newMovieGenre;
    }

    public function setMovieDuration($newMovieDuration): void {
        $this->movie_duration = $newMovieDuration;
    }

    public function setMovieDescription($newMovieDescription): void {
        $this->movie_description = $newMovieDescription;
    }

    public static function getMoviesByCinema(PDO $pdo, string $cinemaID): array {
        $query = "SELECT m.*, r.cinema_id as cinema_id FROM movies m
                  JOIN movie_cinema_relationship r ON m.movie_id = r.movie_id
                  WHERE r.cinema_id = :cinema_id";
    
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $cinemaID, PDO::PARAM_STR);
        $stmt->execute();
    
        $movies = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $movie = new Movie(
                $row['movie_id'],
                $row['movie_title'],
                $row['movie_description'],
                $row['movie_duration'],
                $row['movie_genre'],
                $row['cinema_id']  // Fetch cinema ID from the relationship table
            );
    
            $movies[] = $movie;
        }
    
        return $movies;
    }

    // Fetch all movies
    public static function getAllMovies(PDO $pdo): array {
        $query = "SELECT * FROM movies";

        $stmt = $pdo->prepare($query);
        $stmt->execute();

        $movies = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $movie = new Movie($row['movie_id'], $row['movie_title'], $row['movie_description'], $row['movie_duration'], $row['movie_genre'], $row['cinema_id']);
            $movies[] = $movie;
        }

        return $movies;
    }

    public function addMovie(PDO $pdo, string $cinemaId): int {
        $query = "INSERT INTO movies (movie_title, movie_description, movie_duration, movie_genre)
                  VALUES (:title, :description, :duration, :genre)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $this->movie_title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $this->movie_description, PDO::PARAM_STR);
        $stmt->bindParam(':duration', $this->movie_duration, PDO::PARAM_INT);
        $stmt->bindParam(':genre', $this->movie_genre, PDO::PARAM_STR);
    
        $result = $stmt->execute();
    
        if ($result) {

            // Retrieve the last inserted movie ID
            $lastInsertId = $pdo->lastInsertId();
   
            // Associate the movie with the cinema
            $query = "INSERT INTO movie_cinema_relationship (movie_id, cinema_id)
                      VALUES (:movie_id, :cinema_id)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':movie_id', $lastInsertId, PDO::PARAM_INT);
            $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
            $result = $stmt->execute();

            if (!$result) {
                // Handle the case where the association fails
                throw new Exception("Failed to associate movie with cinema.");
            }
            return $lastInsertId;
        } else {
            // Handle the case where the movie insertion fails
            throw new Exception("Failed to add movie.");
        }
    }

    public static function addExistingMovieToCinema(PDO $pdo, $movieId, $cinemaId): bool
    {
        // Check if the movie and cinema IDs are valid
        if (!$movieId || !$cinemaId) {
            return false;
        }

        // Check if the movie is already associated with the cinema
        $query = "SELECT COUNT(*) FROM movie_cinema_relationship WHERE movie_id = :movie_id AND cinema_id = :cinema_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Movie is already associated with the cinema
            return false;
        }

        // Associate the movie with the cinema
        $query = "INSERT INTO movie_cinema_relationship (movie_id, cinema_id)
                  VALUES (:movie_id, :cinema_id)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);

        return $stmt->execute();
    }

    
    // Method to check if a movie already exists
    public static function getExistingMovieId(PDO $pdo, $title, $duration): ?int
    {
        $query = "SELECT movie_id FROM movies WHERE movie_title = :title AND movie_duration = :duration";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        $stmt->execute();
        $existingMovieId = $stmt->fetchColumn();
        return $existingMovieId ? (int) $existingMovieId : null;
    }

    public static function searchMovies(PDO $pdo, string $searchQuery): array {
        $query ="SELECT * FROM movies WHERE movie_title LIKE :search_query";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search_query', '%' . $searchQuery . '%', PDO::PARAM_STR);
        $stmt->execute();
        $movies = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $movie = new Movie(
                $row['movie_id'],
                $row['movie_title'],
                $row['movie_description'],
                $row['movie_duration'],
                $row['movie_genre']
            );
            $movies[] = $movie;
        }
        return $movies;
    }

    public static function deleteMovie(PDO $pdo, $cinemaId, $movieId) {
        try {
            // Fetch sessions related to this movie
            $sessions = Session::getSessionsByMovie($pdo, $cinemaId, $movieId);
            
            // Initialize a variable to keep track of total booking count
            $totalBookingCount = 0;
    
            // Iterate over each session to calculate total booking count
            foreach ($sessions as $session) {
                $sessionId = $session->getId();
                $bookingCount = Session::getBookingCountForSession($pdo, $sessionId);
                $totalBookingCount += $bookingCount;
            }
    
            // Check if there are any bookings for this movie
            if ($totalBookingCount > 0) {
                // Notify admin about existing bookings
                echo "Warning: This movie is attached to sessions with existing bookings. Deleting it will remove all associated bookings.";
            } else {
                // Begin a transaction to ensure data consistency
                $pdo->beginTransaction();
    
                // Delete records from movie_cinema_relationship table
                $stmt = $pdo->prepare("DELETE FROM movie_cinema_relationship WHERE movie_id = :movie_id");
                $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
                $stmt->execute();
    
                // Delete records from session_movies table
                $stmt = $pdo->prepare("DELETE FROM session_movies WHERE movie_id = :movie_id");
                $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
                $stmt->execute();
    
                // Commit the transaction if all queries succeed
                $pdo->commit();
            }
        } catch (PDOException $e) {
            // Rollback the transaction if any query fails
            $pdo->rollback();
            // Handle PDO exceptions
            echo "Error: " . $e->getMessage();
        }
    }
}
