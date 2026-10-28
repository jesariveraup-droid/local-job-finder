
<?php
session_start();
require_once "database.php";

// Only logged-in employers can delete jobs.
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "employer"
) {
    header("Location: login.php");
    exit;
}

$employer_id = (int) $_SESSION["user_id"];
$message = "";

// Get the job ID from the URL.
$job_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$job_id || $job_id < 1) {
    exit("Invalid job ID.");
}

// Find the job and confirm it belongs to this employer.
$stmt = $conn->prepare(
    "SELECT title FROM jobs
     WHERE id = ? AND employer_id = ?"
);
$stmt->bind_param("ii", $job_id, $employer_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
$stmt->close();

if (!$job) {
    exit("Job not found or you do not have permission to delete it.");
}

// Delete only after the employer confirms using the form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare(
        "DELETE FROM jobs
         WHERE id = ? AND employer_id = ?"
    );
    $stmt->bind_param("ii", $job_id, $employer_id);

    if ($stmt->execute() && $stmt->affected_rows === 1) {
        $stmt->close();
        header("Location: my-jobs.php");
        exit;
    }

    $stmt->close();
    $message = "Unable to delete the job. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Job - Local Job Finder</title>
</head>
<body>

    <h1>Delete Job Listing</h1>

    <p>
        Are you sure you want to delete this job?
    </p>

    <h2><?php echo htmlspecialchars($job["title"]); ?></h2>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST" action="">
        <button type="submit">Yes, Delete Job</button>
        <a href="my-jobs.php">Cancel</a>
    </form>

</body>
</html>
