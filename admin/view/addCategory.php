<?php
session_start();

include "../view/header.php";
include "../view/sidebar.php";
include "../view/navbar.php";
include "../../dbConnection.php"; 

$errors = []; 
$success = ""; 

if (isset($_POST['addCategory'])) {
    $title = trim($_POST['title']);

    try {
        $query = "INSERT INTO category (name) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $title); 

        if ($stmt->execute()) {
            $success = "Category added successfully!";
        } else {
            $errors[] = "Failed to add category: " . $stmt->error; 
        }

        $stmt->close(); 
    } catch (Exception $e) {
        $errors[] = "An error occurred: " . $e->getMessage();
    }
}

?>

<div class="container-fluid page-body-wrapper">
    <div class="card-body px-5 py-5">
        <h3 class="card-title text-left mb-3">Add Category</h3>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="addCategory.php">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control p_input text-light" value="<?php echo isset($title) ? $title : ''; ?>" />
            </div>
            <div class="text-center">
                <button type="submit" name="addCategory" class="btn btn-primary btn-block enter-btn">Add</button>
            </div>
        </form>
    </div>
</div>

<?php 
if (!empty($success)) { 
    header('Location: addCategory.php'); 
}

include "../view/footer.php";
?>