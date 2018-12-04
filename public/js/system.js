
// var global script variables
var csvFile;

function openUploadWindow(){
    // open upload window
    document.getElementById('csvUploadButton').click();
}


function handleFileUploaded() {
    // get upladed file
    var uploadedFile = document.getElementById('csvUploadButton').files[0];

    // get uploaded file name
    var name = uploadedFile.name;

    // create file object to send
    csvFile = new FormData();
    csvFile.append("file[]", uploadedFile);

    // show uploaded file name
    document.getElementById('uploadFileName').innerHTML = name;
}

function updateSystemData() {
    // update association info
    updateAssociationInfo();

    // import csv codes
    // check if any csv file is uploaded
    var file = document.getElementById('csvUploadButton').files[0];
    if (file !== undefined) {
        // if yes upload it to the server
        importCsvCodes();
        console.log("There is file uploaded. Sending...");
    } else {
        console.log("No file uploaded");
    }
}


function updateAssociationInfo() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var text = JSON.parse(this.responseText)
        }
    };
    xhttp.open("PUT", '/system/edit');
    xhttp.send(JSON.stringify(
        {
            "email": document.getElementById('associationEmail').value,
            "id": document.getElementById('associationCode').value,
            "text": document.getElementById('emailText').value
        }
    ));
}


function importCsvCodes() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var text = JSON.parse(this.responseText);
            console.log(text);
        }
    };
    xhttp.open("POST", '/upload');
    xhttp.send(csvFile);
}
