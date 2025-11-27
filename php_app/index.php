<?php
$host = getenv('DB_HOST') ?: '136.114.93.122';
$dbname = getenv('DB_NAME') ?: '88327';
$username = getenv('DB_USER') ?: 'stud';
$password = getenv('DB_PASSWORD') ?: 'Uwb123!!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $stmt = $pdo->prepare("INSERT INTO tasks (title, description) VALUES (?, ?)");
                $stmt->execute([$title, $description]);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                
            case 'complete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE tasks SET completed = 1 WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
        }
    }
}

// Fetch all tasks
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        button {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        .tasks {
            margin-top: 40px;
        }
        
        .task {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .task.completed {
            opacity: 0.6;
            border-left-color: #28a745;
        }
        
        .task.completed .task-content h3 {
            text-decoration: line-through;
        }
        
        .task-content {
            flex: 1;
        }
        
        .task-content h3 {
            color: #333;
            margin-bottom: 8px;
        }
        
        .task-content p {
            color: #666;
            margin-bottom: 5px;
        }
        
        .task-content small {
            color: #999;
        }
        
        .task-actions {
            display: flex;
            gap: 10px;
        }
        
        .task-actions button {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .btn-complete {
            background: #28a745;
        }
        
        .btn-complete:hover {
            background: #218838;
        }
        
        .btn-delete {
            background: #dc3545;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .no-tasks {
            text-align: center;
            color: #999;
            padding: 40px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task Manager</h1>
        
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <button type="submit">Add Task</button>
        </form>
        
        <div class="tasks">
            <h2>Your Tasks</h2>
            
            <?php if (empty($tasks)): ?>
                <div class="no-tasks">No tasks yet. Add your first task above!</div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task <?= $task['completed'] ? 'completed' : '' ?>">
                        <div class="task-content">
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                            <p><?= htmlspecialchars($task['description']) ?></p>
                            <small>Created: <?= date('M d, Y H:i', strtotime($task['created_at'])) ?></small>
                        </div>
                        
                        <div class="task-actions">
                            <?php if (!$task['completed']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="btn-complete">✓ Complete</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                <button type="submit" class="btn-delete">✗ Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>