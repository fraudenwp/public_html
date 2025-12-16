function startLoading() {
  $('#page-overlay').show();
  $('body').css('pointer-events', 'none');
}

function stopLoading() {
  $('#page-overlay').hide();
  $('body').css('pointer-events', 'auto');
}
