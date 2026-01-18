<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense</title>
</head>
<body>

<h2>Input Expense</h2>

@if (session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<h2>Add Expense</h2>

<form action="/expense" method="POST">
    @csrf

    <label>Date</label><br>
    <input type="date" name="date" value="{{ date('Y-m-d') }}" required><br><br>

    <label>Amount</label><br>
    <input type="text" name="expense" required><br><br>

    <label>Category</label><br>
    <select name="category" required>
        <option value="">-- Choose Category --</option>
        <option value="food">Food</option>
        <option value="utilities">Utilities</option>
        <option value="savings">Savings</option>
        <option value="transportation">Transportation</option>
        <option value="entertainment">Entertainment</option>
    </select><br><br>

    <label>Description</label><br>
    <textarea name="description" rows="3"></textarea><br><br>

    <button type="submit">Save</button>
    <a href="/dashboard" style="margin-left:10px;">Back to Dashboard</a>
</form>


</body>
</html>
