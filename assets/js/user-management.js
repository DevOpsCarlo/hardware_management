const addUserBtn = document.getElementById('add-user-btn');
const userModal = document.getElementById('user-modal');
const toggleClose = document.getElementById('toggle-close');
const userForm = document.getElementById('user-form');
const editUserBtns = document.querySelectorAll('.edit-user-btn');
const deleteUserBtns = document.querySelectorAll('.delete-user-btn');
const selectOptions = document.querySelectorAll('.select-option');

// Open modal for new user
addUserBtn?.addEventListener('click', () => {
  resetForm();
  userModal.classList.remove('hidden');
});

// Close modal
toggleClose?.addEventListener('click', () => {
  userModal.classList.add('hidden');
});

// Close modal when clicking outside
userModal?.addEventListener('click', (e) => {
  if (e.target === userModal) {
    userModal.classList.add('hidden');
  }
});

// Reset form
function resetForm() {
  userForm.reset();
  document.getElementById('user-id').value = '';
  document.getElementById('input-password').required = true;
  document.getElementById('input-confirm-password').required = true;
  document.getElementById('submit-btn').textContent = 'Create User';
}

// Edit user
editUserBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const userId = btn.dataset.id;
    const username = btn.dataset.username;
    const role = btn.dataset.role;

    document.getElementById('user-id').value = userId;
    document.getElementById('input-username').value = username;
    document.getElementById('input-role').value = role;
    document.getElementById('input-password').required = false;
    document.getElementById('input-confirm-password').required = false;
    document.getElementById('submit-btn').textContent = 'Update User';

    userModal.classList.remove('hidden');
  });
});

// Delete user
deleteUserBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const userId = btn.dataset.id;
    const username = btn.dataset.username;

    Swal.fire({
      title: 'Delete User?',
      text: `Are you sure you want to delete "${username}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Delete'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch(`/user?id=${userId}`, {
          method: 'DELETE'
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              text: 'User deleted successfully!',
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true
            }).then(() => {
              location.reload();
            });
          }
        })
        .catch(err => {
          Swal.fire({
            icon: 'error',
            text: 'Failed to delete user'
          });
        });
      }
    });
  });
});

// Toggle options menu
selectOptions.forEach(option => {
  option.addEventListener('click', (e) => {
    e.stopPropagation();
    const optionsDiv = option.parentElement.querySelector('.options');
    optionsDiv.classList.toggle('hidden');
  });
});

// Close options when clicking outside
document.addEventListener('click', () => {
  document.querySelectorAll('.options').forEach(opt => opt.classList.add('hidden'));
});