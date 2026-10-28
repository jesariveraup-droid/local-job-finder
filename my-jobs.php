
<?php
session_start();
require_once "database.php";

// Only employers can access this page.
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "employer"
) {
    header("Location: login.php");
    exit;
}

$employer_id = (int) $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, title, company, location, salary,
            description, created_at
     FROM jobs
     WHERE employer_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $employer_id);
$stmt->execute();
$jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Listings - Local Job Finder</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>My Job Listings</h1>

    <p>
        Employer:
        <?php echo htmlspecialchars($_SESSION["name"]); ?>
    </p>

    <p>
        <a href="index.php">Home</a> |
        <a href="post-job.php">Post a New Job</a> |
        <a href="logout.php">Logout</a>
    </p>

    <hr>

    <?php if ($jobs->num_rows > 0): ?>

        <?php while ($job = $jobs->fetch_assoc()): ?>
            <p>
        <a href="edit-job.php?id=<?php echo (int) $job['id']; ?>">
            Edit Job</a>
            
<p>
    <a href="delete-job.php?id=<?php echo (int) $job['id']; ?>">
        Delete Job
    </a>
</p>

            </p>
            <article>
                <h2>
                    <?php echo htmlspecialchars($job["title"]); ?>
                </h2>

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
                    <?php echo htmlspecialchars(
                        $job["salary"] ?: "Not specified"
                    ); ?>
                </p>

                <p>
                    <strong>Description:</strong><br>
                    <?php echo nl2br(
                        htmlspecialchars($job["description"])
                    ); ?>
                </p>

                <p>
                    <strong>Posted:</strong>
                    <?php echo htmlspecialchars($job["created_at"]); ?>
                </p>

                <hr>
            </article>
        <?php endwhile; ?>

    <?php else: ?>
        <p>You haven't posted any jobs yet.</p>
        <p><a href="post-job.php">Post your first job</a></p>
    <?php endif; ?>

    <?php $stmt->close(); ?>

</body>
</html>
