<div class="container p-2">
<h1>Simple URL Shortener</h1>
<p>planetlan simple URL Shortener. Makes long URLs shorter :)</p>
</div>
<div class="container bg-dark bg-gradient text-light p-3 shadow-lg rounded">
<form action="#" method="POST" class="row g-4 needs-validation" novalidate>

  <div class="col-md-8">
    <label for="validationCustom01" class="form-label">URL to Shorten</label>
    <input name="url" type="text" class="form-control" id="validationCustom01" value="" placeholder="https://example.com" pattern="[Hh][Tt][Tt][Pp][Ss]?:\/\/(?:(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)(?:\.(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)*(?:\.(?:[a-zA-Z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:\/[^\s]*)?" required>

    <div class="valid-feedback">
      Looks good!
    </div>

    <div class="invalid-feedback">
      Please enter a valid URL
    </div>
  </div>

  <div class="col-md-4">
    <label for="validationCustom02" class="form-label">Validity Duration</label>

    <select name="HoursValid" class="form-select" aria-label="How long should it be valid?" id="validationCustom02" pattern="^[2-9][0-9]{1,3}$" required>
      <option value=24>One Day</option>
      <option value=168>One Week</option>
      <option selected value=720>One Month</option>
      <option value="1440">Two Month</option>
      <option value="2160">Three Month</option>
      <option value="4320">Half a Year</option>
      <option value="8640">One Year</option>
    </select>

    <div class="valid-feedback">
      Looks good!
    </div>

    <div class="invalid-feedback">
      Please pick a Timeframe
    </div>
  </div>

  <div class="col-12">
    <button class="btn btn-primary" type="submit">Submit</button>
  </div>

</form>
</div>

<script>
(function () {
    'use strict'
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
    .forEach(function (form) {
        form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
        }

        form.classList.add('was-validated')
        }, false)
    })
    })()
</script>
