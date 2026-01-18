<h2>Edit Expense</h2>

<form action="/expense/{{ $expense->id }}" method="POST">
    @csrf
    @method('PUT')

    <label>Date</label><br>
    <input type="date" name="date"
           value="{{ $expense->date }}" required><br><br>

    <label>Amount</label><br>
    <input type="text" name="expense" value="{{ $expense->expense }}" required><br><br>

    <label>Category</label><br>
    <select name="category" required>
        <option value="food" {{ $expense->category == 'food' ? 'selected' : '' }}>Food</option>
        <option value="utilities" {{ $expense->category == 'utilities' ? 'selected' : '' }}>Utilities</option>
        <option value="savings" {{ $expense->category == 'savings' ? 'selected' : '' }}>Savings</option>
        <option value="transportation" {{ $expense->category == 'transportation' ? 'selected' : '' }}>Transportation</option>
        <option value="entertainment" {{ $expense->category == 'entertainment' ? 'selected' : '' }}>Entertainment</option>
    </select><br><br>

    <label>Description</label><br>
    <textarea name="description" rows="3">{{ $expense->description }}</textarea><br><br>

    <button type="submit">Update</button>
    <a href="/dashboard" style="margin-left:10px;">Back to Dashboard</a>
</form>

