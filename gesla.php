<?php
$DEBUG = true;

$sql_host = "localhost";
$sql_user = "root";
$sql_password = "";
$sql_db = "db_pass_mngr";

$zbirka = mysqli_connect($sql_host, $sql_user, $sql_password, $sql_db);
mysqli_select_db(mysqli_connect($sql_host, $sql_user, $sql_password, $sql_db),$sql_db);

header('Content-Type: application/json');	// Nastavimo MIME tip vsebine odgovora
header('Access-Control-Allow-Origin: *');	// Dovolimo dostop izven trenutne domene (CORS)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');		//v preflight poizvedbi za CORS sta dovoljeni le metodi GET in POST

switch($_SERVER["REQUEST_METHOD"]){
    case 'POST':
        if(preveri_prijavo()){           
            // Retrieve the username from the request body
            $request_body = json_decode(file_get_contents('php://input'), true);
            $usernameMaster = $request_body["master_username"];
			$userMaster_id = getUserMasterId($usernameMaster);

			// izdelava tokna
			$headers = array('alg'=>'HS256','typ'=>'JWT');
            //$payload = array('sub'=>'1234567890','name'=>'projekt', 'admin'=>true, 'exp'=>(time() + 6000));
			$payload = ['exp' => time() + 3600];
			$secret = 'secret';
			$user_id = $userMaster_id;
            $jwt = generate_jwt($headers, $payload, $secret, $user_id); //IZDELAVA TOKNA OB USPESNI PRIJAVI
            echo($jwt);

        }
        break;
    default:
        //http_response_code(405);
}



switch($_SERVER["REQUEST_METHOD"])		// Glede na HTTP metodo v zahtevi izberemo ustrezno dejanje nad virom
{
	case 'GET':
		if (true) {

			if(!empty($_GET["website"]))
			{
				pridobi_geslo($_GET["website"]);		// Če odjemalec posreduje vzdevek, mu vrnemo podatke izbranega igralca
			}
			else
			{
				pridobi_vsa_gesla();					// Če odjemalec ne posreduje vzdevka, mu vrnemo podatke vseh igralcev
			}
			break;
        }
		//$userMaster_id = getUserMasterId($usernameMaster);

		
	// Dopolnite še z dodajanjem, posodabljanjem in brisanjem igralca
	case 'POST':
		//if(!empty($_GET["token"])){
		//echo $_GET["website"];
			dodaj_geslo2();
			//echo ("vnos uspel. ");
		//}
		//if(preveri_prijavo()==true){echo ("GUCCI");}
		break;
		
	case 'PUT':
		//echo "put";
		if(true)
		{
			//echo "klic funckije";
			posodobi_geslo();
		}
		else
		{
			http_response_code(400);	// Če ne posredujemo vzdevka je to 'Bad Request'
		}
		break;
		
	case 'DELETE':
		$data = json_decode(file_get_contents("php://input"), true);
		if($data === NULL) {
			http_response_code(400);
			die("Invalid JSON format");
		}
		if(empty($data["token"])) {
			http_response_code(401);
			die("Token is missing");
		}
		$user_id = is_jwt_valid($data["token"]);
		if($user_id === FALSE) {
			http_response_code(401);
			die("Invalid token");
		}
		if(!empty($data["website"]) && !empty($data["username"])) {
			izbrisi_geslo($data["website"], $data["username"], $user_id);
		} else {
			http_response_code(400); // Bad Request
		}
		break;
		
	case 'OPTIONS':						//Options dodan zaradi pre-fight poizvedbe za CORS (pri uporabi metod PUT in DELETE)
		//http_response_code(204);
		break;
		
	default:
		//http_response_code(405);		//Če naredimo zahtevo s katero koli drugo metodo je to 'Method Not Allowed'
		break;
}

mysqli_close($zbirka);					// Sprostimo povezavo z zbirko


// ----------- konec skripte, sledijo funkcije -----------

function PridobiIDUporabnika($usern){
    global $zbirka;
    $results = $zbirka->query("SELECT loginform.ID FROM loginform WHERE loginform.user = '$usern'");
    while($row = $results->fetch_assoc()){
        return($row['ID']);
    }
}

function is_password_secure($password, $usedPasswords) {
    $reason = "";
    $date = new DateTime();
    $passwordAge = date_diff(new DateTime($password['date']), $date);
    if(strlen($password['pass_enc']) < 8) {
        $reason .= "Password must be at least 8 characters long. ";
    }
    if(!preg_match("/[a-z]/", $password['pass_enc']) || !preg_match("/[A-Z]/", $password['pass_enc']) || !preg_match("/[0-9]/", $password['pass_enc']) || !preg_match("/[!@#\$%^&\*]/", $password['pass_enc'])) {
        $reason .= "Password must contain lower and upper letters, numbers and special characters !@#\$%^&\*";
    }
    if(in_array($password['pass_enc'], $usedPasswords)) {
        $reason .= "Password cannot be used more than once. ";
    }
    if($passwordAge->format('%m')>6) {
        $reason .= "Password should not be older than 6 months. ";
    }
    if($reason == "") {
        return json_encode(array("secure" => true, "reason" => "Password is secure."));
    } else {
        return json_encode(array("secure" => false, "reason" => $reason));
    }
}

function getUserMasterId($usernameMaster) {
    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "db_pass_mngr");

    // Retrieve the user_id from the loginform table
    $sql = "SELECT id FROM loginform WHERE user='$usernameMaster'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $userMaster_id = $row["id"];

    // Close the connection
    $conn->close();

    return $userMaster_id;
}


function pridobi_vsa_gesla() {
    global $zbirka;
	
	if(isset($_GET["token"])){
		$token = $_GET["token"];
		$is_valid = is_jwt_valid($token, $secret="secret");
		if (!$is_valid) {
            http_response_code(401); // Unauthorized
            echo "Token is not valid";
            return;
        }
		else{
			//STARA KODA
			$odgovor=array();
			$usedPasswords = array();
			$poizvedba="SELECT website, username, pass_enc, date FROM passwords where user_id=" . $is_valid . ";";	
			$rezultat=mysqli_query($zbirka, $poizvedba);
			while($vrstica=mysqli_fetch_assoc($rezultat)) {
				$vrstica['pass_enc'] = decrypt($vrstica['pass_enc']);
				$security = is_password_secure($vrstica, $usedPasswords);
				$vrstica["security"] = json_decode($security, true);
				$odgovor[]=$vrstica;
				$usedPasswords[] = $vrstica['pass_enc'];
			}
			http_response_code(200);
			echo ('{"Friends":' . json_encode($odgovor) . '}');
			//KONEC STARE KODE
		}

	}

}

function pridobi_geslo($website)
{
	global $zbirka;
	$website=mysqli_escape_string($zbirka, $website);
	
	$poizvedba="SELECT website, username, pass_enc, date FROM passwords WHERE website='$website' and user_id=1;";
	
	$rezultat=mysqli_query($zbirka, $poizvedba);

	if(mysqli_num_rows($rezultat)>0)	//igralec obstaja
	{
		$odgovor=mysqli_fetch_assoc($rezultat);
		
		http_response_code(200);		//OK
		echo json_encode($odgovor);
	}
	else							// igralec ne obstaja
	{
		http_response_code(404);		//Not found
	}
}

function dodaj_geslo2()
{
    global $zbirka, $DEBUG;
    $podatki = json_decode(file_get_contents('php://input'), true);
    
    // Check if all required fields are present
    if(isset($podatki["website"], $podatki["username"], $podatki["pass_enc"], $podatki["token"])){
        $website = $podatki["website"];
        $username = $podatki["username"];
        $pass = $podatki["pass_enc"];
        $token = $podatki["token"];

        $is_valid = is_jwt_valid($token, $secret="secret"); // check if token is valid
        if (!$is_valid) {
            http_response_code(401); // Unauthorized
            echo "Token is not valid";
            return;
        }
        $user_id = $is_valid; // get user_id from token validation function
        $date = date('d-m-Y'); // generate date in format dd-mm-yyyy

        $pass_enc = encrypt($pass); 
        
        // check for duplicates
        if(is_jwt_valid($token, "secret"))
        {   
            $poizvedba="INSERT INTO passwords (website, username, pass_enc, date, user_id) VALUES ('$website', '$username', '$pass_enc', '$date', '$user_id')";
            
            if(mysqli_query($zbirka, $poizvedba))
            {
                http_response_code(201);    // Created
                echo ("VNOS USPEL. ");
            }
            else
            {
                http_response_code(500);    // Internal Server Error (not always server's fault!)
                if($DEBUG)    // Warning: returning error data on server is a security risk!
                {
                    pripravi_odgovor_napaka(mysqli_error($zbirka));
                }
            }
        }
        else
        {
            http_response_code(409);    // Conflict
        }
    }
    else {
        //http_response_code(400); // Bad Request
        //echo "Missing required fields";
    }
}


function preveri_prijavo()
{
	global $zbirka, $DEBUG;
	$podatki = json_decode(file_get_contents('php://input'), true);
	
	if(!empty($podatki["master_username"]) && !empty($podatki["master_password"])){

		$username = $podatki["master_username"];
		$password = $podatki["master_password"];
		
		$poizvedba = "SELECT pass_hash, salt FROM loginform WHERE user = '$username'";
		$result2 = $zbirka->query("SELECT pass_hash, salt FROM loginform WHERE user = '$username';");
		$counter = mysqli_num_rows($result2);
		while($row = $result2->fetch_assoc()){
			$pass_hash2 = $row['pass_hash'];
			$salt2 = $row['salt'];
		}
		if(mysqli_query($zbirka, $poizvedba) && $counter>0)
		{
			http_response_code(201);
			$entered_hashed_password = hash('sha256', $salt2 . $password);

			// Compare the entered hashed password to the stored hashed password
			if ($entered_hashed_password === $pass_hash2) {
				return true;
			} 
			else {
				return false;
			}
		}
		else
		{
			http_response_code(500);	// Internal Server Error (ni nujno vedno streznik kriv!)
		}
	}
}


function posodobi_geslo()
{
    global $zbirka, $DEBUG;
    global $secret;
    
    $podatki = json_decode(file_get_contents("php://input"),true);
    
    if(isset($podatki["website"]) && isset($podatki["geslo"]) && isset($podatki["token"]))
    {
        $valid = is_jwt_valid($podatki["token"], $secret);
        if($valid)
        {
            $user_id = $valid;
            $website = $podatki["website"];
            $geslo = encrypt($podatki["geslo"]);
            $poizvedba = "update passwords set pass_enc='$geslo' where (user_id=$user_id and website='$website');";
            
            if(mysqli_query($zbirka, $poizvedba))
            {
                http_response_code(204);  //OK with no content
            }
            else
            {
                http_response_code(500);  // Internal Server Error (not always the server's fault!)
                
                if($DEBUG)   //Warning: returning error data from server is a security risk!
                {
                    mysqli_error($zbirka);
                }
            }
        }
        else
        {
            http_response_code(401);  // Unauthorized
            echo json_encode(array("message" => "Invalid token, please login again."));
        }
    }
    else
    {
        http_response_code(400);  // Bad Request
        echo json_encode(array("message" => "Website, password, and token are required to update the password."));
    }
}
	
function izbrisi_igralca($vzdevek)
{	
	global $zbirka, $DEBUG;
	$vzdevek=mysqli_escape_string($zbirka, $vzdevek);

	if(igralec_obstaja($vzdevek))
	{
		$poizvedba="DELETE FROM igralec WHERE vzdevek='$vzdevek'";
		
		if(mysqli_query($zbirka, $poizvedba))
		{
			http_response_code(204);	//OK with no content
		}
		else
		{
			http_response_code(500);	// Internal Server Error (ni nujno vedno streznik kriv!)
			
			if($DEBUG)	//Pozor: vračanje podatkov o napaki na strežniku je varnostno tveganje!
			{
				pripravi_odgovor_napaka(mysqli_error($zbirka));
			}
		}
	}
	else
	{
		http_response_code(404);	// Not Found
	}
}

function izbrisi_geslo($website, $username) {
    // Connect to the MySQL server
    $conn = new mysqli("localhost", "root", "", "db_pass_mngr");

    // Check for errors
    if ($conn->connect_error) {
        http_response_code(500);
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the DELETE statement
    $stmt = $conn->prepare("DELETE FROM passwords WHERE website = ? AND username = ?");
    $stmt->bind_param("ss", $website, $username);
    if (!$stmt->execute()) {
        http_response_code(500);
        die("Error: " . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows == 0) {
        http_response_code(404);
        die("Error: No matching record found");
    } else {
        http_response_code(204);
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}

function generate_jwt($headers, $payload, $secret = 'secret', $user_id) {
    $payload['user_id'] = $user_id;
    $headers_encoded = base64url_encode($headers);
    $payload_encoded = base64url_encode($payload);
    $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);
    $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
    return $jwt;
}

function is_jwt_valid($jwt, $secret = 'secret') {
    $tokenParts = explode('.', $jwt);
    $header = base64_decode($tokenParts[0]);
    $payload = json_decode(base64_decode($tokenParts[1]), true);
    $user_id = $payload['user_id'];
    $signature_provided = $tokenParts[2];

    $expiration = $payload['exp'];
    $is_token_expired = ($expiration - time()) < 0;

    $base64_url_header = base64url_encode($header);
    $base64_url_payload = base64url_encode($payload);
    $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
    $base64_url_signature = base64url_encode($signature);

    $is_signature_valid = ($base64_url_signature === $signature_provided);
	
    if ($is_token_expired || !$is_signature_valid) {
        return FALSE;
    } else {
        return $user_id;
    }
}

function base64url_encode($str) {
    return rtrim(strtr(base64_encode(json_encode($str)), '+/', '-_'), '=');
}

function encrypt($plaintext, $key="kljuc123") {
    $ivlen = 16; // explicitly set the length of the IV to 16 bytes
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, "aes-256-cbc", $key, OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    return base64_encode($iv.$hmac.$ciphertext_raw);
}


function decrypt($ciphertext, $key="kljuc123") {
    $c = base64_decode($ciphertext);
    $ivlen = 16; // use the same length of the IV as in the encryption function
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, 32);
    $ciphertext_raw = substr($c, $ivlen+32);
    $original_plaintext = openssl_decrypt($ciphertext_raw, "aes-256-cbc", $key, OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    if (hash_equals($hmac, $calcmac)) {
        return $original_plaintext;
    }
}

//echo "  --  ";
$enctest = encrypt("urban hej");
//echo ($enctest);
//echo "  ";
//echo decrypt($enctest);

?>