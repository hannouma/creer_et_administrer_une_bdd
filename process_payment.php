<?php
require_once 'Booking.php';
require_once 'BackupManager.php';

// Start the session
session_start([
    'cookie_lifetime' => 86400, // 24 hours session lifetime
    'cookie_secure'   => true,  // Requires HTTPS
    'cookie_httponly' => true,  // Prevents client-side scripts from accessing cookies
    'use_strict_mode' => true   // Regenerates session ID on every request
]);

$dsn = 'mysql:host=localhost;dbname=cinemaBDD';
$username = 'user.php';
$password = 'Cinem@d4t4B@$e';

// Validate form submission and input data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize input values
    $bookingId = htmlspecialchars($_POST['booking_id'], ENT_QUOTES, 'UTF-8');
    $paymentMethod = htmlspecialchars($_POST['payment_method'], ENT_QUOTES, 'UTF-8');

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // Check if a payment with the same booking ID already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE booking_id = :booking_id");
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_STR);
        $stmt->execute();
        $paymentExists = $stmt->fetchColumn();
        if ($paymentExists) {
            // Payment already exists for this booking ID, alert the user
            echo "You have already paid for this booking. Please check your bookings.";
            echo '<button onclick="window.location.href=\'index.php\'">Go Back to Movies</button>';
        } else {
            // Validate payment (replace this with your payment validation logic)
            $paymentValidated = validatePayment($paymentMethod);

            if ($paymentValidated) {
                // Insert new payment record
                // Fetch the payment amount from the bookings table
                $stmt = $pdo->prepare("SELECT payment_amount FROM bookings WHERE booking_id = :booking_id");
                $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_STR);
                $stmt->execute();
                $paymentAmount = $stmt->fetchColumn();
                // Get the payment type ID based on the selected payment method
                $stmt = $pdo->prepare("SELECT id FROM paymentTypes WHERE type_name = :payment_method");
                $stmt->bindValue(':payment_method', $paymentMethod, PDO::PARAM_STR);
                $stmt->execute();
                $paymentTypeId = $stmt->fetchColumn();
                // Insert payment details into payments table
                $stmt = $pdo->prepare("INSERT INTO payments (id, amount, date, payment_type_id, booking_id) VALUES (UUID(), :payment_amount, NOW(), :payment_type_id, :booking_id)");
                $stmt->bindValue(':payment_amount', $paymentAmount, PDO::PARAM_STR);
                $stmt->bindValue(':payment_type_id', $paymentTypeId, PDO::PARAM_INT);
                $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_STR);
                $stmt->execute();
                // Get the last inserted UUID payment ID
                $paymentId = $pdo->lastInsertId();
                // Update booking with payment ID
                $stmt = $pdo->prepare("UPDATE bookings SET status_id = 2 WHERE booking_id = :booking_id");
                $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_STR);
                $stmt->execute();
            
                // Check if the update query was successful
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0) {
                    echo "Payment validated and booking status updated to confirmed!";
                    // Backup payment data
                    $paymentData = array(
                        'booking_id' => $bookingId, // Add the booking ID to payment data
                        'payment_method' => $paymentMethod, // Add the payment method to payment data
                        'payment_amount' => $paymentAmount, // Add the payment amount to payment data
                        'payment_id' => $paymentId, // Add the payment ID to payment data
                        'payment_date' => date('Y-m-d H:i:s')
                    );
                    BackupManager::backupPaymentData($paymentData);
                } else {
                    echo "Failed to update booking status. No rows were affected.";
                }
            } else {
                echo "Payment validation failed. Please try again later.";
            }
        }
    } catch (PDOException $e) {
        echo "Error updating booking status: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Invalid request method. Please submit the form.";
}

// Function to validate payment
function validatePayment($paymentMethod) {
    // I always consider payment validated but i can add further credit card validations and the cash validations here
    return true;
}
?>
