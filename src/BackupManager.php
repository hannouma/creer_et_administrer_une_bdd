<?php
class BackupManager {
    // Changed backup directory to a writable location '/tmp/backups/'
    private static $backupDir = '/tmp/backups/';

    public static function backupUserData($userData) {
        // Ensure the backup directory exists
        if (!file_exists(self::$backupDir)) {
            mkdir(self::$backupDir, 0777, true);
        }

        $filePath = self::$backupDir . 'user_data_backup_' . date('Y-m-d_H-i-s') . '.txt';
        $backupContent = serialize(['userData' => $userData]); // Serialize user data

        // Append the serialized data to the backup file
        if (file_put_contents($filePath, $backupContent . PHP_EOL, FILE_APPEND) === false) {
            error_log('Failed to write to backup file: ' . $filePath);
        }
    }

    public static function backupBookingData($bookingData) {
        // Ensure the backup directory exists
        if (!file_exists(self::$backupDir)) {
            mkdir(self::$backupDir, 0777, true);
        }

        $filePath = self::$backupDir . 'booking_data_backup_' . date('Y-m-d_H-i-s') . '.txt';
        $backupContent = serialize(['bookingData' => $bookingData]); // Serialize booking data

        // Append the serialized data to the backup file
        if (file_put_contents($filePath, $backupContent . PHP_EOL, FILE_APPEND) === false) {
            error_log('Failed to write to backup file: ' . $filePath);
        }
    }

    public static function backupSessionData($sessionData) {
        // Ensure the backup directory exists
        if (!file_exists(self::$backupDir)) {
            mkdir(self::$backupDir, 0777, true);
        }

        $filePath = self::$backupDir . 'session_data_backup_' . date('Y-m-d_H-i-s') . '.txt';
        $backupContent = serialize(['sessionData' => $sessionData]); // Serialize session data

        // Append the serialized data to the backup file
        if (file_put_contents($filePath, $backupContent . PHP_EOL, FILE_APPEND) === false) {
            error_log('Failed to write to backup file: ' . $filePath);
        }
    }

    public static function backupPaymentData($paymentData) {
        // Ensure the backup directory exists
        if (!file_exists(self::$backupDir)) {
            mkdir(self::$backupDir, 0777, true);
        }

        $filePath = self::$backupDir . 'payment_data_backup_' . date('Y-m-d_H-i-s') . '.txt';
        $backupContent = serialize(['paymentData' => $paymentData]); // Serialize payment data

        // Append the serialized data to the backup file
        if (file_put_contents($filePath, $backupContent . PHP_EOL, FILE_APPEND) === false) {
            error_log('Failed to write to backup file: ' . $filePath);
        }
    }

    public static function backupDatabase() {
        // Load environment variables
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // Ensure the backup directory exists
        if (!file_exists(self::$backupDir)) {
            mkdir(self::$backupDir, 0777, true);
        }

        $backupFile = self::$backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
        $errorLogFile = self::$backupDir . 'backup_error.log'; // Changed the path to be inside the writable dir

        // Execute mysqldump command to create a backup
        $command = sprintf(
            'mysqldump --single-transaction --quick --lock-tables=false -h %s -P %s -u %s -p%s %s > %s 2>> %s',
            $_ENV['MYSQLHOST'],
            $_ENV['MYSQLPORT'],
            $_ENV['MYSQLUSER'],
            $_ENV['MYSQLPASSWORD'],
            $_ENV['MYSQLDATABASE'],
            $backupFile,
            $errorLogFile
        );
        exec($command, $output, $returnVar);

        // Check if the backup was successful
        if ($returnVar === 0) {
            echo 'Backup completed successfully.';
        } else {
            echo 'Backup failed.';
        }
    }

    public static function restoreLatestBackup() {
        // Ensure the backup directory exists
        if (!file_exists(self::$backupDir)) {
            mkdir(self::$backupDir, 0777, true);
        }

        // Get the list of backup files
        $backupFiles = glob(self::$backupDir . '*.sql.gz');

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
        $command = sprintf(
            'mysql -h %s -P %s -u %s -p%s %s < %s',
            $_ENV['MYSQLHOST'],
            $_ENV['MYSQLPORT'],
            $_ENV['MYSQLUSER'],
            $_ENV['MYSQLPASSWORD'],
            $_ENV['MYSQLDATABASE'],
            $latestBackupFile
        );
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
