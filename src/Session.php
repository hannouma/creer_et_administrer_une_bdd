<?php
include_once 'Hall.php';
include_once 'Movie.php';
include_once 'Cinema.php';

class Session {
    private $session_id;
    private $start_time;
    private $end_time;
    private $user;
    private $date;
    private $cinema;

    public function __construct($session_id, $start_time, $end_time, $user, $date, $cinema = null) {
        $this->session_id = $session_id;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->user = $user;
        $this->date = $date;
        $this->cinema = $cinema;
    }

    public function getId(): int {
        return $this->session_id;
    }

    public function getStartTime(): string {
        return $this->start_time;
    }

    public function getEndTime(): string {
        return $this->end_time;
    }

    public function getUser(): string {
        return $this->user->getId();
    }

    public function getMovie(PDO $pdo): ?Movie {
        // Fetch the movie associated with this session
        $query = "SELECT m.*
                  FROM session_movies sm
                  JOIN movies m ON sm.movie_id = m.movie_id
                  WHERE sm.session_id = :session_id";
    
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_id', $this->session_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $movieData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // If movie data is found, create and return a Movie object
        if ($movieData) {
            return new Movie(
                $movieData['movie_id'],
                $movieData['movie_title'],
                $movieData['movie_description'],
                $movieData['movie_duration'],
                $movieData['movie_genre']
            );
        } else {
            // Return null if no movie is associated with this session
            return null;
        }
    }
    

    public function getCinema(): ?Cinema {
        return $this->cinema;
    }
    
    public function getDate() {
        return date("d-m-Y", strtotime($this->date));
    }

    public function getHall(PDO $pdo, $sessionId): Hall {
        $query = "SELECT halls.*, session_halls.*
            FROM halls
            INNER JOIN session_halls ON halls.hall_id = session_halls.hall_id
            WHERE session_halls.session_id = :session_id";
    
        try {

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);            
            $stmt->execute();
    
            $hallData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($hallData !== false) { // Check if fetch was successful
                // Create a Hall object using the fetched data
                return new Hall($hallData['hall_id'], $hallData['cinema_id'], $hallData['hall_name'], $hallData['hall_capacity']);
            } else {
                throw new RuntimeException("Hall not found for Session ID: {$this->session_id}");
            }
        } catch (PDOException $e) {
            //var_dump($e->getMessage());
            throw new RuntimeException("Error fetching hall for Session ID: {$this->session_id}");
        }
    }

    // Add a new session to the database
    public function addSession(PDO $pdo, $movieId, $startTime, $endTime, $hallId, $cinemaId, $date): array {

        if ($this->user === null) {
            echo 'Error: User information is missing for the session.';
        }

        // Check if the session overlaps with existing sessions in the same hall on the same date
        $query = "SELECT COUNT(*) FROM sessions s 
        JOIN session_halls sh ON s.session_id = sh.session_id 
        WHERE sh.hall_id = :hall_id 
        AND DATE(s.date) = DATE(:date) 
        AND (
            (TIME(:start_time) < TIME(s.end_time) AND TIME(:end_time) > TIME(s.start_time)) 
            OR (TIME(:start_time) = TIME(s.end_time) AND TIME(:end_time) > TIME(s.start_time)) 
            OR (TIME(:start_time) < TIME(s.end_time) AND TIME(:end_time) = TIME(s.start_time))
        )";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':start_time', $startTime, PDO::PARAM_STR);
        $stmt->bindParam(':end_time', $endTime, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':hall_id', $hallId, PDO::PARAM_INT);
        $stmt->execute();
        $existingSessionCount = $stmt->fetchColumn();

        // If the session overlaps with existing sessions, return false or handle the duplication as needed
        if ($existingSessionCount > 0) {
        echo 'Error: A session already takes place at this hour in this hall.';
        return false;
        }

        $query = "INSERT INTO sessions (start_time, end_time, cinema_id, user_id, date) 
                VALUES (STR_TO_DATE(:start_time, '%H:%i'), STR_TO_DATE(:end_time, '%H:%i'), :cinema_id, :user_id, STR_TO_DATE(:date, '%Y-%m-%d'))";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':start_time', $startTime, PDO::PARAM_STR);
            $stmt->bindParam(':end_time', $endTime, PDO::PARAM_STR);

            // Use the provided hallId directly in the query
            $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
            $user_id = $this->user; // $this->user is a string representing the user ID
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $result =  $stmt->execute();

            // Check if the query was executed successfully
            if ($result) {
                // Get the last inserted session ID
                $sessionId = $pdo->lastInsertId();

                // Insert into session_halls relationship table
                $query = "INSERT INTO session_halls (session_id, hall_id) VALUES (:session_id, :hall_id)";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);

                // Use the provided hallId directly in the query
                $stmt->bindParam(':hall_id', $hallId, PDO::PARAM_INT);

                $result = $stmt->execute();

                // Check if the relationship was inserted successfully
                if (!$result) {
                    return false;
                }

                // Insert into session_movies relationship table
                $query = "INSERT INTO session_movies (session_id, movie_id) VALUES (:session_id, :movie_id)";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
                $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);

                $result = $stmt->execute();

                // Check if the relationship was inserted successfully
                if (!$result) {
                    //var_dump($stmt->errorInfo());
                    return false;
                }

                return array(
                    'success' => true,
                    'sessionId' => $sessionId
                );
            } else {
                //var_dump($stmt->errorInfo());
                return false;
            }
        } catch (PDOException $e) {
            // Add var_dump for debugging
            //var_dump($e->getMessage());
            return false;
        }
    }



    public function getSeats(PDO $pdo): int
    {
        // Fetch the hall associated with this session
        $hall = $this->getHall($pdo, $this->session_id);
        
        // Check if the hall is found
        if ($hall) {
            // Return the hall capacity
            return $hall->getHallCapacity($pdo, $this->session_id);
        } else {
            // Handle the case where the hall is not found
            echo "Hall not found for Session ID: {$this->session_id}\n";
            return 0;
        }
    }

    public function getAvailableSeats(PDO $pdo): int
    {
        // Fetch the hall associated with this session
        $hall = $this->getHall($pdo, $this->session_id);
        
        // Check if the hall is found
        if ($hall) {
            // Get the hall capacity
            $hallCapacity = $hall->getHallCapacity($pdo, $this->session_id);
            
            // Fetch the number of seats booked for this session with successful payment (status_id = 2)
            $stmt = $pdo->prepare("SELECT SUM(seat_number) AS booked_seats FROM bookings WHERE session_id = :session_id AND status_id = 2");
            $stmt->bindParam(':session_id', $this->session_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $bookedSeats = $row['booked_seats'] ?? 0;
            
            // Calculate the number of available seats
            $availableSeats = $hallCapacity - $bookedSeats;
            
            return $availableSeats >= 0 ? $availableSeats : 0; // Ensure available seats are non-negative
        } else {
            // Handle the case where the hall is not found
            echo "Hall not found for Session ID: {$this->session_id}\n";
            return 0;
        }
    }


    // Update an existing session in the database
    public function updateSession(PDO $pdo, $sessionId, $startTime, $endTime, $hallId, $newSeatsCapacity, $date, $userId): bool {

        try {
            // Check if the session overlaps with existing sessions in the same hall on the same date
            $query = "SELECT COUNT(*) FROM sessions s 
            JOIN session_halls sh ON s.session_id = sh.session_id 
            WHERE sh.hall_id = :hall_id 
            AND DATE(s.date) = DATE(:date) 
            AND s.session_id != :session_id 
            AND (
                (TIME(:start_time) < TIME(s.end_time) AND TIME(:end_time) > TIME(s.start_time)) 
                OR (TIME(:start_time) = TIME(s.end_time) AND TIME(:end_time) > TIME(s.start_time)) 
                OR (TIME(:start_time) < TIME(s.end_time) AND TIME(:end_time) = TIME(s.start_time))
            )";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':start_time', $startTime, PDO::PARAM_STR);
            $stmt->bindParam(':end_time', $endTime, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':hall_id', $hallId, PDO::PARAM_INT);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
            $stmt->execute();
            $existingSessionCount = $stmt->fetchColumn();

            // If the session overlaps with existing sessions and it's not the session being updated, return false
            if ($existingSessionCount > 0 ) {
            echo 'Error: A session already takes place at this hour in this hall.';
            return false;
            }
            $pdo->beginTransaction();

            // Update the hall capacity
            $query = "UPDATE halls SET hall_capacity = :new_seats_capacity WHERE hall_id = :hall_id";
            $stmt = $pdo->prepare($query);
            $hall = $this->getHall($pdo, $this->session_id);
            $hallId = $hall->getHallID();
            $stmt->bindParam(':new_seats_capacity', $newSeatsCapacity, PDO::PARAM_INT);
            $stmt->bindParam(':hall_id', $hallId, PDO::PARAM_INT);
            $stmt->execute();

            // Update the session
            $query = "UPDATE sessions 
                    SET start_time = STR_TO_DATE(:start_time, '%H:%i'), 
                        end_time = STR_TO_DATE(:end_time, '%H:%i'),  
                        date = STR_TO_DATE(:date, '%Y-%m-%d'),
                        user_id = :user_id 
                    WHERE session_id = :session_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':start_time', $startTime, PDO::PARAM_STR);
            $stmt->bindParam(':end_time', $endTime, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
            $result = $stmt->execute();
            $pdo->commit();
            return $result;

        } catch (PDOException $e) {
            $pdo->rollBack();
            //var_dump($e->getMessage());
            error_log('PDOException: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            throw new RuntimeException("Error updating session with ID: {$this->session_id}");
        }
    }

    // Delete an existing session from the database
    public function deleteSession(PDO $pdo): bool {
        try {
            // Start a transaction
            $pdo->beginTransaction();

            // Delete the session record from the sessions table
            $query = "DELETE FROM sessions WHERE session_id = :session_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':session_id', $this->session_id, PDO::PARAM_INT);
            $stmt->execute();

            // Commit the transaction
            $pdo->commit();

            return true;
        } catch (PDOException $e) {
            // An error occurred, rollback the transaction
            $pdo->rollBack();

            // Handle the exception as needed
            throw new RuntimeException("Error deleting session: {$e->getMessage()}");
        }
    }

    // Fetch sessions from the database
    public static function getSessions(PDO $pdo, $cinemaId): array {
        $query = "SELECT 
            s.session_id AS session_id, 
            s.start_time, 
            s.end_time, 
            s.date,
            m.movie_id, 
            m.movie_title, 
            m.movie_description, 
            m.movie_duration, 
            m.movie_genre,
            c.cinema_id AS cinema_id, 
            c.name, 
            c.location,
            h.hall_id AS hall_id, 
            h.hall_name,
            ci.halls_number, 
            h.hall_capacity,
            u.user_id, 
            u.username, 
            u.email
        FROM sessions s
        JOIN session_halls sh ON s.session_id = sh.session_id
        JOIN halls h ON sh.hall_id = h.hall_id
        LEFT JOIN session_movies sm ON s.session_id = sm.session_id
        LEFT JOIN movies m ON sm.movie_id = m.movie_id
        LEFT JOIN cinemas c ON h.cinema_id = c.cinema_id
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN cinemas ci ON c.cinema_id = ci.cinema_id
        WHERE h.cinema_id = :cinema_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
        $stmt->execute();

        $sessions = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Create Cinema object
            $cinema = isset($row['cinema_id'])
                ? new Cinema($row['cinema_id'], $row['name'], $row['location'], $row['halls_number'])
                : null;

            // Create Hall object
            $hall = isset($row['hall_id'])
                ? new Hall($row['hall_id'], $cinema, $row['hall_name'], $row['hall_capacity'])
                : null;

            // Create User object
            $user = isset($row['user_id']) ? new User($row['user_id'], $row['username'], $row['email'], '', $row['cinema_id']) : null;

            // Create Session object with Cinema and Hall
            $session = new Session($row['session_id'], $row['start_time'], $row['end_time'], $user, $row['date'], $cinema, $hall);

            $sessions[] = $session;
        }

        return $sessions;
    }

    public static function getSessionsBySessionId(PDO $pdo, $sessionId): array {
        $query = "SELECT s.session_id, s.start_time, s.end_time, s.date, m.*, c.*, h.*, sh.hall_id, u.*
        FROM sessions s
        JOIN session_halls sh ON s.session_id = sh.session_id
        JOIN halls h ON sh.hall_id = h.hall_id
        LEFT JOIN session_movies sm ON s.session_id = sm.session_id
        LEFT JOIN movies m ON sm.movie_id = m.movie_id
        LEFT JOIN cinemas c ON h.cinema_id = c.cinema_id
        LEFT JOIN users u ON s.user_id = u.user_id
        WHERE s.session_id = :session_id";
    
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
        $stmt->execute();
    
        $sessions = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Create Cinema object
            $cinema = isset($row['cinema_id'])
                ? new Cinema($row['cinema_id'], $row['name'], $row['location'], $row['halls_number'])
                : null;
    
            // Create Hall object
            $hall = isset($row['hall_id'])
                ? new Hall($row['hall_id'], $cinema, $row['hall_name'], $row['hall_capacity'])
                : null;
    
            // Create User object
            $user = isset($row['user_id']) ? new User($row['user_id'], $row['username'], $row['email'], '', $row['cinema_id']) : null;
    
            // Create Session object with Cinema and Hall
            $session = new Session($row['session_id'], $row['start_time'], $row['end_time'], $user, $row['date'], $cinema, $hall);
    
            $sessions[] = $session;
        }
        return $sessions;
    }

    // Fetch sessions for a specific movie in a cinema
    public static function getSessionsByMovie(PDO $pdo, $cinemaId, $movieId): array
    {
        $query = "SELECT s.session_id AS session_id, s.start_time, s.end_time, s.date,
                    h.hall_id AS hall_id, h.hall_name
                FROM sessions s
                JOIN session_halls sh ON s.session_id = sh.session_id
                JOIN halls h ON sh.hall_id = h.hall_id
                LEFT JOIN session_movies sm ON s.session_id = sm.session_id
                WHERE sm.movie_id = :movie_id AND h.cinema_id = :cinema_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->execute();

        $sessions = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Do not create Hall object, instead, store hall_id and hall_name
            $hall = [
                'hall_id' => $row['hall_id'],
                'hall_name' => $row['hall_name']
            ];

            // Create Session object with Hall information
            $session = new Session($row['session_id'], $row['start_time'], $row['end_time'], null, $row['date'], $hall);

            $sessions[] = $session;
        }

        return $sessions;
    }

    public static function getBookingCountForSession($pdo, $sessionId): int {
        try {
            // Prepare SQL statement to count bookings for the given session_id
            $stmt = $pdo->prepare("SELECT COUNT(*) AS booking_count FROM bookings WHERE session_id = :session_id");

            // Bind session_id parameter
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);

            // Execute the statement
            $stmt->execute();

            // Fetch the result
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if result is not empty
            if ($result && isset($result['booking_count'])) {
                return (int) $result['booking_count']; // Return the booking count
            } else {
                return 0; // Return 0 if no bookings found
            }
        } catch (PDOException $e) {
            //echo "Error: " . htmlspecialchars($e->getMessage());
            return 0; // Return 0 in case of error
        }
    }
}
