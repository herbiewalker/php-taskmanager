<!DOCTYPE html>
<html>
<head>
    <title>TaskNinja - The Task Manager V3.5</title>
    <style>
        /* Your CSS styling goes here */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f0f0;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: #fff;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        input[type="submit"], button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>TaskNinja - The Task Manager VS3.5 add feature, fix SQL</h1>

    <?php
    // Database configuration
	$db_host = 'localhost';
	$db_user = 'dh22d';
	$db_pass = 'ZjIyNzBhZjE4';
	$db_name = 'dh22d';

    try {
        // Create a PDO database connection
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Function to create the tasks table if it doesn't exist
        function createTasksTable($conn) {
            $sql = "CREATE TABLE IF NOT EXISTS tasks (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        task TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";

            $conn->exec($sql);
        }

        // Call the function to create the table
        createTasksTable($conn);

        // Initialize variables
        $task = '';
        $edit_id = 0;
        $message = '';

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_task'])) {
                $task = $_POST['task'];

                // Insert task into the database
                $stmt = $conn->prepare("INSERT INTO tasks (task) VALUES (:task)");
                $stmt->bindParam(':task', $task);
                if ($stmt->execute()) {
                    $message = '<p class="success">Task added successfully!</p>';
                } else {
                    $message = '<p class="error">Error: Unable to add task.</p>';
                }
            } elseif (isset($_POST['update_task'])) {
                $edit_id = $_POST['edit_id'];
                $task = $_POST['task'];

                // Update task in the database
                $stmt = $conn->prepare("UPDATE tasks SET task=:task WHERE id=:id");
                $stmt->bindParam(':task', $task);
                $stmt->bindParam(':id', $edit_id);
                if ($stmt->execute()) {
                    $message = '<p class="success">Task updated successfully!</p>';
                } else {
                    $message = '<p class="error">Error: Unable to update task.</p>';
                }
            } elseif (isset($_POST['delete_task'])) {
                $delete_id = $_POST['delete_id'];

                // Delete task from the database
                $stmt = $conn->prepare("DELETE FROM tasks WHERE id=:id");
                $stmt->bindParam(':id', $delete_id);
                if ($stmt->execute()) {
                    $message = '<p class="success">Task deleted successfully!</p>';
                } else {
                    $message = '<p class="error">Error: Unable to delete task.</p>';
                }
            }
        }

        // Sort tasks based on user selection
        $sort_by = isset($_POST['sort_by']) ? $_POST['sort_by'] : 'date';
        if ($sort_by === 'date') {
            $sql = "SELECT * FROM tasks ORDER BY created_at DESC";
        } else {
            $sql = "SELECT * FROM tasks ORDER BY task ASC";
        }

        // Display tasks from the database
        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php echo $message; ?>

        <form method="post">
            <input type="text" name="task" placeholder="Enter your task" value="<?php echo $task; ?>" required />
            <?php if ($edit_id === 0): ?>
                <input type="submit" name="add_task" value="Add Task" />
            <?php else: ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>" />
                <input type="submit" name="update_task" value="Update Task" />
                <button onclick="cancelEdit()">Cancel</button>
            <?php endif; ?>
        </form>

        <form method="post">
            <label for="sort_by">Sort by:</label>
            <select id="sort_by" name="sort_by">
                <option value="date" <?php echo ($sort_by === 'date') ? 'selected' : ''; ?>>Date</option>
                <option value="alphabetical" <?php echo ($sort_by === 'alphabetical') ? 'selected' : ''; ?>>Alphabetical</option>
            </select>
            <input type="submit" value="Sort" />
        </form>

        <table>
            <tr>
                <th>Task</th>
                <th>Action</th>
            </tr>
            <?php foreach ($result as $row) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['task']); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>" />
                            <input type="hidden" name="task" value="<?php echo htmlspecialchars($row['task']); ?>" />
                            <input type="submit" name="edit_task" value="Edit" />
                        </form>
                        <form method="post">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>" />
                            <input type="submit" name="delete_task" value="Delete" onclick="return confirm('Are you sure?')" />
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    ?>

    <script>
        function cancelEdit() {
            window.location.href = window.location.href;
        }
    </script>
</body>
</html>
