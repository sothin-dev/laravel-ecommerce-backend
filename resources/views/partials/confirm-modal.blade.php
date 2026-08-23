{{-- Reusable confirmation modal (delete actions) --}}
<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
        <h2 id="confirmDeleteTitle" class="text-lg font-semibold text-gray-800">
            Confirm
        </h2>
        <p id="confirmDeleteMessage" class="text-gray-600 mt-2">
            Are you sure you want to perform this action? This cannot be undone.
        </p>
        <div class="flex justify-end gap-3 mt-6">
            <button type="button" onclick="closeConfirmDelete()"
                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                Cancel
            </button>
            <form id="confirmDeleteForm" method="POST" class="inline">
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

<script>
    function openConfirmDelete(url, title, message) {
        document.getElementById('confirmDeleteForm').action = url;
        document.getElementById('confirmDeleteTitle').textContent = title;
        document.getElementById('confirmDeleteMessage').textContent = message;
        document.getElementById('confirmDeleteModal').classList.remove('hidden');
    }

    function closeConfirmDelete() {
        document.getElementById('confirmDeleteModal').classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeConfirmDelete();
    });
</script>
