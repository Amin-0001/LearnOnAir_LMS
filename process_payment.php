<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// 1. Security Check: Only logged-in students can process payments
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
// 2. Validate incoming data
if (!isset($_POST['course_id']) || !isset($_POST['price'])) {
    echo "<script>alert('Invalid payment request.'); window.location='student_dashboard.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];
$course_id = intval($_POST['course_id']);
$amount = floatval($_POST['price']);
$payment_success = false;
// 3. Prevent Double Enrollment
$check_sql = "SELECT id FROM enrollments WHERE user_id = '$user_id' AND course_id = '$course_id'";
if(mysqli_num_rows(mysqli_query($conn, $check_sql)) > 0) {
    echo "<script>alert('You are already enrolled in this course!'); window.location='view_course.php?id=$course_id';</script>";
    exit();
}
// 4. STEP TWO: Process the Payment
if (isset($_POST['confirm_payment'])) {
    $selected_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    // Grab the last 4 digits to make the receipt look realistic
    $last_four = "0000";
    if ($selected_method == 'gpay' || $selected_method == 'phonepe') {
        $last_four = substr($_POST['phone_number'], -4);
    } else {
        $last_four = substr($_POST['card_number'], -4);
    }
    // Generate a Professional Transaction ID
    $transaction_id = "TXN_" . strtoupper(substr($selected_method, 0, 3)) . "_" . date("Ymd") . "_" . $last_four;
    // Record the payment
    $pay_sql = "INSERT INTO payments (user_id, course_id, amount, transaction_id, payment_status) 
                VALUES ('$user_id', '$course_id', '$amount', '$transaction_id', 'success')";
    if (mysqli_query($conn, $pay_sql)) {
        // Grant access to the course
        $enroll_sql = "INSERT INTO enrollments (user_id, course_id) VALUES ('$user_id', '$course_id')";
        if (mysqli_query($conn, $enroll_sql)) {
            $payment_success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style> body { display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; } </style>
</head>
<body>
    <div class="glass">
        <?php if ($payment_success): ?>
            <div id="loadingUI" style="text-align: center;">
                <div class="spinner"></div>
                <h3 style="margin: 0; color: #007bb5;">Contacting Bank...</h3>
                <p style="opacity: 0.7; font-size: 14px;">Please do not close or refresh this window.</p>
            </div>
            <div id="successUI" style="display: none; text-align: center;">
                <div class="success-mark">✔</div>
                <h3 style="margin: 0; color: #2ecc71;">Payment Successful!</h3>
                <p style="opacity: 0.8; font-size: 14px; margin-top: 10px;">Your enrollment is confirmed.</p>
                <div class="txn-id">Receipt: <?php echo $transaction_id; ?></div>
                <p style="font-size: 12px; opacity: 0.6; margin-top: 20px;">Unlocking course content...</p>
            </div>
            <script>
                // Simulate gateway processing delay 
                setTimeout(() => {
                    document.getElementById('loadingUI').style.display = 'none';
                    document.getElementById('successUI').style.display = 'block';
                    // Redirect to the course player after showing success
                    setTimeout(() => {
                        window.location.href = 'course_details.php?id=<?php echo $course_id; ?>';
                    }, 2500);
                }, 2000);
            </script>
        <?php else: ?>
            <div class="checkout-header">
                <h2>Select Payment Method</h2>
                <div class="pay-amount">₹<?php echo number_format($amount, 2); ?></div>
                <div style="font-size: 13px; opacity: 0.7;">Secure Encrypted Checkout</div>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <input type="hidden" name="price" value="<?php echo $amount; ?>">
                <input type="hidden" name="confirm_payment" value="1">
                <label class="method-card">
                    <input type="radio" name="payment_method" value="gpay" required checked onchange="toggleInputs()">
                    <span class="method-icon">📱</span>
                    <span class="method-name">Google Pay (GPay)</span>
                </label>
                <label class="method-card">
                    <input type="radio" name="payment_method" value="phonepe" required onchange="toggleInputs()">
                    <span class="method-icon">🟣</span>
                    <span class="method-name">PhonePe / UPI</span>
                </label>
                <label class="method-card">
                    <input type="radio" name="payment_method" value="card" required onchange="toggleInputs()">
                    <span class="method-icon">💳</span>
                    <span class="method-name">Credit / Debit Card</span>
                </label>
                <label class="method-card">
                    <input type="radio" name="payment_method" value="cred" required onchange="toggleInputs()">
                    <span class="method-icon">🎖️</span>
                    <span class="method-name">CRED Pay</span>
                </label>
                <div class="dynamic-inputs" id="upi_inputs">
                    <label>Enter 10-Digit Mobile Number linked to UPI</label>
                    <input type="text" name="phone_number" id="phone_field" class="input-field" placeholder="e.g. 9876543210" pattern="\d{10}" maxlength="10" required>
                </div>
                <div class="dynamic-inputs" id="card_inputs" style="display: none;">
                    <label>Card Number</label>
                    <input type="text" name="card_number" id="card_field" class="input-field" placeholder="0000 0000 0000 0000" pattern="\d{16}" maxlength="16">
                    <div class="flex-inputs">
                        <div style="flex: 1;">
                            <label>Expiry Date</label>
                            <input type="text" id="exp_field" class="input-field" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div style="flex: 1;">
                            <label>CVV</label>
                            <input type="password" id="cvv_field" class="input-field" placeholder="•••" maxlength="3">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-pay">Confirm Payment</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="course_details.php?id=<?php echo $course_id; ?>" style="color: rgba(0, 0, 0, 0.5); font-size: 12px; text-decoration: none;">Cancel and Return</a>
            </div>
            <script>
                function toggleInputs() {
                    const method = document.querySelector('input[name="payment_method"]:checked').value;
                    const upiContainer = document.getElementById('upi_inputs');
                    const cardContainer = document.getElementById('card_inputs');
                    const phoneField = document.getElementById('phone_field');
                    const cardField = document.getElementById('card_field');
                    const expField = document.getElementById('exp_field');
                    const cvvField = document.getElementById('cvv_field');
                    if (method === 'gpay' || method === 'phonepe') {
                        // Show UPI, Hide Card
                        upiContainer.style.display = 'block';
                        cardContainer.style.display = 'none';
                        // Set HTML5 Form Validation Requirements
                        phoneField.required = true;
                        cardField.required = false;
                        expField.required = false;
                        cvvField.required = false;
                    } else {
                        // Show Card, Hide UPI
                        upiContainer.style.display = 'none';
                        cardContainer.style.display = 'block';
                        // Set HTML5 Form Validation Requirements
                        phoneField.required = false;
                        cardField.required = true;
                        expField.required = true;
                        cvvField.required = true;
                    }
                }
                // Run once on load to ensure the correct fields are showing initially
                window.onload = toggleInputs;
            </script>
        <?php endif; ?>
    </div>
</body>
</html>