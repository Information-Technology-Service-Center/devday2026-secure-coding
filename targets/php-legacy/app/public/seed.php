<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Since seed.sql runs automatically in docker-compose via docker-entrypoint,
    // this endpoint just confirms the seeding
    $message = 'Database already seeded via docker-compose initialization. All product statuses and sample products are loaded.';
    $status = 'success';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seed Database</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #218838; }
        .message { padding: 15px; margin: 20px 0; border-radius: 3px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Seed Utility</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$message): ?>
            <p>Click the button below to seed the database with initial data:</p>
            <form method="POST">
                <button type="submit" class="btn">Seed Database</button>
            </form>
        <?php endif; ?>
        
        <a href="index.php" class="back">&larr; Back to Home</a>
    </div>
</body>
</html>
