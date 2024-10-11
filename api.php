<?php

// Set the response content type to JSON
header("Content-Type: application/json; charset=UTF-8");

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Function to get input data from the request body
function getInputData() {
    return json_decode(file_get_contents("php://input"), true);
}

// Handle different HTTP request methods (CRUD operations)
switch ($request_method) {
    case 'GET':
        // Handle GET request (e.g., retrieving data)
        handleGet();
        break;

    case 'POST':
        // Handle POST request (e.g., inserting data)
        handlePost();
        break;

    case 'PUT':
        // Handle PUT request (e.g., updating data)
        handlePut();
        break;

    case 'DELETE':
        // Handle DELETE request (e.g., deleting data)
        handleDelete();
        break;

    default:
        // Handle unsupported request methods
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
        break;
}

// Function to handle GET requests
function handleGet() {
    if (isset($_GET['name']) && isset($_GET['age'])) {
        $name = $_GET['name'];
        $age = $_GET['age'];

        // Simulate fetching data from a database
        echo json_encode([
            "status" => "success",
            "message" => "GET request received",
            "data" => [
                "name" => $name,
                "age" => $age
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid GET parameters"]);
    }
}

// Function to handle POST requests
function handlePost() {
    $input_data = getInputData();
    if(isset($_POST['name'])){
        echo json_encode([
            "status" => "success",
            "message" => "POST request received",
            "data" => [
                "name" => $_POST['name'],
                "age" => $_POST['age']
            ]
        ]);
    }else{
        echo json_encode(["status" => "error", "message" => "Invalid POST data"]);
    }
  //if uper script not work then use below code
  
    // if (isset($input_data['name']) && isset($input_data['age'])) {
    //     $name = $input_data['name'];
    //     $age = $input_data['age'];

    //     // Simulate inserting data into a database
    //     echo json_encode([
    //         "status" => "success",
    //         "message" => "POST request received",
    //         "data" => [
    //             "name" => $name,
    //             "age" => $age
    //         ]
    //     ]);
    // } else {
    //     echo json_encode(["status" => "error", "message" => "Invalid POST data"]);
    // }
}

// Function to handle PUT requests
function handlePut() {
    $input_data = getInputData();
    if (isset($input_data['id']) && isset($input_data['name']) && isset($input_data['age'])) {
        $id = $input_data['id'];
        $name = $input_data['name'];
        $age = $input_data['age'];

        // Simulate updating data in a database
        echo json_encode([
            "status" => "success",
            "message" => "PUT request received",
            "data" => [
                "id" => $id,
                "name" => $name,
                "age" => $age
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid PUT data"]);
    }
}

// Function to handle DELETE requests
function handleDelete() {
    $input_data = getInputData();
    if (isset($input_data['id'])) {
        $id = $input_data['id'];

        // Simulate deleting data from a database
        echo json_encode([
            "status" => "success",
            "message" => "DELETE request received",
            "data" => [
                "id" => $id
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid DELETE data"]);
    }
}

?>
