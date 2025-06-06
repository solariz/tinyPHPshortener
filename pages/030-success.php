<div class="container p-2 col-md-4">
<h1>Simple URL Shortener</h1>
<p>simple URL Shortener. Makes long URLs shorter :)</p>
</div>
<div class="container col-md-4 bg-dark bg-gradient text-light p-3 shadow-lg rounded">
  <div class="col-md-12">
    <label class="form-label">Your Short-URL will expire on: <?=$ExpireDate?></label>
    <div class="input-group">
      <input type="text" class="form-control" id="validationCustom01" value="<?=$ShortURL?>" readonly>
      <button class="btn btn-outline btn-primary" type="button" id="copyButton">Copy</button>
    </div>
    <div id="copyMessage" class="text-success"></div>
  </div>
</div>

<script>
  document.getElementById("copyButton").addEventListener("click", function() {
    var inputField = document.getElementById("validationCustom01");
    inputField.select();
    document.execCommand("copy");
    document.getElementById("copyMessage").textContent = "Copied to clipboard";
  });
</script>
