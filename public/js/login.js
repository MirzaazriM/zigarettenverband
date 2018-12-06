/**
 * Check login credentials function
 */
function checkLoginData() {

    // create XMLHttpRequest object
    var xhttp = new XMLHttpRequest();

    // check status code and make appropriete action
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.status);
            location.href = "/system";
        } else if (this.readyState == 4 && this.status == 401) {
            console.log(this.status);
            document.getElementById("errorMessage").innerHTML =
                "Bad credentials";
        } else if (this.readyState == 4 && this.status == 404) {
            console.log(this.status);
            document.getElementById("errorMessage").innerHTML =
                "Bad formatted email or password";
        }
    };

    // send credentials to the backend
    xhttp.open("POST", "/check/user", true);
    xhttp.send(JSON.stringify(
        {
            "email": document.getElementById("emailInput").value,
            "password": document.getElementById("passwordInput").value
        }
      )
    );
}
