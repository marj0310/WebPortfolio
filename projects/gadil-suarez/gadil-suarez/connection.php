<?php
$host='localhost';
$username='root';
$password='';
$dbname='badil';

try{
    //Establish the Connection
    $conn = new mysqli($host, $username, $password, $dbname);


    //Check if the form is submitted
    if($_SERVER['REQUEST_METHOD']=='POST'){
        //Retrieve form data
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phoneNum = trim($_POST['phoneNum']);
        $gender = trim($_POST['gender']);
        $password = trim($_POST['password']);

        //Validate inputs

        //Check for duplicate username
        $checkQuery = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            die('<script>alert("Error: Username already exists!"); window.history.back();</script>');
        }

        //Insert data into database
        $insertQuery = "INSERT INTO users (username, email, phoneNum, gender, password) VALUE (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('sssss', $username, $email, $phoneNum, $gender, $password);

        if($stmt->execute()){
            echo '<script>alert("Registration succesful"); window.location.href="login.php";</script>';
        }else{
            echo '<script>alert("Error: Could not register. Please try again."); window.history.back();</script>';
        }

        //Close statement
        $stmt->close();
    }
}catch(Exception $e){
    die('Error: '.$e->getMessage());
}

//Close connection
$conn->close();
?>