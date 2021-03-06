
// global script variables
var csvFile = "";


/**
 * Function for opening upload window after clicking on fake button element
 */
function openUploadWindow(){
    // open upload window
    document.getElementById('csvUploadButton').click();
}


/**
 * Function for handling file upload from user computer to the browser
 */
function handleFileUploaded() {
    // get uploaded file
    var uploadedFile = document.getElementById('csvUploadButton').files[0];

    // check if user selected any file
    if (uploadedFile != undefined) {
        // get uploaded file name
        var name = uploadedFile.name;

        // create file object to send
        csvFile = new FormData();
        csvFile.append("file[]", uploadedFile);

        // show uploaded file name
        document.getElementById('uploadFileName').innerHTML = name;
    }

}


/**
 * Update system data and upload CSV file if it is uploaded
 */
function updateSystemData() {
    // before sending data check if id and email follow specified pattern
    var email = document.getElementById('associationEmail').value;
    var code =  document.getElementById('associationCode').value;
    var emailText = document.getElementsByClassName('ql-editor')[0].innerHTML;
    var emailPattern = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/;
    var codePattern = /^[a-zA-Z0-9]{5,}$/;

    // check if edit data is well formatted
    if (!emailPattern.test(email) || !codePattern.test(code) || emailText.length <= 20) {
        alert("bad id, email text or email data");
        // TODO tell user about bad data ? paper-toast
    } else {
        // TODO set progress bar
        // first send request to upload new codes if file is uploaded
        var file = document.getElementById('csvUploadButton').files[0];
        if (file !== undefined || csvFile != "") {
            // call function
            importCsvCodes();
        }

        // after that update association info
        updateAssociationInfo();

        // reload page after data is updated (wait 2 seconds for asynchronous request to finish)
        setTimeout(function() {
             location.href = "/system";
        }, 2000);

        // TODO alert user that everything passed ok
    }

}


/**
 * Function which sends request to update Association data
 */
function updateAssociationInfo() {
    // create XMLHttpRequest object
    var xhttp = new XMLHttpRequest();

    // check response status code
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.status);
        } else if (this.readyState == 4 && this.status == 404) {
            console.log(this.status);
            // TODO tell to the user
        }
    };

    // send request
    xhttp.open("PUT", '/system/edit');
    xhttp.send(JSON.stringify(
        {
            "email": document.getElementById('associationEmail').value,
            "id": document.getElementById('associationCode').value,
            "text": document.getElementsByClassName('ql-editor')[0].innerHTML // document.getElementById('editor').innerHTML
        }
    ));
}


/**
 * Send uploaded CSV to the server for reading and writing in backend
 */
function importCsvCodes() {
    // create XMLHttpRequest object
    var xhttp = new XMLHttpRequest();

    // check response status code
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("File uploaded");
        } else if (this.readyState == 4 && this.status == 404) {
            // TODO set another way of alerting user about problem
            alert("File not uploaded " + this.status);
        }
    };

    // send request
    xhttp.open("POST", '/upload');
    xhttp.send(csvFile);
}
