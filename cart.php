<?php
include 'header.php';
include 'navbar.php';

?>

<section id="page-header" class="about-header"> 
    <h2>#Cart</h2>
    <p>Let's see what you have.</p>
</section>

<section id="cart" class="section-p1">
    <table width="100%">
        <thead>
            <tr>
                <td>Image</td>
                <td>Name</td>
                <td>Desc</td>
                <td>Quantity</td>
                <td>Price</td>
                <td>Subtotal</td>
                <td>Remove</td>
                <td>Edit</td>
            </tr>
        </thead>

        <tbody>
            <?php
            session_start();

            $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

            if (!empty($cart_items)) {
                foreach ($cart_items as $item) {
                    $image = $item['image'];
                    $name = $item['name'];
                    $desc = $item['desc'];
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $subtotal = $price * $quantity;
                    ?>
                    <tr>
                        <td><img src="<?php echo $image; ?>" alt="product"></td>
                        <td><?php echo $name; ?></td>
                        <td><?php echo $desc; ?></td>
                        <td><?php echo $quantity; ?></td>
                        <td><?php echo '$' . number_format($price, 2); ?></td>
                        <td><?php echo '$' . number_format($subtotal, 2); ?></td>
                        <td>
                            <form method="POST" action="remove_item.php">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                        <td>
                            <a href="edit_item.php?item_id=<?php echo $item['id']; ?>" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='8'>Your cart is empty.</td></tr>";
            }
            ?>
        </tbody>

        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td><strong>Total</strong></td>
                <td><strong>
                    <?php
                    $total = 0;
                    foreach ($cart_items as $item) {
                        $total += $item['price'] * $item['quantity'];
                    }
                    echo '$' . number_format($total, 2);
                    ?>
                </strong></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align: right;">
                    <a href="checkout.php" class="btn btn-success">Checkout</a> 
                </td>
            </tr>
        </tfoot>
    </table>
</section>

<?php include 'footer.php'; ?>