document.addEventListener('DOMContentLoaded', function() {
  let currentField = '';
  let editingLabel = null;

  // Инициализация функциональности редактирования лейблов
  window.initProductFieldEditor = function(productFieldNames = {}) {

    // Функция обновления состояния иконок редактирования
    function updateEditIconsState(enabled = true) {
      document.querySelectorAll('.edit_product_field').forEach(function(icon) {
        if (enabled) {
          icon.style.opacity = '1';
          icon.style.cursor = 'pointer';
          icon.style.pointerEvents = 'auto';
        } else {
          icon.style.opacity = '0.3';
          icon.style.cursor = 'not-allowed';
          icon.style.pointerEvents = 'none';
        }
      });
    }

    // Функция загрузки лейблов
    function loadFieldNames() {
      // Показываем индикатор загрузки
      showLoadingState();

      const route = window.productFieldRouteGet || '/admin/product/field-names';

      fetch(route)
        .then(response => response.json())
        .then(data => {
          if (data.field_names) {
            updateFieldLabels(data.field_names);
            updateEditIconsState(true);
          }
        })
        .catch(error => {
          console.error('Error loading field names:', error);
          NioApp.Toast('Ошибка загрузки названий полей', 'error', {
            position: 'top-right',
            ui: 'is-dark',
          });
          updateEditIconsState(false);
          resetFieldLabels();
        });
    }

    // Функция показа состояния загрузки
    function showLoadingState() {
      document.querySelectorAll('.edit_product_field').forEach(function(icon) {
        const label = icon.closest('label');
        if (label) {
          const textNode = label.firstChild;
          if (textNode && textNode.nodeType === Node.TEXT_NODE) {
            // Сохраняем оригинальный текст если еще не сохранен
            if (!label.dataset.originalText) {
              label.dataset.originalText = textNode.textContent.trim();
            }
            // Показываем точки загрузки
            textNode.textContent = '... ';
          }
        }
      });
    }

    // Функция сброса лейблов к состоянию по умолчанию
    function resetFieldLabels() {
      document.querySelectorAll('.edit_product_field').forEach(function(icon) {
        const label = icon.closest('label');
        if (label && label.dataset.originalText) {
          const textNode = label.firstChild;
          if (textNode && textNode.nodeType === Node.TEXT_NODE) {
            textNode.textContent = label.dataset.originalText;
          }
        }
      });
    }

    // Функция обновления лейблов
    function updateFieldLabels(fieldNames) {
      Object.keys(fieldNames).forEach(function(field) {
        const customName = fieldNames[field];
        document.querySelectorAll(`.edit_product_field[data-field="${field}"]`).forEach(function(icon) {
          const label = icon.closest('label');
          if (label && customName) {
            // Сохраняем оригинальный текст если еще не сохранен
            if (!label.dataset.originalText) {
              const textNode = label.firstChild;
              if (textNode && textNode.nodeType === Node.TEXT_NODE) {
                label.dataset.originalText = textNode.textContent.trim();
              }
            }
            // Обновляем текст
            const textNode = label.firstChild;
            if (textNode && textNode.nodeType === Node.TEXT_NODE) {
              textNode.textContent = customName + ' ';
            }
          }
        });
      });
    }

    // Функция добавления обработчиков на иконки редактирования
    function attachEditHandlers() {
      document.querySelectorAll('.edit_product_field').forEach(function(icon) {
        // Удаляем существующие обработчики
        icon.removeEventListener('click', handleEditClick);
        icon.addEventListener('click', handleEditClick);
      });
    }

    // Функция обработки клика по иконке редактирования
    function handleEditClick(e) {
      e.preventDefault();
      e.stopPropagation();

      currentField = this.dataset.field;
      editingLabel = this.closest('label');

      // Получаем текущее название поля
      const labelText = editingLabel.textContent.trim().replace(this.outerHTML, '').trim();

      // Создаем элементы редактирования
      const container = document.createElement('div');
      container.className = 'd-flex align-items-center gap-1';
      container.style.marginBottom = '24px';

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'form-control';
      input.value = labelText;
      input.style.width = 'auto';
      input.style.flex = '1';

      const saveBtn = document.createElement('button');
      saveBtn.type = 'button';
      saveBtn.className = 'btn btn-success btn-sm btn-icon';
      saveBtn.innerHTML = '<em class="icon ni ni-check"></em>';
      saveBtn.title = 'Сохранить';

      const cancelBtn = document.createElement('button');
      cancelBtn.type = 'button';
      cancelBtn.className = 'btn btn-danger btn-sm btn-icon';
      cancelBtn.innerHTML = '<em class="icon ni ni-cross"></em>';
      cancelBtn.title = 'Отменить';

      container.appendChild(input);
      container.appendChild(saveBtn);
      container.appendChild(cancelBtn);

      // Скрываем оригинальный label и показываем контейнер редактирования
      editingLabel.style.display = 'none';
      editingLabel.style.marginBottom = '0';
      container.style.marginTop = '0';
      editingLabel.parentNode.insertBefore(container, editingLabel.nextSibling);

      // Фокус на инпут
      input.focus();
      input.select();

      // Обработчики кнопок
      saveBtn.addEventListener('click', function() {
        saveFieldName(input.value.trim());
      });

      cancelBtn.addEventListener('click', function() {
        cancelEdit();
      });

      // Обработчики клавиш
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          saveFieldName(input.value.trim());
        } else if (e.key === 'Escape') {
          e.preventDefault();
          cancelEdit();
        }
      });
    }

    // Функция сохранения
    function saveFieldName(newValue) {
      if (!newValue) {
        NioApp.Toast('Значение не может быть пустым', 'error', {
          position: 'top-right',
          ui: 'is-dark',
        });
        return;
      }

      const formData = new FormData();
      formData.append('field', currentField);
      formData.append('value', newValue);

      const route = window.productFieldRoute || '/admin/product/set-field-name';

      fetch(route, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.message) {
            // Обновляем текст label для всех полей с таким именем
            document.querySelectorAll(`.edit_product_field[data-field="${currentField}"]`).forEach(function(icon) {
              const label = icon.closest('label');
              if (label) {
                // Сохраняем оригинальный текст если еще не сохранен
                if (!label.dataset.originalText) {
                  const textNode = label.firstChild;
                  if (textNode && textNode.nodeType === Node.TEXT_NODE) {
                    label.dataset.originalText = textNode.textContent.trim();
                  }
                }
                // Обновляем текст
                const textNode = label.firstChild;
                if (textNode && textNode.nodeType === Node.TEXT_NODE) {
                  textNode.textContent = newValue + ' ';
                }
              }
            });

            // Удаляем контейнер редактирования и показываем обновленный label
            const container = editingLabel.nextSibling;
            if (container && container.tagName === 'DIV' && container.classList.contains('d-flex')) {
              container.remove();
            }
            editingLabel.style.display = 'block';
            editingLabel.style.marginBottom = '';

            NioApp.Toast(data.message, 'success', {
              position: 'top-right',
              ui: 'is-dark',
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          NioApp.Toast('Произошла ошибка при сохранении', 'error', {
            position: 'top-right',
            ui: 'is-dark',
          });
        });
    }

    // Функция отмены
    function cancelEdit() {
      const container = editingLabel.nextSibling;
      if (container && container.tagName === 'DIV' && container.classList.contains('d-flex')) {
        container.remove();
      }
      editingLabel.style.display = 'block';
      editingLabel.style.marginBottom = '';
    }

    // Инициализация
    attachEditHandlers();

    // Если есть начальные данные, применяем их
    if (productFieldNames && Object.keys(productFieldNames).length > 0) {
      updateFieldLabels(productFieldNames);
      updateEditIconsState(true);
    } else {
      loadFieldNames();
    }
  };

  // Автоматическая инициализация если на странице есть поля для редактирования
  if (document.querySelector('.edit_product_field')) {
    // Пытаемся получить данные о полях из глобальной переменной
    if (window.productFieldNames) {
      window.initProductFieldEditor(window.productFieldNames);
    } else {
      window.initProductFieldEditor();
    }
  }
});
