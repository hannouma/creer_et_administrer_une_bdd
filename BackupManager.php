<?php
class BackupManager {
    public static function backupUserData($userData) {
        $filePath = 'backup.txt';
        $backupContent = serialize(['userData' => $userData]); // Serialize user data
        
        // Append the serialized data to the backup file
        file_put_contents($filePath, $backupContent, FILE_APPEND);
    }
    public static function backupBookingData($bookingData) {
        $filePath = 'backup.txt';
        $backupContent = serialize(['bookingData' => $bookingData]); // Serialize booking data
        
        // Append the serialized data to the backup file
        file_put_contents($filePath, $backupContent, FILE_APPEND);
    }
    public static function backupSessionData($sessionData) {
        $filePath = 'backup.txt';
        $backupContent = serialize(['sessionData' => $sessionData]); // Serialize session data
        
        // Append the serialized data to the backup file
        file_put_contents($filePath, $backupContent, FILE_APPEND);
    }
    public static function backupPaymentData($paymentData) {
        $filePath = 'backup.txt';
        $backupContent = serialize(['paymentData' => $paymentData]); // Serialize payment data

        // Append the serialized data to the backup file
        file_put_contents($filePath, $backupContent, FILE_APPEND);
    }
}

?>
