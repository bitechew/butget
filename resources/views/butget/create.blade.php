<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Butget - Monthly Income</title>
</head>
<body>

    <h2>Input Monthly Income</h2>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="/butget" method="POST">
        @csrf

        <label for="monthly_income">Monthly Income</label><br>
        <input type="text" name="monthly_income" id="monthly_income" required><br><br>

        <button type="submit">Save</button>
        <a href="/dashboard" style="margin-left:10px;">Back to Dashboard</a>
    </form>

</body>
</html>
