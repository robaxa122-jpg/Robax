document.addEventListener('DOMContentLoaded', () => {
  const addButton = document.querySelector('.tamin-exporter-add');
  const table = document.querySelector('.tamin-exporter-workers');

  if (!addButton || !table) {
    return;
  }

  const tbody = table.querySelector('tbody');

  const bindRemove = (row) => {
    const removeButton = row.querySelector('.tamin-exporter-remove');
    if (!removeButton) {
      return;
    }
    removeButton.addEventListener('click', () => {
      if (tbody.querySelectorAll('.tamin-exporter-row').length > 1) {
        row.remove();
      }
    });
  };

  tbody.querySelectorAll('.tamin-exporter-row').forEach(bindRemove);

  addButton.addEventListener('click', () => {
    const templateRow = tbody.querySelector('.tamin-exporter-row');
    if (!templateRow) {
      return;
    }
    const clone = templateRow.cloneNode(true);
    clone.querySelectorAll('input').forEach((input) => {
      input.value = '';
    });
    bindRemove(clone);
    tbody.appendChild(clone);
  });
});
