<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Butget Dashboard</title>
</head>
<body>

@if (session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<h1>Butget Dashboard</h1>

<a href="/butget">+ Add Income</a>
<a href="/expense">+ Add Expense</a>

<hr>

<h2>Total Summary</h2>
<p><strong>Total Income:</strong> {{ rupiah($totalIncome) }}</p>
<p><strong>Total Expense:</strong> {{ rupiah($totalExpense) }}</p>
<p><strong>Remaining:</strong> {{ rupiah($remaining) }}</p>

<hr>

<h2>Income List</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Monthly Income</th>
        <th>Action</th>
    </tr>
    @foreach ($incomes as $income)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ rupiah($income->monthly_income) }}</td>
        <td>
            <a href="/butget/{{ $income->id }}/edit">Edit</a>

            <form action="/butget/{{ $income->id }}" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete income?')">Delete</button>
            </form>
        </td>

    </tr>
    @endforeach
</table>

<hr>

<h2>Expense List</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Expense</th>
        <th>Date</th>
        <th>Category</th>
        <th>Description</th>
        <th>Action</th>
    </tr>
    @foreach ($expenses as $expense)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ rupiah($expense->expense) }}</td>
        <td>{{ $expense->date }}</td>
        <td>{{ ucfirst($expense->category) }}</td>
        <td>{{ $expense->description }}</td>
        <td>
            <a href="/expense/{{ $expense->id }}/edit">Edit</a>

            <form action="/expense/{{ $expense->id }}" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete expense?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>

</body>
</html>
