
<?php
require_once "database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "job_seeker";

    if ($name === "" || $email === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif (!in_array($role, ["job_seeker", "employer"], true)) {
        $message = "Invalid account type.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $hashedPassword,
            $role
        );

        if ($stmt->execute()) {
            $message = "Registration successful! You can now log in.";
        } else {
            if ($stmt->errno === 1062) {
                $message = "That email address is already registered.";
            } else {
                $message = "Registration failed. Please try again.";
            }
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
    <title>Register - Local Job Finder</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Create an Account</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST" action="">
        <label>Full Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password (at least 8 characters):</label><br>
        <input type="password" name="password" minlength="8" required><br><br>

        <label>Account Type:</label><br>
        <select name="role" required>
            <option value="job_seeker">Job Seeker</option>
            <option value="employer">Employer</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account?
        <a href="login.php">Log in here</a>
    </p>

    <p><a href="index.php">Back to Home</a></p>

</body>
</html>
