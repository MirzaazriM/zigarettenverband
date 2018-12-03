
function sendEmail() {
    console.log("sending");

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            // document.getElementById("demo").innerHTML =
            //     this.responseText;
        }
    };
    xhttp.open("POST", "/send/email", true);
    xhttp.send(JSON.stringify(
        {"email": document.getElementById("emailInput").value})
    );
}