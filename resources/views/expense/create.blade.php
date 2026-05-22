<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Expense</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white w-full max-w-4xl rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6">Add New Expense</h2>

        @if (session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
        @endif

        <form action="{{ route('expenses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf

            <!-- Date -->
            <div>
                <label class="block text-sm font-medium mb-2">Date</label>
                <input
                    type="date"
                    name="date"
                    value="{{ date('Y-m-d') }}"
                    class="w-full h-12 px-4 rounded-lg border border-gray-400 bg-white
                        focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    required
                >
            </div>

            <!-- Amount -->
            <div>
                <label class="block text-sm font-medium mb-2">Amount ($)</label>
                <input
                    type="text"
                    step="0.01"
                    name="expense"
                    placeholder="0.00"
                    class="w-full h-12 px-4 rounded-lg border border-gray-400 bg-white
                        focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    required
                >
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select
                    name="category"
                    class="w-full h-12 px-4 pr-12 rounded-lg border border-gray-400 bg-white
                        focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    required
                >
                    <option value="food">Food</option>
                    <option value="utilities">Utilities</option>
                    <option value="savings">Savings</option>
                    <option value="transportation">Transportation</option>
                    <option value="entertainment">Entertainment</option>
                </select>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium mb-2">Description (optional)</label>
                <input
                    type="text"
                    name="description"
                    placeholder="e.g., Grocery shopping"
                    class="w-full h-12 px-4 rounded-lg border border-gray-400 bg-white
                        focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                >
            </div>

            <!-- Button -->
            <div class="md:col-span-2 mt-4 flex items-center gap-4">
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition"
                >
                    Save Expense
                </button>

                <a href="{{ route('butgets.index') }}" class="text-gray-600 hover:underline">
                    Back to Dashboard
                </a>
            </div>
        </form>
    </div>

</body>
</html>
