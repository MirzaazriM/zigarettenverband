
/**
 * Send user email function
 */
function sendEmail() {

    // create XMLHttpRequest
    var xhttp = new XMLHttpRequest();

    // check response and make action
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("returnMessage").innerHTML = "Email sended";

            // TODO if email is successfully sended redirect to certain page ?
        } else if (this.readyState == 4 && this.status == 404) {
            document.getElementById("returnMessage").innerHTML = "Email not sended. Check your email.";
        }
    };

    // send request
    xhttp.open("POST", "/send/email", true);
    xhttp.send(JSON.stringify(
        {
            "email": document.getElementById("emailInput").value
        }
       )
    );
}