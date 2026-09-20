<?php
require_once "db_connect.php"; // adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'] ?? 'user';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        // Check duplicate email
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            header("Location: /govconnect/register.php?error=" . urlencode("This email is already registered."));
            exit;
        }

        if ($role === "user") {
            $phone = trim($_POST['phone']);
            $nid = trim($_POST['nid']);
            $dob = $_POST['dob'];
            $location = trim($_POST['location']);

            $sql = "INSERT INTO users (name,email,phone,nid,dob,location,password,role,status) 
                    VALUES (?,?,?,?,?,?,?,'user','active')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name,$email,$phone,$nid,$dob,$location,$password]);

        } elseif ($role === "response") {
            $category = $_POST['category'];
            $incharge_name = $_POST['incharge_name'];
            $incharge_id = $_POST['incharge_id'];
            $incharge_email = $_POST['incharge_email'];
            $incharge_phone = $_POST['incharge_phone'];
            $identification = $_POST['identification'];
            $location = $_POST['location'];
            $phone = $_POST['phone'];   // official phone
            $employee_number = $_POST['employee_number'];

            $sql = "INSERT INTO users 
                (name,email,phone,password,role,category,
                 incharge_name,incharge_id,incharge_email,incharge_phone,
                 identification,location,employee_number,status) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,$email,$phone,$password,'response',$category,
                $incharge_name,$incharge_id,$incharge_email,$incharge_phone,
                $identification,$location,$employee_number,'pending'
            ]);
        }

        $msg = ($role === "response") 
            ? "Response Team registered. Your account is Pending admin approval." 
            : "Registration successful! Please login.";

        header("Location: /govconnect/register.php?success=" . urlencode($msg));
        exit;

    } catch (Exception $e) {
        header("Location: /govconnect/register.php?error=" . urlencode("Error: ".$e->getMessage()));
        exit;
    }
}
?>
