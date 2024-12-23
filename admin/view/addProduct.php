<?php
session_start();

include "../view/header.php";
include "../view/sidebar.php";
include "../view/navbar.php";
include "../../dbConnection.php";

$errors = []; 
$success = ""; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addProduct'])) {
    $category = trim($_POST['category']);
    $name = trim($_POST['name']); 
    $description = trim($_POST['desc']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $image = $_FILES['img'];

    if (!$category) {
        $errors[] = "Category is required.";
    }
    if (!$name) {
        $errors[] = "Product name is required.";
    }
    if (!$description) {
        $errors[] = "Product description is required.";
    }
    if (!$price || !is_numeric($price) || $price <= 0) {
        $errors[] = "Price must be a valid number greater than 0.";
    }
    if (!$quantity || !is_numeric($quantity) || $quantity <= 0) {
        $errors[] = "Quantity must be a valid number greater than 0.";
    }
    if (empty($image['name'])) {
        $errors[] = "Product image is required.";
    }

    if (empty($errors)) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $imageFileType = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        if (!in_array($imageFileType, $allowedExtensions)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            $maxFileSize = 2097152; 
            if ($image['size'] > $maxFileSize) {
                $errors[] = "File size exceeds the limit.";
            } else {
                $imageName = uniqid() . '.' . $imageFileType; 
                $targetDir = "../../uploads/";

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true); 
                }

                $targetFile = $targetDir . $imageName;

                if (!move_uploaded_file($image['tmp_name'], $targetFile)) {
                    $errors[] = "Failed to upload image.";
                }
            }
        }
    }

    if (empty($errors)) {
        $categoryQuery = "SELECT id FROM category WHERE name = ?";
        $categoryStmt = $conn->prepare($categoryQuery);
        $categoryStmt->bind_param('s', $category);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        $categoryRow = $categoryResult->fetch_assoc();

        if ($categoryRow) {
            $categoryId = $categoryRow['id'];

            $query = "INSERT INTO products (category_id, name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issdis', $categoryId, $name, $description, $price, $quantity, $imageName);

            if ($stmt->execute()) {
                $success = "Product added successfully!";
            } else {
                $errors[] = "Failed to add product: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errors[] = "Category not found.";
        }

        $categoryStmt->close();
    }
}

?>

<div class="card-body px-5 py-5">
    <h3 class="card-title text-left mb-3">Add Product</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="addProduct.php" enctype="multipart/form-data">
        <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control p_input">
                <?php 
                    $categoryQuery = "SELECT name FROM category";
                    $categoryResult = $conn->query($categoryQuery);
                    while($row = $categoryResult->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Name</label> 
            <input type="text" name="name" class="form-control p_input" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="desc" class="form-control p_input"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea> 
        </div>
        <div class="form-group">
            <label>Price</label>
            <input type="number" name="price" class="form-control p_input" step="0.01" value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>"> 
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control p_input" value="<?php echo isset($quantity) ? htmlspecialchars($quantity) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="img" class="form-control p_input">
        </div>
        <div class="text-center">
            <button type="submit" name="addProduct" class="btn btn-primary btn-block enter-btn">Add</button>
        </div>
    </form>
</div>

<?php 
include "../view/footer.php";
?>

<?php
$query = "SELECT p.id, p.name, p.price, p.image, c.name as category_name 
          FROM products p 
          JOIN category c ON p.category_id = c.id 
          ORDER BY p.created_at DESC"; 
$result = $conn->query($query);
?>

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th> 
            <th>Price</th>
            <th>Category</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td> 
                <td><?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><img src="../../uploads/<?php echo htmlspecialchars($row['image']); ?>" width="50"></td>
                <td>
                    <a href="editProduct.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="deleteProduct.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
$conn->close();
?>