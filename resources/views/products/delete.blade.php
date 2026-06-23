<div id="deleteModal"
     class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">

    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-semibold mb-4">
            Delete Product
        </h2>

        <p class="text-gray-600 mb-6">
            Are you sure you want to delete this product?
        </p>

        <div class="flex justify-end gap-3">

            <!-- Cancel -->
            <button onclick="document.getElementById('deleteModal').classList.add('hidden')"
               class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                Cancel
            </button>

            <!-- Delete -->
            <form action="{{ route('products.destroy', $product->id) }}"
                  method="POST">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                    Delete
                </button>
            </form>

        </div>
    </div>

</div>