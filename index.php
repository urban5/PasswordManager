<html>
  <head>
    <title>Password Manager Login</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body onload="init()">
    <div class="header">
      <img src="lock.png" alt="Cybersecurity Lock Logo">
      <div id="credit">Urban Vidergar, 2022</div>
    </div>
    <div class="login-container">    
      <form method="POST" action="test.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username">
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password">
        <br>
        <button  type="submit">Log In</button>
        <button id="sign-up-button" onclick="redirect_to_reg()">Sign Up</button>
      <br>
      
    </div>
    
    <script type="text/javascript">
        function redirect_to_reg(){
          window.location.href = "registracija.php"
        }
        function init() {
          document.querySelector("form").addEventListener("submit", function(event) {
            event.preventDefault();
            var username = document.querySelector("#username").value;
            var password = document.querySelector("#password").value;
            verifyPassword3(username, password);
          });
        }
        function verifyPassword3(username, password) {
              let url = 'http://localhost/password_manager/gesla.php';
              let body = JSON.stringify({
                  master_username: username,
                  master_password: password
              });

              fetch(url, { //asinhrona, zato uporabimo then, torej da se koda nadaljuje ko dobimo response
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json'
                  },
                  body: body
              })
              .then(response => response.text()) //converts to plaintext
              .then(result => {
                  console.log(result);
                  saveToken(result);
                  var token123 = localStorage.getItem("token");
            console.log(token123);
                  if(result.length >= 1){ // result je token, ki ga vrne api ob pravilni prijavi
                    //save the data to cookies
                    let d = new Date();
                    d.setTime(d.getTime() + (365*24*60*60*1000));
                    let expires = "expires="+ d.toUTCString();
                    document.cookie = "master_username=" + username + ";" + expires + ";path=/";
                    document.cookie = "master_password=" + password + ";" + expires + ";path=/";
                    document.cookie = "token=" + result + ";" + expires + ";path=/";
                    //redirect to test.php
                    
                    window.location.href = "test.php";
                  }
                  return result;
              });
            }

            function saveToken(response) {
                // Get the token from the response
                var token = response;
                
                // Save the token to Local Storage
                localStorage.setItem("token", token);
                console.log("TOKEN: " + token);
            }


    </script>
  </body>
</html>
