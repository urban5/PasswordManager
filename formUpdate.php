<link rel="stylesheet" type="text/css" href="form.css" />
<form method="post">
  <div>
    <span>Website:</span>
    <input type="text" id="website" name="website" placeholder="Enter website" readonly style="background-color: lightgrey;">
  </div>
  <div>
    <span>Username:</span>
    <input type="text" id="username" name="username" placeholder="Enter username" readonly style="background-color: lightgrey;">
  </div>
  <div>
    <span>Password:</span>
    <input type="text" id="password" name="password" placeholder="Enter password">
  </div>
  <input type="submit" id="myBtn2" value="Save the new entry">
</form>
<script>
    
    const token = localStorage.getItem("token");

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }

    window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }

  document.getElementById("myBtn2").addEventListener("click", function(event) {
  event.preventDefault();
  const newPassword = document.getElementById("password").value;
  const website = document.getElementById("website").value;
  const token = localStorage.getItem("token");
  console.log("YOUR TOKEN: " + token);
  if (!newPassword ) {
    alert("Website, password, and token are required to update the password.");
    return;
  }

  sendPutRequest(newPassword, website, token);
});

function sendPutRequest(newPassword, website, token) {

  newPassword = String(newPassword);
  website = String(website);
  const body = {
    website: website,
    geslo: newPassword,
    token: token
  };

  console.log(body);
  const xhr = new XMLHttpRequest();
  xhr.open("PUT", "http://localhost/password_manager/gesla.php", true);
  xhr.setRequestHeader("Content-type", "application/json");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 204) {
        //alert(newPassword);
      } else {
        alert("Error updating password, please try again later.");
      }
    }
  };
  xhr.send(JSON.stringify(body));
}

    function getJSONData(index, token) {  
      const body = {
            token: "tuki"
        };
        povezavaAPI = "http://localhost/password_manager/gesla.php?token=" + token;
        console.log("POVEZAVA: " + povezavaAPI);
        const xhr = new XMLHttpRequest();
        xhr.open("GET", povezavaAPI, true);
        xhr.setRequestHeader("Content-type", "application/json");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
              const responseJSON = JSON.parse(xhr.responseText);
              const friends = responseJSON.Friends;
              const { website, username, pass_enc } = friends[index];
              document.getElementById("website").value = website;
              document.getElementById("username").value = username;
              document.getElementById("password").value = pass_enc;
              document.getElementById("website").readOnly = true;
              document.getElementById("username").readOnly = true;
              }
              else if(xhr.status !== 200){
              alert("Error getting data, please try again later.")
              }
              };
              xhr.send(JSON.stringify(body));
              }

        var row = getCookie("row");
        //alert(user)
        console.log(row);
        getJSONData(row, token);
</script>