<?php
class Booking {
    private $booking_id;
    private $date;
    private $status_id;
    private $payment_amount;
    private $seat_number;
    private $session_id;
    private $user_id;
    private $hall_id;

    public function __construct($booking_id, $date, $status_id, $payment_amount, $seat_number, $session_id, $user_id, $hall_id) {
        $this->booking_id = $booking_id;
        $this->date = $date;
        $this->status_id = $status_id;
        $this->payment_amount = $payment_amount;
        $this->seat_number = $seat_number;
        $this->session_id = $session_id;
        $this->user_id = $user_id;
        $this->hall_id = $hall_id;
    }

    public function getId()
    {
        return $this->booking_id;
    }

    public function getHallID(): int {
        return $this->hall_id;
    }

    public function setBookingId($booking_id) {
        $this->booking_id = $booking_id;
    }

    // Function to calculate the payment amount based on the number of seats for each tariff
    public static function calculatePaymentAmount($fullTariffSeats, $studentSeats, $under14Seats): float {
        $fullTarifPrice = 9.20;
        $studentPrice = 7.60;
        $under14Price = 5.90;

        $totalAmount = ($fullTariffSeats * $fullTarifPrice) + ($studentSeats * $studentPrice) + ($under14Seats * $under14Price);
        $totalAmount = number_format($totalAmount, 2);
        return $totalAmount;
    }

    public function makeBooking($pdo): bool {
        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO bookings (booking_id, date, status_id, payment_amount, seat_number, session_id, user_id, hall_id) 
                                   VALUES (UUID(), NOW(), :status_id, :payment_amount, :seat_number, :session_id, :user_id, :hall_id)");
            
            // Bind parameters
            $stmt->bindParam(':status_id', $this->status_id, PDO::PARAM_INT);
            $stmt->bindParam(':payment_amount', $this->payment_amount, PDO::PARAM_STR);
            $stmt->bindParam(':seat_number', $this->seat_number, PDO::PARAM_INT);
            $stmt->bindParam(':session_id', $this->session_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_STR);
            $stmt->bindParam(':hall_id', $this->hall_id, PDO::PARAM_INT);
            
            // Execute the statement
            $stmt->execute();
    
            // Check if the booking was successfully inserted
            if ($stmt->rowCount() > 0) {
                return true; // Booking inserted successfully
            } else {
                return false; // Booking insertion failed
            }
        } catch (PDOException $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            return false; // Booking insertion failed
        }
    }
}

