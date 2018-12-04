
function checkLoginData() {
    console.log("checking");

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var text = JSON.parse(this.responseText)

            if (text.length === 0) {
                // redirect to system page
                location.href = "/system";
            } else {
                document.getElementById("errorMessage").innerHTML =
                    this.responseText;
            }
        }
    };

    xhttp.open("POST", "/check/user", true);
    xhttp.send(JSON.stringify(
        {
            "email": document.getElementById("emailInput").value,
            "password": document.getElementById("passwordInput").value
        }
      )
    );
}
