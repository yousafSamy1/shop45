<?php
session_start();

include "header.php";
include "navbar.php";
include "dbConnection.php"; 

$errors = [];

if (isset($_POST['signup'])) {
    $username = trim($_POST['UserName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errors[] = "Username must be between 3-20 characters and can only contain letters, numbers, and underscores.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if (empty($phone)) {
        $errors[] = "Phone is required.";
    } elseif (!preg_match("/^01[0-1,2,5][0-9]{8}$/", $phone)) {
        $errors[] = "Invalid phone number. It should be a valid Egyptian phone number (e.g., 01012345678).";
    }

    if (empty($address)) {
        $errors[] = "Address is required.";
    }

    if (empty($errors)) {
        $query = "SELECT id FROM users WHERE email = ? OR phone = ?"; 
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email or Phone number is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'user')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sssss', $username, $email, $hashedPassword, $phone, $address);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id; 
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';

                header('Location: shop.php');
                exit();
            } else {
                $errors[] = "Something went wrong. Please try again later.";
            }
        }

        $stmt->close();
    }
}
?>

<div class="card-body px-5 py-5" style="background-color:darkgray;">
    <h3 class="card-title text-left mb-3">Register</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control p_input" name="UserName" value="<?php echo isset($username) ? $username : ''; ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control p_input" name="email" value="<?php echo isset($email) ? $email : ''; ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control p_input" name="password">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" class="form-control p_input" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>">
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" class="form-control p_input" name="address" value="<?php echo isset($address) ? $address : ''; ?>">
        </div>
        <div class="text-center">
            <button type="submit" name="signup" class="btn btn-primary btn-block enter-btn">Signup</button>
        </div>
        <p class="sign-up text-center">Already have an Account? <a href="login.php"> Login</a></p>
        <p class="terms">By creating an account you are accepting our <a href="#">Terms & Conditions</a></p>
    </form>
</div>

<?php include "footer.php"; ?>