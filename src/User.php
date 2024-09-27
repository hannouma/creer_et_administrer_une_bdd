<?php
class User {
    private $user_id;
    private $username;
    private $email;
    private $password;
    private $cinemaId;
    private array $roles = [];

    public function __construct($user_id, $username, $email, $password, $cinemaId) {
        $this->user_id = $user_id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->cinemaId = $cinemaId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getId(): string
    {
        return $this->user_id;
    }
    public function getCinemaId(): string
    {
        return $this->cinemaId;
    }

    public function addRole(string $role): void
    {
        $this->roles[] = $role;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
    // Fetch a user by ID from the database
    public static function getUserById(PDO $pdo, $userId): ?User {
        $query = "SELECT u.*, GROUP_CONCAT(r.name) as roles 
                  FROM users u 
                  LEFT JOIN userRoles ur ON u.user_id = ur.userId
                  LEFT JOIN roles r ON ur.roleId = r.id
                  WHERE u.user_id = :user_id
                  GROUP BY u.user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
        $stmt->execute();

        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            $user = new User(
                $userData['user_id'],
                $userData['username'],
                $userData['email'],
                $userData['password_hash'],
                $userData['cinema_id']
            );

            $roles = !empty($userData['roles']) ? explode(',', $userData['roles']) : [];
            foreach ($roles as $role) {
                $user->addRole($role);
            }

            return $user;
        } else {
            return null; // User not found
        }
    }
}
