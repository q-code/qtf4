function regsafeChanged(str) {
  qtToggle('#recaptcha2', str==='recaptcha2' ? 'table-row' : 'none', '#config-registration');
  qtToggle('#recaptcha3', str==='recaptcha3' ? 'table-row' : 'none', '#config-registration');
 }