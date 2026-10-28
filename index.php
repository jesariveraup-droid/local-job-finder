
<?php
session_start();
require_once "database.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $stmt = $conn->prepare(
        "SELECT jobs.*, users.name AS employer_name
         FROM jobs
         JOIN users ON jobs.employer_id = users.id
         WHERE jobs.title LIKE ?
            OR jobs.location LIKE ?
            OR jobs.company LIKE ?
         ORDER BY jobs.created_at DESC"
    );

    $keyword = "%" . $search . "%";
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
} else {
    $stmt = $conn->prepare(
        "SELECT jobs.*, users.name AS employer_name
         FROM jobs
         JOIN users ON jobs.employer_id = users.id
         ORDER BY jobs.created_at DESC"
    );
}

$stmt->execute();
$jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Job Finder</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Local Job Finder</h1>
    <p>Find job opportunities in your community!</p>

    <nav>
        <a href="index.php">Home</a> |
        <?php if (isset($_SESSION["user_id"])): ?>
            Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?> |
            <?php if ($_SESSION["role"] === "employer"): ?>
                <a href="post-job.php">Post a Job</a> |
                <a href="my-jobs.php">My Job Listings</a> |
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="register.php">Register</a> |
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>

    <hr>

    <h2>Find Local Jobs</h2>

    <form method="GET" action="index.php">
        <input
            type="text"
            name="search"
            placeholder="Search job title, company, or location"
            value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit">Search</button>
        <a href="index.php">Show All</a>
    </form>

    <h2>Available Jobs</h2>

    <?php if ($jobs->num_rows > 0): ?>
        <?php while ($job = $jobs->fetch_assoc()): ?>
            <article>
                <h3><?php echo htmlspecialchars($job["title"]); ?></h3>
                <p>
                    <strong>Company:</strong>
                    <?php echo htmlspecialchars($job["company"]); ?>
                </p>
                <p>
                    <strong>Location:</strong>
                    <?php echo htmlspecialchars($job["location"]); ?>
                </p>
                <p>
                    <strong>Salary:</strong>
                    <?php echo htmlspecialchars($job["salary"] ?: "Not specified"); ?>
                </p>
                <p>
                    <strong>Posted by:</strong>
                    <?php echo htmlspecialchars($job["employer_name"]); ?>
                </p>
                <p>
                    <?php echo nl2br(htmlspecialchars($job["description"])); ?>
                </p>
                <hr>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No jobs found. Please check back later.</p>
    <?php endif; ?>

    <?php $stmt->close(); ?>

</body>
</html>
