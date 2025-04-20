<?php 
class Inventory {
    private $pdo;

    public function __construct() {
        // Database connection code
        $host = 'localhost'; 
        $dbname = 'shop_inventory';
        $username = 'root';
        $password = '';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function login($email, $password) {
        // Prepare SQL query to fetch user by email
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE username = :email"); 
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and if the password matches
        if ($user && md5($password) === $user['password']) {
            return $user; // User is authenticated
        } else {
            return null; // Invalid login credentials
        }
    }
    public function checkLogin(){
		if(empty($_SESSION['userid'])) {
			header("Location:login.php");
		}
	}
}
?>