
<?php
session_start();
require_once "database.php";

// Only logged-in employers can edit jobs.
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "employer"
) {
    header("Location: login.php");
    exit;
}

$employer_id = (int) $_SESSION["user_id"];
$job_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$job_id || $job_id < 1) {
    exit("Invalid job ID.");
}

$message = "";

// Find a job owned by the logged-in employer.
$stmt = $conn->prepare(
    "SELECT title, company, location, salary, description
     FROM jobs
     WHERE id = ? AND employer_id = ?"
);
$stmt->bind_param("ii", $job_id, $employer_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
$stmt->close();

if (!$job) {
    exit("Job not found or you do not have permission to edit it.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $company = trim($_POST["company"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $salary = trim($_POST["salary"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (
        $title === "" || $company === "" ||
        $location === "" || $description === ""
    ) {
        $message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE jobs
             SET title = ?, company = ?, location = ?,
                 salary = ?, description = ?
             WHERE id = ? AND employer_id = ?"
        );

        $stmt->bind_param(
            "sssssii",
            $title,
            $company,
            $location,
            $salary,
            $description,
            $job_id,
            $employer_id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: my-jobs.php");
            exit;
        } else {
            $message = "Unable to update the job. Please try again.";
            $stmt->close();
        }
    }

    // Keep the submitted values in the form if validation fails.
    $job["title"] = $title;
    $job["company"] = $company;
    $job["location"] = $location;
    $job["salary"] = $salary;
    $job["description"] = $description;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Local Job Finder</title>
</head>
<body>

    <h1>Edit Job Listing</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST" action="">
        <label>Job Title: *</label><br>
        <input type="text" name="title"
            maxlength="150"
            value="<?php echo htmlspecialchars($job["title"]); ?>"
            required><br><br>

        <label>Company Name: *</label><br>
        <input type="text" name="company"
            maxlength="150"
            value="<?php echo htmlspecialchars($job["company"]); ?>"
            required><br><br>

        <label>Location: *</label><br>
        <input type="text" name="location"
            maxlength="150"
            value="<?php echo htmlspecialchars($job["location"]); ?>"
            required><br><br>

        <label>Salary:</label><br>
        <input type="text" name="salary"
            maxlength="100"
            value="<?php echo htmlspecialchars($job["salary"] ?? ""); ?>"><br><br>

        <label>Description: *</label><br>
        <textarea name="description" rows="6" cols="40"
            required><?php echo htmlspecialchars($job["description"]); ?></textarea><br><br>

        <button type="submit">Save Changes</button>
    </form>

    <p><a href="my-jobs.php">Cancel and Go Back</a></p>

</body>
</html>
