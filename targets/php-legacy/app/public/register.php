<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_helper.php';
require_once __DIR__ . '/../includes/validation.php';

$errors = array();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate email
    if (!validateEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    // Validate first name
    if (!validateName($first_name)) {
        $errors[] = 'Invalid first name';
    }
    
    // Validate last name
    if (!validateName($last_name)) {
        $errors[] = 'Invalid last name';
    }
    
    // Validate phone
    if (!validatePhone($phone)) {
        $errors[] = 'Invalid phone number format';
    }
    
    // Validate password
    if (!validatePassword($password)) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    // Check password confirmation
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (emailExists($email)) {
        $errors[] = 'Email already registered';
    }
    
    // If no errors, insert user
    if (empty($errors)) {
        if (insertUser($email, $first_name, $last_name, $phone, $password)) {
            $success = 'Registration successful! You can now <a href="login.php">login</a>.';
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;
        }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; width: 100%; }
        .btn:hover { background: #0056b3; }
        .errors { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Registration</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <strong>Errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <a href="index.php" class="back">&larr; Back to Home</a>
    </div>
</body>
</html>
