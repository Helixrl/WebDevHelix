<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'songs'; 
$user = 'chase'; 
$pass = 'chase';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle songs search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT id, artist, title, real_name FROM songs WHERE title LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['artist']) && isset($_POST['title']) && isset($_POST['real_name'])) {
        // Insert new entry
        $artist = htmlspecialchars($_POST['artist']);
        $title = htmlspecialchars($_POST['title']);
        $real_name = htmlspecialchars($_POST['real_name']);
        
        $insert_sql = 'INSERT INTO songs (artist, title, real_name) VALUES (:artist, :title, :real_name)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['artist' => $artist, 'title' => $title, 'real_name' => $real_name]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM songs WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    }
}

// Get all songs for main table
$sql = 'SELECT id, artist, title, real_name FROM songs';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chases Songs</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Chases Songs</h1>
        <p class="hero-subtitle">"Because nothing brings a community together like Music"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a Song to Ban</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Title:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Artist</th>
                                    <th>Title</th>
                                    <th>Real Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['artist']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['real_name']); ?></td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <input type="submit" value="Ban!">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No songs found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Songs in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Artist</th>
                    <th>Title</th>
                    <th>Real Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['artist']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['real_name']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Ban!">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Condemn a Song Today</h2>
        <form action="index5.php" method="post">
            <label for="artist">Artist:</label>
            <input type="text" id="artist" name="artist" required>
            <br><br>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <br><br>
            <label for="real_name">Real Name:</label>
            <input type="text" id="real_name" name="real_name" required>
            <br><br>
            <input type="submit" value="Condemn Song">
        </form>
    </div>
</body>
</html>