<link rel="stylesheet" type="text/css" href="form.css" />

<form id="form">
  <input type="text" name="website" placeholder="Enter website">
  <input type="text" name="username" placeholder="Enter username">
  <input type="text" name="password" placeholder="Enter password">
  <input type="submit" id="myBtn2" value="Save the new entry" onclick="submitForm()">
</form>

<script>
    function submitForm() {
        // Get the form data
        var website = String(document.forms["form"]["website"].value);
        var username = String(document.forms["form"]["username"].value);
        var password = String(document.forms["form"]["password"].value);
        
        // Get the token from the cookie
        //var token = getCookie("token");
        //console.log("TOKEN" + token);

        var token123 = localStorage.getItem("token");
            console.log("HTML STORAGE: " + token123);
        
        // Send the POST request with the form data and token
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "http://localhost/password_manager/gesla.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                console.log(xhr.responseText);
                close();
            }
        }
        var data = JSON.stringify({
            "website": website,
            "username": username,
            "pass_enc": password,
            "token": token123
        });
        console.log(data);
        xhr.send(data);

    }
    
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }

    window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }

</script>
