<?php
session_start();

include "header.php";
include "navbar.php";
include "dbConnection.php"; 

$errors = []; 
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $query = "SELECT id, username, password, role FROM users WHERE email = ?"; 
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin/view/layout.php'); 
                } else {
                    header('Location: shop.php'); 
                }
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No user found with this email.";
        }
    }
}
?>

<div class="card-body px-5 py-5" style="background-color:darkgray;">
    <h3 class="card-title text-left mb-3">Login</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control p_input" value="<?php echo isset($email) ? $email : ''; ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control p_input">
        </div>
        <div class="form-group d-flex align-items-center justify-content-between">
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input"> Remember me
                </label>
            </div>
            <a href="forgetPassword.php" class="forgot-pass">Forgot password</a>
        </div>
        <div class="text-center">
            <button type="submit" name="login" class="btn btn-primary btn-block enter-btn">Login</button>
        </div>
        <p class="sign-up">Don't have an Account? <a href="signup.php"> Sign Up</a></p>
    </form>
</div>

<?php include "footer.php"; ?>

