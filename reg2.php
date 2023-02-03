<?php 

$sql_host = "localhost";
$sql_user = "root";
$sql_password = "";
$sql_db = "db_pass_mngr";

$con = mysqli_connect($sql_host, $sql_user, $sql_password, $sql_db);
mysqli_select_db(mysqli_connect($sql_host, $sql_user, $sql_password, $sql_db),$sql_db);


//generate salted hash of a password and write it to DB
function write_to_db($uname, $password) {
    // Generate a random salt
    $salt = bin2hex(random_bytes(32));
    
    $vrednost_null = "null";
  
    // Hash the password with the salt
    $hashed_password = hash('sha256', $salt . $password);
  
    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'db_pass_mngr');
    if ($conn->connect_error) {
      // Handle connection error
      die("Connection failed: " . $conn->connect_error);
    }
  
    // Insert the hashed password and salt into the users table
    $stmt = $conn->prepare("INSERT INTO loginform (id, user, pass_hash, salt) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $vrednost_null, $uname, $hashed_password, $salt);
    if (!$stmt->execute()) {
      // Handle insert error
      die("Insert failed: " . $conn->error);
    }
  
    // Close the connection to the database
    $conn->close();
  }


    function verify_password($password, $hashed_password, $salt) {
    // Hash the entered password with the salt
    $entered_hashed_password = hash('sha256', $salt . $password);

    // Compare the entered hashed password to the stored hashed password
    if ($entered_hashed_password === $hashed_password) {
        return true;
    } else {
        return false;
    }
    }



  
if(isset($_POST['username'])){
    $uname = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM loginform WHERE loginform.user='".$uname."' LIMIT 1";
    $result = mysqli_query($con, $sql);
    if(mysqli_num_rows($result)==0){
        write_to_db($uname, $password);
        echo "Registracija je uspešna. ";
		$_SESSION["sejaUporabniskoIme"] = $uname; //v tej vrstici se nastavi php sejna spremenljivka sejaUporabniskoIme na vnešeno vrednost
    }
    else{
		echo "Registracija ni bila uspešna. Vnešeno uporabniško ime že obstaja.";
		session_unset();
	}
    exit();
}





?>