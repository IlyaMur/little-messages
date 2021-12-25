const deleteBtn = document.getElementById('deletePost');

if (deleteBtn) {
    deleteBtn.addEventListener('click', e => {
        if (!confirm('Вы уверены, что хотите удалить данный пост?')) {
            e.preventDefault();
        }
    });
}



