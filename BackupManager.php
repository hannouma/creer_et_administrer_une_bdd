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
    public static function backupDatabase() {
        $backupDir = 'backups/';
        $backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $errorLogFile = $backupDir . 'backup_error.log'; // Path to error log file
        
        // Create the backup directory if it doesn't exist
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        
        // Execute mysqldump command to create a backup
        $command = 'mysqldump -h localhost -u user.php -pCinem@d4t4B@$e cinemaBDD > ' . $backupFile . ' 2> ' . $errorLogFile;
        exec($command, $output, $returnVar);
        
        // Check if the backup was successful
        if ($returnVar === 0) {
            echo 'Backup completed successfully.';
        } else {
            echo 'Backup failed: ';
        }
    }
    public static function restoreLatestBackup() {
        $backupDir = 'backups/';
        
        // Get the list of backup files
        $backupFiles = glob($backupDir . '*.sql');
        
        // Check if any backup files exist
        if (empty($backupFiles)) {
            echo 'No backup files found.';
            return;
        }
        
        // Sort the backup files by modification time (latest first)
        rsort($backupFiles);
        
        // Get the path of the latest backup file
        $latestBackupFile = $backupFiles[0];
        
        // Execute mysql command to restore the database
        $command = 'mysql -h localhost -u user.php -pCinem@d4t4B@$e cinemaBDD < ' . $latestBackupFile;
        exec($command, $output, $returnVar);
        
        // Check if the restore was successful
        if ($returnVar === 0) {
            echo 'Restore completed successfully using backup file: ' . basename($latestBackupFile);
        } else {
            echo 'Restore failed.';
        }
    }
}

?>
