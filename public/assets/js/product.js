$(document).on('click', '.js-ajax-request', function (e) {
  e.preventDefault()

  let $btn = $(this)
  let url = $btn.attr('href')
  let method = ($btn.data('method') || 'GET').toUpperCase()
  let data = {}

  // Читаем JSON из data-payload
  let payload = $btn.attr('data-payload')
  if (payload) {
    try {
      data = JSON.parse(payload)
    }
    catch {
      console.error('Invalid JSON in data-payload:', payload)
    }
  }

  // Если GET — сериализуем payload в query
  if (method === 'GET' && Object.keys(data).length) {
    let query = $.param(data) // jQuery умеет массивы как ids[]=1&ids[]=2
    url += (url.includes('?') ? '&' : '?') + query
    data = {} // в теле ничего не шлём
  }

  // Laravel CSRF токен для не-GET
  let csrf = $('meta[name="csrf-token"]').attr('content')
  if (csrf && method !== 'GET') {
    data._token = csrf
  }

  // Иконка/лоадер
  let $icon = $btn.find('em.icon')
  let originalIcon = $icon.length ? $icon.attr('class') : null
  $btn.prop('disabled', true).addClass('disabled')
  if ($icon.length) {
    $icon.attr('class', 'icon ni ni-loader spinner')
  }

  $.ajax({
    url,
    method,
    data,
    success(response) {
      NioApp.Toast(response.message || 'Request successful', 'success', {
        position: 'top-right',
        ui: 'is-dark',
      })
    },
    error(xhr) {
      let error = xhr.responseJSON?.message || 'Error'
      NioApp.Toast(error, 'error', {
        position: 'top-right',
        ui: 'is-dark',
      })
    },
    complete() {
      $btn.prop('disabled', false).removeClass('disabled')
      if ($icon.length && originalIcon) {
        $icon.attr('class', originalIcon)
      }
    },
  })
})
