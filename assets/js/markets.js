(function(){
  // Example: <select id="lfa-country-chooser" value="pk|uae|global">
  var sel = document.getElementById('lfa-country-chooser');
  if (!sel) return;
  sel.addEventListener('change', function(){
    var m = sel.value;
    if (!m) return;
    var url = window.location.origin + '/?market=' + encodeURIComponent(m);
    window.location.href = url;
  });
})();
