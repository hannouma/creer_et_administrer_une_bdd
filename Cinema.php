<?php
class Cinema {
    private $cinema_id; 
    private $name;
    private $location;
    private $halls_number;

    public function __construct($cinema_id, $name, $location, $halls_number) {
        $this->cinema_id = $cinema_id;
        $this->name = $name;
        $this->location = $location;
        $this->halls_number = $halls_number;
    }

    public function getCinemaID(): string {
        return $this->cinema_id; 
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function getHallsNumber(): int {
        return $this->halls_number;
    }

    public function setName(string $newName): void {
        $this->name = $newName;
    }

    public function setLocation(string $newLocation): void {
        $this->location = $newLocation;
    }

    public function setHallsNumber(int $newHallsNumber): void {
        $this->halls_number = $newHallsNumber;
    }

    public static function getCinemas(PDO $pdo): array {
        $query = "SELECT * FROM cinemas";
        $stmt = $pdo->query($query);

        $cinemas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cinema = new Cinema(
                $row['cinema_id'],
                $row['name'],
                $row['location'],
                $row['halls_number']
            );

            $cinemas[] = $cinema;
        }

        return $cinemas;
    }
}
