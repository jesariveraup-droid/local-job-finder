
<?php
session_start();
require_once "database.php";

// Only logged-in employers can post jobs.
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "employer") {
    header("Location: login.php");
    exit;
}

$message = "";

$title = "";
$company = "";
$location = "";
$salary = "";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $company = trim($_POST["company"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $salary = trim($_POST["salary"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($title === "" || $company === "" ||
        $location === "" || $description === "") {
        $message = "Please fill in all required fields.";
    } else {
        $employer_id = (int) $_SESSION["user_id"];

        $stmt = $conn->prepare(
            "INSERT INTO jobs
            (employer_id, title, company, location, salary, description)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isssss",
            $employer_id,
            $title,
            $company,
            $location,
            $salary,
            $description
        );

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $message = "Unable to post the job. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - Local Job Finder</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Post a Job</h1>

    <p>
        Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!
    </p>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST" action="">
        <label>Job Title: *</label><br>
        <input type="text" name="title"
            value="<?php echo htmlspecialchars($title); ?>"
            maxlength="150" required><br><br>

        <label>Company Name: *</label><br>
        <input type="text" name="company"
            value="<?php echo htmlspecialchars($company); ?>"
            maxlength="150" required><br><br>

        <label>Job Location: *</label><br>
        <input type="text" name="location"
            placeholder="e.g. Balanga, Bataan"
            value="<?php echo htmlspecialchars($location); ?>"
            maxlength="150" required><br><br>

        <label>Salary:</label><br>
        <input type="text" name="salary"
            placeholder="e.g. ₱500 per day"
            value="<?php echo htmlspecialchars($salary); ?>"
            maxlength="100"><br><br>

        <label>Job Description: *</label><br>
        <textarea name="description" rows="6" cols="40"
            required><?php echo htmlspecialchars($description); ?></textarea><br><br>

        <button type="submit">Publish Job</button>
    </form>

    <p><a href="index.php">Back to Home</a></p>

</body>
</html>
