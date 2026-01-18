<h2>Edit Income</h2>

<form action="/butget/{{ $income->id }}" method="POST">
    @csrf
    @method('PUT')

    <input type="number" name="monthly_income" value="{{ $income->monthly_income }}" required>
    <button type="submit">Update</button>
</form>
