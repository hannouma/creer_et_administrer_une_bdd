<?php
include_once 'Cinema.php';

class Hall {
    private $hall_id;
    private $cinema;
    private $hall_name;
    private $hall_capacity;

    public function __construct($hall_id, $cinema, $hall_name, $hall_capacity) {
        $this->hall_id = $hall_id; 
        $this->cinema = $cinema;
        $this->hall_name = $hall_name;
        $this->hall_capacity = $hall_capacity;
    }

    public function getHallID(): int {
        return $this->hall_id;
    }

    public function getCinema(PDO $pdo): Cinema {
        $query = "SELECT * FROM cinemas WHERE cinema_id = :cinema_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $this->cinema->getCinemaID(), PDO::PARAM_STR);
        $stmt->execute();
    
        $cinemaData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($cinemaData) {
            // Create Cinema object based on retrieved data
            $cinema = new Cinema($cinemaData['cinema_id'], $cinemaData['name'], $cinemaData['location'], $cinemaData['halls_number']);
            return $cinema;
        } else {
            // Throw an exception when cinema data is not found
            throw new RuntimeException("Cinema not found for ID: {$this->cinema->getCinemaID()}");
        }
    }
    
    // Fetch all halls from the database associated with a specific cinema
    public static function getHallsByCinema(PDO $pdo, $cinemaId): array {
        $query = "SELECT h.*, c.cinema_id AS cinema_id, c.name AS cinema_name, c.location AS cinema_location, c.halls_number AS cinema_halls_number
                  FROM halls h
                  LEFT JOIN cinemas c ON h.cinema_id = c.cinema_id
                  WHERE c.cinema_id = :cinema_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
        $stmt->execute();

        $halls = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Create Cinema object based on retrieved data
            $cinema = new Cinema(
                $row['cinema_id'],
                $row['cinema_name'] ?? '',
                $row['cinema_location'] ?? '',
                $row['cinema_halls_number'] ?? 0
            );

            // Create Hall object based on retrieved data
            $hall = new Hall($row['hall_id'], $cinema, $row['hall_name'] ?? '', $row['hall_capacity'] ?? 0);

            $halls[] = $hall;
        }

        return $halls;
    }
    

    public function getHallName(): string {
        return $this->hall_name;
    }

    public function getHallCapacity(PDO $pdo, $sessionId): int {
        // Fetch the hall capacity associated with the given session
        $query = "SELECT h.hall_capacity 
                  FROM halls h
                  INNER JOIN session_halls sh ON h.hall_id = sh.hall_id
                  WHERE sh.session_id = :session_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
        $stmt->execute();

        $hallCapacity = $stmt->fetch(PDO::FETCH_COLUMN);

        // If hall capacity is found, return it; otherwise, return 0
        return $hallCapacity ? (int)$hallCapacity : 0;
    }

    public function setCapacity($newCapacity): void {
        $this->hall_capacity = $newCapacity;
    }

    // Add a new hall to the database
    public function addHall(PDO $pdo): bool {
        $query = "INSERT INTO halls (cinema_id, hall_name, hall_capacity) 
                  VALUES (:cinema_id, :hall_name, :hall_capacity)";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $this->cinema->getCinemaID(), PDO::PARAM_STR);
        $stmt->bindParam(':hall_name', $this->hall_name, PDO::PARAM_STR);
        $stmt->bindParam(':hall_capacity', $this->hall_capacity, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Update an existing hall in the database
    public function updateHall(PDO $pdo): bool {
        $query = "UPDATE halls 
                  SET cinema_id = :cinema_id, hall_name = :hall_name, hall_capacity = :hall_capacity
                  WHERE hall_id = :hall_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cinema_id', $this->cinema->getCinemaID(), PDO::PARAM_STR);
        $stmt->bindParam(':hall_name', $this->hall_name, PDO::PARAM_STR);
        $stmt->bindParam(':hall_capacity', $this->hall_capacity, PDO::PARAM_INT);
        $stmt->bindParam(':hall_id', $this->hall_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete an existing hall from the database
    public function deleteHall(PDO $pdo): bool {
        $query = "DELETE FROM halls WHERE hall_id = :hall_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':hall_id', $this->hall_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

}
