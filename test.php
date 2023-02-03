<html>

  <head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Gesla</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" type="text/css" href="test.css" />

	</head>

  <body">

    <div class="table-container">
        <table id="tabela">
          <tr class="table-head">
            <td></td> <!-- Add an empty cell for the circle indicator -->
            <td>Website</td>
            <td>Username</td>
            <td>Password</td>
            <td>Date of Entry</td>
          </tr>
          


        </table>
        <button id="myBtn">Enter new data</button>
        
      </div>
       
      

      <script>

function getPasswords() {
  // Read the JSON file
  var token = localStorage.getItem("token");
  console.log("TOKEN: " + token);
  var povezava = "http://localhost/password_manager/gesla.php?token=" + token;
  var request = new Request(povezava, {
    method: "GET"
  });

  fetch(request)
    .then(response => response.json())
    .then(data => {
      var passwordTable = document.getElementById("tabela");
      data.Friends.forEach(item => {
        // Get the values from the array
        var website = item.website;
        console.log(website);
        var username = item.username;
        var password = item.pass_enc;
        var passwordDots = "•".repeat(password.length);
        var security = item.security;

        // Get the current date
        var date = new Date().toLocaleDateString();

        // Create the table row
        var row = document.createElement("tr");
        var securityCell = document.createElement("td");
        var websiteCell = document.createElement("td");
        var usernameCell = document.createElement("td");
        var passwordCell = document.createElement("td");
            passwordCell.classList.add("password-cell");
        var dateCell = document.createElement("td");
        var actionsCell = document.createElement("td");
        var revealBtn = document.createElement("button");
        var editBtn = document.createElement("button");
            editBtn.innerHTML = "EDIT";
            editBtn.onclick = function(){
            window.open('formUpdate.php', '_blank', 'width=600,height=600');
            }
            document.body.appendChild(editBtn);
        var deleteBtn = document.createElement("button");
            deleteBtn.innerHTML = "DELETE";
            deleteBtn.onclick = function(){
            deleteRow(website,username);
            }
            document.body.appendChild(deleteBtn);

        //tipka za razkritje gesla
        revealBtn.addEventListener("click", function(){
            passwordCell.textContent = password;
        });
        passwordCell.addEventListener("mouseout", function() {
    passwordCell.textContent = passwordDots;
});
        

        //create class for circle-indicator
        var circleIndicator = document.createElement("div");
        circleIndicator.classList.add("circle-indicator");
        if (security.secure) {
            circleIndicator.classList.add("green");
            circleIndicator.setAttribute("title", security.reason);
        } else {
            circleIndicator.classList.add("red");
            circleIndicator.setAttribute("title", security.reason);
        }
        circleIndicator.addEventListener("mouseover", function(event) {
            showReason(event.target);
        });
        securityCell.appendChild(circleIndicator);

        websiteCell.textContent = website;
        usernameCell.textContent = username;
        passwordCell.textContent = passwordDots;
        dateCell.textContent = date;
        revealBtn.textContent = "Reveal";
        revealBtn.classList.add("reveal-password-button");
        editBtn.textContent = "Edit";
        editBtn.classList.add("edit-entry-button");
        actionsCell.appendChild(revealBtn);
        actionsCell.appendChild(editBtn);
        actionsCell.appendChild(deleteBtn);
        row.appendChild(securityCell);
        row.appendChild(websiteCell);
        row.appendChild(usernameCell);
        row.appendChild(passwordCell);
        row.appendChild(dateCell);
        row.appendChild(actionsCell);
passwordTable.appendChild(row);
});
});
}

// Function to show the security reason
function showReason(element) {
    //alert(element.getAttribute("title"));
    toastr.info('Form submitted successfully!');
}

// Call the function to make the GET request
getPasswords();


//ob pritisku delete 
function deleteRow(website,username) {
    var token = localStorage.getItem("token");
    var data = {
        website: website,
        username: username,
        token: token
    };
    console.log(data);
    fetch("http://localhost/password_manager/gesla.php", {
            method: 'DELETE',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .then(window.location.reload());
}



      </script>

      
      <script> //pokaže razlog za varnost gesla
        function showReason(x) {
            //alert(x.getAttribute("title"));
            toastr.info(x.getAttribute("title"));
        }
      </script>



      <script> //odpre okno za novo geslo
        var btn = document.getElementById("myBtn");
        btn.onclick = function() {
          window.open("form.php", "Small Window", "height=400, width=500, left=200, top=200");
        }
        window.addEventListener("beforeunload", function() {
    window.location.reload();
});

        $(document).ready(function() {
  $(".edit-entry-button").click(function() {
    window.open("formUpdate.php", "Update Form", "width=500, height=400, left=200, top=200");
  });
});
      </script>

      <script>
        $("table").on('click', 'button', function(e) {
          var rowIndex = $(this).closest('tr').index();
          //rowIndex = Math.floor(rowIndex/2);
          rowIndex -= 1; 
          //var colIndex = $(this).closest('td').index()
          //console.log(`Row index is ${rowIndex} & button index is ${colIndex}`)
          console.log(`Row index is ${rowIndex}`);
          document.cookie = "row=" + (rowIndex);
        });        
      </script>



  </html>