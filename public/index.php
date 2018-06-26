<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}


require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="";
    $dbname="slim-practice";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);

$app->get('/', function($request, $response) {
    $response->write('Bienvenidos a Slim 3 API');
    return $response;
});
/*
$app->get('getEmployes', function($request, $response, $args) {
    $sql = "select * FROM employee";
    try {
        $stmt = getConnection()->query($sql);
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        //header("content-type:application/json");       
        //return json_encode($wines);
        if($wines) {
            $response->withHeader('Content-Type', 'application/json');
            $response->write(json_encode($wines));
            return $response;
        } else { 
            throw new PDOException('No records found');
        }      
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});
*/
// Run app
$app->run();


function getCourseList($request, $response, $args) {
    $sql = "select * FROM tbl_courses WHERE status=1";
    try{
        $stmt = getConnection()->query($sql);
        $row = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $response->withStatus(200)
        ->withJson($row);
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getEmployes($request, $response, $args) { 
    $sql = "select * FROM employee";
    try {
        $stmt = getConnection()->query($sql);
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);        
        return $response->withStatus(200)
        ->withJson($wines);

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


function addEmployee($request) { 
    $emp = json_decode($request->getBody());	
    $sql = "INSERT INTO employee (employee_name, employee_salary, employee_age) VALUES (:name, :salary, :age)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $emp->name);
        $stmt->bindParam("salary", $emp->salary);
        $stmt->bindParam("age", $emp->age);
        $stmt->execute();
        $emp->id = $db->lastInsertId();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateEmployee($request) {
    $emp = json_decode($request->getBody());
	$id = $request->getAttribute('id');
    $sql = "UPDATE employee SET employee_name=:name, employee_salary=:salary, employee_age=:age WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $emp->name);
        $stmt->bindParam("salary", $emp->salary);
        $stmt->bindParam("age", $emp->age);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deleteEmployee($request) {
	$id = $request->getAttribute('id');
    $sql = "DELETE FROM employee WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
		echo '{"error":{"text":"successfully! deleted Records"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
