// https://gist.github.com/jiankaiwang/dc5690318bd035232a8ac9294bc2af29
function checkRecaptcha() {
  var response = grecaptcha.getResponse();
  if(response.length == 0) {
    // reCaptcha not verified
    alert("no pass");
  }
  else {
    // reCaptcha verified
    alert("pass");
  }
}

// implement on the backend
// function backend_API_challenge() {
//     var response = grecaptcha.getResponse();
//     $.ajax({
//         type: "POST",
//         url: 'https://www.google.com/recaptcha/api/siteverify',
//         data: {"secret" : "(your-secret-key)", "response" : response, "remoteip":"localhost"},
//         contentType: 'application/x-www-form-urlencoded',
//         success: function(data) { console.log(data); }
//     });
// }




// https://formspree.io/blog/grecaptcha/
// grecaptcha.render(containerElement, {
//   sitekey: 'your_site_key',
//   theme: 'light', // or 'dark'
//   size: 'normal', // or 'compact'
//   tabindex: 3,
//   callback: verifyResponse, // optional callback function
//   'expired-callback': handleExpiredToken, // optional callback function
//   'error-callback': handleRenderError // optional callback function
// });
